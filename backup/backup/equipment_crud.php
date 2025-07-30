<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Equipment CRUD with Overlay Form</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
<style>
  /* Hide the overlay by default */
  #overlay-form {
    display: none;
  }
</style>
</head>
<body class="bg-gray-50 p-4 min-h-screen flex flex-col">

<?php
// Include config for $conn
include '../config.php';

// Create
if (isset($_POST['add_equipment'])) {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert into financial_transactions
        $stmt = $conn->prepare("INSERT INTO financial_transactions (category, description, amount, transaction_date) VALUES ('equipment', ?, ?, NOW())");
        $description = "Equipment purchase: " . $_POST['item_name'];
        $amount = $_POST['quantity'] * $_POST['unit_price'];
        $stmt->bind_param("sd", $description, $amount);
        $stmt->execute();
        $transaction_id = $conn->insert_id; // Get the last inserted transaction_id
        $stmt->close();

        // Insert into equipment
        $stmt = $conn->prepare("INSERT INTO equipment (transaction_id, item_name, quantity, unit_price, supplier) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isids", $transaction_id, $_POST['item_name'], $_POST['quantity'], $_POST['unit_price'], $_POST['supplier']);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();
        header("Location: dashboard.php?page=financial_records&subpage=equipment_crud");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        throw $e;
    }
}

// Update
if (isset($_POST['update_equipment'])) {
    $stmt = $conn->prepare("UPDATE equipment SET item_name=?, quantity=?, unit_price=?, supplier=? WHERE equipment_id=?");
    $stmt->bind_param("sidsi", $_POST['item_name'], $_POST['quantity'], $_POST['unit_price'], $_POST['supplier'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=financial_records&subpage=equipment_crud");
    exit;
}

// Delete
if (isset($_GET['delete_equipment'])) {
    $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id=?");
    $stmt->bind_param("i", $_GET['delete_equipment']);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?page=financial_records&subpage=equipment_crud");
    exit;
}

// Read
$equipmentItems = [];
$result = $conn->query("SELECT * FROM equipment");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $equipmentItems[] = $row;
    }
    $result->free();
}

// Determine if editing and get the item data
$editing = false;
$editItem = null;
if (isset($_GET['edit_equipment'])) {
    $editing = true;
    $edit_id = (int)$_GET['edit_equipment'];
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editItem = $res->fetch_assoc();
    $stmt->close();
}
?>

<!-- Button to open Add Equipment form -->
<div class="mb-4 flex justify-end">
  <button id="open-add-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2">
    <i class="fas fa-plus"></i> Add Equipment
  </button>
  <a href="reports/equipment_reports.php" class="bg-green-500 text-white px-4 py-2 rounded">
    <i class="fas fa-print"></i> Print PDF
</a>
</div>

<!-- Equipment Table -->
<div class="overflow-x-auto bg-white rounded shadow">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <?php foreach ($equipmentItems as $item): ?>
      <tr>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($item['equipment_id']) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($item['item_name']) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($item['quantity']) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">$<?= number_format($item['unit_price'], 2) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($item['supplier']) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">$<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 flex gap-2">
          <button 
            class="edit-btn text-blue-600 hover:text-blue-800 font-semibold"
            data-id="<?= $item['equipment_id'] ?>"
            data-item_name="<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>"
            data-quantity="<?= $item['quantity'] ?>"
            data-unit_price="<?= $item['unit_price'] ?>"
            data-supplier="<?= htmlspecialchars($item['supplier'], ENT_QUOTES) ?>"
          >
            <i class="fas fa-edit"></i> Edit
          </button>
          <a href="dashboard.php?page=financial_records&subpage=equipment_crud&delete_equipment=<?= $item['equipment_id'] ?>" onclick="return confirm('Are you sure you want to delete this equipment?')" class="text-red-600 hover:text-red-800 font-semibold flex items-center gap-1">
            <i class="fas fa-trash-alt"></i> Delete
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (count($equipmentItems) === 0): ?>
      <tr>
        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No equipment found.</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Overlay Form -->
<div id="overlay-form" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md relative">
    <button id="close-overlay" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl font-bold" aria-label="Close form">&times;</button>
    <h2 id="form-title" class="text-xl font-semibold text-gray-800 px-6 pt-6 pb-4 border-b">Add Equipment</h2>
    <form id="equipment-form" method="post" class="px-6 pb-6 space-y-4">
      <input type="hidden" name="id" id="equipment-id" value="" />
      <div>
        <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name <span class="text-red-500">*</span></label>
        <input type="text" name="item_name" id="item_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50" placeholder="Enter item name" />
      </div>
      <div>
        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
        <input type="number" name="quantity" id="quantity" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50" />
      </div>
      <div>
        <label for="unit_price" class="block text-sm font-medium text-gray-700">Unit Price <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" name="unit_price" id="unit_price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50" placeholder="Enter unit price" />
      </div>
      <div>
        <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
        <input type="text" name="supplier" id="supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-300 focus:ring-opacity-50" placeholder="Enter supplier name" />
      </div>
      <div class="flex justify-between items-center pt-4 border-t">
        <button type="submit" name="add_equipment" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow flex items-center gap-2">
          <i class="fas fa-plus" id="submit-icon"></i> Add Equipment
        </button>
        <button type="button" id="cancel-btn" class="text-gray-600 hover:text-gray-900 font-semibold">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Elements
  const overlay = document.getElementById('overlay-form');
  const openAddBtn = document.getElementById('open-add-btn');
  const closeOverlayBtn = document.getElementById('close-overlay');
  const cancelBtn = document.getElementById('cancel-btn');
  const formTitle = document.getElementById('form-title');
  const equipmentForm = document.getElementById('equipment-form');
  const submitBtn = document.getElementById('submit-btn');
  const submitIcon = document.getElementById('submit-icon');

  // Form inputs
  const inputId = document.getElementById('equipment-id');
  const inputItemName = document.getElementById('item_name');
  const inputQuantity = document.getElementById('quantity');
  const inputUnitPrice = document.getElementById('unit_price');
  const inputSupplier = document.getElementById('supplier');

  // Edit buttons
  const editButtons = document.querySelectorAll('.edit-btn');

  // Open Add Equipment form
  openAddBtn.addEventListener('click', () => {
    openForm('add');
  });

  // Close overlay
  closeOverlayBtn.addEventListener('click', closeForm);
  cancelBtn.addEventListener('click', closeForm);

  // Close form function
  function closeForm() {
    overlay.style.display = 'none';
    clearForm();
  }

  // Clear form inputs
  function clearForm() {
    inputId.value = '';
    inputItemName.value = '';
    inputQuantity.value = 1;
    inputUnitPrice.value = '';
    inputSupplier.value = '';
    submitBtn.name = 'add_equipment';
    submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Equipment';
    formTitle.textContent = 'Add Equipment';
  }

  // Open form with mode: 'add' or 'edit'
  function openForm(mode, data = null) {
    overlay.style.display = 'flex';
    if (mode === 'edit' && data) {
      formTitle.textContent = 'Edit Equipment';
      inputId.value = data.id;
      inputItemName.value = data.item_name;
      inputQuantity.value = data.quantity;
      inputUnitPrice.value = data.unit_price;
      inputSupplier.value = data.supplier;
      submitBtn.name = 'update_equipment';
      submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Equipment';
    } else {
      clearForm();
    }
  }

  // Attach event listeners to edit buttons
  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const data = {
        id: btn.getAttribute('data-id'),
        item_name: btn.getAttribute('data-item_name'),
        quantity: btn.getAttribute('data-quantity'),
        unit_price: btn.getAttribute('data-unit_price'),
        supplier: btn.getAttribute('data-supplier'),
      };
      openForm('edit', data);
    });
  });

  // If PHP detected editing on page load, open overlay with data
  <?php if ($editing && $editItem): ?>
    window.addEventListener('DOMContentLoaded', () => {
      openForm('edit', {
        id: '<?= $editItem['equipment_id'] ?>',
        item_name: '<?= addslashes($editItem['item_name']) ?>',
        quantity: '<?= $editItem['quantity'] ?>',
        unit_price: '<?= $editItem['unit_price'] ?>',
        supplier: '<?= addslashes($editItem['supplier']) ?>'
      });
    });
  <?php endif; ?>
</script>

</body>
</html>