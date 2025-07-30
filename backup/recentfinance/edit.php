<?php
include '../../../config.php';

// Get budget ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: view.php");
    exit();
}

// Fetch existing budget data
$stmt = $conn->prepare("SELECT * FROM budgets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$budget = $result->fetch_assoc();

if (!$budget) {
    $_SESSION['error'] = "Budget not found";
    header("Location: view.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $program_name = $_POST['program_name'];
    $allocated_amount = $_POST['allocated_amount'];
    $fiscal_year = $_POST['fiscal_year'];

    $stmt = $conn->prepare("UPDATE budgets SET category=?, subcategory=?, program_name=?, allocated_amount=?, fiscal_year=? WHERE id=?");
    $stmt->bind_param("sssssi", $category, $subcategory, $program_name, $allocated_amount, $fiscal_year, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Budget updated successfully!";
        header("Location: view.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating budget: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Budget</title>
</head>
<body>
    
    <div class="container">
        <h2>Edit Budget</h2>
        
        <?php include '../message/messages.php'; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" value="<?= htmlspecialchars($budget['category']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Subcategory</label>
                <input type="text" name="subcategory" value="<?= htmlspecialchars($budget['subcategory']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Program Name</label>
                <input type="text" name="program_name" value="<?= htmlspecialchars($budget['program_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Allocated Amount (₱)</label>
                <input type="number" step="0.01" name="allocated_amount" value="<?= htmlspecialchars($budget['allocated_amount']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Fiscal Year</label>
                <input type="number" name="fiscal_year" min="2000" max="2100" value="<?= htmlspecialchars($budget['fiscal_year']) ?>" required>
            </div>
            
            <button type="submit" class="btn">Update Budget</button>
            <a href="view.php" class="btn">Cancel</a>
        </form>
    </div>
</body>
</html>