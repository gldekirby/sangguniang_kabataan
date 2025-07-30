<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Annual Budget
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
            font-family: 'Inter', sans-serif;
        }
        /* Scrollbar styling */
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
        /* Recent budgets list styling without stairs effect */
        .recent-budgets-list li {
            cursor: pointer;
            border-radius: 0.375rem; /* rounded-md */
            background-color: white;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            outline-offset: 2px;
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .recent-budgets-list li:hover,
        .recent-budgets-list li:focus {
            box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
            background-color: #ebf8ff; /* blue-100 */
            outline: none;
        }
        /* Responsive fixes for table */
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb; /* Tailwind gray-200 */
            word-break: break-word;
        }
        th {
            background-color: #f9fafb; /* Tailwind gray-50 */
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #4b5563; /* Tailwind gray-600 */
        }
        /* Fix for action buttons alignment */
        .actions-cell {
            text-align: center;
            white-space: nowrap;
        }
        /* Responsive layout adjustments */
        @media (max-width: 1024px) {
            main > div {
                flex-direction: column;
            }
            aside[aria-label="Recent Budgets"] {
                position: relative;
                top: auto;
                height: auto;
                max-height: none;
                margin-top: 1.5rem;
                width: 100% !important;
            }
        }
        /* Improved No Budget Found styling */
        .no-budget-row {
            background-color: #f3f4f6; /* Tailwind gray-100 */
            font-weight: 600;
            color: #6b7280; /* Tailwind gray-500 */
            font-size: 1rem;
            padding: 2rem 1rem;
            text-align: center;
            user-select: none;
        }
        /* Remove scrollbar from no-budget-row */
        .no-budget-row td {
            overflow: visible !important;
        }
  </style>
 </head>
 <body class="bg-gray-50 min-h-screen flex flex-col">
  <?php
$conn = new mysqli("localhost", "root", "", "youth_sk");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch total funds
$total_funds = 0;
$res = $conn->query("SELECT SUM(amount) as total_funds FROM fund_sources");
if ($row = $res->fetch_assoc()) {
    $total_funds = $row['total_funds'];
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
    header("Location: dashboard.php?page=annual_budget");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM annual_budget WHERE id=$id");
    header("Location: dashboard.php?page=annual_budget");
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
    header("Location: dashboard.php?page=annual_budget");
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
  <header class="bg-white shadow p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between sticky top-0 z-30">
   <h1 class="text-3xl font-extrabold text-gray-900 mb-3 sm:mb-0">
    Annual Budget
   </h1>
  </header>
  <main class="flex-grow w-full mx-auto px-4 sm:px-6 lg:px-8 p-6 overflow-y-auto">
   <div class="flex flex-col lg:flex-row gap-6">
    <!-- Left: Search, Filter, Add Button, and Table -->
    <section class="flex flex-col flex-grow">
     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
      <div class="relative w-full sm:w-80">
       <input aria-label="Search budgets" autocomplete="off" class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 text-base shadow-sm" id="search-input" placeholder="Search budgets by program name..." type="search"/>
       <button aria-label="Clear search" class="hidden absolute right-3 top-3 text-gray-400 hover:text-gray-700 focus:outline-none" id="clear-search">
        <i class="fas fa-times-circle fa-lg">
        </i>
       </button>
       <ul class="absolute z-50 bg-white border border-gray-300 rounded-md shadow-lg max-w-sm w-full max-h-60 overflow-y-auto mt-12 hidden" id="search-suggestions">
       </ul>
      </div>
      <div class="flex flex-wrap items-center gap-4">
       <select class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="filter-category">
        <option value="">
         All Categories
        </option>
        <?php foreach ($categories as $c): ?>
        <option value="&lt;?= $c['id'] ?>">
         <?= htmlspecialchars($c['category_name']) ?>
        </option>
        <?php endforeach; ?>
       </select>
       <select class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="filter-year">
        <option value="">
         All Years
        </option>
        <?php
            $years_res = $conn->query("SELECT DISTINCT DATE_FORMAT(fiscal_year, '%Y') as year FROM annual_budget ORDER BY year DESC");
                while ($year_row = $years_res->fetch_assoc()) {
                    $year_val = $year_row['year'];
                        echo "<option value='\"$year_val\"'>$year_val</option>";
            }
        ?>
       </select>
      </div>
      <div class="flex justify-end w-full sm:w-auto">
       <button class="inline-flex items-center px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-1 transition text-base" id="open-popover">
        <i class="fas fa-plus mr-2">
        </i>
        Add New Budget
       </button>
      </div>
     </div>
     <div class="space-y-8 min-w-0" id="budgets-container">
      <?php foreach ($all_categories as $cat): ?>
      <?php
                    $cat_id = $cat['id'];
                    $sql = "SELECT ab.*, bc.category_name FROM annual_budget ab LEFT JOIN budget_categories bc ON ab.category_id = bc.id WHERE ab.category_id = $cat_id ORDER BY ab.fiscal_year DESC, ab.id DESC";
                    $result = $conn->
      query($sql);

                    $total_sql = "SELECT SUM(allocated_amount) as total_allocated FROM annual_budget WHERE category_id = $cat_id";
                    $total_result = $conn->query($total_sql);
                    $total_allocated = 0;
                    if ($total_row = $total_result->fetch_assoc()) {
                        $total_allocated = $total_row['total_allocated'];
                    }

                    if ($result->num_rows > 0): ?>
      <section class="category-section rounded-lg shadow-md border border-gray-200 w-full mx-auto" data-category-id="&lt;?= $cat_id ?>">
       <div class="mx-auto w-full overflow-x-auto">
        <h3 class="text-lg font-semibold bg-gray-100 px-6 py-3 border-b border-gray-300 text-left text-gray-900">
         <?= htmlspecialchars($cat['category_name']) ?>
        </h3>
        <table class="w-full mx-auto text-sm">
         <thead>
          <tr>
           <th class="w-12" scope="col">
            ID
           </th>
           <th class="min-w-[140px] text-left" scope="col">
            Program Name
           </th>
           <th class="min-w-[280px] text-left" scope="col">
            Description
           </th>
           <th class="w-36 text-right" scope="col">
            Allocated Amount
           </th>
           <th class="w-36 text-center" scope="col">
            Fiscal Year
           </th>
           <th class="w-28 text-center" scope="col">
            Actions
           </th>
          </tr>
         </thead>
         <tbody>
          <?php while($row = $result->
          fetch_assoc()): ?>
          <tr class="hover:bg-blue-50 transition-colors" data-fiscal-year="&lt;?= date('Y', strtotime($row['fiscal_year'])) ?>" data-program-name="&lt;?= htmlspecialchars(strtolower($row['program_name'])) ?>">
           <td class="text-gray-700 font-mono text-center">
            <?= $row['id'] ?>
           </td>
           <td class="text-gray-900 font-semibold">
            <?= htmlspecialchars($row['program_name']) ?>
           </td>
           <td class="text-gray-600">
            <?= htmlspecialchars($row['description']) ?>
           </td>
           <td class="text-green-600 font-semibold text-right">
            ₱
            <?= number_format($row['allocated_amount'], 2) ?>
           </td>
           <td class="text-gray-700 font-mono text-center">
            <?= date('Y-m-d H:i', strtotime($row['fiscal_year'])) ?>
           </td>
           <td class="actions-cell space-x-3">
            <button aria-label="Edit budget &lt;?= htmlspecialchars($row['program_name']) ?>" class="edit-btn text-blue-600 hover:text-blue-800 focus:outline-none" data-id="&lt;?= $row['id'] ?>" type="button">
             <i class="fas fa-edit fa-lg">
             </i>
            </button>
            <button aria-label="Delete budget &lt;?= htmlspecialchars($row['program_name']) ?>" class="hidden delete-btn text-red-600 hover:text-red-800 focus:outline-none" data-id="&lt;?= $row['id'] ?>" type="button">
             <i class="fas fa-trash-alt fa-lg">
             </i>
            </button>
           </td>
          </tr>
          <?php endwhile; ?>
          <tr class="no-budget-row hidden">
           <td class="text-center py-6" colspan="6">
            <img alt="Illustration of a sad chart with no data, symbolizing no budget found" class="mx-auto mb-4" height="80" src="https://storage.googleapis.com/a1aa/image/04e6f885-7b67-4d27-27c8-d20486d90b97.jpg" width="80"/>
            No budget found.
           </td>
          </tr>
          <tr class="bg-gray-100 font-semibold text-gray-800 hidden">
           <td class="text-right pr-4" colspan="3">
            Total Allocated Amount:
           </td>
           <td class="text-green-700 text-right">
            ₱
            <?= number_format($total_allocated, 2) ?>
           </td>
           <td colspan="2">
           </td>
          </tr>
         </tbody>
        </table>
       </div>
      </section>
      <?php else: ?>
      <section class="category-section rounded-lg shadow-md border border-gray-200 w-full mx-auto" data-category-id="&lt;?= $cat_id ?>">
       <div class="mx-auto w-full overflow-x-auto">
        <h3 class="text-lg font-semibold bg-gray-100 px-6 py-3 border-b border-gray-300 text-left text-gray-900">
         <?= htmlspecialchars($cat['category_name']) ?>
        </h3>
        <table class="w-full mx-auto text-sm">
         <tbody>
          <tr class="no-budget-row">
           <td class="text-center py-12" colspan="6">
            <img alt="Illustration of a sad chart with no data, symbolizing no budget found" class="mx-auto mb-6" height="100" src="https://storage.googleapis.com/a1aa/image/04e6f885-7b67-4d27-27c8-d20486d90b97.jpg" width="100"/>
            <p class="text-gray-500 text-lg font-semibold select-none">
             No budget found.
            </p>
           </td>
          </tr>
         </tbody>
        </table>
       </div>
      </section>
      <?php endif; ?>
      <?php endforeach; ?>
     </div>
    </section>
   </div>
  </main>
  <!-- Add Budget Popover -->
  <div class="fixed inset-0 bg-black bg-opacity-30 hidden z-40" id="add-popover-backdrop">
  </div>
  <form action="" class="fixed top-1/2 left-1/2 max-w-lg w-full bg-white rounded-lg shadow-lg p-6 z-50 -translate-x-1/2 -translate-y-1/2 hidden" id="add-budget-popover" method="post" novalidate="">
   <h2 class="text-xl font-semibold mb-5 text-gray-900 flex items-center space-x-3">
    <i class="fas fa-plus text-blue-600">
    </i>
    <span>
     Add New Budget
    </span>
   </h2>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="add_category_id">
     Category
     <span class="text-red-600">
      *
     </span>
    </label>
    <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="add_category_id" name="category_id" required="">
     <option value="">
      -- Select --
     </option>
     <?php foreach ($categories as $c): ?>
     <option value="&lt;?= $c['id'] ?>">
      <?= htmlspecialchars($c['category_name']) ?>
     </option>
     <?php endforeach; ?>
    </select>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="add_program_name">
     Program Name
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="add_program_name" name="program_name" required="" type="text" value=""/>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="add_description">
     Description
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="add_description" name="description" type="text" value=""/>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="add_allocated_amount">
     Allocated Amount
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="add_allocated_amount" min="0" name="allocated_amount" required="" step="0.01" type="number" value=""/>
   </div>
   <div class="mb-6">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="add_fiscal_year">
     Fiscal Year
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="add_fiscal_year" name="fiscal_year" required="" type="datetime-local" value=""/>
   </div>
   <div class="flex justify-end space-x-3">
    <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600 transition text-sm" name="add" type="submit">
     Add
    </button>
    <button class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-md font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" id="close-add-popover" type="button">
     Cancel
    </button>
   </div>
  </form>
  <!-- Edit Budget Popover -->
  <div class="fixed inset-0 bg-black bg-opacity-30 hidden z-40" id="edit-popover-backdrop">
  </div>
  <form action="dashboard.php?page=annual_budget" class="fixed top-1/2 left-1/2 max-w-lg w-full bg-white rounded-lg shadow-lg p-6 z-50 -translate-x-1/2 -translate-y-1/2 hidden" id="edit-budget-popover" method="post" novalidate="">
   <?php if ($edit_row): ?>
   <input name="id" type="hidden" value="&lt;?= $edit_row['id'] ?>"/>
   <?php endif; ?>
   <h2 class="text-xl font-semibold mb-5 text-gray-900 flex items-center space-x-3">
    <i class="fas fa-edit text-green-600">
    </i>
    <span>
     Edit Budget
    </span>
   </h2>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="edit_category_id">
     Category
     <span class="text-red-600">
      *
     </span>
    </label>
    <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="edit_category_id" name="category_id" required="">
     <option value="">
      -- Select --
     </option>
     <?php foreach ($categories as $c): ?>
     <option value="<?= $c['id'] ?>" <?= ($edit_row && $edit_row['category_id'] == $c['id']) ? 'selected' : '' ?>>
      <?= htmlspecialchars($c['category_name']) ?>
     </option>
     <?php endforeach; ?>
    </select>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="edit_program_name">
     Program Name
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="edit_program_name" name="program_name" required="" type="text" value="&lt;?= $edit_row ? htmlspecialchars($edit_row['program_name']) : '' ?>">
    </input>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="edit_description">
     Description
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="edit_description" name="description" type="text" value="&lt;?= $edit_row ? htmlspecialchars($edit_row['description']) : '' ?>"/>
   </div>
   <div class="mb-4">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="edit_allocated_amount">
     Allocated Amount
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="edit_allocated_amount" min="0" name="allocated_amount" required="" step="0.01" type="number" value="&lt;?= $edit_row ? $edit_row['allocated_amount'] : '' ?>"/>
   </div>
   <div class="mb-6">
    <label class="block text-gray-800 font-semibold mb-2 text-sm" for="edit_fiscal_year">
     Fiscal Year
     <span class="text-red-600">
      *
     </span>
    </label>
    <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm shadow-sm" id="edit_fiscal_year" name="fiscal_year" required="" type="datetime-local" value="&lt;?= $edit_row ? date('Y-m-d\TH:i', strtotime($edit_row['fiscal_year'])) : '' ?>"/>
   </div>
   <div class="flex justify-end space-x-3">
    <button class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-green-600 transition text-sm" name="update" type="submit">
     Update
    </button>
    <button class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-md font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" id="close-edit-popover" type="button">
     Cancel
    </button>
   </div>
  </form>
  <!-- Delete Confirmation Popover -->
  <div class="fixed inset-0 bg-black bg-opacity-30 hidden z-40" id="delete-popover-backdrop">
  </div>
  <div aria-describedby="delete-popover-desc" aria-labelledby="delete-popover-title" aria-modal="true" class="fixed top-1/2 left-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg p-6 z-50 -translate-x-1/2 -translate-y-1/2 hidden" id="delete-popover" role="dialog">
   <h2 class="text-xl font-semibold text-gray-900 mb-5" id="delete-popover-title">
    Confirm Deletion
   </h2>
   <p class="mb-6 text-gray-700" id="delete-popover-desc">
    Are you sure you want to delete this budget?
   </p>
   <form action="dashboard.php?page=annual_budget" class="flex justify-end space-x-4" method="get">
    <input id="delete-id-input" name="delete" type="hidden"/>
    <button class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-semibold focus:outline-none focus:ring-2 focus:ring-red-600 transition text-sm" type="submit">
     Yes, Delete
    </button>
    <button class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-md font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" id="cancel-delete" type="button">
     Cancel
    </button>
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
    const deleteForm = document.querySelector('#delete-popover form');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteIdInput.value = this.getAttribute('data-id');
            // Always set the action to dashboard.php?page=annual_budget
            deleteForm.action = 'dashboard.php?page=annual_budget';
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

    const recentBudgets = <?php echo json_encode($recent_budgets); ?>;

    function createSuggestionItem(budget) {
        const li = document.createElement('li');
        li.tabIndex = 0;
        li.className = "px-4 py-2 cursor-pointer hover:bg-blue-100 focus:bg-blue-100 text-gray-900 text-sm";
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
            noResult.className = "px-4 py-2 text-gray-500 text-sm";
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
        const filteredSuggestions = recentBudgets.filter(b => b.program_name.toLowerCase().includes(val));
        showSuggestions(filteredSuggestions);
        filterBudgets(val, document.getElementById('filter-category').value, document.getElementById('filter-year').value);
    });

    clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
        hideSuggestions();
        filterBudgets('', document.getElementById('filter-category').value, document.getElementById('filter-year').value);
        searchInput.focus();
    });

    document.addEventListener('click', (e) => {
        if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
            hideSuggestions();
        }
    });

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
            if (categoryId && categoryId !== sectionCategoryId) {
                section.style.display = 'none';
                return;
            } else {
                section.style.display = '';
            }

            const rows = section.querySelectorAll('tbody tr');
            let anyRowVisible = false;
            let dataRowCount = 0;
            let visibleDataRowCount = 0;
            let totalRow = null;
            let noBudgetRow = null;

            rows.forEach(row => {
                if (row.classList.contains('bg-gray-100')) {
                    totalRow = row;
                    return;
                }
                if (row.classList.contains('no-budget-row')) {
                    noBudgetRow = row;
                    return;
                }
                dataRowCount++;
                const programName = row.getAttribute('data-program-name') || '';
                const rowFiscalYear = row.getAttribute('data-fiscal-year') || '';

                const matchesSearch = searchTerm === '' || programName.includes(searchTerm);
                const matchesYear = fiscalYear === '' || rowFiscalYear === fiscalYear;

                if (matchesSearch && matchesYear) {
                    row.style.display = '';
                    anyRowVisible = true;
                    visibleDataRowCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (totalRow) {
                totalRow.style.display = anyRowVisible ? '' : 'none';
            }
            if (noBudgetRow) {
                // Show if there are no visible data rows and the section is visible
                noBudgetRow.style.display = (anyRowVisible ? 'none' : '');
            }

            if (!anyRowVisible) {
                section.style.display = 'none';
            }
        });
    }

    filterBudgets('', '', '');

    // Recent budgets click and keyboard navigation
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
