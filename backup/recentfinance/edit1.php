<?php
include '../../../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: view.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    $_SESSION['error'] = "Payment not found";
    header("Location: view.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_date = $_POST['payment_date'];
    $purpose = $_POST['purpose'];
    $amount = $_POST['amount'];
    $beneficiary = $_POST['beneficiary'];

    $stmt = $conn->prepare("UPDATE payments SET payment_date=?, purpose=?, amount=?, beneficiary=? WHERE id=?");
    $stmt->bind_param("ssdsi", $payment_date, $purpose, $amount, $beneficiary, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment updated successfully!";
        header("Location: view.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating payment: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment</title>
</head>
<body>
    <div class="container">
        <h2>Edit Payment</h2>
        
        <?php include '../message/messages.php'; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Payment Date</label>
                <input type="date" name="payment_date" value="<?= htmlspecialchars($payment['payment_date']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Purpose</label>
                <input type="text" name="purpose" value="<?= htmlspecialchars($payment['purpose']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Amount (₱)</label>
                <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($payment['amount']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Beneficiary</label>
                <input type="text" name="beneficiary" value="<?= htmlspecialchars($payment['beneficiary']) ?>" required>
            </div>
            
            <button type="submit" class="btn">Update Payment</button>
            <a href="view.php" class="btn">Cancel</a>
        </form>
    </div>
</body>
</html>