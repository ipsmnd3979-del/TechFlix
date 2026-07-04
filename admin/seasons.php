<?php
// Adjust the path for admin folder
require_once '../includes/header.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_season'])) {
        $content_id = intval($_POST['content_id']);
        $season_number = intval($_POST['season_number']);
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $release_date = $conn->real_escape_string($_POST['release_date']);
        $episode_count = intval($_POST['episode_count']);
        $poster_image = $conn->real_escape_string($_POST['poster_image']);
        
        $insert_query = "INSERT INTO seasons (content_id, season_number, title, description, release_date, episode_count, poster_image, created_at) 
                         VALUES ($content_id, $season_number, '$title', '$description', '$release_date', $episode_count, '$poster_image', NOW())";
        
        if ($conn->query($insert_query)) {
            $success_message = "Season added successfully!";
        } else {
            $error_message = "Error adding season: " . $conn->error;
        }
    }
    
    if (isset($_POST['update_season'])) {
        $season_id = intval($_POST['season_id']);
        $content_id = intval($_POST['content_id']);
        $season_number = intval($_POST['season_number']);
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $release_date = $conn->real_escape_string($_POST['release_date']);
        $episode_count = intval($_POST['episode_count']);
        $poster_image = $conn->real_escape_string($_POST['poster_image']);
        
        $update_query = "UPDATE seasons SET 
                        content_id = $content_id,
                        season_number = $season_number,
                        title = '$title',
                        description = '$description',
                        release_date = '$release_date',
                        episode_count = $episode_count,
                        poster_image = '$poster_image',
                        updated_at = NOW()
                        WHERE id = $season_id";
        
        if ($conn->query($update_query)) {
            $success_message = "Season updated successfully!";
        } else {
            $error_message = "Error updating season: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete_season'])) {
        $season_id = intval($_POST['season_id']);
        
        // First, delete associated episodes
        $delete_episodes_query = "DELETE FROM episodes WHERE season_id = $season_id";
        $conn->query($delete_episodes_query);
        
        // Then delete the season
        $delete_query = "DELETE FROM seasons WHERE id = $season_id";
        
        if ($conn->query($delete_query)) {
            $success_message = "Season and associated episodes deleted successfully!";
        } else {
            $error_message = "Error deleting season: " . $conn->error;
        }
    }
}

// Get all TV shows for dropdown
$tv_shows_query = "SELECT id, title FROM content WHERE type = 'tv_show' ORDER BY title";
$tv_shows_result = $conn->query($tv_shows_query);

// Get all seasons with show information
$seasons_query = "SELECT s.*, c.title as show_title 
                  FROM seasons s 
                  JOIN content c ON s.content_id = c.id 
                  ORDER BY c.title, s.season_number";
$seasons_result = $conn->query($seasons_query);

// Check if seasons table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'seasons'");
if ($table_check->num_rows == 0) {
    // Create seasons table
    $create_table = "CREATE TABLE seasons (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        content_id INT(11) NOT NULL,
        season_number INT(11) NOT NULL,
        title VARCHAR(255),
        description TEXT,
        release_date DATE,
        episode_count INT(11) DEFAULT 0,
        poster_image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
        UNIQUE KEY unique_season (content_id, season_number)
    )";
    
    if ($conn->query($create_table)) {
        // Check if episodes table exists, if not create it
        $episodes_table_check = $conn->query("SHOW TABLES LIKE 'episodes'");
        if ($episodes_table_check->num_rows == 0) {
            $create_episodes_table = "CREATE TABLE episodes (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                season_id INT(11) NOT NULL,
                episode_number INT(11) NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                duration INT(11),
                video_url VARCHAR(500),
                release_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
                UNIQUE KEY unique_episode (season_id, episode_number)
            )";
            $conn->query($create_episodes_table);
        }
        
        $success_message = "Seasons and episodes tables created successfully!";
        // Refresh the page to show the new data
        header("Location: seasons.php");
        exit();
    }
}
?>

<div class="page-content">
    <div class="admin-header">
        <h1><i class="fas fa-tv"></i> Season Management</h1>
        <p>Manage TV show seasons and episodes</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Add Season Form -->
    <div class="admin-card">
        <h2><i class="fas fa-plus-circle"></i> Add New Season</h2>
        <form method="POST" class="season-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="content_id">TV Show *</label>
                    <select id="content_id" name="content_id" required>
                        <option value="">Select TV Show</option>
                        <?php while($show = $tv_shows_result->fetch_assoc()): ?>
                            <option value="<?php echo $show['id']; ?>"><?php echo $show['title']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="season_number">Season Number *</label>
                    <input type="number" id="season_number" name="season_number" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="title">Season Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g., The Final Season">
                </div>
                
                <div class="form-group">
                    <label for="episode_count">Episode Count</label>
                    <input type="number" id="episode_count" name="episode_count" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label for="release_date">Release Date</label>
                    <input type="date" id="release_date" name="release_date">
                </div>
                
                <div class="form-group">
                    <label for="poster_image">Poster Image URL</label>
                    <input type="url" id="poster_image" name="poster_image" placeholder="https://example.com/poster.jpg">
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Season Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the season storyline..."></textarea>
                </div>
            </div>
            
            <button type="submit" name="add_season" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Season
            </button>
        </form>
    </div>

    <!-- Existing Seasons -->
    <div class="admin-card">
        <h2><i class="fas fa-list"></i> Existing Seasons</h2>
        
        <?php if ($seasons_result && $seasons_result->num_rows > 0): ?>
            <div class="seasons-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Show</th>
                            <th>Season</th>
                            <th>Title</th>
                            <th>Episodes</th>
                            <th>Release Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($season = $seasons_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo $season['show_title']; ?></strong>
                            </td>
                            <td>
                                <span class="season-badge">S<?php echo $season['season_number']; ?></span>
                            </td>
                            <td>
                                <?php echo $season['title'] ?: 'Season ' . $season['season_number']; ?>
                            </td>
                            <td>
                                <?php echo $season['episode_count']; ?> episodes
                            </td>
                            <td>
                                <?php echo $season['release_date'] ? date('M j, Y', strtotime($season['release_date'])) : 'TBA'; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-sm btn-outline" 
                                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($season)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <!-- Episodes Button -->
                                    <a href="episodes.php?season_id=<?php echo $season['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-play-circle"></i> Episodes
                                    </a>
                                    
                                    <!-- Delete Form -->
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure? This will also delete all episodes in this season.');">
                                        <input type="hidden" name="season_id" value="<?php echo $season['id']; ?>">
                                        <button type="submit" name="delete_season" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <h3>No Seasons Found</h3>
                <p>No seasons have been created yet. Add your first season using the form above.</p>
                <?php if ($tv_shows_result->num_rows == 0): ?>
                    <p class="warning-text">You need to create TV shows first before adding seasons.</p>
                    <a href="content.php?type=tv_show" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create TV Show
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Season Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Edit Season</h2>
            <span class="close">&times;</span>
        </div>
        <form method="POST" id="editForm">
            <div class="modal-body">
                <input type="hidden" name="season_id" id="edit_season_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_content_id">TV Show</label>
                        <select id="edit_content_id" name="content_id" required>
                            <?php 
                            // Reset pointer and get TV shows again for the modal
                            $tv_shows_result->data_seek(0);
                            while($show = $tv_shows_result->fetch_assoc()): ?>
                                <option value="<?php echo $show['id']; ?>"><?php echo $show['title']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_season_number">Season Number</label>
                        <input type="number" id="edit_season_number" name="season_number" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_title">Season Title</label>
                        <input type="text" id="edit_title" name="title" placeholder="e.g., The Final Season">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_episode_count">Episode Count</label>
                        <input type="number" id="edit_episode_count" name="episode_count" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_release_date">Release Date</label>
                        <input type="date" id="edit_release_date" name="release_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_poster_image">Poster Image URL</label>
                        <input type="url" id="edit_poster_image" name="poster_image" placeholder="https://example.com/poster.jpg">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="edit_description">Season Description</label>
                        <textarea id="edit_description" name="description" rows="4" placeholder="Describe the season storyline..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_season" class="btn btn-primary">Update Season</button>
            </div>
        </form>
    </div>
</div>

<style>
.season-form .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.season-form .full-width {
    grid-column: 1 / -1;
}

.seasons-table-container {
    overflow-x: auto;
    margin-top: 1rem;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.admin-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.season-badge {
    background: #007bff;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.inline-form {
    display: inline;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    flex: 1;
}

.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.warning-text {
    color: #ff9800;
    font-weight: 500;
}

@media (max-width: 768px) {
    .season-form .form-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .inline-form .btn {
        width: 100%;
    }
    
    .admin-table {
        font-size: 0.9rem;
    }
}
</style>

<script>
// Modal functionality
const modal = document.getElementById('editModal');
const closeBtn = document.querySelector('.close');

function openEditModal(season) {
    document.getElementById('edit_season_id').value = season.id;
    document.getElementById('edit_content_id').value = season.content_id;
    document.getElementById('edit_season_number').value = season.season_number;
    document.getElementById('edit_title').value = season.title || '';
    document.getElementById('edit_description').value = season.description || '';
    document.getElementById('edit_release_date').value = season.release_date || '';
    document.getElementById('edit_episode_count').value = season.episode_count || 0;
    document.getElementById('edit_poster_image').value = season.poster_image || '';
    
    modal.style.display = 'block';
}

function closeEditModal() {
    modal.style.display = 'none';
}

closeBtn.onclick = closeEditModal;

window.onclick = function(event) {
    if (event.target == modal) {
        closeEditModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});
</script>

<?php 
// Adjust the path for admin folder
require_once '../includes/footer.php'; 
?>