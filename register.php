<?php
/**
 * Agent Registration Page
 */

require_once 'includes/auth.php';

// Redirect if already logged in
if ($agentAuth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
$form_data = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'agency_name' => trim($_POST['agency_name'] ?? ''),
        'license_number' => trim($_POST['license_number'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];
    
    // Server-side validation
    if (empty($form_data['username']) || empty($form_data['email']) || 
        empty($form_data['full_name']) || empty($form_data['password'])) {
        $error_message = 'All required fields must be filled';
    } elseif ($form_data['password'] !== $form_data['confirm_password']) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($form_data['password']) < 8) {
        $error_message = 'Password must be at least 8 characters long';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $form_data['username'])) {
        $error_message = 'Username can only contain letters, numbers, and underscores';
    } else {
        $result = $agentAuth->register(
            $form_data['username'],
            $form_data['email'],
            $form_data['password'],
            $form_data['full_name'],
            $form_data['phone'],
            $form_data['agency_name'],
            $form_data['license_number']
        );
        
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Registration - ifiti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box register-box">
            <div class="auth-logo">
                <i></i>
                <h1><img src="logo.png" style="height: 50px; box-shadow: 1px 3px 5px black; border-radius: 30%;"></h1>
            </div>
            
            <h2 class="auth-title">Agent Registration</h2>
            <p class="auth-subtitle">Join our platform to Post your properties</p>
            
            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-user"></i>
                            Full Name *
                        </label>
                        <input 
                            type="text" 
                            id="full_name"
                            name="full_name" 
                            class="form-input" 
                            required
                            value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-at"></i>
                            Username *
                        </label>
                        <input 
                            type="text" 
                            id="username"
                            name="username" 
                            class="form-input" 
                            required
                            pattern="[a-zA-Z0-9_]+"
                            title="Username can only contain letters, numbers, and underscores"
                            value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address *
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        class="form-input" 
                        required
                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i>
                            Phone Number
                        </label>
                        <input 
                            type="tel" 
                            id="phone"
                            name="phone" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="license_number" class="form-label">
                            <i class="fas fa-id-card"></i>
                            License Number
                        </label>
                        <input 
                            type="text" 
                            id="license_number"
                            name="license_number" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($form_data['license_number'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="agency_name" class="form-label">
                        <i class="fas fa-building"></i>
                        Agency Name
                    </label>
                    <input 
                        type="text" 
                        id="agency_name"
                        name="agency_name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($form_data['agency_name'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password *
                        </label>
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="form-input" 
                            required
                            minlength="8"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-check-double"></i>
                            Confirm Password *
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password"
                            name="confirm_password" 
                            class="form-input" 
                            required
                            minlength="8"
                        >
                    </div>
                </div>
                
                <button type="submit" class="auth-button" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    Register as Agent
                </button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login</a></p>
                <p><a href="index.php">← Back to Properties</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>
