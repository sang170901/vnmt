<?php
/**
 * Initialize the MySQL database schema and seed admin user.
 * This file provides init_db(PDO $pdo, array $config) and does NOT echo by default.
 */
function init_db(PDO $pdo, array $config){
    // MySQL doesn't need PRAGMA, skip this line
    // Foreign keys are enabled by default in MySQL with InnoDB

    // Create tables with MySQL syntax
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        password VARCHAR(255),
        role VARCHAR(50) DEFAULT 'user',
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Check if suppliers table exists before creating it
    $supplierExists = $pdo->query("SHOW TABLES LIKE 'suppliers'")->fetch();
    if (!$supplierExists) {
        $pdo->exec("CREATE TABLE suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            slug VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(20),
            address TEXT,
            logo VARCHAR(255),
            description TEXT,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        slug VARCHAR(255),
        description TEXT,
        price DECIMAL(10,2),
        status TINYINT(1) DEFAULT 1,
        featured TINYINT(1) DEFAULT 0,
        images TEXT,
        supplier_id INT,
        classification VARCHAR(255), -- New column for product classification
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(100),
        discount_type VARCHAR(50),
        discount_value DECIMAL(10,2),
        min_purchase DECIMAL(10,2),
        max_uses INT,
        used_count INT DEFAULT 0,
        start_date DATETIME,
        end_date DATETIME,
        supplier_id INT,
        status TINYINT(1) DEFAULT 1,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sliders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        description TEXT,
        image VARCHAR(500),
        link VARCHAR(500),
        link_text VARCHAR(100),
        start_date DATE,
        end_date DATE,
        status TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS scheduled_publishings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        model_type VARCHAR(100),
        model_id INT,
        publish_at DATETIME,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255),
        model_type VARCHAR(100),
        model_id INT,
        changes TEXT,
        ip VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS partners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        image_path VARCHAR(500),
        status TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content LONGTEXT,
        featured_image VARCHAR(500),
        author_id INT DEFAULT NULL,
        category VARCHAR(100),
        tags VARCHAR(255),
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        views INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published_at DATETIME DEFAULT NULL,
        INDEX idx_status (status),
        INDEX idx_category (category),
        INDEX idx_created_at (created_at),
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Seed admin user - Use INSERT IGNORE for MySQL
    $hash = password_hash($config['admin_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 1)");
    $stmt->execute(['Admin', $config['admin_email'], $hash]);

    // Update existing products to include classifications (if products exist)
    $pdo->exec("UPDATE products SET classification = 'Vật liệu' WHERE id IN (1, 2) AND classification IS NULL");
    $pdo->exec("UPDATE products SET classification = 'Thiết Bị' WHERE id = 3");
    $pdo->exec("UPDATE products SET classification = 'Công nghệ' WHERE id = 4");
    $pdo->exec("UPDATE products SET classification = 'Cảnh quan' WHERE id = 5");
}
