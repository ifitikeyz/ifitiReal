<?php
/**
 * ifiti Real Estate - Public Feed (No login required)
 * Users can view all property listings without authentication
 */

require_once 'config/database.php';

// Get search parameters
$search_query = $_GET['search'] ?? '';
$location_filter = $_GET['location'] ?? '';
$property_type = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Pagination
$posts_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

try {
    $db = getDB();
    
    // Clean up expired posts
    $stmt = $db->prepare("DELETE FROM posts WHERE expires_at < NOW()");
    $stmt->execute();
    
    // Build search query
    $where_conditions = ["posts.expires_at > NOW()"];
    $params = [];
    
    if (!empty($search_query)) {
        $where_conditions[] = "(posts.title LIKE ? OR posts.description LIKE ? OR posts.location LIKE ? OR agents.full_name LIKE ? OR agents.agency_name LIKE ?)";
        $search_param = "%$search_query%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($location_filter)) {
        $where_conditions[] = "posts.location LIKE ?";
        $params[] = "%$location_filter%";
    }
    
    if (!empty($property_type)) {
        $where_conditions[] = "posts.property_type = ?";
        $params[] = $property_type;
    }
    
    if (!empty($min_price)) {
        $where_conditions[] = "posts.price >= ?";
        $params[] = $min_price;
    }
    
    if (!empty($max_price)) {
        $where_conditions[] = "posts.price <= ?";
        $params[] = $max_price;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get posts with agent information
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
        WHERE $where_clause
        ORDER BY posts.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $posts_per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    
    // Get total count for pagination
    $count_params = array_slice($params, 0, -2); // Remove limit and offset
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM posts 
        JOIN agents ON posts.agent_id = agents.id
        WHERE $where_clause
    ");
    $stmt->execute($count_params);
    $total_posts = $stmt->fetch()['total'];
    $total_pages = ceil($total_posts / $posts_per_page);
    
} catch (PDOException $e) {
    error_log("Feed error: " . $e->getMessage());
    $posts = [];
    $total_pages = 1;
}

/**
 * Helper function to format price
 */
function formatPrice($price) {
    return '$' . number_format($price, 0);
}

/**
 * Helper function to format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ifiti - Find Your Perfect Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header/Navigation -->
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <a href="terms.php">
                   <img src="logo.png" style="height: 50px; box-shadow: 1px 3px 5px black; border-radius: 30%;">
                </a>
            </div>
            
            <div class="search-container">
                <form method="GET" action="index.php" class="search-form">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Search for properties, agents, or locations..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="nav-actions">
                <a href="login.php" class="nav-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Agent Login
                </a>
            </div>
        </nav>
    </header>
          
    <!-- Filters -->
    <div class="filters-container">
        <form method="GET" action="index.php" class="filters-form">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">

    
            
               <a href="terms.php" class="filter-btn"> Terms of service </a>
            

            <div class="filter-group">
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="apartment" <?php echo $property_type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="house" <?php echo $property_type === 'house' ? 'selected' : ''; ?>>House</option>
                    <option value="condo" <?php echo $property_type === 'condo' ? 'selected' : ''; ?>>Condo</option>
                    <option value="studio" <?php echo $property_type === 'studio' ? 'selected' : ''; ?>>Studio</option>
                    <option value="villa" <?php echo $property_type === 'villa' ? 'selected' : ''; ?>>Villa</option>
                </select>
            </div>
            
            <div class="filter-group">
                <input type="text" 
                       name="location" 
                       class="filter-input" 
                       placeholder="Location" 
                       value="<?php echo htmlspecialchars($location_filter); ?>">
            </div>
            
            <div class="filter-group">
                <input type="number" 
                       name="min_price" 
                       class="filter-input" 
                       placeholder="Min Price" 
                       value="<?php echo htmlspecialchars($min_price); ?>">
            </div>
            
            <div class="filter-group">
                <input type="number" 
                       name="max_price" 
                       class="filter-input" 
                       placeholder="Max Price" 
                       value="<?php echo htmlspecialchars($max_price); ?>">
            </div>
            
            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i>
                Filter
            </button>
            
            <a href="index.php" class="clear-filters-btn">
                <i class="fas fa-times"></i>
                Clear
            </a>
            
          
        </form>
    </div>

    <!-- Main content -->
    <main class="main-content">
        <div class="feed-container">
            <?php if (empty($posts)): ?>
                <div class="empty-feed">
                    <i class="fas fa-home" style="font-size: 64px; color: #4a5568; margin-bottom: 20px;"></i>
                    <h2>No Post Found</h2>
                    <p>Try adjusting your search criteria or check back later for new posts.</p>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="property-card" onclick="openPropertyModal(<?php echo $post['id']; ?>)">
                            <div class="property-image-container">
                                <?php 
                                $images = json_decode($post['images'] ?? '[]', true);
                                $first_image = !empty($images) ? $images[0] : 'default-property.jpg';
                                ?>
                                <img src="uploads/properties/<?php echo $first_image; ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                     class="property-image">
                                
                                <div class="property-overlay">
                                    <div class="property-price">
                                    ₦<?php echo number_format($post['price']); ?>
                                        <span class="price-period">/year</span>
                                    </div>
                                    
                                    <?php if ($post['days_remaining'] <= 2): ?>
                                        <div class="expiry-badge">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $post['days_remaining']; ?> days left
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (count($images) > 1): ?>
                                    <div class="image-count">
                                        <i class="fas fa-images"></i>
                                        <?php echo count($images); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="property-content">
                                <div class="property-header">
                                    <h3 class="property-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="property-type"><?php echo ucfirst($post['property_type']); ?></div>
                                </div>
                                
                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($post['location']); ?>
                                </div>
                                
                                <div class="property-details">
                                    <?php if ($post['bedrooms'] > 0): ?>
                                        <span class="detail-item">
                                            <i class="fas fa-bed"></i>
                                            <?php echo $post['bedrooms']; ?> bed
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($post['bathrooms'] > 0): ?>
                                        <span class="detail-item">
                                            <i class="fas fa-bath"></i>
                                            <?php echo $post['bathrooms']; ?> bath
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($post['area_sqft']): ?>
                                        <span class="detail-item">
                                            <i class="fas fa-ruler-combined"></i>
                                            <?php echo number_format($post['area_sqft']); ?> sqft
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="property-description">
                                    <?php echo htmlspecialchars(substr($post['description'], 0, 100)); ?>
                                    <?php if (strlen($post['description']) > 100): ?>...<?php endif; ?>
                                </div>
                                
                                <div class="agent-info">
                                    <img src="uploads/profiles/<?php echo $post['profile_picture']; ?>" 
                                         alt="<?php echo htmlspecialchars($post['full_name']); ?>" 
                                         class="agent-avatar">
                                    <div class="agent-details">
                                        <div class="agent-name"><?php echo htmlspecialchars($post['full_name']); ?></div>
                                        <div class="agent-agency"><?php echo htmlspecialchars($post['agency_name']); ?></div>
                                         <div class="agent-agency"><?php echo htmlspecialchars($post['contact_info']); ?></div>
                                    </div>
                                    <div class="post-time"><?php echo timeAgo($post['created_at']); ?></div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <div class="pagination-info">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">
                                Next
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Property Modal -->
    <div class="modal" id="propertyModal">
        <div class="modal-content property-modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Property Details</h3>
                <button class="modal-close" onclick="closePropertyModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Property details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
