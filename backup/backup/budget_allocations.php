<?php
$mysqli = new mysqli('localhost', 'root', '', 'youth_sk'); // Database connection

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Define the execute function
function execute($sql, $params = []) {
    global $mysqli; // Use $mysqli instead of $pdo
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        // Dynamically bind parameters
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt->execute();
}

// Add new budget allocation
function addBudgetAllocation($category, $fiscal_year, $allocated_amount, $notes = null) {
    $sql = "INSERT INTO budget_allocations (category, fiscal_year, allocated_amount, notes) 
            VALUES (?, ?, ?, ?)";
    return execute($sql, [$category, $fiscal_year, $allocated_amount, $notes]);
}

// Define the fetchAll function
function fetchAll($sql, $params = []) {
    global $mysqli; // Use $mysqli instead of $pdo
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        // Dynamically bind parameters
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all budget allocations
function getAllBudgetAllocations() {
    $sql = "SELECT * FROM budget_allocations ORDER BY fiscal_year DESC, category";
    return fetchAll($sql);
}

// Get budget totals by category
function getBudgetTotals() {
    $sql = "SELECT category, SUM(allocated_amount) as total FROM budget_allocations GROUP BY category";
    return fetchAll($sql);
}

// Get budget totals by fiscal year
function getBudgetTotalsByYear() {
    $sql = "SELECT fiscal_year, SUM(allocated_amount) as total FROM budget_allocations GROUP BY fiscal_year";
    return fetchAll($sql);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    $category = $_POST['category'];
    $fiscal_year = $_POST['fiscal_year'];
    $allocated_amount = $_POST['allocated_amount'];
    $notes = $_POST['notes'] ?? null;
    
    if (addBudgetAllocation($category, $fiscal_year, $allocated_amount, $notes)) {
        $message = "Budget allocation added successfully!";
    } else {
        $error = "Error adding budget allocation.";
    }
}

$allBudgets = getAllBudgetAllocations();
$budgetTotals = getBudgetTotals();
$budgetByYear = getBudgetTotalsByYear();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <title>Budget Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <?php if (isset($message)): ?>
            <p class="text-green-500"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <p class="text-red-500"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <header class="bg-white shadow p-4">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Expense Management System</h1>
        </header>
        <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
            <div class="flex justify-between mb-4">
                <div class="flex items-center">
                    <select id="filterSelect" class="border border-gray-300 rounded px-4 py-2 mr-2">
                        <option value="date">Filter by Date</option>
                        <option value="alphabet">Filter by Alphabet</option>
                        <option value="amount">Filter by Amount</option>
                        <option value="source">Filter by Source</option>
                    </select>
                    <button id="applyFilterButton" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Apply Filter</button>
                </div>
                <div>
                    <input type="text" id="searchInput" class="border border-gray-300 rounded px-4 py-2 mr-2" placeholder="Search...">
                    <button id="addBudgetButton" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">
                        <i class="fas fa-plus"></i> Add New Budget
                    </button>
                </div>
            </div>
            <table class="min-w-full bg-white" id="budgetTable">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Category</th>
                        <th class="py-2 px-4 border-b">Fiscal Year</th>
                        <th class="py-2 px-4 border-b">Allocated Amount</th>
                        <th class="py-2 px-4 border-b">Notes</th>
                    </tr>
                </thead>
                <tbody id="budgetTableBody">
                    <?php foreach ($allBudgets as $budget): ?>
                    <tr>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($budget['budget_id']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($budget['category']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($budget['fiscal_year']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo number_format($budget['allocated_amount'], 2); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($budget['notes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div id="addBudgetForm" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Add New Budget Allocation</h2>
                <form method="POST">
                    <div class="mb-4">
                        <label for="category" class="block text-gray-700">Category:</label>
                        <select name="category" id="category" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="equipment">Equipment</option>
                            <option value="travel">Travel</option>
                            <option value="staff">Staff</option>
                            <option value="facilities">Facilities</option>
                            <option value="admin">Admin</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="fiscal_year" class="block text-gray-700">Fiscal Year:</label>
                        <input type="text" name="fiscal_year" id="fiscal_year" class="w-full p-2 border border-gray-300 rounded" required placeholder="YYYY-YYYY">
                    </div>
                    
                    <div class="mb-4">
                        <label for="allocated_amount" class="block text-gray-700">Allocated Amount:</label>
                        <input type="number" step="0.01" name="allocated_amount" id="allocated_amount" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700">Notes:</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="cancelButton" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                        <button type="submit" name="add_budget" class="bg-blue-500 text-white px-4 py-2 rounded">Add Budget Allocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('addBudgetButton').addEventListener('click', function() {
            document.getElementById('addBudgetForm').classList.remove('hidden');
        });
        
        document.getElementById('cancelButton').addEventListener('click', function() {
            document.getElementById('addBudgetForm').classList.add('hidden');
        });

        const budgets = <?php echo json_encode($allBudgets); ?>;
        
        document.getElementById('applyFilterButton').addEventListener('click', function() {
            const filterType = document.getElementById('filterSelect').value;
            let sortedBudgets;
            switch (filterType) {
                case 'date':
                    sortedBudgets = budgets.sort((a, b) => new Date(b.fiscal_year) - new Date(a.fiscal_year));
                    break;
                case 'alphabet':
                    sortedBudgets = budgets.sort((a, b) => a.category.localeCompare(b.category));
                    break;
                case 'amount':
                    sortedBudgets = budgets.sort((a, b) => b.allocated_amount - a.allocated_amount);
                    break;
                case 'source':
                    sortedBudgets = budgets.sort((a, b) => a.category.localeCompare(b.category));
                    break;
                default:
                    sortedBudgets = budgets;
            }
            renderTable(sortedBudgets);
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filteredBudgets = budgets.filter(budget => 
                budget.category.toLowerCase().includes(searchTerm) ||
                budget.allocated_amount.toString().includes(searchTerm) ||
                budget.fiscal_year.includes(searchTerm)
            );
            renderTable(filteredBudgets);
        });

        function renderTable(budgets) {
            const tableBody = document.getElementById('budgetTableBody');
            tableBody.innerHTML = '';
            if (budgets.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="py-2 px-4 border-b text-center">No budget allocations found</td></tr>';
            } else {
                budgets.forEach(budget => {
                    const row = `
                        <tr>
                            <td class="py-2 px-4 border-b">${budget.budget_id}</td>
                            <td class="py-2 px-4 border-b">${budget.category}</td>
                            <td class="py-2 px-4 border-b">${budget.fiscal_year}</td>
                            <td class="py-2 px-4 border-b">${number_format(budget.allocated_amount, 2)}</td>
                            <td class="py-2 px-4 border-b">${budget.notes}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
        }

        function number_format(number, decimals) {
            return parseFloat(number).toFixed(decimals);
        }

        document.getElementById('printButton').addEventListener('click', function() {
            const printContents = document.getElementById('budgetTable').outerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    </script>
</body>
</html>