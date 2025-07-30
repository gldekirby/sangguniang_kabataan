<?php
session_start();
include 'C:/xampp/htdocs/www.sangguniang_kabataan.com/config.php';

// Check if the user is logged in
if (isset($_SESSION['member_id'])) {
    $member_id = $_SESSION['member_id'];

    try {
        // Update the user's status to inactive
        $query = "UPDATE members SET status = 'inactive' WHERE member_id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                error_log("No rows affected when updating status for member_id: $member_id");
            }
            
            $stmt->close();
        } else {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login_member.php");
exit();
?>