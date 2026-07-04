<?php
// General utility functions for TechFlix

// Format file size
function format_file_size($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
function safe_output($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Add to watchlist
function addToWatchlist($content_id, $user_id) {
    global $conn;
    
    // Check if already in watchlist
    $stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $user_id, $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $user_id, $content_id);
        return $stmt->execute();
    }
    
    return false;
}

// Remove from watchlist
function removeFromWatchlist($content_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ii", $user_id, $content_id);
    return $stmt->execute();
}

// Check if content is in watchlist
function isInWatchlist($content_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $user_id, $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Sanitize output
function sanitize_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Get content rating class
function getRatingClass($rating) {
    if ($rating >= 8.0) return 'rating-high';
    if ($rating >= 6.0) return 'rating-medium';
    return 'rating-low';
}

// Format duration
function format_duration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' min';
    } else {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . 'h ' . $mins . 'm';
    }
}
?>