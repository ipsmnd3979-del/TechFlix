<?php
require_once '../includes/header.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get season_id from URL
$season_id = isset($_GET['season_id']) ? intval($_GET['season_id']) : 0;

if ($season_id > 0) {
    // Get season information
    $season_query = "SELECT s.*, c.title as show_title 
                     FROM seasons s 
                     JOIN content c ON s.content_id = c.id 
                     WHERE s.id = $season_id";
    $season_result = $conn->query($season_query);
    $season = $season_result->fetch_assoc();
}

// Handle episode operations here...
?>

<div class="page-content">
    <div class="admin-header">
        <h1><i class="fas fa-play-circle"></i> Episode Management</h1>
        <p>Manage episodes for <?php echo $season['show_title'] ?? 'Season'; ?> - Season <?php echo $season['season_number'] ?? ''; ?></p>
    </div>
    
    <!-- Episode management content will go here -->
</div>

<?php require_once '../includes/footer.php'; ?>