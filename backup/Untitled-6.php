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
    $stmt = $conn->prepare("INSERT INTO projects (reference_code, project_name, implementing_office, start_date, end_date, expected_output, funding_source, personal_services, mooe, capital_outlay, sector, budget_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssidssi", $reference_code, $project_name, $implementing_office, $start_date, $end_date, $expected_output, $funding_source, $personal_services, $mooe, $capital_outlay, $sector, $budget_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=abyip");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
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
    $stmt = $conn->prepare("UPDATE projects SET reference_code=?, project_name=?, implementing_office=?, start_date=?, end_date=?, expected_output=?, funding_source=?, personal_services=?, mooe=?, capital_outlay=?, sector=?, budget_id=? WHERE id=?");
    $stmt->bind_param("sssssssidssii", $reference_code, $project_name, $implementing_office, $start_date, $end_date, $expected_output, $funding_source, $personal_services, $mooe, $capital_outlay, $sector, $budget_id, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=abyip");
    exit();
}

// Fetch budgets for dropdown (with category id and name)
$budgets = $conn->query("SELECT ab.id, ab.program_name, bc.category_name FROM annual_budget ab LEFT JOIN budget_categories bc ON ab.category_id = bc.id");

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
<main class=" bg-white flex-grow w-full border h-full mx-auto px-4 py-6 space-y-10" id="projects-container">
    
  <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
              <input type="text" id="search-input" placeholder="Search projects..." aria-label="Search projects" class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <button id="open-popover" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2 whitespace-nowrap">
                  <i class="fas fa-plus"></i> Add New Project
              </button>
          </div>
    <?php if (count($projects_by_sector) === 0): ?>
        <p class="text-center text-gray-600">No projects found.</p>
    <?php else: ?>
        <?php foreach ($projects_by_sector as $sector_name => $projects): ?>
          
            <section aria-labelledby="sector-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $sector_name))) ?>" class="bg-gray-200 border shadow p-4 sector-section" data-sector="<?= htmlspecialchars(strtolower($sector_name)) ?>">
                <h2 id="sector-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $sector_name))) ?>" class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
                    <?= htmlspecialchars($sector_name) ?>
                </h2>
                <table class="min-w-full divide-y divide-gray-200 text-sm project-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal hidden">ID</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Reference Code</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Project Name</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Implementing Office</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Start Date</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">End Date</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Expected Output</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Funding Source</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700 whitespace-normal">Personal Services</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700 whitespace-normal">MOOE</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700 whitespace-normal">Capital Outlay</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700 whitespace-normal">Total Cost</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Created At</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 whitespace-normal">Budget Program</th>
                            <th class="px-3 py-2 text-center font-medium text-gray-700 whitespace-normal">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($projects as $row): ?>
                        <tr class="hover:bg-gray-50 project-row" data-project-name="<?= htmlspecialchars(strtolower($row['project_name'])) ?>" data-implementing-office="<?= htmlspecialchars(strtolower($row['implementing_office'])) ?>" data-funding-source="<?= htmlspecialchars(strtolower($row['funding_source'])) ?>">
                            <td class="px-3 py-2 whitespace-normal text-gray-700 hidden"><?= $row['id'] ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['reference_code']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['project_name']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['implementing_office']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['start_date']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['end_date']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['expected_output']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['funding_source']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-right text-gray-700">₱<?= number_format($row['personal_services'], 2) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-right text-gray-700">₱<?= number_format($row['mooe'], 2) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-right text-gray-700">₱<?= number_format($row['capital_outlay'], 2) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-right font-semibold text-gray-900">₱<?= number_format($row['total_cost'], 2) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= $row['created_at'] ?></td>
                            <td class="px-3 py-2 whitespace-normal text-gray-700"><?= htmlspecialchars($row['program_name']) ?></td>
                            <td class="px-3 py-2 whitespace-normal text-center">
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
                </table>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

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
            <input type="text" id="expected_output" name="expected_output" value="<?= $edit_row ? htmlspecialchars($edit_row['expected_output']) : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="funding_source" class="block text-gray-700 font-medium mb-1">Funding Source</label>
            <input type="text" id="funding_source" name="funding_source" value="<?= $edit_row ? htmlspecialchars($edit_row['funding_source']) : 'GF - 10%SK' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
            <label for="sector-input" class="block text-gray-700 font-medium mb-1">Sector</label>
            <input type="text" id="sector-input" name="sector" value="<?= $edit_row ? htmlspecialchars($edit_row['sector']) : '' ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
            <label for="budget-select" class="block text-gray-700 font-medium mb-1">Budget Program</label>
            <select name="budget_id" id="budget-select" class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select --</option>
                <?php foreach ($budgets as $b): ?>
                    <option value="<?= $b['id'] ?>" data-category="<?= htmlspecialchars($b['category_name']) ?>" <?= $edit_row && $edit_row['budget_id'] == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['program_name']) ?></option>
                <?php endforeach; ?>
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
    const deleteForm = document.querySelector('#delete-popover form');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteIdInput.value = this.getAttribute('data-id');
            // Always keep ?page=abyip in the URL for delete form
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'abyip');
            deleteForm.action = url.pathname + '?' + url.searchParams.toString();
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

    // Auto-fill sector based on selected budget program (category)
    const budgetSelect = document.getElementById('budget-select');
    const sectorInput = document.getElementById('sector-input');
    budgetSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const category = selected.getAttribute('data-category') || '';
        sectorInput.value = category;
        // Optionally, focus the sector input for user clarity
        sectorInput.focus();
    });

    // If editing, auto-fill sector on load
    <?php if ($edit_row && $edit_row['budget_id']): ?>
    (function() {
        const selected = budgetSelect.options[budgetSelect.selectedIndex];
        const category = selected.getAttribute('data-category') || '';
        sectorInput.value = category;
    })();
    <?php endif; ?>

    // Search/filter functionality
    const searchInput = document.getElementById('search-input');
    const sectorSections = document.querySelectorAll('.sector-section');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        sectorSections.forEach(section => {
            let anyVisible = false;
            const rows = section.querySelectorAll('.project-row');
            rows.forEach(row => {
                // Search in project name, implementing office, funding source
                const projectName = row.getAttribute('data-project-name') || '';
                const implementingOffice = row.getAttribute('data-implementing-office') || '';
                const fundingSource = row.getAttribute('data-funding-source') || '';
                const match = projectName.includes(query) || implementingOffice.includes(query) || fundingSource.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) anyVisible = true;
            });
            // Show/hide entire sector section if no rows visible
            section.style.display = anyVisible ? '' : 'none';
        });
    });

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
</script>

</body>
</html>