<?php
include '../config.php';

// Function to fetch events (used for both initial load and AJAX)
function getUpcomingEvents($conn) {
    $today = date('Y-m-d');
    $sql = "SELECT event_id, event_name, start_date, end_date, start_time, end_time, location, description
            FROM schedule_events 
            WHERE start_date >= '$today' 
            ORDER BY start_date ASC, start_time ASC";
    $result = $conn->query($sql);

    $upcomingEvents = array();
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = array(
            'id' => $row['event_id'],
            'title' => $row['event_name'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'location' => $row['location'],
            'description' => $row['description']
        );
    }
    return $upcomingEvents;
}

// Handle AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode(getUpcomingEvents($conn));
    $conn->close();
    exit();
}

$upcomingEvents = getUpcomingEvents($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .event-card {
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Upcoming Events Display -->
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Upcoming Events</h1>
        
        <div id="events-container">
            <?php if (!empty($upcomingEvents)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="event-card bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-blue-200">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <?php echo date('F j, Y', strtotime($event['start_date'])); ?>
                                <?php if ($event['start_date'] != $event['end_date']): ?>
                                    to <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
                                <?php endif; ?>
                                <?php if (!empty($event['start_time']) && !empty($event['end_time'])): ?>
                                    <span class="ml-3">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                    </span>
                                <?php elseif (!empty($event['start_time'])): ?>
                                    <span class="ml-3">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($event['location'])): ?>
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($event['description'])): ?>
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-1">Description</h4>
                                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($event['description']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <h3 class="text-lg font-medium text-gray-900">No upcoming events</h3>
                    <p class="mt-1 text-gray-500">Check back later for scheduled events</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial fetch
        fetchEvents();
        
        // Set up polling every 30 seconds
        setInterval(fetchEvents, 30000);
    });

    function fetchEvents() {
        fetch('?ajax=1')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(events => {
                updateEventsDisplay(events);
            })
            .catch(error => {
                console.error('Error fetching events:', error);
            });
    }

    function updateEventsDisplay(events) {
        const eventsContainer = document.getElementById('events-container');
        
        if (events.length === 0) {
            eventsContainer.innerHTML = `
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <h3 class="text-lg font-medium text-gray-900">No upcoming events</h3>
                    <p class="mt-1 text-gray-500">Check back later for scheduled events</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        
        events.forEach(event => {
            html += `
                <div class="event-card bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-blue-200">
                    <h3 class="text-lg font-semibold text-gray-800">${escapeHtml(event.title)}</h3>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i class="far fa-calendar-alt mr-2"></i>
                        ${formatDate(event.start_date)}
                        ${event.start_date != event.end_date ? `to ${formatDate(event.end_date)}` : ''}
                        ${(event.start_time && event.end_time) ? `
                            <span class="ml-3">
                                <i class="far fa-clock mr-1"></i>
                                ${formatTime(event.start_time)} - ${formatTime(event.end_time)}
                            </span>
                        ` : (event.start_time ? `
                            <span class="ml-3">
                                <i class="far fa-clock mr-1"></i>
                                ${formatTime(event.start_time)}
                            </span>
                        ` : '')}
                    </div>
                    ${event.location ? `
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            ${escapeHtml(event.location)}
                        </div>
                    ` : ''}
                    ${event.description ? `
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Description</h4>
                            <p class="text-gray-600 text-sm">${escapeHtml(event.description)}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        
        html += '</div>';
        eventsContainer.innerHTML = html;
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        return new Date(`1970-01-01T${timeString}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    </script>
</body>
</html>