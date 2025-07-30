<?php
// Include config for $conn
include '../config.php';

// Create
if (isset($_POST['add_other_expense'])) {
    $stmt = $conn->prepare("INSERT INTO other_expenses (transaction_id, expense_type, details, is_recurring) VALUES (?, ?, ?, ?)");
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $stmt->bind_param("issi", $_POST['transaction_id'], $_POST['expense_type'], $_POST['details'], $is_recurring);
    $stmt->execute();
    $stmt->close();
}

// Read
$otherExpenses = [];
$result = $conn->query("SELECT * FROM other_expenses");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $otherExpenses[] = $row;
    }
    $result->free();
}

// Update
if (isset($_POST['update_other_expense'])) {
    $stmt = $conn->prepare("UPDATE other_expenses SET transaction_id=?, expense_type=?, details=?, is_recurring=? WHERE other_id=?");
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $stmt->bind_param("issii", $_POST['transaction_id'], $_POST['expense_type'], $_POST['details'], $is_recurring, $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Delete
if (isset($_GET['delete_other_expense'])) {
    $stmt = $conn->prepare("DELETE FROM other_expenses WHERE other_id=?");
    $stmt->bind_param("i", $_GET['delete_other_expense']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
}
?>
<form method="post" style="margin-bottom: 20px;">
    <input type="hidden" name="id" value="<?= isset($_GET['edit_other_expense']) ? $_GET['edit_other_expense'] : '' ?>">
    <input type="number" name="transaction_id" placeholder="Transaction ID" required style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;">
    <input type="text" name="expense_type" placeholder="Expense Type" required style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;">
    <textarea name="details" placeholder="Details" style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;"></textarea>
    <label>
        <input type="checkbox" name="is_recurring" <?= isset($_GET['edit_other_expense']) && $otherExpense['is_recurring'] ? 'checked' : '' ?>>
        Recurring Expense
    </label>
    <button type="submit" name="<?= isset($_GET['edit_other_expense']) ? 'update_other_expense' : 'add_other_expense' ?>" style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">
        <?= isset($_GET['edit_other_expense']) ? 'Update' : 'Add' ?> Other Expense
    </button>
    <?php if (isset($_GET['edit_other_expense'])): ?>
        <a href="index.php">Cancel</a>
    <?php endif; ?>
</form>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">ID</th>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Transaction ID</th>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Expense Type</th>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Details</th>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Recurring</th>
        <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Actions</th>
    </tr>
    <?php foreach ($otherExpenses as $expense): ?>
    <tr>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;"><?= $expense['other_id'] ?></td>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;"><?= $expense['transaction_id'] ?></td>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;"><?= $expense['expense_type'] ?></td>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;"><?= $expense['details'] ?></td>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;"><?= $expense['is_recurring'] ? 'Yes' : 'No' ?></td>
        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">
            <a href="index.php?edit_other_expense=<?= $expense['other_id'] ?>">Edit</a>
            <a href="index.php?delete_other_expense=<?= $expense['other_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>