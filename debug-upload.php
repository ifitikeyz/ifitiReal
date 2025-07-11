<?php
/**
 * Debug Profile Picture Upload
 * This file helps identify upload issues
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

// Require user to be logged in
$auth->requireLogin();

$debug_info = [];

// Check PHP configuration
$debug_info['php_config'] = [
    'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'max_file_uploads' => ini_get('max_file_uploads')
];

// Check directory permissions
$upload_dir = 'uploads/profiles/';
$debug_info['directory'] = [
    'exists' => is_dir($upload_dir),
    'writable' => is_writable($upload_dir),
    'permissions' => substr(sprintf('%o', fileperms($upload_dir)), -4) ?? 'N/A'
];

// Check GD extension
$debug_info['gd'] = [
    'loaded' => extension_loaded('gd'),
    'version' => function_exists('gd_info') ? gd_info()['GD Version'] ?? 'Unknown' : 'N/A',
    'jpeg_support' => function_exists('imagecreatefromjpeg'),
    'png_support' => function_exists('imagecreatefrompng'),
    'gif_support' => function_exists('imagecreatefromgif'),
    'webp_support' => function_exists('imagecreatefromwebp')
];

// Check database connection
try {
    $db = getDB();
    $debug_info['database'] = [
        'connected' => true,
        'users_table_exists' => false
    ];
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    $debug_info['database']['users_table_exists'] = $stmt->rowCount() > 0;
    
} catch (Exception $e) {
    $debug_info['database'] = [
        'connected' => false,
        'error' => $e->getMessage()
    ];
}

// Test file upload if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_upload'])) {
    $debug_info['upload_test'] = testFileUpload($_FILES['test_upload']);
}

function testFileUpload($file) {
    $result = [
        'file_received' => !empty($file['name']),
        'file_name' => $file['name'] ?? 'N/A',
        'file_size' => $file['size'] ?? 0,
        'file_type' => $file['type'] ?? 'N/A',
        'upload_error' => $file['error'] ?? 'N/A',
        'upload_error_message' => getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE)
    ];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $result['temp_file_exists'] = file_exists($file['tmp_name']);
        $result['temp_file_readable'] = is_readable($file['tmp_name']);
        
        // Try to get image info
        if (function_exists('getimagesize')) {
            $image_info = @getimagesize($file['tmp_name']);
            $result['image_info'] = $image_info ? [
                'width' => $image_info[0],
                'height' => $image_info[1],
                'type' => $image_info[2],
                'mime' => $image_info['mime']
            ] : 'Failed to get image info';
        }
        
        // Try to move file
        $upload_dir = 'uploads/profiles/';
        $test_filename = 'test_' . time() . '.jpg';
        $test_path = $upload_dir . $test_filename;
        
        if (move_uploaded_file($file['tmp_name'], $test_path)) {
            $result['file_moved'] = true;
            $result['final_path'] = $test_path;
            
            // Clean up test file
            if (file_exists($test_path)) {
                unlink($test_path);
            }
        } else {
            $result['file_moved'] = false;
            $result['move_error'] = error_get_last()['message'] ?? 'Unknown error';
        }
    }
    
    return $result;
}

function getUploadErrorMessage($error_code) {
    $errors = [
        UPLOAD_ERR_OK => 'No error',
        UPLOAD_ERR_INI_SIZE => 'File too large (php.ini limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    
    return $errors[$error_code] ?? 'Unknown error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Debug - Instagram Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .debug-container {
            max-width: 800px;
            margin: 80px auto 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .debug-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #dbdbdb;
            border-radius: 6px;
        }
        
        .debug-section h3 {
            margin-bottom: 15px;
            color: #262626;
            border-bottom: 2px solid #0095f6;
            padding-bottom: 5px;
        }
        
        .debug-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #efefef;
        }
        
        .debug-item:last-child {
            border-bottom: none;
        }
        
        .debug-label {
            font-weight: 600;
            color: #262626;
        }
        
        .debug-value {
            color: #8e8e8e;
        }
        
        .status-ok {
            color: #2ed573;
            font-weight: 600;
        }
        
        .status-error {
            color: #ed4956;
            font-weight: 600;
        }
        
        .status-warning {
            color: #ff9500;
            font-weight: 600;
        }
        
        .test-upload {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .upload-btn {
            background: #0095f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0095f6;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <a href="upload-profile-picture.php" class="back-link">← Back to Upload</a>
        
        <h1>🔧 Profile Picture Upload Debug</h1>
        <p>This page helps identify issues with profile picture uploads.</p>
        
        <!-- PHP Configuration -->
        <div class="debug-section">
            <h3>📋 PHP Configuration</h3>
            <?php foreach ($debug_info['php_config'] as $key => $value): ?>
                <div class="debug-item">
                    <span class="debug-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span>
                    <span class="debug-value <?php echo $key === 'file_uploads' ? ($value === 'Enabled' ? 'status-ok' : 'status-error') : ''; ?>">
                        <?php echo $value; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Directory Permissions -->
        <div class="debug-section">
            <h3>📁 Directory Status</h3>
            <?php foreach ($debug_info['directory'] as $key => $value): ?>
                <div class="debug-item">
                    <span class="debug-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span>
                    <span class="debug-value <?php 
                        if ($key === 'exists' || $key === 'writable') {
                            echo $value ? 'status-ok' : 'status-error';
                        }
                    ?>">
                        <?php echo is_bool($value) ? ($value ? 'Yes' : 'No') : $value; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- GD Extension -->
        <div class="debug-section">
            <h3>🖼️ Image Processing (GD)</h3>
            <?php foreach ($debug_info['gd'] as $key => $value): ?>
                <div class="debug-item">
                    <span class="debug-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span>
                    <span class="debug-value <?php 
                        if (strpos($key, 'support') !== false || $key === 'loaded') {
                            echo $value ? 'status-ok' : 'status-error';
                        }
                    ?>">
                        <?php echo is_bool($value) ? ($value ? 'Yes' : 'No') : $value; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Database -->
        <div class="debug-section">
            <h3>🗄️ Database Connection</h3>
            <?php foreach ($debug_info['database'] as $key => $value): ?>
                <div class="debug-item">
                    <span class="debug-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span>
                    <span class="debug-value <?php 
                        if ($key === 'connected' || $key === 'users_table_exists') {
                            echo $value ? 'status-ok' : 'status-error';
                        }
                    ?>">
                        <?php echo is_bool($value) ? ($value ? 'Yes' : 'No') : $value; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Upload Test -->
        <div class="debug-section">
            <h3>🧪 Upload Test</h3>
            <p>Upload a test image to check if the upload process works:</p>
            
            <div class="test-upload">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="test_upload" accept="image/*" required>
                    <button type="submit" class="upload-btn">Test Upload</button>
                </form>
            </div>
            
            <?php if (isset($debug_info['upload_test'])): ?>
                <h4 style="margin-top: 20px; color: #262626;">Test Results:</h4>
                <?php foreach ($debug_info['upload_test'] as $key => $value): ?>
                    <div class="debug-item">
                        <span class="debug-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</span>
                        <span class="debug-value <?php 
                            if ($key === 'file_moved' || $key === 'temp_file_exists' || $key === 'temp_file_readable') {
                                echo $value ? 'status-ok' : 'status-error';
                            } elseif ($key === 'upload_error' && $value != 0) {
                                echo 'status-error';
                            }
                        ?>">
                            <?php 
                            if (is_array($value)) {
                                echo json_encode($value, JSON_PRETTY_PRINT);
                            } else {
                                echo is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
                            }
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Recommendations -->
        <div class="debug-section">
            <h3>💡 Common Solutions</h3>
            <div style="line-height: 1.6; color: #262626;">
                <h4>If uploads are failing, try these fixes:</h4>
                <ol>
                    <li><strong>Directory Permissions:</strong> Set uploads/profiles/ to 755 or 777</li>
                    <li><strong>PHP Settings:</strong> Increase upload_max_filesize and post_max_size</li>
                    <li><strong>GD Extension:</strong> Install/enable PHP GD extension</li>
                    <li><strong>File Size:</strong> Check if files exceed PHP limits</li>
                    <li><strong>JavaScript Errors:</strong> Check browser console for errors</li>
                    <li><strong>API Path:</strong> Ensure api/upload_profile_picture.php exists</li>
                </ol>
                
                <h4 style="margin-top: 20px;">Quick Fixes:</h4>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;">
# Fix directory permissions
chmod 755 uploads/
chmod 755 uploads/profiles/

# Or if needed:
chmod 777 uploads/profiles/

# Check PHP settings in php.ini:
upload_max_filesize = 10M
post_max_size = 10M
file_uploads = On
                </pre>
            </div>
        </div>
    </div>
</body>
</html>
