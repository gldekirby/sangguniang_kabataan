<?php
include '../../../config.php';

$sql = "SELECT * FROM budgets ORDER BY fiscal_year DESC, category";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Budgets</title>
</head>
<body>
    
    <div class="container">
        <h2>Budget Overview</h2>
        
        <?php include '../message/messages.php'; ?>
        
        <a href="add.php" class="btn">Add New Budget</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Program</th>
                    <th>Amount</th>
                    <th>Fiscal Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['subcategory']) ?></td>
                    <td><?= htmlspecialchars($row['program_name']) ?></td>
                    <td>₱<?= number_format($row['allocated_amount'], 2) ?></td>
                    <td><?= $row['fiscal_year'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn-small">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>