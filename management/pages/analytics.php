<html class="scroll-smooth" lang="en" style="font-family: 'Inter', sans-serif;">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Analytics Dashboard
  </title>
  <link href="bgi/tupi_logo.png" rel="icon" type="image/x-icon"/>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js">
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   html {
      font-size: 12px;
    }
    @media (min-width: 640px) {
      html {
        font-size: 14px;
      }
    }
    @media (min-width: 1024px) {
      html {
        font-size: 16px;
      }
    }
    /* Scrollbar for overflow containers */
    .overflow-y-auto::-webkit-scrollbar {
      width: 8px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
      background-color: rgba(107, 114, 128, 0.5);
      border-radius: 4px;
    }
  </style>
 </head>
 <body class="bg-gray-50 flex flex-col min-h-screen">
  <?php
  include '../config.php';

    // --- Finance Analytics Data ---
    $annualBudgetYears = [];
    $annualBudgetTotals = [];
    $annualBudgetQuery = "SELECT YEAR(fiscal_year) as year, SUM(allocated_amount) as total_budget FROM annual_budget GROUP BY year ORDER BY year";
    $annualBudgetResult = mysqli_query($conn, $annualBudgetQuery);
    while ($row = mysqli_fetch_assoc($annualBudgetResult)) {
        $annualBudgetYears[] = $row['year'];
        $annualBudgetTotals[] = (float)$row['total_budget'];
    }
    $latestYear = end($annualBudgetYears);

    $categoryNames = [];
    $categoryAmounts = [];
    $categoryQuery = "SELECT bc.category_name, SUM(ab.allocated_amount) as total_amount FROM annual_budget ab JOIN budget_categories bc ON ab.category_id = bc.id WHERE YEAR(ab.fiscal_year) = '$latestYear' GROUP BY ab.category_id, bc.category_name ORDER BY bc.category_name";
    $categoryResult = mysqli_query($conn, $categoryQuery);
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoryNames[] = $row['category_name'];
        $categoryAmounts[] = (float)$row['total_amount'];
    }

    $projectCounts = [];
    $projectCosts = [];
    $costLabels = ['Personal Services', 'MOOE', 'Capital Outlay'];
    $costValues = [0, 0, 0];
    $projectQuery = "SELECT YEAR(created_at) as year, COUNT(*) as project_count, 
                    SUM(COALESCE(personal_services,0)) as ps, 
                    SUM(COALESCE(mooe,0)) as mooe, 
                    SUM(COALESCE(capital_outlay,0)) as co, 
                    SUM(COALESCE(total_cost,0)) as total 
                    FROM projects 
                    GROUP BY year 
                    ORDER BY year";
    $projectResult = mysqli_query($conn, $projectQuery);
    while ($row = mysqli_fetch_assoc($projectResult)) {
        $projectCounts[] = (int)$row['project_count'];
        $projectCosts[] = (float)$row['total'];
        if ($row['year'] == $latestYear) {
            $costValues[0] = (float)$row['ps'];
            $costValues[1] = (float)$row['mooe'];
            $costValues[2] = (float)$row['co'];
        }
    }

    $membersQuery = "SELECT * FROM members WHERE status1 = 'approved'";
    $membersResult = mysqli_query($conn, $membersQuery);
    $members = mysqli_fetch_all($membersResult, MYSQLI_ASSOC);

    $totalMembers = count($members);

    $approvedDayCounts = [];
    $approvedMonthCounts = [];
    $approvedYearCounts = [];

    foreach ($members as $member) {
        $approvedDate = $member['created_at'];
        if ($approvedDate && $approvedDate !== '0000-00-00 00:00:00') {
            $dateObj = new DateTime($approvedDate);
            $day = $dateObj->format('Y-m-d');
            $month = $dateObj->format('Y-m');
            $year = $dateObj->format('Y');

            if (!isset($approvedDayCounts[$day])) {
                $approvedDayCounts[$day] = 0;
            }
            $approvedDayCounts[$day]++;

            if (!isset($approvedMonthCounts[$month])) {
                $approvedMonthCounts[$month] = 0;
            }
            $approvedMonthCounts[$month]++;

            if (!isset($approvedYearCounts[$year])) {
                $approvedYearCounts[$year] = 0;
            }
            $approvedYearCounts[$year]++;
        }
    }

    ksort($approvedDayCounts);
    ksort($approvedMonthCounts);
    ksort($approvedYearCounts);

    // District mapping for puroks
    $districts = [
        'District 1' => ['Purok 1', 'Purok 2', 'Purok 2A'],
        'District 2' => ['Purok 3', 'Purok 4', 'Purok 6'],
        'District 3' => ['Purok 3A', 'Purok 14', 'Purok 12'],
        'District 4' => ['Purok 11A', 'Purok 11C', 'Purok 11D'],
        'District 5' => ['Purok 5', 'Purok 7', 'Purok 13', 'Purok 9'],
        'District 6' => ['Purok 11', 'Purok 11B', 'Purok 10A'],
        'District 7' => ['Purok 8', 'Purok 8A', 'Purok 9A'],
        'District 8' => ['Purok 10', 'Purok 10B', 'Candelaria'],
        'District 9' => ['Relocation'],
    ];

    // Initialize district counts
    $districtCounts = array_fill_keys(array_keys($districts), 0);

    // Query all members' street/purok
    $address_query = "SELECT street FROM members WHERE street IS NOT NULL AND street != ''";
    $address_result = $conn->query($address_query);
    while($row = $address_result->fetch_assoc()) {
        $street = trim(strtolower($row['street']));
        foreach ($districts as $district => $puroks) {
            foreach ($puroks as $purok) {
                if (stripos($street, strtolower($purok)) !== false) {
                    $districtCounts[$district]++;
                    break 2;
                }
            }
        }
    }
    $addresses = array_keys($districtCounts);
    $addressCounts = array_values($districtCounts);

    $summary_sql = "SELECT bc.category_name, SUM(ab.allocated_amount) as total_allocated FROM budget_categories bc LEFT JOIN annual_budget ab ON bc.id = ab.category_id GROUP BY bc.id ORDER BY bc.category_name";
    $summary_result = $conn->query($summary_sql);

    $fund_sources_total_sql = "SELECT SUM(amount) as total_funds FROM fund_sources";
    $fund_sources_total_result = $conn->query($fund_sources_total_sql);
    $fund_sources_total = 0;
    if ($fund_sources_total_result) {
        $row = $fund_sources_total_result->fetch_assoc();
        $fund_sources_total = (float)$row['total_funds'];
    }

    // Calculate total cost of ABYIP projects with status 'done'
    $total_abyip_done_cost = 0;
    $abyip_done_result = $conn->query("SELECT SUM(total_cost) as total FROM projects WHERE status = 'done'");
    if ($abyip_done_result && $abyip_done_result->num_rows) {
        $row = $abyip_done_result->fetch_assoc();
        $total_abyip_done_cost = (float)($row['total'] ?? 0);
    }
    $total_balanced_funds = $fund_sources_total - $total_abyip_done_cost;

    $recentFundsResult = $conn->query("SELECT * FROM fund_sources ORDER BY created_at DESC LIMIT 5");
    $recentFunds = [];
    while ($row = $recentFundsResult->fetch_assoc()) {
        $recentFunds[] = [
            'type' => 'Fund Source',
            'name' => $row['name'],
            'amount' => $row['amount'],
            'created_at' => $row['created_at'],
            'icon' => 'fa-wallet',
            'color' => 'indigo',
        ];
    }
    $recentAnnualResult = $conn->query("SELECT * FROM annual_budget ORDER BY fiscal_year DESC LIMIT 5");
    while ($row = $recentAnnualResult->fetch_assoc()) {
        $recentFunds[] = [
            'type' => 'Annual Budget',
            'name' => 'Annual Budget ' . (isset($row['fiscal_year']) ? date('Y', strtotime($row['fiscal_year'])) : ''),
            'amount' => $row['allocated_amount'],
            'created_at' => isset($row['created_at']) ? $row['created_at'] : $row['fiscal_year'],
            'icon' => 'fa-file-invoice-dollar',
            'color' => 'yellow',
        ];
    }
    if ($conn->query("SHOW TABLES LIKE 'abyip'")->num_rows) {
        $recentAbyipResult = $conn->query("SELECT * FROM abyip ORDER BY created_at DESC LIMIT 5");
        while ($row = $recentAbyipResult->fetch_assoc()) {
            $recentFunds[] = [
                'type' => 'ABYIP',
                'name' => $row['name'],
                'amount' => $row['amount'],
                'created_at' => $row['created_at'],
                'icon' => 'fa-briefcase',
                'color' => 'blue',
            ];
        }
    }
    $allocationsByCategory = [];
    $allocationQuery = "SELECT ab.id as budget_id, bc.category_name, ab.allocated_amount, ab.program_name FROM annual_budget ab JOIN budget_categories bc ON ab.category_id = bc.id ORDER BY bc.category_name, ab.fiscal_year DESC";
    $allocationResult = $conn->query($allocationQuery);
    if ($allocationResult && $allocationResult->num_rows) {
        while ($row = $allocationResult->fetch_assoc()) {
            $catName = $row['category_name'];
            $progName = $row['program_name'];
            $budgetId = $row['budget_id'];
            $actual = 0;
            $projectTotalResult = $conn->query("SELECT SUM(total_cost) as total FROM projects WHERE budget_id = '$budgetId'");
            if ($projectTotalResult && $projectTotalResult->num_rows) {
                $projectRow = $projectTotalResult->fetch_assoc();
                $actual = $projectRow['total'] ? (float)$projectRow['total'] : 0;
            }
            $balance = (float)$row['allocated_amount'] - $actual;
            if (!isset($allocationsByCategory[$catName])) {
                $allocationsByCategory[$catName] = [];
            }
            $allocationsByCategory[$catName][] = [
                'program' => $progName,
                'allocated' => (float)$row['allocated_amount'],
                'actual' => $actual,
                'balance' => $balance,
                'budget_id' => $budgetId // Add budget_id for later use
            ];
        }
    }
    ksort($allocationsByCategory);
  ?>
  <?php
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    // Re-run the same queries as below, but only output the needed data
    include '../config.php';
    // --- Finance Analytics Data ---
    $annualBudgetYears = [];
    $annualBudgetTotals = [];
    $annualBudgetQuery = "SELECT YEAR(fiscal_year) as year, SUM(allocated_amount) as total_budget FROM annual_budget GROUP BY year ORDER BY year";
    $annualBudgetResult = mysqli_query($conn, $annualBudgetQuery);
    while ($row = mysqli_fetch_assoc($annualBudgetResult)) {
        $annualBudgetYears[] = $row['year'];
        $annualBudgetTotals[] = (float)$row['total_budget'];
    }
    $latestYear = end($annualBudgetYears);
    $categoryNames = [];
    $categoryAmounts = [];
    $categoryQuery = "SELECT bc.category_name, SUM(ab.allocated_amount) as total_amount FROM annual_budget ab JOIN budget_categories bc ON ab.category_id = bc.id WHERE YEAR(ab.fiscal_year) = '$latestYear' GROUP BY ab.category_id, bc.category_name ORDER BY bc.category_name";
    $categoryResult = mysqli_query($conn, $categoryQuery);
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoryNames[] = $row['category_name'];
        $categoryAmounts[] = (float)$row['total_amount'];
    }
    $projectCounts = [];
    $projectCosts = [];
    $costLabels = ['Personal Services', 'MOOE', 'Capital Outlay'];
    $costValues = [0, 0, 0];
    $projectQuery = "SELECT YEAR(created_at) as year, COUNT(*) as project_count, SUM(COALESCE(personal_services,0)) as ps, SUM(COALESCE(mooe,0)) as mooe, SUM(COALESCE(capital_outlay,0)) as co, SUM(COALESCE(total_cost,0)) as total FROM projects GROUP BY year ORDER BY year";
    $projectResult = mysqli_query($conn, $projectQuery);
    while ($row = mysqli_fetch_assoc($projectResult)) {
        $projectCounts[] = (int)$row['project_count'];
        $projectCosts[] = (float)$row['total'];
        if ($row['year'] == $latestYear) {
            $costValues[0] = (float)$row['ps'];
            $costValues[1] = (float)$row['mooe'];
            $costValues[2] = (float)$row['co'];
        }
    }
    $membersQuery = "SELECT * FROM members WHERE status1 = 'approved'";
    $membersResult = mysqli_query($conn, $membersQuery);
    $members = mysqli_fetch_all($membersResult, MYSQLI_ASSOC);
    $totalMembers = count($members);
    $approvedDayCounts = [];
    $approvedMonthCounts = [];
    $approvedYearCounts = [];
    foreach ($members as $member) {
        $approvedDate = $member['created_at'];
        if ($approvedDate && $approvedDate !== '0000-00-00 00:00:00') {
            $dateObj = new DateTime($approvedDate);
            $day = $dateObj->format('Y-m-d');
            $month = $dateObj->format('Y-m');
            $year = $dateObj->format('Y');
            if (!isset($approvedDayCounts[$day])) {
                $approvedDayCounts[$day] = 0;
            }
            $approvedDayCounts[$day]++;
            if (!isset($approvedMonthCounts[$month])) {
                $approvedMonthCounts[$month] = 0;
            }
            $approvedMonthCounts[$month]++;
            if (!isset($approvedYearCounts[$year])) {
                $approvedYearCounts[$year] = 0;
            }
            $approvedYearCounts[$year]++;
        }
    }
    ksort($approvedDayCounts);
    ksort($approvedMonthCounts);
    ksort($approvedYearCounts);
    // District mapping for puroks
    $districts = [
        'District 1' => ['Purok 1', 'Purok 2', 'Purok 2A'],
        'District 2' => ['Purok 3', 'Purok 4', 'Purok 6'],
        'District 3' => ['Purok 3A', 'Purok 14', 'Purok 12'],
        'District 4' => ['Purok 11A', 'Purok 11C', 'Purok 11D'],
        'District 5' => ['Purok 5', 'Purok 7', 'Purok 13', 'Purok 9'],
        'District 6' => ['Purok 11', 'Purok 11B', 'Purok 10A'],
        'District 7' => ['Purok 8', 'Purok 8A', 'Purok 9A'],
        'District 8' => ['Purok 10', 'Purok 10B', 'Candelaria'],
        'District 9' => ['Relocation'],
    ];
    $districtCounts = array_fill_keys(array_keys($districts), 0);
    $address_query = "SELECT street FROM members WHERE street IS NOT NULL AND street != ''";
    $address_result = $conn->query($address_query);
    while($row = $address_result->fetch_assoc()) {
        $street = trim(strtolower($row['street']));
        foreach ($districts as $district => $puroks) {
            foreach ($puroks as $purok) {
                if (stripos($street, strtolower($purok)) !== false) {
                    $districtCounts[$district]++;
                    break 2;
                }
            }
        }
    }
    $addresses = array_keys($districtCounts);
    $addressCounts = array_values($districtCounts);
    $fund_sources_total_sql = "SELECT SUM(amount) as total_funds FROM fund_sources";
    $fund_sources_total_result = $conn->query($fund_sources_total_sql);
    $fund_sources_total = 0;
    if ($fund_sources_total_result) {
        $row = $fund_sources_total_result->fetch_assoc();
        $fund_sources_total = (float)$row['total_funds'];
    }
    $total_abyip_done_cost = 0;
    $abyip_done_result = $conn->query("SELECT SUM(total_cost) as total FROM projects WHERE status = 'done'");
    if ($abyip_done_result && $abyip_done_result->num_rows) {
        $row = $abyip_done_result->fetch_assoc();
        $total_abyip_done_cost = (float)($row['total'] ?? 0);
    }
    $total_balanced_funds = $fund_sources_total - $total_abyip_done_cost;
    echo json_encode([
        'projectCounts' => $projectCounts,
        'projectCosts' => $projectCosts,
        'annualBudgetTotals' => $annualBudgetTotals,
        'annualBudgetYears' => $annualBudgetYears,
        'categoryNames' => $categoryNames,
        'categoryAmounts' => $categoryAmounts,
        'costLabels' => $costLabels,
        'costValues' => $costValues,
        'latestYear' => $latestYear,
        'totalMembers' => $totalMembers,
        'approvedDayCounts' => $approvedDayCounts,
        'approvedMonthCounts' => $approvedMonthCounts,
        'approvedYearCounts' => $approvedYearCounts,
        'addresses' => $addresses,
        'addressCounts' => $addressCounts,
        'fund_sources_total' => $fund_sources_total,
        'total_balanced_funds' => $total_balanced_funds,
    ]);
    exit;
}
  ?>
  <header class="bg-white shadow sticky top-0 z-30">
   <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <div class="flex items-center space-x-3">
     <img alt="Company logo with green background and white text 'Logo'" class="hidden h-10 w-10 rounded" height="40" src="https://storage.googleapis.com/a1aa/image/c47e04aa-8d6b-4f49-a778-002e1fb1fd25.jpg" width="40"/>
     <h1 class="text-xl text-green-900 tracking-tight">
      Analytics Reports
     </h1>
    </div>
    <nav class="hidden md:flex space-x-6 text-gray-700 font-semibold">
     <a class="hover:text-green-700 transition hidden" href="#finance-heading">
      Finance
     </a>
     <a class="hover:text-green-700 transition hidden" href="#members-heading">
      Members
     </a>
    </nav>
    <button aria-label="Open menu" class="md:hidden text-green-700 hover:text-green-900 focus:outline-none focus:ring-2 focus:ring-green-600" id="mobile-menu-button">
     <i class="fas fa-bars fa-lg">
     </i>
    </button>
   </div>
   <nav aria-label="Mobile menu" class="hidden md:hidden bg-white border-t border-gray-200" id="mobile-menu">
    <a class="block px-4 py-3 text-green-700 font-semibold hover:bg-green-50" href="#finance-heading">
     Finance
    </a>
    <a class="block px-4 py-3 text-green-700 font-semibold hover:bg-green-50" href="#members-heading">
     Members
    </a>
   </nav>
  </header>
  <main class=" w-full mx-auto bg-gray-50">
   <div class="flex-grow space-y-10">
    <!-- Finance Section -->
    <section aria-labelledby="finance-heading" class="bg-white p-6">
     <h2 class="text-3xl font-extrabold text-green-900 mb-8 border-b border-green-200 pb-3" id="finance-heading">
      Finance Analytics
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
      <div class="bg-green-50 rounded-lg shadow p-5 flex flex-col justify-center items-center text-center">
       <div class="text-green-700 text-4xl font-extrabold mb-1 sm:mb-2 tracking-tight">
        <?php echo end($projectCounts); ?>
       </div>
       <div class="text-gray-700 font-semibold text-base sm:text-lg">
        Projects (
        <?php echo $latestYear; ?>
        )
       </div>
       <div class="mt-4 text-green-600">
        <i class="fas fa-project-diagram fa-4x sm:fa-5x">
        </i>
       </div>
      </div>
      <div class="bg-green-50 rounded-lg shadow p-5 flex flex-col justify-center items-center text-center">
       <div class="text-green-600 text-4xl font-extrabold mb-1 sm:mb-2 tracking-tight">
        ₱
        <?php echo number_format(end($projectCosts), 0); ?>
       </div>
       <div class="text-gray-700 font-semibold text-base sm:text-lg">
        Total Project Cost (
        <?php echo $latestYear; ?>
        )
       </div>
       <div class="mt-4 text-green-500">
        <i class="fas fa-coins fa-4x sm:fa-5x">
        </i>
       </div>
      </div>
      <div class="bg-yellow-50 rounded-lg shadow p-5 flex flex-col justify-center items-center text-center">
       <div class="text-yellow-700 text-4xl font-extrabold mb-1 sm:mb-2 tracking-tight">
        ₱
        <?php echo number_format(end($annualBudgetTotals), 0); ?>
       </div>
       <div class="text-gray-700 font-semibold text-base sm:text-lg">
        Annual Budget (
        <?php echo $latestYear; ?>
        )
       </div>
       <div class="mt-4 text-yellow-600">
        <i class="fas fa-file-invoice-dollar fa-4x sm:fa-5x">
        </i>
       </div>
      </div>
      <div class="bg-indigo-50 rounded-lg shadow p-5 flex flex-col justify-center items-center text-center">
       <div class="text-indigo-700 text-4xl font-extrabold mb-1 sm:mb-2 tracking-tight">
        ₱
        <?php echo number_format($fund_sources_total, 0); ?>
       </div>
       <div class="text-gray-700 font-semibold text-base sm:text-lg">
        Total Funds
       </div>
       <div class="mt-4 text-indigo-600">
        <i class="fas fa-wallet fa-4x sm:fa-5x">
        </i>
       </div>
      </div>
      <div class="bg-teal-50 rounded-lg shadow p-5 flex flex-col justify-center items-center text-center">
       <div class="text-teal-700 text-4xl font-extrabold mb-1 sm:mb-2 tracking-tight">
        ₱
        <?php echo number_format($total_balanced_funds, 0); ?>
       </div>
       <div class="text-gray-700 font-semibold text-base sm:text-lg">
        Total Balanced of Funds
       </div>
       <div class="mt-4 text-teal-600">
        <i class="fas fa-balance-scale fa-4x sm:fa-5x"></i>
       </div>
      </div>
     </div>
     <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-10">
      <?php while ($summary = $summary_result->fetch_assoc()): ?>
      <div class="bg-white rounded-lg shadow p-5 flex items-center space-x-4 border border-gray-100 hover:shadow-lg transition">
       <div class="flex-shrink-0 bg-blue-100 text-blue-600 rounded-full p-3">
        <i class="fas fa-layer-group fa-lg">
        </i>
       </div>
       <div>
        <h3 class="text-lg font-semibold text-gray-800">
         <?= htmlspecialchars($summary['category_name']) ?>
        </h3>
        <p class="text-green-600 font-bold text-xl">
         ₱
         <?= number_format($summary['total_allocated'] ?? 0, 0) ?>
        </p>
        <p class="text-gray-500 text-sm">
         Allocated Amount
        </p>
       </div>
      </div>
      <?php endwhile; ?>
     </div>
     <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
      <section aria-labelledby="recent-funds-heading" class="bg-white rounded-lg shadow p-6 overflow-x-auto md:col-span-2">
       <h3 class="text-xl font-semibold text-gray-800 mb-5 flex items-center space-x-3" id="recent-funds-heading">
        <i class="fas fa-clock text-blue-600">
        </i>
        <span>
         Recent Financial Activity
        </span>
       </h3>
       <?php if (!empty($recentFunds)): ?>
       <ul class="divide-y divide-gray-200 overflow-y-auto max-h-96 rounded border border-gray-200 bg-white shadow">
        <?php foreach ($recentFunds as $recent): ?>
        <li class="py-4 px-5 flex items-center space-x-4 hover:bg-gray-50 transition">
         <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-<?= htmlspecialchars($recent['color']) ?>-100 text-<?= htmlspecialchars($recent['color']) ?>-600 shadow">
          <i class="fas <?= htmlspecialchars($recent['icon']) ?> fa-lg">
          </i>
         </span>
         <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900 truncate" title="<?= htmlspecialchars($recent['name']) ?>">
           <?= htmlspecialchars($recent['name']) ?>
          </p>
          <p class="text-xs text-green-600 font-semibold mt-1">
           ₱
           <?= number_format($recent['amount'], 0) ?>
          </p>
          <p class="text-xs text-gray-400 font-mono mt-0.5 flex items-center space-x-2">
           <span>
            <?= date('Y-m-d', strtotime($recent['created_at'])) ?>
           </span>
           <span class="ml-2 px-2 py-0.5 rounded bg-gray-100 text-gray-600 border border-gray-200 text-xs font-medium">
            <?= $recent['type'] ?>
           </span>
          </p>
         </div>
        </li>
        <?php endforeach; ?>
       </ul>
       <?php else: ?>
       <p class="text-gray-500 text-sm text-center">
        No recent financial activity found.
       </p>
       <?php endif; ?>
      </section>
      <section class="bg-white rounded-lg shadow p-6 overflow-x-auto md:col-span-2">
       <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center space-x-3">
        <i class="fas fa-balance-scale text-teal-600">
        </i>
        <span>
         Budget Allocations by Category &amp; Program
        </span>
       </h3>
       <?php if (!empty($allocationsByCategory)): ?>
       <table class="min-w-full text-sm border border-gray-200 rounded-lg">
        <thead class="bg-gray-50">
         <tr>
          <th class="px-5 py-3 text-left font-semibold text-gray-600 border-b border-gray-200">
           Category
          </th>
          <th class="px-5 py-3 text-left font-semibold text-gray-600 border-b border-gray-200">
           Program Name
          </th>
          <th class="px-5 py-3 text-right font-semibold text-gray-600 border-b border-gray-200">
           Allocated Amount
          </th>
          <th class="px-5 py-3 text-right font-semibold text-gray-600 border-b border-gray-200">
           Projects (Done)
          </th>
          <th class="px-5 py-3 text-right font-semibold text-gray-600 border-b border-gray-200">
           Total Balanced
          </th>
         </tr>
        </thead>
        <tbody>
         <?php foreach ($allocationsByCategory as $cat => $programs): ?>
         <?php foreach ($programs as $prog): ?>
         <?php
            $budgetId = $prog['budget_id'] ?? null;
            $actual_done = 0;
            $actual_ongoing = 0;
            if ($budgetId) {
                // Done
                $result_done = $conn->query("SELECT SUM(total_cost) as total FROM projects WHERE budget_id = '" . $conn->real_escape_string($budgetId) . "' AND status = 'done'");
                if ($result_done && $result_done->num_rows) {
                    $row_done = $result_done->fetch_assoc();
                    $actual_done = $row_done['total'] ? (float)$row_done['total'] : 0;
                }
                // Ongoing
                $result_ongoing = $conn->query("SELECT SUM(total_cost) as total FROM projects WHERE budget_id = '" . $conn->real_escape_string($budgetId) . "' AND status = 'ongoing'");
                if ($result_ongoing && $result_ongoing->num_rows) {
                    $row_ongoing = $result_ongoing->fetch_assoc();
                    $actual_ongoing = $row_ongoing['total'] ? (float)$row_ongoing['total'] : 0;
                }
            }
            $actual_total = $actual_done + $actual_ongoing;
            $balance_total = $prog['allocated'] - $actual_total;
         ?>
         <tr class="border-b border-gray-200 hover:bg-green-50 transition">
          <td class="px-5 py-3 font-semibold text-gray-800">
           <?= htmlspecialchars($cat) ?>
          </td>
          <td class="px-5 py-3 text-gray-800">
           <?= htmlspecialchars($prog['program']) ?>
          </td>
          <td class="px-5 py-3 text-right text-blue-700 font-semibold">
           ₱
           <?= number_format($prog['allocated'], 0) ?>
          </td>
          <td class="px-5 py-3 text-right text-green-700 font-semibold">
           ₱
           <?= number_format($actual_total, 0) ?>
          </td>
          <td class="px-5 py-3 text-right font-semibold <?php echo ($balance_total < 0) ? 'text-red-700' : 'text-green-700'; ?>">
           ₱
           <?= number_format($balance_total, 0) ?>
          </td>
         </tr>
         <?php endforeach; ?>
         <?php endforeach; ?>
        </tbody>
       </table>
       <?php else: ?>
       <p class="text-gray-500 text-sm text-center">
        No budget allocations found.
       </p>
       <?php endif; ?>
      </section>
     </div>
     <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
      <div class="bg-white rounded-lg shadow p-6 flex flex-col">
       <h3 class="text-xl font-semibold text-green-800 mb-4 border-b border-green-200 pb-2">
        Annual Budget by Year
       </h3>
       <canvas class="w-full" height="220" id="annualBudgetChart">
       </canvas>
      </div>
      <div class="bg-white rounded-lg shadow p-6 flex flex-col">
       <h3 class="text-xl font-semibold text-green-800 mb-4 border-b border-green-200 pb-2">
        Budget Allocation by Category (
        <?php echo $latestYear; ?>
        )
       </h3>
       <canvas class="w-full" height="220" id="categoryBudgetChart">
       </canvas>
      </div>
      <div class="bg-white rounded-lg shadow p-6 flex flex-col">
       <h3 class="text-xl font-semibold text-green-800 mb-4 border-b border-green-200 pb-2">
        Project Cost Breakdown (
        <?php echo $latestYear; ?>
        )
       </h3>
       <canvas class="w-full" height="220" id="projectCostChart">
       </canvas>
      </div>
     </div>
    </section>
    <!-- Members Section -->
    <section aria-labelledby="members-heading" class="bg-white p-6">
     <h2 class="text-3xl font-extrabold text-green-900 mb-8 border-b border-green-200 pb-3" id="members-heading">
      Member Statistics
     </h2>
     <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
      <div class="bg-blue-50 rounded-lg shadow p-6 flex flex-col justify-center items-center text-center">
       <div class="text-blue-700 text-5xl font-extrabold mb-3 tracking-tight">
        <?php echo $totalMembers; ?>
       </div>
       <div class="text-gray-700 font-semibold text-lg">
        Total Members
       </div>
       <div class="mt-6 text-blue-600">
        <i class="fas fa-user-check fa-5x">
        </i>
       </div>
      </div>
      <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
       <h3 class="text-xl font-semibold text-green-800 mb-6 border-b border-green-200 pb-2">
        Member Over Time
       </h3>
       <canvas class="w-full" height="300" id="combinedMemberChart"></canvas>
      </div>
     </div>
     <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
      <h3 class="text-xl font-semibold text-green-800 mb-6 border-b border-green-200 pb-2">
       Member Distribution by District
      </h3>
      <canvas class="min-w-[320px] sm:min-w-[480px]" height="280" id="addressChart">
      </canvas>
     </div>
    </section>
   </div>
  </main>
  <script>
   // Mobile menu toggle
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    menuButton.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    // Address Count Chart
    const addressCtx = document.getElementById('addressChart').getContext('2d');
    new Chart(addressCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($addresses); ?>,
        datasets: [
          {
            label: 'Member Count',
            data: <?php echo json_encode($addressCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 40,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              color: '#065f46',
              font: { weight: '600', size: 14 },
            },
            grid: {
              color: '#d1fae5',
            },
          },
          x: {
            ticks: {
              color: '#065f46',
              font: { weight: '600', size: 14 },
            },
            grid: {
              display: false,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: '#065f46',
            titleFont: { weight: '700', size: 16 },
            bodyFont: { weight: '600', size: 14 },
          },
        },
      },
    });

    // Annual Budget by Year
    const annualBudgetCtx = document
      .getElementById('annualBudgetChart')
      .getContext('2d');
    new Chart(annualBudgetCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($annualBudgetYears); ?>,
        datasets: [
          {
            label: 'Annual Budget',
            data: <?php echo json_encode($annualBudgetTotals); ?>,
            backgroundColor: 'rgba(253, 224, 71, 0.9)',
            borderColor: 'rgba(202, 138, 4, 1)',
            borderWidth: 1,
            borderRadius: 8,
            maxBarThickness: 40,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#78350f',
              font: { weight: '700', size: 14 },
            },
            grid: {
              color: '#fef3c7',
            },
          },
          x: {
            ticks: {
              color: '#78350f',
              font: { weight: '700', size: 14 },
            },
            grid: {
              display: false,
            },
          },
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#78350f',
            titleFont: { weight: '700', size: 16 },
            bodyFont: { weight: '600', size: 14 },
          },
        },
      },
    });

    // Budget by Category (latest year)
    const categoryBudgetCtx = document
      .getElementById('categoryBudgetChart')
      .getContext('2d');
    new Chart(categoryBudgetCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($categoryNames); ?>,
        datasets: [
          {
            label: 'Budget by Category',
            data: <?php echo json_encode($categoryAmounts); ?>,
            backgroundColor: [
              'rgba(54, 162, 235, 0.85)',
              'rgba(255, 99, 132, 0.85)',
              'rgba(255, 206, 86, 0.85)',
              'rgba(75, 192, 192, 0.85)',
              'rgba(153, 102, 255, 0.85)',
              'rgba(255, 159, 64, 0.85)',
            ],
            borderColor: 'rgba(255, 255, 255, 0.95)',
            borderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { font: { weight: '700', size: 14 } },
          },
          tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { weight: '700', size: 16 },
            bodyFont: { weight: '600', size: 14 },
          },
        },
      },
    });

    // Project Cost Breakdown (latest year) - Bar Chart
    const projectCostCtx = document
      .getElementById('projectCostChart')
      .getContext('2d');
    new Chart(projectCostCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($costLabels); ?>,
        datasets: [
          {
            label: 'Project Cost Breakdown',
            data: <?php echo json_encode($costValues); ?>,
            backgroundColor: [
              'rgba(54, 162, 235, 0.85)',
              'rgba(255, 206, 86, 0.85)',
              'rgba(255, 99, 132, 0.85)',
            ],
            borderColor: 'rgba(255, 255, 255, 0.95)',
            borderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#1e293b',
              font: { weight: '700', size: 14 },
              callback: function (value) {
                return '₱' + value.toLocaleString();
              },
            },
            grid: {
              color: '#e0e7ff',
            },
          },
          x: {
            ticks: {
              color: '#1e293b',
              font: { weight: '700', size: 14 },
            },
            grid: {
              display: false,
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { weight: '700', size: 16 },
            bodyFont: { weight: '600', size: 14 },
            callbacks: {
              label: function (context) {
                return '₱' + context.parsed.y.toLocaleString();
              },
            },
          },
        },
      },
    });

    // Combined Member Over Time Chart (By Day, By Month, By Year)
    const combinedMemberCtx = document.getElementById('combinedMemberChart').getContext('2d');

    // Prepare unified labels (dates) for all datasets
    // We'll use the union of all keys from day, month, and year counts, sorted
    const approvedDayCounts = <?php echo json_encode($approvedDayCounts); ?>;
    const approvedMonthCounts = <?php echo json_encode($approvedMonthCounts); ?>;
    const approvedYearCounts = <?php echo json_encode($approvedYearCounts); ?>;

    // Create a Set of all labels
    const allLabelsSet = new Set([
      ...Object.keys(approvedDayCounts),
      ...Object.keys(approvedMonthCounts),
      ...Object.keys(approvedYearCounts),
    ]);

    // Convert to array and sort
    // Sorting mixed date formats (Y-m-d, Y-m, Y) lexically works correctly for these formats
    const allLabels = Array.from(allLabelsSet).sort();

    // Helper function to get value or 0 if missing
    function getValue(data, label) {
      return data[label] !== undefined ? data[label] : 0;
    }

    // Build data arrays aligned with allLabels
    const dayData = allLabels.map(label => getValue(approvedDayCounts, label));
    const monthData = allLabels.map(label => getValue(approvedMonthCounts, label));
    const yearData = allLabels.map(label => getValue(approvedYearCounts, label));

    // Create the combined line chart
    const combinedMemberChart = new Chart(combinedMemberCtx, {
      type: 'line',
      data: {
        labels: allLabels,
        datasets: [
          {
            label: 'By Day',
            data: dayData,
            borderColor: 'rgba(37, 99, 235, 0.85)',
            backgroundColor: 'rgba(37, 99, 235, 0.3)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            borderWidth: 2,
          },
          {
            label: 'By Month',
            data: monthData,
            borderColor: 'rgba(16, 185, 129, 0.85)',
            backgroundColor: 'rgba(16, 185, 129, 0.3)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            borderWidth: 2,
          },
          {
            label: 'By Year',
            data: yearData,
            borderColor: 'rgba(234, 179, 8, 0.85)',
            backgroundColor: 'rgba(234, 179, 8, 0.3)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'nearest',
          intersect: false,
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              color: '#065f46',
              font: { weight: '700', size: 14 },
            },
            grid: {
              color: '#d1fae5',
            },
          },
          x: {
            ticks: {
              color: '#065f46',
              font: { weight: '700', size: 14 },
              maxRotation: 45,
              minRotation: 45,
            },
            grid: {
              display: false,
            },
          },
        },
        plugins: {
          legend: {
            display: true,
            labels: { font: { weight: '700', size: 14 } },
          },
          tooltip: {
            backgroundColor: '#065f46',
            titleFont: { weight: '700', size: 16 },
            bodyFont: { weight: '600', size: 14 },
          },
        },
      },
    });

    // --- AJAX Polling for Real-Time Updates ---
const POLL_INTERVAL = 5000; // 5 seconds
// Store chart instances
let annualBudgetChart, categoryBudgetChart, projectCostChart, addressChart, combinedMemberChartInstance;
window.addEventListener('DOMContentLoaded', function() {
  // Get chart instances after initial render
  annualBudgetChart = Chart.getChart('annualBudgetChart');
  categoryBudgetChart = Chart.getChart('categoryBudgetChart');
  projectCostChart = Chart.getChart('projectCostChart');
  addressChart = Chart.getChart('addressChart');
  combinedMemberChartInstance = combinedMemberChart;
  pollAnalytics();
});
function pollAnalytics() {
  fetch(window.location.pathname + '?ajax=1')
    .then(res => res.json())
    .then(data => {
      // Update dashboard numbers
      document.querySelectorAll('.text-green-700.text-4xl').forEach((el, i) => {
        if (i === 0) el.textContent = data.projectCounts[data.projectCounts.length-1] || 0;
      });
      document.querySelectorAll('.text-green-600.text-4xl').forEach((el, i) => {
        if (i === 0) el.innerHTML = '₱' + Number(data.projectCosts[data.projectCosts.length-1]||0).toLocaleString();
      });
      document.querySelectorAll('.text-yellow-700.text-4xl').forEach((el, i) => {
        if (i === 0) el.innerHTML = '₱' + Number(data.annualBudgetTotals[data.annualBudgetTotals.length-1]||0).toLocaleString();
      });
      document.querySelectorAll('.text-indigo-700.text-4xl').forEach((el, i) => {
        if (i === 0) el.innerHTML = '₱' + Number(data.fund_sources_total||0).toLocaleString();
      });
      document.querySelectorAll('.text-teal-700.text-4xl').forEach((el, i) => {
        if (i === 0) el.innerHTML = '₱' + Number(data.total_balanced_funds||0).toLocaleString();
      });
      document.querySelectorAll('.text-blue-700.text-5xl').forEach((el, i) => {
        if (i === 0) el.textContent = data.totalMembers;
      });
      // Update charts
      if (annualBudgetChart) {
        annualBudgetChart.data.labels = data.annualBudgetYears;
        annualBudgetChart.data.datasets[0].data = data.annualBudgetTotals;
        annualBudgetChart.update();
      }
      if (categoryBudgetChart) {
        categoryBudgetChart.data.labels = data.categoryNames;
        categoryBudgetChart.data.datasets[0].data = data.categoryAmounts;
        categoryBudgetChart.update();
      }
      if (projectCostChart) {
        projectCostChart.data.labels = data.costLabels;
        projectCostChart.data.datasets[0].data = data.costValues;
        projectCostChart.update();
      }
      if (addressChart) {
        addressChart.data.labels = data.addresses;
        addressChart.data.datasets[0].data = data.addressCounts;
        addressChart.update();
      }
      if (combinedMemberChartInstance) {
        // Rebuild combined labels set
        const dayCounts = data.approvedDayCounts || {};
        const monthCounts = data.approvedMonthCounts || {};
        const yearCounts = data.approvedYearCounts || {};
        const labelSet = new Set([
          ...Object.keys(dayCounts),
          ...Object.keys(monthCounts),
          ...Object.keys(yearCounts),
        ]);
        const labels = Array.from(labelSet).sort();
        function getVal(obj, key) {
          return obj[key] !== undefined ? obj[key] : 0;
        }
        const dayData = labels.map(l => getVal(dayCounts, l));
        const monthData = labels.map(l => getVal(monthCounts, l));
        const yearData = labels.map(l => getVal(yearCounts, l));
        combinedMemberChartInstance.data.labels = labels;
        combinedMemberChartInstance.data.datasets[0].data = dayData;
        combinedMemberChartInstance.data.datasets[1].data = monthData;
        combinedMemberChartInstance.data.datasets[2].data = yearData;
        combinedMemberChartInstance.update();
      }
    })
    .finally(() => setTimeout(pollAnalytics, POLL_INTERVAL));
}
  </script>
 </body>
</html>