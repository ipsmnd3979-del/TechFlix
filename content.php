<?php
require_once __DIR__ . '/../includes/config.php';

// Safe include loading mechanics framework patch
if (file_exists(__DIR__ . '/../includes/header.php')) {
    require_once __DIR__ . '/../includes/header.php';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit();
}

// Handle administrative mutations safely using Prepared Statements
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_content'])) {
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $type = htmlspecialchars(trim($_POST['type'] ?? 'movie'));
        $category_id = intval($_POST['category_id'] ?? 0);
        $release_year = intval($_POST['release_year'] ?? 2026);
        $duration = intval($_POST['duration'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0.0);
        $trailer_url = htmlspecialchars(trim($_POST['trailer_url'] ?? ''));
        $content_url = htmlspecialchars(trim($_POST['content_url'] ?? ''));
        
        $poster_image = ''; 
        $thumbnail = '';

        $stmt = $conn->prepare("INSERT INTO content (title, description, type, category_id, release_year, duration, rating, poster_image, thumbnail, trailer_url, content_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiidssss", $title, $description, $type, $category_id, $release_year, $duration, $rating, $poster_image, $thumbnail, $trailer_url, $content_url);
        
        if ($stmt->execute()) {
            $success_message = "Content added successfully!";
        } else {
            $error_message = "Failed to add content: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Setup safe pagination offsets
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$content_query = "SELECT c.*, cat.name as category_name FROM content c LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($content_query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$content_result = $stmt->get_result();
?>
<div class="page-content">
    <h3>Content management system console panel</h3>
    </div>
<?php require_once '../includes/footer.php'; ?>