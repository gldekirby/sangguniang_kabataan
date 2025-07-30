<?php
include '../../../config.php';

$sql = "SELECT r.*, CONCAT(m.first_name, ' ', m.last_name) as approver 
        FROM receipts r
        JOIN members m ON r.approved_by = m.member_id
        ORDER BY r.transaction_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Receipts</title>
</head>
<body>
    <div class="container">
        <h2>Receipts Records</h2>
        
        <?php include '../message/messages.php'; ?>
        
        <a href="add.php" class="btn">Add New Receipt</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Amount</th>
                    <th>Approved By</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($row['transaction_date'])) ?></td>
                    <td><?= htmlspecialchars($row['source']) ?></td>
                    <td>₱<?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['approver']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>