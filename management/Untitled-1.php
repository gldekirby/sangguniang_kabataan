<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Fund Sources
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .popover-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            z-index: 1000;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 28rem;
        }
        .popover-form.active {
            display: block;
        }
        .popover-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 999;
        }
        .popover-backdrop.active {
            display: block;
        }
  </style>
 </head>
 <body class="min-h-screen flex flex-col items-center p-4">
  <?php
$conn = new mysqli("localhost", "root", "", "youth_sk");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $stmt = $conn->prepare("INSERT INTO fund_sources (name, description, amount) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $name, $description, $amount);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard1.php?page=funds");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM fund_sources WHERE id=$id");
    header("Location: dashboard1.php?page=funds");
    exit();
}

// Handle Edit
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $stmt = $conn->prepare("UPDATE fund_sources SET name=?, description=?, amount=? WHERE id=?");
    $stmt->bind_param("ssdi", $name, $description, $amount, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard1.php?page=funds");
    exit();
}

// Fetch for edit form
$edit_row = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM fund_sources WHERE id=$id");
    $edit_row = $res->fetch_assoc();
}

// Pagination variables
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build where clause
$whereClauses = [];
if ($search !== '') {
    $whereClauses[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
}
$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Get total count for pagination
$countResult = $conn->query("SELECT COUNT(*) as total FROM fund_sources $whereSQL");
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

// Fetch data with filters and pagination
$sql = "SELECT * FROM fund_sources $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);

// Calculate total amount displayed
$totalAmount = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalAmount += $row['amount'];
    }
    // Re-run query to reset pointer for display
    $result = $conn->query($sql);
}

// Fetch recent 5 fund sources for sidebar
$recentResult = $conn->query("SELECT * FROM fund_sources ORDER BY created_at DESC LIMIT 5");
?>
  <div class="w-full h-full mx-auto bg-gray-300 rounded-lg shadow p-6">
    <header class="w-full mb-2 p-1 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
   <button class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-semibold" id="open-popover">
    <i class="fas fa-plus mr-2"></i>
    Add New Fund Source
   </button>
  </header>
  <div class="w-full flex flex-col lg:flex-row space-y-6 lg:space-y-0 lg:space-x-6">
   <main class="flex-grow bg-white rounded-lg shadow p-4 overflow-x-auto">
    <!-- Search and Filters -->
    <form action="funds.php" class="mb-6 flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-4 sm:space-y-0" method="get">
     <input aria-label="Search fund sources by name or description" class="flex-grow border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="search" placeholder="Search by name or description" type="text" value="<?= htmlspecialchars($search) ?>"/>
     <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm flex items-center justify-center" type="submit">
      <i class="fas fa-filter mr-2"></i>
      Filter
     </button>
     <a aria-label="Clear filters and search" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" href="funds.php">
      <i class="fas fa-times mr-2"></i>
      Clear
     </a>
    </form>
    <table aria-label="Fund sources table" class="w-full border border-gray-300 rounded-lg text-sm" role="table">
     <thead class="bg-gray-50">
      <tr>
       <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b border-gray-200 w-12" scope="col">ID</th>
       <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b border-gray-200" scope="col">Name</th>
       <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b border-gray-200" scope="col">Description</th>
       <th class="px-4 py-2 text-right font-semibold text-gray-600 border-b border-gray-200 w-28" scope="col">Amount</th>
       <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b border-gray-200 w-40" scope="col">Created At</th>
       <th class="px-4 py-2 text-center font-semibold text-gray-600 border-b border-gray-200 w-32" scope="col">Actions</th>
      </tr>
     </thead>
     <tbody>
      <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr class="border-b border-gray-200 hover:bg-gray-50">
       <td class="px-4 py-2 font-mono text-gray-700"><?= $row['id'] ?></td>
       <td class="px-4 py-2 font-semibold text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
       <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($row['description']) ?></td>
       <td class="px-4 py-2 text-right text-green-600 font-semibold">$
        <?= number_format($row['amount'], 2) ?></td>
       <td class="px-4 py-2 font-mono text-gray-700"><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
       <td class="px-4 py-2 text-center space-x-2">
        <button aria-label="Edit fund source <?= htmlspecialchars($row['name']) ?>" class="edit-btn text-blue-600 hover:text-blue-800 focus:outline-none" data-id="<?= $row['id'] ?>" type="button">
         <i class="fas fa-edit"></i>
        </button>
        <button aria-label="Delete fund source <?= htmlspecialchars($row['name']) ?>" class="delete-btn text-red-600 hover:text-red-800 focus:outline-none" data-id="<?= $row['id'] ?>" type="button">
         <i class="fas fa-trash-alt"></i>
        </button>
       </td>
      </tr>
      <?php endwhile; ?>
      <tr class="bg-gray-100 font-semibold text-gray-900">
       <td class="px-4 py-3 text-right" colspan="3">Total Amount on Page:</td>
       <td class="px-4 py-3 text-right text-green-700">$
        <?= number_format($totalAmount, 2) ?></td>
       <td colspan="2"></td>
      </tr>
      <?php else: ?>
      <tr>
       <td class="px-4 py-6 text-center text-gray-500" colspan="6">No fund sources found.</td>
      </tr>
      <?php endif; ?>
     </tbody>
    </table>
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Pagination" class="mt-6 flex justify-center space-x-2 flex-wrap">
     <?php
            $queryParams = $_GET;
            $queryParams['page'] = 1;
            $firstPageUrl = 'funds.php?' . http_build_query($queryParams);
            ?>
     <a aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>" aria-label="Go to first page" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $page <= 1 ? 'cursor-not-allowed opacity-50' : '' ?>" href="<?= $page > 1 ? $firstPageUrl : '#' ?>">
      <i class="fas fa-angle-double-left"></i>
     </a>
     <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
                $queryParams['page'] = $i;
                $pageUrl = 'funds.php?' . http_build_query($queryParams);
            ?>
     <a aria-current="<?= $page === $i ? 'page' : 'false' ?>" aria-label="Go to page <?= $i ?>" class="px-3 py-1 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $page === $i ? 'bg-blue-600 text-white cursor-default' : 'text-gray-600 hover:bg-gray-100' ?>" href="<?= $page === $i ? '#' : $pageUrl ?>">
      <?= $i ?>
     </a>
     <?php endfor; ?>
     <?php
            $queryParams['page'] = $totalPages;
            $lastPageUrl = 'funds.php?' . http_build_query($queryParams);
            ?>
     <a aria-disabled="<?= $page >= $totalPages ? 'true' : 'false' ?>" aria-label="Go to last page" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $page >= $totalPages ? 'cursor-not-allowed opacity-50' : '' ?>" href="<?= $page < $totalPages ? $lastPageUrl : '#' ?>">
      <i class="fas fa-angle-double-right"></i>
     </a>
    </nav>
    <?php endif; ?>
   </main>
   <aside class="w-full lg:w-80 bg-white rounded-lg shadow p-4 flex flex-col">
    <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2 text-gray-800">
     <i class="fas fa-clock text-blue-600"></i>
     <span>Recent Fund Sources</span>
    </h3>
    <?php if ($recentResult->num_rows > 0): ?>
    <ul class="divide-y divide-gray-200 overflow-y-auto max-h-96">
     <?php while($recent = $recentResult->fetch_assoc()): ?>
     <li class="py-3 flex items-center space-x-3">
      <img alt="Icon representing fund source named <?= htmlspecialchars($recent['name']) ?>" class="w-12 h-12 rounded-full object-cover flex-shrink-0" height="48" src="https://storage.googleapis.com/a1aa/image/15291135-0aff-4fd0-245d-ac0fdba089ea.jpg" width="48"/>
      <div class="flex-1 min-w-0">
       <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($recent['name']) ?></p>
       <p class="text-xs text-green-600 font-semibold">$
        <?= number_format($recent['amount'], 2) ?></p>
       <p class="text-xs text-gray-400 font-mono"><?= date('Y-m-d', strtotime($recent['created_at'])) ?></p>
      </div>
     </li>
     <?php endwhile; ?>
    </ul>
    <?php else: ?>
    <p class="text-gray-500 text-sm text-center">No recent fund sources found.</p>
    <?php endif; ?>
   </aside>
  </div>
  <!-- Add/Edit Popover -->
  <div class="popover-backdrop" id="popover-backdrop"></div>
  <form action="" aria-describedby="popover-desc" aria-labelledby="popover-title" aria-modal="true" class="popover-form" id="fundsource-popover" method="post" novalidate role="dialog">
   <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center space-x-2" id="popover-title">
    <i class="fas fa-edit"></i>
    <span><?= $edit_row ? 'Edit Fund Source' : 'Add New Fund Source' ?></span>
   </h3>
   <p class="sr-only" id="popover-desc">Form to <?= $edit_row ? 'edit' : 'add' ?> a fund source</p>
   <?php if ($edit_row): ?>
   <input name="id" type="hidden" value="<?= $edit_row['id'] ?>"/>
   <?php endif; ?>
   <div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1 text-sm" for="name">Name <span class="text-red-500">*</span></label>
    <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="name" name="name" required type="text" value="<?= $edit_row ? htmlspecialchars($edit_row['name']) : '' ?>"/>
   </div>
   <div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1 text-sm" for="description">Description</label>
    <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="description" name="description" type="text" value="<?= $edit_row ? htmlspecialchars($edit_row['description']) : '' ?>"/>
   </div>
   <div class="mb-6">
    <label class="block text-gray-700 font-medium mb-1 text-sm" for="amount">Amount <span class="text-red-500">*</span></label>
    <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="amount" min="0" name="amount" required step="0.01" type="number" value="<?= $edit_row ? $edit_row['amount'] : '' ?>"/>
   </div>
   <div class="flex justify-end space-x-3">
    <?php if ($edit_row): ?>
    <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-green-500 transition text-sm" name="update" type="submit">Update</button>
    <a class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" href="funds.php">Cancel</a>
    <?php else: ?>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm" name="add" type="submit">Add</button>
    <button class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" id="close-popover" type="button">Cancel</button>
    <?php endif; ?>
   </div>
  </form>
  <!-- Delete Confirmation Popover -->
  <div class="popover-backdrop" id="delete-popover-backdrop"></div>
  <div aria-describedby="delete-popover-desc" aria-labelledby="delete-popover-title" aria-modal="true" class="popover-form" id="delete-popover" role="dialog">
   <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2" id="delete-popover-title">
    <i class="fas fa-exclamation-triangle text-red-600"></i>
    <span>Confirm Deletion</span>
   </h3>
   <p class="mb-6 text-gray-700" id="delete-popover-desc">Are you sure you want to delete this fund source?</p>
   <form action="funds.php" class="flex justify-end space-x-3" method="get">
    <input id="delete-id-input" name="delete" type="hidden"/>
    <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold focus:outline-none focus:ring-2 focus:ring-red-500 transition text-sm" type="submit">Yes, Delete</button>
    <button class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 transition text-sm" id="cancel-delete" type="button">Cancel</button>
   </form>
  </div>
  </div>
  <script>
   // Edit popover logic
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Preserve existing query params except page
            const url = new URL(window.location);
            url.searchParams.set('edit', this.getAttribute('data-id'));
            url.searchParams.delete('page');
            window.location.href = url.toString();
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
            deletePopover.classList.add('active');
            deleteBackdrop.classList.add('active');
            cancelDeleteBtn.focus();
        });
    });
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', function() {
        deletePopover.classList.remove('active');
        deleteBackdrop.classList.remove('active');
    });
    if (deleteBackdrop) deleteBackdrop.addEventListener('click', function() {
        deletePopover.classList.remove('active');
        deleteBackdrop.classList.remove('active');
    });

    // Popover open/close logic for add/edit
    const openBtn = document.getElementById('open-popover');
    const popover = document.getElementById('fundsource-popover');
    const closeBtn = document.getElementById('close-popover');
    const backdrop = document.getElementById('popover-backdrop');

    function openPopover() {
        popover.classList.add('active');
        backdrop.classList.add('active');
        // Focus first input
        const firstInput = popover.querySelector('input, select, textarea, button');
        if (firstInput) firstInput.focus();
    }
    function closePopover() {
        popover.classList.remove('active');
        backdrop.classList.remove('active');
        // Remove URL edit param if any
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('edit');
            window.history.replaceState({}, document.title, url.toString());
        }
    }
    if (openBtn) openBtn.addEventListener('click', openPopover);
    if (closeBtn) closeBtn.addEventListener('click', closePopover);
    if (backdrop) backdrop.addEventListener('click', closePopover);

    // If in edit mode, show the popover automatically
    <?php if ($edit_row): ?>
    openPopover();
    <?php endif; ?>

    // Accessibility: trap focus inside popovers when open
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll('a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length === 0) return;
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) { // Shift + Tab
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else { // Tab
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
            if (e.key === 'Escape') {
                if (!element.classList.contains('hidden') && element.classList.contains('active')) {
                    if (element === popover) closePopover();
                    if (element === deletePopover) {
                        deletePopover.classList.remove('active');
                        deleteBackdrop.classList.remove('active');
                    }
                }
            }
        });
    }
    trapFocus(popover);
    trapFocus(deletePopover);
  </script>
 </body>
</html>
