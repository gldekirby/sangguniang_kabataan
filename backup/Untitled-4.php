<?php
// Start session and output buffering
ob_start();

// Include necessary files and initialize variables
include '../config.php';

// ✅ Include SMS Gateway dependencies
require 'C:/xampp/htdocs/www.sangguniang_kabataan.com/sms/vendor/autoload.php';
use AndroidSmsGateway\Client;
use AndroidSmsGateway\Domain\Message;

// Fetch events from the database
$events = [];
$sql = "SELECT event_id, event_name, start_date, end_date, event_time, location, description FROM schedule_events";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start_date = new DateTime($row['start_date']);
        $end_date = new DateTime($row['end_date']);
        $end_date->modify('+1 day'); // Include the end date

        // Loop through each day of the event
        while ($start_date < $end_date) {
            $events[] = [
                'id' => $row['event_id'],
                'title' => $row['event_name'],
                'start' => $start_date->format('Y-m-d') . 'T' . $row['event_time'],
                'description' => $row['description'],
                'location' => $row['location']
            ];
            $start_date->modify('+1 day'); // Move to the next day
        }
    }
}

// Handle form submission to add a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $event_name = $_POST['event_name'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $event_time = $_POST['event_time'];
            $location = $_POST['location'];
            $description = $_POST['description'];

            // Add validation for past dates
            $today = date('Y-m-d');
            if ($start_date < $today) {
                $_SESSION['error'] = "You cannot add events for past dates. Please select a current or future date.";
            } else {
                // Insert the event into the database
                $stmt = $conn->prepare("INSERT INTO schedule_events (event_name, start_date, end_date, event_time, location, description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $event_name, $start_date, $end_date, $event_time, $location, $description);

                if ($stmt->execute()) {
                    // Set success message
                    $_SESSION['success'] = "Event added successfully!";
                    
                    // JavaScript redirect to dashboard
                    echo '<script>window.location.href = "dashboard.php?page=schedule_event";</script>';
                    
                    // Flush all output buffers to send the redirect immediately
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                    flush();
                    
                    // Close session to allow other requests
                    session_write_close();
                    
                    // Continue processing in the background
                    ignore_user_abort(true);
                    set_time_limit(0);
                    
                    // ✅ SMS Notification Code
                    $login = 'KWBUN-';
                    $password = '2342Gldekirby@21';
                    $client = new Client($login, $password);
                
                    // Fetch members and send SMS
                    $result = $conn->query("SELECT contact_number, member_id, first_name FROM members");
                    if ($result && $result->num_rows > 0) {
                        while ($member = $result->fetch_assoc()) {
                            $raw_number = $member['contact_number'];
                            $formatted_number = '+63' . ltrim($raw_number, '0');
                            if (preg_match('/^\+639\d{9}$/', $formatted_number)) {
                                $firstname = $member['first_name'];
                                $messageText = "Hi $firstname! 📅 You're invited to the event: \"$event_name\" on $start_date to $end_date at $event_time in $location.\n\nEvent Details:\n$description\n\nSee you there!";
                                $message = new Message($messageText, [$formatted_number]);
                                try {
                                    $client->Send($message);
                                    // Insert message into messages table
                                    $insertMsgStmt = $conn->prepare("INSERT INTO messages (member_id, sender_name, message) VALUES (?, ?, ?)");
                                    $senderName = "Admin";
                                    $msgText = $messageText;
                                    $insertMsgStmt->bind_param("iss", $member['member_id'], $senderName, $msgText);
                                    $insertMsgStmt->execute();
                                    $insertMsgStmt->close();
                                } catch (Exception $e) {
                                    error_log("SMS send failed to $formatted_number: " . $e->getMessage());
                                }
                            } else {
                                error_log("Invalid number for member ID {$member['member_id']}: $raw_number");
                            }
                        }
                    }
                    
                    exit();
                }
                $stmt->close();
            }
        }

        // ... (Your existing edit and delete logic remains unchanged)
        elseif ($action === 'edit' && isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            $event_name = $_POST['event_name'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $event_time = $_POST['event_time'];
            $location = $_POST['location'];
            $description = $_POST['description'];

            // Update the event in the database
            $stmt = $conn->prepare("UPDATE schedule_events SET event_name = ?, start_date = ?, end_date = ?, event_time = ?, location = ?, description = ? WHERE event_id = ?");
            $stmt->bind_param("ssssssi", $event_name, $start_date, $end_date, $event_time, $location, $description, $event_id);

            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success'] = "Event updated successfully!";
                
                // ✅ SMS Notification Code for Event Update
                $login = 'KWBUN-';
                $password = '2342Gldekirby@21';
                $client = new Client($login, $password);

                // Fetch members and send SMS
                $result = $conn->query("SELECT contact_number, member_id, first_name FROM members");
                if ($result && $result->num_rows > 0) {
                    while ($member = $result->fetch_assoc()) {
                        $raw_number = $member['contact_number'];
                        $formatted_number = '+63' . ltrim($raw_number, '0');
                        if (preg_match('/^\+639\d{9}$/', $formatted_number)) {
                            $firstname = $member['first_name'];
                            $messageText = "Hi $firstname! 📅 The event \"$event_name\" has been updated. It will now take place from $start_date to $end_date at $event_time in $location.\n\nUpdated Details:\n$description\n\nThank you!";
                            $message = new Message($messageText, [$formatted_number]);
                                try {
                                $client->Send($message);
                                // Insert message into messages table
                                $insertMsgStmt = $conn->prepare("INSERT INTO messages (member_id, sender_name, message) VALUES (?, ?, ?)");
                                $senderName = "Admin";
                                $msgText = $messageText;
                                $insertMsgStmt->bind_param("iss", $member['member_id'], $senderName, $msgText);
                                $insertMsgStmt->execute();
                                $insertMsgStmt->close();
                            } catch (Exception $e) {
                                error_log("SMS send failed to $formatted_number: " . $e->getMessage());
                            }
                        } else {
                            error_log("Invalid number for member ID {$member['member_id']}: $raw_number");
                        }
                    }
                }

                // Redirect to the dashboard
                echo '<script>window.location.href = "dashboard.php?page=schedule_event";</script>';
                exit();
            } else {
                $_SESSION['error'] = "Error updating event: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);

    // Fetch event details before deletion
    $eventQuery = $conn->prepare("SELECT event_name, start_date, end_date, event_time, location FROM schedule_events WHERE event_id = ?");
    $eventQuery->bind_param("i", $event_id);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();
    $eventDetails = $eventResult->fetch_assoc();
    $eventQuery->close();

    if ($eventDetails) {
        $event_name = $eventDetails['event_name'];
        $start_date = $eventDetails['start_date'];
        $end_date = $eventDetails['end_date'];
        $event_time = $eventDetails['event_time'];
        $location = $eventDetails['location'];
    }

    // Delete the event from the database
    $stmt = $conn->prepare("DELETE FROM schedule_events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Event deleted successfully!";

        // Removed SMS Notification Code for Event Cancellation as per user request

        echo '<script>window.location.href = "dashboard.php?page=schedule_event";</script>';
    } else {
        $_SESSION['error'] = "Error deleting event: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// End output buffering
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Youth Calendar Planner</title>
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure the modal overlay is properly displayed */
        #addEventModal, #eventDetailsModal, #editEventModal {
            z-index: 1050; /* High z-index to ensure it overlays other elements */
        }
        /* Confirmation modal styling */
        #confirmDeleteModal {
            z-index: 1060; /* Higher z-index to ensure it overlays the edit modal */
        }

        /* Professional styling for the calendar container */
        #calendar {
            border: 1px solid #e5e7eb; /* Light gray border */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            position: relative; /* Ensure it is positioned correctly */
        }

        /* Modal styling */
        .modal-header {
            background-color: #4f46e5; /* Indigo background */
            color: white; /* White text */
            padding: 1rem;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem;
            background-color: #f9fafb; /* Light gray background */
            border-top: 1px solid #e5e7eb; /* Light gray border */
        }

        .modal-footer button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }

        .modal-footer .close-btn {
            background-color: #6b7280; /* Gray background */
            color: white;
        }

        .modal-footer .save-btn {
            background-color: #4f46e5; /* Indigo background */
            color: white;
        }

        .modal-footer .delete-btn {
            background-color: #ef4444; /* Red background */
            color: white;
        }

        .modal-footer .close-btn:hover {
            background-color: #4b5563; /* Darker gray */
        }

        .modal-footer .save-btn:hover {
            background-color: #4338ca; /* Darker indigo */
        }

        .modal-footer .delete-btn:hover {
            background-color: #dc2626; /* Darker red */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #calendar {
                margin: 0;
                padding: 0.5rem;
                width: 100%;
            }
            
            .fc-header-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .fc-toolbar-chunk {
                margin-bottom: 0.5rem;
            }
            
            .fc .fc-toolbar-title {
                font-size: 1.25rem;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.75rem;
                padding: 2px;
            }
            
            .fc-daygrid-event {
                font-size: 0.7rem;
                padding: 1px 2px;
                margin-bottom: 1px;
            }
            
            /* Modal adjustments for mobile */
            #addEventModal .bg-white,
            #eventDetailsModal .bg-white,
            #editEventModal .bg-white {
                width: 90% !important;
                margin: 0 auto;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .fc-col-header-cell-cushion {
                font-size: 0.7rem;
                padding: 2px;
            }
            
            .fc-daygrid-day-frame {
                min-height: 30px;
            }
        }

        @media (max-width: 480px) {
            .fc .fc-toolbar-title {
                font-size: 1rem;
            }
            
            .fc-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.65rem;
            }
            
            .fc-daygrid-event {
                font-size: 0.6rem;
            }
            
            .fc-col-header-cell-cushion {
                font-size: 0.6rem;
            }
        }

        /* Enhanced overlay styling */
        #addEventModal, #eventDetailsModal, #editEventModal, #confirmDeleteModal {
            background-color: rgba(0, 0, 0, 0.75); /* Darker, more prominent overlay */
            backdrop-filter: blur(4px); /* Blur effect for background */
            transition: all 0.3s ease-in-out;
        }

        /* Enhanced modal container styling */
        .modal-container {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }

        .modal-container.show {
            transform: scale(1);
            opacity: 1;
        }

        /* Enhanced modal styling */
        .modal-content {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Enhanced modal header */
        .modal-header {
            background: linear-gradient(to right, #4f46e5, #4338ca);
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1.25rem;
        }

        .modal-header h5 {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced modal close button */
        .modal-close-btn {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        /* Enhanced form inputs */
        .modal-body input,
        .modal-body textarea {
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .modal-body input:focus,
        .modal-body textarea:focus {
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
            border-color: #4f46e5;
        }

        /* Enhanced buttons */
        .modal-footer button {
            transition: all 0.2s ease;
            transform: scale(1);
        }

        .modal-footer button:hover {
            transform: scale(1.05);
        }

        .modal-footer button:active {
            transform: scale(0.95);
        }

        /* Animation keyframes */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes overlayFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Apply animations */
        .modal-overlay {
            animation: overlayFadeIn 0.3s ease-out;
        }

        .modal-container {
            animation: modalFadeIn 0.3s ease-out;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .modal-container {
                width: 95%;
                margin: 1rem;
            }
            
            .modal-header {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-footer {
                padding: 1rem;
            }
        }

        /* Add these styles in your existing <style> tag */
        #calendar {
            margin: 0.5rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 85vh;
        }

        .fc {
            height: 100% !important;
        }

        .fc-view {
            overflow-x: hidden;
        }

        .fc-header-toolbar {
            padding: 0.5rem;
            margin-bottom: 0.5rem !important;
        }

        .fc-view-harness {
            height: calc(100% - 50px) !important;
        }

        .fc-daygrid-day {
            min-height: 100px !important;
        }

        .fc-day-today {
            background: rgba(79, 70, 229, 0.1) !important;
        }

        .fc-event {
            border-radius: 4px;
            padding: 2px 4px;
            margin: 1px 0;
            border: none;
            background-color: #4f46e5;
            color: white;
            cursor: pointer;
            transition: transform 0.1s ease;
        }

        .fc-event:hover {
            transform: scale(1.02);
        }

        .fc-button-primary {
            background-color: #4f46e5 !important;
            border-color: #4338ca !important;
        }

        .fc-button-primary:hover {
            background-color: #4338ca !important;
            border-color: #3730a3 !important;
        }

        @media (max-width: 768px) {
            #calendar {
                height: calc(100vh - 2rem);
                margin: 0.25rem;
                padding: 0.5rem;
            }

            .fc-header-toolbar {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.25rem;
            }

            .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                width: 100%;
            }

            .fc-daygrid-day {
                min-height: 80px !important;
            }

            .fc-toolbar-title {
                font-size: 1.2rem !important;
            }
        }
    </style>
</head>
<body class="bg-white w-full">
    <?php if (isset($_SESSION['success'])): ?>
        <div id='successAlert' class='fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50'>
            <div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg flex items-start'>
                <div class='flex-1'><?php echo $_SESSION['success']; ?></div>
                <button onclick="dismissAlert('successAlert')" class='ml-4 text-green-500 hover:text-green-700'>
                    &times;
                </button>
            </div>
        </div>
        <script>
            // Slide in
            setTimeout(function() {
                var element = document.getElementById('successAlert');
                if (element) element.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-dismiss after delay
            setTimeout(function() {
                dismissAlert('successAlert');
            }, 3000);
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div id='errorAlert' class='fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50'>
            <div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg flex items-start'>
                <div class='flex-1'><?php echo $_SESSION['error']; ?></div>
                <button onclick="dismissAlert('errorAlert')" class='ml-4 text-red-500 hover:text-red-700'>
                    &times;
                </button>
            </div>
        </div>
        <script>
            // Slide in
            setTimeout(function() {
                var element = document.getElementById('errorAlert');
                if (element) element.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-dismiss after delay
            setTimeout(function() {
                dismissAlert('errorAlert');
            }, 5000);
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <header class="bg-white shadow sticky top-0 z-30">
   <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <div class="flex items-center space-x-3">
     <img alt="Company logo with green background and white text 'Logo'" class="hidden h-10 w-10 rounded" height="40" src="https://storage.googleapis.com/a1aa/image/c47e04aa-8d6b-4f49-a778-002e1fb1fd25.jpg" width="40"/>
     <h1 class="text-xl text-green-900 tracking-tight">
      Schedule Event
     </h1>
     
    </div>
    <nav class="hidden md:flex space-x-6 text-gray-700 font-semibold">
            <div class="flex flex-wrap items-center gap-4 mt-4 mb-2" id="calendar-legend">
            <span class="flex items-center"><span class="inline-block w-4 h-4 rounded mr-2" style="background-color: #4f46e5;"></span>Upcoming Event</span>
            <span class="flex items-center"><span class="inline-block w-4 h-4 rounded mr-2" style="background-color: #868f93;"></span>Past Event</span>
        </div>
    </nav>
  </header>
    <div class="container-fluid p-0">
        <div id="calendar"></div>
        <!-- Floating Add Event Button -->
        <button id="addEventBtn" type="button" onclick="openModal()" class="hidden fixed bottom-8 right-8 z-50 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg p-4 text-2xl focus:outline-none focus:ring-2 focus:ring-indigo-400">
            +
        </button>
    </div>

    <!-- Modal for adding events -->
    <div id="addEventModal" class="modal-overlay hidden fixed inset-0 items-center justify-center p-4">
        <div class="modal-container bg-white rounded-xl shadow-2xl w-full sm:w-2/3 md:w-1/2 lg:w-1/3 max-w-lg">
            <div class="modal-header flex justify-between items-center">
                <h5 class="text-lg font-bold">Add Event</h5>
                <button type="button" class="modal-close-btn text-white" onclick="closeModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="event_name" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="end_date" name="end_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_time" class="block text-sm font-medium text-gray-700">Event Time</label>
                        <input type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="event_time" name="event_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer flex justify-end">
                    <button type="button" class="close-btn" onclick="closeModal()">Close</button>
                    <button type="submit" class="ml-2 save-btn">Save Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for editing events -->
    <div id="editEventModal" class="modal-overlay hidden fixed inset-0 items-center justify-center p-2 sm:p-4">
        <div class="modal-container bg-white rounded-lg shadow-lg w-full sm:w-2/3 md:w-1/2 lg:w-1/3">
            <div class="modal-header flex justify-between items-center">
                <h5 class="text-lg font-bold">Edit Event</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_event_id" name="event_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_event_name" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_end_date" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_event_time" class="block text-sm font-medium text-gray-700">Event Time</label>
                        <input type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_event_time" name="event_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer flex justify-between">
                <div>
                    <button type="button" class="close-btn" onclick="closeEditModal()">Close</button>
                    <button type="submit" class="ml-2 save-btn">Update Event</button>
                </div>
                    <button type="button" class="delete-btn" onclick="confirmDelete()">Delete Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for displaying event details -->
    <div id="eventDetailsModal" class="modal-overlay hidden fixed inset-0 items-center justify-center p-2 sm:p-4">
        <div class="modal-container bg-white rounded-lg shadow-lg w-full sm:w-2/3 md:w-1/2 lg:w-1/3">
            <div class="modal-header flex justify-between items-center">
                <h5 class="text-lg font-bold">Event Details</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeEventDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Event:</strong> <span id="eventTitle"></span></p>
                <p><strong>Start:</strong> <span id="eventStart"></span></p>
                <p><strong>End:</strong> <span id="eventEnd"></span></p>
                <p><strong>Description:</strong> <span id="eventDescription"></span></p>
                <p><strong>Location:</strong> <span id="eventLocation"></span></p>
            </div>
            <div class="modal-footer flex justify-end">
                <button type="button" class="close-btn" onclick="closeEventDetailsModal()">Close</button>
                <button type="button" class="ml-2 save-btn" id="editEventBtn">Edit Event</button>
            </div>
        </div>
    </div>

    <!-- Confirmation modal for delete -->
    <div id="confirmDeleteModal" class="modal-overlay hidden fixed inset-0 items-center justify-center p-4 z-50">
        <div class="modal-container bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
            <div class="modal-header flex justify-between items-center mb-4">
                <h5 class="text-lg font-bold">Confirm Delete</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeConfirmDeleteModal()">&times;</button>
            </div>
            <div class="modal-body mb-4">
                <p>Are you sure you want to delete this event? This action cannot be undone.</p>
            </div>
            <div class="modal-footer flex justify-start">
                <button type="button" class="close-btn mr-2" onclick="closeConfirmDeleteModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="delete-btn">Delete</a>
            </div>
        </div>
    </div>

    

    <script>
        // Initialize calendar
        var calendar;
        var currentEventId = null;
        
        // Utility functions
        function dismissAlert(id) {
            var element = document.getElementById(id);
            if (element) {
                element.classList.add('translate-x-full');
                setTimeout(function(){ element.remove(); }, 300);
            }
        }
        
        function showToast(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = `fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50`;
            toast.innerHTML = `
                <div class='${type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700'} border px-4 py-3 rounded-lg shadow-lg flex items-start'>
                    <div class='flex-1'>${message}</div>
                    <button onclick="dismissAlert('${toastId}')" class='ml-4 ${type === 'error' ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'}'>
                        &times;
                    </button>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Slide in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-dismiss after delay
            setTimeout(() => {
                dismissAlert(toastId);
            }, type === 'error' ? 5000 : 3000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: '85vh', // Set height to 85% of viewport height
                expandRows: true, // Expand rows to fill available height
                handleWindowResize: true,
                windowResizeDelay: 200,
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 6, // Increase max events per day
                        fixedWeekCount: false // Allow variable number of weeks
                    },
                    timeGridWeek: {
                        dayMaxEventRows: 6,
                        slotMinTime: '06:00:00', // Start time
                        slotMaxTime: '20:00:00'  // End time
                    },
                    timeGridDay: {
                        dayMaxEventRows: 10,
                        slotMinTime: '06:00:00',
                        slotMaxTime: '20:00:00'
                    }
                },
                events: <?php echo json_encode($events); ?>,
                eventDisplay: 'block', // Display events as blocks
                eventDidMount: function(info) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Reset time to midnight

                    const eventDate = new Date(info.event.start);
                    eventDate.setHours(0, 0, 0, 0); // Reset time to midnight

                    if (eventDate < today) {
                        // Mark past events as red
                        info.el.style.backgroundColor = '#868f93'; // Tailwind's red-400
                        info.el.style.borderColor = '#868f93';
                        info.el.style.color = 'white';
                    }
                },
                dateClick: function(info) {
                    // Your existing dateClick logic
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const clickedDate = new Date(info.dateStr);

                    if (clickedDate < today) {
                        showToast('You cannot add events for past dates. Please select a current or future date.', 'error');
                        return;
                    }

                    document.getElementById('start_date').value = info.dateStr;
                    document.getElementById('end_date').value = info.dateStr;
                    openModal();
                },
                eventClick: function(info) {
                    // Your existing eventClick logic
                    currentEventId = info.event.id;
                    const startDate = info.event.start ? new Date(info.event.start) : null;
                    const endDate = info.event.end ? new Date(info.event.end) : null;

                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    };

                    const startStr = startDate ? startDate.toLocaleDateString('en-US', options) : 'N/A';
                    const endStr = endDate ? endDate.toLocaleDateString('en-US', options) : 'N/A';

                    document.getElementById('eventTitle').textContent = info.event.title;
                    document.getElementById('eventStart').textContent = startStr;
                    document.getElementById('eventEnd').textContent = endStr;
                    document.getElementById('eventDescription').textContent = info.event.extendedProps.description || 'N/A';
                    document.getElementById('eventLocation').textContent = info.event.extendedProps.location || 'N/A';

                    document.getElementById('editEventBtn').onclick = function() {
                        closeEventDetailsModal();
                        openEditModal(info.event);
                    };

                    openEventDetailsModal();
                }
            });

            // Add hover effect to days
            calendarEl.addEventListener('mouseover', function(event) {
                if (event.target.closest('.fc-daygrid-day')) {
                    event.target.closest('.fc-daygrid-day').classList.add('bg-gray-200');
                }
            });

            calendarEl.addEventListener('mouseout', function(event) {
                if (event.target.closest('.fc-daygrid-day')) {
                    event.target.closest('.fc-daygrid-day').classList.remove('bg-gray-200');
                }
            });

            calendar.render();
            
            // Periodic refresh of calendar events every 3ms seconds for realtime update
            setInterval(function() {
                calendar.refetchEvents();
            }, 300); //300 ms = 1 seconds

            // Immediate system time change detection and reload
            (function monitorSystemTime() {
                let lastTime = Date.now();
                function checkTime() {
                    const currentTime = Date.now();
                    // If system time changed significantly (more than 3 millisecond difference)
                    if (Math.abs(currentTime - lastTime - 300) > 300) {
                        location.reload();
                    } else {
                        lastTime = currentTime;
                        setTimeout(checkTime, 300);
                    }
                }
                checkTime();
            })();
            
            // Handle window resize
            window.addEventListener('resize', function() {
                calendar.updateSize();
            });
        });

        function openModal() {
            const modal = document.getElementById('addEventModal');
            const container = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Trigger animation
            setTimeout(() => {
                container.classList.add('show');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('addEventModal');
            const container = modal.querySelector('.modal-container');
            container.classList.remove('show');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        function openEditModal(event) {
            // Populate the edit form with event data
            document.getElementById('edit_event_id').value = event.id;
            document.getElementById('edit_event_name').value = event.title;
            
            const startDate = event.start ? new Date(event.start) : null;
            const endDate = event.end ? new Date(event.end) : null;
            
            if (startDate) {
                document.getElementById('edit_start_date').value = startDate.toISOString().split('T')[0];
                
                // Format time for edit form - simplified
                const hours = startDate.getHours().toString().padStart(2, '0');
                const minutes = startDate.getMinutes().toString().padStart(2, '0');
                document.getElementById('edit_event_time').value = `${hours}:${minutes}`;
            }
            
            if (endDate) {
                document.getElementById('edit_end_date').value = endDate.toISOString().split('T')[0];
            }
            
            document.getElementById('edit_location').value = event.extendedProps.location || '';
            document.getElementById('edit_description').value = event.extendedProps.description || '';
            
            const modal = document.getElementById('editEventModal');
            const container = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                container.classList.add('show');
            }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('editEventModal');
            const container = modal.querySelector('.modal-container');
            container.classList.remove('show');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        function openEventDetailsModal() {
            const modal = document.getElementById('eventDetailsModal');
            const container = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                container.classList.add('show');
            }, 10);
        }

        function closeEventDetailsModal() {
            const modal = document.getElementById('eventDetailsModal');
            const container = modal.querySelector('.modal-container');
            container.classList.remove('show');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        function confirmDelete() {
            const modal = document.getElementById('confirmDeleteModal');
            const container = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                container.classList.add('show');
            }, 10);
            // Set up delete button
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = function() {
                window.location.href = `dashboard.php?page=schedule_event&delete=${currentEventId}`;
            };
        }

        function closeConfirmDeleteModal() {
            const modal = document.getElementById('confirmDeleteModal');
            const container = modal.querySelector('.modal-container');
            container.classList.remove('show');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        // Add validation for date inputs
        function validateDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const today = new Date().toISOString().split('T')[0];

            if (startDate < today) {
                showToast('Start date cannot be in the past', 'error');
                return false;
            }

            if (endDate < startDate) {
                showToast('End date cannot be before start date', 'error');
                return false;
            }

            return true;
        }

        // Add form validation to both add and edit forms
        document.querySelectorAll('#addEventModal form, #editEventModal form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateDates()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>