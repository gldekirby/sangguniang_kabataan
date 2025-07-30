<?php
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $method = $_POST['login_method'] ?? 'username';
    
    // Prepare the query based on login method
    $query = "SELECT COUNT(*) as count FROM members WHERE $method = ?";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode(['exists' => $row['count'] > 0]);
    exit();
}

echo json_encode(['exists' => false]);
?>