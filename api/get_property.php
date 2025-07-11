<?php
/**
 * Get Property Details API
 */

header('Content-Type: application/json');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID required']);
    exit();
}

try {
    $db = getDB();
    
    // Get property with agent information
    $stmt = $db->prepare("
        SELECT 
            posts.*,
            agents.username,
            agents.full_name,
            agents.profile_picture,
            agents.agency_name,
            agents.phone,
            DATEDIFF(posts.expires_at, NOW()) as days_remaining
        FROM posts 
        JOIN agents ON posts.agent_id = agents.id
        WHERE posts.id = ? AND posts.expires_at > NOW()
    ");
    $stmt->execute([$post_id]);
    $property = $stmt->fetch();
    
    if (!$property) {
        echo json_encode(['success' => false, 'message' => 'Property not found or expired']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'property' => $property
    ]);
    
} catch (PDOException $e) {
    error_log("Get property error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
