<?php
include '../../../config.php';

$sql = "SELECT p.* 
        FROM payments p
        ORDER BY p.payment_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Payments</title>
</head>
<body>
    
    <div class="container">
        <h2>Payments Records</h2>
        
        <a href="add.php" class="btn">Add New Payment</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Beneficiary</th>
                    <th>Authorized By</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($row['purpose']) ?></td>
                    <td>₱<?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['beneficiary']) ?></td>
                    <td><?= htmlspecialchars($row['authorized_by']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>