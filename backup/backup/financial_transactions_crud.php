<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'youth_sk';

$mysqli = new mysqli($host, $user, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
// Create
if (isset($_POST['add_transaction'])) {
    $stmt = $mysqli->prepare("INSERT INTO financial_transactions (category, description, amount, transaction_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $_POST['category'], $_POST['description'], $_POST['amount'], $_POST['transaction_date']);
    $stmt->execute();
    $stmt->close();
}

// Read
$result = $mysqli->query("SELECT * FROM financial_transactions");
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Update
if (isset($_POST['update_transaction'])) {
    $stmt = $mysqli->prepare("UPDATE financial_transactions SET category=?, description=?, amount=?, transaction_date=? WHERE transaction_id=?");
    $stmt->bind_param("ssdsi", $_POST['category'], $_POST['description'], $_POST['amount'], $_POST['transaction_date'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Delete
if (isset($_GET['delete_transaction'])) {
    // First delete dependent equipment rows
    $stmt = $mysqli->prepare("DELETE FROM equipment WHERE transaction_id=?");
    $stmt->bind_param("i", $_GET['delete_transaction']);
    $stmt->execute();
    $stmt->close();

    // Then delete the financial transaction
    $stmt = $mysqli->prepare("DELETE FROM financial_transactions WHERE transaction_id=?");
    $stmt->bind_param("i", $_GET['delete_transaction']);
    $stmt->execute();
    $stmt->close();

    // Use JavaScript for redirection instead of header
    echo '<script>window.location.href = "dashboard.php?page=financial_records&subpage=financial_transactions_crud";</script>';
    exit;
}
?>
<!-- Removed the financial transaction form as per request -->

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tr>
        <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">ID</th>
        <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Category</th>
        <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Description</th>
        <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Amount</th>
        <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Date</th>
    </tr>
    <?php foreach ($transactions as $transaction): ?>
    <tr style="border: 1px solid #ddd; padding: 8px; text-align: left;" onmouseover="this.style.backgroundColor='#f1f1f1';" onmouseout="this.style.backgroundColor='';">
        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?= $transaction['transaction_id'] ?></td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?= $transaction['category'] ?></td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?= $transaction['description'] ?></td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?= $transaction['amount'] ?></td>
        <td style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?= $transaction['transaction_date'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>