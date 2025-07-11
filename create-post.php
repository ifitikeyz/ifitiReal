<?php
/**
 * Create New Property Listing
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

// Require agent to be logged in
$agentAuth->requireLogin();

$current_agent = $agentAuth->getCurrentAgent();
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $property_type = $_POST['property_type'] ?? '';
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $area_sqft = intval($_POST['area_sqft'] ?? 0);
    $features = $_POST['features'] ?? [];
    $contact_info = trim($_POST['contact_info'] ?? '');
    
    // Validation
    if (empty($title) || empty($description) || $price <= 0 || empty($location) || empty($property_type)) {
        $error_message = 'Please fill in all required fields';
    } else {
        try {
            $db = getDB();
            
            // Handle file uploads
            $uploaded_images = [];
            $uploaded_videos = [];
            
            // Process image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploaded_images = handleImageUploads($_FILES['images']);
            }
            
            // Process video uploads
            if (!empty($_FILES['videos']['name'][0])) {
                $uploaded_videos = handleVideoUploads($_FILES['videos']);
            }
            
            // Insert post
            $stmt = $db->prepare("
                INSERT INTO posts (agent_id, title, description, price, location, property_type, 
                                 bedrooms, bathrooms, area_sqft, images, videos, features, contact_info) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $current_agent['id'],
                $title,
                $description,
                $price,
                $location,
                $property_type,
                $bedrooms,
                $bathrooms,
                $area_sqft,
                json_encode($uploaded_images),
                json_encode($uploaded_videos),
                json_encode($features),
                $contact_info
            ]);
            
            $success_message = 'Property listing created successfully!';
            
            // Redirect to dashboard after 2 seconds
            header("refresh:2;url=dashboard.php");
            
        } catch (Exception $e) {
            error_log("Create post error: " . $e->getMessage());
            $error_message = 'Failed to create listing. Please try again.';
        }
    }
}

/**
 * Handle image uploads
 */
function handleImageUploads($files) {
    $uploaded_files = [];
    $upload_dir = 'uploads/properties/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_type = $files['type'][$i];
            $file_size = $files['size'][$i];
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                    // Resize image
                    resizeImage($file_path, 1200, 800);
                    $uploaded_files[] = $filename;
                }
            }
        }
    }
    
    return $uploaded_files;
}

/**
 * Handle video uploads
 */
function handleVideoUploads($files) {
    $uploaded_files = [];
    $upload_dir = 'uploads/properties/videos/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_size = 50 * 1024 * 1024; // 50MB
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_type = $files['type'][$i];
            $file_size = $files['size'][$i];
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $file_extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                    $uploaded_files[] = $filename;
                }
            }
        }
    }
    
    return $uploaded_files;
}

/**
 * Resize image
 */
function resizeImage($file_path, $max_width, $max_height) {
    $image_info = getimagesize($file_path);
    if (!$image_info) return false;
    
    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];
    
    if ($width <= $max_width && $height <= $max_height) {
        return true;
    }
    
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }
    
    $destination = imagecreatetruecolor($new_width, $new_height);
    
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $file_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $file_path);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $file_path);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $file_path, 90);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - ifiti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/create-post.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header/Navigation -->
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <a href="index.php">
                    <i></i>
                    <img src="logo.png" style="height: 50px; box-shadow: 1px 3px 5px black; border-radius: 30%;">
                </a>
            </div>
            
            <div class="nav-center">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="create-post.php" class="nav-link active">
                    <i class="fas fa-plus"></i>
                    New Post
                </a>
              
            </div>
            
            <div class="nav-actions">
                <div class="agent-menu">
                    <img src="uploads/profiles/<?php echo $current_agent['profile_picture']; ?>" 
                         alt="Profile" class="nav-profile-pic" onclick="toggleAgentMenu()">
                    <div class="agent-dropdown" id="agentDropdown">
                        <div class="agent-info">
                            <div class="agent-name"><?php echo htmlspecialchars($current_agent['full_name']); ?></div>
                            <div class="agent-agency"><?php echo htmlspecialchars($current_agent['agency_name'] ?? 'Independent'); ?></div>
                        </div>
                        <hr>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Edit Profile
                        </a>
                        <a href="change-password.php" class="dropdown-item">
                            <i class="fas fa-lock"></i>
                            Change Password
                        </a>
                        <a href="index.php" class="dropdown-item">
                            <i class="fas fa-eye"></i>
                            View Public Feed
                        </a>
                        <hr>
                        <a href="logout.php" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main content -->
    <main class="main-content">
        <div class="create-post-container">
            <div class="create-post-header">
                <h1>Create New Post</h1>
                <p>Add a new property to your portfolio</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <br><small>Redirecting to dashboard...</small>
                </div>
            <?php endif; ?>
            
            <form class="create-post-form" method="POST" enctype="multipart/form-data" id="createPostForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Basic Information
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-tag"></i>
                                Post Title *
                            </label>
                            <input 
                                type="text" 
                                id="title"
                                name="title" 
                                class="form-input" 
                                required
                                placeholder="e.g., Luxury Downtown Apartment"
                                maxlength="200"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="property_type" class="form-label">
                                <i class="fas fa-building"></i>
                                Property Type *
                            </label>
                            <select id="property_type" name="property_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="condo">Condo</option>
                                <option value="studio">Studio</option>
                                <option value="villa">Villa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i>
                            Description *
                        </label>
                        <textarea 
                            id="description"
                            name="description" 
                            class="form-textarea" 
                            required
                            placeholder="Describe your property in detail..."
                            rows="5"
                        ></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price" class="form-label">
                                <i class="fas fa-dollar-sign"></i>
                                Yearly Rent *
                            </label>
                            <input 
                                type="number" 
                                id="price"
                                name="price" 
                                class="form-input" 
                                required
                                min="0"
                                step="0.01"
                                placeholder="2500"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Location *
                            </label>
                            <input 
                                type="text" 
                                id="location"
                                name="location" 
                                class="form-input" 
                                required
                                placeholder="e.g., Downtown, New York"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Property Details -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-home"></i>
                        Property Details
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bedrooms" class="form-label">
                                <i class="fas fa-bed"></i>
                                Bedrooms
                            </label>
                            <input 
                                type="number" 
                                id="bedrooms"
                                name="bedrooms" 
                                class="form-input" 
                                min="0"
                                max="20"
                                value="0"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="bathrooms" class="form-label">
                                <i class="fas fa-bath"></i>
                                Bathrooms
                            </label>
                            <input 
                                type="number" 
                                id="bathrooms"
                                name="bathrooms" 
                                class="form-input" 
                                min="0"
                                max="20"
                                value="0"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="area_sqft" class="form-label">
                                <i class="fas fa-ruler-combined"></i>
                                Area (sqft)
                            </label>
                            <input 
                                type="number" 
                                id="area_sqft"
                                name="area_sqft" 
                                class="form-input" 
                                min="0"
                                placeholder="1200"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Media Upload -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-camera"></i>
                        Media Upload
                    </h3>
                    
                    <div class="upload-section">
                        <div class="upload-group">
                            <label for="images" class="upload-label">
                                <i class="fas fa-images"></i>
                                Property Images
                            </label>
                            <div class="file-upload-area" id="imageUploadArea">
                                <input 
                                    type="file" 
                                    id="images"
                                    name="images[]" 
                                    class="file-input" 
                                    accept="image/*"
                                    multiple
                                >
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to upload images or drag and drop</p>
                                    <small>JPEG, PNG, GIF, WebP up to 5MB each</small>
                                </div>
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
                        
                        <div class="upload-group">
                            <label for="videos" class="upload-label">
                                <i class="fas fa-video"></i>
                                Property Videos (Optional)
                            </label>
                            <div class="file-upload-area" id="videoUploadArea">
                                <input 
                                    type="file" 
                                    id="videos"
                                    name="videos[]" 
                                    class="file-input" 
                                    accept="video/*"
                                    multiple
                                >
                                <div class="upload-placeholder">
                                    <i class="fas fa-video"></i>
                                    <p>Click to upload videos or drag and drop</p>
                                    <small>MP4, WebM, OGG up to 50MB each</small>
                                </div>
                            </div>
                            <div class="video-preview" id="videoPreview"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Features & Amenities -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-star"></i>
                        Features & Amenities
                    </h3>
                    
                    <div class="features-grid">
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Parking">
                            <span class="checkmark"></span>
                            <i class="fas fa-car"></i>
                            Parking
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Gym">
                            <span class="checkmark"></span>
                            <i class="fas fa-dumbbell"></i>
                            Gym
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Pool">
                            <span class="checkmark"></span>
                            <i class="fas fa-swimming-pool"></i>
                            Pool
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Balcony">
                            <span class="checkmark"></span>
                            <i class="fas fa-building"></i>
                            Balcony
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Garden">
                            <span class="checkmark"></span>
                            <i class="fas fa-leaf"></i>
                            Garden
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Pet Friendly">
                            <span class="checkmark"></span>
                            <i class="fas fa-paw"></i>
                            Pet Friendly
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Furnished">
                            <span class="checkmark"></span>
                            <i class="fas fa-couch"></i>
                            Furnished
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Air Conditioning">
                            <span class="checkmark"></span>
                            <i class="fas fa-snowflake"></i>
                            Air Conditioning
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Heating">
                            <span class="checkmark"></span>
                            <i class="fas fa-fire"></i>
                            Heating
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Laundry">
                            <span class="checkmark"></span>
                            <i class="fas fa-tshirt"></i>
                            Laundry
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Dishwasher">
                            <span class="checkmark"></span>
                            <i class="fas fa-utensils"></i>
                            Dishwasher
                        </label>
                        
                        <label class="feature-checkbox">
                            <input type="checkbox" name="features[]" value="Security">
                            <span class="checkmark"></span>
                            <i class="fas fa-shield-alt"></i>
                            Security
                        </label>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-phone"></i>
                        Contact Information
                    </h3>
                    
                    <div class="form-group">
                        <label for="contact_info" class="form-label">
                            <i class="fas fa-address-card"></i>
                            Contact Details
                        </label>
                        <textarea 
                            id="contact_info"
                            name="contact_info" 
                            class="form-textarea" 
                            placeholder="How should interested parties contact you? (Phone, email, etc.)"
                            rows="3"
                        ></textarea>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-plus"></i>
                        Create Post
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/js/create-post.js"></script>
</body>
</html>
