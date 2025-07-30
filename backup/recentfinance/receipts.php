<?php
include '../config.php';

// Delete action
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM receipts WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Receipt deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting receipt: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Edit action - form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $transaction_date = $_POST['transaction_date'];
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE receipts SET transaction_date = ?, source = ?, amount = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssdsi", $transaction_date, $source, $amount, $description, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Receipt updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating receipt: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Add new receipt action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_id'])) {
    $transaction_date = $_POST['transaction_date'];
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $approved_by = 1;

    $stmt = $conn->prepare("INSERT INTO receipts (transaction_date, source, amount, approved_by, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $transaction_date, $source, $amount, $approved_by, $description);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Receipt recorded successfully!";
    } else {
        $_SESSION['error'] = "Error recording receipt: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch receipt for editing
$edit_receipt = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM receipts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_receipt = $result->fetch_assoc();
}

// Fetch receipts to display in the table
$receipts = [];
$result = $conn->query("SELECT * FROM receipts ORDER BY transaction_date DESC");
if ($result) {
    $receipts = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        textarea {
            height: 100px;
        }
        table {
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
    </style>
</head>
<body>
    <div class="container p-4">
        <?php include 'message/messages.php'; ?>
        
        <!-- Add New Receipt Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
            Add New Receipt
        </button>
        <a href="receipt_report.php" class="btn btn-secondary mb-3">Print</a>

        <!-- Receipt Modal -->
        <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="receiptModalLabel">Record New Receipt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Transaction Date</label>
                                <input type="date" name="transaction_date" class="form-control" required 
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Source</label>
                                <input type="text" name="source" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Record Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if (isset($edit_receipt)): ?>
        <div class="modal fade show" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="false" style="display: block; padding-right: 15px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Receipt</h5>
                        <a href="?" class="btn-close" aria-label="Close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="edit_id" value="<?= $edit_receipt['id'] ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Transaction Date</label>
                                <input type="date" name="transaction_date" class="form-control" required 
                                    value="<?= $edit_receipt['transaction_date'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Source</label>
                                <input type="text" name="source" class="form-control" required 
                                    value="<?= htmlspecialchars($edit_receipt['source']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required 
                                    value="<?= $edit_receipt['amount'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control"><?= htmlspecialchars($edit_receipt['description']) ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="?" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
        <?php endif; ?>

        <h2>Receipt Records</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Description</th>
                        <th>Approved By</th>
                        <th>Amount (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receipts)): ?>
                        <tr>
                            <td colspan="6">No receipts found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td><?= htmlspecialchars($receipt['transaction_date']) ?></td>
                                <td><?= htmlspecialchars($receipt['source']) ?></td>
                                <td><?= htmlspecialchars($receipt['description']) ?></td>
                                <td><?= htmlspecialchars($receipt['approved_by']) ?></td>
                                <td><?= number_format($receipt['amount'], 2) ?></td>
                                <td class="action-btns">
                                    <a href="?edit_id=<?= $receipt['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete_id=<?= $receipt['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this receipt?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;">Total Amount (₱)</td>
                        <td>
                            <?php
                            $total_amount = 0;
                            foreach ($receipts as $receipt) {
                                $total_amount += $receipt['amount'];
                            }
                            echo number_format($total_amount, 2);
                            ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show edit modal if there's an edit_id in the URL
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($edit_receipt)): ?>
                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>