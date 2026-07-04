<?php
require_once 'includes/header.php';

// Resolve a content row's poster to a real, existing file path.
// (The same pattern other pages use — see getCorrectImagePath() in home.php/browse.php —
// because the DB's poster_image column is unused and thumbnail paths are stored as ../assets/...)
function resolveContentImage($content) {
    if (!empty($content['thumbnail'])) {
        $thumbnail = $content['thumbnail'];
        if (strpos($thumbnail, '../') === 0) {
            $thumbnail = substr($thumbnail, 3);
        }
        if (file_exists($thumbnail)) {
            return $thumbnail;
        }
        $directPath = 'assets/uploads/thumbnails/' . basename($thumbnail);
        if (file_exists($directPath)) {
            return $directPath;
        }
    }
    if (!empty($content['poster_image'])) {
        return $content['poster_image'];
    }
    return 'https://via.placeholder.com/300x450/333/fff?text=' . urlencode(substr($content['title'], 0, 15));
}

function formatDuration($minutes) {
    $minutes = (int) $minutes;
    if ($minutes <= 0) {
        return '';
    }
    $hours = intdiv($minutes, 60);
    $mins = $minutes % 60;
    return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
}

// Hero slides: top-rated published content
$hero_content = [];
$heroResult = $conn->query(
    "SELECT c.*, cat.name AS category_name FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'published'
     ORDER BY c.rating DESC LIMIT 4"
);
if ($heroResult) {
    while ($row = $heroResult->fetch_assoc()) {
        $hero_content[] = $row;
    }
}

// Trending: a random sample of published content (mirrors home.php's trending query)
$trending_content = [];
$trendingResult = $conn->query(
    "SELECT c.*, cat.name AS category_name FROM content_new c
     LEFT JOIN categories cat ON c.category_id = cat.id
     WHERE c.status = 'published'
     ORDER BY RAND() LIMIT 12"
);
if ($trendingResult) {
    while ($row = $trendingResult->fetch_assoc()) {
        $trending_content[] = $row;
    }
}
?>

<style>
    /* Landing-page-only styles; everything else comes from assets/css/main.css */
    #hero-slider {
        position: relative;
        overflow: hidden;
        height: clamp(400px, 75vh, 800px);
    }

    .hero-slide {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        /* background-size: cover; */
        /* background-position: center; */
        background-repeat: no-repeat;
    }

    .hero-slide::after {
        content: '';
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(to right, rgba(15, 12, 41, 0.9) 0%, rgba(15, 12, 41, 0.5) 50%, rgba(15, 12, 41, 0.15) 100%);
    }

    .hero-slide.active {
        opacity: 1;
        z-index: 2;
    }

    .info-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .info-line .year {
        color: #ccc;
        font-size: 0.9rem;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: var(--space-lg);
    }

    .feature-card {
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: var(--space-lg);
        text-align: center;
        transition: transform 0.3s, background 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        background: rgba(138, 43, 226, 0.15);
    }

    .feature-icon {
        font-size: clamp(2rem, 6vw, 3rem);
        margin-bottom: var(--space-sm);
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .feature-card h3 {
        margin-bottom: var(--space-sm);
    }

    .feature-card h3 a {
        color: #fff;
        text-decoration: none;
    }

    .feature-card p {
        color: #bbb;
        line-height: 1.6;
    }

    .cta-section {
        text-align: center;
        padding: var(--space-xl) var(--space-md);
        background: linear-gradient(135deg, rgba(10, 10, 10, 0.9), rgba(20, 20, 20, 0.9));
        border-radius: 20px;
        margin: var(--space-xl) auto;
        max-width: 800px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .cta-section h2 {
        font-size: var(--text-3xl);
        margin-bottom: var(--space-lg);
        background: linear-gradient(135deg, #fff, #e0e0e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cta-form {
        max-width: 500px;
        margin: 0 auto;
    }

    .cta-form input[type="email"] {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 1rem;
        margin-bottom: var(--space-sm);
    }

    .cta-form input[type="email"]:focus {
        outline: none;
        border-color: var(--primary);
    }

    .cta-form input[type="email"]::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
</style>

<!-- Hero Slider -->
<section id="hero-slider">
    <?php foreach ($hero_content as $index => $content): ?>
    <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>"
         data-movie="<?php echo htmlspecialchars($content['title']); ?>"
         style="background-image: url('<?php echo htmlspecialchars(resolveContentImage($content)); ?>')">
        <div class="hero-content">
            <span class="hero-badge">PREMIERING</span>
            <h1 class="hero-title"><?php echo htmlspecialchars($content['title']); ?></h1>
            <div class="hero-info">
                <span class="hero-rating">
                    <i class="fas fa-star"></i> <?php echo $content['rating']; ?>
                </span>
                <span><?php echo $content['release_year']; ?></span>
                <span><?php echo htmlspecialchars($content['category_name'] ?: ucfirst(str_replace('_', ' ', $content['type']))); ?> | <?php echo $content['type'] == 'movie' ? 'Movie' : 'TV Show'; ?></span>
                <span><?php echo htmlspecialchars(formatDuration($content['duration'])); ?></span>
            </div>
            <p class="hero-description">
                <?php echo htmlspecialchars($content['description']); ?>
            </p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="redirectToLogin('play', <?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title'], ENT_QUOTES); ?>')">
                    <i class="fas fa-play"></i> Play Now
                </button>
                <button class="btn btn-outline" onclick="redirectToLogin('watchlist', <?php echo $content['id']; ?>, '<?php echo htmlspecialchars($content['title'], ENT_QUOTES); ?>')">
                    <i class="fas fa-plus"></i> Add to List
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="slide-indicators" id="hero-indicators">
        <?php foreach ($hero_content as $index => $content): ?>
        <div class="slide-indicator <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Video Player Container (Hidden by default) -->
<div class="video-container" id="videoPlayer">
    <h1>Now Playing</h1>
    <div class="video-player">
        <iframe id="player" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>

    <div class="video-info">
        <h2 id="movie-title" class="video-title"></h2>
        <div class="video-meta">
            <span class="video-rating">
                <i class="fas fa-star"></i> 8.7
            </span>
            <span>2023</span>
            <span>Action | Adventure</span>
            <span>2h 15m</span>
        </div>
        <p id="movie-description" class="video-description"></p>
        <button class="btn btn-secondary" id="addFromPlayer">
            <i class="fas fa-plus"></i> Add to My List
        </button>
        <button class="back-button" id="backButton">
            <i class="fas fa-arrow-left"></i> Back to Home
        </button>
    </div>
</div>

<!-- Trending Now Section -->
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-fire"></i> Trending Now</h2>
    </div>
    <div class="trending-grid">
        <?php foreach ($trending_content as $index => $content): ?>
        <div class="trending-card" data-content-id="<?php echo $content['id']; ?>">
            <img src="<?php echo htmlspecialchars(resolveContentImage($content)); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>" loading="lazy" class="trending-img">
            <div class="card-overlay">
                <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                <div class="info-line">
                    <span class="rating">
                        <i class="fas fa-star"></i> <?php echo $content['rating']; ?>/10
                    </span>
                    <span class="year"><?php echo htmlspecialchars($content['release_year']); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Features Section -->
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">More Reasons to Join</h2>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-tv"></i>
            </div>
            <h3><a href="auth/login.php">Watch Anywhere</a></h3>
            <p>Enjoy on your TV, PlayStation, Xbox, Chromecast, Apple TV, Blu-ray players, and more.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-download"></i>
            </div>
            <h3>Download &amp; Watch</h3>
            <p>Save your favorites easily and always have something to watch offline.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-child"></i>
            </div>
            <h3><a href="auth/login.php">Create profiles for kids</a></h3>
            <p>Send kids on adventures with their favourite characters in a space made just for them — free with your membership.</p>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <h2>Ready to watch? Enter your email to start your membership.</h2>
    <div class="cta-form">
        <input autocomplete="email" minlength="5" maxlength="50" type="email" name="email" placeholder="Enter your email address">
        <button class="btn btn-primary" onclick="window.location.href='auth/register.php'">Get Started <i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hero Slider Functionality
        const slides = document.querySelectorAll('#hero-slider .hero-slide');
        const indicators = document.querySelectorAll('#hero-slider .slide-indicator');
        let currentSlide = 0;
        const slideCount = slides.length;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            currentSlide = index;
        }

        function nextSlide() {
            let next = currentSlide + 1;
            if (next >= slideCount) next = 0;
            showSlide(next);
        }

        let slideInterval = setInterval(nextSlide, 5000);

        indicators.forEach(indicator => {
            indicator.addEventListener('click', function() {
                clearInterval(slideInterval);
                showSlide(parseInt(this.getAttribute('data-slide')));
                slideInterval = setInterval(nextSlide, 5000);
            });
        });

        // Demo "Now Playing" panel (index.php has no real player backend yet)
        document.getElementById('backButton').addEventListener('click', hideDemoPlayer);
        document.getElementById('addFromPlayer').addEventListener('click', function() {
            const movieTitle = document.getElementById('movie-title').textContent;
            redirectToLogin('watchlist', 1, movieTitle);
        });

        document.querySelectorAll('.trending-card').forEach(card => {
            card.addEventListener('click', function() {
                const contentId = this.getAttribute('data-content-id');
                const title = this.querySelector('h3').textContent;
                redirectToLogin('play', contentId, title);
            });
        });
    });

    // Redirect anonymous visitors to login, remembering what they wanted to do
    function redirectToLogin(action, contentId, title) {
        sessionStorage.setItem('action', action);
        sessionStorage.setItem('contentId', contentId);
        sessionStorage.setItem('contentTitle', title);
        window.location.href = 'auth/login.php';
    }

    function showDemoPlayer(contentId, title) {
        document.getElementById('hero-slider').style.display = 'none';
        document.getElementById('videoPlayer').style.display = 'block';
        document.getElementById('movie-title').textContent = title;
        document.getElementById('movie-description').textContent = "This is a demo description for " + title + ". In a real implementation, this would be fetched from the database.";
        document.getElementById('player').src = "https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1";
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideDemoPlayer() {
        document.getElementById('videoPlayer').style.display = 'none';
        document.getElementById('hero-slider').style.display = 'block';
        document.getElementById('player').src = "";
    }
</script>

<?php require_once 'includes/footer.php'; ?>
