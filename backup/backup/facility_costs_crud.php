<?php
// Include config for $conn
include '../config.php';

// Create
if (isset($_POST['add_facility_cost'])) {
    $stmt = $conn->prepare("INSERT INTO facility_costs (transaction_id, venue_name, purpose, duration_hours) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $_POST['transaction_id'], $_POST['venue_name'], $_POST['purpose'], $_POST['duration_hours']);
    $stmt->execute();
    $stmt->close();
}

// Read
$facilityCosts = [];
$result = $conn->query("SELECT * FROM facility_costs");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $facilityCosts[] = $row;
    }
    $result->free();
}

// Update
if (isset($_POST['update_facility_cost'])) {
    $stmt = $conn->prepare("UPDATE facility_costs SET transaction_id=?, venue_name=?, purpose=?, duration_hours=? WHERE facility_id=?");
    $stmt->bind_param("issdi", $_POST['transaction_id'], $_POST['venue_name'], $_POST['purpose'], $_POST['duration_hours'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// Delete
if (isset($_GET['delete_facility_cost'])) {
    $stmt = $conn->prepare("DELETE FROM facility_costs WHERE facility_id=?");
    $stmt->bind_param("i", $_GET['delete_facility_cost']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
}
?>
<style>s
    form {
        margin-bottom: 20px;
    }
    input[type="text"],
    input[type="number"],
    input[type="date"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    button {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    button:hover {
        background-color: #218838;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
</style>
<form method="post">
    <input type="hidden" name="id" value="<?= isset($_GET['edit_facility_cost']) ? $_GET['edit_facility_cost'] : '' ?>">
    <input type="number" name="transaction_id" placeholder="Transaction ID" required>
    <input type="text" name="venue_name" placeholder="Venue Name" required>
    <input type="text" name="purpose" placeholder="Purpose" required>
    <input type="number" step="0.01" name="duration_hours" placeholder="Duration (hours)">
    <button type="submit" name="<?= isset($_GET['edit_facility_cost']) ? 'update_facility_cost' : 'add_facility_cost' ?>">
        <?= isset($_GET['edit_facility_cost']) ? 'Update' : 'Add' ?> Facility Cost
    </button>
    <?php if (isset($_GET['edit_facility_cost'])): ?>
        <a href="index.php">Cancel</a>
    <?php endif; ?>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Transaction ID</th>
        <th>Venue Name</th>
        <th>Purpose</th>
        <th>Duration (hours)</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($facilityCosts as $cost): ?>
    <tr>
        <td><?= $cost['facility_id'] ?></td>
        <td><?= $cost['transaction_id'] ?></td>
        <td><?= $cost['venue_name'] ?></td>
        <td><?= $cost['purpose'] ?></td>
        <td><?= $cost['duration_hours'] ?></td>
        <td>
            <a href="index.php?edit_facility_cost=<?= $cost['facility_id'] ?>">Edit</a>
            <a href="index.php?delete_facility_cost=<?= $cost['facility_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>