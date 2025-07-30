<?php
header('Content-Type: application/json');
include '../config.php';

// Example: Fetch new messages count from database (adjust table and column names as needed)
$new_messages_count = 0;

if ($conn) {
    $sql = "SELECT COUNT(*) AS count FROM messages WHERE is_read = 0";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $new_messages_count = (int)$row['count'];
    }
}

echo json_encode([
    'new_messages_count' => $new_messages_count
]);
?>
