<?php ob_start(); ?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Dashboard with Sidebar and PHP Page Sections
  </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
      font-family: 'Inter', sans-serif;
    }
  </style>
 </head>
 <body class="bg-gray-100 min-h-screen flex flex-col">
  <div class="flex flex-1 overflow-hidden">
   <!-- Sidebar -->
   <aside aria-label="Sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col" id="sidebar">
    <div class="h-full flex flex-col flex-1">
     <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <div class="flex items-center space-x-3">
       <img alt="Sangguniang Kabataan logo, blue and white emblem with youth icon" class="h-10 w-auto" height="40" src="bgi/tupi_logo.png" width="40"/>
       <span class="font-semibold text-xl text-gray-800">
        Sangguniang Kabataan Youth
       </span>
      </div>
      <button aria-label="Close sidebar" class="md:hidden text-gray-600 hover:text-blue-600 focus:outline-none" id="sidebar-close-btn">
       <i class="fas fa-times fa-lg"></i>
      </button>
     </div>
     <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2 text-white font-medium bg-blue-700">
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=analytics">
       <i class="fas fa-chart-bar mr-3 w-5 text-center"></i>
       Analytics
      </a>
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=member">
       <i class="fas fa-users mr-3 w-5 text-center"></i>
       Members
      </a>
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=schedule_event">
       <i class="fas fa-calendar-alt mr-3 w-5 text-center"></i>
        Events
      </a>
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=handle_admin_status">
       <i class="fas fa-check-circle mr-3 w-5 text-center"></i>
       Approvals
      </a>
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=reports">
       <i class="fas fa-file-invoice mr-3 w-5 text-center"></i>
       Reports
      </a>
      <div class="relative">
       <button id="financial-dropdown-btn" aria-haspopup="true" aria-expanded="false" class="w-full flex items-center justify-between rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition focus:outline-none" type="button">
        <span class="flex items-center">
         <i class="fas fa-wallet mr-3 w-5 text-center"></i>
         Financial
        </span>
        <i class="fas fa-chevron-down text-white"></i>
       </button>
       <div id="financial-dropdown" class="hidden mt-1 ml-8 flex flex-col space-y-1">
        <a href="?page=annual_budget" class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition flex items-center">
         <i class="fas fa-file-invoice-dollar mr-3 w-5 text-center"></i>
         Annual Budget
        </a>
        <a href="?page=abyip" class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition flex items-center">
         <i class="fas fa-file-alt mr-3 w-5 text-center"></i>
         ABYIP
        </a>
        <a href="?page=funds" class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition flex items-center">
         <i class="fas fa-file-alt mr-3 w-5 text-center"></i>
         Funds
        </a>
       </div>
      </div>
      <a class="block rounded-md px-3 py-2 hover:bg-blue-100 hover:text-blue-700 transition" href="?page=settings">
       <i class="fas fa-cog mr-3 w-5 text-center"></i>
       Settings
      </a>
     </nav>
     <div class="border-t bg-blue-700 border-gray-200 p-4">
      <a class="flex items-center rounded-md px-3 py-2 text-white hover:bg-red-100 hover:text-red-700 transition font-medium" href="?page=logout">
       <i class="fas fa-sign-out-alt mr-3 w-5 text-center text-red-700"></i>
       Logout
      </a>
     </div>
    </div>
   </aside>
   <!-- Main content area -->
   <div class="flex flex-col flex-1 md:pl-64">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-20">
     <div class="w-full px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
       <div class="flex items-center space-x-3">
        <button aria-label="Open sidebar" class="text-gray-600 hover:text-blue-600 focus:outline-none md:hidden" id="sidebar-open-btn">
         <i class="fas fa-bars fa-lg"></i>
        </button>
        <span class="font-semibold text-xl text-gray-800" id="page-title">
         <?php
          $page = isset($_GET['page']) ? $_GET['page'] : 'analytics';
          $page_titles = [
            'analytics' => 'Analytics',
            'member' => 'Members',
            'schedule_event' => 'Events',
            'handle_admin_status' => 'Approvals',
            'reports' => 'Reports',
            'annual_budget' => 'Annual Budget',
            'abyip' => 'ABYIP',
            'funds' => 'Funds',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'sms_messaging' => 'SMS Messaging',
            'financial' => 'Financial'
          ];
          echo isset($page_titles[$page]) ? $page_titles[$page] : ucfirst($page);
         ?>
        </span>
       </div>
       <div class="flex items-center space-x-4">
        <button aria-label="User menu" class="flex items-center space-x-2 focus:outline-none">
         <img alt="User avatar, a round image with initials JD on a green background" class="rounded-full" height="32" src="https://storage.googleapis.com/a1aa/image/19c3e437-e48c-42c9-09b1-a51fff4bfcf5.jpg" width="32"/>
         <span class="hidden sm:block text-gray-700 font-medium">
          Haizel Jane
         </span>
         <i class="fas fa-chevron-down text-gray-600 hidden"></i>
        </button>
       </div>
      </div>
     </div>
    </nav>
    <main class="flex-1 bg-gray-300 overflow-y-auto w-full p-0 m-0 max-w-full">
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
                    'financial',
                    'annual_budget',
                    'abyip',
                    'funds',
                    'reports',
                    'settings',
                    'logout'
                ];
                
                if (in_array($page, $allowed_pages)) {
                    include "pages/{$page}.php";
                } else {
                    echo "
                Page not found
                ";
                }
            } else {
                include "pages/analytics.php"; // Set default to analytics.php
            }
            ?>
    </main>
   </div>
  </div>
  <script>
   const sidebar = document.getElementById('sidebar');
    const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');

    function openSidebar() {
      sidebar.classList.remove('-translate-x-full');
      document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
      sidebar.classList.add('-translate-x-full');
      document.body.style.overflow = '';
    }

    sidebarOpenBtn.addEventListener('click', openSidebar);
    sidebarCloseBtn.addEventListener('click', closeSidebar);

    // Close sidebar on window resize if desktop
    window.addEventListener('resize', () => {
      if(window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        document.body.style.overflow = '';
      } else {
        sidebar.classList.add('-translate-x-full');
      }
    });

    // Dropdown toggle for Financial menu
    const financialBtn = document.getElementById('financial-dropdown-btn');
    const financialDropdown = document.getElementById('financial-dropdown');

    financialBtn.addEventListener('click', (event) => {
      event.stopPropagation();
      const isHidden = financialDropdown.classList.contains('hidden');
      if (isHidden) {
        financialDropdown.classList.remove('hidden');
        financialBtn.setAttribute('aria-expanded', 'true');
      } else {
        financialDropdown.classList.add('hidden');
        financialBtn.setAttribute('aria-expanded', 'false');
      }
    });

    // Prevent dropdown from closing when clicking links inside (optional: only if you want to prevent navigation)
    financialDropdown.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', function(e) {
        // e.preventDefault(); // Uncomment this line if you want to prevent navigation
        // Optionally, load content via AJAX here
      });
    });

  </script>
 </body>
</html>
<?php ob_end_flush(); ?>