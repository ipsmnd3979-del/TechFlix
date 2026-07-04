<?php
require_once 'includes/config.php';

if (isset($_GET['q'])) {
    $searchTerm = $conn->real_escape_string($_GET['q']);
    
    $query = "SELECT * FROM content 
              WHERE title LIKE '%$searchTerm%' 
              OR description LIKE '%$searchTerm%' 
              LIMIT 10";
    
    $result = $conn->query($query);
    $searchResults = [];
    
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($searchResults);
}
?>