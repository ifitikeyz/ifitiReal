<?php
/**
 * Enhanced Profile Picture Upload API
 * Handles cropped image upload with multiple size generation
 */

header('Content-Type: application/json');

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validate file upload
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit();
    }
    
    $file = $_FILES['profile_picture'];
    $user_id = $_SESSION['user_id'];
    
    // Validate file type using finfo
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Only JPEG, PNG, GIF, and WebP files are allowed']);
        exit();
    }
    
    // Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 10MB']);
        exit();
    }
    
    // Validate image
    $image_info = getimagesize($file['tmp_name']);
    if (!$image_info) {
        echo json_encode(['success' => false, 'message' => 'Invalid image file']);
        exit();
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = getExtensionFromMimeType($mime_type);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        exit();
    }
    
    // Process image (create multiple sizes)
    if (!processProfilePicture($file_path, $mime_type)) {
        unlink($file_path);
        echo json_encode(['success' => false, 'message' => 'Failed to process image']);
        exit();
    }
    
    // Update database
    $db = getDB();
    
    // Get current profile picture to delete old one
    $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    
    // Update user's profile picture
    $stmt = $db->prepare("UPDATE users SET profile_picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$filename, $user_id]);
    
    // Delete old profile picture files (if not default)
    if ($current_user && $current_user['profile_picture'] !== 'default-avatar.jpg') {
        deleteOldProfilePicture($upload_dir, $current_user['profile_picture']);
    }
    
    // Update session
    $_SESSION['profile_picture'] = $filename;
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'message' => 'Profile picture updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Profile picture upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log("Profile picture upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

/**
 * Get file extension from MIME type
 */
function getExtensionFromMimeType($mime_type) {
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    return $extensions[$mime_type] ?? 'jpg';
}

/**
 * Process profile picture and create multiple sizes
 */
function processProfilePicture($file_path, $mime_type) {
    try {
        // Create image resource based on type
        switch ($mime_type) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($file_path);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        $original_width = imagesx($source);
        $original_height = imagesy($source);
        
        // Create multiple sizes for different use cases
        $sizes = [
            'large' => 300,   // Profile page, modals
            'medium' => 150,  // Posts, comments
            'small' => 56,    // Navigation, small avatars
            'thumb' => 32     // Tiny avatars
        ];
        
        foreach ($sizes as $size_name => $size) {
            $destination = imagecreatetruecolor($size, $size);
            
            // Preserve transparency for PNG and GIF
            if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $size, $size, $transparent);
            }
            
            // Use high-quality resampling
            imagecopyresampled(
                $destination, $source,
                0, 0, 0, 0,
                $size, $size, $original_width, $original_height
            );
            
            // Save processed image
            $size_file_path = str_replace('.', '_' . $size_name . '.', $file_path);
            
            switch ($mime_type) {
                case 'image/jpeg':
                    imagejpeg($destination, $size_file_path, 92);
                    break;
                case 'image/png':
                    imagepng($destination, $size_file_path, 8);
                    break;
                case 'image/gif':
                    imagegif($destination, $size_file_path);
                    break;
                case 'image/webp':
                    imagewebp($destination, $size_file_path, 92);
                    break;
            }
            
            imagedestroy($destination);
        }
        
        imagedestroy($source);
        return true;
        
    } catch (Exception $e) {
        error_log("Image processing error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete old profile picture files
 */
function deleteOldProfilePicture($upload_dir, $filename) {
    $base_name = pathinfo($filename, PATHINFO_FILENAME);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    $files_to_delete = [
        $filename,
        $base_name . '_large.' . $extension,
        $base_name . '_medium.' . $extension,
        $base_name . '_small.' . $extension,
        $base_name . '_thumb.' . $extension
    ];
    
    foreach ($files_to_delete as $file) {
        $file_path = $upload_dir . $file;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}
?>
