<?php
require_once 'includes/header.php';

// Guard against redeclaration when multiple pages define this helper
if (!function_exists('getCorrectImagePath')) {
function getCorrectImagePath($content)
{
    // Use thumbnail first (where actual images are stored)
    if (!empty($content['thumbnail'])) {
        $thumbnail = $content['thumbnail'];

        // Fix the path - remove ../ if present
        if (strpos($thumbnail, '../') === 0) {
            $thumbnail = substr($thumbnail, 3);
        }

        // Check if file exists
        if (file_exists($thumbnail)) {
            return $thumbnail;
        }

        // Try direct path to uploads
        $filename = basename($thumbnail);
        $directPath = 'assets/uploads/thumbnails/' . $filename;
        if (file_exists($directPath)) {
            return $directPath;
        }

        return $thumbnail;
    }

    // Try poster_image as fallback
    if (!empty($content['poster_image']) && $content['poster_image'] !== 'assets/img/default-poster.jpg') {
        return $content['poster_image'];
    }

    // Final fallback - placeholder
    return 'https://via.placeholder.com/300x450/333/fff?text=' . urlencode(substr($content['title'] ?? '', 0, 15));
}
} // end function_exists

// Get TV shows
$tv_shows_query = "SELECT c.*, cat.name as category_name 
                   FROM content_new c
                   LEFT JOIN categories cat ON c.category_id = cat.id 
                   WHERE c.type = 'tv_show' 
                   ORDER BY c.rating DESC, c.created_at DESC 
                   LIMIT 50";
$tv_shows_result = $conn->query($tv_shows_query);

// Get featured TV shows
$featured_tv_query = "SELECT c.*, cat.name as category_name 
                      FROM content_new c
                      LEFT JOIN categories cat ON c.category_id = cat.id 
                      WHERE c.type = 'tv_show' AND c.rating >= 8.5 
                      ORDER BY c.rating DESC 
                      LIMIT 10";
$featured_tv_result = $conn->query($featured_tv_query);

// Get TV show seasons
$seasons_query = "SELECT s.*, c.title as show_title
                  FROM seasons s
                  JOIN content_new c ON s.content_id = c.id
                  WHERE c.type = 'tv_show'
                  ORDER BY s.season_number
                  LIMIT 5";
$seasons_result = $conn->query($seasons_query);

// Initialize banners variables
$banners_result = null;
$banners_error = null;
// Get featured TV show with video
$banner_tv_query = "SELECT c.*, cat.name as category_name 
                   FROM content_new c
                   LEFT JOIN categories cat ON c.category_id = cat.id 
                   WHERE c.type = 'tv_show' 
                   ORDER BY c.rating DESC, c.created_at DESC 
                   LIMIT 1";
$banner_tv_result = $conn->query($banner_tv_query);
$banner_show = ($banner_tv_result && $banner_tv_result->num_rows > 0) ? $banner_tv_result->fetch_assoc() : null;

// Safe defaults if no show found
$banner_title       = $banner_show ? htmlspecialchars($banner_show['title'])        : 'TechFlix TV Shows';
$banner_year        = $banner_show ? htmlspecialchars($banner_show['release_year'])  : '';
$banner_category    = $banner_show ? htmlspecialchars($banner_show['category_name'] ?? '') : '';
$banner_description = $banner_show ? htmlspecialchars($banner_show['description'] ?? '') : 'Stream the latest TV shows exclusively on TechFlix.';
$banner_id          = $banner_show ? (int)$banner_show['id'] : 0;
$banner_bg          = $banner_show ? htmlspecialchars(getCorrectImagePath($banner_show)) : '';
?>

<div class="page-content">
    <!-- Hero Banner -->
    <div class="hero-banner" style="position: relative; overflow: hidden;">
        <!-- Background Image -->
        <?php if ($banner_bg): ?>
        <div class="hero-background" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo $banner_bg; ?>'); background-size: cover; background-position: center;"></div>
        <?php endif; ?>

        <!-- Gradient Overlay -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(45deg, rgba(141,45,226,0.5), rgba(75,0,224,0.3));"></div>

        <div class="hero-content" style="position: relative; z-index: 2;">
            <div class="hero-tag">NEW SEASON</div>
            <h1><?php echo $banner_title; ?></h1>
            <?php if ($banner_year || $banner_category): ?>
            <p><?php echo $banner_year; ?><?php echo ($banner_year && $banner_category) ? ' • ' : ''; ?><?php echo $banner_category; ?></p>
            <?php endif; ?>
            <p><?php echo $banner_description; ?></p>
            <div class="hero-buttons">
                <?php if ($banner_id): ?>
                <button class="btn btn-primary" onclick="playContent(<?php echo $banner_id; ?>, '<?php echo addslashes($banner_title); ?>')">
                    <i class="fas fa-play"></i> Watch Now
                </button>
                <button class="btn btn-outline" onclick="addToWatchlist(<?php echo $banner_id; ?>)">
                    <i class="fas fa-plus"></i> Add to Watchlist
                </button>
                <?php else: ?>
                <button class="btn btn-primary" onclick="window.location.href='browse.php?type=tv_show'">
                    <i class="fas fa-tv"></i> Browse Shows
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Auto Slider -->
    <div class="auto-slider-container">
        <div class="auto-slider" id="auto-slider">
            <div class="auto-slide" style="background: linear-gradient(45deg, #FF416C, #FF4B2B);">
                <div class="auto-slide-content">
                    <h2>New Episodes Every Friday</h2>
                    <p>Don't miss the latest episodes of your favorite shows. Available exclusively on TechFlix.</p>
                    <button class="btn btn-primary">View Schedule</button>
                </div>
            </div>
            <div class="auto-slide" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                <div class="auto-slide-content">
                    <h2>Binge-Worthy Series</h2>
                    <p>Discover our collection of complete series ready for your next binge-watching session.</p>
                    <button class="btn btn-primary">Explore Series</button>
                </div>
            </div>
            <div class="auto-slide" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                <div class="auto-slide-content">
                    <h2>Family Friendly Shows</h2>
                    <p>Find the perfect shows for family movie nights in our curated collection.</p>
                    <button class="btn btn-primary">Browse Family Shows</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured TV Shows -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star"></i> Featured Series</h2>
            <a href="browse.php?type=tv_show" class="view-all">View All</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="featured-slider">
                <?php if ($featured_tv_result && $featured_tv_result->num_rows > 0): ?>
                <?php while ($show = $featured_tv_result->fetch_assoc()): ?>
                    <div class="content-card" data-content-id="<?php echo $show['id']; ?>">
                        <img src="<?php echo getCorrectImagePath($show); ?>"
                            alt="<?php echo $show['title']; ?>"
                            class="card-img">
                        <div class="card-overlay">
                            <h3><?php echo $show['title']; ?></h3>
                            <div class="info">
                                <span class="rating"><i class="fas fa-star"></i> <?php echo $show['rating']; ?></span>
                                <span><?php echo $show['release_year']; ?></span>
                            </div>
                            <div class="card-actions">
                                <button class="btn-play" onclick="playContent(<?php echo $show['id']; ?>, '<?php echo addslashes($show['title']); ?>')">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn-watchlist" onclick="addToWatchlist(<?php echo $show['id']; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding:30px;text-align:center;color:#aaa;width:100%;"><i class="fas fa-tv" style="font-size:2rem;margin-bottom:10px;opacity:0.5;display:block;"></i><p>No featured shows available yet.</p></div>
                <?php endif; ?>
            </div>
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="slideFeatured('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next-btn" onclick="slideFeatured('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- Popular TV Shows -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-fire"></i> Popular Shows</h2>
            <a href="browse.php?type=tv_show&sort=popular" class="view-all">View All</a>
        </div>
        <div class="trending-grid">
            <?php
            $popular_counter = 0;
            if ($tv_shows_result && $tv_shows_result->num_rows > 0):
            while ($show = $tv_shows_result->fetch_assoc()):
                $popular_counter++;
                if ($popular_counter > 12) break;
            ?>
                <div class="trending-card" data-content-id="<?php echo $show['id']; ?>">
                    <img src="<?php echo getCorrectImagePath($show); ?>"
                        alt="<?php echo $show['title']; ?>">
                    <div class="card-overlay">
                        <h3><?php echo $show['title']; ?></h3>
                        <div class="info-line">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo $show['rating']; ?>/10</span>
                            </div>
                            <div class="year">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo $show['release_year']; ?></span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-play" onclick="playContent(<?php echo $show['id']; ?>, '<?php echo addslashes($show['title']); ?>')">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn-watchlist" onclick="addToWatchlist(<?php echo $show['id']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:#aaa;"><i class="fas fa-tv" style="font-size:3rem;margin-bottom:15px;opacity:0.5;display:block;"></i><p>No TV shows available yet.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- TV Show Seasons Section -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-list"></i> Latest Seasons</h2>
        </div>

        <div class="season-selector">
            <button class="season-btn active">All Seasons</button>
            <button class="season-btn">Action</button>
            <button class="season-btn">Drama</button>
            <button class="season-btn">Sci-Fi</button>
            <button class="season-btn">Comedy</button>
        </div>

        <div class="episode-grid">
            <?php if ($seasons_result && $seasons_result->num_rows > 0): ?>
                <?php while ($season = $seasons_result->fetch_assoc()): ?>
                    <div class="episode-card">
                        <div class="episode-img" style="background: linear-gradient(45deg, #8E2DE2, #4A00E0); display: flex; justify-content: center; align-items: center; font-size: 3rem; color: white;">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="episode-info">
                            <div class="episode-title">
                                <span><?php echo $season['show_title']; ?></span>
                                <span class="episode-number">S<?php echo $season['season_number']; ?></span>
                            </div>
                            <p class="episode-desc"><?php echo $season['description'] ?: 'New season now available with exciting episodes.'; ?></p>
                            <button class="btn btn-primary" onclick="playContent(<?php echo $season['content_id']; ?>, '<?php echo addslashes($season['show_title']); ?>')">
                                <i class="fas fa-play"></i> Watch Now
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-tv"></i>
                    <h3>No Seasons Available</h3>
                    <p>No TV show seasons have been added yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Genres Section -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-tags"></i> Browse by Genre</h2>
        </div>
        <div class="category-filter">
            <button class="category-btn active" onclick="filterTVShows('all')">All</button>
            <button class="category-btn" onclick="filterTVShows('action')">Action</button>
            <button class="category-btn" onclick="filterTVShows('drama')">Drama</button>
            <button class="category-btn" onclick="filterTVShows('comedy')">Comedy</button>
            <button class="category-btn" onclick="filterTVShows('sci-fi')">Sci-Fi</button>
            <button class="category-btn" onclick="filterTVShows('fantasy')">Fantasy</button>
            <button class="category-btn" onclick="filterTVShows('mystery')">Mystery</button>
            <button class="category-btn" onclick="filterTVShows('romance')">Romance</button>
        </div>
    </section>
</div>

<script>
    // Video player functionality
function toggleMute() {
    const video = document.getElementById('heroVideo');
    const muteBtn = document.getElementById('muteBtn');
    
    if (video) {
        video.muted = !video.muted;
        if (video.muted) {
            muteBtn.innerHTML = '<i class="fas fa-volume-mute"></i> Unmute';
        } else {
            muteBtn.innerHTML = '<i class="fas fa-volume-up"></i> Mute';
        }
    }
}

// Handle video autoplay with user interaction
function initVideoAutoplay() {
    const video = document.getElementById('heroVideo');
    if (!video) return;
    
    // Try to play video (may require user interaction on some browsers)
    const playPromise = video.play();
    
    if (playPromise !== undefined) {
        playPromise.catch(error => {
            console.log('Auto-play prevented:', error);
            // Show play button instead
            const playOverlay = document.createElement('div');
            playOverlay.className = 'video-play-overlay';
            playOverlay.innerHTML = '<button onclick="playVideo()" class="btn btn-primary"><i class="fas fa-play"></i> Play Video</button>';
            playOverlay.style.position = 'absolute';
            playOverlay.style.top = '50%';
            playOverlay.style.left = '50%';
            playOverlay.style.transform = 'translate(-50%, -50%)';
            playOverlay.style.zIndex = '3';
            video.parentNode.appendChild(playOverlay);
        });
    }
}

function playVideo() {
    const video = document.getElementById('heroVideo');
    const overlay = document.querySelector('.video-play-overlay');
    
    if (video) {
        video.play();
        if (overlay) {
            overlay.remove();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initVideoAutoplay();
    
    // Your existing auto slider code
    const autoSlider = document.getElementById('auto-slider');
    if (autoSlider) {
        let currentSlide = 0;
        const slides = autoSlider.children;
        const totalSlides = slides.length;
        
        setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            autoSlider.scrollTo({
                left: currentSlide * autoSlider.offsetWidth,
                behavior: 'smooth'
            });
        }, 5000);
    }

    // Season selector functionality
    const seasonBtns = document.querySelectorAll('.season-btn');
    seasonBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            seasonBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

// Note: playContent(contentId, contentTitle) is provided by includes/footer.php
// and takes precedence over any same-named function declared here.

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

// Filter by genre - navigate to browse with type + category
function filterTVShows(genre) {
    if (genre === 'all') {
        window.location.href = 'browse.php?type=tv_show';
    } else {
        window.location.href = 'browse.php?type=tv_show&category=' + encodeURIComponent(genre);
    }
}

// Notification helper
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
</script>

<?php
require_once 'includes/footer.php';
?>