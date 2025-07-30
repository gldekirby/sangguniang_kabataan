<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Projects Management by Sector</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        /* Make table cells wrap text and prevent horizontal scroll */
        table.project-table {
            table-layout: fixed;
            width: 100%;
            word-wrap: break-word;
        }
        table.project-table th,
        table.project-table td {
            white-space: normal !important;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        /* Reduce padding on very small screens */
        @media (max-width: 640px) {
            table.project-table th,
            table.project-table td {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
                font-size: 0.75rem;
            }
            /* Hide less important columns on very small screens */
            table.project-table th:nth-child(4),
            table.project-table td:nth-child(4),
            table.project-table th:nth-child(6),
            table.project-table td:nth-child(6),
            table.project-table th:nth-child(7),
            table.project-table td:nth-child(7),
            table.project-table th:nth-child(8),
            table.project-table td:nth-child(8),
            table.project-table th:nth-child(13),
            table.project-table td:nth-child(13) {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php
ob_start();
$conn = new mysqli("localhost", "root", "", "youth_sk");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch for edit form (move this up so $edit_row is always defined)
$edit_row = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM projects WHERE id=$id");
    $edit_row = $res->fetch_assoc();
}

// Generate auto reference code for new project
function generateReferenceCode($conn) {
    $prefix = 'PRJ-';
    $year = date('Y');
    $res = $conn->query("SELECT id FROM projects ORDER BY id DESC LIMIT 1");
    $lastId = 1;
    if ($row = $res->fetch_assoc()) {
        $lastId = intval($row['id']) + 1;
    }
    return $prefix . $year . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
}
$auto_reference_code = !$edit_row ? generateReferenceCode($conn) : '';

// Handle Add
if (isset($_POST['add'])) {
    $reference_code = $_POST['reference_code'];
    $project_name = $_POST['project_name'];
    $implementing_office = $_POST['implementing_office'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $expected_output = $_POST['expected_output'];
    $funding_source = $_POST['funding_source'];
    $personal_services = $_POST['personal_services'];
    $mooe = $_POST['mooe'];
    $capital_outlay = $_POST['capital_outlay'];
    $sector = $_POST['sector'];
    $budget_id = $_POST['budget_id'];
    $status = $_POST['status'];
    if (!in_array($status, ['ongoing', 'done'])) {
        $status = 'ongoing'; // fallback/default
    }
    $stmt = $conn->prepare("INSERT INTO projects (reference_code, project_name, implementing_office, start_date, end_date, expected_output, funding_source, personal_services, mooe, capital_outlay, sector, budget_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssidssis", $reference_code, $project_name, $implementing_office, $start_date, $end_date, $expected_output, $funding_source, $personal_services, $mooe, $capital_outlay, $sector, $budget_id, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=abyip");
    exit();
}

// Handle Delete
if (isset($_GET['delete']) && isset($_GET['page']) && $_GET['page'] === 'abyip') {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM projects WHERE id=$id");
    header("Location: dashboard.php?page=abyip");
    exit();
}

// Handle Edit
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $reference_code = $_POST['reference_code'];
    $project_name = $_POST['project_name'];
    $implementing_office = $_POST['implementing_office'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $expected_output = $_POST['expected_output'];
    $funding_source = $_POST['funding_source'];
    $personal_services = $_POST['personal_services'];
    $mooe = $_POST['mooe'];
    $capital_outlay = $_POST['capital_outlay'];
    $sector = $_POST['sector'];
    $budget_id = $_POST['budget_id'];
    $status = $_POST['status'];
    if (!in_array($status, ['ongoing', 'done'])) {
        $status = 'ongoing'; // fallback/default
    }
    $stmt = $conn->prepare("UPDATE projects SET reference_code=?, project_name=?, implementing_office=?, start_date=?, end_date=?, expected_output=?, funding_source=?, personal_services=?, mooe=?, capital_outlay=?, sector=?, budget_id=?, status=? WHERE id=?");
    $stmt->bind_param("sssssssidssisi", $reference_code, $project_name, $implementing_office, $start_date, $end_date, $expected_output, $funding_source, $personal_services, $mooe, $capital_outlay, $sector, $budget_id, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=abyip");
    exit();
}

// Handle inline status update (AJAX)
if (isset($_GET['update_status']) && $_GET['update_status'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id > 0 && in_array($status, ['ongoing', 'done'])) {
        $stmt = $conn->prepare("UPDATE projects SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
        http_response_code(200);
        exit();
    } else {
        http_response_code(400);
        exit();
    }
}

// Fetch budgets for dropdown (with category id and name)
$budgets = $conn->query("SELECT ab.id, ab.program_name, bc.category_name FROM annual_budget ab LEFT JOIN budget_categories bc ON ab.category_id = bc.id");

// Fetch all categories
$categories = $conn->query("SELECT id, category_name FROM budget_categories ORDER BY category_name");
// Fetch all programs with their category
$programs = $conn->query("SELECT ab.id, ab.program_name, ab.category_id FROM annual_budget ab");
$programs_by_category = [];
foreach ($programs as $p) {
    $cat_id = $p['category_id'];
    if (!isset($programs_by_category[$cat_id])) $programs_by_category[$cat_id] = [];
    $programs_by_category[$cat_id][] = [
        'id' => $p['id'],
        'name' => $p['program_name']
    ];
}

// Fetch all projects grouped by sector
$sql = "SELECT p.*, ab.program_name, ab.fiscal_year, fs.name AS fund_name FROM projects p
        LEFT JOIN annual_budget ab ON p.budget_id = ab.id
        LEFT JOIN fund_sources fs ON ab.fund_id = fs.id
        ORDER BY p.sector, p.id";
$result = $conn->query($sql);

// Group projects by sector
$projects_by_sector = [];
while ($row = $result->fetch_assoc()) {
    $sector = $row['sector'] ?: 'Unspecified';
    if (!isset($projects_by_sector[$sector])) {
        $projects_by_sector[$sector] = [];
    }
    $projects_by_sector[$sector][] = $row;
}

ob_end_flush();
?>

<header class="bg-white shadow p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-3 sm:mb-0">ABYIP</h1>
    </header>
<main class="bg-white flex-grow w-full border h-full mx-auto px-4 py-6 space-y-10" id="projects-container">
  <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mb-4">
    <input type="text" id="search-input" placeholder="Search projects..." aria-label="Search projects" class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
    <select id="filter-sector" class="border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm">
      <option value="">All Sectors</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filter-year" class="border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm">
      <option value="">All Years</option>
      <?php
        $years_res = $conn->query("SELECT DISTINCT YEAR(start_date) as year FROM projects WHERE start_date IS NOT NULL AND start_date != '' ORDER BY year DESC");
        while ($year_row = $years_res->fetch_assoc()) {
          $year_val = $year_row['year'];
          if ($year_val) echo "<option value='$year_val'>$year_val</option>";
        }
      ?>
    </select>
    <select id="filter-month" class="border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm">
      <option value="">All Months</option>
      <option value="01">January</option>
      <option value="02">February</option>
      <option value="03">March</option>
      <option value="04">April</option>
      <option value="05">May</option>
      <option value="06">June</option>
      <option value="07">July</option>
      <option value="08">August</option>
      <option value="09">September</option>
      <option value="10">October</option>
      <option value="11">November</option>
      <option value="12">December</option>
    </select>
    <button id="open-popover" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2 whitespace-nowrap">
      <i class="fas fa-plus"></i> Add New Project
    </button>
  </div>
  <div class="space-y-8 min-w-0" id="projects-table-container">
    <table class="bg-white shadow-sm w-full mx-auto text-sm">
      <thead>
        <tr>
          <th class="w-12" scope="col">ID</th>
          <th class="min-w-[140px] text-left" scope="col">Reference Code</th>
          <th class="min-w-[140px] text-left" scope="col">Project Name</th>
          <th class="min-w-[140px] text-left" scope="col">Implementing Office</th>
          <th class="min-w-[140px] text-left" scope="col">Duration</th>
          <th class="min-w-[280px] text-left" scope="col">Expected Output</th>
          <th class="min-w-[140px] text-left hidden" scope="col">Funding Source</th>
          <th class="w-36 text-right" scope="col">Personal Services</th>
          <th class="w-36 text-right" scope="col">MOOE</th>
          <th class="w-36 text-right" scope="col">Capital Outlay</th>
          <th class="w-36 text-right" scope="col">Total Cost</th>
          <th class="w-28 text-center" scope="col">Status</th>
          <th class="w-36 text-center" scope="col">Created At</th>
          <th class="min-w-[140px] text-left" scope="col">Budget Program</th>
          <th class="w-28 text-center" scope="col">Actions</th>
        </tr>
      </thead>
      <?php
      $has_any_project = false;
      foreach ($projects_by_sector as $sector_name => $projects):
        if (count($projects) > 0) {
          $has_any_project = true;
      ?>
        <tbody>
          <tr class="bg-blue-700 category-header-row">
            <td colspan="15" class="font-bold text-lg text-gray-800 py-3">
              <?= htmlspecialchars($sector_name) ?>
            </td>
          </tr>
          <?php foreach ($projects as $row): ?>
          <tr class="hover:bg-blue-50 transition-colors project-row" data-project-name="<?= htmlspecialchars(strtolower($row['project_name'])) ?>" data-implementing-office="<?= htmlspecialchars(strtolower($row['implementing_office'])) ?>" data-funding-source="<?= htmlspecialchars(strtolower($row['funding_source'])) ?>" data-sector="<?= htmlspecialchars($row['sector']) ?>" data-year="<?= $row['start_date'] ? date('Y', strtotime($row['start_date'])) : '' ?>" data-month="<?= $row['start_date'] ? date('m', strtotime($row['start_date'])) : '' ?>">
            <td class="text-gray-700 font-mono text-center">
              <?= $row['id'] ?>
            </td>
            <td class="text-gray-900 font-semibold">
              <?= htmlspecialchars($row['reference_code']) ?>
            </td>
            <td class="text-gray-900 font-semibold">
              <?= htmlspecialchars($row['project_name']) ?>
            </td>
            <td class="text-gray-700">
              <?= htmlspecialchars($row['implementing_office']) ?>
            </td>
            <td class="text-gray-700">
              <?php
                $start = $row['start_date'] ? htmlspecialchars($row['start_date']) : '';
                $end = $row['end_date'] ? htmlspecialchars($row['end_date']) : '';
                if ($start && $end) {
                  echo $start . ' to ' . $end;
                } elseif ($start) {
                  echo $start;
                } elseif ($end) {
                  echo $end;
                } else {
                  echo '-';
                }
              ?>
            </td>
            <td class="text-gray-600">
              <?= htmlspecialchars($row['expected_output']) ?>
            </td>
            <td class="text-gray-700 hidden">
              <?= htmlspecialchars($row['funding_source']) ?>
            </td>
            <td class="text-right text-gray-700">
              ₱<?= number_format($row['personal_services'], 0) ?>
            </td>
            <td class="text-right text-gray-700">
              ₱<?= number_format($row['mooe'], 0) ?>
            </td>
            <td class="text-right text-gray-700">
              ₱<?= number_format($row['capital_outlay'], 0) ?>
            </td>
            <td class="text-right font-semibold text-gray-900">
              ₱<?= number_format($row['total_cost'], 0) ?>
            </td>
            <td class="text-center">
              <select class="status-select border rounded px-2 py-1 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="<?= $row['id'] ?>">
                <option value="ongoing" <?= $row['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                <option value="done" <?= $row['status'] === 'done' ? 'selected' : '' ?>>Done</option>
              </select>
            </td>
            <td class="text-gray-700 text-center">
              <?= $row['created_at'] ?>
            </td>
            <td class="text-gray-700">
              <?= htmlspecialchars($row['program_name']) ?>
            </td>
            <td class="text-center">
              <button type="button" class="edit-btn text-blue-600 hover:text-blue-800 mr-2" data-id="<?= $row['id'] ?>" aria-label="Edit project <?= htmlspecialchars($row['project_name']) ?>">
                <i class="fas fa-edit"></i>
              </button>
              <button type="button" class="delete-btn text-red-600 hover:text-red-800" data-id="<?= $row['id'] ?>" aria-label="Delete project <?= htmlspecialchars($row['project_name']) ?>">
                <i class="fas fa-trash-alt"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      <?php } endforeach; ?>
      </tbody>
      <tbody>
        <tr class="no-projects-found-row" style="display:none">
          <td colspan="15" class="px-4 py-12 text-center text-gray-500 bg-gray-100">
            <img alt="Illustration of a sad chart with no data, symbolizing no project found" class="mx-auto mb-6" height="100" src="https://storage.googleapis.com/a1aa/image/04e6f885-7b67-4d27-27c8-d20486d90b97.jpg" width="100"/>
            <p class="text-gray-500 text-lg font-semibold select-none">No project found.</p>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</main>
<style>
  /* Enhanced table design */
  #projects-table-container {
    padding: 1.5rem 1.5rem 1.5rem 1.5rem;
  }
  #projects-table-container table {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px 0 rgba(0,0,0,0.04);
    background: #fff;
  }
  #projects-table-container th {
    background: #f1f5f9;
    color: #334155;
    font-weight: 700;
    letter-spacing: 0.03em;
    border-bottom: 2px solid #e2e8f0;
    padding-top: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    padding-right: 1rem;
  }
  #projects-table-container td {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding-top: 0.9rem;
    padding-bottom: 0.9rem;
    padding-left: 1rem;
    padding-right: 1rem;
    font-size: 0.97rem;
  }
  #projects-table-container tr.category-header-row td {
    background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
    color: #fff;
    font-size: 1.1rem;
    letter-spacing: 0.02em;
    border-bottom: 2px solid #3b82f6;
    box-shadow: 0 2px 6px 0 rgba(37,99,235,0.08);
    padding-top: 1.1rem;
    padding-bottom: 1.1rem;
    padding-left: 1.2rem;
    padding-right: 1.2rem;
  }
  #projects-table-container tr.no-projects-found-row td {
    background: #f3f4f6;
    color: #64748b;
    font-size: 1.1rem;
    border-bottom: none;
    padding-top: 2.5rem;
    padding-bottom: 2.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
  #projects-table-container tr:hover:not(.category-header-row):not(.no-projects-found-row) td {
    background: #eff6ff;
  }
</style>

<!-- Add/Edit Project Popover -->
<div id="popover-backdrop" class="fixed inset-0 bg-black bg-opacity-30 hidden z-40"></div>
<form method="post" action="" id="project-popover" class="fixed top-1/2 left-1/2 max-w-lg w-full bg-white rounded-lg shadow-lg p-6 z-50 -translate-x-1/2 -translate-y-1/2 hidden overflow-y-auto max-h-[90vh]" aria-modal="true" role="dialog" aria-labelledby="popover-title">
    <h2 id="popover-title" class="text-xl font-semibold mb-4 text-gray-800"><?= $edit_row ? 'Edit Project' : 'Add New Project' ?></h2>
    <?php if ($edit_row): ?>
        <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
    <?php endif; ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="reference_code" class="block text-gray-700 font-medium mb-1">Reference Code <span class="text-red-500">*</span></label>
            <input type="text" id="reference_code" name="reference_code" value="<?= $edit_row ? htmlspecialchars($edit_row['reference_code']) : htmlspecialchars($auto_reference_code) ?>" <?= $edit_row ? '' : 'readonly' ?> required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="project_name" class="block text-gray-700 font-medium mb-1">Project Name <span class="text-red-500">*</span></label>
            <input type="text" id="project_name" name="project_name" value="<?= $edit_row ? htmlspecialchars($edit_row['project_name']) : '' ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="implementing_office" class="block text-gray-700 font-medium mb-1">Implementing Office</label>
            <input type="text" id="implementing_office" name="implementing_office" value="<?= $edit_row ? htmlspecialchars($edit_row['implementing_office']) : 'Sangguniang Kabataan' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="start_date" class="block text-gray-700 font-medium mb-1">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?= $edit_row ? htmlspecialchars($edit_row['start_date']) : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="end_date" class="block text-gray-700 font-medium mb-1">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?= $edit_row ? htmlspecialchars($edit_row['end_date']) : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="expected_output" class="block text-gray-700 font-medium mb-1">Expected Output</label>
            <textarea id="expected_output" name="expected_output" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= $edit_row ? htmlspecialchars($edit_row['expected_output']) : '' ?></textarea>
        </div>
        <div>
            <label for="funding_source" class="block text-gray-700 font-medium mb-1">Funding Source</label>
            <input type="text" id="funding_source" name="funding_source" value="<?= $edit_row ? htmlspecialchars($edit_row['funding_source']) : 'GF - 10%SK' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="category-select" class="block text-gray-700 font-medium mb-1">Sector/Category <span class="text-red-500">*</span></label>
            <select id="category-select" name="sector" required class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $edit_row && $edit_row['sector'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="budget-select" class="block text-gray-700 font-medium mb-1">Budget Program <span class="text-red-500">*</span></label>
            <select name="budget_id" id="budget-select" required class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select Program --</option>
                <!-- Options will be populated by JS -->
            </select>
        </div>
        <div>
            <label for="personal_services" class="block text-gray-700 font-medium mb-1">Personal Services</label>
            <input type="number" min="0" step="1" id="personal_services" name="personal_services" value="<?= $edit_row ? $edit_row['personal_services'] : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="mooe" class="block text-gray-700 font-medium mb-1">MOOE</label>
            <input type="number" step="0.01" id="mooe" name="mooe" value="<?= $edit_row ? $edit_row['mooe'] : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="capital_outlay" class="block text-gray-700 font-medium mb-1">Capital Outlay</label>
            <input type="number" step="0.01" id="capital_outlay" name="capital_outlay" value="<?= $edit_row ? $edit_row['capital_outlay'] : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="status" class="block text-gray-700 font-medium mb-1">Status</label>
            <select id="status" name="status" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="ongoing" <?= ($edit_row && $edit_row['status'] == 'ongoing') ? 'selected' : '' ?>>Ongoing</option>
                <option value="done" <?= ($edit_row && $edit_row['status'] == 'done') ? 'selected' : '' ?>>Done</option>
            </select>
        </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
        <?php if ($edit_row): ?>
            <button type="submit" name="update" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-2">
                <i class="fas fa-save"></i> Update
            </button>
            <a href="dashboard.php?page=abyip" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
                Cancel
            </a>
        <?php else: ?>
            <button type="submit" name="add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2">
                <i class="fas fa-plus"></i> Add
            </button>
            <button type="button" id="close-popover" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
                Cancel
            </button>
        <?php endif; ?>
    </div>
</form>

<!-- Delete Confirmation Popover -->
<div id="delete-popover-backdrop" class="fixed inset-0 bg-black bg-opacity-30 hidden z-40"></div>
<div id="delete-popover" class="fixed top-1/2 left-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg p-6 z-50 -translate-x-1/2 -translate-y-1/2 hidden" role="dialog" aria-modal="true" aria-labelledby="delete-popover-title">
    <h3 id="delete-popover-title" class="text-lg font-semibold text-gray-800 mb-4">Confirm Deletion</h3>
    <p class="text-gray-800 mb-6">Are you sure you want to delete this project?</p>
    <form method="get" action="dashboard.php?page=abyip" class="flex justify-end gap-3">
        <input type="hidden" name="delete" id="delete-id-input" />
        <input type="hidden" name="page" value="abyip" />
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center gap-2">
            <i class="fas fa-trash-alt"></i> Yes, Delete
        </button>
        <button type="button" id="cancel-delete" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
            Cancel
        </button>
    </form>
</div>

<script>
    // Popover open/close logic for add/edit
    const openBtn = document.getElementById('open-popover');
    const popover = document.getElementById('project-popover');
    const closeBtn = document.getElementById('close-popover');
    const backdrop = document.getElementById('popover-backdrop');

    function openPopover() {
        popover.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Focus first input for accessibility
        setTimeout(() => {
            document.getElementById('project_name').focus();
        }, 100);
    }
    function closePopover() {
        popover.classList.add('hidden');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
        // Clear form if adding new project
        if (!<?= $edit_row ? 'true' : 'false' ?>) {
            popover.reset?.();
            // Reset sector input and budget select
            document.getElementById('sector-input').value = '';
            document.getElementById('budget-select').selectedIndex = 0;
        }
    }
    if (openBtn) openBtn.addEventListener('click', openPopover);
    if (closeBtn) closeBtn.addEventListener('click', closePopover);
    if (backdrop) backdrop.addEventListener('click', closePopover);

    // If in edit mode, show the popover automatically
    <?php if ($edit_row): ?>
    openPopover();
    <?php endif; ?>

    // Edit button logic
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Always keep ?page=abyip in the URL
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'abyip');
            url.searchParams.set('edit', id);
            window.location.href = url.pathname + '?' + url.searchParams.toString();
        });
    });

    // Delete popover logic
    const deleteBtns = document.querySelectorAll('.delete-btn');
    const deletePopover = document.getElementById('delete-popover');
    const deleteBackdrop = document.getElementById('delete-popover-backdrop');
    const deleteIdInput = document.getElementById('delete-id-input');
    const cancelDeleteBtn = document.getElementById('cancel-delete');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteIdInput.value = this.getAttribute('data-id');
            // No need to set form action dynamically, it is always correct
            deletePopover.classList.remove('hidden');
            deleteBackdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                cancelDeleteBtn.focus();
            }, 100);
        });
    });
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', function() {
        deletePopover.classList.add('hidden');
        deleteBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    });
    if (deleteBackdrop) deleteBackdrop.addEventListener('click', function() {
        deletePopover.classList.add('hidden');
        deleteBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    });

    // --- Cascading dropdown for category -> budget program ---
const programsByCategory = <?= json_encode($programs_by_category) ?>;
const categorySelect = document.getElementById('category-select');
const budgetSelect = document.getElementById('budget-select');
function updateBudgetPrograms() {
    const catId = categorySelect.value;
    budgetSelect.innerHTML = '<option value="">-- Select Program --</option>';
    if (programsByCategory[catId]) {
        programsByCategory[catId].forEach(function(prog) {
            const opt = document.createElement('option');
            opt.value = prog.id;
            opt.textContent = prog.name;
            budgetSelect.appendChild(opt);
        });
    }
}
categorySelect.addEventListener('change', updateBudgetPrograms);
// On page load, if editing, set the correct programs
window.addEventListener('DOMContentLoaded', function() {
    if (categorySelect.value) {
        updateBudgetPrograms();
        <?php if ($edit_row && $edit_row['budget_id']): ?>
        budgetSelect.value = "<?= $edit_row['budget_id'] ?>";
        <?php endif; ?>
    }
});

    // Search/filter functionality for single table with category headers
    const searchInput = document.getElementById('search-input');
    const filterSector = document.getElementById('filter-sector');
    const filterYear = document.getElementById('filter-year');
    const filterMonth = document.getElementById('filter-month');
    const table = document.querySelector('#projects-table-container table');

    function filterProjects() {
      const searchVal = searchInput.value.trim().toLowerCase();
      const sectorVal = filterSector.value;
      const yearVal = filterYear.value;
      const monthVal = filterMonth.value;
      const rows = Array.from(table.querySelectorAll('tbody tr'));
      let anyVisible = false;
      let currentHeader = null;
      let hasVisibleInCategory = false;
      let noProjectsRow = table.querySelector('.no-projects-found-row');
      let categoryRows = [];

      // Hide all rows initially
      rows.forEach(row => {
        if (!row.classList.contains('no-projects-found-row')) {
          row.style.display = 'none';
        }
      });

      rows.forEach((row, idx) => {
        if (row.classList.contains('category-header-row')) {
          currentHeader = row;
          hasVisibleInCategory = false;
          categoryRows = [];
          return;
        }
        if (row.classList.contains('no-projects-found-row')) {
          return;
        }
        // Data row
        const projectName = (row.getAttribute('data-project-name') || '').toLowerCase();
        const implementingOffice = (row.getAttribute('data-implementing-office') || '').toLowerCase();
        const fundingSource = (row.getAttribute('data-funding-source') || '').toLowerCase();
        const rowSector = row.getAttribute('data-sector') || '';
        const rowYear = row.getAttribute('data-year') || '';
        const rowMonth = row.getAttribute('data-month') || '';
        const matchSearch = !searchVal || projectName.includes(searchVal) || implementingOffice.includes(searchVal) || fundingSource.includes(searchVal);
        const matchSector = !sectorVal || rowSector === sectorVal;
        const matchYear = !yearVal || rowYear === yearVal;
        const matchMonth = !monthVal || rowMonth === monthVal;
        const visible = matchSearch && matchSector && matchYear && matchMonth;
        if (visible) {
          row.style.display = '';
          hasVisibleInCategory = true;
          anyVisible = true;
        }
        categoryRows.push(row);
        // After each data row, check if next is a header or end
        const nextRow = rows[idx + 1];
        if (!nextRow || (nextRow && nextRow.classList.contains('category-header-row')) || (nextRow && nextRow.classList.contains('no-projects-found-row'))) {
          if (currentHeader) {
            currentHeader.style.display = hasVisibleInCategory ? '' : 'none';
            // Hide all rows in this category if header is hidden
            if (!hasVisibleInCategory) {
              categoryRows.forEach(r => r.style.display = 'none');
            }
          }
        }
      });
      // Show/hide the no projects row with dynamic message
      if (noProjectsRow) {
        // Only show if there are no visible project rows (not just headers)
        const visibleProjectRows = rows.filter(row => !row.classList.contains('category-header-row') && !row.classList.contains('no-projects-found-row') && row.style.display !== 'none');
        if (visibleProjectRows.length === 0) {
          let msg = 'No project found.';
          if (yearVal && !rows.some(row => row.getAttribute('data-year') === yearVal && row.style.display !== 'none')) {
            msg = `No project found in year ${yearVal}.`;
          }
          if (monthVal && !rows.some(row => row.getAttribute('data-month') === monthVal && row.style.display !== 'none')) {
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const monthName = monthNames[parseInt(monthVal, 10) - 1] || monthVal;
            msg = `No project found in ${monthName}.`;
          }
          if (sectorVal) {
            const sectorOption = filterSector.querySelector(`option[value="${sectorVal}"]`);
            const sectorName = sectorOption ? sectorOption.textContent.trim() : sectorVal;
            msg = `No project found in category ${sectorName}.`;
          }
          noProjectsRow.querySelector('p').textContent = msg;
          noProjectsRow.style.display = '';
        } else {
          noProjectsRow.style.display = 'none';
        }
      }
    }

    searchInput.addEventListener('input', filterProjects);
    filterSector.addEventListener('change', filterProjects);
    filterYear.addEventListener('change', filterProjects);
    filterMonth.addEventListener('change', filterProjects);
    // Initial call
    filterProjects();

    // Keyboard accessibility: close popovers with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (!popover.classList.contains('hidden')) {
                closePopover();
            }
            if (!deletePopover.classList.contains('hidden')) {
                deletePopover.classList.add('hidden');
                deleteBackdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
    });

    // Inline status update
    document.querySelectorAll('.status-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const status = this.value;
            // Simple AJAX request to update status
            fetch('dashboard.php?page=abyip&update_status=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
            })
            .then(res => res.ok ? location.reload() : alert('Failed to update status'));
        });
    });
</script>

</body>
</html>