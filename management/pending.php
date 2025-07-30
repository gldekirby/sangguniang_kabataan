<?php
// Start output buffering and session before any HTML
ob_start();
session_start();
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sk_youth";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    if ($isAjax) { echo json_encode(['error' => 'Database connection failed']); exit(); }
    ob_end_flush();
    echo '<div class="text-red-600 font-semibold text-lg mb-4"><i class="fas fa-exclamation-triangle mr-2"></i>Database connection failed. Please try again later.</div>';
    exit();
}

// Check if member_id is set in session
if (!isset($_SESSION['member_id'])) {
    if ($isAjax) { echo json_encode(['error' => 'Not logged in']); exit(); }
    header("Location: login_member.php");
    ob_end_flush();
    exit();
}

// Fetch the user's status from the database
$member_id = $_SESSION['member_id'];
$stmt = $conn->prepare("SELECT status1 FROM members WHERE member_id = ?");
$stmt->bind_param("i", $member_id); // Assuming member_id is an integer
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($isAjax) {
  echo json_encode(['status' => $status]);
  exit();
}
ob_end_flush();
?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Application Status
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <script>
   // Real-time polling for status
    function fetchStatus() {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", window.location.href, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.onload = function () {
        if (xhr.status === 200) {
          try {
            var data = JSON.parse(xhr.responseText);
            if (data.status) {
              updateStatusUI(data.status);
            } else if (data.error) {
              updateStatusUI("error", data.error);
            }
          } catch (e) {
            // Not JSON, ignore
          }
        }
      };
      xhr.send();
    }
    function updateStatusUI(status, errorMsg) {
      var container = document.getElementById("status-container");
      if (!container) return;
      if (status === "pending") {
        container.innerHTML = `<div class="text-yellow-600 flex flex-col items-center">
          <i class="fas fa-hourglass-half fa-5x mb-6 animate-pulse"></i>
          <h1 class="text-3xl font-extrabold mb-3 tracking-wide">Application Under Review</h1>
          <p class="text-gray-700 max-w-md text-center leading-relaxed">
            Your application is still under review. Please wait for admin approval. We appreciate your patience.
          </p>
        </div>`;
      } else if (status === "denied") {
        container.innerHTML = `<div class="text-red-600 flex flex-col items-center">
          <i class="fas fa-times-circle fa-5x mb-6 animate-shake"></i>
          <h1 class="text-3xl font-extrabold mb-3 tracking-wide">Application Denied</h1>
          <p class="text-gray-700 max-w-md text-center leading-relaxed">
            Your application has been denied. Please contact the admin for more details.
          </p>
          <a class="mt-8 inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="mailto:admin@youthsk.org" aria-label="Contact Admin via email">
            Contact Admin
          </a>
        </div>`;
        setTimeout(function () {
          window.location.href = "login_member.php";
        }, 5000);
      } else if (status === "approved") {
        container.innerHTML = `<div class="text-green-600 flex flex-col items-center">
          <i class="fas fa-check-circle fa-5x mb-6 animate-pulse"></i>
          <h1 class="text-3xl font-extrabold mb-3 tracking-wide">Application Approved</h1>
          <p class="text-gray-700 max-w-md text-center leading-relaxed">
            Your application has been approved. You can now access all features of the platform. Welcome aboard!
          </p>
          <a class="mt-8 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="login_member.php" aria-label="Go to Dashboard">
            Go to Dashboard
          </a>
        </div>`;
        setTimeout(function () {
          window.location.href = "login_member.php";
        }, 5000);
      } else {
        container.innerHTML = `<div class="text-gray-800 flex flex-col items-center">
          <i class="fas fa-exclamation-circle fa-5x mb-6 text-gray-400"></i>
          <h1 class="text-3xl font-extrabold mb-3 tracking-wide">Unexpected Error</h1>
          <p class="text-gray-600 max-w-md text-center leading-relaxed">${
            errorMsg ||
            "An unexpected error occurred. Please contact support for assistance."
          }</p>
          <a class="mt-8 inline-block bg-gray-700 hover:bg-gray-900 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="mailto:support@youthsk.org" aria-label="Contact Support via email">
            Contact Support
          </a>
        </div>`;
      }
    }
    document.addEventListener("DOMContentLoaded", function () {
      fetchStatus();
      setInterval(fetchStatus, 3000); // Poll every 3 seconds
    });
  </script>
  <style>
   @keyframes shake {
      0%,
      100% {
        transform: translateX(0);
      }
      20%,
      60% {
        transform: translateX(-8px);
      }
      40%,
      80% {
        transform: translateX(8px);
      }
    }
    .animate-shake {
      animation: shake 0.8s ease-in-out infinite;
    }
  </style>
 </head>
 <body class="bg-gradient-to-tr from-indigo-50 via-white to-indigo-50 min-h-screen flex items-center justify-center px-4">
  <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl p-10 sm:p-12 text-center">
   <img alt="Illustration of a young person checking application status on a digital device, modern flat style with bright colors and friendly atmosphere" class="mx-auto mb-8 w-36 h-36 sm:w-40 sm:h-40 object-contain" decoding="async" fetchpriority="high" height="160" loading="lazy" src="https://storage.googleapis.com/a1aa/image/33be7621-283b-47be-9923-fce64d7496fb.jpg" width="160"/>
   <div class="select-none" id="status-container">
    <?php
      // Initial HTML for non-AJAX load
      if ($status === 'pending') {
          echo '<div class="text-yellow-600 flex flex-col items-center">
    <i class="fas fa-hourglass-half fa-5x mb-6 animate-pulse">
    </i>
    <h1 class="text-3xl font-extrabold mb-3 tracking-wide">
     Application Under Review
    </h1>
    <p class="text-gray-700 max-w-md text-center leading-relaxed">
     Your application is still under review. Please wait for admin approval. We appreciate your patience.
    </p>
   </div>
   ';
      } elseif ($status === 'denied') {
          echo '
   <div class="text-red-600 flex flex-col items-center">
    <i class="fas fa-times-circle fa-5x mb-6 animate-shake">
    </i>
    <h1 class="text-3xl font-extrabold mb-3 tracking-wide">
     Application Denied
    </h1>
    <p class="text-gray-700 max-w-md text-center leading-relaxed">
     Your application has been denied. Please contact the admin for more details.
    </p>
    <a aria-label="Contact Admin via email" class="mt-8 inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="mailto:admin@youthsk.org">
     Contact Admin
    </a>
   </div>
   <script>
    setTimeout(function(){window.location.href = "login_member.php";}, 5000);
   </script>
   ';
      } elseif ($status === 'approved') {
          echo '
   <div class="text-green-600 flex flex-col items-center">
    <i class="fas fa-check-circle fa-5x mb-6 animate-pulse">
    </i>
    <h1 class="text-3xl font-extrabold mb-3 tracking-wide">
     Application Approved
    </h1>
    <p class="text-gray-700 max-w-md text-center leading-relaxed">
     Your application has been approved. You can now access all features of the platform. Welcome aboard!
    </p>
    <a aria-label="Go to Dashboard" class="mt-8 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="login_member.php">
     Go to Dashboard
    </a>
   </div>
   <script>
    setTimeout(function(){window.location.href = "login_member.php";}, 5000);
   </script>
   ';
      } else {
          echo '
   <div class="text-gray-800 flex flex-col items-center">
    <i class="fas fa-exclamation-circle fa-5x mb-6 text-gray-400">
    </i>
    <h1 class="text-3xl font-extrabold mb-3 tracking-wide">
     Unexpected Error
    </h1>
    <p class="text-gray-600 max-w-md text-center leading-relaxed">
     An unexpected error occurred. Please contact support for assistance.
    </p>
    <a aria-label="Contact Support via email" class="mt-8 inline-block bg-gray-700 hover:bg-gray-900 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition duration-300" href="mailto:support@youthsk.org">
     Contact Support
    </a>
   </div>
   ';
      }
      ?>
  </div>
 </body>
</html>
