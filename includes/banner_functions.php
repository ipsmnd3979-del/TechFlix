<div class="hero-banner">
    <div class="hero-content">
        <div class="hero-tag">BROWSE CONTENT</div>
        <h1>Discover Amazing Content</h1>
        <p>Explore our vast library of movies, TV shows, and exclusive content. Filter by category, type, or search for your favorites.</p>
        
        <div class="search-bar" style="background: rgba(255, 255, 255, 0.15); padding: 15px; border-radius: 30px; margin: 20px 0; max-width: 500px;">
            <form method="GET" action="browse.php" style="display: flex; gap: 10px; align-items: center;">
                <i class="fas fa-search" style="color: #ddd;"></i>
                <input type="text" name="q" placeholder="Search movies, TV shows..." 
                       value="<?php echo isset($search_query) ? htmlspecialchars($search_query) : ''; ?>"
                       style="flex: 1; background: transparent; border: none; color: white; outline: none; font-size: 16px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Search</button>
            </form>
        </div>
    </div>
</div>
