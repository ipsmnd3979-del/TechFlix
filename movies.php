<?php
require_once 'includes/header.php';

// Safe query helper - returns result or false with error logged
function safe_query($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) {
        error_log("Query failed: " . $conn->error . " | SQL: " . $sql);
    }
    return $result;
}

// Image path helper (defined once here; other pages define their own)
if (!function_exists('getCorrectImagePath')) {
    function getCorrectImagePath($content) {
        if (!empty($content['thumbnail'])) {
            $thumbnail = $content['thumbnail'];
            if (strpos($thumbnail, '../') === 0) {
                $thumbnail = substr($thumbnail, 3);
            }
            if (file_exists($thumbnail)) return $thumbnail;
            $filename = basename($thumbnail);
            $directPath = 'assets/uploads/thumbnails/' . $filename;
            if (file_exists($directPath)) return $directPath;
            return $thumbnail;
        }
        if (!empty($content['poster_image']) && $content['poster_image'] !== 'assets/img/default-poster.jpg') {
            return $content['poster_image'];
        }
        return 'https://via.placeholder.com/300x450/333/fff?text=' . urlencode(substr($content['title'] ?? 'Movie', 0, 15));
    }
}

// Get movies
$movies_result = safe_query($conn,
    "SELECT c.*, cat.name as category_name
     FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.type = 'movie'
     ORDER BY c.rating DESC, c.created_at DESC
     LIMIT 50"
);

// Get featured movies (rating >= 8.0)
$featured_movies_result = safe_query($conn,
    "SELECT c.*, cat.name as category_name
     FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.type = 'movie' AND c.rating >= 8.0
     ORDER BY c.rating DESC
     LIMIT 10"
);

// Get new movies
$new_movies_result = safe_query($conn,
    "SELECT c.*, cat.name as category_name
     FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.type = 'movie'
     ORDER BY c.created_at DESC
     LIMIT 10"
);

// Get action movies
$action_movies_result = safe_query($conn,
    "SELECT c.*, cat.name as category_name
     FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.type = 'movie' AND cat.name = 'Action'
     ORDER BY c.rating DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - TechFlix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="page-content">
    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <div class="hero-tag">BLOCKBUSTER MOVIES</div>
            <h1>THAMMA</h1>
            <p>Thamma is an upcoming 2025 Indian Hindi-language romantic comedy horror film directed by Aditya Sarpotdar.</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="playContent(1, 'Thamma')">
                    <i class="fas fa-play"></i> Watch Now
                </button>
                <button class="btn btn-outline" onclick="window.location.href='#featured'">
                    <i class="fas fa-compass"></i> Explore Movies
                </button>
            </div>
        </div>
    </div>

    <!-- Featured Movies -->
    <section class="content-section" id="featured">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star"></i> Featured Movies</h2>
            <a href="browse.php?type=movie" class="view-all">View All Movies</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="featured-slider">
                <?php if ($featured_movies_result && $featured_movies_result->num_rows > 0): ?>
                    <?php while($movie = $featured_movies_result->fetch_assoc()): ?>
                    <div class="content-card" data-content-id="<?php echo (int)$movie['id']; ?>">
                        <img src="<?php echo htmlspecialchars(getCorrectImagePath($movie)); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="card-img">
                        <div class="card-overlay">
                            <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="info">
                                <span class="rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($movie['rating']); ?></span>
                                <span><?php echo htmlspecialchars($movie['release_year']); ?></span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-play" onclick="playContent(<?php echo (int)$movie['id']; ?>, '<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn-watchlist" onclick="addToWatchlist(<?php echo (int)$movie['id']; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding: 40px; text-align: center; width: 100%;">
                        <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Featured Movies Yet</h3>
                        <p>Check back soon for highly-rated movies!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="slideFeatured('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next-btn" onclick="slideFeatured('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- New Releases -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-calendar-plus"></i> New Releases</h2>
            <a href="browse.php?type=movie&sort=newest" class="view-all">View All New</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="new-releases-slider">
                <?php if ($new_movies_result && $new_movies_result->num_rows > 0): ?>
                    <?php while($movie = $new_movies_result->fetch_assoc()): ?>
                    <div class="content-card" data-content-id="<?php echo (int)$movie['id']; ?>">
                        <img src="<?php echo htmlspecialchars(getCorrectImagePath($movie)); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="card-img">
                        <div class="card-overlay">
                            <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="info">
                                <span class="rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($movie['rating']); ?></span>
                                <span>NEW</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-play" onclick="playContent(<?php echo (int)$movie['id']; ?>, '<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn-watchlist" onclick="addToWatchlist(<?php echo (int)$movie['id']; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding: 40px; text-align: center; width: 100%;">
                        <i class="fas fa-calendar-plus" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No New Releases</h3>
                        <p>New movies will be added soon!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="slideNewReleases('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next-btn" onclick="slideNewReleases('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Action Movies -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-fire"></i> Action Movies</h2>
            <a href="browse.php?type=movie&category=action" class="view-all">View All Action</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="action-slider">
                <?php if ($action_movies_result && $action_movies_result->num_rows > 0): ?>
                    <?php while($movie = $action_movies_result->fetch_assoc()): ?>
                    <div class="content-card" data-content-id="<?php echo (int)$movie['id']; ?>">
                        <img src="<?php echo htmlspecialchars(getCorrectImagePath($movie)); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="card-img">
                        <div class="card-overlay">
                            <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="info">
                                <span class="rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($movie['rating']); ?></span>
                                <span>Action</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-play" onclick="playContent(<?php echo (int)$movie['id']; ?>, '<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn-watchlist" onclick="addToWatchlist(<?php echo (int)$movie['id']; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <i class="fas fa-film" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <h3>No Action Movies</h3>
                        <p>Action movies will be added soon!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="slideAction('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next-btn" onclick="slideAction('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- All Movies Grid -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-film"></i> All Movies</h2>
            <a href="browse.php?type=movie" class="view-all">Browse All</a>
        </div>

        <div class="trending-grid">
            <?php
            $movies_counter = 0;
            if ($movies_result && $movies_result->num_rows > 0):
                while($movie = $movies_result->fetch_assoc()):
                    $movies_counter++;
                    if ($movies_counter > 16) break;
            ?>
            <div class="trending-card" data-content-id="<?php echo (int)$movie['id']; ?>">
                <img src="<?php echo htmlspecialchars(getCorrectImagePath($movie)); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                <div class="card-overlay">
                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    <div class="info-line">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo htmlspecialchars($movie['rating']); ?>/10</span>
                        </div>
                        <div class="year">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo htmlspecialchars($movie['release_year']); ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-play" onclick="playContent(<?php echo (int)$movie['id']; ?>, '<?php echo htmlspecialchars($movie['title'], ENT_QUOTES); ?>')">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn-watchlist" onclick="addToWatchlist(<?php echo (int)$movie['id']; ?>)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php
                endwhile;
            endif;
            ?>

            <?php if ($movies_counter === 0): ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-film" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>No Movies Available</h3>
                    <p>Movies will be added to the platform soon.</p>
                    <a href="browse.php" class="btn btn-primary" style="margin-top: 20px;">Browse All Content</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Movie Genres -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-tags"></i> Movie Genres</h2>
        </div>
        <div class="category-filter">
            <button class="category-btn active" onclick="window.location.href='movies.php'">All Genres</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Action'">Action</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Comedy'">Comedy</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Drama'">Drama</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Sci-Fi'">Sci-Fi</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Horror'">Horror</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Romance'">Romance</button>
            <button class="category-btn" onclick="window.location.href='browse.php?type=movie&category=Fantasy'">Fantasy</button>
        </div>
    </section>
</div>

<script>
    // Slider functionality
    function slideFeatured(direction) {
        const slider = document.getElementById('featured-slider');
        slider.scrollLeft += direction === 'next' ? 300 : -300;
    }

    function slideNewReleases(direction) {
        const slider = document.getElementById('new-releases-slider');
        slider.scrollLeft += direction === 'next' ? 300 : -300;
    }

    function slideAction(direction) {
        const slider = document.getElementById('action-slider');
        slider.scrollLeft += direction === 'next' ? 300 : -300;
    }

    // Note: playContent(contentId, contentTitle) is provided by includes/footer.php
    // and takes precedence over any same-named function declared here.

    // Add to watchlist via AJAX
    function addToWatchlist(contentId) {
        fetch('add_to_watchlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'content_id=' + encodeURIComponent(contentId)
        })
        .then(response => response.json())
        .then(data => {
            showNotification(data.message || 'Added to watchlist!', data.success ? 'success' : 'error');
        })
        .catch(() => {
            showNotification('Added to watchlist!', 'success');
        });
    }

    // Simple notification helper
    function showNotification(message, type) {
        const existing = document.querySelector('.tf-notification');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = 'tf-notification';
        div.textContent = message;
        div.style.cssText = 'position:fixed;bottom:20px;right:20px;background:' +
            (type === 'success' ? '#28a745' : '#dc3545') +
            ';color:#fff;padding:12px 20px;border-radius:8px;z-index:9999;font-weight:600;box-shadow:0 4px 15px rgba(0,0,0,0.3);';
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Movies page loaded successfully');
    });
</script>

<?php require_once 'includes/footer.php'; ?>
