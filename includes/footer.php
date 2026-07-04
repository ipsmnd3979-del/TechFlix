<?php
// Footer template for consistent footer across all pages
?>
    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h3>TechFlix</h3>
                <p>Your portal to the universe of entertainment. Explore galaxies of content with our premium streaming service.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Navigation</h3>
                <ul class="footer-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="browse.php">Browse</a></li>
                    <li><a href="movies.php">Movies</a></li>
                    <li><a href="tvshows.php">TV Shows</a></li>
                    <li><a href="kids.php">Kids</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <li><a href="browse.php?category=action">Action</a></li>
                    <li><a href="browse.php?category=sci-fi">Sci-Fi</a></li>
                    <li><a href="browse.php?category=adventure">Adventure</a></li>
                    <li><a href="browse.php?category=comedy">Comedy</a></li>
                    <li><a href="browse.php?category=drama">Drama</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Support</h3>
                <ul class="footer-links">
                    <li><a href="profile.php">Account</a></li>
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="terms.php">Terms of Use</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> TechFlix Streaming Service. All rights reserved.</p>
        </div>
    </footer>

    <!-- Mobile Navigation Bar -->
    <div class="mobile-user-actions">
        <div class="mobile-action" onclick="window.location.href='home.php'">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </div>
        <div class="mobile-action" onclick="window.location.href='browse.php'">
            <i class="fas fa-compass"></i>
            <span>Browse</span>
        </div>
        <div class="mobile-action" onclick="window.location.href='movies.php'">
            <i class="fa-solid fa-tv"></i>
            <span>Movies</span>
        </div>
        <div class="mobile-action" onclick="showMobileSearch()">
            <i class="fas fa-search"></i>
            <span>Search</span>
        </div>
        <div class="mobile-action" onclick="window.location.href='<?php echo $isLoggedIn ? 'profile.php' : 'auth/login.php'; ?>'">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Additional JavaScript for mobile functionality -->
    <script>
        // Mobile menu toggle + header scroll effect are handled by
        // assets/js/main.js's ResponsiveNavigation class.

        function createGalaxyBackground() {
            const galaxy = document.getElementById('galaxy');
            if (!galaxy) return;

            // Create stars
            const starsCount = 200;
            for (let i = 0; i < starsCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');

                const size = Math.random() * 3;
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const duration = 2 + Math.random() * 5;
                const delay = Math.random() * 5;

                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.left = `${left}%`;
                star.style.top = `${top}%`;
                star.style.setProperty('--duration', `${duration}s`);
                star.style.setProperty('--delay', `${delay}s`);

                galaxy.appendChild(star);
            }

            // Create nebulas
            for (let i = 0; i < 3; i++) {
                const nebula = document.createElement('div');
                nebula.classList.add('nebula');

                const left = Math.random() * 70;
                const top = Math.random() * 70;
                const duration = 30 + Math.random() * 30;

                nebula.style.left = `${left}%`;
                nebula.style.top = `${top}%`;
                nebula.style.animationDuration = `${duration}s`;

                galaxy.appendChild(nebula);
            }
        }

        function showMobileSearch() {
            const searchTerm = prompt("Enter search term:");
            if (searchTerm) {
                window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
            }
        }


        
        // Auto slider functionality
        function initAutoSlider(sliderId, interval = 5000) {
            const slider = document.getElementById(sliderId);
            if (!slider) return;

            const slides = slider.children;
            let currentSlide = 0;

            function showSlide(index) {
                slider.style.transform = `translateX(-${index * 100}%)`;
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }

            // Auto slide
            setInterval(nextSlide, interval);
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification-popup ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Add to watchlist function
        function addToWatchlist(contentId, contentTitle) {
    <?php if ($isLoggedIn): ?>
        fetch('includes/watchlist_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                content_id: contentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Optional: Update UI without reload
                if (data.action === 'added') {
                    // Change button to "Added" state
                    const buttons = document.querySelectorAll(`[onclick*="${contentId}"]`);
                    buttons.forEach(btn => {
                        if (btn.innerHTML.includes('fa-plus')) {
                            btn.innerHTML = '<i class="fas fa-check"></i> Added';
                            btn.classList.add('added');
                        }
                    });
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Watchlist error:', error);
            showNotification('Error updating watchlist', 'error');
        });
    <?php else: ?>
        showNotification('Please login to add to watchlist', 'error');
        setTimeout(() => {
            window.location.href = 'auth/login.php';
        }, 1500);
    <?php endif; ?>
}

        // Play content function
        function playContent(contentId, contentTitle) {
            window.location.href = `player.php?id=${contentId}&title=${encodeURIComponent(contentTitle)}`;
        }
    </script>
</body>
</html>