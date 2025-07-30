<?php
session_start();
include '../config.php';

// Check if logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Verify user is logged in
    if (isset($_SESSION['member_id'])) {
        $member_id = $_SESSION['member_id'];

        try {
            // Update the user's status to inactive
            $query = "UPDATE members SET status = 'inactive' WHERE member_id = ?";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $member_id);
                $stmt->execute();
                
                if ($stmt->affected_rows === 0) {
                    error_log("No rows affected when updating status for member_id: $member_id");
                }
                
                $stmt->close();
            } else {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: login_member.php");
    exit();
}

// Verify user is logged in and active for normal dashboard access
if (!isset($_SESSION['member_id'])) {
    header("Location: login_member.php");
    exit();
}

$query = "SELECT status, id_photo FROM members WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['member_id']);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if ($member['status'] !== 'active') {
    session_destroy();
    header("Location: login_member.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/boostercopy/public/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-image: url('bgi/blurred.png');
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        /* Custom CSS for scrolling */
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        #main-content {
            overflow-y: auto;
            height: calc(100vh - 4rem);
        }
        
        /* Floating notification panel */
        #notificationPanel {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            max-height: 500px;
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transform: translateY(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        #notificationPanel.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .notification-container {
            position: relative;
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Notification item styling */
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f9fafb;
        }
        
        .notification-item.unread {
            background-color: #f0f9ff;
        }
        
        .notification-time {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        /* Improved sidebar styling */
        #sidebar {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        #sidebar nav {
            flex-grow: 1;
        }
        
        #sidebar nav ul {
            padding: 0;
            margin: 0;
        }
        
        #sidebar nav ul li {
            list-style: none;
            margin-bottom: 0.25rem;
        }
        
        #sidebar nav ul li a {
            display: block;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0.375rem;
        }
        
        #sidebar nav ul li a:hover {
            background-color: #1d4ed8;
            transform: translateX(5px);
        }
        
        #sidebar nav ul li a.active {
            background-color: #1e40af;
            font-weight: 500;
            transform: translateX(10px);
        }
        
        #sidebar nav ul li a.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: white;
            border-radius: 0 2px 2px 0;
        }
        
        /* Logout button styling */
        #sidebar .logout-container {
            padding: 4rem 0;
        }

        /* Spinner styles */
        .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top-color: #3498db;
        animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Ensure dashboard content can fade */
        #main-content {
            transition: opacity 0.5s ease;
        }
        
        /* Close button for notification panel */
        .close-notification {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            color: #6b7280;
        }
        
        .close-notification:hover {
            color: #1f2937;
        }
        
        /* Clear notifications button */
        .clear-notifications {
            display: block;
            width: 100%;
            padding: 8px;
            text-align: center;
            background-color: #f3f4f6;
            color: #3b82f6;
            font-weight: 500;
            border-radius: 0 0 8px 8px;
        }
        
        .clear-notifications:hover {
            background-color: #e5e7eb;
        }
    </style>
    <script>
        // JavaScript to toggle the sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('-translate-x-full');
            mainContent.classList.toggle('lg:ml-64');
        }
        
        // Function to set active state for sidebar links
        function setActiveSidebarLink() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 'upcoming_events'; // Default to 'upcoming_events' if no page is specified
            const sidebarLinks = document.querySelectorAll('#sidebar nav ul li a');
            
            // Reset all active states
            sidebarLinks.forEach(link => link.classList.remove('active'));
            financialSubLinks.forEach(link => link.classList.remove('active'));
            
            // Check main links
            sidebarLinks.forEach(link => {
                const linkUrl = new URL(link.href, window.location.origin);
                const linkPage = linkUrl.searchParams.get('page');
                
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        }
            
        
        // Initialize active state when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setActiveSidebarLink();
            
            // Also set active state when navigating back/forward
            window.addEventListener('popstate', setActiveSidebarLink);
            
            // Check for notifications periodically
            setInterval(() => {
                fetch('check_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        updateNotificationBadge(data.unread_count);
                    })
                    .catch(error => console.error('Error checking notifications:', error));
            }, 30000); // Check every 30 seconds
        });
    </script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md p-4 flex justify-between items-center fixed w-full z-10">
        <div class="flex items-center space-x-4">
            <button onclick="toggleSidebar()" class="text-gray-700 focus:outline-none lg:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
            <img src="bgi/tupi_logo.png" alt="Logo" class="h-10 w-auto">
            <div class="logo text-xl font-bold text-blue-600">Sangguniang Kabataan Youth Management</div>
        </div>
        <div class="header-right flex items-center space-x-4">
            <p class="text-gray-700 font-semibold">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <img src="<?php echo $member['id_photo']; ?>" alt="Profile Photo" class="h-10 w-10 rounded-full">
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex pt-18 lg:pt-16 transition-all duration-300 ease-in-out">
        <!-- Sidebar -->
        <aside id="sidebar" class="pt-10 bg-blue-800 text-white w-60 p-4 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out fixed lg:relative h-full">
            <h2 class="text-lg font-semibold mb-4">Navigation</h2> <!-- Added label -->
            <nav>
                <ul class="space-y-1">
                    <li>
                        <a href="?page=upcoming_events" class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300">
                            <i class="fas fa-calendar-alt mr-2"></i> <!-- Upcoming Events Icon -->
                            Upcoming Events
                        </a>
                    </li>
                    <h2 class="text-lg font-semibold mb-4">Setting</h2> <!-- Added label -->
                    <li>
                        <a href="?page=profile" class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300">
                            <i class="fas fa-user mr-2"></i> <!-- Profile Icon -->
                            Profile
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Profile Photo and Logout Link -->
            <div class="flex items-center mt-4"> <!-- Added margin-top for spacing -->
                <div class="logout-container ml-2"> <!-- Added margin-left for spacing -->
                    <a href="?action=logout" class="block p-2 hover:bg-blue-700 rounded transition-all duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i> <!-- Logout Icon -->
                        Logout
                    </a>
                </div>
            </div>
        </aside>
        <!-- Main Section -->
        <main id="main-content" class="flex-1 p-8 transition-all duration-300 ease-in-out">
            <?php
            include '../config.php';
            
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
                $allowed_pages = [
                    'upcoming_events', 
                    'profile', 
                    'receiver',
                ];
                
                if (in_array($page, $allowed_pages)) {
                    include "member_pages/{$page}.php";
                } else {
                    echo "<h1 class='text-2xl font-bold text-red-600'>Page not found</h1>";
                }
            } else {
                include "member_pages/upcoming_events.php"; // Set default to analytics.php
            }
            ?>

            <?php
            // Removed calendar-related code
            ?>
        </main>
    </div>

    <!-- Add this right before the closing </body> tag -->
    <div id="logoutOverlay" class="fixed inset-0 bg-opacity-0 flex items-center justify-center z-50 pointer-events-none transition-opacity duration-500">
        <div class=" p-8 rounded-lg text-center max-w-md w-full opacity-0 transform scale-95 transition-all duration-500">
            <div class="flex justify-center mb-4">
                <div class="spinner"></div>
            </div>
            <h2 class="text-xl font-bold mb-2">Goodbye...</h2>
            <p class="text-gray-600">Securing your session</p>
        </div>
    </div>

    <script>
        // Update the logout click handler to this:
        document.querySelector('.logout-container a').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show the overlay with fade-in
            const overlay = document.getElementById('logoutOverlay');
            overlay.classList.remove('pointer-events-none');
            overlay.classList.remove('bg-opacity-0');
            overlay.querySelector('div').classList.remove('opacity-0');
            overlay.querySelector('div').classList.remove('scale-95');
            
            // Fade out the entire dashboard
            document.getElementById('main-content').style.transition = 'opacity 1.0s ease';
            document.getElementById('main-content').style.opacity = '0';
            
            // Redirect to logout script after animations start
            setTimeout(() => {
                window.location.href = '?action=logout';
            }, 2000);
        });
    </script>
    <script>
        // Update active state when clicking sidebar links
        document.querySelectorAll('#sidebar nav ul li a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't prevent default behavior - let the page load normally
                
                // Update active state after a short delay to allow page load
                setTimeout(setActiveSidebarLink, 100);
            });
        });
        
        // Also handle financial submenu links
        document.querySelectorAll('#financial-menu li a').forEach(link => {
            link.addEventListener('click', function(e) {
                setTimeout(setActiveSidebarLink, 100);
            });
        });
    </script>
</body>
</html>