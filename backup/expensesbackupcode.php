<?php
$mysqli = new mysqli('localhost', 'root', '', 'youth_sk'); // Database connection

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

function fetchAll($sql, $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function execute($sql, $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt->execute();
}

function addTransaction($category, $description, $amount, $transaction_date) {
    $sql = "INSERT INTO financial_transactions (category, description, amount, transaction_date) 
            VALUES (?, ?, ?, ?)";
    return execute($sql, [$category, $description, $amount, $transaction_date]);
}

function addEquipmentExpense($transaction_id, $item_name, $quantity, $unit_price, $supplier) {
    $sql = "INSERT INTO equipment (transaction_id, item_name, quantity, unit_price, supplier) 
            VALUES (?, ?, ?, ?, ?)";
    return execute($sql, [$transaction_id, $item_name, $quantity, $unit_price, $supplier]);
}

function getAllExpenses() {
    $sql = "SELECT ft.*, 
           e.item_name AS equipment_item, 
           e.quantity, e.unit_price, e.supplier,
           t.trip_purpose AS travel_purpose, t.destination, t.start_date, t.end_date, t.participants,
           a.expense_type AS admin_type, a.vendor, a.service_period,
           s.staff_name,
           f.venue_name,
           o.expense_type AS expense_type_other, o.details, o.is_recurring
           FROM financial_transactions ft
           LEFT JOIN equipment e ON ft.transaction_id = e.transaction_id
           LEFT JOIN travel_expenses t ON ft.transaction_id = t.transaction_id
           LEFT JOIN admin_expenses a ON ft.transaction_id = a.transaction_id
           LEFT JOIN staff_payments s ON ft.transaction_id = s.transaction_id
           LEFT JOIN facility_costs f ON ft.transaction_id = f.transaction_id
           LEFT JOIN other_expenses o ON ft.transaction_id = o.transaction_id
           ORDER BY ft.transaction_date DESC";
    
    return fetchAll($sql);
}

function getExpenseTotals() {
    $sql = "SELECT category, SUM(amount) as total FROM financial_transactions GROUP BY category";
    return fetchAll($sql);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $category = $_POST['category'] ?? null;
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d');

    if ($category && addTransaction($category, $description, $amount, $transaction_date)) {
        $message = "Transaction added successfully!";
        $transaction_id = $mysqli->insert_id;

        switch ($category) {
            case 'equipment':
                if (isset($_POST['item_name'])) {
                    addEquipmentExpense(
                        $transaction_id,
                        $_POST['item_name'],
                        $_POST['quantity'] ?? 1,
                        $_POST['unit_price'] ?? 0,
                        $_POST['supplier'] ?? ''
                    );
                }
                break;
                
            case 'travel':
                if (isset($_POST['trip_purpose'])) {
                    $sql = "INSERT INTO travel_expenses (transaction_id, trip_purpose, destination, start_date, end_date, participants) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    execute($sql, [
                        $transaction_id,
                        $_POST['trip_purpose'],
                        $_POST['destination'] ?? '',
                        $_POST['start_date'] ?? null,
                        $_POST['end_date'] ?? null,
                        $_POST['participants'] ?? 1
                    ]);
                }
                break;
                
            case 'admin':
                if (isset($_POST['expense_type'])) {
                    $sql = "INSERT INTO admin_expenses (transaction_id, expense_type, vendor, service_period) 
                            VALUES (?, ?, ?, ?)";
                    execute($sql, [
                        $transaction_id,
                        $_POST['expense_type'],
                        $_POST['vendor'] ?? '',
                        $_POST['service_period'] ?? ''
                    ]);
                }
                break;
                
            case 'other':
                if (isset($_POST['description'])) {
                    $sql = "INSERT INTO other_expenses (transaction_id, expense_type, details, is_recurring) 
                            VALUES (?, ?, ?, ?)";
                    execute($sql, [
                        $transaction_id,
                        $_POST['expense_type_other'] ?? '',
                        $_POST['details'] ?? '',
                        isset($_POST['is_recurring']) ? 1 : 0
                    ]);
                }
                break;
        }
        
        // Refresh data after successful submission
        $expenses = getAllExpenses();
        $totals = getExpenseTotals();
    } else {
        $error = "Error adding transaction. Please check the form.";
    }
}

$expenses = getAllExpenses();
$totals = getExpenseTotals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Expenses Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    />
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <?php if (isset($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="relative w-full sm:w-64">
                <input
                  type="text"
                  id="searchInput"
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded"
                  placeholder="Search in all categories..."
                  aria-label="Search expenses"
                />
                <div class="absolute left-3 top-2.5 text-gray-400 pointer-events-none">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <div class="flex items-center space-x-2">
                    <select id="filterSelect" class="border border-gray-300 rounded px-4 py-2" aria-label="Filter expenses">
                        <option value="date">Date</option>
                        <option value="alphabet">Alphabet</option>
                        <option value="amount">Amount</option>
                        <option value="category">Category</option>
                    </select>
                    <button id="applyFilterButton" class="bg-blue-500 text-white px-4 py-2 rounded hidden" aria-label="Apply filter">Apply</button>
                </div>
                <select id="addExpenseSelect" class="bg-blue-500 text-white px-4 py-2 rounded" aria-label="Add new expense category">
                    <option value="" disabled selected>Add New Expense</option>
                    <option value="equipment">Equipment</option>
                    <option value="travel">Travel</option>
                    <option value="admin">Admin</option>
                    <option value="other">Other</option>
                </select>
                <button id="printButton" class="bg-green-500 text-white px-4 py-2 rounded" aria-label="Print expenses">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div id="expensesTables" class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Equipment Expenses Table -->
            <section aria-labelledby="equipmentHeading" class="mb-8">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 id="equipmentHeading" class="text-xl font-bold">Equipment Expenses</h3>
                    <input
                      type="text"
                      id="searchEquipment"
                      class="w-48 p-1 border border-gray-300 rounded"
                      placeholder="Search Equipment..."
                      aria-label="Search equipment expenses"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="equipmentTable" role="table" aria-describedby="equipmentDesc">
                        <caption id="equipmentDesc" class="sr-only">List of equipment expenses</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody id="equipmentTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </section>

            <!-- Travel Expenses Table -->
            <section aria-labelledby="travelHeading" class="mb-8">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 id="travelHeading" class="text-xl font-bold">Travel Expenses</h3>
                    <input
                      type="text"
                      id="searchTravel"
                      class="w-48 p-1 border border-gray-300 rounded"
                      placeholder="Search Travel..."
                      aria-label="Search travel expenses"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="travelTable" role="table" aria-describedby="travelDesc">
                        <caption id="travelDesc" class="sr-only">List of travel expenses</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="travelTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </section>

            <!-- Admin Expenses Table -->
            <section aria-labelledby="adminHeading" class="mb-8">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 id="adminHeading" class="text-xl font-bold">Admin Expenses</h3>
                    <input
                      type="text"
                      id="searchAdmin"
                      class="w-48 p-1 border border-gray-300 rounded"
                      placeholder="Search Admin..."
                      aria-label="Search admin expenses"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="adminTable" role="table" aria-describedby="adminDesc">
                        <caption id="adminDesc" class="sr-only">List of admin expenses</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="adminTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </section>

            <!-- Other Expenses Table -->
            <section aria-labelledby="otherHeading" class="mb-8">
                <div class="p-4 border-b flex items-center justify-between">
                    <h3 id="otherHeading" class="text-xl font-bold">Other Expenses</h3>
                    <input
                      type="text"
                      id="searchOther"
                      class="w-48 p-1 border border-gray-300 rounded"
                      placeholder="Search Other..."
                      aria-label="Search other expenses"
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="otherTable" role="table" aria-describedby="otherDesc">
                        <caption id="otherDesc" class="sr-only">List of other expenses</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recurring</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="otherTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Add Expense Modal -->
        <div id="addExpenseForm" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalTitle" class="text-2xl font-bold">Add New Expense</h2>
                    <button id="closeModalButton" class="text-gray-500 hover:text-gray-700" aria-label="Close modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Equipment Form -->
                <form method="POST" id="equipmentForm" class="hidden" novalidate>
                    <input type="hidden" name="category" value="equipment" />
                    <h3 class="text-xl font-bold mb-2 border-b pb-2">Equipment Details</h3>
                    <div class="mb-4">
                        <label for="equipment_transaction_date" class="block text-gray-700 font-medium mb-2">Date:</label>
                        <input type="date" id="equipment_transaction_date" name="transaction_date" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="item_name" class="block text-gray-700 font-medium mb-2">Item Name:</label>
                        <input type="text" id="item_name" name="item_name" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-gray-700 font-medium mb-2">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="unit_price" class="block text-gray-700 font-medium mb-2">Unit Price:</label>
                        <input type="number" step="0.01" id="unit_price" name="unit_price" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="supplier" class="block text-gray-700 font-medium mb-2">Supplier:</label>
                        <input type="text" id="supplier" name="supplier" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="closeModal()">Cancel</button>
                        <button type="submit" name="add_transaction" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Expense</button>
                    </div>
                </form>

                <!-- Travel Form -->
                <form method="POST" id="travelForm" class="hidden" novalidate>
                    <input type="hidden" name="category" value="travel" />
                    <h3 class="text-xl font-bold mb-2 border-b pb-2">Travel Details</h3>
                    <div class="mb-4">
                        <label for="travel_amount" class="block text-gray-700 font-medium mb-2">Amount:</label>
                        <input type="number" step="0.01" id="travel_amount" name="amount" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="travel_transaction_date" class="block text-gray-700 font-medium mb-2">Date:</label>
                        <input type="date" id="travel_transaction_date" name="transaction_date" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="trip_purpose" class="block text-gray-700 font-medium mb-2">Purpose:</label>
                        <input type="text" id="trip_purpose" name="trip_purpose" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="destination" class="block text-gray-700 font-medium mb-2">Destination:</label>
                        <input type="text" id="destination" name="destination" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="start_date" class="block text-gray-700 font-medium mb-2">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="block text-gray-700 font-medium mb-2">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="participants" class="block text-gray-700 font-medium mb-2">Participants:</label>
                        <input type="number" id="participants" name="participants" min="1" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="closeModal()">Cancel</button>
                        <button type="submit" name="add_transaction" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Expense</button>
                    </div>
                </form>

                <!-- Admin Form -->
                <form method="POST" id="adminForm" class="hidden" novalidate>
                    <input type="hidden" name="category" value="admin" />
                    <h3 class="text-xl font-bold mb-2 border-b pb-2">Admin Details</h3>
                    <div class="mb-4">
                        <label for="admin_description" class="block text-gray-700 font-medium mb-2">Description:</label>
                        <input type="text" id="admin_description" name="description" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="admin_amount" class="block text-gray-700 font-medium mb-2">Amount:</label>
                        <input type="number" step="0.01" id="admin_amount" name="amount" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="admin_transaction_date" class="block text-gray-700 font-medium mb-2">Date:</label>
                        <input type="date" id="admin_transaction_date" name="transaction_date" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="expense_type" class="block text-gray-700 font-medium mb-2">Expense Type:</label>
                        <input type="text" id="expense_type" name="expense_type" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="vendor" class="block text-gray-700 font-medium mb-2">Vendor:</label>
                        <input type="text" id="vendor" name="vendor" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="service_period" class="block text-gray-700 font-medium mb-2">Service Period:</label>
                        <input type="text" id="service_period" name="service_period" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="closeModal()">Cancel</button>
                        <button type="submit" name="add_transaction" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Expense</button>
                    </div>
                </form>

                <!-- Other Form -->
                <form method="POST" id="otherForm" class="hidden" novalidate>
                    <input type="hidden" name="category" value="other" />
                    <h3 class="text-xl font-bold mb-2 border-b pb-2">Other Details</h3>
                    <div class="mb-4">
                        <label for="other_description" class="block text-gray-700 font-medium mb-2">Description:</label>
                        <input type="text" id="other_description" name="description" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="other_amount" class="block text-gray-700 font-medium mb-2">Amount:</label>
                        <input type="number" step="0.01" id="other_amount" name="amount" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="other_transaction_date" class="block text-gray-700 font-medium mb-2">Date:</label>
                        <input type="date" id="other_transaction_date" name="transaction_date" class="w-full p-2 border border-gray-300 rounded" required />
                    </div>
                    <div class="mb-4">
                        <label for="expense_type_other" class="block text-gray-700 font-medium mb-2">Expense Type:</label>
                        <input type="text" id="expense_type_other" name="expense_type_other" class="w-full p-2 border border-gray-300 rounded" />
                    </div>
                    <div class="mb-4">
                        <label for="details" class="block text-gray-700 font-medium mb-2">Details:</label>
                        <textarea id="details" name="details" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="is_recurring" name="is_recurring" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <span class="ml-2 text-gray-700">Is Recurring</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" onclick="closeModal()">Cancel</button>
                        <button type="submit" name="add_transaction" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize expenses data
        const expenses = <?php echo json_encode($expenses); ?>;

        // DOM elements
        const addExpenseSelect = document.getElementById('addExpenseSelect');
        const addExpenseForm = document.getElementById('addExpenseForm');
        const closeModalButton = document.getElementById('closeModalButton');

        // Search inputs per category
        const searchInputAll = document.getElementById('searchInput');
        const searchEquipment = document.getElementById('searchEquipment');
        const searchTravel = document.getElementById('searchTravel');
        const searchAdmin = document.getElementById('searchAdmin');
        const searchOther = document.getElementById('searchOther');

        // Event listeners
        closeModalButton.addEventListener('click', closeModal);

        // Close modal function
        function closeModal() {
            addExpenseForm.classList.add('hidden');
            addExpenseSelect.value = "";
            document.querySelectorAll('#addExpenseForm form').forEach(form => form.classList.add('hidden'));
            document.querySelectorAll('#addExpenseForm form input, #addExpenseForm form textarea').forEach(input => {
                if(input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
            const quantityInput = document.querySelector('#equipmentForm input[name="quantity"]');
            if(quantityInput) quantityInput.value = 1;
        }

        // Show form based on selected category
        addExpenseSelect.addEventListener('change', function () {
            const selectedCategory = this.value;
            document.querySelectorAll('#addExpenseForm form').forEach(form => {
                form.classList.add('hidden');
            });
            if (selectedCategory) {
                document.getElementById(`${selectedCategory}Form`).classList.remove('hidden');
                addExpenseForm.classList.remove('hidden');
                const dateInput = document.querySelector(`#${selectedCategory}Form input[name="transaction_date"]`);
                if (dateInput) {
                    const today = new Date().toISOString().split('T')[0];
                    dateInput.value = today;
                }
            }
        });

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[m];
            });
        }

        // Format number with 2 decimal places
        function numberFormat(number) {
            return parseFloat(number || 0).toFixed(2);
        }

        // Format date to YYYY-MM-DD
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            if (isNaN(date)) return '';
            return date.toISOString().split('T')[0];
        }

        // Render tables with data
        function renderTables(expenses) {
            renderEquipmentTable(expenses.filter(e => e.category === 'equipment'));
            renderTravelTable(expenses.filter(e => e.category === 'travel'));
            renderAdminTable(expenses.filter(e => e.category === 'admin'));
            renderOtherTable(expenses.filter(e => e.category === 'other'));
        }

        function renderEquipmentTable(equipmentExpenses) {
            const tbody = document.getElementById('equipmentTableBody');
            tbody.innerHTML = '';
            let totalAmount = 0;
            if (equipmentExpenses.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No equipment expenses found</td></tr>`;
                return;
            }
            equipmentExpenses.forEach(expense => {
                const unitPrice = parseFloat(expense.unit_price || 0);
                const quantity = parseInt(expense.quantity || 1);
                const total = unitPrice * quantity;
                totalAmount += total;
                tbody.innerHTML += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${expense.transaction_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.equipment_item || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.supplier || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${quantity}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${unitPrice.toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.transaction_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">${total.toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML += `
                <tr class="bg-gray-100 font-bold">
                    <td colspan="6" class="px-6 py-4 text-right">Total:</td>
                    <td class="px-6 py-4 font-medium">${totalAmount.toFixed(2)}</td>
                </tr>
            `;
        }

        function renderTravelTable(travelExpenses) {
            const tbody = document.getElementById('travelTableBody');
            tbody.innerHTML = '';
            let totalAmount = 0;
            if (travelExpenses.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No travel expenses found</td></tr>`;
                return;
            }
            travelExpenses.forEach(expense => {
                totalAmount += parseFloat(expense.amount || 0);
                tbody.innerHTML += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${expense.transaction_id}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.participants || 1)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.destination || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.travel_purpose || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.start_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.end_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.transaction_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">${numberFormat(expense.amount)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML += `
                <tr class="bg-gray-100 font-bold">
                    <td colspan="7" class="px-6 py-4 text-right">Total:</td>
                    <td class="px-6 py-4 font-medium">${totalAmount.toFixed(2)}</td>
                </tr>
            `;
        }

        function renderAdminTable(adminExpenses) {
            const tbody = document.getElementById('adminTableBody');
            tbody.innerHTML = '';
            let totalAmount = 0;
            if (adminExpenses.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No admin expenses found</td></tr>`;
                return;
            }
            adminExpenses.forEach(expense => {
                totalAmount += parseFloat(expense.amount || 0);
                tbody.innerHTML += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${expense.transaction_id}</td>
                        <td class="px-6 py-4">${escapeHtml(expense.description)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.admin_type || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.vendor || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.service_period || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.transaction_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">${numberFormat(expense.amount)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML += `
                <tr class="bg-gray-100 font-bold">
                    <td colspan="6" class="px-6 py-4 text-right">Total:</td>
                    <td class="px-6 py-4 font-medium">${totalAmount.toFixed(2)}</td>
                </tr>
            `;
        }

        function renderOtherTable(otherExpenses) {
            const tbody = document.getElementById('otherTableBody');
            tbody.innerHTML = '';
            let totalAmount = 0;
            if (otherExpenses.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No other expenses found</td></tr>`;
                return;
            }
            otherExpenses.forEach(expense => {
                totalAmount += parseFloat(expense.amount || 0);
                tbody.innerHTML += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${expense.transaction_id}</td>
                        <td class="px-6 py-4">${escapeHtml(expense.description)}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${escapeHtml(expense.expense_type_other || '')}</td>
                        <td class="px-6 py-4">${escapeHtml(expense.details || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${expense.is_recurring ? 'Yes' : 'No'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatDate(expense.transaction_date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">${numberFormat(expense.amount)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML += `
                <tr class="bg-gray-100 font-bold">
                    <td colspan="6" class="px-6 py-4 text-right">Total:</td>
                    <td class="px-6 py-4 font-medium">${totalAmount.toFixed(2)}</td>
                </tr>
            `;
        }

        // Search filter functions per category
        function filterExpensesByCategory(searchTerm, category) {
            searchTerm = searchTerm.toLowerCase();
            return expenses.filter(expense => {
                if (expense.category !== category) return false;
                switch(category) {
                    case 'equipment':
                        return (
                            (expense.equipment_item && expense.equipment_item.toLowerCase().includes(searchTerm)) ||
                            (expense.supplier && expense.supplier.toLowerCase().includes(searchTerm)) ||
                            (expense.transaction_date && expense.transaction_date.includes(searchTerm)) ||
                            (expense.transaction_id && expense.transaction_id.toString().includes(searchTerm))
                        );
                    case 'travel':
                        return (
                            (expense.destination && expense.destination.toLowerCase().includes(searchTerm)) ||
                            (expense.travel_purpose && expense.travel_purpose.toLowerCase().includes(searchTerm)) ||
                            (expense.transaction_date && expense.transaction_date.includes(searchTerm)) ||
                            (expense.transaction_id && expense.transaction_id.toString().includes(searchTerm)) ||
                            (expense.participants && expense.participants.toString().includes(searchTerm))
                        );
                    case 'admin':
                        return (
                            (expense.description && expense.description.toLowerCase().includes(searchTerm)) ||
                            (expense.admin_type && expense.admin_type.toLowerCase().includes(searchTerm)) ||
                            (expense.vendor && expense.vendor.toLowerCase().includes(searchTerm)) ||
                            (expense.service_period && expense.service_period.toLowerCase().includes(searchTerm)) ||
                            (expense.transaction_date && expense.transaction_date.includes(searchTerm)) ||
                            (expense.transaction_id && expense.transaction_id.toString().includes(searchTerm))
                        );
                    case 'other':
                        return (
                            (expense.description && expense.description.toLowerCase().includes(searchTerm)) ||
                            (expense.expense_type_other && expense.expense_type_other.toLowerCase().includes(searchTerm)) ||
                            (expense.details && expense.details.toLowerCase().includes(searchTerm)) ||
                            (expense.transaction_date && expense.transaction_date.includes(searchTerm)) ||
                            (expense.transaction_id && expense.transaction_id.toString().includes(searchTerm))
                        );
                    default:
                        return false;
                }
            });
        }

        // Search all categories combined
        function filterExpensesAll(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            return expenses.filter(expense => {
                return (
                    (expense.description && expense.description.toLowerCase().includes(searchTerm)) ||
                    (expense.amount !== undefined && expense.amount.toString().includes(searchTerm)) ||
                    (expense.transaction_date && expense.transaction_date.includes(searchTerm)) ||
                    (expense.category && expense.category.toLowerCase().includes(searchTerm)) ||
                    (expense.equipment_item && expense.equipment_item.toLowerCase().includes(searchTerm)) ||
                    (expense.supplier && expense.supplier.toLowerCase().includes(searchTerm)) ||
                    (expense.travel_purpose && expense.travel_purpose.toLowerCase().includes(searchTerm)) ||
                    (expense.destination && expense.destination.toLowerCase().includes(searchTerm)) ||
                    (expense.admin_type && expense.admin_type.toLowerCase().includes(searchTerm)) ||
                    (expense.vendor && expense.vendor.toLowerCase().includes(searchTerm)) ||
                    (expense.service_period && expense.service_period.toLowerCase().includes(searchTerm)) ||
                    (expense.expense_type_other && expense.expense_type_other.toLowerCase().includes(searchTerm)) ||
                    (expense.details && expense.details.toLowerCase().includes(searchTerm)) ||
                    (expense.transaction_id && expense.transaction_id.toString().includes(searchTerm))
                );
            });
        }

        // Event listeners for search inputs
        searchInputAll.addEventListener('input', () => {
            const term = searchInputAll.value.trim();
            if(term === '') {
                renderTables(expenses);
                // Clear individual category searches
                searchEquipment.value = '';
                searchTravel.value = '';
                searchAdmin.value = '';
                searchOther.value = '';
            } else {
                const filtered = filterExpensesAll(term);
                renderTables(filtered);
                // Clear individual category searches
                searchEquipment.value = '';
                searchTravel.value = '';
                searchAdmin.value = '';
                searchOther.value = '';
            }
        });

        searchEquipment.addEventListener('input', () => {
            const term = searchEquipment.value.trim();
            if(term === '') {
                renderEquipmentTable(expenses.filter(e => e.category === 'equipment'));
            } else {
                renderEquipmentTable(filterExpensesByCategory(term, 'equipment'));
            }
        });

        searchTravel.addEventListener('input', () => {
            const term = searchTravel.value.trim();
            if(term === '') {
                renderTravelTable(expenses.filter(e => e.category === 'travel'));
            } else {
                renderTravelTable(filterExpensesByCategory(term, 'travel'));
            }
        });

        searchAdmin.addEventListener('input', () => {
            const term = searchAdmin.value.trim();
            if(term === '') {
                renderAdminTable(expenses.filter(e => e.category === 'admin'));
            } else {
                renderAdminTable(filterExpensesByCategory(term, 'admin'));
            }
        });

        searchOther.addEventListener('input', () => {
            const term = searchOther.value.trim();
            if(term === '') {
                renderOtherTable(expenses.filter(e => e.category === 'other'));
            } else {
                renderOtherTable(filterExpensesByCategory(term, 'other'));
            }
        });

        // Print functionality
        document.getElementById('printButton').addEventListener('click', function() {
            window.print();
        });

        // Initial render
        renderTables(expenses);
    </script>
</body>
</html>