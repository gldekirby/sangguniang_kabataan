<?php
$mysqli = new mysqli('localhost', 'root', '', 'youth_sk'); // Database connection

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Add new fund
function addFund($source, $amount, $received_date, $purpose, $notes = null) {
    $sql = "INSERT INTO funds (source, amount, received_date, purpose, notes) 
            VALUES (?, ?, ?, ?, ?)";
    return execute($sql, [$source, $amount, $received_date, $purpose, $notes]);
}

// Get all funds
function getAllFunds() {
    $sql = "SELECT * FROM funds ORDER BY received_date DESC";
    return fetchAll($sql);
}

// Get total funds received
function getTotalFunds() {
    $sql = "SELECT SUM(amount) as total FROM funds";
    $result = fetchOne($sql); // Fetch a single row
    
    return $result['total'] ?? 0; // Return the total or 0 if null
}

// Get funds by source
function getFundsBySource() {
    $sql = "SELECT source, SUM(amount) as total FROM funds GROUP BY source";
    return fetchAll($sql);
}

// Fetch all function
function fetchAll($sql, $params = []) {
    global $mysqli; // Ensure $mysqli is accessible
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

// Fetch one function
function fetchOne($sql, $params = []) {
    global $mysqli; // Ensure $mysqli is accessible
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        // Dynamically bind parameters
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // Fetch a single row as an associative array
}

// Execute function
function execute($sql, $params = []) {
    global $mysqli; // Ensure $mysqli is accessible
    $stmt = $mysqli->prepare($sql);
    
    if ($params) {
        // Dynamically bind parameters
        $types = str_repeat('s', count($params)); // Assuming all parameters are strings
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt->execute(); // Execute the statement and return the result
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fund'])) {
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $received_date = $_POST['received_date'];
    $purpose = $_POST['purpose'];
    $notes = $_POST['notes'] ?? null;
    
    if (addFund($source, $amount, $received_date, $purpose, $notes)) {
        $message = "Fund added successfully!";
    } else {
        $error = "Error adding fund.";
    }
}

$allFunds = getAllFunds();
$totalFunds = getTotalFunds();
$fundsBySource = getFundsBySource();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <title>Funds Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
</head>
<body class="bg-gray-50">
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
        <div class="overflow-x-auto bg-white shadow rounded-lg p-4">
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
                    <button id="addFundButton" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">
                        <i class="fas fa-plus"></i> Add New Fund
                    </button>
                    <a href="reports/funds_report.php" class="bg-green-500 text-white px-4 py-2 rounded">
                        <i class="fas fa-print"></i> Print PDF
                    </a>
                </div>
            </div>
            <table class="min-w-full bg-white" id="fundsTable">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Source</th>
                        <th class="py-2 px-4 border-b">Purpose</th>
                        <th class="py-2 px-4 border-b">Notes</th>
                        <th class="py-2 px-4 border-b">Date</th>
                        <th class="py-2 px-4 border-b">Amount</th>
                    </tr>
                </thead>
                <tbody id="fundsTableBody">
                    <?php foreach ($allFunds as $fund): ?>
                    <tr>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($fund['fund_id']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($fund['source']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($fund['purpose']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($fund['notes']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo htmlspecialchars($fund['received_date']); ?></td>
                        <td class="py-2 px-4 border-b text-center"><?php echo number_format($fund['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div id="addFundForm" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
                <h2 class="text-2xl font-bold mb-4">Add New Fund</h2>
                <form method="POST">
                    <div class="mb-4">
                        <label for="source" class="block text-gray-700">Source:</label>
                        <input type="text" name="source" id="source" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="amount" class="block text-gray-700">Amount:</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="received_date" class="block text-gray-700">Received Date:</label>
                        <input type="date" name="received_date" id="received_date" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="purpose" class="block text-gray-700">Purpose:</label>
                        <input type="text" name="purpose" id="purpose" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700">Notes:</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full p-2 border border-gray-300 rounded"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="cancelButton" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                        <button type="submit" name="add_fund" class="bg-blue-500 text-white px-4 py-2 rounded">Add Fund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('addFundButton').addEventListener('click', function() {
            document.getElementById('addFundForm').classList.remove('hidden');
        });
        
        document.getElementById('cancelButton').addEventListener('click', function() {
            document.getElementById('addFundForm').classList.add('hidden');
        });

        const funds = <?php echo json_encode($allFunds); ?>;
        
        document.getElementById('applyFilterButton').addEventListener('click', function() {
            const filterType = document.getElementById('filterSelect').value;
            let sortedFunds;
            switch (filterType) {
                case 'date':
                    sortedFunds = funds.sort((a, b) => new Date(b.received_date) - new Date(a.received_date));
                    break;
                case 'alphabet':
                    sortedFunds = funds.sort((a, b) => a.source.localeCompare(b.source));
                    break;
                case 'amount':
                    sortedFunds = funds.sort((a, b) => b.amount - a.amount);
                    break;
                case 'source':
                    sortedFunds = funds.sort((a, b) => a.source.localeCompare(b.source));
                    break;
                default:
                    sortedFunds = funds;
            }
            renderTable(sortedFunds);
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filteredFunds = funds.filter(fund => 
                fund.source.toLowerCase().includes(searchTerm) ||
                fund.amount.toString().includes(searchTerm) ||
                fund.received_date.includes(searchTerm)
            );
            renderTable(filteredFunds);
        });

        function renderTable(funds) {
            const tableBody = document.getElementById('fundsTableBody');
            tableBody.innerHTML = '';
            if (funds.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="py-2 px-4 border-b text-center">No funds found</td></tr>';
            } else {
                funds.forEach(fund => {
                    const row = `
                        <tr>
                            <td class="py-2 px-4 border-b">${fund.fund_id}</td>
                            <td class="py-2 px-4 border-b">${fund.source}</td>
                            <td class="py-2 px-4 border-b">${number_format(fund.amount, 2)}</td>
                            <td class="py-2 px-4 border-b">${fund.received_date}</td>
                            <td class="py-2 px-4 border-b">${fund.purpose}</td>
                            <td class="py-2 px-4 border-b">${fund.notes}</td>
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
            const printContents = document.getElementById('fundsTable').outerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    </script>
</body>
</html>