<?php
session_start();
require_once '../includes/config.php';



// Define required functions if they don't exist
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_null($data)) return '';
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

if (!function_exists('upload_thumbnail')) {
    function upload_thumbnail($file) {
        $upload_dir = '../assets/uploads/thumbnails/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'thumbnail_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image.');
        }
        
        // Check file size (5MB max)
        if ($file['size'] > 5000000) {
            throw new Exception('File is too large. Maximum size is 5MB.');
        }
        
        // Allow certain file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed.');
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $upload_path;
        } else {
            throw new Exception('Sorry, there was an error uploading your file.');
        }
    }
}

if (!function_exists('upload_video')) {
    function upload_video($file) {
        $upload_dir = '../assets/uploads/videos/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'video_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Check file size (500MB max)
        if ($file['size'] > 500000000) {
            throw new Exception('File is too large. Maximum size is 500MB.');
        }
        
        // Allow certain file formats
        $allowed_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            throw new Exception('Only MP4, AVI, MOV, WMV, FLV, and WebM files are allowed.');
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $upload_path;
        } else {
            throw new Exception('Sorry, there was an error uploading your video file.');
        }
    }
}

if (!function_exists('upload_banner_image')) {
    function upload_banner_image($file) {
        $upload_dir = '../assets/uploads/banners/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'banner_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image.');
        }
        
        // Check file size (10MB max for banners)
        if ($file['size'] > 10000000) {
            throw new Exception('File is too large. Maximum size is 10MB.');
        }
        
        // Allow certain file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            throw new Exception('Only JPG, JPEG, PNG, GIF & WebP files are allowed.');
        }
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $upload_path;
        } else {
            throw new Exception('Sorry, there was an error uploading your banner image.');
        }
    }
}

// Check admin access (commented out for testing)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    // For testing - allow access without login
    // header("Location: login.php");
    // exit();
}

// Check and create banners table if it doesn't exist
function checkAndCreateBannersTable($conn) {
    $check_table = $conn->query("SHOW TABLES LIKE 'banners'");
    if ($check_table->num_rows == 0) {
        // Create banners table
        $create_table_sql = "CREATE TABLE banners (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(500),
            button_text VARCHAR(100),
            button_link VARCHAR(500),
            image VARCHAR(500) NOT NULL,
            type VARCHAR(50) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($create_table_sql)) {
            return true;
        } else {
            throw new Exception("Failed to create banners table: " . $conn->error);
        }
    }
    return true;
}

// Get table structure for banners
function getBannersTableColumns($conn) {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM banners");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

// Initialize banners table
try {
    checkAndCreateBannersTable($conn);
    $banners_columns = getBannersTableColumns($conn);
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}// --- ADD THIS BLOCK AROUND LINE 170 ---
// Initialize $banners_result as an empty result to avoid undefined variable warnings
$banners_result = $conn->query("SELECT * FROM banners ORDER BY created_at DESC");

// Fallback: If the query fails, ensure it's at least an empty object 
// to prevent "fetch_assoc on bool" errors later
if (!$banners_result) {
    $banners_result = $conn->query("SELECT 1 FROM banners WHERE 1=0"); // Dummy empty result
}

// Handle content management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_content'])) {
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $type = sanitize_input($_POST['type']);
        $category = sanitize_input($_POST['category']);
        $release_year = intval($_POST['release_year']);
        $duration = intval($_POST['duration']);
        $rating = floatval($_POST['rating']);
        
        // Handle thumbnail upload
        $thumbnail_path = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
            try {
                $thumbnail_path = upload_thumbnail($_FILES['thumbnail']);
            } catch (Exception $e) {
                $error_message = "Thumbnail upload failed: " . $e->getMessage();
            }
        }
        
        // Handle video upload
        $video_path = '';
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
            try {
                $video_path = upload_video($_FILES['video_file']);
            } catch (Exception $e) {
                $error_message = "Video upload failed: " . $e->getMessage();
            }
        }
        
        if (!isset($error_message)) {
            // First, let's check what columns actually exist
            $check_columns = $conn->query("SHOW COLUMNS FROM content");
            $columns = [];
            while ($col = $check_columns->fetch_assoc()) {
                $columns[] = $col['Field'];
            }
            
            // Build dynamic query based on available columns
            $available_columns = [];
            $placeholders = [];
            $values = [];
            $types = '';
            
            // Always include basic columns
            $available_columns[] = 'title';
            $placeholders[] = '?';
            $values[] = $title;
            $types .= 's';
            
            $available_columns[] = 'description';
            $placeholders[] = '?';
            $values[] = $description;
            $types .= 's';
            
            $available_columns[] = 'type';
            $placeholders[] = '?';
            $values[] = $type;
            $types .= 's';
            
            // Add optional columns if they exist
            if (in_array('category', $columns)) {
                $available_columns[] = 'category';
                $placeholders[] = '?';
                $values[] = $category;
                $types .= 's';
            }
            
            if (in_array('release_year', $columns)) {
                $available_columns[] = 'release_year';
                $placeholders[] = '?';
                $values[] = $release_year;
                $types .= 'i';
            }
            
            if (in_array('duration', $columns)) {
                $available_columns[] = 'duration';
                $placeholders[] = '?';
                $values[] = $duration;
                $types .= 'i';
            }
            
            if (in_array('rating', $columns)) {
                $available_columns[] = 'rating';
                $placeholders[] = '?';
                $values[] = $rating;
                $types .= 'd';
            }
            
            if (in_array('thumbnail', $columns) && $thumbnail_path) {
                $available_columns[] = 'thumbnail';
                $placeholders[] = '?';
                $values[] = $thumbnail_path;
                $types .= 's';
            }
            
            if (in_array('video_path', $columns) && $video_path) {
                $available_columns[] = 'video_path';
                $placeholders[] = '?';
                $values[] = $video_path;
                $types .= 's';
            }
            
            $sql = "INSERT INTO content (" . implode(', ', $available_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param($types, ...$values);
                
                if ($stmt->execute()) {
                    $success_message = "Content added successfully!";
                } else {
                    $error_message = "Failed to add content: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Failed to prepare statement: " . $conn->error;
            }
        }
    }
    
    // Handle content deletion
    if (isset($_POST['delete_content'])) {
        $content_id = intval($_POST['content_id']);
        
        $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
        $stmt->bind_param("i", $content_id);
        
        if ($stmt->execute()) {
            $success_message = "Content deleted successfully!";
        } else {
            $error_message = "Failed to delete content: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Handle banner management
    if (isset($_POST['add_banner'])) {
        $banner_title = sanitize_input($_POST['banner_title']);
        $banner_subtitle = sanitize_input($_POST['banner_subtitle'] ?? '');
        $banner_button_text = sanitize_input($_POST['banner_button_text'] ?? '');
        $banner_button_link = sanitize_input($_POST['banner_button_link'] ?? '');
        $banner_type = sanitize_input($_POST['banner_type']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle banner image upload
        $banner_image = '';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            try {
                $banner_image = upload_banner_image($_FILES['banner_image']);
            } catch (Exception $e) {
                $error_message = "Banner image upload failed: " . $e->getMessage();
            }
        }
        
        if (!isset($error_message) && !empty($banner_image)) {
            // Build dynamic query based on available columns
            $available_columns = ['title', 'type', 'is_active'];
            $placeholders = ['?', '?', '?'];
            $values = [$banner_title, $banner_type, $is_active];
            $types = 'ssi';
            
            // Add image column - check what the actual column name is
            $image_column = 'image'; // default
            if (in_array('image', $banners_columns)) {
                $image_column = 'image';
            } elseif (in_array('image_path', $banners_columns)) {
                $image_column = 'image_path';
            } elseif (in_array('image_url', $banners_columns)) {
                $image_column = 'image_url';
            }
            
            // Add the image column to the query
            $available_columns[] = $image_column;
            $placeholders[] = '?';
            $values[] = $banner_image;
            $types .= 's';
            
            // Add optional columns if they exist
            if (in_array('subtitle', $banners_columns)) {
                $available_columns[] = 'subtitle';
                $placeholders[] = '?';
                $values[] = $banner_subtitle;
                $types .= 's';
            }
            
            if (in_array('button_text', $banners_columns)) {
                $available_columns[] = 'button_text';
                $placeholders[] = '?';
                $values[] = $banner_button_text;
                $types .= 's';
            }
            
            if (in_array('button_link', $banners_columns)) {
                $available_columns[] = 'button_link';
                $placeholders[] = '?';
                $values[] = $banner_button_link;
                $types .= 's';
            }
            
            $sql = "INSERT INTO banners (" . implode(', ', $available_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param($types, ...$values);
                
                if ($stmt->execute()) {
                    $success_message = "Banner added successfully!";
                } else {
                    $error_message = "Failed to add banner: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Failed to prepare statement: " . $conn->error;
            }
        } elseif (empty($banner_image)) {
            $error_message = "Please upload a banner image.";
        }
    }

    // Handle banner deletion
    if (isset($_POST['delete_banner'])) {
        $banner_id = intval($_POST['banner_id']);
        
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        
        if ($stmt->execute()) {
            $success_message = "Banner deleted successfully!";
        } else {
            $error_message = "Failed to delete banner: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Helper function to safely fetch counts
function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Get statistics safely
$total_content = getCount($conn, "SELECT COUNT(*) as count FROM content");
$total_movies = getCount($conn, "SELECT COUNT(*) as count FROM content WHERE type='movie'");
$total_tv_shows = getCount($conn, "SELECT COUNT(*) as count FROM content WHERE type='tv_show'");
$total_kids = getCount($conn, "SELECT COUNT(*) as count FROM content WHERE type='kids'");
$total_banners = getCount($conn, "SELECT COUNT(*) as count FROM banners");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TechFlix Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6a5af9;
            --primary-dark: #5b4af0;
            --secondary: #d66efd;
            --success: #2ed573;
            --warning: #ffa502;
            --danger: #ff4757;
            --dark: #1e1e2d;
            --darker: #151521;
            --light: #f8f9fa;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--darker);
            color: var(--light);
            min-height: 100vh;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--dark);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--light);
            text-decoration: none;
        }

        .logo-icon {
            font-size: 24px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(106, 90, 249, 0.1);
            border-left-color: var(--primary);
            color: var(--primary);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .top-bar {
            background: var(--dark);
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .page-title p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--dark);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .content-card {
            background: var(--dark);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .content-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .content-thumbnail {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .content-info {
            padding: 15px;
        }

        .content-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--light);
        }

        .content-meta {
            display: flex;
            justify-content: space-between;
            color: var(--gray);
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        .content-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-warning {
            background: var(--warning);
            color: black;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Forms */
        .form-section {
            background: var(--dark);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: var(--light);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--light);
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .file-upload {
            border: 2px dashed rgba(255,255,255,0.2);
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(106, 90, 249, 0.05);
        }

        .preview-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
        }

        /* Recent Activity */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(106, 90, 249, 0.1);
            color: var(--primary);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .activity-time {
            color: var(--gray);
            font-size: 0.8rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--dark);
            border-radius: 10px;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--gray);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: var(--danger);
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            color: #2ed573;
            border-color: rgba(46, 213, 115, 0.3);
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            border-color: rgba(255, 71, 87, 0.3);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .quick-action-btn {
            background: var(--dark);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            color: var(--light);
            text-decoration: none;
        }

        .quick-action-btn:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .quick-action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        /* Conditional form fields */
        .optional-field {
            opacity: 0.8;
        }

        .optional-field .form-label::after {
            content: " (Optional)";
            color: var(--gray);
            font-weight: normal;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .logo-text,
            .sidebar .nav-text {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .content-grid {
                grid-template-columns: 1fr;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="logo-text">TechFlix</div>
                </a>
            </div>
            
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a></li>
                <li><a href="content.php">
                    <i class="fas fa-film"></i>
                    <span class="nav-text">Content</span>
                </a></li>
                <li><a href="users.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Users</span>
                </a></li>
                <li><a href="media.php">
                    <i class="fas fa-photo-video"></i>
                    <span class="nav-text">Media</span>
                </a></li>
                <li><a href="analytics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Analytics</span>
                </a></li>
                <li><a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back! Manage your content and track performance</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Content
                    </button>
                    <button class="btn btn-outline" onclick="openBannerModal()" style="margin-left: 10px;">
                        <i class="fas fa-image"></i> Add Banner
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

                    <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="content.php" class="quick-action-btn">
                    <div class="quick-action-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <div>Manage Content</div>
                </a>
                <a href="media.php" class="quick-action-btn">
                    <div class="quick-action-icon">
                        <i class="fas fa-photo-video"></i>
                    </div>
                    <div>Media Library</div>
                </a>
                <a href="users.php" class="quick-action-btn">
                    <div class="quick-action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>User Management</div>
                </a>
                <a href="analytics.php" class="quick-action-btn">
                    <div class="quick-action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>View Analytics</div>
                </a>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_content; ?></div>
                    <div class="stat-label">Total Content</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_movies; ?></div>
                    <div class="stat-label">Movies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_tv_shows; ?></div>
                    <div class="stat-label">TV Shows</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_kids; ?></div>
                    <div class="stat-label">Kids Content</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_banners; ?></div>
                    <div class="stat-label">Active Banners</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Recent Content -->
                <div class="form-section">
                    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-clock"></i> Recent Content
                    </h2>
                    
                    <?php if (!empty($recent_content)): ?>
                        <div class="content-grid">
                            <?php foreach($recent_content as $content): ?>
                            <div class="content-card">
                                <?php if (!empty($content['thumbnail'])): ?>
                                    <img src="<?php echo $content['thumbnail']; ?>" 
                                         alt="<?php echo $content['title']; ?>" 
                                         class="content-thumbnail">
                                <?php else: ?>
                                    <div style="background: linear-gradient(45deg, var(--primary), var(--secondary)); height: 200px; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-film" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="content-info">
                                    <div class="content-title"><?php echo $content['title']; ?></div>
                                    <div class="content-meta">
                                        <span><?php echo ucfirst($content['type']); ?></span>
                                        <?php if (isset($content['release_year'])): ?>
                                            <span><?php echo $content['release_year']; ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($content['rating'])): ?>
                                            <span>⭐ <?php echo $content['rating']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 10px;">
                                        <?php 
                                        $description = $content['description'] ?? '';
                                        echo substr($description, 0, 100);
                                        if (strlen($description) > 100) echo '...';
                                        ?>
                                    </p>
                                    <div class="content-actions">
                                        <button class="btn btn-primary" onclick="editContent(<?php echo $content['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" name="delete_content" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this content?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-film" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                            <h3>No Content Added</h3>
                            <p>Start by adding your first video content.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="form-section">
                    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-bell"></i> Recent Activity
                    </h2>
                    
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">New content added</div>
                                <div class="activity-time">2 minutes ago</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">User registered</div>
                                <div class="activity-time">1 hour ago</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-image"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Banner updated</div>
                                <div class="activity-time">3 hours ago</div>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">System maintenance</div>
                                <div class="activity-time">Yesterday</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Banner Management -->
            <div class="form-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-images"></i> Banner Management
                    </h2>
                </div>
                
                <?php if ($banners_result->num_rows > 0): ?>
                    <div class="content-grid">
                        <?php while($banner = $banners_result->fetch_assoc()): ?>
                        <div class="content-card">
                            <?php if (!empty($banner['image'])): ?>
                                <img src="<?php echo $banner['image']; ?>" 
                                     alt="<?php echo $banner['title']; ?>" 
                                     class="content-thumbnail">
                            <?php else: ?>
                                <div style="background: linear-gradient(45deg, var(--secondary), var(--primary)); height: 200px; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="content-info">
                                <div class="content-title"><?php echo $banner['title']; ?></div>
                                <div class="content-meta">
                                    <span><?php echo ucfirst($banner['type']); ?> Page</span>
                                    <span style="color: <?php echo $banner['is_active'] ? 'var(--success)' : 'var(--danger)'; ?>">
                                        <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 10px;">
                                    <?php 
                                    $subtitle = $banner['subtitle'] ?? '';
                                    echo substr($subtitle, 0, 100);
                                    if (strlen($subtitle) > 100) echo '...';
                                    ?>
                                </p>
                                <div class="content-actions">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                        <button type="submit" name="delete_banner" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this banner?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--gray);">
                        <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Banners Added</h3>
                        <p>Create banners for your homepage and other pages.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Content Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Content</h2>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="addForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-control" required>
                            <option value="movie">Movie</option>
                            <option value="tv_show">TV Show</option>
                            <option value="kids">Kids Content</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" required placeholder="Enter content description..."></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g., Action, Comedy, Drama">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Release Year</label>
                        <input type="number" name="release_year" class="form-control" min="1900" max="2030" value="2024">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" class="form-control" min="1" value="120">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating (0-10)</label>
                        <input type="number" name="rating" class="form-control" min="0" max="10" step="0.1" value="7.5">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Thumbnail Image *</label>
                        <div class="file-upload" onclick="document.getElementById('thumbnail').click()">
                            <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 10px; color: var(--primary);"></i>
                            <p>Click to upload thumbnail</p>
                            <p style="font-size: 0.8rem; color: var(--gray);">JPG, PNG, GIF, WebP (Max 10MB)</p>
                            <img id="thumbnailPreview" class="preview-image" style="display: none;">
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" style="display: none;" onchange="previewImage(this, 'thumbnailPreview')" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Video File *</label>
                        <div class="file-upload" onclick="document.getElementById('video_file').click()">
                            <i class="fas fa-video" style="font-size: 2rem; margin-bottom: 10px; color: var(--primary);"></i>
                            <p>Click to upload video</p>
                            <p style="font-size: 0.8rem; color: var(--gray);">MP4, AVI, MOV (Max 500MB)</p>
                            <input type="file" id="video_file" name="video_file" accept="video/*" style="display: none;" required>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="add_content" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Content
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Banner Modal -->
    <div id="bannerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Banner</h2>
                <button class="close-modal" onclick="closeBannerModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="bannerForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Banner Title *</label>
                        <input type="text" name="banner_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Page Type *</label>
                        <select name="banner_type" class="form-control" required>
                            <option value="home">Home Page</option>
                            <option value="browse">browse Page</option>
                            <option value="movie">Movies Page</option>
                            <option value="kids">Kids Page</option>
                            <option value="tv_show">TV Shows Page</option>
                        </select>
                    </div>
                </div>
                
                <?php if (in_array('subtitle', $banners_columns)): ?>
                <div class="form-group">
                    <label class="form-label">Subtitle</label>
                    <textarea name="banner_subtitle" class="form-control" placeholder="Enter banner subtitle..."></textarea>
                </div>
                <?php endif; ?>
                
                <div class="form-grid">
                    <?php if (in_array('button_text', $banners_columns)): ?>
                    <div class="form-group optional-field">
                        <label class="form-label">Button Text</label>
                        <input type="text" name="banner_button_text" class="form-control" placeholder="e.g., Watch Now">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('button_link', $banners_columns)): ?>
                    <div class="form-group optional-field">
                        <label class="form-label">Button Link</label>
                        <input type="text" name="banner_button_link" class="form-control" placeholder="e.g., /movie-details.php?id=123">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Banner Image *</label>
                    <div class="file-upload" onclick="document.getElementById('banner_image').click()">
                        <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 10px; color: var(--primary);"></i>
                        <p>Click to upload banner image</p>
                        <p style="font-size: 0.8rem; color: var(--gray);">JPG, PNG, GIF, WebP (Max 10MB)</p>
                        <img id="bannerPreview" class="preview-image" style="display: none;">
                        <input type="file" id="banner_image" name="banner_image" accept="image/*" style="display: none;" onchange="previewImage(this, 'bannerPreview')" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Active Banner</span>
                    </label>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeBannerModal()">Cancel</button>
                    <button type="submit" name="add_banner" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Banner
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Dashboard Console Insights
        console.group('🎛️ DASHBOARD DEBUG MODE');
        console.log('🕒 Dashboard loaded at:', new Date().toLocaleString());
        console.log('📊 Available banner columns:', <?php echo json_encode($banners_columns ?? []); ?>);

        // Monitor file uploads
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        console.log('📁 File selected:', {
                            name: file.name,
                            size: (file.size / 1024 / 1024).toFixed(2) + 'MB',
                            type: file.type
                        });
                        
                        // Validate file
                        if (file.size > 10000000) { // 10MB
                            console.error('❌ File too large:', file.name);
                        }
                    }
                });
            });
            
            // Monitor form submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    console.log('📤 Form submission:', form.id || 'unnamed-form');
                    const formData = new FormData(form);
                    for (let [key, value] of formData.entries()) {
                        if (key !== 'thumbnail' && key !== 'video_file' && key !== 'banner_image') {
                            console.log(`   ${key}: ${value}`);
                        }
                    }
                });
            });
        });

        console.groupEnd();

        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            // Reset form
            document.getElementById('addForm').reset();
            document.getElementById('thumbnailPreview').style.display = 'none';
        }

        function openBannerModal() {
            document.getElementById('bannerModal').style.display = 'flex';
        }

        function closeBannerModal() {
            document.getElementById('bannerModal').style.display = 'none';
            document.getElementById('bannerForm').reset();
            document.getElementById('bannerPreview').style.display = 'none';
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        function editContent(contentId) {
            alert('Edit functionality for content ID: ' + contentId + '\nIn a complete system, this would open an edit form.');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const bannerModal = document.getElementById('bannerModal');
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === bannerModal) {
                closeBannerModal();
            }
        }

        // Form validation
        document.getElementById('addForm').addEventListener('submit', function(e) {
            const thumbnail = document.getElementById('thumbnail').files[0];
            const video = document.getElementById('video_file').files[0];
            
            if (!thumbnail || !video) {
                e.preventDefault();
                alert('Please upload both thumbnail and video files.');
                return false;
            }
        });

        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            const bannerImage = document.getElementById('banner_image').files[0];
            
            if (!bannerImage) {
                e.preventDefault();
                alert('Please upload a banner image.');
                return false;
            }
        });

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            console.log('🔄 Auto-refreshing dashboard data...');
            // In a real application, you would fetch updated data via AJAX
        }, 30000);
    </script>
</body>
</html>