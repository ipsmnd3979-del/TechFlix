<?php
require_once 'includes/header.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_banner'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $target_url = $_POST['target_url'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $banner_type = $_POST['banner_type'] ?? 'promotional';

        $insert_stmt = $conn->prepare("INSERT INTO banners (title, description, image_url, target_url, is_active, banner_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("ssssis", $title, $description, $image_url, $target_url, $is_active, $banner_type);

        if ($insert_stmt->execute()) {
            $success_message = "Banner added successfully!";
        } else {
            $error_message = "Error adding banner: " . $conn->error;
        }
        $insert_stmt->close();
    }

    if (isset($_POST['update_banner'])) {
        $banner_id = intval($_POST['banner_id']);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        $target_url = $_POST['target_url'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $banner_type = $_POST['banner_type'] ?? 'promotional';

        $update_stmt = $conn->prepare("UPDATE banners SET title = ?, description = ?, image_url = ?, target_url = ?, is_active = ?, banner_type = ?, updated_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("ssssisi", $title, $description, $image_url, $target_url, $is_active, $banner_type, $banner_id);

        if ($update_stmt->execute()) {
            $success_message = "Banner updated successfully!";
        } else {
            $error_message = "Error updating banner: " . $conn->error;
        }
        $update_stmt->close();
    }

    if (isset($_POST['delete_banner'])) {
        $banner_id = intval($_POST['banner_id']);
        $delete_stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $delete_stmt->bind_param("i", $banner_id);

        if ($delete_stmt->execute()) {
            $success_message = "Banner deleted successfully!";
        } else {
            $error_message = "Error deleting banner: " . $conn->error;
        }
        $delete_stmt->close();
    }
}

// Check if banners table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'banners'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE banners (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(500) NOT NULL,
        target_url VARCHAR(500),
        is_active TINYINT(1) DEFAULT 1,
        banner_type ENUM('movie', 'tv_show', 'promotional', 'hero') DEFAULT 'promotional',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($create_table)) {
        $sample_banners = [
            "('TV Shows Banner', 'Discover amazing TV shows', './img/tvshows/tv-banner1.webp', 'tvshows.php', 1, 'tv_show', NOW())",
            "('Movies Banner', 'Latest movies collection', './img/movies/movie-banner1.webp', 'movies.php', 1, 'movie', NOW())",
            "('Promotional Banner', 'Special offers', './img/promotional/banner1.webp', 'offers.php', 1, 'promotional', NOW())"
        ];
        foreach ($sample_banners as $banner) {
            $conn->query("INSERT INTO banners (title, description, image_url, target_url, is_active, banner_type, created_at) VALUES $banner");
        }
        header("Location: banners.php");
        exit();
    }
}

// Get all banners safely
$banners_result = $conn->query("SELECT * FROM banners ORDER BY created_at DESC");
?>

<div class="page-content">
    <div class="admin-header">
        <h1><i class="fas fa-images"></i> Banner Management</h1>
        <p>Create and manage promotional banners for your website</p>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <h2><i class="fas fa-plus-circle"></i> Add New Banner</h2>
        <form method="POST" class="banner-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Banner Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="banner_type">Banner Type</label>
                    <select id="banner_type" name="banner_type" required>
                        <option value="promotional">Promotional</option>
                        <option value="movie">Movie</option>
                        <option value="tv_show">TV Show</option>
                        <option value="hero">Hero Banner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image_url">Image URL *</label>
                    <input type="url" id="image_url" name="image_url" required placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label for="target_url">Target URL</label>
                    <input type="url" id="target_url" name="target_url" placeholder="https://example.com/page">
                </div>
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief description of the banner"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="checkmark"></span>
                        Active Banner
                    </label>
                </div>
            </div>
            <button type="submit" name="add_banner" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Banner
            </button>
        </form>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-list"></i> Existing Banners</h2>
        <?php if ($banners_result && $banners_result->num_rows > 0): ?>
            <div class="banners-grid">
                <?php while ($banner = $banners_result->fetch_assoc()): ?>
                    <div class="banner-card <?php echo $banner['is_active'] ? 'active' : 'inactive'; ?>">
                        <div class="banner-preview">
                            <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>" onerror="this.src='./assets/img/default-banner.jpg'">
                            <div class="banner-overlay">
                                <span class="banner-type"><?php echo htmlspecialchars(ucfirst($banner['banner_type'])); ?></span>
                                <span class="banner-status"><?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?></span>
                            </div>
                        </div>
                        <div class="banner-info">
                            <h3><?php echo htmlspecialchars($banner['title']); ?></h3>
                            <p><?php echo htmlspecialchars($banner['description']); ?></p>
                            <?php if ($banner['target_url']): ?>
                                <p><strong>Target:</strong> <?php echo htmlspecialchars($banner['target_url']); ?></p>
                            <?php endif; ?>
                            <p><small>Created: <?php echo date('M j, Y', strtotime($banner['created_at'])); ?></small></p>
                            <div class="banner-actions">
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($banner['title']); ?>">
                                    <input type="hidden" name="description" value="<?php echo htmlspecialchars($banner['description']); ?>">
                                    <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($banner['image_url']); ?>">
                                    <input type="hidden" name="target_url" value="<?php echo htmlspecialchars($banner['target_url']); ?>">
                                    <input type="hidden" name="banner_type" value="<?php echo htmlspecialchars($banner['banner_type']); ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $banner['is_active']; ?>">
                                    <button type="submit" name="update_banner" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Edit</button>
                                </form>
                                <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <button type="submit" name="delete_banner" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($banner['title']); ?>">
                                    <input type="hidden" name="description" value="<?php echo htmlspecialchars($banner['description']); ?>">
                                    <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($banner['image_url']); ?>">
                                    <input type="hidden" name="target_url" value="<?php echo htmlspecialchars($banner['target_url']); ?>">
                                    <input type="hidden" name="banner_type" value="<?php echo htmlspecialchars($banner['banner_type']); ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $banner['is_active'] ? '0' : '1'; ?>">
                                    <button type="submit" name="update_banner" class="btn btn-sm <?php echo $banner['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <i class="fas fa-power-off"></i> <?php echo $banner['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No Banners Found</h3>
                <p>No banners have been created yet. Add your first banner using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    .banner-form .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .banner-form .full-width { grid-column: 1 / -1; }
    .banners-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
    .banner-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; }
    .banner-card.active { border-left: 4px solid #4CAF50; }
    .banner-card.inactive { border-left: 4px solid #ff9800; opacity: 0.7; }
    .banner-preview { position: relative; height: 150px; overflow: hidden; }
    .banner-preview img { width: 100%; height: 100%; object-fit: cover; }
    .banner-overlay { position: absolute; top: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0, 0, 0, 0.7)); color: white; padding: 0.5rem; display: flex; justify-content: space-between; }
    .banner-info { padding: 1rem; }
    .banner-info h3 { margin: 0 0 0.5rem 0; font-size: 1.1rem; }
    .banner-info p { margin: 0.25rem 0; font-size: 0.9rem; color: #666; }
    .banner-actions { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
    .inline-form { display: inline; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
    .info-item { text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; }
    .info-item h3 { color: #333; margin-bottom: 0.5rem; }
    .info-item p { color: #666; margin: 0; }
    @media (max-width: 768px) {
        .banner-form .form-grid { grid-template-columns: 1fr; }
        .banners-grid { grid-template-columns: 1fr; }
        .banner-actions { flex-direction: column; }
        .inline-form { display: block; margin-bottom: 0.5rem; }
        .inline-form .btn { width: 100%; }
    }
</style>
<?php require_once 'includes/footer.php'; ?>