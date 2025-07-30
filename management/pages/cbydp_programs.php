<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CBYDP Programs Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center p-4">
<?php
include '../config.php';

// Handle form submissions for create and update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $program_name = $_POST['program_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $budget_id = $_POST['budget_id'] ?? null;

    if ($id) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE cbydp_programs SET program_name = ?, description = ?, start_date = ?, end_date = ?, budget_id = ? WHERE id = ?");
        if (empty($budget_id)) {
            $null = null;
            $stmt->bind_param("ssssii", $program_name, $description, $start_date, $end_date, $null, $id);
        } else {
            $stmt->bind_param("ssssii", $program_name, $description, $start_date, $end_date, $budget_id, $id);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO cbydp_programs (program_name, description, start_date, end_date, budget_id) VALUES (?, ?, ?, ?, ?)");
        if (empty($budget_id)) {
            $null = null;
            $stmt->bind_param("ssssi", $program_name, $description, $start_date, $end_date, $null);
        } else {
            $stmt->bind_param("ssssi", $program_name, $description, $start_date, $end_date, $budget_id);
        }
        $stmt->execute();
        $stmt->close();
    }
    echo '<script>window.location.href = "dashboard.php?page=cbydp_programs";</script>';
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM cbydp_programs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php?page=cbydp_programs');
    exit;
}

// Fetch all cbydp_programs records
$result = $conn->query("SELECT * FROM cbydp_programs ORDER BY start_date DESC");
$programs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    $result->free();
}

// Fetch annual_budget items for dropdown
$result = $conn->query("SELECT id, program_name FROM annual_budget ORDER BY program_name ASC");
$budget_items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $budget_items[] = $row;
    }
    $result->free();
}
?>

<div class="max-w-7xl w-full p-4">
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-extrabold text-gray-900">CBYDP Programs Management</h1>
    </header>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0 max-w-7xl w-full">
        <div class="flex space-x-2 justify-center sm:justify-start w-full sm:w-auto">
            <button id="openModalBtn" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-haspopup="dialog" aria-controls="programModal" aria-expanded="false">
                <i class="fas fa-plus mr-2"></i> Add Program
            </button>
            <a href="cbydp_programs_print.php" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-white font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" aria-label="Print programs list">
                <i class="fas fa-print mr-2"></i> Print
            </a>
        </div>
        <div class="w-full sm:w-64">
            <input type="text" id="searchInput" placeholder="Search programs..." aria-label="Search programs" class="rounded-md border border-gray-300 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full" />
        </div>
    </div>

    <section class="bg-white shadow rounded-lg p-6 max-w-7xl w-full overflow-x-auto">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Programs List</h2>
        <table class="min-w-full divide-y divide-gray-200 text-sm" id="programsTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 cursor-pointer select-none" data-sort="id">ID <i class="fas fa-sort"></i></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 cursor-pointer select-none" data-sort="program_name">Program Name <i class="fas fa-sort"></i></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Description</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 cursor-pointer select-none" data-sort="start_date">Start Date <i class="fas fa-sort"></i></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 cursor-pointer select-none" data-sort="end_date">End Date <i class="fas fa-sort"></i></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Budget Item</th>
                    <th class="px-4 py-2 text-center font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="programsTableBody">
                <?php if (count($programs) === 0): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No programs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($programs as $program): ?>
                        <tr data-program-name="<?= htmlspecialchars(strtolower($program['program_name'])) ?>" data-description="<?= htmlspecialchars(strtolower($program['description'])) ?>">
                            <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($program['id']) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($program['program_name']) ?></td>
                            <td class="px-4 py-2 max-w-xs break-words whitespace-pre-wrap"><?= nl2br(htmlspecialchars($program['description'])) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($program['start_date']) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($program['end_date']) ?></td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <?php
                                $budgetName = '';
                                foreach ($budget_items as $item) {
                                    if ($item['id'] == $program['budget_id']) {
                                        $budgetName = $item['program_name'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($budgetName ?: 'N/A');
                                ?>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-center space-x-2">
                                <button 
                                    onclick="openEditModal(<?= $program['id'] ?>, '<?= addslashes(htmlspecialchars($program['program_name'])) ?>', '<?= addslashes(htmlspecialchars($program['description'])) ?>', '<?= $program['start_date'] ?>', '<?= $program['end_date'] ?>', <?= $program['budget_id'] ?? 'null' ?>)" 
                                    class="text-indigo-600 hover:text-indigo-900 focus:outline-none" 
                                    aria-label="Edit program <?= htmlspecialchars($program['program_name']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="cbydp_programs.php?delete=<?= $program['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this program?')" 
                                   class="text-red-600 hover:text-red-900" 
                                   aria-label="Delete program <?= htmlspecialchars($program['program_name']) ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<!-- Modal backdrop -->
<div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

<!-- Modal -->
<div id="programModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1" class="fixed inset-0 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <header class="flex justify-between items-center border-b border-gray-200 px-6 py-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Add Program</h3>
            <button id="closeModalBtn" aria-label="Close modal" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                <i class="fas fa-times text-xl"></i>
            </button>
        </header>
        <form method="post" action="dashboard.php?page=cbydp_programs" class="px-6 py-4 space-y-6" id="programFormModal">
            <input type="hidden" name="id" id="modal_id" value="">
            <div>
                <label for="modal_program_name" class="block text-sm font-medium text-gray-700 mb-1">Program Name</label>
                <input type="text" name="program_name" id="modal_program_name" required class="block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <div>
                <label for="modal_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="modal_description" rows="3" class="block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="modal_start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="modal_start_date" class="block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                    <label for="modal_end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" id="modal_end_date" class="block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            </div>

            <div>
                <label for="modal_budget_id" class="block text-sm font-medium text-gray-700 mb-1">Budget Item</label>
                <select name="budget_id" id="modal_budget_id" class="block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Budget Item</option>
                    <?php foreach ($budget_items as $item): ?>
                        <option value="<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['program_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-between items-center space-x-4">
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
                <button type="button" id="modalClearBtn" class="inline-flex items-center justify-center rounded-md bg-gray-300 px-4 py-2 text-gray-700 font-semibold hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <i class="fas fa-times mr-2"></i> Clear
                </button>
                <button type="button" id="modalCancelBtn" class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-white font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-ban mr-2"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('programModal');
const backdrop = document.getElementById('modalBackdrop');
const openModalBtn = document.getElementById('openModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');
const modalClearBtn = document.getElementById('modalClearBtn');
const modalCancelBtn = document.getElementById('modalCancelBtn');

const modalId = document.getElementById('modal_id');
const modalProgramName = document.getElementById('modal_program_name');
const modalDescription = document.getElementById('modal_description');
const modalStartDate = document.getElementById('modal_start_date');
const modalEndDate = document.getElementById('modal_end_date');
const modalBudgetId = document.getElementById('modal_budget_id');
const modalTitle = document.getElementById('modalTitle');

const searchInput = document.getElementById('searchInput');
const programsTableBody = document.getElementById('programsTableBody');
const programsTable = document.getElementById('programsTable');

let currentSort = { column: null, asc: true };

function openModal() {
    modal.classList.remove('hidden');
    backdrop.classList.remove('hidden');
    openModalBtn.setAttribute('aria-expanded', 'true');
    modalProgramName.focus();
}

function closeModal() {
    modal.classList.add('hidden');
    backdrop.classList.add('hidden');
    openModalBtn.setAttribute('aria-expanded', 'false');
    clearModalForm();
    openModalBtn.focus();
}

function clearModalForm() {
    modalId.value = '';
    modalProgramName.value = '';
    modalDescription.value = '';
    modalStartDate.value = '';
    modalEndDate.value = '';
    modalBudgetId.value = '';
    modalTitle.textContent = 'Add Program';
}

function openEditModal(id, program_name, description, start_date, end_date, budget_id) {
    modalId.value = id;
    modalProgramName.value = program_name;
    modalDescription.value = description;
    modalStartDate.value = start_date;
    modalEndDate.value = end_date;
    modalBudgetId.value = budget_id ?? '';
    modalTitle.textContent = 'Edit Program';
    openModal();
}

openModalBtn.addEventListener('click', () => {
    clearModalForm();
    openModal();
});

closeModalBtn.addEventListener('click', closeModal);
modalClearBtn.addEventListener('click', () => {
    modalProgramName.value = '';
    modalDescription.value = '';
    modalStartDate.value = '';
    modalEndDate.value = '';
    modalBudgetId.value = '';
    modalProgramName.focus();
});
modalCancelBtn.addEventListener('click', closeModal);
backdrop.addEventListener('click', closeModal);

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeModal();
    }
});

// Search/filter programs
function filterPrograms() {
    const term = searchInput.value.toLowerCase();
    Array.from(programsTableBody.rows).forEach(row => {
        const name = row.getAttribute('data-program-name');
        const desc = row.getAttribute('data-description');
        if (name.includes(term) || desc.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
searchInput.addEventListener('input', filterPrograms);

// Sorting table columns
function sortTable(column) {
    const tbody = programsTableBody;
    const rows = Array.from(tbody.rows);

    let compareFn;
    switch(column) {
        case 'id':
            compareFn = (a,b) => parseInt(a.cells[0].textContent) - parseInt(b.cells[0].textContent);
            break;
        case 'program_name':
            compareFn = (a,b) => a.cells[1].textContent.localeCompare(b.cells[1].textContent);
            break;
        case 'start_date':
            compareFn = (a,b) => new Date(a.cells[3].textContent) - new Date(b.cells[3].textContent);
            break;
        case 'end_date':
            compareFn = (a,b) => new Date(a.cells[4].textContent) - new Date(b.cells[4].textContent);
            break;
        default:
            return;
    }

    if (currentSort.column === column) {
        currentSort.asc = !currentSort.asc;
    } else {
        currentSort.column = column;
        currentSort.asc = true;
    }

    rows.sort((a,b) => currentSort.asc ? compareFn(a,b) : compareFn(b,a));

    rows.forEach(row => tbody.appendChild(row));

    updateSortIcons();
}

function updateSortIcons() {
    const headers = programsTable.querySelectorAll('th[data-sort]');
    headers.forEach(th => {
        const icon = th.querySelector('i');
        if (th.dataset.sort === currentSort.column) {
            icon.classList.remove('fa-sort', 'fa-sort-up', 'fa-sort-down');
            icon.classList.add(currentSort.asc ? 'fa-sort-up' : 'fa-sort-down');
        } else {
            icon.classList.remove('fa-sort-up', 'fa-sort-down');
            icon.classList.add('fa-sort');
        }
    });
}

document.querySelectorAll('th[data-sort]').forEach(th => {
    th.addEventListener('click', () => sortTable(th.dataset.sort));
});

// Initialize sort icons
updateSortIcons();
</script>
</body>
</html>