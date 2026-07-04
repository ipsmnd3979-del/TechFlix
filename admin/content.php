<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check admin permissions
// if (!isAdmin()) {
//     header("Location: ../login.php");
//     exit();
// }

// Initialize variables to prevent undefined variable warnings
$success_message = '';
$error_message = '';
$total_content = 0;
$total_movies = 0;
$total_tv_shows = 0;
$total_kids = 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_content'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $type = $_POST['type'];
        $release_year = $_POST['release_year'];
        $duration = $_POST['duration'];
        $rating = $_POST['rating'];
        $poster_image = $_POST['poster_image'];
        $trailer_url = $_POST['trailer_url'];
        $content_file = $_POST['content_file'];

        try {
            $stmt = $conn->prepare("INSERT INTO content (title, description, category_id, type, release_year, duration, rating, poster_image, trailer_url, content_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisiiisss", $title, $description, $category_id, $type, $release_year, $duration, $rating, $poster_image, $trailer_url, $content_file);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Content added successfully!";
                header("Location: content.php");
                exit();
            } else {
                $error_message = "Error adding content: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    
    // Handle update if exists
    if (isset($_POST['update_content'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $type = $_POST['type'];
        $release_year = $_POST['release_year'];
        $duration = $_POST['duration'];
        $rating = $_POST['rating'];
        $poster_image = $_POST['poster_image'];
        $trailer_url = $_POST['trailer_url'];
        $content_file = $_POST['content_file'];

        try {
            $stmt = $conn->prepare("UPDATE content SET title=?, description=?, category_id=?, type=?, release_year=?, duration=?, rating=?, poster_image=?, trailer_url=?, content_file=? WHERE id=?");
            $stmt->bind_param("ssisiiisssi", $title, $description, $category_id, $type, $release_year, $duration, $rating, $poster_image, $trailer_url, $content_file, $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Content updated successfully!";
                header("Location: content.php");
                exit();
            } else {
                $error_message = "Error updating content: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    
    // Handle delete if exists
    if (isset($_POST['delete_content'])) {
        $id = $_POST['id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM content WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Content deleted successfully!";
                header("Location: content.php");
                exit();
            } else {
                $error_message = "Error deleting content: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Get content statistics
try {
    // Total content
    $result = $conn->query("SELECT COUNT(*) as total FROM content");
    if ($result && $row = $result->fetch_assoc()) {
        $total_content = $row['total'];
    }
    
    // Total movies
    $result = $conn->query("SELECT COUNT(*) as total FROM content WHERE type = 'movie'");
    if ($result && $row = $result->fetch_assoc()) {
        $total_movies = $row['total'];
    }
    
    // Total TV shows
    $result = $conn->query("SELECT COUNT(*) as total FROM content WHERE type = 'tv_show'");
    if ($result && $row = $result->fetch_assoc()) {
        $total_tv_shows = $row['total'];
    }
    
    // Total kids content
    $result = $conn->query("SELECT COUNT(*) as total FROM content WHERE type = 'kids'");
    if ($result && $row = $result->fetch_assoc()) {
        $total_kids = $row['total'];
    }
} catch (Exception $e) {
    // If there's an error, the variables will remain 0
    error_log("Error fetching content statistics: " . $e->getMessage());
}

// Get existing content for display
$content_query = "SELECT c.*, cat.name as category_name 
                  FROM content c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  ORDER BY c.created_at DESC";
$content_result = $conn->query($content_query);

// Get categories for dropdown
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");

// Check for success/error messages from session
if (isset($_SESSION['message'])) {
    $success_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content - TechFlix Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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
            z-index: 100;
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
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
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

        .btn-warning:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            opacity: 0.9;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: rgba(106, 90, 249, 0.1);
        }

        /* Forms */
        .form-section {
            background: var(--dark);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
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
        }

        .preview-image {
            max-width: 200px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 5px;
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
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
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
            color: var(--light);
        }

        /* Search and Filter */
        .table-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--light);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filter-select {
            width: 200px;
        }

        /* File Upload Preview */
        .upload-preview {
            display: none;
            margin-top: 10px;
            text-align: center;
        }

        .upload-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
        }

        /* Loading Spinner */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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
                grid-template-columns: 1fr 1fr;
            }
            .content-grid {
                grid-template-columns: 1fr;
            }
            .table-controls {
                flex-direction: column;
            }
            .filter-select {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .top-bar {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
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
                <li><a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a></li>
                <li><a href="content.php" class="active">
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
                    <h1>Content Management</h1>
                    <p>Manage movies, TV shows, and other content</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add New Content
                    </button>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_content; ?></div>
                    <div class="stat-label">Total Content</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_movies; ?></div>
                    <div class="stat-label">Movies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_tv_shows; ?></div>
                    <div class="stat-label">TV Shows</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_kids; ?></div>
                    <div class="stat-label">Kids Content</div>
                </div>
            </div>

            <!-- Content List -->
            <div class="form-section">
                <div class="table-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search content...">
                    </div>
                    <select id="typeFilter" class="form-control filter-select">
                        <option value="">All Types</option>
                        <option value="movie">Movie</option>
                        <option value="tv_show">TV Show</option>
                        <option value="kids">Kids</option>
                    </select>
                    <select id="categoryFilter" class="form-control filter-select">
                        <option value="">All Categories</option>
                        <?php 
                        // Reset categories pointer for filter dropdown
                        $categories_result->data_seek(0);
                        while($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo strtolower($category['name']); ?>"><?php echo $category['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="content-grid" id="contentGrid">
                    <?php if ($content_result && $content_result->num_rows > 0): ?>
                        <?php while($content = $content_result->fetch_assoc()): ?>
                        <div class="content-card" data-type="<?php echo $content['type']; ?>" data-category="<?php echo strtolower($content['category_name']); ?>">
                            <?php if (!empty($content['poster_image'])): ?>
                                <img src="<?php echo $content['poster_image']; ?>" 
                                     alt="<?php echo $content['title']; ?>" 
                                     class="content-thumbnail">
                            <?php else: ?>
                                <div style="background: var(--primary); height: 200px; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-film" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="content-info">
                                <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
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
                                    <?php echo substr($content['description'], 0, 100) . '...'; ?>
                                </p>
                                <div class="content-actions">
                                    <button class="btn btn-primary" onclick="editContent(<?php echo $content['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                        <button type="submit" name="delete_content" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this content?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-film"></i>
                            <h3>No Content Added</h3>
                            <p>Start by adding your first video content.</p>
                            <button class="btn btn-primary" onclick="openAddModal()" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Add Content
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Content Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Content</h2>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" action="" id="addForm" onsubmit="return validateForm()">
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
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php 
                            // Reset categories pointer for add form dropdown
                            $categories_result->data_seek(0);
                            while($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
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

                <div class="form-group">
                    <label class="form-label">Poster Image URL</label>
                    <input type="text" name="poster_image" class="form-control" placeholder="Enter poster image URL" id="posterImageInput" onchange="previewImage(this.value, 'posterPreview')">
                    <div class="upload-preview" id="posterPreview">
                        <img src="" alt="Poster Preview" id="posterPreviewImg">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Trailer URL</label>
                    <input type="text" name="trailer_url" class="form-control" placeholder="Enter trailer URL">
                </div>

                <div class="form-group">
                    <label class="form-label">Content File URL</label>
                    <input type="text" name="content_file" class="form-control" placeholder="Enter content file URL">
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="add_content" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Add Content
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Content</h2>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="" id="editForm" onsubmit="return validateEditForm()">
                <input type="hidden" name="id" id="editId">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" id="editTitle" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-control" id="editType" required>
                            <option value="movie">Movie</option>
                            <option value="tv_show">TV Show</option>
                            <option value="kids">Kids Content</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" id="editDescription" required placeholder="Enter content description..."></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" id="editCategoryId" required>
                            <option value="">Select Category</option>
                            <?php 
                            // Reset categories pointer for edit form dropdown
                            $categories_result->data_seek(0);
                            while($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Release Year</label>
                        <input type="number" name="release_year" class="form-control" id="editReleaseYear" min="1900" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" class="form-control" id="editDuration" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating (0-10)</label>
                        <input type="number" name="rating" class="form-control" id="editRating" min="0" max="10" step="0.1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Poster Image URL</label>
                    <input type="text" name="poster_image" class="form-control" id="editPosterImage" placeholder="Enter poster image URL" onchange="previewImage(this.value, 'editPosterPreview')">
                    <div class="upload-preview" id="editPosterPreview">
                        <img src="" alt="Poster Preview" id="editPosterPreviewImg">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Trailer URL</label>
                    <input type="text" name="trailer_url" class="form-control" id="editTrailerUrl" placeholder="Enter trailer URL">
                </div>

                <div class="form-group">
                    <label class="form-label">Content File URL</label>
                    <input type="text" name="content_file" class="form-control" id="editContentFile" placeholder="Enter content file URL">
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="update_content" class="btn btn-primary" id="editSubmitBtn">
                        <i class="fas fa-save"></i> Update Content
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addForm').reset();
            document.getElementById('posterPreview').style.display = 'none';
        }

        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editPosterPreview').style.display = 'none';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('#contentGrid .content-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.content-title').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                const text = title + ' ' + description;
                
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Filter functionality
        document.getElementById('typeFilter').addEventListener('change', filterContent);
        document.getElementById('categoryFilter').addEventListener('change', filterContent);

        function filterContent() {
            const typeFilter = document.getElementById('typeFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
            const cards = document.querySelectorAll('#contentGrid .content-card');
            
            cards.forEach(card => {
                const type = card.getAttribute('data-type');
                const category = card.getAttribute('data-category');
                
                const typeMatch = !typeFilter || type === typeFilter;
                const categoryMatch = !categoryFilter || category.includes(categoryFilter);
                
                card.style.display = typeMatch && categoryMatch ? '' : 'none';
            });
        }

        // Image preview
        function previewImage(url, previewId) {
            const preview = document.getElementById(previewId);
            const img = document.getElementById(previewId + 'Img');
            
            if (url) {
                img.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        // Form validation
        function validateForm() {
            const title = document.querySelector('#addForm input[name="title"]').value;
            const description = document.querySelector('#addForm textarea[name="description"]').value;
            
            if (!title.trim() || !description.trim()) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<div class="loading"></div> Saving...';
            submitBtn.disabled = true;
            
            return true;
        }

        function validateEditForm() {
            const title = document.querySelector('#editForm input[name="title"]').value;
            const description = document.querySelector('#editForm textarea[name="description"]').value;
            
            if (!title.trim() || !description.trim()) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('editSubmitBtn');
            submitBtn.innerHTML = '<div class="loading"></div> Updating...';
            submitBtn.disabled = true;
            
            return true;
        }

        // Edit content function
        function editContent(id) {
            // In a real application, this would fetch data from the server
            // For this demo, we'll simulate with dummy data
            const content = {
                id: id,
                title: 'Sample Movie Title',
                description: 'This is a sample movie description that would be loaded from the database.',
                type: 'movie',
                category_id: 1,
                release_year: 2024,
                duration: 120,
                rating: 7.5,
                poster_image: 'https://via.placeholder.com/300x450',
                trailer_url: 'https://www.youtube.com/watch?v=example',
                content_file: 'https://example.com/movie.mp4'
            };
            
            // Populate the edit form
            document.getElementById('editId').value = content.id;
            document.getElementById('editTitle').value = content.title;
            document.getElementById('editDescription').value = content.description;
            document.getElementById('editType').value = content.type;
            document.getElementById('editCategoryId').value = content.category_id;
            document.getElementById('editReleaseYear').value = content.release_year;
            document.getElementById('editDuration').value = content.duration;
            document.getElementById('editRating').value = content.rating;
            document.getElementById('editPosterImage').value = content.poster_image;
            document.getElementById('editTrailerUrl').value = content.trailer_url;
            document.getElementById('editContentFile').value = content.content_file;
            
            // Show image preview if exists
            if (content.poster_image) {
                previewImage(content.poster_image, 'editPosterPreview');
            }
            
            openEditModal();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>