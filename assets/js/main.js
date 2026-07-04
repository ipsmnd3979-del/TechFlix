// responsive.js - Comprehensive Responsive Functionality

class ResponsiveManager {
    constructor() {
        this.breakpoints = {
            sm: 576,
            md: 768,
            lg: 992,
            xl: 1200,
            xxl: 1400
        };
        
        this.currentBreakpoint = this.getCurrentBreakpoint();
        this.observers = [];
        this.resizeTimeout = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupResizeObserver();
        this.setupIntersectionObserver();
        this.setupTouchOptimizations();
        this.detectDeviceType();
        this.applyResponsiveClasses();
    }
    
    // Get current breakpoint
    getCurrentBreakpoint() {
        const width = window.innerWidth;
        
        if (width >= this.breakpoints.xxl) return 'xxl';
        if (width >= this.breakpoints.xl) return 'xl';
        if (width >= this.breakpoints.lg) return 'lg';
        if (width >= this.breakpoints.md) return 'md';
        if (width >= this.breakpoints.sm) return 'sm';
        return 'xs';
    }
    
    // Setup event listeners
    setupEventListeners() {
        // Debounced resize handler
        window.addEventListener('resize', () => {
            clearTimeout(this.resizeTimeout);
            this.resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
        
        // Orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResize();
            }, 100);
        });
        
        // Load event
        window.addEventListener('load', () => {
            this.handleLoad();
        });
    }
    
    // Handle window resize
    handleResize() {
        const newBreakpoint = this.getCurrentBreakpoint();
        
        if (newBreakpoint !== this.currentBreakpoint) {
            const oldBreakpoint = this.currentBreakpoint;
            this.currentBreakpoint = newBreakpoint;
            
            // Notify observers of breakpoint change
            this.notifyObservers('breakpointChange', {
                old: oldBreakpoint,
                new: newBreakpoint
            });
            
            // Update responsive classes
            this.applyResponsiveClasses();
        }
        
        // Notify observers of resize
        this.notifyObservers('resize', {
            width: window.innerWidth,
            height: window.innerHeight,
            breakpoint: this.currentBreakpoint
        });
    }
    
    // Handle page load
    handleLoad() {
        this.applyResponsiveClasses();
        this.setupLazyLoading();
        this.optimizeImages();
        this.notifyObservers('load');
    }
    
    // Setup ResizeObserver for specific elements
    setupResizeObserver() {
        if ('ResizeObserver' in window) {
            this.resizeObserver = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    this.notifyObservers('elementResize', {
                        element: entry.target,
                        size: entry.contentRect
                    });
                });
            });
        }
    }
    
    // Setup IntersectionObserver for lazy loading
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.intersectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.handleElementInViewport(entry.target);
                        this.intersectionObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });
        }
    }
    
    // Setup touch optimizations
    setupTouchOptimizations() {
        if (this.isTouchDevice()) {
            document.body.classList.add('touch-device');
            
            // Add touch-specific event listeners
            this.setupTouchEvents();
        } else {
            document.body.classList.add('no-touch-device');
        }
    }
    
    // Detect device type
    detectDeviceType() {
        const userAgent = navigator.userAgent.toLowerCase();
        
        if (/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/.test(userAgent)) {
            document.body.classList.add('mobile-device');
        } else if (/tablet|ipad/.test(userAgent)) {
            document.body.classList.add('tablet-device');
        } else {
            document.body.classList.add('desktop-device');
        }
    }
    
    // Check if touch device
    isTouchDevice() {
        return ('ontouchstart' in window) || 
               (navigator.maxTouchPoints > 0) || 
               (navigator.msMaxTouchPoints > 0);
    }
    
    // Setup touch events
    setupTouchEvents() {
        // Improve touch scrolling
        document.addEventListener('touchstart', function(e) {
            // Add passive: true for better performance
        }, { passive: true });
        
        // Prevent zoom on double tap
        document.addEventListener('touchend', function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                e.preventDefault();
            }
        }, { passive: false });
    }
    
    // Apply responsive classes to body
    applyResponsiveClasses() {
        const body = document.body;
        
        // Remove existing breakpoint classes
        body.classList.remove('breakpoint-xs', 'breakpoint-sm', 'breakpoint-md', 
                            'breakpoint-lg', 'breakpoint-xl', 'breakpoint-xxl');
        
        // Add current breakpoint class
        body.classList.add(`breakpoint-${this.currentBreakpoint}`);
        
        // Add orientation class
        const orientation = window.innerWidth > window.innerHeight ? 'landscape' : 'portrait';
        body.classList.remove('orientation-landscape', 'orientation-portrait');
        body.classList.add(`orientation-${orientation}`);
    }
    
    // Setup lazy loading
    setupLazyLoading() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-srcset]');
        
        lazyImages.forEach(img => {
            if (this.intersectionObserver) {
                this.intersectionObserver.observe(img);
            } else {
                // Fallback: load all images immediately
                this.loadImage(img);
            }
        });
    }
    
    // Handle element in viewport
    handleElementInViewport(element) {
        if (element.tagName === 'IMG') {
            this.loadImage(element);
        }
        
        // Add visible class for animations
        element.classList.add('in-viewport');
    }
    
    // Load lazy image
    loadImage(img) {
        const src = img.getAttribute('data-src');
        const srcset = img.getAttribute('data-srcset');
        
        if (src) {
            img.src = src;
            img.removeAttribute('data-src');
        }
        
        if (srcset) {
            img.srcset = srcset;
            img.removeAttribute('data-srcset');
        }
        
        img.classList.add('lazy-loaded');
    }
    
    // Optimize images based on viewport
    optimizeImages() {
        const images = document.querySelectorAll('img:not([data-optimized])');
        
        images.forEach(img => {
            // Mark as optimized
            img.setAttribute('data-optimized', 'true');
            
            // Add loading lazy for better performance
            if (!img.loading) {
                img.loading = 'lazy';
            }
        });
    }
    
    // Observer pattern for responsive events
    subscribe(event, callback) {
        if (!this.observers[event]) {
            this.observers[event] = [];
        }
        this.observers[event].push(callback);
    }
    
    unsubscribe(event, callback) {
        if (this.observers[event]) {
            this.observers[event] = this.observers[event].filter(cb => cb !== callback);
        }
    }
    
    notifyObservers(event, data) {
        if (this.observers[event]) {
            this.observers[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in ${event} observer:`, error);
                }
            });
        }
    }
    
    // Utility methods
    isMobile() {
        return this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm';
    }
    
    isTablet() {
        return this.currentBreakpoint === 'md';
    }
    
    isDesktop() {
        return this.currentBreakpoint === 'lg' || this.currentBreakpoint === 'xl' || this.currentBreakpoint === 'xxl';
    }
    
    getViewportSize() {
        return {
            width: window.innerWidth,
            height: window.innerHeight,
            breakpoint: this.currentBreakpoint
        };
    }
}

// Initialize responsive manager
const responsiveManager = new ResponsiveManager();

// Enhanced responsive slider functionality
class ResponsiveSlider {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? 
            document.querySelector(container) : container;
        this.slider = this.container.querySelector('.slider-responsive');
        this.items = Array.from(this.slider.querySelectorAll('.slider-item-responsive'));
        this.prevBtn = this.container.querySelector('.slider-prev');
        this.nextBtn = this.container.querySelector('.slider-next');
        
        this.options = {
            itemsToShow: 1,
            itemsToScroll: 1,
            autoPlay: false,
            autoPlayInterval: 5000,
            loop: false,
            ...options
        };
        
        this.currentIndex = 0;
        this.isDragging = false;
        this.startX = 0;
        this.currentX = 0;
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        this.calculateItemsToShow();
        this.setupEventListeners();
        this.updateSlider();
        
        if (this.options.autoPlay) {
            this.startAutoPlay();
        }
        
        // Subscribe to responsive changes
        responsiveManager.subscribe('breakpointChange', () => {
            this.calculateItemsToShow();
            this.updateSlider();
        });
        
        responsiveManager.subscribe('resize', () => {
            this.calculateItemsToShow();
            this.updateSlider();
        });
    }
    
    calculateItemsToShow() {
        const width = window.innerWidth;
        
        if (width >= responsiveManager.breakpoints.xl) {
            this.options.itemsToShow = this.options.itemsToShowXXL || 5;
        } else if (width >= responsiveManager.breakpoints.lg) {
            this.options.itemsToShow = this.options.itemsToShowLG || 4;
        } else if (width >= responsiveManager.breakpoints.md) {
            this.options.itemsToShow = this.options.itemsToShowMD || 3;
        } else if (width >= responsiveManager.breakpoints.sm) {
            this.options.itemsToShow = this.options.itemsToShowSM || 2;
        } else {
            this.options.itemsToShow = this.options.itemsToShowXS || 1;
        }
        
        this.options.itemsToScroll = this.options.itemsToShow;
    }
    
    setupEventListeners() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prev());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
        
        // Touch events for mobile
        this.slider.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
        this.slider.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: false });
        this.slider.addEventListener('touchend', () => this.handleTouchEnd());
        
        // Mouse events for desktop
        this.slider.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        this.slider.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.slider.addEventListener('mouseup', () => this.handleMouseUp());
        this.slider.addEventListener('mouseleave', () => this.handleMouseUp());
        
        // Pause autoplay on hover
        if (this.options.autoPlay) {
            this.container.addEventListener('mouseenter', () => this.stopAutoPlay());
            this.container.addEventListener('mouseleave', () => this.startAutoPlay());
        }
    }
    
    handleTouchStart(e) {
        this.isDragging = true;
        this.startX = e.touches[0].clientX;
        this.slider.style.transition = 'none';
        this.stopAutoPlay();
    }
    
    handleTouchMove(e) {
        if (!this.isDragging) return;
        
        this.currentX = e.touches[0].clientX;
        const diff = this.currentX - this.startX;
        
        if (Math.abs(diff) > 10) {
            e.preventDefault();
        }
        
        this.slider.style.transform = `translateX(${this.getTranslateX() + diff}px)`;
    }
    
    handleTouchEnd() {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.slider.style.transition = 'transform 0.3s ease';
        
        const diff = this.currentX - this.startX;
        const threshold = 50;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.prev();
            } else {
                this.next();
            }
        } else {
            this.updateSlider();
        }
        
        if (this.options.autoPlay) {
            this.startAutoPlay();
        }
    }
    
    handleMouseDown(e) {
        this.isDragging = true;
        this.startX = e.clientX;
        this.slider.style.transition = 'none';
        this.stopAutoPlay();
    }
    
    handleMouseMove(e) {
        if (!this.isDragging) return;
        
        this.currentX = e.clientX;
        const diff = this.currentX - this.startX;
        this.slider.style.transform = `translateX(${this.getTranslateX() + diff}px)`;
    }
    
    handleMouseUp() {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.slider.style.transition = 'transform 0.3s ease';
        
        const diff = this.currentX - this.startX;
        const threshold = 50;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.prev();
            } else {
                this.next();
            }
        } else {
            this.updateSlider();
        }
        
        if (this.options.autoPlay) {
            this.startAutoPlay();
        }
    }
    
    getTranslateX() {
        const itemWidth = this.items[0].offsetWidth + 
                         parseInt(getComputedStyle(this.slider).gap) || 0;
        return -this.currentIndex * itemWidth;
    }
    
    updateSlider() {
        this.slider.style.transform = `translateX(${this.getTranslateX()}px)`;
        this.updateButtons();
    }
    
    updateButtons() {
        if (this.prevBtn) {
            this.prevBtn.disabled = !this.options.loop && this.currentIndex === 0;
        }
        
        if (this.nextBtn) {
            const maxIndex = this.items.length - this.options.itemsToShow;
            this.nextBtn.disabled = !this.options.loop && this.currentIndex >= maxIndex;
        }
    }
    
    next() {
        const maxIndex = this.items.length - this.options.itemsToShow;
        
        if (this.currentIndex >= maxIndex) {
            if (this.options.loop) {
                this.currentIndex = 0;
            } else {
                return;
            }
        } else {
            this.currentIndex += this.options.itemsToScroll;
        }
        
        this.updateSlider();
    }
    
    prev() {
        if (this.currentIndex <= 0) {
            if (this.options.loop) {
                const maxIndex = this.items.length - this.options.itemsToShow;
                this.currentIndex = maxIndex;
            } else {
                return;
            }
        } else {
            this.currentIndex -= this.options.itemsToScroll;
        }
        
        this.updateSlider();
    }
    
    startAutoPlay() {
        if (this.options.autoPlay && !this.autoPlayInterval) {
            this.autoPlayInterval = setInterval(() => {
                this.next();
            }, this.options.autoPlayInterval);
        }
    }
    
    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    goToSlide(index) {
        this.currentIndex = Math.max(0, Math.min(index, this.items.length - this.options.itemsToShow));
        this.updateSlider();
    }
}

// Initialize all sliders on page
function initResponsiveSliders() {
    document.querySelectorAll('.slider-container-responsive').forEach(container => {
        new ResponsiveSlider(container, {
            autoPlay: true,
            autoPlayInterval: 5000,
            loop: true,
            itemsToShowXS: 1,
            itemsToShowSM: 2,
            itemsToShowMD: 3,
            itemsToShowLG: 4,
            itemsToShowXXL: 5
        });
    });
}

// Enhanced responsive navigation
class ResponsiveNavigation {
    constructor() {
        this.header = document.querySelector('.header-responsive');
        this.nav = this.header?.querySelector('.nav-responsive');
        this.menuToggle = this.header?.querySelector('.menu-toggle');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        if (!this.header || !this.nav) return;
        
        this.setupEventListeners();
        this.setupScrollBehavior();
        
        // Subscribe to responsive changes
        responsiveManager.subscribe('breakpointChange', (data) => {
            this.handleBreakpointChange(data);
        });
    }
    
    setupEventListeners() {
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleMenu());
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.header.contains(e.target)) {
                this.closeMenu();
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeMenu();
            }
        });
    }
    
    setupScrollBehavior() {
        let lastScrollY = window.scrollY;
        
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > lastScrollY && currentScrollY > 100) {
                // Scrolling down
                this.header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                this.header.style.transform = 'translateY(0)';
            }
            
            // Add background when scrolled
            if (currentScrollY > 50) {
                this.header.style.background = 'rgba(15, 12, 41, 0.98)';
            } else {
                this.header.style.background = 'rgba(15, 12, 41, 0.95)';
            }
            
            lastScrollY = currentScrollY;
        });
    }
    
    handleBreakpointChange(data) {
        if (data.new === 'lg' || data.new === 'xl' || data.new === 'xxl') {
            // Desktop - ensure menu is open
            this.nav.style.display = 'block';
            this.isOpen = false;
        } else {
            // Mobile - ensure menu is closed initially
            this.nav.style.display = 'none';
            this.isOpen = false;
        }
    }
    
    toggleMenu() {
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        this.nav.style.display = 'block';
        this.isOpen = true;
        
        // Animate in
        setTimeout(() => {
            this.nav.style.opacity = '1';
            this.nav.style.transform = 'translateY(0)';
        }, 10);
        
        // Prevent body scroll on mobile
        if (responsiveManager.isMobile()) {
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeMenu() {
        this.nav.style.opacity = '0';
        this.nav.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            this.nav.style.display = 'none';
            this.isOpen = false;
            
            // Restore body scroll
            document.body.style.overflow = '';
        }, 300);
    }
}

// Initialize responsive navigation
function initResponsiveNavigation() {
    new ResponsiveNavigation();
}

// Enhanced image optimization
function optimizeAllImages() {
    const images = document.querySelectorAll('img:not([data-optimized])');
    
    images.forEach(img => {
        // Skip if already optimized
        if (img.getAttribute('data-optimized')) return;
        
        const src = img.src;
        
        // Add error handling
        img.addEventListener('error', function() {
            console.warn('Image failed to load:', src);
            this.style.display = 'none';
            
            // Try to load fallback image
            const fallbackSrc = this.getAttribute('data-fallback');
            if (fallbackSrc) {
                this.src = fallbackSrc;
                this.style.display = '';
            }
        });
        
        // Add load event for successful loads
        img.addEventListener('load', function() {
            this.classList.add('image-loaded');
        });
        
        // Mark as optimized
        img.setAttribute('data-optimized', 'true');
    });
}

// Performance monitoring
function monitorPerformance() {
    if ('performance' in window) {
        // Monitor long tasks
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.duration > 50) {
                        console.warn('Long task detected:', entry);
                    }
                }
            });
            
            observer.observe({ entryTypes: ['longtask'] });
        }
        
        // Monitor largest contentful paint
        if ('PerformanceObserver' in window) {
            const lcpObserver = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    console.log('LCP:', entry);
                }
            });
            
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        }
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize responsive manager (already done, but ensure it's ready)
    
    // Initialize components
    initResponsiveNavigation();
    initResponsiveSliders();
    optimizeAllImages();
    monitorPerformance();
    
    // Add loading state management
    document.body.classList.add('page-loaded');
    
    // Remove loading spinner if exists
    const loadingSpinner = document.querySelector('.loading-spinner');
    if (loadingSpinner) {
        setTimeout(() => {
            loadingSpinner.style.opacity = '0';
            setTimeout(() => {
                loadingSpinner.remove();
            }, 500);
        }, 500);
    }
});

// Export for use in other modules
window.ResponsiveManager = responsiveManager;
window.ResponsiveSlider = ResponsiveSlider;
window.ResponsiveNavigation = ResponsiveNavigation;

// Global error handling for better user experience
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    
    // You could show a user-friendly error message here
    // showErrorMessage('Something went wrong. Please try refreshing the page.');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    e.preventDefault();
});

// 
// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new NoZoom();
    preventZoomEverywhere();
    
    // Additional mobile-specific fixes
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        enableMobileZoomPrevention();
    }
});

// Mobile-specific zoom prevention
function enableMobileZoomPrevention() {
    // Disable elastic scrolling (can sometimes cause zoom issues)
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
    
    // Force no zoom on load
    setTimeout(() => {
        document.body.style.zoom = "1";
        document.documentElement.style.zoom = "1";
    }, 100);

    // Prevent pull-to-refresh (can cause zoom)
    document.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1) {
            e.preventDefault();
        }
    }, { passive: false });

    // iOS specific fixes
    if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
        enableIOSZoomPrevention();
    }
}

// iOS-specific zoom prevention
function enableIOSZoomPrevention() {
    // iOS specific viewport fix
    const viewport = document.querySelector('meta[name="viewport"]');
    if (viewport) {
        viewport.setAttribute('content', 
            'width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover'
        );
    }

    // Prevent iOS text size adjustment
    document.addEventListener('touchstart', (e) => {
        if (e.touches.length > 1) {
            e.preventDefault();
        }
    }, { passive: false });

    // Disable iOS double-tap to zoom
    let lastTouchEnd = 0;
    document.addEventListener('touchend', (e) => {
        const now = Date.now();
        if (now - lastTouchEnd <= 500) {
            e.preventDefault();
        }
        lastTouchEnd = now;
    }, { passive: false });
}

// Nuclear option - completely disable any scaling
function nuclearNoZoom() {
    // Disable any transform scaling
    const style = document.createElement('style');
    style.textContent = `
        * {
            transform: none !important;
            scale: none !important;
            zoom: 1 !important;
        }
        
        body {
            zoom: 1 !important;
            -webkit-text-size-adjust: 100% !important;
            -moz-text-size-adjust: 100% !important;
            -ms-text-size-adjust: 100% !important;
            text-size-adjust: 100% !important;
        }
    `;
    document.head.appendChild(style);

    // Continuously reset zoom
    setInterval(() => {
        document.body.style.zoom = "1";
        if (window.visualViewport) {
            window.visualViewport.scale = 1;
        }
    }, 500);
}

// Apply nuclear option for maximum prevention
document.addEventListener('DOMContentLoaded', () => {
    nuclearNoZoom();
});

// Fallback for older browsers
window.onload = function() {
    // Final viewport enforcement
    const viewport = document.querySelector('meta[name="viewport"]');
    if (viewport) {
        viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no';
    }
    
    // Force initial scale
    document.body.style.zoom = "1";
};


// 


// 

// Enhanced breakpoint detection
const breakpoints = {
    xs: 340,
    sm: 480,
    md: 576,
    lg: 768,
    xl: 992,
    xxl: 1200
};

function getCurrentBreakpoint() {
    const width = window.innerWidth;
    
    if (width >= breakpoints.xxl) return 'xxl';
    if (width >= breakpoints.xl) return 'xl';
    if (width >= breakpoints.lg) return 'lg';
    if (width >= breakpoints.md) return 'md';
    if (width >= breakpoints.sm) return 'sm';
    return 'xs';
}

// Update body classes for breakpoint-specific styling
function updateBreakpointClasses() {
    const breakpoint = getCurrentBreakpoint();
    const orientation = window.innerWidth > window.innerHeight ? 'landscape' : 'portrait';
    
    document.body.className = document.body.className.replace(/\bbreakpoint-\w+\b/g, '');
    document.body.className = document.body.className.replace(/\borientation-\w+\b/g, '');
    
    document.body.classList.add(`breakpoint-${breakpoint}`);
    document.body.classList.add(`orientation-${orientation}`);
}

// Initialize on load and resize
window.addEventListener('load', updateBreakpointClasses);
window.addEventListener('resize', updateBreakpointClasses);
window.addEventListener('orientationchange', updateBreakpointClasses);

// 


 document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('mainVideo');
            const playBtn = document.getElementById('playBtn');
            const pauseBtn = document.getElementById('pauseBtn');
            const muteBtn = document.getElementById('muteBtn');
            
            // Play button functionality
            playBtn.addEventListener('click', function() {
                video.play();
            });
            
            // Pause button functionality
            pauseBtn.addEventListener('click', function() {
                video.pause();
            });
            
            // Mute/unmute button functionality
            muteBtn.addEventListener('click', function() {
                if (video.muted) {
                    video.muted = false;
                    muteBtn.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M6.5 5H3v6h3.5l4 3V2l-4 3z"/>
                        </svg>
                        Mute
                    `;
                } else {
                    video.muted = true;
                    muteBtn.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M6.5 5H3v6h3.5l4 3V2l-4 3z"/>
                        </svg>
                        Unmute
                    `;
                }
            });
        });
