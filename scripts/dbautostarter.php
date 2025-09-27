<?php
// Enhanced Database Auto Starter - Creates all required tables automatically
// This version fixes all foreign key issues and creates a robust database structure

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$db_name = 'stacknro_blog';
$username = 'stacknro_blog';
$password = 'admin-2025';
$charset = 'utf8mb4';

// Root credentials (temporary - set these only for initial setup)
$root_username = 'root';
$root_password = ''; // <-- SET YOUR MYSQL ROOT PASSWORD HERE TEMPORARILY, THEN REMOVE IT

echo "🚀 Starting Enhanced Database Auto Setup...\n";
echo "==========================================\n";

// Enhanced SQL statements for table creation with proper foreign key handling
$allSqlStatements = [
    // Users table (must be created first as it's referenced by other tables)
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
        is_admin TINYINT(1) DEFAULT 0,
        is_banned TINYINT(1) DEFAULT 0,
        email_verified TINYINT(1) DEFAULT 0,
        verification_code VARCHAR(6),
        verification_expires TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_created_at (created_at),
        INDEX idx_is_admin (is_admin),
        INDEX idx_is_banned (is_banned)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Blog posts table
    "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        content LONGTEXT NOT NULL,
        keywords VARCHAR(500),
        featured_image VARCHAR(255),
        author_id INT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_author (author_id),
        INDEX idx_created_at (created_at),
        INDEX idx_slug (slug),
        INDEX idx_views (views),
        FULLTEXT(title, content, keywords),
        CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Post likes table
    "CREATE TABLE IF NOT EXISTS post_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_like (post_id, user_id),
        INDEX idx_post_id (post_id),
        INDEX idx_user_id (user_id),
        CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Post views table (user_id can be NULL for anonymous views)
    "CREATE TABLE IF NOT EXISTS post_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NULL,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post_user_date (post_id, user_id, created_at),
        INDEX idx_post_ip_date (post_id, ip_address, created_at),
        INDEX idx_created_at (created_at),
        CONSTRAINT fk_views_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_views_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Comments table
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post_id (post_id),
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Chat messages table
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        CONSTRAINT fk_chat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Site statistics table
    "CREATE TABLE IF NOT EXISTS site_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE UNIQUE NOT NULL,
        visits INT DEFAULT 0,
        unique_visitors INT DEFAULT 0,
        page_views INT DEFAULT 0,
        INDEX idx_date (date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Contact messages table
    "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // DDoS protection table
    "CREATE TABLE IF NOT EXISTS ddos_bans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL UNIQUE,
        ban_reason VARCHAR(255) DEFAULT 'DDoS Attack',
        banned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ban_expires TIMESTAMP NULL,
        is_permanent TINYINT(1) DEFAULT 0,
        ban_count INT DEFAULT 1,
        INDEX idx_ip (ip_address),
        INDEX idx_expires (ban_expires),
        INDEX idx_permanent (is_permanent)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Request tracking table
    "CREATE TABLE IF NOT EXISTS request_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        request_count INT DEFAULT 1,
        last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        request_method VARCHAR(10) DEFAULT 'GET',
        user_agent TEXT,
        request_uri VARCHAR(255),
        INDEX idx_ip_time (ip_address, last_request),
        INDEX idx_method (request_method)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Email verification table
    "CREATE TABLE IF NOT EXISTS email_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        code VARCHAR(6) NOT NULL,
        type ENUM('registration', 'password_reset') DEFAULT 'registration',
        expires_at TIMESTAMP NOT NULL,
        used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email_code (email, code),
        INDEX idx_expires (expires_at),
        INDEX idx_type (type),
        INDEX idx_used (used)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Security logs table (user_id can be NULL for anonymous events)
    "CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        event_type VARCHAR(50) NOT NULL,
        description TEXT,
        user_agent TEXT,
        user_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, created_at),
        INDEX idx_event_type (event_type),
        INDEX idx_created_at (created_at),
        INDEX idx_user_id (user_id),
        CONSTRAINT fk_security_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Failed login attempts table
    "CREATE TABLE IF NOT EXISTS failed_logins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(100),
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_agent TEXT,
        INDEX idx_ip_time (ip_address, attempt_time),
        INDEX idx_username (username),
        INDEX idx_attempt_time (attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Session security table
    "CREATE TABLE IF NOT EXISTS secure_sessions (
        session_id VARCHAR(128) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent_hash VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity),
        INDEX idx_is_active (is_active),
        CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Insert statements with proper user creation first
$insertStatements = [
    // Create admin user first (this ensures user_id=1 exists)
    "INSERT IGNORE INTO users (id, username, email, password, is_admin, email_verified, profile_image) VALUES 
    (1, 'admin', 'admin@gmail.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 'default-avatar.jpg')",
    
    // Create admin-blog user (this ensures user_id=2 exists)
    "INSERT IGNORE INTO users (id, username, email, password, is_admin, email_verified, profile_image) VALUES 
    (2, 'admin-blog', 'admin-blog@gmail.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 'default-avatar.jpg')",
    
    // Create sample regular user (this ensures user_id=3 exists)
    "INSERT IGNORE INTO users (id, username, email, password, is_admin, email_verified, profile_image) VALUES 
    (3, 'testuser', 'testuser@gmail.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 1, 'default-avatar.jpg')",
    
    // Sample posts (now references existing users)
    "INSERT IGNORE INTO posts (id, title, slug, content, keywords, author_id, status, featured_image, views) VALUES 
    (1, '🚀 Complete Web Development Guide 2025', 'complete-web-development-guide-2025', 
    '<div style=\"text-align: center; margin-bottom: 2rem;\">
        <img src=\"https://images.pexels.com/photos/11035380/pexels-photo-11035380.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1\" alt=\"Web Development\" style=\"width: 100%; max-width: 800px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);\">
    </div>

    <h2 style=\"color: #667eea; margin-bottom: 1.5rem;\">🎯 Introduction</h2>
    <p style=\"font-size: 1.1rem; line-height: 1.8; color: #334155;\">Welcome to the most comprehensive web development guide for 2025! This tutorial will take you from beginner to advanced level, covering all the essential technologies and best practices.</p>

    <h3 style=\"color: #764ba2; margin: 2rem 0 1rem;\">📚 What You Will Learn</h3>
    <ul style=\"font-size: 1.05rem; line-height: 1.7; color: #475569;\">
        <li>Modern HTML5 and CSS3 techniques</li>
        <li>JavaScript ES6+ features and frameworks</li>
        <li>Responsive design principles</li>
        <li>Backend development with PHP/Node.js</li>
        <li>Database design and optimization</li>
    </ul>

    <h3 style=\"color: #764ba2; margin: 2rem 0 1rem;\">💻 Code Example</h3>
    <pre style=\"background: linear-gradient(145deg, #1e293b, #334155); color: #e2e8f0; padding: 24px; border-radius: 12px; overflow-x: auto; margin: 2rem 0; box-shadow: 0 8px 25px rgba(0,0,0,0.2);\"><code>// Modern JavaScript Example
const fetchUserData = async (userId) => {
    try {
        const response = await fetch(`/api/users/\${userId}`);
        const userData = await response.json();
        
        return {
            success: true,
            data: userData
        };
    } catch (error) {
        console.error(\"Error fetching user:\", error);
        return {
            success: false,
            error: error.message
        };
    }
};

// Usage
fetchUserData(123).then(result => {
    if (result.success) {
        console.log(\"User data:\", result.data);
    }
});</code></pre>

    <h3 style=\"color: #764ba2; margin: 2rem 0 1rem;\">🎥 Tutorial Video</h3>
    <div style=\"position: relative; width: 100%; height: 0; padding-bottom: 56.25%; margin: 2rem 0; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.1);\">
        <iframe src=\"https://www.youtube.com/embed/dQw4w9WgXcQ\" 
                style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;\" 
                allowfullscreen></iframe>
    </div>', 
    'web development, programming, javascript, html, css, tutorial, 2025, guide, coding, frontend, backend', 1, 'published', 'default.png', 150)",
    
    // Sample chat messages (references existing users)
    "INSERT IGNORE INTO chat_messages (user_id, message) VALUES 
    (1, 'Welcome to our amazing chat system! 👋'),
    (1, 'Feel free to start conversations here and connect with other users.'),
    (2, 'This chat supports real-time messaging with infinite scroll - try it out!')",
    
    // Sample comments (references existing users and posts)
    "INSERT IGNORE INTO comments (post_id, user_id, content) VALUES 
    (1, 2, 'Great post! Looking forward to more content like this.'),
    (1, 3, 'Very helpful tutorial. Thanks for sharing!')",
    
    // Sample site stats
    "INSERT IGNORE INTO site_stats (date, visits, unique_visitors, page_views) VALUES 
    (CURDATE(), 25, 15, 45),
    (DATE_SUB(CURDATE(), INTERVAL 1 DAY), 30, 20, 60),
    (DATE_SUB(CURDATE(), INTERVAL 2 DAY), 35, 25, 70)",
    
    // Sample contact message
    "INSERT IGNORE INTO contact_messages (name, email, phone, message) VALUES 
    ('John Doe', 'john.doe@gmail.com', '1234567890', 'This is a test contact message from the blog system.')",
    
    // Sample security log (references existing user)
    "INSERT IGNORE INTO security_logs (ip_address, event_type, description, user_id) VALUES 
    ('127.0.0.1', 'LOGIN', 'Admin user logged in successfully', 1),
    ('127.0.0.1', 'INFO', 'System initialized successfully', NULL)",
    
    // Sample failed login attempt
    "INSERT IGNORE INTO failed_logins (ip_address, username, user_agent) VALUES 
    ('192.168.1.100', 'wronguser', 'Mozilla/5.0 (Test Browser)')",
    
    // Sample session (references existing user)
    "INSERT IGNORE INTO secure_sessions (session_id, user_id, ip_address, user_agent_hash) VALUES 
    ('sample_session_123456', 1, '127.0.0.1', 'hash_example_123')"
];

try {
    if (!empty($root_password)) {
        // Connect as root to create database and user if needed
        $root_mysqli = new mysqli($host, $root_username, $root_password);
        
        if ($root_mysqli->connect_error) {
            throw new Exception("Root connection failed: " . $root_mysqli->connect_error);
        }
        echo "✅ Connected as root for initial setup\n";
        
        // Set charset
        if (!$root_mysqli->set_charset($charset)) {
            throw new Exception("Error setting charset for root: " . $root_mysqli->error);
        }
        
        // Create database if not exists
        $root_mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        if ($root_mysqli->error) {
            throw new Exception("Error creating database: " . $root_mysqli->error);
        }
        echo "✅ Database '$db_name' created/verified by root\n";
        
        // Create user if not exists and grant privileges
        $create_user_sql = "CREATE USER IF NOT EXISTS '$username'@'$host' IDENTIFIED BY '$password'";
        $root_mysqli->query($create_user_sql);
        if ($root_mysqli->error) {
            echo "⚠️ Warning: Error creating user (may already exist): " . $root_mysqli->error . "\n";
        } else {
            echo "✅ Created database user '$username'\n";
        }
        
        $grant_sql = "GRANT ALL PRIVILEGES ON `$db_name`.* TO '$username'@'$host'";
        $root_mysqli->query($grant_sql);
        if ($root_mysqli->error) {
            throw new Exception("Error granting privileges: " . $root_mysqli->error);
        }
        echo "✅ Granted privileges to '$username' on '$db_name'\n";
        
        $root_mysqli->query("FLUSH PRIVILEGES");
        echo "✅ Flushed privileges\n";
        
        // Close root connection
        $root_mysqli->close();
        echo "✅ Root setup complete - disconnected\n";
    } else {
        echo "ℹ️ Root password not set - assuming database user already exists\n";
    }
    
    // Connect as the regular user
    $mysqli = new mysqli($host, $username, $password, $db_name);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    echo "✅ Connected to MySQL server as '$username'\n";
    
    // Set charset and SQL mode
    if (!$mysqli->set_charset($charset)) {
        throw new Exception("Error setting charset: " . $mysqli->error);
    }
    
    // Disable foreign key checks temporarily for clean setup
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    
    echo "✅ Using database '$db_name'\n";
    echo "📊 Found " . count($allSqlStatements) . " table creation statements\n";
    echo "==========================================\n";
    
    $successCount = 0;
    $skipCount = 0;
    $errorCount = 0;
    
    // Execute table creation statements
    foreach ($allSqlStatements as $statement) {
        try {
            if ($mysqli->query($statement)) {
                $successCount++;
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "✅ Created/verified table: {$matches[1]}\n";
                }
            } else {
                if (strpos($mysqli->error, 'already exists') !== false) {
                    $skipCount++;
                    if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                        echo "ℹ️  Table already exists: {$matches[1]}\n";
                    }
                } else {
                    $errorCount++;
                    echo "⚠️  Table creation error: " . $mysqli->error . "\n";
                }
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "⚠️  Table creation exception: " . $e->getMessage() . "\n";
        }
    }
    
    // Re-enable foreign key checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "==========================================\n";
    echo "📊 Executing insert statements...\n";
    
    // Execute insert statements
    foreach ($insertStatements as $statement) {
        try {
            if ($mysqli->query($statement)) {
                $successCount++;
                if (preg_match('/INSERT.*?INTO.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "✅ Inserted data into: {$matches[1]}\n";
                }
            } else {
                if (strpos($mysqli->error, 'Duplicate entry') !== false || strpos($mysqli->error, 'duplicate key') !== false) {
                    $skipCount++;
                    echo "ℹ️  Skipped duplicate entry\n";
                } else {
                    $errorCount++;
                    echo "⚠️  Insert error: " . $mysqli->error . "\n";
                }
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "⚠️  Insert exception: " . $e->getMessage() . "\n";
        }
    }
    
    // Create upload directories
    $uploadDirs = [
        '../uploads',
        '../uploads/posts', 
        '../uploads/profiles',
        '../assets'
    ];
    
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            if (@mkdir($dir, 0755, true)) {
                echo "✅ Created directory: $dir\n";
            } else {
                echo "⚠️  Failed to create directory: $dir\n";
            }
        }
    }
    
    // Create default avatar if it doesn't exist
    $defaultAvatarPath = '../assets/default-avatar.jpg';
    if (!file_exists($defaultAvatarPath)) {
        // Create a simple 1x1 transparent image as placeholder
        $defaultImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        @file_put_contents($defaultAvatarPath, $defaultImageData);
        echo "✅ Created default avatar: assets/default-avatar.jpg\n";
    }
    
    // Create default post image if it doesn't exist
    $defaultPostImagePath = '../uploads/posts/default.png';
    if (!file_exists($defaultPostImagePath)) {
        $defaultImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        @file_put_contents($defaultPostImagePath, $defaultImageData);
        echo "✅ Created default post image: uploads/posts/default.png\n";
    }
    
    echo "==========================================\n";
    echo "🎉 Enhanced Database setup completed!\n";
    echo "✅ Successful operations: $successCount\n";
    echo "ℹ️  Skipped (already exists): $skipCount\n";
    echo "⚠️  Errors: $errorCount\n";
    
    // Verify tables were created
    echo "\n🔍 Verifying database structure...\n";
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $tables[] = $row[0];
    }
    
    $expectedTables = [
        'users', 'posts', 'post_likes', 'post_views', 'comments', 
        'chat_messages', 'site_stats', 'contact_messages', 'ddos_bans', 
        'request_tracking', 'email_verifications', 'security_logs', 
        'failed_logins', 'secure_sessions'
    ];
    
    $missingTables = array_diff($expectedTables, $tables);
    
    if (empty($missingTables)) {
        echo "✅ All required tables created successfully!\n";
    } else {
        echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
    }
    
    echo "\n📊 Database Statistics:\n";
    foreach ($tables as $table) {
        try {
            $result = $mysqli->query("SELECT COUNT(*) FROM `$table`");
            if ($result) {
                $count = $result->fetch_row()[0];
                echo "   📋 $table: $count records\n";
            }
        } catch (Exception $e) {
            echo "   ❌ $table: Error reading - " . $e->getMessage() . "\n";
        }
    }
    
    // Test admin users
    echo "\n👤 Checking admin users...\n";
    $result = $mysqli->query("SELECT id, username, email, is_admin FROM users WHERE is_admin = 1");
    if ($result) {
        while ($adminCheck = $result->fetch_assoc()) {
            echo "✅ Admin user found: {$adminCheck['username']} (ID: {$adminCheck['id']}, Email: {$adminCheck['email']})\n";
        }
        echo "🔑 Admin login credentials:\n";
        echo "   - Username: admin-blog | Password: admin2025\n";
        echo "   - Username: admin | Password: password\n";
    }
    
    // Verify foreign key constraints
    echo "\n🔗 Verifying foreign key constraints...\n";
    $fk_check = $mysqli->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_SCHEMA = '$db_name' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($fk_check) {
        $fk_count = 0;
        while ($fk = $fk_check->fetch_assoc()) {
            $fk_count++;
            echo "   🔗 {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
        echo "✅ Total foreign key constraints: $fk_count\n";
    }
    
    echo "\n🎯 Enhanced setup complete! Your blog system is ready to use.\n";
    echo "🌐 You can now access:\n";
    echo "   - Main site: index.php\n";
    echo "   - Admin panel: panel.php (admin-blog / admin2025)\n";
    echo "   - Chat system: chat.php\n";
    echo "   - User registration: auth/register.php\n";
    
    // Close the connection
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
    if (isset($root_mysqli) && $root_mysqli instanceof mysqli) {
        $root_mysqli->close();
    }
    exit(1);
}
?>