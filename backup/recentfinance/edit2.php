<?php
include '../../../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: view.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM receipts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    $_SESSION['error'] = "Receipt not found";
    header("Location: view.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_date = $_POST['transaction_date'];
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE receipts SET transaction_date=?, source=?, amount=?, description=? WHERE id=?");
    $stmt->bind_param("ssdsi", $transaction_date, $source, $amount, $description, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Receipt updated successfully!";
        header("Location: view.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating receipt: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Receipt</title>
</head>
<body>
    
    <div class="container">
        <h2>Edit Receipt</h2>
        
        <?php include '../message/messages.php'; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Transaction Date</label>
                <input type="date" name="transaction_date" value="<?= htmlspecialchars($receipt['transaction_date']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Source</label>
                <input type="text" name="source" value="<?= htmlspecialchars($receipt['source']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Amount (₱)</label>
                <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($receipt['amount']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($receipt['description']) ?></textarea>
            </div>
            
            <button type="submit" class="btn">Update Receipt</button>
            <a href="view.php" class="btn">Cancel</a>
        </form>
    </div>
</body>
</html>