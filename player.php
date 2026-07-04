
<?php
require_once 'includes/header.php';

// Get content ID from URL
$content_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check database connection
if (!$conn) {
    die("<div style='padding: 50px; text-align: center;'>
            <h1>Database Error</h1>
            <p>Cannot connect to database. Please check your database configuration.</p>
            <a href='index.php' class='btn btn-primary'>Return to Home</a>
         </div>");
}

// Use prepared statement for security
$content_query = "SELECT c.*, cat.name as category_name
                  FROM content_new c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  WHERE c.id = ?";
                  
$stmt = $conn->prepare($content_query);
if ($stmt) {
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $content_result = $stmt->get_result();
    $content = $content_result->fetch_assoc();
    $stmt->close();
} else {
    $content = null;
}

// If content not found, show helpful error
if (!$content) {
    echo "<div style='padding: 50px; text-align: center;'>
            <h1>Content Not Found</h1>
            <p><strong>Content ID: $content_id</strong> was not found in the database.</p>
            <p>Available content IDs: ";
    
    // Show available content IDs
    $available_query = $conn->query("SELECT id, title FROM content_new ORDER BY id LIMIT 10");
    $available_ids = [];
    while ($available_query && $row = $available_query->fetch_assoc()) {
        $available_ids[] = "<a href='player.php?id={$row['id']}'>{$row['id']} - {$row['title']}</a>";
    }
    echo implode(', ', $available_ids);
    
    echo "</p>
            <a href='home.php' class='btn btn-primary'>Browse Content</a>
            <a href='index.php' class='btn btn-secondary'>Return to Home</a>
          </div>";
    require_once 'includes/footer.php';
    exit();
}

// Define sample videos
$sample_videos = [
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
    'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4'
];

// Initialize variables
$video_url = '';
$is_sample_video = false;
$video_status = '';
$local_file_path = '';

// DEBUG: Check what video data we have
error_log("Content ID $content_id - content_url: " . ($content['content_url'] ?? 'NULL'));

// Get video URL from database
if (!empty($content['content_url'])) {
    $raw_url = trim($content['content_url']);
    $video_status = "Found content_url in database: " . $raw_url;
    
    // Convert local paths to absolute URLs
    if (strpos($raw_url, 'http') === 0) {
        // It's already a full URL
        $video_url = $raw_url;
        $video_status .= " (Full URL)";
    } else {
        // It's a local path, convert to absolute URL
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $project_path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        
        // Handle different path formats - FIXED: Properly handle ../ paths
        if (strpos($raw_url, '../') === 0) {
            // Relative path like ../assets/uploads/videos/file.mp4
            // FIX: Use str_replace to remove only the ../ prefix, not individual characters
            $clean_path = str_replace('../', '', $raw_url);
            $video_url = $base_url . $project_path . '/' . ltrim($clean_path, '/');
            
            // Also calculate local file path for existence check
            $local_file_path = $_SERVER['DOCUMENT_ROOT'] . $project_path . '/' . ltrim($clean_path, '/');
        } elseif (strpos($raw_url, '/') === 0) {
            // Absolute path from root like /assets/uploads/videos/file.mp4
            $video_url = $base_url . $raw_url;
            $local_file_path = $_SERVER['DOCUMENT_ROOT'] . $raw_url;
        } else {
            // Relative path like assets/uploads/videos/file.mp4
            $video_url = $base_url . $project_path . '/' . $raw_url;
            $local_file_path = $_SERVER['DOCUMENT_ROOT'] . $project_path . '/' . $raw_url;
        }
        
        // Clean up any double slashes
        $video_url = str_replace('//', '/', $video_url);
        $video_url = str_replace(':/', '://', $video_url); // Fix protocol
        $local_file_path = str_replace('//', '/', $local_file_path);
        
        $video_status .= " -> Converted to: " . $video_url;
        
        // Check if local file exists
        if (file_exists($local_file_path) && is_readable($local_file_path)) {
            $file_size = filesize($local_file_path);
            $video_status .= " - Local file exists (" . round($file_size / (1024 * 1024), 2) . " MB)";
        } else {
            $video_status .= " - Local file NOT found or not readable: " . $local_file_path;
            $video_url = $sample_videos[array_rand($sample_videos)];
            $is_sample_video = true;
        }
    }
} else {
    $video_status = "No content_url found in database";
}

// If no valid video URL found, use sample video
if (empty($video_url)) {
    $video_url = $sample_videos[array_rand($sample_videos)];
    $is_sample_video = true;
    $video_status .= " - Using sample video as fallback";
} else {
    // Check if it's one of our sample videos
    $is_sample_video = in_array($video_url, $sample_videos);
    if ($is_sample_video) {
        $video_status .= " - Database contains sample video";
    }
}

// Log for debugging
error_log("Player - ID: $content_id, Title: {$content['title']}, URL: $video_url, Status: $video_status");
error_log("Local file path: " . ($local_file_path ?? 'Not calculated'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title']); ?> - TechFlix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            background: #000; 
            color: #fff; 
            font-family: 'Arial', sans-serif; 
            line-height: 1.6; 
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        /* Galaxy Background */
        .galaxy-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(229, 9, 20, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(100, 20, 200, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(31, 55, 86, 0.2) 0%, transparent 50%);
            background-color: #000;
            z-index: -2;
        }
        
        .player-container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px;
            position: relative;
            z-index: 1;
            width: 100%;
        }
        
        .video-section { 
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            border-radius: 20px; 
            overflow: hidden; 
            margin-bottom: 30px; 
            box-shadow: 0 20px 40px rgba(229, 9, 20, 0.3);
            border: 1px solid rgba(229, 9, 20, 0.3);
            position: relative;
            width: 100%;
        }
        
        #mainVideo { 
            width: 100%; 
            height: auto; 
            max-height: 75vh; 
            background: #000; 
            display: block;
            border-radius: 15px;
            aspect-ratio: 16/9;
            object-fit: cover;
        }
        
        .video-controls { 
            position: absolute; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            background: linear-gradient(transparent, rgba(0,0,0,0.9)); 
            padding: 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            gap: 15px; 
            opacity: 0; 
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            flex-wrap: wrap;
        }
        
        .video-section:hover .video-controls,
        .video-section:focus-within .video-controls { 
            opacity: 1; 
            transform: translateY(0);
        }
        
        .control-btn { 
            background: linear-gradient(45deg, #e50914, #ff4757);
            border: none; 
            color: white; 
            padding: 12px 20px; 
            border-radius: 50px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: bold;
            transition: all 0.3s; 
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            min-height: 44px;
        }
        
        .control-btn:hover,
        .control-btn:focus { 
            background: linear-gradient(45deg, #ff4757, #e50914);
            transform: translateY(-2px) scale(1.05); 
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.6);
            outline: none;
        }
        
        .content-info { 
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.9), rgba(42, 42, 42, 0.9));
            border-radius: 20px; 
            padding: 30px; 
            margin-bottom: 20px; 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
        }
        
        .content-title { 
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 20px; 
            color: #fff; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            background: linear-gradient(45deg, #fff, #e50914);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .content-meta { 
            display: flex; 
            gap: 15px; 
            margin-bottom: 25px; 
            flex-wrap: wrap; 
        }
        
        .meta-tag { 
            background: linear-gradient(45deg, #e50914, #ff4757);
            padding: 10px 18px; 
            border-radius: 25px; 
            font-size: 0.9rem; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            gap: 8px;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.3);
            transition: transform 0.3s;
            flex-shrink: 0;
        }
        
        .meta-tag:hover {
            transform: translateY(-2px);
        }
        
        .content-description { 
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            line-height: 1.7; 
            color: #ccc; 
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #e50914;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .action-buttons { 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
        }
        
        .btn { 
            padding: 15px 25px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 1rem; 
            font-weight: bold; 
            transition: all 0.3s ease; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            text-decoration: none;
            min-width: 140px;
            justify-content: center;
            min-height: 54px;
            flex: 1;
            text-align: center;
        }
        
        .btn-primary { 
            background: linear-gradient(45deg, #e50914, #ff4757);
            color: white; 
            box-shadow: 0 6px 20px rgba(229, 9, 20, 0.4);
        }
        
        .btn-primary:hover,
        .btn-primary:focus { 
            background: linear-gradient(45deg, #ff4757, #e50914);
            transform: translateY(-3px) scale(1.05); 
            box-shadow: 0 10px 30px rgba(229, 9, 20, 0.6);
            outline: none;
        }
        
        .btn-secondary { 
            background: rgba(255,255,255,0.1); 
            color: white; 
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover,
        .btn-secondary:focus { 
            background: rgba(255,255,255,0.2); 
            transform: translateY(-3px);
            border-color: #e50914;
            outline: none;
        }
        
        .status-message { 
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.9), rgba(42, 42, 42, 0.9));
            padding: 20px; 
            border-radius: 15px; 
            margin-bottom: 25px; 
            border-left: 5px solid #e50914;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
        }
        
        .status-message i { 
            color: #e50914; 
            margin-right: 15px; 
            font-size: 1.5rem; 
        }
        
        .success-message { 
            border-left: 5px solid #00ff8c;
        }
        
        .success-message i { 
            color: #00ff8c; 
        }
        
        .video-stats { 
            display: flex; 
            gap: 20px; 
            margin: 25px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        
        .video-stats span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
        
        .debug-info { 
            background: rgba(42, 42, 42, 0.9); 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            font-family: 'Courier New', monospace; 
            font-size: 13px; 
            color: #00ff8c; 
            white-space: pre-wrap; 
            display: none;
            border: 1px solid #00ff8c;
            backdrop-filter: blur(10px);
            width: 100%;
            overflow-x: auto;
        }
        
        .related-content {
            margin-top: 40px;
            width: 100%;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .related-card {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            aspect-ratio: 2/3;
        }
        
        .related-card:hover,
        .related-card:focus {
            transform: translateY(-5px) scale(1.02);
            border-color: #e50914;
            outline: none;
        }
        
        .related-card img {
            width: 100%;
            height: 70%;
            object-fit: cover;
        }
        
        .related-card h4 {
            padding: 12px;
            font-size: 0.85rem;
            text-align: center;
            line-height: 1.3;
            height: 30%;
            display: flex;
            align-items: center;
            justify-content: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Mobile-First Responsive Design */
        
        /* Large Desktop */
        @media (min-width: 1200px) {
            .player-container {
                padding: 30px;
            }
            
            .video-controls {
                padding: 25px;
                gap: 20px;
            }
            
            .control-btn {
                padding: 12px 25px;
            }
            
            .content-info {
                padding: 40px;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }
        }
        
        /* Tablets and Small Desktops */
        @media (max-width: 1024px) {
            .player-container {
                padding: 15px;
            }
            
            .video-controls {
                padding: 15px;
                gap: 12px;
            }
            
            .control-btn {
                padding: 10px 18px;
                font-size: 13px;
            }
            
            .content-info {
                padding: 25px;
            }
            
            .btn {
                min-width: 120px;
                padding: 12px 20px;
            }
        }
        
        /* Tablets */
        @media (max-width: 768px) {
            .player-container { 
                padding: 10px; 
            }
            
            .content-title { 
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            
            .content-meta { 
                flex-direction: row; 
                align-items: center; 
                gap: 10px;
                justify-content: flex-start;
            }
            
            .meta-tag {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
            
            .action-buttons { 
                flex-direction: column; 
            }
            
            .btn { 
                width: 100%; 
                min-width: auto;
            }
            
            .video-controls { 
                position: relative; 
                opacity: 1; 
                background: rgba(51, 51, 51, 0.95);
                justify-content: space-around;
                padding: 15px;
                bottom: 0;
            }
            
            .control-btn { 
                padding: 10px 15px; 
                font-size: 12px; 
                flex: 1;
                min-width: 0;
            }
            
            .content-info { 
                padding: 20px; 
            }
            
            .content-description { 
                font-size: 1rem; 
                padding: 15px; 
                margin-bottom: 20px;
            }
            
            .video-stats {
                gap: 15px;
                padding: 15px;
            }
            
            .video-stats span {
                min-width: 100px;
                font-size: 0.8rem;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
            }
        }
        
        /* Mobile Phones */
       /* Mobile Phones */
@media (max-width: 480px) {
    :root {
        --primary: #8a2be2;
        --secondary: #00bfff;
        --dark: #0f0c29;
        --darker: #090617;
        --light: #f5f5f5;
        --card-bg: rgba(25, 20, 60, 0.7);
        --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        --gradient-alt: linear-gradient(45deg, #ff00cc, #3333ff);
    }

    .player-container {
        padding: 8px;
    }
    
    .content-title {
        font-size: 1.5rem;
    }
    
    .content-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .meta-tag {
        width: 100%;
        justify-content: center;
        background: var(--gradient);
        box-shadow: 0 4px 15px rgba(138, 43, 226, 0.4);
    }
    
    /* Updated Video Controls - Row Style */
    .video-controls {
        flex-direction: row;
        gap: 6px;
        padding: 10px;
        background: linear-gradient(transparent, rgba(15, 12, 41, 0.95));
        backdrop-filter: blur(15px);
        border-top: 1px solid rgba(138, 43, 226, 0.3);
        justify-content: space-between;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .control-btn {
        width: auto;
        flex: 1;
        min-width: 60px;
        padding: 10px 8px;
        font-size: 11px;
        justify-content: center;
        background: var(--gradient);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(138, 43, 226, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--light);
        transition: all 0.3s ease;
    }
    
    .control-btn:hover,
    .control-btn:focus {
        background: var(--gradient-alt);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(138, 43, 226, 0.6);
    }
    
    .control-btn i {
        font-size: 14px;
        margin-bottom: 2px;
    }
    
    .btn-text {
        display: block;
        font-size: 9px;
        font-weight: 600;
        margin-top: 2px;
    }
    
    .content-info {
        padding: 15px;
        background: linear-gradient(135deg, rgba(15, 12, 41, 0.9), rgba(25, 20, 60, 0.9));
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .content-description {
        padding: 12px;
        font-size: 0.9rem;
        border-left: 4px solid var(--primary);
        background: rgba(138, 43, 226, 0.1);
    }
    
    /* Updated Action Buttons - Row Style */
    .action-buttons {
        flex-direction: row;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn {
        flex: 1;
        min-width: calc(50% - 4px);
        max-width: calc(50% - 4px);
        padding: 12px 8px;
        font-size: 0.8rem;
        border-radius: 12px;
        min-height: 48px;
        background: var(--gradient);
        box-shadow: 0 4px 15px rgba(138, 43, 226, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .btn-primary {
        background: var(--gradient);
    }
    
    .btn-primary:hover,
    .btn-primary:focus {
        background: var(--gradient-alt);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(138, 43, 226, 0.6);
    }
    
    .btn-secondary {
        background: rgba(138, 43, 226, 0.2);
        border: 1px solid rgba(138, 43, 226, 0.5);
        backdrop-filter: blur(10px);
    }
    
    .btn-secondary:hover,
    .btn-secondary:focus {
        background: rgba(138, 43, 226, 0.3);
        border-color: var(--secondary);
    }
    
    .video-stats {
        flex-direction: row;
        gap: 8px;
        padding: 12px;
        background: rgba(138, 43, 226, 0.1);
        border-radius: 10px;
        border: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .video-stats span {
        min-width: auto;
        justify-content: center;
        font-size: 0.75rem;
        flex: 1;
    }
    
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .related-card {
        background: var(--card-bg);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 10px;
    }
    
    .related-card:hover,
    .related-card:focus {
        border-color: var(--primary);
        transform: translateY(-3px);
    }
    
    .related-card h4 {
        font-size: 0.75rem;
        padding: 8px;
    }
    
    .status-message {
        padding: 12px;
        background: linear-gradient(135deg, rgba(15, 12, 41, 0.9), rgba(25, 20, 60, 0.9));
        border-left: 4px solid var(--primary);
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .success-message {
        border-left: 4px solid var(--secondary);
    }
    
    /* Progress Bar */
    .progress-container {
        height: 4px;
    }
    
    .progress-bar {
        background: var(--gradient);
    }
    
    /* Content Title Gradient */
    .content-title {
        background: linear-gradient(45deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Galaxy Background Update */
    .galaxy-bg {
        background: 
            radial-gradient(circle at 20% 80%, rgba(138, 43, 226, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(0, 191, 255, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(15, 12, 41, 0.3) 0%, transparent 50%);
        background-color: var(--darker);
    }
}

/* Small Mobile Phones - Enhanced for very small screens */
@media (max-width: 360px) {
    .video-controls {
        gap: 4px;
        padding: 8px;
    }
    
    .control-btn {
        min-width: 55px;
        padding: 8px 6px;
        font-size: 10px;
    }
    
    .control-btn i {
        font-size: 12px;
    }
    
    .btn-text {
        font-size: 8px;
    }
    
    .btn {
        min-width: calc(50% - 4px);
        padding: 10px 6px;
        font-size: 0.75rem;
    }
    
    .action-buttons {
        gap: 2px;
    }
}

/* Landscape Mobile Optimization */
@media (max-height: 500px) and (orientation: landscape) and (max-width: 480px) {
    .video-controls {
        position: relative;
        opacity: 1;
        padding: 8px;
    }
    
    .control-btn {
        padding: 6px 8px;
        min-height: 40px;
    }
    
    .btn {
        min-height: 42px;
        padding: 8px 6px;
    }
}
        
        /* Small Mobile Phones */
        @media (max-width: 360px) {
            .player-container {
                padding: 5px;
            }
            
            .content-title {
                font-size: 1.3rem;
            }
            
            .content-info {
                padding: 12px;
            }
            
            .video-controls {
                padding: 10px;
            }
            
            .control-btn {
                padding: 8px 12px;
                font-size: 11px;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                padding: 10px 12px;
                font-size: 0.85rem;
            }
        }
        
        /* Landscape Mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .player-container {
                padding: 10px;
            }
            
            #mainVideo {
                max-height: 60vh;
            }
            
            .video-controls {
                position: relative;
                opacity: 1;
            }
            
            .content-info {
                padding: 15px;
            }
        }
        
        /* High DPI Screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .control-btn,
            .btn {
                border-width: 0.5px;
            }
        }
        
        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            .control-btn:hover,
            .btn:hover {
                transform: none;
            }
        }
        
        /* Loading animation */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
            width: 100%;
        }
        
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #e50914;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Progress bar */
        .progress-container {
            width: 100%;
            height: 5px;
            background: rgba(255, 255, 255, 0.2);
            position: absolute;
            bottom: 0;
            left: 0;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(45deg, #e50914, #ff4757);
            width: 0%;
            transition: width 0.1s ease;
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) {
            .video-controls {
                opacity: 1;
                transform: translateY(0);
            }
            
            .control-btn:hover,
            .btn:hover {
                transform: none;
            }
            
            .meta-tag:hover {
                transform: none;
            }
        }
        
        /* Focus styles for accessibility */
        .control-btn:focus-visible,
        .btn:focus-visible,
        .related-card:focus-visible {
            outline: 2px solid #e50914;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Galaxy Background -->
    <div class="galaxy-bg"></div>
    
    <div class="player-container">
        <!-- Debug info -->
        <div class="debug-info" id="debugInfo">
            <strong>Debug Information:</strong>
            <?php echo "\nContent ID: " . $content_id; ?>
            <?php echo "\nTitle: " . htmlspecialchars($content['title']); ?>
            <?php echo "\nVideo URL: " . $video_url; ?>
            <?php echo "\nLocal Path: " . ($local_file_path ?? 'Not calculated'); ?>
            <?php echo "\nStatus: " . $video_status; ?>
            <?php echo "\nIs Sample: " . ($is_sample_video ? 'Yes' : 'No'); ?>
            <?php echo "\nDatabase Fields:"; ?>
            <?php echo "\n- content_url: " . ($content['content_url'] ?? 'NULL'); ?>
            <?php echo "\n- video_url: " . ($content['video_url'] ?? 'NULL'); ?>
            <?php echo "\n- trailer_url: " . ($content['trailer_url'] ?? 'NULL'); ?>
            <?php echo "\nPath Conversion:"; ?>
            <?php echo "\n- Base URL: " . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"); ?>
            <?php echo "\n- Project Path: " . dirname($_SERVER['PHP_SELF']); ?>
        </div>

        <!-- Status Message -->
        <?php if ($is_sample_video): ?>
        <div class="status-message">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Demo Mode:</strong> Playing sample video. 
                <br><small>The original video for "<?php echo htmlspecialchars($content['title']); ?>" is not available in the database.</small>
                <br><small>Content ID: <?php echo $content_id; ?> | Status: <?php echo $video_status; ?></small>
            </div>
        </div>
        <?php else: ?>
        <!-- <div class="status-message success-message">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Now Playing:</strong> <?php echo htmlspecialchars($content['title']); ?>
                <br><small>Content ID: <?php echo $content_id; ?> | Status: <?php echo $video_status; ?></small>
            </div>
        </div>
        <?php endif; ?> -->
        
        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner"></div>
            <p>Loading video...</p>
        </div>
        
        <!-- Video Section -->
        <div class="video-section">
            <video id="mainVideo" controls autoplay playsinline crossorigin="anonymous" 
                   onerror="handleVideoError(this)" onloadstart="showLoading()" oncanplay="hideLoading()">
                <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            
            <!-- <div class="video-controls">
                <button class="control-btn" onclick="togglePlay()" aria-label="Play/Pause">
                    <i class="fas fa-play" id="playIcon"></i> <span class="btn-text"></span>
                </button>
                <button class="control-btn" onclick="toggleMute()" aria-label="Mute">
                    <i class="fas fa-volume-up" id="volumeIcon"></i> <span class="btn-text"></span>
                </button>
                <button class="control-btn" onclick="skipBackward()" aria-label="Skip Backward 10 seconds">
                    <i class="fas fa-backward"></i> <span class="btn-text"></span>
                </button>
                <button class="control-btn" onclick="skipForward()" aria-label="Skip Forward 10 seconds">
                    <i class="fas fa-forward"></i> <span class="btn-text"></span>
                </button>
                <button class="control-btn" onclick="toggleFullscreen()" aria-label="Toggle Fullscreen">
                    <i class="fas fa-expand"></i> <span class="btn-text"></span>
                </button>
                <button class="control-btn" onclick="toggleDebug()" aria-label="Toggle Debug Information">
                    <i class="fas fa-bug"></i> <span class="btn-text">Debug</span>
                </button> 
            </div> -->
        </div>
        
        <!-- Content Info -->
        <div class="content-info">
            <h1 class="content-title"><?php echo htmlspecialchars($content['title']); ?></h1>
            
            <!-- <div class="content-meta">
                <span class="meta-tag">
                    <i class="fas fa-star"></i> <?php echo $content['rating'] ?? '8.2'; ?>/10
                </span>
                <span class="meta-tag">
                    <i class="fas fa-calendar"></i> <?php echo $content['release_year'] ?? '2023'; ?>
                </span>
                <span class="meta-tag">
                    <i class="fas fa-clock"></i> <?php echo $content['duration'] ?? '120'; ?> min
                </span>
                <span class="meta-tag">
                    <i class="fas fa-tag"></i> <?php echo $content['category_name'] ?? ucfirst($content['type'] ?? 'Movie'); ?>
                </span>
                <span class="meta-tag">
                    <i class="fas fa-film"></i> <?php echo ucfirst($content['type'] ?? 'Movie'); ?>
                </span>
            </div> -->
            
            <p class="content-description">
                <?php echo $content['description'] ?? 'An engaging story that will keep you on the edge of your seat. Watch now for an unforgettable viewing experience.'; ?>
            </p>
            
            <div class="video-stats">
                <span><i class="fas fa-eye"></i> <?php echo rand(1000, 50000); ?> views</span>
                <span><i class="fas fa-heart"></i> <?php echo rand(500, 10000); ?> likes</span>
                <span><i class="fas fa-share-alt"></i> <?php echo rand(100, 2000); ?> shares</span>
                <span><i class="fas fa-comment"></i> <?php echo rand(50, 1000); ?> comments</span>
            </div>
            
            <div class="action-buttons">
                <!-- <button class="btn btn-primary" onclick="togglePlay()">
                    <i class="fas fa-play" id="mainPlayIcon"></i> <span id="mainPlayText">Play</span>
                </button> -->
                <button class="btn btn-secondary" onclick="addToWatchlist(<?php echo $content_id; ?>)">
                    <i class="fas fa-plus"></i> Watchlist
                </button>
                <button class="btn btn-secondary" onclick="toggleFavorite(<?php echo $content_id; ?>)">
                    <i class="far fa-heart" id="favoriteIcon"></i> <span id="favoriteText">Favorite</span>
                </button>
                <!-- <button class="btn btn-secondary" onclick="shareContent()">
                    <i class="fas fa-share"></i> Share
                </button> -->
                <!-- <a href="home.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a> -->
            </div>
        </div>
        
        <!-- Related Content -->
        <div class="related-content">
            <h3 style="margin-bottom: 20px; color: #fff; font-size: clamp(1.2rem, 4vw, 1.5rem);">
                <i class="fas fa-film"></i> Related to Watch
            </h3>
            <div class="related-grid" id="relatedContent">
                <!-- Related content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Video elements
        const video = document.getElementById('mainVideo');
        const playIcon = document.getElementById('playIcon');
        const mainPlayIcon = document.getElementById('mainPlayIcon');
        const mainPlayText = document.getElementById('mainPlayText');
        const volumeIcon = document.getElementById('volumeIcon');
        const favoriteIcon = document.getElementById('favoriteIcon');
        const favoriteText = document.getElementById('favoriteText');
        const debugInfo = document.getElementById('debugInfo');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const progressBar = document.getElementById('progressBar');
        const relatedContent = document.getElementById('relatedContent');
        
        // Sample videos for fallback
        const sampleVideos = [
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4'
        ];
        
        // Debug functions
        function toggleDebug() {
            debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
        }
        
        function showLoading() {
            loadingSpinner.style.display = 'block';
        }
        
        function hideLoading() {
            loadingSpinner.style.display = 'none';
        }
        
        // Video error handling
        function handleVideoError(videoElement) {
            console.error('Video loading failed:', videoElement.error);
            hideLoading();
            
            // Try fallback videos
            const currentSrc = videoElement.src;
            const fallbackVideo = sampleVideos.find(vid => vid !== currentSrc) || sampleVideos[0];
            
            videoElement.src = fallbackVideo;
            videoElement.load();
            
            // Update status message
            const statusMessage = document.querySelector('.status-message');
            if (statusMessage) {
                statusMessage.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Video Error:</strong> Using fallback sample video.
                        <br><small>Original video failed to load. Trying backup source.</small>
                    </div>
                `;
                statusMessage.className = 'status-message';
            }
            
            videoElement.play().catch(err => {
                console.log('Fallback video autoplay prevented:', err);
            });
        }
        
        // Playback control functions
        function updatePlayState() {
            const isPlaying = !video.paused;
            playIcon.className = isPlaying ? 'fas fa-pause' : 'fas fa-play';
            mainPlayIcon.className = isPlaying ? 'fas fa-pause' : 'fas fa-play';
            mainPlayText.textContent = isPlaying ? 'Pause' : 'Play';
        }
        
        function updateProgress() {
            const progress = (video.currentTime / video.duration) * 100;
            progressBar.style.width = progress + '%';
        }
        
        function togglePlay() {
            if (video.paused) {
                video.play().catch(err => {
                    console.log('Play failed:', err);
                    handleVideoError(video);
                });
            } else {
                video.pause();
            }
        }
        
        function toggleMute() {
            video.muted = !video.muted;
            volumeIcon.className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
        }
        
        function skipBackward() {
            video.currentTime = Math.max(0, video.currentTime - 10);
        }
        
        function skipForward() {
            video.currentTime = Math.min(video.duration, video.currentTime + 10);
        }
        
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                video.requestFullscreen().catch(err => {
                    console.log('Fullscreen error:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }
        
        // User interaction functions
        function addToWatchlist(contentId) {
            // Simulate API call
            showNotification('Added to watchlist!', 'success');
            console.log('Added to watchlist:', contentId);
        }
        
        function toggleFavorite(contentId) {
            const isFavorite = favoriteIcon.className.includes('fas');
            if (isFavorite) {
                favoriteIcon.className = 'far fa-heart';
                favoriteText.textContent = 'Favorite';
                showNotification('Removed from favorites', 'info');
            } else {
                favoriteIcon.className = 'fas fa-heart';
                favoriteText.textContent = 'Favorited';
                showNotification('Added to favorites!', 'success');
            }
        }
        
        function shareContent() {
            const shareUrl = window.location.href;
            const title = '<?php echo addslashes($content['title']); ?>';
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Check out this video on TechFlix',
                    url: shareUrl,
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareUrl).then(() => {
                    showNotification('Link copied to clipboard!', 'success');
                });
            }
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#00ff8c' : '#e50914'};
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                z-index: 1000;
                font-weight: bold;
                animation: slideIn 0.3s ease;
                max-width: calc(100vw - 40px);
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Load related content
        function loadRelatedContent() {
            // Simulate loading related content
            const relatedItems = [
                { id: 1, title: 'Action Movie 1', thumbnail: 'https://via.placeholder.com/200x120/e50914/fff?text=Action' },
                { id: 2, title: 'Drama Series', thumbnail: 'https://via.placeholder.com/200x120/00ff8c/000?text=Drama' },
                { id: 3, title: 'Comedy Special', thumbnail: 'https://via.placeholder.com/200x120/ff4757/fff?text=Comedy' },
                { id: 4, title: 'Sci-Fi Adventure', thumbnail: 'https://via.placeholder.com/200x120/3742fa/fff?text=Sci-Fi' }
            ];
            
            relatedContent.innerHTML = relatedItems.map(item => `
                <div class="related-card" onclick="window.location.href='player.php?id=${item.id}'" tabindex="0">
                    <img src="${item.thumbnail}" alt="${item.title}" loading="lazy">
                    <h4>${item.title}</h4>
                </div>
            `).join('');
        }
        
        // Event listeners
        video.addEventListener('play', updatePlayState);
        video.addEventListener('pause', updatePlayState);
        video.addEventListener('volumechange', function() {
            volumeIcon.className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
        });
        video.addEventListener('timeupdate', updateProgress);
        video.addEventListener('loadstart', showLoading);
        video.addEventListener('canplay', hideLoading);
        video.addEventListener('loadeddata', function() {
            console.log('✅ Video loaded successfully');
            hideLoading();
            video.play().catch(err => {
                console.log('Autoplay prevented:', err);
            });
        });
        video.addEventListener('error', function(e) {
            console.error('❌ Video error:', e);
            handleVideoError(video);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            switch(e.key.toLowerCase()) {
                case ' ': case 'k': 
                    e.preventDefault(); 
                    togglePlay(); 
                    break;
                case 'm': 
                    e.preventDefault(); 
                    toggleMute(); 
                    break;
                case 'f': 
                    e.preventDefault(); 
                    toggleFullscreen(); 
                    break;
                case 'arrowleft': 
                    e.preventDefault(); 
                    skipBackward(); 
                    break;
                case 'arrowright': 
                    e.preventDefault(); 
                    skipForward(); 
                    break;
                case 'd': 
                    e.preventDefault(); 
                    toggleDebug(); 
                    break;
            }
        });
        
        // Touch event handling for mobile
        let touchStartX = 0;
        let touchStartY = 0;
        
        video.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        });
        
        video.addEventListener('touchend', function(e) {
            const touchEndX = e.changedTouches[0].screenX;
            const touchEndY = e.changedTouches[0].screenY;
            const diffX = touchEndX - touchStartX;
            const diffY = touchEndY - touchStartY;
            
            // Horizontal swipe for seeking
            if (Math.abs(diffX) > 50 && Math.abs(diffY) < 50) {
                if (diffX > 0) {
                    skipForward();
                } else {
                    skipBackward();
                }
            }
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎬 Video Player Ready');
            console.log('📹 Video Source:', video.src);
            console.log('🎭 Content:', '<?php echo $content['title']; ?>');
            console.log('🆔 Content ID:', <?php echo $content_id; ?>);
            console.log('🔧 Is Sample Video:', <?php echo $is_sample_video ? 'true' : 'false'; ?>);
            
            // Hide button text on very small screens
            const updateButtonText = () => {
                const btnTexts = document.querySelectorAll('.btn-text');
                if (window.innerWidth <= 360) {
                    btnTexts.forEach(span => span.style.display = 'none');
                } else {
                    btnTexts.forEach(span => span.style.display = 'inline');
                }
            };
            
            updateButtonText();
            window.addEventListener('resize', updateButtonText);
            
            loadRelatedContent();
        });
    </script>
    
    <style>
        /* Animation for notifications */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        /* Print styles */
        @media print {
            .galaxy-bg,
            .video-controls,
            .action-buttons,
            .video-stats {
                display: none !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .content-info {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
            }
        }
    </style>
</body>
</html>

<?php  require_once 'includes/footer.php';  ?>