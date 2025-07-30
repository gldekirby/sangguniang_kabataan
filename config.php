<?php
$servername = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "sk_youth"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$mysqli = $conn; // Alias for compatibility

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
