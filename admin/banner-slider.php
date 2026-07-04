<?php
// banner-slider.php

// Check if functions are already defined to prevent redeclaration
if (!function_exists('displayBannerSlider')) {
    function displayBannerSlider($page_type = 'home', $conn = null) {
        // If no connection provided, try to get global connection
        if ($conn === null) {
            if (!isset($GLOBALS['conn'])) {
                error_log("Database connection not available for banner slider");
                return displayDefaultBanner();
            }
            $conn = $GLOBALS['conn'];
        }
        
        try {
            $query = "SELECT * FROM banners WHERE type = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 5";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $page_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<div class="banner-slider">';
                echo '<div class="banner-slides">';
                
                while($banner = $result->fetch_assoc()) {
                    echo '<div class="banner-slide">';
                    echo '<img src="' . htmlspecialchars($banner['image_path']) . '" alt="' . htmlspecialchars($banner['title']) . '">';
                    echo '<div class="banner-content">';
                    echo '<h2>' . htmlspecialchars($banner['title']) . '</h2>';
                    if (!empty($banner['subtitle'])) {
                        echo '<p>' . htmlspecialchars($banner['subtitle']) . '</p>';
                    }
                    if (!empty($banner['button_text']) && !empty($banner['button_link'])) {
                        echo '<a href="' . htmlspecialchars($banner['button_link']) . '" class="banner-btn">' . htmlspecialchars($banner['button_text']) . '</a>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '<div class="banner-dots"></div>';
                echo '</div>';
                
                // Add slider JavaScript
                echo '
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        let currentSlide = 0;
                        const slides = document.querySelectorAll(".banner-slide");
                        const dotsContainer = document.querySelector(".banner-dots");
                        
                        if (slides.length > 0 && dotsContainer) {
                            // Create dots
                            slides.forEach((_, index) => {
                                const dot = document.createElement("span");
                                dot.classList.add("banner-dot");
                                if (index === 0) dot.classList.add("active");
                                dot.addEventListener("click", () => goToSlide(index));
                                dotsContainer.appendChild(dot);
                            });
                            
                            function goToSlide(slideIndex) {
                                slides.forEach(slide => slide.style.display = "none");
                                document.querySelectorAll(".banner-dot").forEach(dot => dot.classList.remove("active"));
                                
                                slides[slideIndex].style.display = "block";
                                document.querySelectorAll(".banner-dot")[slideIndex].classList.add("active");
                                currentSlide = slideIndex;
                            }
                            
                            function nextSlide() {
                                currentSlide = (currentSlide + 1) % slides.length;
                                goToSlide(currentSlide);
                            }
                            
                            // Auto slide every 5 seconds
                            setInterval(nextSlide, 5000);
                            
                            // Initialize first slide
                            goToSlide(0);
                        }
                    });
                </script>
                ';
            } else {
                displayDefaultBanner($page_type);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Banner slider error: " . $e->getMessage());
            displayDefaultBanner($page_type);
        }
    }
}

if (!function_exists('displayDefaultBanner')) {
    function displayDefaultBanner($page_type = 'home') {
        $titles = [
            'home' => 'Welcome to TechFlix',
            'movie' => 'Latest Movies',
            'kids' => 'Family Entertainment',
            'tv_show' => 'TV Shows'
        ];
        
        $subtitles = [
            'home' => 'Discover amazing content',
            'movie' => 'Explore our movie collection',
            'kids' => 'Fun content for the whole family',
            'tv_show' => 'Binge-watch your favorite series'
        ];
        
        $title = $titles[$page_type] ?? 'Welcome to TechFlix';
        $subtitle = $subtitles[$page_type] ?? 'Discover amazing content';
        
        echo '<div class="banner-slider">';
        echo '<div class="banner-slides">';
        echo '<div class="banner-slide">';
        echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 400px; display: flex; align-items: center; justify-content: center; color: white;">';
        echo '<div class="banner-content" style="text-align: center;">';
        echo '<h2>' . $title . '</h2>';
        echo '<p>' . $subtitle . '</p>';
        echo '<a href="' . $page_type . '.php" class="banner-btn">Explore Now</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

if (!function_exists('bannerSliderStyles')) {
    function bannerSliderStyles() {
        echo '
        <style>
            /* Banner Slider Styles */
            .banner-slider {
                position: relative;
                width: 100%;
                height: 500px;
                overflow: hidden;
                margin-bottom: 30px;
                border-radius: 10px;
            }

            .banner-slides {
                width: 100%;
                height: 100%;
            }

            .banner-slide {
                width: 100%;
                height: 100%;
                position: relative;
                display: none;
            }

            .banner-slide img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .banner-content {
                position: absolute;
                bottom: 50px;
                left: 50px;
                color: white;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
                max-width: 600px;
            }

            .banner-content h2 {
                font-size: 3rem;
                margin-bottom: 15px;
                font-weight: 700;
            }

            .banner-content p {
                font-size: 1.2rem;
                margin-bottom: 20px;
                opacity: 0.9;
            }

            .banner-btn {
                display: inline-block;
                padding: 12px 30px;
                background: #6a5af9;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: 600;
                transition: all 0.3s;
                border: none;
                cursor: pointer;
                font-size: 1rem;
            }

            .banner-btn:hover {
                background: #5b4af0;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }

            .banner-dots {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 10px;
            }

            .banner-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: rgba(255,255,255,0.5);
                cursor: pointer;
                transition: all 0.3s;
            }

            .banner-dot.active {
                background: white;
                transform: scale(1.2);
            }

            @media (max-width: 768px) {
                .banner-slider {
                    height: 300px;
                }
                
                .banner-content {
                    bottom: 20px;
                    left: 20px;
                    right: 20px;
                    text-align: center;
                }
                
                .banner-content h2 {
                    font-size: 2rem;
                }
                
                .banner-content p {
                    font-size: 1rem;
                }
                
                .banner-btn {
                    padding: 10px 20px;
                    font-size: 0.9rem;
                }
            }

            @media (max-width: 480px) {
                .banner-slider {
                    height: 250px;
                }
                
                .banner-content h2 {
                    font-size: 1.5rem;
                }
                
                .banner-content p {
                    font-size: 0.9rem;
                }
            }
        </style>
        ';
    }
}
?>