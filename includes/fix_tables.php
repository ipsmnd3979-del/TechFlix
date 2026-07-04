<?php
require_once 'config.php';

$tables = [
    'viewing_history' => "CREATE TABLE IF NOT EXISTS viewing_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content_id INT NOT NULL,
        watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        duration_watched INT DEFAULT 0,
        progress INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
    )",
    
    'watchlist' => "CREATE TABLE IF NOT EXISTS watchlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
        UNIQUE KEY unique_watchlist (user_id, content_id)
    )",
    
    'seasons' => "CREATE TABLE IF NOT EXISTS seasons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        season_number INT NOT NULL,
        title VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
    )",
    
    'episodes' => "CREATE TABLE IF NOT EXISTS episodes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        season_id INT NOT NULL,
        episode_number INT NOT NULL,
        title VARCHAR(255),
        description TEXT,
        episode_url VARCHAR(500),
        duration VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $table_name => $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Table '$table_name' created or already exists.<br>";
        }
    } catch (Exception $e) {
        echo "Error with table '$table_name': " . $e->getMessage() . "<br>";
    }
}

echo "Table creation completed.";
?>