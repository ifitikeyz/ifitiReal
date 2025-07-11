-- ifiti Real Estate Website Database Schema

-- Agents table (only agents can login)
CREATE TABLE agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    phone VARCHAR(20),
    agency_name VARCHAR(100),
    license_number VARCHAR(50),
    profile_picture VARCHAR(255) DEFAULT 'default-agent.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table (property listings)
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(200) NOT NULL,
    property_type ENUM('apartment', 'house', 'condo', 'studio', 'villa') NOT NULL,
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    area_sqft INT,
    images TEXT, -- JSON array of image filenames
    videos TEXT, -- JSON array of video filenames
    features TEXT, -- JSON array of features
    contact_info TEXT,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 7 DAY),
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    INDEX idx_location (location),
    INDEX idx_price (price),
    INDEX idx_property_type (property_type),
    INDEX idx_expires_at (expires_at)
);

-- Post views/interests (for analytics)
CREATE TABLE post_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
