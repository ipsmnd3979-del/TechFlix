// assets/js/no-zoom.js

class NoZoom {
    constructor() {
        this.init();
    }

    init() {
        this.preventAllZoom();
        this.fixViewport();
        this.blockZoomEvents();
        this.disableDoubleTap();
        this.preventPinchZoom();
        this.handleOrientation();
        this.forceNoZoom();
    }

    // Completely prevent all forms of zoom
    preventAllZoom() {
        // Block wheel zoom
        document.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { passive: false });

        // Block keyboard zoom (Ctrl +, Ctrl -)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=' || e.keyCode === 187 || e.keyCode === 189)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Block context menu zoom
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            return false;
        });
    }

    // Fix viewport for maximum zoom prevention
    fixViewport() {
        let viewport = document.querySelector('meta[name="viewport"]');
        if (!viewport) {
            viewport = document.createElement('meta');
            viewport.name = 'viewport';
            document.head.appendChild(viewport);
        }
        
        const viewportContent = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, shrink-to-fit=no';
        viewport.setAttribute('content', viewportContent);
        
        // Force viewport update
        this.forceViewportUpdate();
    }

    forceViewportUpdate() {
        // This forces mobile browsers to respect the viewport
        window.addEventListener('resize', () => {
            document.body.style.zoom = "1";
        });

        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                document.body.style.zoom = "1";
                this.resetViewport();
            }, 100);
        });
    }

    resetViewport() {
        const viewport = document.querySelector('meta[name="viewport"]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no');
        }
    }

    // Block all zoom-related events
    blockZoomEvents() {
        // Block gesture events
        document.addEventListener('gesturestart', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        document.addEventListener('gesturechange', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        document.addEventListener('gestureend', (e) => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        // Block touch events that could cause zoom
        document.addEventListener('touchstart', (e) => {
            if (e.touches.length > 1) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { passive: false });

        document.addEventListener('touchmove', (e) => {
            if (e.touches.length > 1) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { passive: false });

        document.addEventListener('touchend', (e) => {
            if (e.touches.length > 1) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { passive: false });
    }

    // Disable double tap to zoom
    disableDoubleTap() {
        let lastTouchEnd = 0;
        
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            lastTouchEnd = now;
        }, { passive: false });

        // Prevent double-tap zoom on specific elements
        document.querySelectorAll('a, button, .content-card, .btn').forEach(element => {
            element.style.touchAction = 'manipulation';
        });
    }

    // Prevent pinch zoom specifically
    preventPinchZoom() {
        document.addEventListener('touchmove', (e) => {
            if (e.scale && e.scale !== 1) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, { passive: false });

        // Additional pinch prevention
        let initialPinchDistance = null;

        document.addEventListener('touchstart', (e) => {
            if (e.touches.length === 2) {
                initialPinchDistance = this.getTouchDistance(e.touches);
            }
        });

        document.addEventListener('touchmove', (e) => {
            if (e.touches.length === 2 && initialPinchDistance !== null) {
                const currentDistance = this.getTouchDistance(e.touches);
                if (Math.abs(currentDistance - initialPinchDistance) > 10) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        }, { passive: false });
    }

    getTouchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Handle orientation changes
    handleOrientation() {
        window.addEventListener('orientationchange', () => {
            // Force no zoom on orientation change
            setTimeout(() => {
                this.fixViewport();
                document.body.style.zoom = "1";
            }, 150);
        });
    }

    // Force no zoom by periodically checking and resetting
    forceNoZoom() {
        // Check zoom level periodically and reset if changed
        setInterval(() => {
            if (window.visualViewport) {
                if (window.visualViewport.scale !== 1) {
                    window.visualViewport.scale = 1;
                }
            }
            
            // Reset any potential zoom
            document.body.style.zoom = "1";
            document.documentElement.style.zoom = "1";
        }, 1000);

        // Reset on any user interaction
        document.addEventListener('click', () => {
            document.body.style.zoom = "1";
        });

        document.addEventListener('touchstart', () => {
            document.body.style.zoom = "1";
        });
    }
}

// Additional global zoom prevention
function preventZoomEverywhere() {
    // Disable text selection (can sometimes trigger zoom)
    document.styleSheets[0].insertRule(`
        * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }
    `, 0);

    // Allow text selection only on inputs and textareas
    document.styleSheets[0].insertRule(`
        input, textarea, [contenteditable] {
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            user-select: text !important;
        }
    `, 1);

    // Force touch action
    document.styleSheets[0].insertRule(`
        html, body, * {
            touch-action: manipulation !important;
            -webkit-touch-callout: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }
    `, 2);
}

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