// Fix for InitUserClicker error
function InitUserClicker() {
    console.log('UserClicker initialized');
    
    // Add any user interaction tracking here
    document.addEventListener('click', function(e) {
        console.log('User clicked:', e.target);
    });
    
    // Or if it's for a specific element
    const clickableElements = document.querySelectorAll('[data-user-click]');
    clickableElements.forEach(element => {
        element.addEventListener('click', function() {
            console.log('Tracked click:', this.dataset.userClick);
        });
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', InitUserClicker);
} else {
    InitUserClicker();
}