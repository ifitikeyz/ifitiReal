<?php
/**
 * Agent Dashboard - Post Management
 */

require_once 'includes/auth.php';
require_once 'config/database.php';

// Require agent to be logged in
$agentAuth->requireLogin();

$current_agent = $agentAuth->getCurrentAgent();

try {
    $db = getDB();
    
    // Get agent's posts with stats
    $stmt = $db->prepare("
        SELECT 
            posts.*,
            COUNT(DISTINCT pv.id) as view_count,
            DATEDIFF(posts.expires_at, NOW()) as days_remaining
        FROM posts 
        LEFT JOIN post_views pv ON posts.id = pv.post_id
        WHERE posts.agent_id = ?
        GROUP BY posts.id
        ORDER BY posts.created_at DESC
    ");
    $stmt->execute([$current_agent['id']]);
    $agent_posts = $stmt->fetchAll();
    
    // Get dashboard stats
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_posts,
            COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active_posts,
            COUNT(CASE WHEN expires_at <= NOW() THEN 1 END) as expired_posts
        FROM posts 
        WHERE agent_id = ?
    ");
    $stmt->execute([$current_agent['id']]);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $agent_posts = [];
    $stats = ['total_posts' => 0, 'active_posts' => 0, 'expired_posts' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ifiti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="create-post.php" class="nav-link">
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
                        <a href="edit-profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Edit Profile
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
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="welcome-section">
                    <h1>Welcome back, <?php echo htmlspecialchars($current_agent['full_name']); ?>!</h1>
                    <p>Manage your posts and track performance</p>
                </div>
                
                <a href="create-post.php" class="create-post-btn">
                    <i class="fas fa-plus"></i>
                    Create New Post
                </a>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_posts']; ?></div>
                        <div class="stat-label">Total Posts</div>
                    </div>
                </div>
                
                <div class="stat-card active">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['active_posts']; ?></div>
                        <div class="stat-label">Active Posts</div>
                    </div>
                </div>
                
                <div class="stat-card expired">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['expired_posts']; ?></div>
                        <div class="stat-label">Expired Posts</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">
                            <?php 
                            $total_views = array_sum(array_column($agent_posts, 'view_count'));
                            echo $total_views;
                            ?>
                        </div>
                        <div class="stat-label">Total Views</div>
                    </div>
                </div>
            </div>
            
            <!-- Posts Management -->
            <div class="posts-section">
                <div class="section-header">
                    <h2>Your Properties</h2>
                    <div class="section-actions">
                        <select class="filter-select" id="statusFilter" onchange="filterPosts()">
                            <option value="all">All Posts</option>
                            <option value="active">Active Only</option>
                            <option value="expiring">Expiring Soon</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($agent_posts)): ?>
                    <div class="empty-posts">
                        <i class="fas fa-home" style="font-size: 64px; color: var(--text-muted); margin-bottom: 20px;"></i>
                        <h3>No Posts yet</h3>
                        <p>Create your first property Post to get started</p>
                        <a href="create-post.php" class="create-first-post-btn">
                            <i class="fas fa-plus"></i>
                            Create Your First Post
                        </a>
                    </div>
                <?php else: ?>
                    <div class="posts-grid" id="postsGrid">
                        <?php foreach ($agent_posts as $post): ?>
                            <div class="post-card <?php echo $post['days_remaining'] <= 0 ? 'expired' : ($post['days_remaining'] <= 2 ? 'expiring' : 'active'); ?>" 
                                 data-status="<?php echo $post['days_remaining'] <= 0 ? 'expired' : 'active'; ?>">
                                
                                <div class="post-image-container">
                                    <?php 
                                    $images = json_decode($post['images'] ?? '[]', true);
                                    $first_image = !empty($images) ? $images[0] : 'default-property.jpg';
                                    ?>
                                    <img src="uploads/properties/<?php echo $first_image; ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                         class="post-image">
                                    
                                    <div class="post-overlay">
                                        <div class="post-actions">
                                            <button class="action-btn danger" title="Delete"> <a href="delete.php" onclick="return confirm('Are you sure?')"> 
                                                <i class="fas fa-trash"></i></a>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="post-status">
                                        <?php if ($post['days_remaining'] <= 0): ?>
                                            <span class="status-badge expired">Expired</span>
                                        <?php elseif ($post['days_remaining'] <= 2): ?>
                                            <span class="status-badge expiring"><?php echo $post['days_remaining']; ?> days left</span>
                                        <?php else: ?>
                                            <span class="status-badge active">Active</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="post-content">
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="post-price">₦<?php echo number_format($post['price']); ?>/year</div>
                                    <div class="post-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($post['location']); ?>
                                    </div>
                                    
                                    <div class="post-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-eye"></i>
                                            <span><?php echo $post['view_count']; ?> views</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('M j', strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="assets/js/dashboard.js"></script>
      <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Remove active class from all tabs and content
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            event.target.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        }

        // Agent menu toggle
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

        // Profile picture upload
        document.getElementById('pictureInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicture').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
                
                // Auto-submit the form
                document.getElementById('pictureForm').submit();
            }
        });

        // Password confirmation validation
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>
