<?php
/**
 * Track Property View API
 */

header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit();
}

try {
    $db = getDB();
    
    // Check if post exists and is active
    $stmt = $db->prepare("SELECT id FROM posts WHERE id = ? AND expires_at > NOW()");
    $stmt->execute([$post_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Post not found or expired']);
        exit();
    }
    
    // Track the view
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $db->prepare("
        INSERT INTO post_views (post_id, ip_address, user_agent) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$post_id, $ip_address, $user_agent]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Track view error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
