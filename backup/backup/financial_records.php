<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <title>Expense Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body class="bg-white p-6 min-h-screen flex flex-col">
    <?php include '../config.php'; ?>
    <header class="bg-white shadow p-4" style="background-color: #f8f9fa; padding: 20px;">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Expense Management System</h1>
        <nav>
            <ul class="flex flex-wrap gap-4 text-blue-600 font-semibold" style="list-style: none;">
                <?php
                    $links = [
                        'financial_transactions_crud' => 'Financial Transaction',
                        'other_expenses_crud' => 'Other Expenses',
                        'facility_costs_crud' => 'Facility Costs',
                        'equipment_crud' => 'Equipment',
                        'program_expenses_crud' => 'Program Expenses'
                    ];
                    $current_subpage = isset($_GET['subpage']) ? $_GET['subpage'] : 'financial_transactions_crud';
                    foreach ($links as $key => $label) {
                        $activeClass = ($key === $current_subpage) ? 'active' : '';
                        $activeStyle = ($key === $current_subpage) 
                            ? 'color: #0056b3; font-weight: 700; text-decoration: underline;' 
                            : 'color: #007bff;';
                        echo '<li style="display: inline-block;"><a href="?page=financial_records&subpage=' . $key . '" class="' . $activeClass . '" style="' . $activeStyle . '">' . $label . '</a></li>';
                    }
                ?>
            </ul>
        </nav>
    </header>
    <main class="flex-grow container mx-auto p-4 bg-white shadow rounded">
        <?php
            $allowed_subpages = [
                'financial_transactions_crud',
                'staff_payments_crud',
                'other_expenses_crud',
                'facility_costs_crud',
                'equipment_crud',
                'admin_expenses_crud'
            ];
            
            // Set default subpage if not specified
            $subpage = isset($_GET['subpage']) ? $_GET['subpage'] : 'financial_transactions_crud';
            
            if (in_array($subpage, $allowed_subpages)) {
                $filepath = __DIR__ . '/' . $subpage . '.php';
                if (file_exists($filepath)) {
                    include $filepath;
                } else {
                    echo '<h2 class="text-xl font-semibold text-red-600 mb-4">Page not found.</h2>';
                }
            }
        ?>
    </main>
</body>
</html>