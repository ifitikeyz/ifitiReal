<?php
/**
 * Get Post Details API
 * Returns detailed post information for modal display
 */

header('Content-Type: application/json');

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $post_id = (int)($_GET['id'] ?? 0);
    
    if ($post_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
        exit();
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    // Get post details
    $stmt = $db->prepare("
        SELECT 
            p.id,
            p.image_url,
            p.caption,
            p.created_at,
            u.id as user_id,
            u.username,
            u.full_name,
            u.profile_picture,
            COUNT(DISTINCT l.id) as likes_count,
            COUNT(DISTINCT c.id) as comments_count,
            MAX(CASE WHEN l.user_id = ? THEN 1 ELSE 0 END) as user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN likes l ON p.id = l.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        WHERE p.id = ?
        GROUP BY p.id, p.image_url, p.caption, p.created_at, u.id, u.username, u.full_name, u.profile_picture
    ");
    
    $stmt->execute([$user_id, $post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit();
    }
    
    // Get comments
    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.comment_text,
            c.created_at,
            u.username,
            u.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
        LIMIT 50
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();
    
    // Generate HTML
    $html = generatePostModalHTML($post, $comments);
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'post' => $post
    ]);
    
} catch (PDOException $e) {
    error_log("Get post error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log("Get post error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

/**
 * Generate post modal HTML
 */
function generatePostModalHTML($post, $comments) {
    $time_ago = timeAgo($post['created_at']);
    
    $html = '
    <div class="post-modal-container" data-post-id="' . $post['id'] . '">
        <div class="post-modal-image">
            <img src="uploads/posts/' . htmlspecialchars($post['image_url']) . '" 
                 alt="Post by ' . htmlspecialchars($post['username']) . '"
                 ondblclick="toggleLike(' . $post['id'] . ')">
        </div>
        
        <div class="post-modal-sidebar">
            <div class="post-modal-header">
                <img src="uploads/profiles/' . htmlspecialchars($post['profile_picture']) . '" 
                     alt="' . htmlspecialchars($post['username']) . '" 
                     class="post-modal-avatar">
                <div class="post-modal-user-info">
                    <a href="profile.php?username=' . htmlspecialchars($post['username']) . '" class="post-modal-username">
                        ' . htmlspecialchars($post['username']) . '
                    </a>
                </div>
            </div>
            
            <div class="post-modal-content">
                <div class="post-modal-caption">
                    <img src="uploads/profiles/' . htmlspecialchars($post['profile_picture']) . '" 
                         alt="' . htmlspecialchars($post['username']) . '" 
                         class="caption-avatar">
                    <div class="caption-content">
                        <span class="caption-username">' . htmlspecialchars($post['username']) . '</span>
                        ' . nl2br(htmlspecialchars($post['caption'])) . '
                        <div class="caption-time">' . $time_ago . '</div>
                    </div>
                </div>
                
                <div class="post-modal-comments" id="modalComments">';
    
    foreach ($comments as $comment) {
        $comment_time = timeAgo($comment['created_at']);
        $html .= '
                    <div class="modal-comment">
                        <img src="uploads/profiles/' . htmlspecialchars($comment['profile_picture']) . '" 
                             alt="' . htmlspecialchars($comment['username']) . '" 
                             class="comment-avatar">
                        <div class="comment-content">
                            <span class="comment-username">' . htmlspecialchars($comment['username']) . '</span>
                            ' . nl2br(htmlspecialchars($comment['comment_text'])) . '
                            <div class="comment-time">' . $comment_time . '</div>
                        </div>
                    </div>';
    }
    
    $html .= '
                </div>
            </div>
            
            <div class="post-modal-actions">
                <div class="modal-action-buttons">
                    <button class="action-btn like-btn ' . ($post['user_liked'] ? 'liked' : '') . '" 
                            onclick="toggleLike(' . $post['id'] . ')">
                        <i class="' . ($post['user_liked'] ? 'fas' : 'far') . ' fa-heart action-icon"></i>
                    </button>
                    
                    <button class="action-btn" onclick="focusModalComment()">
                        <i class="far fa-comment action-icon"></i>
                    </button>
                    
                    <button class="action-btn">
                        <i class="far fa-paper-plane action-icon"></i>
                    </button>
                </div>
                
                <div class="modal-likes-count">
                    ' . number_format($post['likes_count']) . ' ' . ($post['likes_count'] == 1 ? 'like' : 'likes') . '
                </div>
                
                <div class="modal-time">' . $time_ago . '</div>
            </div>
            
            <form class="modal-comment-form" onsubmit="submitComment(event, ' . $post['id'] . ')">
                <input type="text" 
                       class="modal-comment-input" 
                       placeholder="Add a comment..." 
                       id="modalCommentInput"
                       required>
                <button type="submit" class="modal-comment-submit">Post</button>
            </form>
        </div>
    </div>';
    
    return $html;
}

/**
 * Helper function to format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm';
    if ($time < 86400) return floor($time/3600) . 'h';
    if ($time < 2592000) return floor($time/86400) . 'd';
    if ($time < 31536000) return floor($time/2592000) . 'mo';
    
    return floor($time/31536000) . 'y';
}
?>
