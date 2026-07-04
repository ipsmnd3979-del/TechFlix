<?php
require_once 'includes/header.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$type_filter     = isset($_GET['type'])     ? trim($_GET['type'])     : 'all';
$sort_by         = isset($_GET['sort'])     ? trim($_GET['sort'])     : 'rating';
$search_query    = isset($_GET['q'])        ? trim($_GET['q'])        : '';

// Build WHERE conditions
$where_conditions = [];
$params = [];
$types  = "";

if ($category_filter !== 'all') {
    $where_conditions[] = "cat.name = ?";
    $params[] = $category_filter;
    $types   .= "s";
}

if ($type_filter !== 'all') {
    $where_conditions[] = "c.type = ?";
    $params[] = $type_filter;
    $types   .= "s";
}

if (!empty($search_query)) {
    $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types   .= "ss";
}

$where_clause = !empty($where_conditions)
    ? "WHERE " . implode(" AND ", $where_conditions)
    : "";

// Sort clause (whitelist to prevent injection)
switch ($sort_by) {
    case 'newest': $sort_clause = "ORDER BY c.created_at DESC";           break;
    case 'title':  $sort_clause = "ORDER BY c.title ASC";                 break;
    case 'year':   $sort_clause = "ORDER BY c.release_year DESC";         break;
    default:       $sort_clause = "ORDER BY c.rating DESC, c.created_at DESC"; break;
}

// ── Count query ──────────────────────────────────────────────────────────────
$total_items = 0;
$count_query = "SELECT COUNT(*) as total
                FROM content_new c
                LEFT JOIN categories cat ON c.category_id = cat.id
                $where_clause";
$count_stmt = $conn->prepare($count_query);
if ($count_stmt) {
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_result) {
        $total_items = (int)($count_result->fetch_assoc()['total'] ?? 0);
    }
    $count_stmt->close();
} else {
    error_log("browse.php count prepare failed: " . $conn->error . " | SQL: $count_query");
}

// ── Pagination ───────────────────────────────────────────────────────────────
$items_per_page = 18;
$total_pages    = max(1, (int)ceil($total_items / $items_per_page));
$current_page   = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
$offset         = ($current_page - 1) * $items_per_page;

// ── Content query ────────────────────────────────────────────────────────────
$content_result  = null;
$content_params  = $params;
$content_types   = $types . "ii";
$content_params[] = $items_per_page;
$content_params[] = $offset;

$content_query = "SELECT c.*, cat.name as category_name
                  FROM content_new c
                  LEFT JOIN categories cat ON c.category_id = cat.id
                  $where_clause
                  $sort_clause
                  LIMIT ? OFFSET ?";

$content_stmt = $conn->prepare($content_query);
if ($content_stmt) {
    $content_stmt->bind_param($content_types, ...$content_params);
    $content_stmt->execute();
    $content_result = $content_stmt->get_result();
} else {
    error_log("browse.php content prepare failed: " . $conn->error . " | SQL: $content_query");
}

// ── Categories for filter dropdown ──────────────────────────────────────────
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");

// ── Image path helper ────────────────────────────────────────────────────────
if (!function_exists('getCorrectImagePath')) {
    function getCorrectImagePath($content) {
        if (!empty($content['thumbnail'])) {
            $thumbnail = $content['thumbnail'];
            if (strpos($thumbnail, '../') === 0) {
                $thumbnail = substr($thumbnail, 3);
            }
            if (file_exists($thumbnail)) return $thumbnail;
            $filename  = basename($thumbnail);
            $directPath = 'assets/uploads/thumbnails/' . $filename;
            if (file_exists($directPath)) return $directPath;
            return $thumbnail;
        }
        if (!empty($content['poster_image']) && $content['poster_image'] !== 'assets/img/default-poster.jpg') {
            return $content['poster_image'];
        }
        return 'https://via.placeholder.com/300x450/333/fff?text=' . urlencode(substr($content['title'] ?? '', 0, 15));
    }
}

function generatePageUrl($page) {
    $p = $_GET;
    $p['page'] = $page;
    return 'browse.php?' . http_build_query($p);
}
?>

<div class="page-content">
    <div class="hero-banner">
        <div class="hero-content">
            <div class="hero-tag">BROWSE CONTENT</div>
            <h1>Discover Amazing Content</h1>
            <p>Explore our vast library of movies, TV shows, and exclusive content.</p>

            <div class="search-bar" style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 30px; margin: 20px 0; max-width: 500px;">
                <form method="GET" action="browse.php" style="display: flex; gap: 10px; align-items: center;">
                    <i class="fas fa-search" style="color: #ddd;"></i>
                    <input type="text" name="q" placeholder="Search movies, TV shows..."
                           value="<?php echo htmlspecialchars($search_query); ?>"
                           style="flex: 1; background: transparent; border: none; color: white; outline: none; font-size: 16px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Search</button>
                </form>
            </div>
        </div>
    </div>

    <section class="content-section">
        <div class="filter-section" style="background: var(--card-bg); padding: 25px; border-radius: 15px; margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px; color: var(--primary);">Filters</h3>
            <div class="filter-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">

                <div class="filter-group">
                    <label style="display: block; margin-bottom: 8px; color: #ddd; font-weight: 500;">Category</label>
                    <select id="categoryFilter" onchange="updateFilters()" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php if ($categories_result): while($category = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>"
                            <?php echo $category_filter === $category['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label style="display: block; margin-bottom: 8px; color: #ddd; font-weight: 500;">Type</label>
                    <select id="typeFilter" onchange="updateFilters()" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">
                        <option value="all"     <?php echo $type_filter === 'all'     ? 'selected' : ''; ?>>All Types</option>
                        <option value="movie"   <?php echo $type_filter === 'movie'   ? 'selected' : ''; ?>>Movies</option>
                        <option value="tv_show" <?php echo $type_filter === 'tv_show' ? 'selected' : ''; ?>>TV Shows</option>
                        <option value="kids"    <?php echo $type_filter === 'kids'    ? 'selected' : ''; ?>>Kids</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label style="display: block; margin-bottom: 8px; color: #ddd; font-weight: 500;">Sort By</label>
                    <select id="sortFilter" onchange="updateFilters()" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">
                        <option value="rating"  <?php echo $sort_by === 'rating'  ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="newest"  <?php echo $sort_by === 'newest'  ? 'selected' : ''; ?>>Newest First</option>
                        <option value="title"   <?php echo $sort_by === 'title'   ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="year"    <?php echo $sort_by === 'year'    ? 'selected' : ''; ?>>Release Year</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label style="display: block; margin-bottom: 8px; color: #ddd; font-weight: 500;">Results</label>
                    <div style="padding: 10px; color: #bbb; background: rgba(255,255,255,0.05); border-radius: 8px; text-align: center;">
                        <?php echo $total_items; ?> items found
                    </div>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-film"></i>
                <?php echo htmlspecialchars(!empty($search_query) ? "Search Results for \"$search_query\"" : "All Content"); ?>
            </h2>
        </div>

        <?php if ($content_result && $content_result->num_rows > 0): ?>
            <div class="trending-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                <?php while($content = $content_result->fetch_assoc()): ?>
                <div class="trending-card" data-content-id="<?php echo (int)$content['id']; ?>"
                     style="position: relative; overflow: hidden; border-radius: 10px; height: 300px; cursor: pointer;"
                     onclick="playContent(<?php echo (int)$content['id']; ?>, '<?php echo htmlspecialchars($content['title'], ENT_QUOTES); ?>')">
                    <img src="<?php echo htmlspecialchars(getCorrectImagePath($content)); ?>"
                         alt="<?php echo htmlspecialchars($content['title']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="card-overlay" style="position: absolute; bottom: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); width: 100%; padding: 15px;">
                        <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                        <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
                            <span style="color: #ffc107;"><i class="fas fa-star"></i> <?php echo htmlspecialchars($content['rating']); ?></span>
                            <?php if (!empty($content['category_name'])): ?>
                            <span style="color:#aaa; font-size:0.8rem;"><?php echo htmlspecialchars($content['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:8px; display:flex; gap:8px;">
                            <button class="btn-play" onclick="event.stopPropagation(); playContent(<?php echo (int)$content['id']; ?>, '<?php echo htmlspecialchars($content['title'], ENT_QUOTES); ?>')" style="padding:5px 12px; font-size:0.8rem;">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="btn-watchlist" onclick="event.stopPropagation(); addToWatchlist(<?php echo (int)$content['id']; ?>)" style="padding:5px 12px; font-size:0.8rem;">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="display: flex; gap: 10px; margin-top: 30px; justify-content: center; flex-wrap: wrap;">
                <?php if ($current_page > 1): ?>
                <a href="<?php echo generatePageUrl($current_page - 1); ?>" style="padding: 10px 15px; background: rgba(255,255,255,0.1); color: white; border-radius: 5px; text-decoration: none;">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end   = min($total_pages, $current_page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="<?php echo generatePageUrl($i); ?>"
                   style="padding: 10px 15px; background: <?php echo $i == $current_page ? 'var(--primary)' : 'rgba(255,255,255,0.1)'; ?>; color: white; border-radius: 5px; text-decoration: none; font-weight: <?php echo $i == $current_page ? '700' : '400'; ?>;">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo generatePageUrl($current_page + 1); ?>" style="padding: 10px 15px; background: rgba(255,255,255,0.1); color: white; border-radius: 5px; text-decoration: none;">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-search" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.4;"></i>
                <h3>No Content Found</h3>
                <p style="color: #aaa;">Try adjusting your filters or search terms.</p>
                <a href="browse.php" class="btn btn-primary" style="margin-top: 20px;">Clear Filters</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    function updateFilters() {
        const category = document.getElementById('categoryFilter').value;
        const type     = document.getElementById('typeFilter').value;
        const sort     = document.getElementById('sortFilter').value;
        const params   = new URLSearchParams();
        if (category !== 'all') params.set('category', category);
        if (type     !== 'all') params.set('type', type);
        if (sort     !== 'rating') params.set('sort', sort);
        const q = new URLSearchParams(window.location.search).get('q');
        if (q) params.set('q', q);
        window.location.href = 'browse.php?' + params.toString();
    }

    // Note: playContent(contentId, contentTitle) is provided by includes/footer.php
    // and takes precedence over any same-named function declared here.

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

    function showNotification(message, type) {
        const existing = document.querySelector('.tf-notification');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = 'tf-notification';
        div.textContent = message;
        div.style.cssText = 'position:fixed;bottom:20px;right:20px;background:' +
            (type === 'success' ? '#28a745' : '#dc3545') +
            ';color:#fff;padding:12px 20px;border-radius:8px;z-index:9999;font-weight:600;box-shadow:0 4px 15px rgba(0,0,0,0.3);transition:opacity 0.3s;';
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
