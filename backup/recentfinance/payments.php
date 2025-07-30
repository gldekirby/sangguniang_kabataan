<?php
include '../config.php';

// Delete action
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting payment: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Edit action - form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $payment_date = $_POST['payment_date'];
    $purpose = $_POST['purpose'];
    $amount = $_POST['amount'];
    $beneficiary = $_POST['beneficiary'];

    $stmt = $conn->prepare("UPDATE payments SET payment_date = ?, purpose = ?, amount = ?, beneficiary = ? WHERE id = ?");
    $stmt->bind_param("ssdsi", $payment_date, $purpose, $amount, $beneficiary, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating payment: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Add new payment action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_id'])) {
    $payment_date = $_POST['payment_date'];
    $purpose = $_POST['purpose'];
    $amount = $_POST['amount'];
    $beneficiary = $_POST['beneficiary'];
    $authorized_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO payments (payment_date, purpose, amount, beneficiary, authorized_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsi", $payment_date, $purpose, $amount, $beneficiary, $authorized_by);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment recorded successfully!";
    } else {
        $_SESSION['error'] = "Error recording payment: " . $conn->error;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch payment for editing
$edit_payment = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_payment = $result->fetch_assoc();
}

// Fetch payments to display in the table
$payments = [];
$result = $conn->query("SELECT * FROM payments ORDER BY payment_date DESC");
if ($result) {
    $payments = $result->fetch_all(MYSQLI_ASSOC);
}

// Calculate total payment amount
$total_amount = 0;
foreach ($payments as $payment) {
    $total_amount += $payment['amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Management</title>
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
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .amount {
            text-align: right;
        }
        .payment-table {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Management</h1>
        
        <?php include 'message/messages.php'; ?>
        
        <!-- Add New Payment Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#paymentModal">
            Record New Payment
        </button>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Record New Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" required 
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <input type="text" name="purpose" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Beneficiary</label>
                                <input type="text" name="beneficiary" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <?php if (isset($edit_payment)): ?>
        <div class="modal fade show" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="false" style="display: block; padding-right: 15px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Payment</h5>
                        <a href="?" class="btn-close" aria-label="Close"></a>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="edit_id" value="<?= $edit_payment['id'] ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" required 
                                       value="<?= $edit_payment['payment_date'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <input type="text" name="purpose" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_payment['purpose']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required 
                                       value="<?= $edit_payment['amount'] ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Beneficiary</label>
                                <input type="text" name="beneficiary" class="form-control" required 
                                       value="<?= htmlspecialchars($edit_payment['beneficiary']) ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="?" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
        <?php endif; ?>

        <h2>Payment Records</h2>
        <div class="table-responsive payment-table">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Purpose</th>
                        <th>Beneficiary</th>
                        <th>Amount (₱)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6">No payment records found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                                <td><?= htmlspecialchars($payment['purpose']) ?></td>
                                <td><?= htmlspecialchars($payment['beneficiary']) ?></td>
                                <td class="amount"><?= number_format($payment['amount'], 2) ?></td>
                                <td class="action-btns">
                                    <a href="?edit_id=<?= $payment['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete_id=<?= $payment['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this payment?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right;">Total Payments (₱)</td>
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
            <?php if (isset($edit_payment)): ?>
                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>