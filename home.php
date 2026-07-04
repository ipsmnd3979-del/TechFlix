
<?php
require_once 'includes/header.php';

// Fetch active banners for home page
$home_banners_query = "SELECT * FROM banners WHERE type = 'home' AND is_active = 1 ORDER BY created_at DESC LIMIT 1";
$home_banners_result = $conn->query($home_banners_query);
$home_banner = $home_banners_result->num_rows > 0 ? $home_banners_result->fetch_assoc() : null;

// If no banner found, use default content
if (!$home_banner) {
    $home_banner = [
        'title' => 'RADHE SHYAM (Hindi)',
        'subtitle' => '2022 U/A 16+ Romance',
        'button_text' => 'Watch Now',
        'button_link' => '#',
        'image' => './img/newdata/k.webp' // Default fallback image
    ];
}

// Fetch featured content - using newest content
$featuredQuery = "SELECT * FROM content ORDER BY created_at DESC LIMIT 12";
$featuredResult = $conn->query($featuredQuery);

// Fetch trending content - using random order
$trendingQuery = "SELECT * FROM content ORDER BY RAND() LIMIT 12";
$trendingResult = $conn->query($trendingQuery);
?>

<div class="page-content">
    <!-- Hero Banner -->
    <div class="hero-banner" style="background-image: url('<?php echo $home_banner['image']; ?>');">
        <div class="hero-content">
            <div class="hero-tag">PREMIERING NOW</div>
            <h1><?php echo htmlspecialchars($home_banner['title']); ?></h1>
            <p><?php echo htmlspecialchars($home_banner['subtitle']); ?></p>
            <p>Starring: Prabhas, Pooja Hegde, and Sathyaraj</p>
            <p>Convinced he isn't destined for love, a renowned palmist must question everything he believes when he falls for a doctor with an uncertain future.</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="window.location.href='<?php echo $home_banner['button_link']; ?>'">
                    <i class="fas fa-play"></i> <?php echo htmlspecialchars($home_banner['button_text']); ?>
                </button>
                <button class="btn btn-outline" onclick="addFeaturedToWatchlist()">
                    <i class="fas fa-plus"></i> Add to Watchlist
                </button>
            </div>
        </div>
    </div>

    <!-- Featured Content Section -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star"></i> Latest Content</h2>
            <a href="browse.php" class="view-all">View All</a>
        </div>
        <div class="slider-container">
            <div class="slider" id="featured-slider">
                <?php if ($featuredResult && $featuredResult->num_rows > 0): ?>
                    <?php while($content = $featuredResult->fetch_assoc()): ?>
                    <div class="content-card" data-content-id="<?php echo $content['id']; ?>" onclick="navigateToContent(<?php echo $content['id']; ?>)">
                        <img src="<?php echo getCorrectImagePath($content); ?>" 
                             alt="<?php echo htmlspecialchars($content['title']); ?>" 
                             class="card-img">
                        <div class="card-overlay">
                            <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                            <div class="info">
                                <span class="rating"><i class="fas fa-star"></i> <?php echo $content['rating'] ?? 'N/A'; ?></span>
                                <span><?php echo $content['release_year'] ?? 'N/A'; ?></span>
                            </div>
                            <!-- <div class="card-actions">
                                <button class="btn-play" onclick="event.stopPropagation(); playContent(<?php echo $content['id']; ?>)">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn-watchlist" onclick="event.stopPropagation(); addToWatchlist(<?php echo $content['id']; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div> -->
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-content-message">
                        <p>No content available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($featuredResult && $featuredResult->num_rows > 0): ?>
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="slideFeatured('prev')"><i class="fas fa-chevron-left"></i></button>
                <button class="slider-btn next-btn" onclick="slideFeatured('next')"><i class="fas fa-chevron-right"></i></button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Trending Now Section -->
    <section class="content-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-fire"></i> Trending Now</h2>
            <a href="browse.php" class="view-all">View All</a>
        </div>
        <div class="trending-grid">
            <?php if ($trendingResult && $trendingResult->num_rows > 0): ?>
                <?php while($content = $trendingResult->fetch_assoc()): ?>
                <div class="trending-card" data-content-id="<?php echo $content['id']; ?>" onclick="navigateToContent(<?php echo $content['id']; ?>)">
                    <?php
                    // Manual image path resolution
                    $imagePath = 'assets/img/default-poster.jpg'; // Default fallback
                    
                    if (function_exists('getCorrectImagePath')) {
                        $imagePath = getCorrectImagePath($content);
                    } else {
                        // Fallback logic
                        if (!empty($content['poster_path'])) {
                            $imagePath = $content['poster_path'];
                        } elseif (!empty($content['backdrop_path'])) {
                            $imagePath = $content['backdrop_path'];
                        } elseif (!empty($content['image_path'])) {
                            $imagePath = $content['image_path'];
                        }
                    }
                    ?>
                    <img src="<?php echo $imagePath; ?>" 
                         alt="<?php echo htmlspecialchars($content['title']); ?>"
                         onerror="this.src='assets/img/default-poster.jpg'"
                         class="trending-img">
                    <div class="card-overlay">
                        <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                        <div class="info-line">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo $content['rating'] ?? 'N/A'; ?></span>
                            </div>
                            <span class="year"><?php echo $content['release_year'] ?? 'N/A'; ?></span>
                        </div>
                        <!-- <div class="card-actions">
                            <button class="btn-play" onclick="event.stopPropagation(); playContent(<?php echo $content['id']; ?>)">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn-watchlist" onclick="event.stopPropagation(); addToWatchlist(<?php echo $content['id']; ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div> -->
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-content-message">
                    <p>No trending content available.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
// Navigate to video player
function playContent(contentId) {
    window.location.href = 'player.php?id=' + contentId;
}

// Watchlist function
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
    .catch(error => {
        console.error('Watchlist error:', error);
        showNotification('Added to watchlist!', 'success');
    });
}

// Featured content functions
function playFeaturedContent(contentId) {
    if (contentId) {
        window.location.href = 'player.php?id=' + contentId;
    }
}

function addFeaturedToWatchlist(contentId) {
    if (contentId) addToWatchlist(contentId);
}

// Slider functionality
function slideFeatured(direction) {
    const slider = document.getElementById('featured-slider');
    if (!slider) return;
    
    const scrollAmount = 300;
    if (direction === 'next') {
        slider.scrollLeft += scrollAmount;
    } else {
        slider.scrollLeft -= scrollAmount;
    }
}

// Notification system
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
        animation: slideIn 0.3s ease;d
        max-width: 300px;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .trending-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
    }
    
    .trending-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(229, 9, 20, 0.3);
    }
    
    .trending-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .trending-card:hover .trending-img {
        transform: scale(1.05);
    }
    
    .card-actions {
        position: absolute;
        bottom: 10px;
        right: 10px;
        display: flex;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .trending-card:hover .card-actions {
        opacity: 1;
    }
    
    .btn-play, .btn-watchlist {
        background: rgba(229, 9, 20, 0.9);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-play:hover, .btn-watchlist:hover {
        background: #e50914;
        transform: scale(1.1);
    }
    
    .content-card {
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    
    .content-card:hover {
        transform: scale(1.05);
    }
    
    .info-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-top: 8px;
    }
    
    .year {
        color: #ccc;
        font-size: 0.9rem;
    }
`;
document.head.appendChild(style);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded successfully');
    
    // Auto-scroll for featured slider
    const featuredSlider = document.getElementById('featured-slider');
    if (featuredSlider && featuredSlider.children.length > 1) {
        let autoScroll = setInterval(() => {
            if (featuredSlider.scrollLeft + featuredSlider.clientWidth >= featuredSlider.scrollWidth) {
                featuredSlider.scrollLeft = 0;
            } else {
                featuredSlider.scrollLeft += 300;
            }
        }, 5000);

        featuredSlider.addEventListener('mouseenter', () => clearInterval(autoScroll));
        featuredSlider.addEventListener('mouseleave', () => {
            autoScroll = setInterval(() => {
                if (featuredSlider.scrollLeft + featuredSlider.clientWidth >= featuredSlider.scrollWidth) {
                    featuredSlider.scrollLeft = 0;
                } else {
                    featuredSlider.scrollLeft += 300;
                }
            }, 5000);
        });
    }
    
    // Image error handling
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            console.log('Image failed to load:', this.src);
            this.src = 'https://via.placeholder.com/300x450/333/fff?text=Image+Not+Found';
        });
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close any open modals or overlays
            const notifications = document.querySelectorAll('[style*="position: fixed"]');
            notifications.forEach(notification => {
                if (notification.textContent.includes('watchlist') || notification.textContent.includes('Added')) {
                    document.body.removeChild(notification);
                }
            });
        }
    });
});

// Enhanced slider function
function slideFeatured(direction) {
    const slider = document.getElementById('featured-slider');
    if (!slider) return;
    
    const scrollAmount = 300;
    const currentScroll = slider.scrollLeft;
    const maxScroll = slider.scrollWidth - slider.clientWidth;
    
    if (direction === 'next') {
        if (currentScroll >= maxScroll - 10) {
            // If at end, scroll to start
            slider.scrollLeft = 0;
        } else {
            slider.scrollLeft += scrollAmount;
        }
    } else if (direction === 'prev') {
        if (currentScroll <= 10) {
            // If at start, scroll to end
            slider.scrollLeft = maxScroll;
        } else {
            slider.scrollLeft -= scrollAmount;
        }
    }
}
</script>

<?php
// CORRECTED image function based on your test results
function getCorrectImagePath($content) {
    // Your test shows thumbnails are stored in: ../assets/uploads/thumbnails/
    // But poster_image always shows: assets/img/default-poster.jpg (which doesn't exist)
    
    // Use thumbnail first (this is where your actual images are)
    if (!empty($content['thumbnail'])) {
        $thumbnail = $content['thumbnail'];
        
        // Fix the path - remove the ../ if present
        if (strpos($thumbnail, '../') === 0) {
            $thumbnail = substr($thumbnail, 3); // Remove ../ from start
        }
        
        // Check if file exists
        if (file_exists($thumbnail)) {
            return $thumbnail;
        }
        
        // If path doesn't work, try direct path
        $directPath = 'assets/uploads/thumbnails/' . basename($thumbnail);
        if (file_exists($directPath)) {
            return $directPath;
        }
        
        return $thumbnail; // Return the original path anyway
    }
    
    // Try poster_image as fallback (but your test shows it's always default)
    if (!empty($content['poster_image']) && $content['poster_image'] !== 'assets/img/default-poster.jpg') {
        return $content['poster_image'];
    }
    
    // Final fallback - use the actual uploaded thumbnails by filename
    if (!empty($content['thumbnail'])) {
        $filename = basename($content['thumbnail']);
        return 'assets/uploads/thumbnails/' . $filename;
    }
    
    // Ultimate fallback - placeholder
    return 'https://via.placeholder.com/300x450/333/fff?text=' . urlencode(substr($content['title'], 0, 15));
}

require_once 'includes/footer.php'; 
?>
