<?php
/**
 * Fixed Profile Picture Upload API
 * Enhanced error handling and debugging
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Add CORS headers if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../includes/auth.php';
    require_once '../config/database.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load required files: ' . $e->getMessage()]);
    exit();
}

// Check authentication
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Debug information
$debug = [
    'post_data' => !empty($_POST),
    'files_data' => !empty($_FILES),
    'file_uploads_enabled' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];

try {
    // Check if file was uploaded
    if (!isset($_FILES['profile_picture'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'No file uploaded',
            'debug' => $debug
        ]);
        exit();
    }
    
    $file = $_FILES['profile_picture'];
    $user_id = $_SESSION['user_id'];
    
    // Check upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        echo json_encode([
            'success' => false,
            'message' => $error_messages[$file['error']] ?? 'Unknown upload error',
            'error_code' => $file['error'],
            'debug' => $debug
        ]);
        exit();
    }
    
    // Validate file exists and is readable
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Uploaded file is not accessible',
            'debug' => array_merge($debug, [
                'tmp_file_exists' => file_exists($file['tmp_name']),
                'tmp_file_readable' => is_readable($file['tmp_name']),
                'tmp_name' => $file['tmp_name']
            ])
        ]);
        exit();
    }
    
    // Validate file type using multiple methods
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Method 1: Check MIME type from upload
    $upload_mime = $file['type'];
    
    // Method 2: Use finfo if available
    $finfo_mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $finfo_mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    }
    
    // Method 3: Use getimagesize
    $image_info = @getimagesize($file['tmp_name']);
    $getimagesize_mime = $image_info ? $image_info['mime'] : '';
    
    // Use the most reliable MIME type
    $mime_type = $finfo_mime ?: $getimagesize_mime ?: $upload_mime;
    
    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.',
            'detected_type' => $mime_type,
            'debug' => [
                'upload_mime' => $upload_mime,
                'finfo_mime' => $finfo_mime,
                'getimagesize_mime' => $getimagesize_mime
            ]
        ]);
        exit();
    }
    
    // Validate file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode([
            'success' => false,
            'message' => 'File too large. Maximum size is 5MB.',
            'file_size' => $file['size']
        ]);
        exit();
    }
    
    // Validate image dimensions
    if (!$image_info) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid image file or corrupted'
        ]);
        exit();
    }
    
    $width = $image_info[0];
    $height = $image_info[1];
    
    if ($width < 50 || $height < 50) {
        echo json_encode([
            'success' => false,
            'message' => 'Image too small. Minimum size is 50x50 pixels.',
            'dimensions' => $width . 'x' . $height
        ]);
        exit();
    }
    
    // Create upload directory
    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create upload directory'
            ]);
            exit();
        }
    }
    
    // Check directory permissions
    if (!is_writable($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Upload directory is not writable',
            'directory' => $upload_dir,
            'permissions' => substr(sprintf('%o', fileperms($upload_dir)), -4)
        ]);
        exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = $mime_type === 'image/jpeg' ? 'jpg' : 
                    ($mime_type === 'image/png' ? 'png' : 'gif');
    }
    
    $filename = 'profile_' . $user_id . '_' . time() . '.' . strtolower($extension);
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save uploaded file',
            'destination' => $file_path,
            'last_error' => error_get_last()
        ]);
        exit();
    }
    
    // Process image (resize if needed)
    if (!processImage($file_path, $mime_type)) {
        // If processing fails, keep original but log warning
        error_log("Image processing failed for: " . $file_path);
    }
    
    // Update database
    $db = getDB();
    
    // Get current profile picture
    $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    
    // Update user's profile picture
    $stmt = $db->prepare("UPDATE users SET profile_picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$filename, $user_id]);
    
    // Delete old profile picture (if not default)
    if ($current_user && $current_user['profile_picture'] !== 'default-avatar.jpg') {
        $old_file = $upload_dir . $current_user['profile_picture'];
        if (file_exists($old_file)) {
            @unlink($old_file);
        }
    }
    
    // Update session
    $_SESSION['profile_picture'] = $filename;
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'message' => 'Profile picture updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in profile upload: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in profile upload: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}

/**
 * Process and resize image
 */
function processImage($file_path, $mime_type) {
    try {
        // Create image resource
        switch ($mime_type) {
            case 'image/jpeg':
                $source = @imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($file_path);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        $original_width = imagesx($source);
        $original_height = imagesy($source);
        
        // Only resize if image is larger than 300px
        if ($original_width > 300 || $original_height > 300) {
            $max_size = 300;
            
            // Calculate new dimensions maintaining aspect ratio
            if ($original_width > $original_height) {
                $new_width = $max_size;
                $new_height = ($original_height * $max_size) / $original_width;
            } else {
                $new_height = $max_size;
                $new_width = ($original_width * $max_size) / $original_height;
            }
            
            // Create new image
            $destination = imagecreatetruecolor($new_width, $new_height);
            
            // Preserve transparency for PNG
            if ($mime_type === 'image/png') {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
            }
            
            // Resize image
            imagecopyresampled(
                $destination, $source,
                0, 0, 0, 0,
                $new_width, $new_height,
                $original_width, $original_height
            );
            
            // Save resized image
            switch ($mime_type) {
                case 'image/jpeg':
                    imagejpeg($destination, $file_path, 90);
                    break;
                case 'image/png':
                    imagepng($destination, $file_path);
                    break;
                case 'image/gif':
                    imagegif($destination, $file_path);
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
?>
