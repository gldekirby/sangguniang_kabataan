<?php ob_start(); ?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Admin Dashboard</title>
  <link href="bgi/tupi_logo.png" rel="icon" type="image/x-icon"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
   /* Financial menu styling */
        #financial-menu {
            pointer-events: auto;
            padding-left: 1rem;
            margin-top: 0.25rem;
        }
        
        #financial-menu li a {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        
        #financial-menu li a:hover {
            background-color: #3b82f6; /* Tailwind blue-500 */
        }
        
        #financial-menu li a.active {
            background-color: #2563eb; /* Tailwind blue-600 */
            font-weight: 700;
        }
        
        /* Logout container styling */
        #logout-container {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: auto;
        }
        
        #logout-container a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: white;
            font-weight: 600;
            border-radius: 0.375rem;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        
        #logout-container a:hover {
            background-color: #dc2626; /* Tailwind red-600 */
        }
        
        /* Financial dropdown button */
        .financial-button {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            cursor: pointer;
            font-weight: 600;
            color: white;
            border-radius: 0.375rem;
            background-color: transparent;
            border: none;
            transition: background-color 0.3s ease;
        }
        .financial-button:hover {
            background-color: #2563eb; /* Tailwind blue-600 */
        }
        .financial-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
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

        /* Main content layout fixes */
        .main-layout {
            height: 100vh;
            padding-top: 64px; /* Height of header */
            display: flex;
            overflow: hidden;
        }

        /* Main content fixes */ 
        #main-content {
            flex: 1;
            overflow-y: auto;
            height: calc(100vh - 64px);
            position: relative;
            transition: all 0.3s ease;
            background-color: #f3f4f6;
        }
        /* Header fixes */
        .main-header {
            height: 64px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }

        /* Sidebar flex container to push logout to bottom */
        #sidebar {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            height: 100%;
        }
  </style>
 </head>
 <body class="bg-gray-100">
  <!-- Header -->
  <header class="main-header bg-white shadow-md p-4 flex justify-between items-center">
   <div class="flex items-center space-x-4">
    <button aria-label="Toggle sidebar" class="text-gray-700 focus:outline-none lg:hidden" onclick="toggleSidebar()">
     <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M4 6h16M4 12h16m-7 6h7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      </path>
     </svg>
    </button>
    <img alt="Sangguniang Kabataan logo, blue and white emblem with youth icon" class="h-10 w-auto" height="40" src="bgi/tupi_logo.png" width="40"/>
    <div class="logo text-xl font-bold text-blue-600 select-none">
     Sangguniang Kabataan Youth Management
    </div>
   </div>
  </header>
  <!-- Main Layout -->
  <div class="main-layout">
   <!-- Sidebar -->
   <aside aria-label="Sidebar navigation" class="bg-blue-900 text-white w-60 p-4 transform -translate-x-full lg:translate-x-0 transition-transform duration-300" id="sidebar">
    <nav>
     <h2 class="text-lg font-semibold mb-4 select-none">Navigation</h2>
     <ul class="space-y-1">
      <li>
       <a class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300" href="?page=analytics" tabindex="0">
        <i class="fas fa-chart-line mr-2 w-5 text-white">
        </i>Analytics</a>
      </li>
      <li>
       <a class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300" href="?page=member" tabindex="0">
        <i class="fas fa-users mr-2 w-5 text-white">
        </i>Youth Members</a>
      </li>
      <li>
       <a class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300" href="?page=handle_admin_status" tabindex="0">
        <i class="fas fa-check-circle mr-2 w-5 text-white">
        </i>Approval Status</a>
      </li>
      <li>
       <a class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300" href="?page=schedule_event" tabindex="0">
        <i class="fas fa-calendar-plus mr-2 w-5 text-white">
        </i>Schedule Event</a>
      </li>
      <li>
       <a class="flex items-center p-2 hover:bg-blue-700 rounded transition-all duration-300" href="?page=reports" tabindex="0">
        <i class="fas fa-bug mr-2 w-5 text-white">
        </i>Reports</a>
      </li>
      <!-- Financial Dropdown -->
      <li class="relative">
       <button aria-controls="financial-menu" aria-expanded="false" class="financial-button flex items-center hover:bg-blue-700 rounded transition-all duration-300 w-full text-left" onclick="toggleFinancialMenu(event)" tabindex="0">
        <span class="flex-grow">Finance</span>
        <i class="fas fa-chevron-down ml-2 transition-transform duration-300" id="financial-arrow">
        </i>
       </button>
       <ul aria-label="Financial submenu" class="hidden pl-4 mt-1 space-y-1" id="financial-menu" role="menu">
        <li>
<a class="hover:bg-blue-600 rounded transition-all duration-300 flex items-center hidden" href="?page=funds" onclick="event.preventDefault(); toggleFinancialMenu(event, true); window.location.href='?page=funds'" role="menuitem" tabindex="-1">
 <i class="fas fa-file-alt mr-2 w-5 text-white"></i>Funds</a>
        </li>
        <li>
<a class="hover:bg-blue-600 rounded transition-all duration-300 flex items-center" href="?page=annual_budget" onclick="event.preventDefault(); toggleFinancialMenu(event, true); window.location.href='?page=annual_budget'" role="menuitem" tabindex="-1">
 <i class="fas fa-file-invoice mr-2 w-5 text-white"></i>Annual Budget</a>
        </li>
        <li>
<a class="hover:bg-blue-600 rounded transition-all duration-300 flex items-center hidden" href="?page=abyip" onclick="event.preventDefault(); toggleFinancialMenu(event, true); window.location.href='?page=abyip'" role="menuitem" tabindex="-1">
 <i class="fas fa-wallet mr-2 w-5 text-white"></i>ABYIP</a>
        </li>
       </ul>
      </li>
     </ul>
    </nav>
    <!-- Logout Link at the Bottom -->
    <div id="logout-container">
     <a class="flex items-center p-2 hover:bg-red-600 rounded transition-all duration-300" href="pages/logout.php" tabindex="0">
      <i class="fas fa-sign-out-alt mr-2 w-5"></i>Logout</a>
    </div>
   </aside>
   <!-- Main Content -->
   <main aria-label="Main content area" class="bg-white" id="main-content" role="main" tabindex="0">
    <?php
        // Check if user is logged in
        include '../config.php';
        
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            $allowed_pages = [
                'analytics', 
                'member', 
                'handle_admin_status',
                'schedule_event',
                'sms_messaging',
                'reports',
                'financial',
                'funds',
                'annual_budget',
                'abyip'
            ];
            
            if (in_array($page, $allowed_pages)) {
                include "pages/{$page}.php";
            } else {
                echo "<h1 class='text-2xl font-bold text-red-600'>
            Page not found
            ";
            }
        } else {
            include "pages/analytics.php"; // Set default to analytics.php
        }
    ?>
   </main>
  </div>
  <!-- Logout Overlay -->
  <div class="fixed inset-0 bg-opacity-0 flex items-center justify-center z-50 pointer-events-none transition-opacity duration-500" id="logoutOverlay">
   <div class="p-8 rounded-lg text-center max-w-md w-full opacity-0 transform scale-95 transition-all duration-500 bg-white shadow-lg">
    <div class="flex justify-center mb-4">
     <div aria-label="Loading spinner" class="spinner">
     </div>
    </div>
    <h2 class="text-xl font-bold mb-2">Goodbye...</h2>
    <p class="text-gray-600">Securing your session</p>
   </div>
  </div>
  <script>
   // JavaScript to toggle the sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            sidebar.classList.toggle('-translate-x-full');
            
            // Only toggle margin on mobile
            if (window.innerWidth < 1024) {
                mainContent.classList.toggle('ml-60');
            }
        }
        
        // Enhanced financial menu toggle function
        function toggleFinancialMenu(event, keepOpen = false) {
            event.stopPropagation();
            const menu = document.getElementById('financial-menu');
            const arrow = document.getElementById('financial-arrow');
            const button = event.currentTarget;
            
            if (keepOpen) {
                menu.classList.remove('hidden');
                arrow.classList.add('transform', 'rotate-180');
                button.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.toggle('hidden');
                arrow.classList.toggle('transform');
                arrow.classList.toggle('rotate-180');
                const expanded = menu.classList.contains('hidden') ? 'false' : 'true';
                button.setAttribute('aria-expanded', expanded);
            }
        }
        
        // Function to set active state for sidebar links
        function setActiveSidebarLink() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 'analytics';
            const sidebarLinks = document.querySelectorAll('#sidebar nav ul li a');
            const financialSubLinks = document.querySelectorAll('#financial-menu li a');
            
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
            
            // Check financial submenu items
            const financialSubItems = ['annual_budget', 'budgets', 'funds', 'abyip'];
            if (financialSubItems.includes(currentPage)) {
                // Highlight the parent financial link
                const financialButton = document.querySelector('#sidebar nav ul li button.financial-button');
                if (financialButton) {
                    financialButton.classList.add('active');
                    financialButton.setAttribute('aria-expanded', 'true');
                }
                
                // Highlight the specific submenu item
                financialSubLinks.forEach(link => {
                    const linkUrl = new URL(link.href, window.location.origin);
                    const linkPage = linkUrl.searchParams.get('page');
                    if (linkPage === currentPage) {
                        link.classList.add('active');
                    }
                });
                
                // Make sure financial menu is visible if on a subpage
                document.getElementById('financial-menu').classList.remove('hidden');
                document.getElementById('financial-arrow').classList.add('transform', 'rotate-180');
            }
        }
        
        // Handle logout click with fade-out animation
        document.querySelector('#logout-container a').addEventListener('click', function(e) {
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
            
            // Create iframe to trigger logout in background
            const iframe = document.createElement('iframe');
            iframe.src = 'pages/logout.php';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            // Redirect after animations complete
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        });

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
            
            // Auto-open financial menu if on financial page
            const financialPages = ['annual_budget', 'budgets', 'funds', 'abyip'];
            const currentPage = new URLSearchParams(window.location.search).get('page');
            if (financialPages.includes(currentPage)) {
                document.getElementById('financial-menu').classList.remove('hidden');
                document.getElementById('financial-arrow').classList.add('transform', 'rotate-180');
                const financialButton = document.querySelector('#sidebar nav ul li button.financial-button');
                if (financialButton) {
                    financialButton.setAttribute('aria-expanded', 'true');
                }
            }
        });

        // Update active state when clicking sidebar links
        document.querySelectorAll('#sidebar nav ul li a').forEach(link => {
            link.addEventListener('click', function(e) {
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
<?php ob_end_flush(); ?>