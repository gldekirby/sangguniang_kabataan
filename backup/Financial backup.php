<?php
// Database connection (removed PDO)
$servername = "localhost";
$username = "username";
$password = "";
$dbname = "youth_sk";

// Placeholder for database connection
$conn = null; // Replace with your preferred database connection method

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add_expense':
                    // Add expense logic (replace with your database logic)
                    // Example: Insert into financial_transactions and related table
                    // Removed PDO code
                    break;

                case 'update_expense':
                    // Update expense logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'delete_expense':
                    // Delete expense logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'add_fund':
                    // Add fund logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'update_fund':
                    // Update fund logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'delete_fund':
                    // Delete fund logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'add_budget':
                    // Add budget logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'update_budget':
                    // Update budget logic (replace with your database logic)
                    // Removed PDO code
                    break;

                case 'delete_budget':
                    // Delete budget logic (replace with your database logic)
                    // Removed PDO code
                    break;
            }
        } catch (Exception $e) {
            $alert = ['type' => 'danger', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

// Placeholder for fetching data
$expenses = []; // Replace with your database query logic
$funds = []; // Replace with your database query logic
$budgets = []; // Replace with your database query logic

// Placeholder for totals
$totalExpenses = 0; // Replace with your database query logic
$totalFunds = 0; // Replace with your database query logic
$totalBudget = 0; // Replace with your database query logic
$balance = $totalFunds - $totalExpenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Youth Financial Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <?php if (isset($alert)): ?>
            <div class="alert <?= $alert['type'] === 'danger' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> p-4 rounded mb-4">
                <?= $alert['message'] ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md" id="expenses-tab" onclick="showTab('expenses')">Expenses</button>
                <button class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 rounded-md" id="funds-tab" onclick="showTab('funds')">Funds</button>
                <button class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 rounded-md" id="budget-tab" onclick="showTab('budget')">Budget Allocation</button>
            </nav>
        </div>

        <!-- Tabs Content -->
        <div class="mt-4">
            <!-- Expenses Tab -->
            <div id="expenses" class="tab-content">
                <div class="flex justify-between mb-4">
                    <div class="flex space-x-2">
                        <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option>All Categories</option>
                            <option>Equipment</option>
                            <option>Travel</option>
                            <option>Staff</option>
                            <option>Facilities</option>
                            <option>Admin</option>
                            <option>Other</option>
                        </select>
                        <input type="date" class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm">Filter</button>
                    </div>
                    <div class="flex space-x-2">
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm" onclick="showExpenseModal()">
                            <i class="bi bi-plus"></i> Add Expense
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Category</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Description</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Details</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $expense['transaction_id'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= date('M d, Y', strtotime($expense['transaction_date'])) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= ucfirst($expense['category']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $expense['description'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $expense['specific_detail'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">₱<?= number_format($expense['amount'], 2) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    <button class="text-blue-500 hover:underline" onclick="editExpense(<?= $expense['transaction_id'] ?>)">Edit</button>
                                    <button class="text-red-500 hover:underline" onclick="confirmDelete(<?= $expense['transaction_id'] ?>, 'expense')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Funds Tab -->
            <div id="funds" class="tab-content hidden">
                <div class="flex justify-between mb-4">
                    <div class="flex space-x-2">
                        <input type="date" class="border border-gray-300 rounded px-2 py-1 text-sm">
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm">Filter</button>
                    </div>
                    <div class="flex space-x-2">
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm" onclick="showFundModal()">
                            <i class="bi bi-plus"></i> Add Fund
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date Received</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Source</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Purpose</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Amount</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Notes</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($funds as $fund): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $fund['fund_id'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= date('M d, Y', strtotime($fund['received_date'])) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $fund['source'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $fund['purpose'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">₱<?= number_format($fund['amount'], 2) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= substr($fund['notes'], 0, 30) ?><?= strlen($fund['notes']) > 30 ? '...' : '' ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    <button class="text-blue-500 hover:underline" onclick="editFund(<?= $fund['fund_id'] ?>)">Edit</button>
                                    <button class="text-red-500 hover:underline" onclick="confirmDelete(<?= $fund['fund_id'] ?>, 'fund')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Budget Allocation Tab -->
            <div id="budget" class="tab-content hidden">
                <div class="flex justify-between mb-4">
                    <div class="flex space-x-2">
                        <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option>All Fiscal Years</option>
                            <option>2023</option>
                            <option>2024</option>
                            <option>2025</option>
                        </select>
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm">Filter</button>
                    </div>
                    <div class="flex space-x-2">
                        <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm" onclick="showBudgetModal()">
                            <i class="bi bi-plus"></i> Add Budget
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">ID</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Category</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Fiscal Year</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Allocated Amount</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Notes</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $budget['budget_id'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= ucfirst($budget['category']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $budget['fiscal_year'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">₱<?= number_format($budget['allocated_amount'], 2) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= substr($budget['notes'], 0, 30) ?><?= strlen($budget['notes']) > 30 ? '...' : '' ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    <button class="text-blue-500 hover:underline" onclick="editBudget(<?= $budget['budget_id'] ?>)">Edit</button>
                                    <button class="text-red-500 hover:underline" onclick="confirmDelete(<?= $budget['budget_id'] ?>, 'budget')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Modal -->
    <div id="expenseModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-bold mb-4">Add Expense</h2>
            <form id="expenseForm">
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" name="category" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="">Select Category</option>
                        <option value="equipment">Equipment</option>
                        <option value="travel">Travel</option>
                        <option value="staff">Staff</option>
                        <option value="facilities">Facilities</option>
                        <option value="admin">Admin</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="transaction_date" name="transaction_date" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" id="description" name="description" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" id="amount" name="amount" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2" onclick="hideModal('expenseModal')">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fund Modal -->
    <div id="fundModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-bold mb-4">Add Fund</h2>
            <form id="fundForm">
                <div class="mb-4">
                    <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                    <select id="source" name="source" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="">Select Source</option>
                        <option value="SK Funds">SK Funds</option>
                        <option value="LGU Allocation">LGU Allocation</option>
                        <option value="Donation">Donation</option>
                        <option value="Fundraising">Fundraising</option>
                        <option value="Government Grant">Government Grant</option>
                        <option value="Private Sponsor">Private Sponsor</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="received_date" class="block text-sm font-medium text-gray-700">Date Received</label>
                    <input type="date" id="received_date" name="received_date" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" id="amount" name="amount" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                    <input type="text" id="purpose" name="purpose" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" name="notes" class="border border-gray-300 rounded px-3 py-2 w-full"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2" onclick="hideModal('fundModal')">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Budget Modal -->
    <div id="budgetModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-bold mb-4">Add Budget Allocation</h2>
            <form id="budgetForm">
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" name="category" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="">Select Category</option>
                        <option value="equipment">Equipment</option>
                        <option value="travel">Travel</option>
                        <option value="staff">Staff</option>
                        <option value="facilities">Facilities</option>
                        <option value="admin">Admin</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="fiscal_year" class="block text-sm font-medium text-gray-700">Fiscal Year</label>
                    <select id="fiscal_year" name="fiscal_year" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="">Select Year</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="allocated_amount" class="block text-sm font-medium text-gray-700">Allocated Amount</label>
                    <input type="number" id="allocated_amount" name="allocated_amount" class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" name="notes" class="border border-gray-300 rounded px-3 py-2 w-full"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded mr-2" onclick="hideModal('budgetModal')">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Confirm Delete</h4>
                <button type="button" class="btn-close" onclick="hideModal('confirmModal')"></button>
            </div>
            
            <p>Are you sure you want to delete this record?</p>
            
            <form id="deleteForm" method="post">
                <input type="hidden" name="action" id="deleteAction">
                <input type="hidden" name="transaction_id" id="deleteTransactionId">
                <input type="hidden" name="fund_id" id="deleteFundId">
                <input type="hidden" name="budget_id" id="deleteBudgetId">
                
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="hideModal('confirmModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');
            document.querySelectorAll('[aria-label="Tabs"] button').forEach(btn => btn.classList.remove('bg-gray-200'));
            document.getElementById(tabId + '-tab').classList.add('bg-gray-200');
        }

        function showExpenseModal() {
            document.getElementById('expenseModal').classList.remove('hidden');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Show/hide modals
        function showExpenseModal() {
            resetExpenseForm();
            document.getElementById('expenseModalTitle').textContent = 'Add New Expense';
            document.getElementById('expenseAction').value = 'add_expense';
            document.getElementById('expenseModal').style.display = 'flex';
        }
        
        function showFundModal() {
            resetFundForm();
            document.getElementById('fundModalTitle').textContent = 'Add New Fund';
            document.getElementById('fundAction').value = 'add_fund';
            document.getElementById('fundModal').style.display = 'flex';
        }
        
        function showBudgetModal() {
            resetBudgetForm();
            document.getElementById('budgetModalTitle').textContent = 'Add Budget Allocation';
            document.getElementById('budgetAction').value = 'add_budget';
            document.getElementById('budgetModal').style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Reset forms
        function resetExpenseForm() {
            document.getElementById('expenseForm').reset();
            document.getElementById('transactionId').value = '';
            hideAllCategoryForms();
        }
        
        function resetFundForm() {
            document.getElementById('fundForm').reset();
            document.getElementById('fundId').value = '';
        }
        
        function resetBudgetForm() {
            document.getElementById('budgetForm').reset();
            document.getElementById('budgetId').value = '';
        }
        
        // Category form handling
        function hideAllCategoryForms() {
            const forms = document.querySelectorAll('.category-form');
            forms.forEach(form => form.style.display = 'none');
        }
        
        function showCategoryForm() {
            hideAllCategoryForms();
            const category = document.getElementById('category').value;
            if (category) {
                document.getElementById(category + 'Form').style.display = 'block';
            }
        }
        
        // Edit functions
        async function editExpense(id) {
            try {
                // Fetch expense data
                const response = await fetch(`get_expense.php?id=${id}`);
                const expense = await response.json();
                
                // Populate main form
                document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
                document.getElementById('expenseAction').value = 'update_expense';
                document.getElementById('transactionId').value = expense.transaction_id;
                document.getElementById('category').value = expense.category;
                document.getElementById('description').value = expense.description;
                document.getElementById('amount').value = expense.amount;
                document.getElementById('transaction_date').value = expense.transaction_date;
                
                // Show category form and populate fields
                showCategoryForm();
                
                // Populate category-specific fields
                if (expense.category === 'equipment') {
                    document.getElementById('item_name').value = expense.item_name;
                    document.getElementById('quantity').value = expense.quantity;
                    document.getElementById('unit_price').value = expense.unit_price;
                    document.getElementById('supplier').value = expense.supplier;
                } else if (expense.category === 'travel') {
                    document.getElementById('trip_purpose').value = expense.trip_purpose;
                    document.getElementById('destination').value = expense.destination;
                    document.getElementById('start_date').value = expense.start_date;
                    document.getElementById('end_date').value = expense.end_date;
                    document.getElementById('participants').value = expense.participants;
                } else if (expense.category === 'staff') {
                    document.getElementById('staff_name').value = expense.staff_name;
                    document.getElementById('role').value = expense.role;
                    document.getElementById('payment_period').value = expense.payment_period;
                } else if (expense.category === 'facilities') {
                    document.getElementById('venue_name').value = expense.venue_name;
                    document.getElementById('purpose').value = expense.purpose;
                    document.getElementById('duration_hours').value = expense.duration_hours;
                } else if (expense.category === 'admin') {
                    document.getElementById('expense_type').value = expense.expense_type;
                    document.getElementById('vendor').value = expense.vendor;
                    document.getElementById('service_period').value = expense.service_period;
                } else if (expense.category === 'other') {
                    document.getElementById('expense_type').value = expense.expense_type;
                    document.getElementById('details').value = expense.details;
                    document.getElementById('is_recurring').checked = expense.is_recurring == 1;
                }
                
                document.getElementById('expenseModal').style.display = 'flex';
            } catch (error) {
                alert('Error loading expense data: ' + error.message);
            }
        }
        
        async function editFund(id) {
            try {
                // Fetch fund data
                const response = await fetch(`get_fund.php?id=${id}`);
                const fund = await response.json();
                
                // Populate form
                document.getElementById('fundModalTitle').textContent = 'Edit Fund';
                document.getElementById('fundAction').value = 'update_fund';
                document.getElementById('fundId').value = fund.fund_id;
                document.getElementById('source').value = fund.source;
                document.getElementById('received_date').value = fund.received_date;
                document.getElementById('amount').value = fund.amount;
                document.getElementById('purpose').value = fund.purpose;
                document.getElementById('notes').value = fund.notes;
                
                document.getElementById('fundModal').style.display = 'flex';
            } catch (error) {
                alert('Error loading fund data: ' + error.message);
            }
        }
        
        async function editBudget(id) {
            try {
                // Fetch budget data
                const response = await fetch(`get_budget.php?id=${id}`);
                const budget = await response.json();
                
                // Populate form
                document.getElementById('budgetModalTitle').textContent = 'Edit Budget Allocation';
                document.getElementById('budgetAction').value = 'update_budget';
                document.getElementById('budgetId').value = budget.budget_id;
                document.getElementById('category').value = budget.category;
                document.getElementById('fiscal_year').value = budget.fiscal_year;
                document.getElementById('allocated_amount').value = budget.allocated_amount;
                document.getElementById('notes').value = budget.notes;
                
                document.getElementById('budgetModal').style.display = 'flex';
            } catch (error) {
                alert('Error loading budget data: ' + error.message);
            }
        }
        
        // Delete confirmation
        function confirmDelete(id, type) {
            // Reset all delete fields first
            document.getElementById('deleteTransactionId').value = '';
            document.getElementById('deleteFundId').value = '';
            document.getElementById('deleteBudgetId').value = '';
            
            if (type === 'expense') {
                document.getElementById('deleteAction').value = 'delete_expense';
                document.getElementById('deleteTransactionId').value = id;
            } else if (type === 'fund') {
                document.getElementById('deleteAction').value = 'delete_fund';
                document.getElementById('deleteFundId').value = id;
            } else if (type === 'budget') {
                document.getElementById('deleteAction').value = 'delete_budget';
                document.getElementById('deleteBudgetId').value = id;
            }
            
            document.getElementById('confirmModal').style.display = 'flex';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal-overlay') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>