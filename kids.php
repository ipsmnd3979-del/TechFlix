<?php
require_once 'includes/header.php';

// Query kids-type content first; fall back to all content if none found
$kidsQuery = "SELECT c.*, cat.name as category_name
              FROM content c
              LEFT JOIN categories cat ON c.category_id = cat.id
              WHERE c.type = \'kids\'
              ORDER BY c.rating DESC, c.created_at DESC
              LIMIT 12";
$kidsResult = $conn->query($kidsQuery);

// If no kids-specific content, show all content (fallback for demo setups)
if (!$kidsResult || $kidsResult->num_rows === 0) {
    $kidsQuery = "SELECT c.*, cat.name as category_name
                  FROM content c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  ORDER BY RAND()
                  LIMIT 12";
    $kidsResult = $conn->query($kidsQuery);
}

// Check if query was successful
if (!$kidsResult) {
    error_log("Kids content query failed: " . $conn->error);
    $kidsResult = null;
}

// Get available columns for dynamic content display
$columns = [];
if ($kidsResult) {
    $fields = $kidsResult->fetch_fields();
    foreach ($fields as $field) {
        $columns[] = $field->name;
    }
    // Reset pointer
    $kidsResult->data_seek(0);
}
?>

<div class="page-content">
    <!-- Kids Hero Banner -->
    <div class="hero-banner kids-hero">
        <div class="hero-content">
            <div class="hero-tag">KIDS ZONE</div>
            <h1>Kung Fu Panda</h1>
            <p>Legendary warrior Po teams up with an elite English knight on a global quest to rescue magical weapons, restore his reputation — and save the world!</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="exploreKidsContent()">
                    <i class="fas fa-play"></i> Start Watching
                </button>
                <button class="btn btn-outline" onclick="showKidsCategories()">
                    <i class="fas fa-list"></i> Browse Categories
                </button>
            </div>
        </div>
    </div>

    <!-- Featured Kids Content -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star"></i> Kids Shows & Movies</h2>
            <a href="browse.php" class="view-all">View All</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="featured-kids-slider">
                <?php if ($kidsResult && $kidsResult->num_rows > 0): ?>
                    <?php while ($content = $kidsResult->fetch_assoc()): ?>
                        <div class="content-card kids-card" data-content-id="<?php echo $content['id']; ?>">
                            <img src="<?php echo isset($content['poster_image']) && !empty($content['poster_image']) ? $content['poster_image'] : 'assets/img/default-poster.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($content['title']); ?>"
                                class="card-img"
                                onerror="this.src='assets/img/default-poster.jpg'">
                            <div class="card-overlay">
                                <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                                <div class="info">
                                    <?php if (in_array('rating', $columns) && isset($content['rating'])): ?>
                                        <span class="rating"><i class="fas fa-star"></i> <?php echo $content['rating']; ?></span>
                                    <?php endif; ?>
                                    <?php if (in_array('release_year', $columns) && isset($content['release_year'])): ?>
                                        <span><?php echo $content['release_year']; ?></span>
                                    <?php endif; ?>
                                    <span class="age-rating">Kids</span>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-play" onclick="playContent(<?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title']); ?>')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn-watchlist" onclick="addToWatchlist(<?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title']); ?>')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-content-message">
                        <p>No content available yet. Here are some sample kids shows:</p>
                        <div class="sample-content">
                            <!-- Sample fallback content -->
                            <div class="content-card kids-card">
                                <img src="assets/img/default-poster.jpg" alt="Animal Adventures" class="card-img">
                                <div class="card-overlay">
                                    <h3>Animal Adventures</h3>
                                    <div class="info">
                                        <span class="rating"><i class="fas fa-star"></i> 4.5</span>
                                        <span class="age-rating">All Ages</span>
                                    </div>
                                </div>
                            </div>
                            <div class="content-card kids-card">
                                <img src="assets/img/default-poster.jpg" alt="Space Explorers" class="card-img">
                                <div class="card-overlay">
                                    <h3>Space Explorers</h3>
                                    <div class="info">
                                        <span class="rating"><i class="fas fa-star"></i> 4.8</span>
                                        <span class="age-rating">6+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="btn btn-primary">Add Content</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($kidsResult && $kidsResult->num_rows > 0): ?>
                <div class="slider-controls">
                    <button class="slider-btn prev-btn" onclick="slideKids('prev')"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-btn next-btn" onclick="slideKids('next')"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Kids Categories -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-layer-group"></i> Kids Categories</h2>
        </div>
        <div class="categories-grid">
            <div class="category-card" onclick="filterKidsContent('cartoons')">
                <div class="category-icon">
                    <i class="fas fa-film"></i>
                </div>
                <h3>Cartoons</h3>
                <p>Animated fun for everyone</p>
            </div>
            <div class="category-card" onclick="filterKidsContent('educational')">
                <div class="category-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Educational</h3>
                <p>Learn while having fun</p>
            </div>
            <div class="category-card" onclick="filterKidsContent('adventure')">
                <div class="category-icon">
                    <i class="fas fa-hiking"></i>
                </div>
                <h3>Adventure</h3>
                <p>Exciting journeys</p>
            </div>
            <div class="category-card" onclick="filterKidsContent('music')">
                <div class="category-icon">
                    <i class="fas fa-music"></i>
                </div>
                <h3>Music & Songs</h3>
                <p>Sing and dance along</p>
            </div>
        </div>
    </section>

    <!-- Age Groups -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-child"></i> Browse by Age</h2>
        </div>
        <div class="age-groups">
            <div class="age-group" onclick="filterByAge('3-5')">
                <div class="age-icon">3-5</div>
                <h4>Preschool</h4>
                <p>Ages 3 to 5</p>
            </div>
            <div class="age-group" onclick="filterByAge('6-8')">
                <div class="age-icon">6-8</div>
                <h4>Early Elementary</h4>
                <p>Ages 6 to 8</p>
            </div>
            <div class="age-group" onclick="filterByAge('9-12')">
                <div class="age-icon">9-12</div>
                <h4>Elementary</h4>
                <p>Ages 9 to 12</p>
            </div>
            <div class="age-group" onclick="filterByAge('family')">
                <div class="age-icon"><i class="fas fa-users"></i></div>
                <h4>Family</h4>
                <p>All ages together</p>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-rocket"></i> Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <div class="action-card" onclick="window.location.href='browse.php'">
                <i class="fas fa-compass"></i>
                <span>Browse All</span>
            </div>
            <div class="action-card" onclick="window.location.href='movies.php'">
                <i class="fas fa-film"></i>
                <span>Movies</span>
            </div>
            <div class="action-card" onclick="window.location.href='tvshows.php'">
                <i class="fas fa-tv"></i>
                <span>TV Shows</span>
            </div>
            <?php if ($isLoggedIn): ?>
                <div class="action-card" onclick="window.location.href='watchlist.php'">
                    <i class="fas fa-bookmark"></i>
                    <span>My Watchlist</span>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
    .kids-hero {
        background-image: url(./img/newdata/k.webp);
    }

    .kids-card {
        border-radius: 15px;
        overflow: hidden;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .category-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s ease, background 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .category-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.2);
    }

    .category-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: #ff6b6b;
    }

    .age-groups {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .age-group {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .age-group:hover {
        transform: scale(1.05);
    }

    .age-icon {
        font-size: 1.5rem;
        font-weight: bold;
        background: #ff6b6b;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }

    .age-rating {
        background: #4ecdc4;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .action-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s ease, background 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .action-card:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.2);
    }

    .action-card i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #ff6b6b;
    }

    .action-card span {
        display: block;
        font-weight: bold;
    }

    .sample-content {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
        justify-content: center;
    }

    .sample-content .content-card {
        width: 200px;
    }
</style>

<script>
    // Kids page specific functionality
    function slideKids(direction) {
        const slider = document.getElementById('featured-kids-slider');
        const scrollAmount = 300;

        if (direction === 'next') {
            slider.scrollLeft += scrollAmount;
        } else {
            slider.scrollLeft -= scrollAmount;
        }
    }

    // Navigate to video player
    function playContent(contentId) {
        window.location.href = 'player.php?id=' + contentId;
    }

    // Add to watchlist via AJAX
    function addToWatchlist(contentId) {
        fetch('add_to_watchlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'content_id=' + encodeURIComponent(contentId)
        })
        .then(r => r.json())
        .then(data => showNotification(data.message || 'Added to watchlist!', data.success ? 'success' : 'error'))
        .catch(() => showNotification('Added to watchlist!', 'success'));
    }

    // Notification helper
    function showNotification(message, type) {
        const existing = document.querySelector('.tf-notification');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = 'tf-notification';
        div.textContent = message;
        const colors = { success: '#28a745', error: '#dc3545', info: '#17a2b8' };
        div.style.cssText = 'position:fixed;bottom:20px;right:20px;background:' +
            (colors[type] || colors.info) +
            ';color:#fff;padding:12px 20px;border-radius:8px;z-index:9999;font-weight:600;box-shadow:0 4px 15px rgba(0,0,0,0.3);';
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    function exploreKidsContent() {
        window.location.href = 'browse.php?type=kids';
    }

    function showKidsCategories() {
        document.querySelector('.categories-grid').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function filterKidsContent(category) {
        showNotification(`Showing ${category} content`, 'info');
        // In a real implementation, this would filter the content
        window.location.href = `browse.php?filter=${category}`;
    }

    function filterByAge(ageGroup) {
        showNotification(`Showing content for ${ageGroup}`, 'info');
        window.location.href = `browse.php?age=${ageGroup}`;
    }

    // Initialize kids page
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll kids slider
        const kidsSlider = document.getElementById('featured-kids-slider');
        if (kidsSlider && kidsSlider.children.length > 1) {
            let autoScroll = setInterval(() => {
                if (kidsSlider.scrollLeft + kidsSlider.clientWidth >= kidsSlider.scrollWidth) {
                    kidsSlider.scrollLeft = 0;
                } else {
                    kidsSlider.scrollLeft += 300;
                }
            }, 4000);

            // Pause auto-scroll on hover
            kidsSlider.addEventListener('mouseenter', () => {
                clearInterval(autoScroll);
            });

            kidsSlider.addEventListener('mouseleave', () => {
                autoScroll = setInterval(() => {
                    if (kidsSlider.scrollLeft + kidsSlider.clientWidth >= kidsSlider.scrollWidth) {
                        kidsSlider.scrollLeft = 0;
                    } else {
                        kidsSlider.scrollLeft += 300;
                    }
                }, 4000);
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>