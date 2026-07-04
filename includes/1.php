<?php
// TechFlix OTT Platform Setup Script
// This script will automatically create the database and all necessary tables

// Disable error reporting for clean output
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlix - Setup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-container {
            background: rgba(25, 20, 60, 0.9);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-header h1 {
            background: linear-gradient(to right, #fff, #d9e3f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .setup-header p {
            color: #bbb;
            font-size: 1.1rem;
        }

        .setup-content {
            margin-bottom: 30px;
        }

        .step {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary, #8a2be2);
        }

        .step h3 {
            color: var(--primary, #8a2be2);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status {
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-weight: 500;
        }

        .status.success {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            border: 1px solid rgba(46, 213, 115, 0.3);
        }

        .status.error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .status.info {
            background: rgba(0, 191, 255, 0.1);
            color: #00bfff;
            border: 1px solid rgba(0, 191, 255, 0.3);
        }

        .setup-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn {
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary, #8a2be2);
            color: var(--light, #f5f5f5);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .credentials {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-label {
            color: #bbb;
        }

        .credential-value {
            color: white;
            font-weight: 600;
        }

        .warning {
            background: rgba(255, 165, 0, 0.1);
            color: #ffa502;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 165, 0, 0.3);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-rocket"></i> TechFlix Setup</h1>
            <p>Welcome to TechFlix OTT Platform Setup Wizard</p>
        </div>

        <div class="setup-content">
            <?php
            // Database configuration for XAMPP
            $host = 'localhost';
            $username = 'root';
            $password = '';
            $database = 'ott_platform';

            $success = true;
            $messages = [];

            // Step 1: Connect to MySQL
            echo '<div class="step">';
            echo '<h3><i class="fas fa-database"></i> Step 1: Database Connection</h3>';
            
            try {
                $conn = new mysqli($host, $username, $password);
                
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                
                echo '<div class="status success">✓ Connected to MySQL server successfully</div>';
                $messages[] = "Connected to MySQL server";
                
            } catch (Exception $e) {
                echo '<div class="status error">✗ Failed to connect to MySQL: ' . $e->getMessage() . '</div>';
                echo '<div class="status info">Make sure MySQL is running in XAMPP and using default credentials (username: root, password: empty)</div>';
                $success = false;
            }
            echo '</div>';

            if ($success) {
                // Step 2: Create Database
                echo '<div class="step">';
                echo '<h3><i class="fas fa-plus-circle"></i> Step 2: Create Database</h3>';
                
                $sql = "CREATE DATABASE IF NOT EXISTS $database";
                if ($conn->query($sql) === TRUE) {
                    echo '<div class="status success">✓ Database "' . $database . '" created successfully</div>';
                    $messages[] = "Database created";
                } else {
                    echo '<div class="status error">✗ Error creating database: ' . $conn->error . '</div>';
                    $success = false;
                }
                echo '</div>';
            }

            if ($success) {
                // Select database
                $conn->select_db($database);

                // Step 3: Create Tables
                echo '<div class="step">';
                echo '<h3><i class="fas fa-table"></i> Step 3: Create Tables</h3>';
                
                $tables_sql = [
                    "users" => "CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        email VARCHAR(100) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        profile_picture VARCHAR(255),
                        first_name VARCHAR(50),
                        last_name VARCHAR(50),
                        role ENUM('user', 'admin', 'superadmin') DEFAULT 'user',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        last_login TIMESTAMP NULL
                    )",
                    
                    "categories" => "CREATE TABLE IF NOT EXISTS categories (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(50) NOT NULL,
                        description TEXT
                    )",
                    
                    "content" => "CREATE TABLE IF NOT EXISTS content (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        description TEXT,
                        type ENUM('movie', 'tv_show', 'kids') NOT NULL,
                        category_id INT,
                        release_year YEAR,
                        duration VARCHAR(20),
                        rating DECIMAL(3,1),
                        poster_image VARCHAR(255),
                        thumbnail VARCHAR(255),
                        trailer_url VARCHAR(255),
                        content_url VARCHAR(255),
                        status ENUM('draft', 'published', 'archived') DEFAULT 'published',
                        featured BOOLEAN DEFAULT FALSE,
                        tags TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )",
                    
                    "watchlist" => "CREATE TABLE IF NOT EXISTS watchlist (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        content_id INT NOT NULL,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_watchlist (user_id, content_id)
                    )",
                    
                    "media_files" => "CREATE TABLE IF NOT EXISTS media_files (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        filename VARCHAR(255) NOT NULL,
                        filepath VARCHAR(500) NOT NULL,
                        filetype VARCHAR(100) NOT NULL,
                        filesize INT NOT NULL,
                        uploaded_by INT NOT NULL,
                        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )"
                ];

                foreach ($tables_sql as $table_name => $sql) {
                    if ($conn->query($sql) === TRUE) {
                        echo '<div class="status success">✓ Table "' . $table_name . '" created successfully</div>';
                        $messages[] = "Table $table_name created";
                    } else {
                        echo '<div class="status error">✗ Error creating table ' . $table_name . ': ' . $conn->error . '</div>';
                        $success = false;
                    }
                }
                echo '</div>';
            }

            if ($success) {
                // Step 4: Insert Default Data
                echo '<div class="step">';
                echo '<h3><i class="fas fa-database"></i> Step 4: Insert Default Data</h3>';
                
                // Insert default categories
                $categories = [
                    'Action' => 'High-energy content with exciting sequences',
                    'Sci-Fi' => 'Futuristic and scientific fiction content',
                    'Adventure' => 'Exciting journeys and explorations',
                    'Comedy' => 'Funny and entertaining content',
                    'Drama' => 'Emotional and character-driven stories',
                    'Fantasy' => 'Magical and imaginative worlds',
                    'Horror' => 'Scary and suspenseful content',
                    'Romance' => 'Love stories and relationships',
                    'Documentary' => 'Real-life stories and facts',
                    'Animation' => 'Animated content for all ages'
                ];

                $categories_count = 0;
                foreach ($categories as $name => $description) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $description);
                    if ($stmt->execute()) {
                        $categories_count++;
                    }
                    $stmt->close();
                }
                echo '<div class="status success">✓ ' . $categories_count . ' categories added</div>';
                $messages[] = "$categories_count categories added";

                // Create admin user
                $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", 'admin', 'admin@techflix.com', $admin_password, 'System', 'Administrator', 'superadmin');
                if ($stmt->execute()) {
                    echo '<div class="status success">✓ Admin user created</div>';
                    $messages[] = "Admin user created";
                } else {
                    echo '<div class="status info">ℹ Admin user already exists</div>';
                }
                $stmt->close();

                // Add sample content
                $sample_content = [
                    ['Galactic Odyssey', 'Join the crew of the starship Odyssey as they explore uncharted galaxies and encounter alien civilizations in this epic space adventure.', 'movie', 2, 2024, 148, 8.7],
                    ['Cosmic Harmony', 'A musical journey through the cosmos where sound and light create harmonious patterns across the universe.', 'movie', 2, 2023, 132, 8.2],
                    ['Nebula Dreams', 'A scientist discovers she can enter other people\'s dreams through a mysterious nebula phenomenon.', 'tv_show', 2, 2024, 45, 8.9],
                    ['Starfall Serenade', 'An interstellar love story set against the backdrop of a dying star system.', 'movie', 8, 2024, 156, 8.5],
                    ['Quantum Beats', 'A DJ discovers he can manipulate reality through music using quantum physics.', 'tv_show', 2, 2023, 42, 8.1]
                ];

                $content_count = 0;
                foreach ($sample_content as $content) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO content (title, description, type, category_id, release_year, duration, rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssiiid", $content[0], $content[1], $content[2], $content[3], $content[4], $content[5], $content[6]);
                    if ($stmt->execute()) {
                        $content_count++;
                    }
                    $stmt->close();
                }
                echo '<div class="status success">✓ ' . $content_count . ' sample content items added</div>';
                $messages[] = "$content_count sample content items added";

                echo '</div>';
            }

            if ($success) {
                // Step 5: Create Assets Directory
                echo '<div class="step">';
                echo '<h3><i class="fas fa-folder"></i> Step 5: Create Directories</h3>';
                
                $directories = [
                    'assets/uploads/posters',
                    'assets/uploads/thumbnails',
                    'assets/uploads/videos',
                    'assets/uploads/images',
                    'assets/uploads/profiles',
                    'assets/css',
                    'assets/js',
                    'assets/img',
                    'admin/includes'
                ];

                $dirs_created = 0;
                foreach ($directories as $dir) {
                    if (!is_dir($dir)) {
                        if (mkdir($dir, 0755, true)) {
                            echo '<div class="status success">✓ Directory created: ' . $dir . '</div>';
                            $dirs_created++;
                        } else {
                            echo '<div class="status error">✗ Failed to create directory: ' . $dir . '</div>';
                        }
                    } else {
                        echo '<div class="status info">ℹ Directory already exists: ' . $dir . '</div>';
                    }
                }
                $messages[] = "$dirs_created directories created";
                echo '</div>';
            }

            // Close connection
            if (isset($conn)) {
                $conn->close();
            }
            ?>
        </div>

        <div class="setup-footer">
            <?php if ($success): ?>
                <div class="status success" style="text-align: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-check-circle"></i> Setup Completed Successfully!</h3>
                </div>
                
                <div class="credentials">
                    <h4 style="color: var(--primary); margin-bottom: 15px;">Default Admin Credentials:</h4>
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value">admin</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value">admin123</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Email:</span>
                        <span class="credential-value">admin@techflix.com</span>
                    </div>
                </div>

                <div class="warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Change the default admin password after your first login!
                </div>

                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Homepage
                    </a>
                    <a href="admin/index.php" class="btn btn-outline">
                        <i class="fas fa-crown"></i> Access Admin Panel
                    </a>
                </div>

            <?php else: ?>
                <div class="status error" style="text-align: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-exclamation-triangle"></i> Setup Failed</h3>
                    <p>Please check your MySQL configuration and try again.</p>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <button onclick="window.location.reload()" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                    <a href="https://www.apachefriends.org/" target="_blank" class="btn btn-outline">
                        <i class="fas fa-download"></i> Download XAMPP
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom to show latest status
        window.scrollTo(0, document.body.scrollHeight);
    </script>
</body>
</html>
______________________________________________________________________________________________________________________________
<!-- Trending Now Section -->
<!-- <section class="section">
    <h2 class="section-title">Trending Now</h2>
    <div class="trending-grid">
        <!-- <?php 
        $trending_counter = 0;
        while($content = $trending_result->fetch_assoc()): 
            $trending_counter++;
            if ($trending_counter > 10) break;
        ?> -->
        <div class="trending-card" data-content-id="<?php echo $content['id']; ?>">
            <img src="<?php echo $content['poster_image'] ?: './assets/img/default-poster.jpg'; ?>" alt="<?php echo $content['title']; ?>">
            <div class="card-overlay">
                <h3><?php echo $content['title']; ?></h3>
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <span><?php echo $content['rating']; ?>/10</span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <!-- Default trending cards if no content -->
        <?php if ($trending_counter == 0): ?>
        <div class="trending-card">
            <div style="background: linear-gradient(45deg, #ff416c, #ff4b2b); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                Movie 1
            </div>
            <div class="card-overlay">
                <h3>Sample Movie</h3>
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <span>8.7/10</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section> -->
