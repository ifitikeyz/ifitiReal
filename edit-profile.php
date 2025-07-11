<?php
session_start();
require_once 'config/database.php';

// Check if agent is logged in
if (!isset($_SESSION['agent_id'])) {
    header('Location: login.php');
    exit();
}

$agent_id = $_SESSION['agent_id'];
$message = '';
$message_type = '';

// Get current agent data
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM agents WHERE id = ?");
    $stmt->execute([$agent_id]);
    $agent = $stmt->fetch();
    
    if (!$agent) {
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    $message = 'Error loading profile data';
    $message_type = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $agency_name = trim($_POST['agency_name'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $website = trim($_POST['website'] ?? '');
    
    // Basic validation
    if (empty($full_name) || empty($username) || empty($email)) {
        $message = 'Full name, username, and email are required';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $message_type = 'error';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $message = 'Username can only contain letters, numbers, and underscores';
        $message_type = 'error';
    } else {
        try {
            // Check if username or email is taken by another agent
            $stmt = $db->prepare("SELECT id FROM agents WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $agent_id]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Username or email is already taken';
                $message_type = 'error';
            } else {
                // Update agent profile
                $stmt = $db->prepare("
                    UPDATE agents 
                    SET full_name = ?, username = ?, email = ?, phone = ?, 
                        agency_name = ?, license_number = ?, bio = ?, location = ?, website = ?
                    WHERE id = ?
                ");
                
                $result = $stmt->execute([
                    $full_name, $username, $email, $phone, 
                    $agency_name, $license_number, $bio, $location, $website, $agent_id
                ]);
                
                if ($result) {
                    // Update session data
                    $_SESSION['agent_username'] = $username;
                    $_SESSION['agent_full_name'] = $full_name;
                    $_SESSION['agent_agency_name'] = $agency_name;
                    
                    $message = 'Profile updated successfully!';
                    $message_type = 'success';
                    
                    // Refresh agent data
                    $stmt = $db->prepare("SELECT * FROM agents WHERE id = ?");
                    $stmt->execute([$agent_id]);
                    $agent = $stmt->fetch();
                } else {
                    $message = 'Failed to update profile';
                    $message_type = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ifiti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-profile-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 0 var(--spacing-lg);
        }
        
        .profile-header {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }
        
        .profile-header h1 {
            color: var(--text-primary);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
        }
        
        .profile-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .edit-form-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
        }
        
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .section-title {
            color: var(--text-primary);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--accent-primary);
            display: inline-block;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .form-label i {
            color: var(--accent-primary);
            width: 16px;
        }
        
        .form-input,
        .form-textarea {
            padding: 12px var(--spacing-md);
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            outline: none;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .char-count {
            font-size: 12px;
            color: var(--text-muted);
            text-align: right;
            margin-top: 4px;
        }
        
        .form-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: flex-end;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-color);
        }
        
        .btn {
            padding: 12px var(--spacing-xl);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: var(--accent-primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: var(--accent-secondary);
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--bg-secondary);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--accent-primary);
            font-weight: 500;
            margin-bottom: var(--spacing-lg);
            transition: color 0.2s ease;
        }
        
        .back-link:hover {
            color: var(--accent-secondary);
        }
        
        @media (max-width: 768px) {
            .edit-profile-container {
                margin-top: 80px;
                padding: 0 var(--spacing-md);
            }
            
            .profile-header,
            .edit-form-container {
                padding: var(--spacing-lg);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-md);
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <i></i>
                <a href="index.php"> <img src="logo.png" style="height: 50px; box-shadow: 1px 3px 5px black; border-radius: 30%;"></a>
            </div>
            
            <div class="nav-actions">
                <a href="dashboard.php" class="nav-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                
                <div class="agent-menu">
                    <img src="uploads/profiles/<?php echo $agent['profile_picture'] ?? 'default-avatar.jpg'; ?>" 
                         alt="Profile" class="nav-profile-pic" onclick="toggleAgentMenu()">
                    
                    <div class="agent-dropdown" id="agentDropdown">
                        <div class="agent-info">
                            <div class="agent-name"><?php echo htmlspecialchars($agent['full_name']); ?></div>
                            <div class="agent-agency"><?php echo htmlspecialchars($agent['agency_name'] ?? 'Independent Agent'); ?></div>
                        </div>

                        
                        <a href="logout.php" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="edit-profile-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Home
        </a>
        
        <div class="profile-header">
            <h1>Edit Profile</h1>
            <p>Update your professional information and contact details</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>" id="message">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="edit-form-container">
            <form method="POST" action="" id="editProfileForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="section-title">Personal Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="fas fa-user"></i>
                                Full Name *
                            </label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['full_name']); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-at"></i>
                                Username *
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['username']); ?>" 
                                   pattern="[a-zA-Z0-9_]+" 
                                   title="Username can only contain letters, numbers, and underscores"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['email']); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i>
                                Phone Number
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['phone'] ?? ''); ?>" 
                                   placeholder="+1 (555) 123-4567">
                        </div>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="bio" class="form-label">
                            <i class="fas fa-info-circle"></i>
                            Bio
                        </label>
                        <textarea id="bio" 
                                  name="bio" 
                                  class="form-textarea" 
                                  maxlength="500" 
                                  placeholder="Tell potential clients about yourself and your expertise..."><?php echo htmlspecialchars($agent['bio'] ?? ''); ?></textarea>
                        <div class="char-count">
                            <span id="bioCount"><?php echo strlen($agent['bio'] ?? ''); ?></span>/500 characters
                        </div>
                    </div>
                </div>
                
                <!-- Professional Information -->
                <div class="form-section">
                    <h3 class="section-title">Professional Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="agency_name" class="form-label">
                                <i class="fas fa-building"></i>
                                Agency Name
                            </label>
                            <input type="text" 
                                   id="agency_name" 
                                   name="agency_name" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['agency_name'] ?? ''); ?>" 
                                   placeholder="Your Real Estate Agency">
                        </div>
                        
                        <div class="form-group">
                            <label for="license_number" class="form-label">
                                <i class="fas fa-id-card"></i>
                                License Number
                            </label>
                            <input type="text" 
                                   id="license_number" 
                                   name="license_number" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['license_number'] ?? ''); ?>" 
                                   placeholder="RE123456789">
                        </div>
                        
                        <div class="form-group">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Location
                            </label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['location'] ?? ''); ?>" 
                                   placeholder="City, State">
                        </div>
                        
                        <div class="form-group">
                            <label for="website" class="form-label">
                                <i class="fas fa-globe"></i>
                                Website
                            </label>
                            <input type="url" 
                                   id="website" 
                                   name="website" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($agent['website'] ?? ''); ?>" 
                                   placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Character counter for bio
        const bioTextarea = document.getElementById('bio');
        const bioCount = document.getElementById('bioCount');
        
        if (bioTextarea && bioCount) {
            bioTextarea.addEventListener('input', function() {
                const length = this.value.length;
                bioCount.textContent = length;
                
                if (length > 450) {
                    bioCount.style.color = 'var(--error-color)';
                } else if (length > 400) {
                    bioCount.style.color = 'var(--warning-color)';
                } else {
                    bioCount.style.color = 'var(--text-muted)';
                }
            });
        }
        
        // Agent dropdown menu
        function toggleAgentMenu() {
            const dropdown = document.getElementById('agentDropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const agentMenu = document.querySelector('.agent-menu');
            const dropdown = document.getElementById('agentDropdown');
            
            if (!agentMenu.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Auto-hide messages
        const message = document.getElementById('message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 300);
            }, 5000);
        }
        
        // Form validation
        const form = document.getElementById('editProfileForm');
        form.addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!fullName || !username || !email) {
                e.preventDefault();
                alert('Please fill in all required fields (Full Name, Username, Email)');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('Username can only contain letters, numbers, and underscores');
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        });
    </script>
</body>
</html>
