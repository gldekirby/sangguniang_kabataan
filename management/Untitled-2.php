<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Annual Budget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        /* For scrollbar in tables */
        ::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background: #a0aec0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-track {
            background: #edf2f7;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php
$conn = new mysqli("localhost", "root", "", "expense");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch total funds
$total_funds = 0;
$res = $conn->query("SELECT SUM(amount) as total_funds FROM fund_sources");
if ($row = $res->fetch_assoc()) {
    $total_funds = $row['total_funds'];
}

// Show success message if redirected after add, edit, or delete
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $success_message = 'Budget added successfully!';
    } elseif ($_GET['success'] === 'updated') {
        $success_message = 'Budget updated successfully!';
    } elseif ($_GET['success'] === 'deleted') {
        $success_message = 'Budget deleted successfully!';
    }
}

// Handle Add
if (isset($_POST['add'])) {
    $category_id = $_POST['category_id'];
    $program_name = $_POST['program_name'];
    $description = $_POST['description'];
    $allocated_amount = $_POST['allocated_amount'];
    $fiscal_year = $_POST['fiscal_year'];
    $stmt = $conn->prepare("INSERT INTO annual_budget (category_id, program_name, description, allocated_amount, fiscal_year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $category_id, $program_name, $description, $allocated_amount, $fiscal_year);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=annual_budget&success=added");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM annual_budget WHERE id=$id");
    header("Location: dashboard.php?page=annual_budget&success=deleted");
    exit();
}

// Handle Edit
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $program_name = $_POST['program_name'];
    $description = $_POST['description'];
    $allocated_amount = $_POST['allocated_amount'];
    $fiscal_year = $_POST['fiscal_year'];
    $stmt = $conn->prepare("UPDATE annual_budget SET category_id=?, program_name=?, description=?, allocated_amount=?, fiscal_year=? WHERE id=?");
    $stmt->bind_param("issdsi", $category_id, $program_name, $description, $allocated_amount, $fiscal_year, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=annual_budget&success=updated");
    exit();
}

// Fetch for edit form
$edit_row = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM annual_budget WHERE id=$id");
    $edit_row = $res->fetch_assoc();
}

// Fetch categories for dropdowns
$categories = $conn->query("SELECT id, category_name FROM budget_categories");

// Fetch all categories for table display
$all_categories = $conn->query("SELECT id, category_name FROM budget_categories");

// Fetch recent budgets for auto search suggestions (limit 10)
$recent_budgets = [];
$recent_res = $conn->query("SELECT ab.id, ab.program_name, ab.description, ab.allocated_amount, ab.fiscal_year, bc.category_name FROM annual_budget ab LEFT JOIN budget_categories bc ON ab.category_id = bc.id ORDER BY ab.fiscal_year DESC, ab.id DESC LIMIT 10");
while ($row = $recent_res->fetch_assoc()) {
    $recent_budgets[] = $row;
}
?>

<header class="bg-white shadow p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <h1 class="text-2xl font-bold text-gray-800 mb-2 sm:mb-0">Annual Budget</h1>
    <p class="text-gray-700 font-semibold">Total Available Funds: <span class="text-green-600">$<?= number_format($total_funds, 2) ?></span></p>
</header>
<?php if ($success_message): ?>
    <div class="max-w-2xl mx-auto mt-4 mb-2 p-3 bg-green-100 border border-green-300 text-green-800 rounded text-center text-sm font-semibold shadow">
        <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<main class="flex-grow container mx-auto px-2 py-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-3 sm:space-y-0">
        
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <div id="budgets-container" class="flex-grow space-y-6 min-w-0 overflow-x-auto">
            <div class="flex items-center space-x-2 w-full sm:w-auto relative">
            <input id="search-input" type="search" placeholder="Search budgets by program name..." aria-label="Search budgets" autocomplete="off" class="w-full sm:w-72 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
            <button id="clear-search" class="hidden text-gray-500 hover:text-gray-700 focus:outline-none absolute right-2 top-2.5" aria-label="Clear search">
                <i class="fas fa-times-circle fa-lg"></i>
            </button>
            <!-- Auto Search Suggestions -->
            <ul id="search-suggestions" class="absolute z-50 bg-white border border-gray-300 rounded shadow max-w-sm w-full sm:max-w-md max-h-60 overflow-y-auto mt-10 hidden"></ul>
        </div>
        <div class="flex items-center space-x-2 w-full sm:w-auto">
            <label for="filter-category" class="text-gray-700 font-medium text-sm">Filter by Category:</label>
            <select id="filter-category" class="border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="filter-year" class="text-gray-700 font-medium text-sm ml-4">Filter by Fiscal Year:</label>
            <select id="filter-year" class="border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Years</option>
                <?php
                // Fetch distinct fiscal years from annual_budget
                $years_res = $conn->query("SELECT DISTINCT DATE_FORMAT(fiscal_year, '%Y') as year FROM annual_budget ORDER BY year DESC");
                while ($year_row = $years_res->fetch_assoc()) {
                    $year_val = $year_row['year'];
                    echo "<option value=\"$year_val\">$year_val</option>";
                }
                ?>
            </select>
        </div>
        <div class="flex justify-end w-full sm:w-auto">
            <button id="open-popover" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition text-sm">
                <i class="fas fa-plus mr-1.5"></i> Add New Budget
            </button>
        </div>
            <?php foreach ($all_categories as $cat): ?>
                <?php
                $cat_id = $cat['id'];
                $sql = "SELECT ab.*, bc.category_name FROM annual_budget ab LEFT JOIN budget_categories bc ON ab.category_id = bc.id WHERE ab.category_id = $cat_id ORDER BY ab.fiscal_year DESC, ab.id DESC";
                $result = $conn->query($sql);

                // Calculate total allocated amount for this category
                $total_sql = "SELECT SUM(allocated_amount) as total_allocated FROM annual_budget WHERE category_id = $cat_id";
                $total_result = $conn->query($total_sql);
                $total_allocated = 0;
                if ($total_row = $total_result->fetch_assoc()) {
                    $total_allocated = $total_row['total_allocated'];
                }

                if ($result->num_rows > 0): ?>
                    <section data-category-id="<?= $cat_id ?>" class="category-section bg-white overflow-x-auto rounded shadow">
                        <h3 class="text-base font-semibold bg-gray-100 px-4 py-2 border-b border-gray-200 text-left text-gray-800"><?= htmlspecialchars($cat['category_name']) ?></h3>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider w-10">ID</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Program Name</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider min-w-[260px]">Description</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider w-28">Allocated Amount</th>
                                    <th scope="col" class="px-3 py-2 text-center font-medium text-gray-500 uppercase tracking-wider w-32">Fiscal Year</th>
                                    <th scope="col" class="px-3 py-2 text-center font-medium text-gray-500 uppercase tracking-wider w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr data-program-name="<?= htmlspecialchars(strtolower($row['program_name'])) ?>" data-fiscal-year="<?= date('Y', strtotime($row['fiscal_year'])) ?>">
                                        <td class="px-3 py-1 whitespace-nowrap text-gray-700 font-mono"><?= $row['id'] ?></td>
                                        <td class="px-3 py-1 whitespace-nowrap text-gray-800 font-semibold"><?= htmlspecialchars($row['program_name']) ?></td>
                                        <td class="px-3 py-1 whitespace-nowrap text-gray-600"><?= htmlspecialchars($row['description']) ?></td>
                                        <td class="px-3 py-1 whitespace-nowrap text-right text-green-600 font-semibold">$<?= number_format($row['allocated_amount'], 2) ?></td>
                                        <td class="px-3 py-1 whitespace-nowrap text-center text-gray-700 font-mono"><?= date('Y-m-d H:i', strtotime($row['fiscal_year'])) ?></td>
                                        <td class="px-3 py-1 whitespace-nowrap text-center space-x-1">
                                            <button type="button" class="edit-btn text-blue-600 hover:text-blue-800 focus:outline-none" data-id="<?= $row['id'] ?>" aria-label="Edit budget <?= htmlspecialchars($row['program_name']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="delete-btn text-red-600 hover:text-red-800 focus:outline-none" data-id="<?= $row['id'] ?>" aria-label="Delete budget <?= htmlspecialchars($row['program_name']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="bg-gray-100 font-semibold text-gray-800">
                                    <td colspan="3" class="px-3 py-1 text-right">Total Allocated Amount:</td>
                                    <td class="px-3 py-1 text-right text-green-700">$<?= number_format($total_allocated, 2) ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <aside class="w-full lg:w-80 bg-white rounded shadow p-4 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto" aria-label="Recent Budgets">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Recent Budgets</h2>
            <?php if (count($recent_budgets) === 0): ?>
                <p class="text-gray-600 text-sm">No recent budgets available.</p>
            <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($recent_budgets as $budget): ?>
                        <li tabindex="0" class="flex flex-col sm:flex-row sm:items-center justify-between py-2 px-3 hover:bg-blue-50 focus:bg-blue-100 rounded cursor-pointer" data-id="<?= $budget['id'] ?>">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 w-full sm:w-auto">
                                <span class="font-semibold text-blue-700 text-sm sm:text-base"><?= htmlspecialchars($budget['program_name']) ?></span><br>
                                <span class="text-gray-600 text-xs sm:text-sm mt-1 sm:mt-0"><?= htmlspecialchars($budget['category_name']) ?></span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-6 mt-1 sm:mt-0 text-gray-700 text-xs sm:text-sm w-full sm:w-auto">
                                <span class="whitespace-nowrap">Allocated: <span class="font-semibold text-green-600">$<?= number_format($budget['allocated_amount'], 2) ?></span></span>
        
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>
    </div>

</main>

<!-- Add Budget Popover -->
<div id="add-popover-backdrop" class="fixed inset-0 bg-black bg-opacity-30 hidden z-40"></div>
<form method="post" action="" id="add-budget-popover" class="fixed top-1/2 left-1/2 max-w-lg w-full bg-white rounded-lg shadow-lg p-5 z-50 -translate-x-1/2 -translate-y-1/2 hidden" novalidate>
    <h2 class="text-lg font-semibold mb-3 text-gray-800 flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Add New Budget</span>
    </h2>
    <div class="mb-3">
        <label for="add_category_id" class="block text-gray-700 font-medium mb-1 text-sm">Category <span class="text-red-500">*</span></label>
        <select id="add_category_id" name="category_id" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            <option value="">-- Select --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="add_program_name" class="block text-gray-700 font-medium mb-1 text-sm">Program Name <span class="text-red-500">*</span></label>
        <input id="add_program_name" type="text" name="program_name" value="" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-3">
        <label for="add_description" class="block text-gray-700 font-medium mb-1 text-sm">Description</label>
        <input id="add_description" type="text" name="description" value="" class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-3">
        <label for="add_allocated_amount" class="block text-gray-700 font-medium mb-1 text-sm">Allocated Amount <span class="text-red-500">*</span></label>
        <input id="add_allocated_amount" type="number" step="0.01" min="0" name="allocated_amount" value="" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-4">
        <label for="add_fiscal_year" class="block text-gray-700 font-medium mb-1 text-sm">Fiscal Year <span class="text-red-500">*</span></label>
        <input id="add_fiscal_year" type="datetime-local" name="fiscal_year" value="" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="flex justify-end space-x-2">
        <button type="submit" name="add" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">Add</button>
        <button type="button" id="close-add-popover" class="px-4 py-1.5 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm">Cancel</button>
    </div>
</form>

<!-- Edit Budget Popover -->
<div id="edit-popover-backdrop" class="fixed inset-0 bg-black bg-opacity-30 hidden z-40"></div>
<form method="post" action="dashboard.php?page=annual_budget" id="edit-budget-popover" class="fixed top-1/2 left-1/2 max-w-lg w-full bg-white rounded-lg shadow-lg p-5 z-50 -translate-x-1/2 -translate-y-1/2 hidden" novalidate>
    <?php if ($edit_row): ?>
    <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
    <?php endif; ?>
    <h2 class="text-lg font-semibold mb-3 text-gray-800 flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit Budget</span>
    </h2>
    <div class="mb-3">
        <label for="edit_category_id" class="block text-gray-700 font-medium mb-1 text-sm">Category <span class="text-red-500">*</span></label>
        <select id="edit_category_id" name="category_id" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            <option value="">-- Select --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $edit_row && $edit_row['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="edit_program_name" class="block text-gray-700 font-medium mb-1 text-sm">Program Name <span class="text-red-500">*</span></label>
        <input id="edit_program_name" type="text" name="program_name" value="<?= $edit_row ? htmlspecialchars($edit_row['program_name']) : '' ?>" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-3">
        <label for="edit_description" class="block text-gray-700 font-medium mb-1 text-sm">Description</label>
        <input id="edit_description" type="text" name="description" value="<?= $edit_row ? htmlspecialchars($edit_row['description']) : '' ?>" class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-3">
        <label for="edit_allocated_amount" class="block text-gray-700 font-medium mb-1 text-sm">Allocated Amount <span class="text-red-500">*</span></label>
        <input id="edit_allocated_amount" type="number" step="0.01" min="0" name="allocated_amount" value="<?= $edit_row ? $edit_row['allocated_amount'] : '' ?>" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="mb-4">
        <label for="edit_fiscal_year" class="block text-gray-700 font-medium mb-1 text-sm">Fiscal Year <span class="text-red-500">*</span></label>
        <input id="edit_fiscal_year" type="datetime-local" name="fiscal_year" value="<?= $edit_row ? date('Y-m-d\TH:i', strtotime($edit_row['fiscal_year'])) : '' ?>" required class="w-full border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" />
    </div>
    <div class="flex justify-end space-x-2">
        <button type="submit" name="update" class="px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-green-500 transition text-sm">Update</button>
        <button type="button" id="close-edit-popover" class="px-4 py-1.5 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm">Cancel</button>
    </div>
</form>

<!-- Delete Confirmation Popover -->
<div id="delete-popover-backdrop" class="fixed inset-0 bg-black bg-opacity-30 hidden z-40"></div>
<div id="delete-popover" class="fixed top-1/2 left-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg p-5 z-50 -translate-x-1/2 -translate-y-1/2 hidden" role="dialog" aria-modal="true" aria-labelledby="delete-popover-title" aria-describedby="delete-popover-desc">
    <h2 id="delete-popover-title" class="text-lg font-semibold text-gray-800 mb-4">Confirm Deletion</h2>
    <p id="delete-popover-desc" class="mb-4 text-gray-700">Are you sure you want to delete this budget?</p>
    <form method="get" action="dashboard.php?page=annual_budget" class="flex justify-end space-x-2">
        <input type="hidden" name="delete" id="delete-id-input" />
        <button type="submit" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-red-500 transition text-sm">Yes, Delete</button>
        <button type="button" id="cancel-delete" class="px-4 py-1.5 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm">Cancel</button>
    </form>
</div>

<script>
    // Popover open/close logic
    const openBtn = document.getElementById('open-popover');
    const addPopover = document.getElementById('add-budget-popover');
    const addBackdrop = document.getElementById('add-popover-backdrop');
    const closeAddBtn = document.getElementById('close-add-popover');

    function openAddPopover() {
        addPopover.classList.remove('hidden');
        addBackdrop.classList.remove('hidden');
        const firstInput = addPopover.querySelector('select, input, textarea, button');
        if (firstInput) firstInput.focus();
    }
    function closeAddPopover() {
        addPopover.classList.add('hidden');
        addBackdrop.classList.add('hidden');
    }
    if (openBtn) openBtn.addEventListener('click', openAddPopover);
    if (closeAddBtn) closeAddBtn.addEventListener('click', closeAddPopover);
    if (addBackdrop) addBackdrop.addEventListener('click', closeAddPopover);

    const editPopover = document.getElementById('edit-budget-popover');
    const editBackdrop = document.getElementById('edit-popover-backdrop');
    const closeEditBtn = document.getElementById('close-edit-popover');
    const editBtns = document.querySelectorAll('.edit-btn');

    function openEditPopover() {
        editPopover.classList.remove('hidden');
        editBackdrop.classList.remove('hidden');
        const firstInput = editPopover.querySelector('select, input, textarea, button');
        if (firstInput) firstInput.focus();
    }
    function closeEditPopover() {
        editPopover.classList.add('hidden');
        editBackdrop.classList.add('hidden');
    }
    if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditPopover);
    if (editBackdrop) editBackdrop.addEventListener('click', closeEditPopover);

    <?php if ($edit_row): ?>
    openEditPopover();
    <?php endif; ?>

    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Always keep ?page=annual_budget in the URL
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'annual_budget');
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
            // Always keep ?page=annual_budget in the URL for delete form
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'annual_budget');
            deleteForm.action = url.pathname + '?' + url.searchParams.toString();
            deletePopover.classList.remove('hidden');
            deleteBackdrop.classList.remove('hidden');
            cancelDeleteBtn.focus();
        });
    });
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', function() {
        deletePopover.classList.add('hidden');
        deleteBackdrop.classList.add('hidden');
    });
    if (deleteBackdrop) deleteBackdrop.addEventListener('click', function() {
        deletePopover.classList.add('hidden');
        deleteBackdrop.classList.add('hidden');
    });

    // Auto Search Suggestions
    const searchInput = document.getElementById('search-input');
    const suggestionsBox = document.getElementById('search-suggestions');
    const clearSearchBtn = document.getElementById('clear-search');

    // Recent budgets from PHP
    const recentBudgets = <?php echo json_encode($recent_budgets); ?>;

    function createSuggestionItem(budget) {
        const li = document.createElement('li');
        li.tabIndex = 0;
        li.className = "px-3 py-2 cursor-pointer hover:bg-blue-100 focus:bg-blue-100 text-gray-800 text-sm";
        li.textContent = budget.program_name;
        li.dataset.id = budget.id;
        li.addEventListener('click', () => {
            window.location.href = '?edit=' + budget.id;
        });
        li.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = '?edit=' + budget.id;
            }
        });
        return li;
    }

    function showSuggestions(filtered) {
        suggestionsBox.innerHTML = '';
        if (filtered.length === 0) {
            const noResult = document.createElement('li');
            noResult.className = "px-3 py-2 text-gray-500 text-sm";
            noResult.textContent = "No matching budgets found.";
            suggestionsBox.appendChild(noResult);
        } else {
            filtered.forEach(budget => {
                suggestionsBox.appendChild(createSuggestionItem(budget));
            });
        }
        suggestionsBox.classList.remove('hidden');
    }

    function hideSuggestions() {
        suggestionsBox.classList.add('hidden');
    }

    searchInput.addEventListener('input', () => {
        const val = searchInput.value.trim().toLowerCase();
        if (val.length === 0) {
            clearSearchBtn.classList.add('hidden');
            hideSuggestions();
            filterBudgets('', '', '');
            return;
        }
        clearSearchBtn.classList.remove('hidden');
        // Filter recent budgets for suggestions
        const filteredSuggestions = recentBudgets.filter(b => b.program_name.toLowerCase().includes(val));
        showSuggestions(filteredSuggestions);
        // Also filter the table by program name live
        filterBudgets(val, document.getElementById('filter-category').value, document.getElementById('filter-year').value);
    });

    clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
        hideSuggestions();
        filterBudgets('', document.getElementById('filter-category').value, document.getElementById('filter-year').value);
        searchInput.focus();
    });

    // Hide suggestions on click outside
    document.addEventListener('click', (e) => {
        if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
            hideSuggestions();
        }
    });

    // Filter budgets by category and fiscal year
    const filterCategory = document.getElementById('filter-category');
    const filterYear = document.getElementById('filter-year');

    filterCategory.addEventListener('change', () => {
        filterBudgets(searchInput.value.trim().toLowerCase(), filterCategory.value, filterYear.value);
    });
    filterYear.addEventListener('change', () => {
        filterBudgets(searchInput.value.trim().toLowerCase(), filterCategory.value, filterYear.value);
    });

    function filterBudgets(searchTerm, categoryId, fiscalYear) {
        const container = document.getElementById('budgets-container');
        const categorySections = container.querySelectorAll('.category-section');

        categorySections.forEach(section => {
            const sectionCategoryId = section.getAttribute('data-category-id');
            // Show/hide category section based on category filter
            if (categoryId && categoryId !== sectionCategoryId) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = '';
            }

            const rows = section.querySelectorAll('tbody tr');
            let anyRowVisible = false;

            rows.forEach(row => {
                // Skip total row (has colspan)
                if (row.querySelector('td[colspan]')) {
                    return;
                }
                const programName = row.getAttribute('data-program-name') || '';
                const rowFiscalYear = row.getAttribute('data-fiscal-year') || '';

                // Check search term match
                const matchesSearch = searchTerm === '' || programName.includes(searchTerm);
                // Check fiscal year match
                const matchesYear = fiscalYear === '' || rowFiscalYear === fiscalYear;

                if (matchesSearch && matchesYear) {
                    row.style.display = '';
                    anyRowVisible = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide total row based on if any rows visible
            const totalRow = section.querySelector('tbody tr.bg-gray-100');
            if (totalRow) {
                totalRow.style.display = anyRowVisible ? '' : 'none';
            }

            // If no rows visible, hide entire section
            if (!anyRowVisible) {
                section.style.display = 'none';
            }
        });
    }

    // Initialize filter on page load (in case URL params or default)
    filterBudgets('', '', '');

    // Recent Budgets click to edit
    const recentList = document.querySelectorAll('aside[aria-label="Recent Budgets"] ul li');
    recentList.forEach(item => {
        item.addEventListener('click', () => {
            const id = item.getAttribute('data-id');
            if (id) {
                window.location.href = '?edit=' + id;
            }
        });
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const id = item.getAttribute('data-id');
                if (id) {
                    window.location.href = '?edit=' + id;
                }
            }
        });
    });
</script>

</body>
</html>