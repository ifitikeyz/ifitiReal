<?php
/**
 * Agent Authentication System for ifiti
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

class AgentAuth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function register($username, $email, $password, $full_name, $phone = '', $agency_name = '', $license_number = '') {
        try {
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                return ['success' => false, 'message' => 'All required fields must be filled'];
            }
            
            // Check if username or email already exists
            $stmt = $this->db->prepare("SELECT id FROM agents WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO agents (username, email, password, full_name, phone, agency_name, license_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $agency_name, $license_number]);
            
            return ['success' => true, 'message' => 'Registration successful'];
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, full_name, profile_picture, agency_name
                FROM agents 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$username, $username]);
            $agent = $stmt->fetch();
            
            if ($agent && password_verify($password, $agent['password'])) {
                $_SESSION['agent_id'] = $agent['id'];
                $_SESSION['agent_username'] = $agent['username'];
                $_SESSION['agent_full_name'] = $agent['full_name'];
                $_SESSION['agent_profile_picture'] = $agent['profile_picture'];
                $_SESSION['agent_agency_name'] = $agent['agency_name'];
                
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['agent_id']);
    }
    
    public function getCurrentAgent() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, full_name, bio, phone, agency_name, license_number, profile_picture, created_at
                FROM agents 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['agent_id']]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Get current agent error: " . $e->getMessage());
            return null;
        }
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
}

$agentAuth = new AgentAuth();
?>
