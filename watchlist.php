<?php
require_once 'includes/header.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: auth/login.php");
    exit();
}

// Handle remove from watchlist action
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $content_id = $_GET['remove'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $content_id);
        if ($stmt->execute()) {
            $success_message = "Content removed from watchlist!";
        } else {
            $error_message = "Error removing content from watchlist.";
        }
        $stmt->close();
    }
}

// Handle clear all action
if (isset($_POST['clear_all'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_message = "Watchlist cleared!";
        } else {
            $error_message = "Error clearing watchlist.";
        }
        $stmt->close();
    }
}

// Fetch user's watchlist
$user_id = $_SESSION['user_id'];
$watchlistQuery = "
    SELECT c.*, w.added_at 
    FROM watchlist w 
    JOIN content c ON w.content_id = c.id 
    WHERE w.user_id = ? 
    ORDER BY w.added_at DESC
";

$watchlistResult = null;
if ($stmt = $conn->prepare($watchlistQuery)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $watchlistResult = $stmt->get_result();
    $stmt->close();
} else {
    error_log("Watchlist query failed: " . $conn->error);
}
?>

<div class="page-content">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-bookmark"></i> My Watchlist
            </h1>
            <p class="page-subtitle">Your personal collection of movies and shows to watch later</p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Watchlist Actions -->
        <?php if ($watchlistResult && $watchlistResult->num_rows > 0): ?>
        <div class="watchlist-actions">
            <div class="watchlist-stats">
                <span class="stat">
                    <i class="fas fa-film"></i>
                    <?php echo $watchlistResult->num_rows; ?> items
                </span>
            </div>
            <form method="POST" class="clear-form" onsubmit="return confirm('Are you sure you want to clear your entire watchlist?');">
                <button type="submit" name="clear_all" class="btn btn-outline btn-danger">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Watchlist Content -->
        <div class="watchlist-content">
            <?php if ($watchlistResult && $watchlistResult->num_rows > 0): ?>
                <div class="content-grid watchlist-grid">
                    <?php while($content = $watchlistResult->fetch_assoc()): ?>
                    <div class="content-card watchlist-card" data-content-id="<?php echo $content['id']; ?>">
                        <div class="card-header">
                            <span class="added-date">
                                Added <?php echo date('M j, Y', strtotime($content['added_at'])); ?>
                            </span>
                            <a href="watchlist.php?remove=<?php echo $content['id']; ?>" 
                               class="remove-btn"
                               onclick="return confirm('Remove from watchlist?');"
                               title="Remove from watchlist">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        
                        <img src="<?php echo isset($content['poster_image']) && !empty($content['poster_image']) ? $content['poster_image'] : 'assets/img/default-poster.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($content['title']); ?>" 
                             class="card-img"
                             onerror="this.src='assets/img/default-poster.jpg'">
                        
                        <div class="card-overlay">
                            <h3 class="card-title"><?php echo htmlspecialchars($content['title']); ?></h3>
                            
                            <div class="card-info">
                                <?php if (isset($content['rating']) && !empty($content['rating'])): ?>
                                <span class="rating">
                                    <i class="fas fa-star"></i> <?php echo $content['rating']; ?>
                                </span>
                                <?php endif; ?>
                                
                                <?php if (isset($content['release_year']) && !empty($content['release_year'])): ?>
                                <span class="year"><?php echo $content['release_year']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm" 
                                        onclick="playContent(<?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title']); ?>')">
                                    <i class="fas fa-play"></i> Play
                                </button>
                                
                                <button class="btn btn-outline btn-sm" 
                                        onclick="removeFromWatchlist(<?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title']); ?>')">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- Empty Watchlist State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h2>Your Watchlist is Empty</h2>
                    <p>Start building your watchlist by adding movies and TV shows you want to watch later.</p>
                    <div class="empty-actions">
                        <a href="browse.php" class="btn btn-primary">
                            <i class="fas fa-compass"></i> Browse Content
                        </a>
                        <a href="movies.php" class="btn btn-outline">
                            <i class="fas fa-film"></i> Explore Movies
                        </a>
                        <a href="tvshows.php" class="btn btn-outline">
                            <i class="fas fa-tv"></i> Explore TV Shows
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: #fff;
}

.page-subtitle {
    color: #ccc;
    font-size: 1.1rem;
}

.watchlist-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.watchlist-stats {
    display: flex;
    gap: 20px;
}

.stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ccc;
    font-weight: 500;
}

.stat i {
    color: #ff6b6b;
}

.watchlist-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.watchlist-card {
    position: relative;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.watchlist-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 255, 255, 0.3);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: rgba(0, 0, 0, 0.5);
}

.added-date {
    font-size: 0.85rem;
    color: #ccc;
}

.remove-btn {
    color: #ff6b6b;
    text-decoration: none;
    padding: 5px;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.remove-btn:hover {
    background: rgba(255, 107, 107, 0.2);
}

.watchlist-card .card-img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.watchlist-card .card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
    padding: 20px;
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #fff;
}

.card-info {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.card-info span {
    font-size: 0.85rem;
    color: #ccc;
}

.card-info .rating {
    color: #ffd700;
}

.card-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 0.85rem;
}

/* Empty State Styles */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 4rem;
    color: #ff6b6b;
    margin-bottom: 20px;
}

.empty-state h2 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #fff;
}

.empty-state p {
    font-size: 1.1rem;
    color: #ccc;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

/* Alert Styles */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid;
}

.alert-success {
    background: rgba(76, 175, 80, 0.2);
    border-color: #4caf50;
    color: #4caf50;
}

.alert-error {
    background: rgba(244, 67, 54, 0.2);
    border-color: #f44336;
    color: #f44336;
}

/* Responsive Design */
@media (max-width: 768px) {
    .watchlist-actions {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .watchlist-stats {
        justify-content: center;
    }
    
    .watchlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .empty-actions .btn {
        width: 200px;
    }
}
</style>

<script>
// Watchlist specific functionality
function removeFromWatchlist(contentId, contentTitle) {
    if (confirm(`Remove "${contentTitle}" from your watchlist?`)) {
        window.location.href = `watchlist.php?remove=${contentId}`;
    }
}

// Initialize watchlist page
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects
    const watchlistCards = document.querySelectorAll('.watchlist-card');
    watchlistCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>