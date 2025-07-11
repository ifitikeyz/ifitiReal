<?php
/**
 * Agent Login Page
 */

require_once 'includes/auth.php';

// Redirect if already logged in
if ($agentAuth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $agentAuth->login($username, $password);
    
    if ($result['success']) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Login - ifiti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <i></i>
                <h1><img src="logo.png" style="height: 50px; box-shadow: 1px 3px 5px black; border-radius: 30%;"></h1>
            </div>
            
            <h2 class="auth-title">Agent Login</h2>
            <p class="auth-subtitle">Access your dashboard</p>
            
            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    Registration successful! Please login with your credentials.
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>
                        Username or Email
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        class="form-input" 
                        required
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        class="form-input" 
                        required
                    >
                </div>
                
                <button type="submit" class="auth-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="pay.html">Register as Agent</a></p>
                <p><a href="index.php">← Back to Posts</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
</body>
</html>
