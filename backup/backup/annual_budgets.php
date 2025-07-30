<?php
include '../config.php';

// Delete action
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Budget deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting budget: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Edit action - form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $program_name = $_POST['program_name'];
    $allocated_amount = $_POST['allocated_amount'];
    $fiscal_year = $_POST['fiscal_year'];

    $stmt = $conn->prepare("UPDATE budgets SET category = ?, subcategory = ?, program_name = ?, allocated_amount = ?, fiscal_year = ? WHERE id = ?");
    $stmt->bind_param("sssdsi", $category, $subcategory, $program_name, $allocated_amount, $fiscal_year, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Budget updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating budget: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Add new budget action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_id'])) {
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $program_name = $_POST['program_name'];
    $allocated_amount = $_POST['allocated_amount'];
    $fiscal_year = $_POST['fiscal_year'];

    $stmt = $conn->prepare("INSERT INTO budgets (category, subcategory, program_name, allocated_amount, fiscal_year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $category, $subcategory, $program_name, $allocated_amount, $fiscal_year);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Budget added successfully!";
    } else {
        $_SESSION['error'] = "Error adding budget: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch budget for editing
$edit_budget = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM budgets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_budget = $result->fetch_assoc();
}

// Fetch budgets to display in the table
$budgets = [];
$result = $conn->query("SELECT * FROM budgets ORDER BY fiscal_year DESC, category, subcategory");
if ($result) {
    $budgets = $result->fetch_all(MYSQLI_ASSOC);
}

// Calculate total allocated amount
$total_amount = 0;
foreach ($budgets as $budget) {
    $total_amount += $budget['allocated_amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .table-responsive {
            margin-top: 30px;
        }
        .action-btns {
            white-space: nowrap;
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .amount {
            text-align: right;
        }
        .budget-table {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Budget Management</h1>
        
        <?php include 'message/messages.php'; ?>
        
        <!-- Add New Budget Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#budgetModal">
            Add New Budget
        </button>

        <!-- Budget Modal -->
        <div class="modal fade" id="budgetModal" tabindex="-1" aria-labelledby="budgetModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="budgetModalLabel">Add New Budget</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Subcategory</label>
                                <input type="text" name="subcategory" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Program Name</label>
                                <input type="text" name="program_name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Allocated Amount (₱)</label>
                                <input type="number" step="0.01" name="allocated_amount" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fiscal Year</label>
                                <input type="number" name="fiscal_year" min="2000" max="2100" 
                                       value="<?= date('Y') ?>" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Budget</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if (isset($edit_budget)): ?>
        <div class="modal fade show" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="false" style="display: block; padding-right: 15px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Budget</h5>
                        <a href="?" class="btn-close" aria-label="Close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="edit_id" value="<?= $edit_budget['id'] ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_budget['category']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Subcategory</label>
                                <input type="text" name="subcategory" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_budget['subcategory']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Program Name</label>
                                <input type="text" name="program_name" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_budget['program_name']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Allocated Amount (₱)</label>
                                <input type="number" step="0.01" name="allocated_amount" class="form-control" required 
                                       value="<?= $edit_budget['allocated_amount'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fiscal Year</label>
                                <input type="number" name="fiscal_year" min="2000" max="2100" class="form-control" required 
                                       value="<?= $edit_budget['fiscal_year'] ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="?" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Budget</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
        <?php endif; ?>

        <h2>Budget Records</h2>
        <div class="table-responsive budget-table">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Program Name</th>
                        <th>Fiscal Year</th>
                        <th>Allocated Amount (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($budgets)): ?>
                        <tr>
                            <td colspan="6">No budget entries found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($budgets as $budget): ?>
                            <tr>
                                <td><?= htmlspecialchars($budget['category']) ?></td>
                                <td><?= htmlspecialchars($budget['subcategory']) ?></td>
                                <td><?= htmlspecialchars($budget['program_name']) ?></td>
                                <td><?= htmlspecialchars($budget['fiscal_year']) ?></td>
                                <td class="amount"><?= number_format($budget['allocated_amount'], 2) ?></td>
                                <td class="action-btns">
                                    <a href="?edit_id=<?= $budget['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete_id=<?= $budget['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this budget?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;">Total Allocated Amount (₱)</td>
                        <td class="amount"><?= number_format($total_amount, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show edit modal if there's an edit_id in the URL
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($edit_budget)): ?>
                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>