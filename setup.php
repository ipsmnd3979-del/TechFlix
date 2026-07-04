<?php
require_once 'config.php';

if ($conn) {
    // Create content table
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `content` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `type` enum('movie','tv_show','kids') NOT NULL,
        `category_id` int(11) DEFAULT 1,
        `release_year` year(4) DEFAULT NULL,
        `duration` varchar(20) DEFAULT NULL,
        `rating` decimal(3,1) DEFAULT NULL,
        `poster_image` varchar(255) DEFAULT NULL,
        `thumbnail` varchar(255) DEFAULT NULL,
        `trailer_url` varchar(255) DEFAULT NULL,
        `content_url` varchar(255) DEFAULT NULL,
        `status` enum('draft','published','archived') DEFAULT 'published',
        `featured` tinyint(1) DEFAULT 0,
        `tags` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if ($conn->query($createTableSQL)) {
        echo "✅ Content table created successfully!<br>";
        
        // Insert sample data
        $sampleDataSQL = "INSERT INTO `content` (`title`, `description`, `type`, `content_url`, `featured`) VALUES
        ('Big Buck Bunny', 'A large and lovable rabbit deals with three tiny bullies.', 'movie', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 1),
        ('Elephants Dream', 'Friends Proog and Emo journey inside the folds of a seemingly infinite Machine.', 'movie', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 1),
        ('For Bigger Blazes', 'A video showcasing various fire effects and blazes.', 'tv_show', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 0)";
        
        if ($conn->query($sampleDataSQL)) {
            echo "✅ Sample data inserted successfully!<br>";
        } else {
            echo "❌ Error inserting sample data: " . $conn->error . "<br>";
        }
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
} else {
    echo "❌ Cannot connect to database. Check your config.php file.";
}

echo "<br><a href='home.php' class='btn btn-primary'>Go to Home</a>";
?>