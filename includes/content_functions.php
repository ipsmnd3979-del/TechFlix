<?php
// Content-related functions for TechFlix

/**
 * Display banner slider on home page
 */
function displayBannerSlider() {
    global $conn;
    
    $query = "SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order LIMIT 5";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo '<div class="banner-slider">';
        echo '<div class="swiper bannerSwiper">';
        echo '<div class="swiper-wrapper">';
        
        while ($banner = $result->fetch_assoc()) {
            echo '<div class="swiper-slide banner-slide">';
            echo '<img src="' . htmlspecialchars($banner['image_url']) . '" alt="' . htmlspecialchars($banner['title']) . '">';
            echo '<div class="banner-content">';
            echo '<h2>' . htmlspecialchars($banner['title']) . '</h2>';
            if (!empty($banner['description'])) {
                echo '<p>' . htmlspecialchars($banner['description']) . '</p>';
            }
            if (!empty($banner['content_id'])) {
                echo '<button class="btn btn-primary" onclick="playContent(' . $banner['content_id'] . ', \'' . htmlspecialchars($banner['title']) . '\')">';
                echo '<i class="fas fa-play"></i> Play Now';
                echo '</button>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<div class="swiper-pagination"></div>';
        echo '<div class="swiper-button-next"></div>';
        echo '<div class="swiper-button-prev"></div>';
        echo '</div>';
        echo '</div>';
        
        // Initialize Swiper
        echo '<script>
            var bannerSwiper = new Swiper(".bannerSwiper", {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
            });
        </script>';
    } else {
        // Fallback banner if no banners in database
        echo '<div class="banner-slider">';
        echo '<div class="banner-slide default-banner">';
        echo '<div class="banner-content">';
        echo '<h2>Welcome to TechFlix</h2>';
        echo '<p>Your portal to the universe of entertainment</p>';
        echo '<button class="btn btn-primary" onclick="window.location.href=\'browse.php\'">';
        echo '<i class="fas fa-play"></i> Start Exploring';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Display content grid with category filtering
 */
function displayContentGrid($category = null, $limit = 12) {
    global $conn;
    
    if ($category) {
        $stmt = $conn->prepare("SELECT c.*, cat.name as category_name 
                               FROM content c 
                               LEFT JOIN categories cat ON c.category_id = cat.id 
                               WHERE c.category_id = ? AND c.is_active = 1 
                               ORDER BY c.created_at DESC 
                               LIMIT ?");
        $stmt->bind_param("ii", $category, $limit);
    } else {
        $stmt = $conn->prepare("SELECT c.*, cat.name as category_name 
                               FROM content c 
                               LEFT JOIN categories cat ON c.category_id = cat.id 
                               WHERE c.is_active = 1 
                               ORDER BY c.created_at DESC 
                               LIMIT ?");
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        echo '<div class="content-grid">';
        while ($content = $result->fetch_assoc()) {
            displayContentCard($content);
        }
        echo '</div>';
    } else {
        echo '<div class="no-content">';
        echo '<p>No content available at the moment.</p>';
        echo '</div>';
    }
}

/**
 * Display individual content card
 */
function displayContentCard($content) {
    $isLoggedIn = isLoggedIn();
    $user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
    $inWatchlist = $isLoggedIn ? isInWatchlist($content['id'], $user_id) : false;
    
    echo '<div class="content-card">';
    echo '<div class="content-poster">';
    echo '<img src="' . htmlspecialchars($content['poster_url'] ?: 'assets/img/default-poster.jpg') . '" alt="' . htmlspecialchars($content['title']) . '">';
    echo '<div class="content-overlay">';
    echo '<button class="btn-play" onclick="playContent(' . $content['id'] . ', \'' . htmlspecialchars($content['title']) . '\')">';
    echo '<i class="fas fa-play"></i>';
    echo '</button>';
    echo '<button class="btn-watchlist ' . ($inWatchlist ? 'in-watchlist' : '') . '" onclick="addToWatchlist(' . $content['id'] . ', \'' . htmlspecialchars($content['title']) . '\')">';
    echo '<i class="fas ' . ($inWatchlist ? 'fa-check' : 'fa-plus') . '"></i>';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="content-info">';
    echo '<h3 class="content-title">' . htmlspecialchars($content['title']) . '</h3>';
    if (!empty($content['release_year'])) {
        echo '<span class="content-year">' . htmlspecialchars($content['release_year']) . '</span>';
    }
    if (!empty($content['rating'])) {
        echo '<span class="content-rating ' . getRatingClass($content['rating']) . '">';
        echo '<i class="fas fa-star"></i> ' . htmlspecialchars($content['rating']);
        echo '</span>';
    }
    echo '</div>';
    echo '</div>';
}

/**
 * Display content by category
 */
function displayContentByCategory($category_id, $title = "Content") {
    echo '<section class="content-section">';
    echo '<div class="section-header">';
    echo '<h2>' . htmlspecialchars($title) . '</h2>';
    echo '<a href="browse.php?category=' . $category_id . '" class="see-all">See All</a>';
    echo '</div>';
    displayContentGrid($category_id, 8);
    echo '</section>';
}

/**
 * Get featured content
 */
function getFeaturedContent($limit = 6) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM content WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $featured = [];
    while ($row = $result->fetch_assoc()) {
        $featured[] = $row;
    }
    
    return $featured;
}

/**
 * Get trending content
 */
function getTrendingContent($limit = 8) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT c.*, COUNT(v.id) as view_count 
                           FROM content c 
                           LEFT JOIN content_views v ON c.id = v.content_id 
                           WHERE c.is_active = 1 
                           GROUP BY c.id 
                           ORDER BY view_count DESC, c.created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trending = [];
    while ($row = $result->fetch_assoc()) {
        $trending[] = $row;
    }
    
    return $trending;
}

/**
 * Search content
 */
function searchContent($query, $category = null, $limit = 20) {
    global $conn;
    
    $search_term = "%$query%";
    
    if ($category) {
        $stmt = $conn->prepare("SELECT * FROM content 
                               WHERE (title LIKE ? OR description LIKE ?) 
                               AND category_id = ? 
                               AND is_active = 1 
                               ORDER BY title 
                               LIMIT ?");
        $stmt->bind_param("ssii", $search_term, $search_term, $category, $limit);
    } else {
        $stmt = $conn->prepare("SELECT * FROM content 
                               WHERE (title LIKE ? OR description LIKE ?) 
                               AND is_active = 1 
                               ORDER BY title 
                               LIMIT ?");
        $stmt->bind_param("ssi", $search_term, $search_term, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    return $results;
}

/**
 * Record content view
 */
function recordContentView($content_id, $user_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO content_views (content_id, user_id, ip_address) VALUES (?, ?, ?)");
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("iis", $content_id, $user_id, $ip_address);
    $stmt->execute();
}

/**
 * Get content details
 */
function getContentDetails($content_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT c.*, cat.name as category_name 
                           FROM content c 
                           LEFT JOIN categories cat ON c.category_id = cat.id 
                           WHERE c.id = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?>