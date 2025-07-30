<?php
require 'C:/xampp/htdocs/www.sangguniang_kabataan.com/sms/vendor/autoload.php';

use AndroidSmsGateway\Client;
use AndroidSmsGateway\Domain\Message;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "youth_sk";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approval or denial
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = intval($_POST['member_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'deny') {
        $status = 'denied';
    } else {
        $status = 'pending';
    }

    $stmt = $conn->prepare("UPDATE members SET status1 = ? WHERE member_id = ?");
    $stmt->bind_param("si", $status, $member_id);
    $stmt->execute();
    $stmt->close();

    // Send SMS notification based on status
    $sms_username = "KWBUN-";
    $sms_password = "2342Gldekirby@21";

    // Get member contact number and first name
    $contact_stmt = $conn->prepare("SELECT contact_number, first_name FROM members WHERE member_id = ?");
    $contact_stmt->bind_param("i", $member_id);
    $contact_stmt->execute();
    $contact_result = $contact_stmt->get_result();
    if ($contact_result && $contact_result->num_rows > 0) {
        $member = $contact_result->fetch_assoc();
        $numberInput = $member['contact_number'];
        $firstName = $member['first_name'];

        // Format and validate number
        $number = "+63" . ltrim($numberInput, '0');

        if (preg_match('/^\+639\d{9}$/', $number)) {
            $client = new Client($sms_username, $sms_password);

            if ($status === 'approved') {
                $messageContent = "Hello $firstName, your application has been approved by SK Chairman. Thank you!";
            } elseif ($status === 'denied') {
                $messageContent = "Hello $firstName, we regret to inform you that your application has been denied by SK Chairman. Please try again next time.";
            } else {
                $messageContent = "";
            }

            if ($messageContent !== "") {
                $message = new Message($messageContent, [$number]);
                try {
                    $client->Send($message);
                } catch (Exception $e) {
                    // Log error or handle silently
                    error_log("SMS sending failed: " . $e->getMessage());
                }
            }
        }
    }
    $contact_stmt->close();
}

// Pagination variables
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = "";
$params = [];
$param_types = "";
if ($search !== '') {
    $search_sql = " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR contact_number LIKE ?) ";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
    $param_types = "ssss";
}

// Count total pending applications for pagination
$count_sql = "SELECT COUNT(*) as total FROM members WHERE status1 = 'pending' $search_sql";
$count_stmt = $conn->prepare($count_sql);
if ($search !== '') {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = max(1, ceil($total_rows / $limit));
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Fetch pending applications with search and pagination
$sql = "SELECT * FROM members WHERE status1 = 'pending' $search_sql ORDER BY member_id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($search !== '') {
    $param_types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $bind_names = [];
    $bind_names[] = $param_types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Collect all member data for modal
$members_data = [];
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    $members_data[$row['member_id']] = $row;
}
$result->data_seek(0);
?>
<html class="scroll-smooth" lang="en">
 <head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Pending Member List - SK Youth</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
    main {
      flex-grow: 1;
      max-width: 100%;
      background-color: #f9fafb;
    }
    /* Scrollbar for modal content */
    .modal-content::-webkit-scrollbar {
      width: 8px;
    }
    .modal-content::-webkit-scrollbar-thumb {
      background-color: rgba(107, 114, 128, 0.5);
      border-radius: 4px;
    }
    /* Remove default list styles inside table cells */
    ul {
      padding-left: 1.25rem; /* Tailwind pl-5 */
      margin: 0;
      list-style-type: disc;
    }
    /* Fieldset legend styling */
    fieldset {
      border: 1px solid #d1d5db; /* Tailwind gray-300 */
      border-radius: 0.375rem; /* Tailwind rounded-md */
      padding: 1rem 1.5rem 1.5rem 1.5rem;
      margin-bottom: 1.5rem;
    }
    legend {
      font-weight: 600;
      font-size: 1.125rem; /* Tailwind text-lg */
      color: #1e40af; /* Tailwind blue-900 */
      padding: 0 0.5rem;
      width: auto;
    }
    /* Horizontal layout for fieldset content */
    .fieldset-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-top: 0.75rem;
    }
    .fieldset-item {
      flex: 1 1 45%;
      min-width: 200px;
    }
    .fieldset-item strong {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      color: #6b7280; /* Tailwind gray-500 */
    }
    .fieldset-item span {
      margin-left: 0.25rem;
      color: #374151; /* Tailwind gray-700 */
      word-break: break-word;
    }
    @media (max-width: 640px) {
      .fieldset-row {
        flex-direction: column;
      }
      .fieldset-item {
        flex: 1 1 100%;
      }
    }
  </style>
 </head>
 <body class="bg-gray-50 min-h-screen flex flex-col font-inter">
  <header class="bg-white shadow border-b border-gray-200 w-full mx-auto">
   <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <div class="flex items-center space-x-3">
      <h1 class="text-xl font-semibold text-gray-900 select-none flex items-center">
      <i class="fas fa-users mr-2 text-blue-600"></i>SK Youth Portal
     </h1>
    </div>
    <nav aria-label="Primary Navigation" class="hidden md:flex space-x-6 text-gray-700 font-medium">
     <a class="hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded hidden" href="#">
      <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
     </a>
     <a class="hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded hidden" href="#">
      <i class="fas fa-user-friends mr-1"></i>Members
     </a>
     <a class="hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded hidden" href="#">
      <i class="fas fa-cog mr-1"></i>Settings
     </a>
    </nav>
    <button aria-expanded="false" aria-label="Open menu" class="md:hidden text-gray-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" id="mobileMenuButton">
     <i class="fas fa-bars fa-lg"></i>
    </button>
   </div>
   <form action="" aria-label="Search members" class=" mx-auto max-w-md sm:max-w-lg" id="searchForm" method="GET" role="search">
    <label class="sr-only" for="search">Search members</label>
    <div class="relative text-gray-400 focus-within:text-gray-600">
     <input aria-describedby="search-desc" autocomplete="off" class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base" id="search" name="search" placeholder="Search by name, email, or contact number" type="search" value=""/>
     <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
      <i aria-hidden="true" class="fas fa-search"></i>
     </div>
    </div>
    <p class="sr-only" id="search-desc">
     Type to filter the list of pending members
    </p>
   </form>
   <nav aria-label="Mobile Primary Navigation" class="md:hidden bg-white border-t border-gray-200 hidden w-full mx-auto" id="mobileMenu">
    <a class="block px-4 py-3 text-gray-700 hover:bg-blue-50 focus:outline-none focus:bg-blue-100" href="#">
     <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
    </a>
    <a class="block px-4 py-3 text-gray-700 hover:bg-blue-50 focus:outline-none focus:bg-blue-100" href="#">
     <i class="fas fa-user-friends mr-2"></i>Members
    </a>
    <a class="block px-4 py-3 text-gray-700 hover:bg-blue-50 focus:outline-none focus:bg-blue-100" href="#">
     <i class="fas fa-cog mr-2"></i>Settings
    </a>
   </nav>
  </header>
  <main class="flex-grow w-full h-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
   <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200 w-full mx-auto">
    <table aria-label="Pending member applications" class="min-w-full divide-y divide-gray-200" id="membersTable" role="table">
     <thead class="bg-blue-50">
      <tr role="row">
       <th aria-sort="none" class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" role="columnheader" scope="col" tabindex="0">
        <i class="fas fa-user mr-1 text-blue-600"></i>Member
       </th>
       <th aria-sort="none" class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" role="columnheader" scope="col" tabindex="0">
        <i class="fas fa-envelope mr-1 text-blue-600"></i>Email
       </th>
       <th aria-sort="none" class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" role="columnheader" scope="col" tabindex="0">
        <i class="fas fa-phone mr-1 text-blue-600"></i>Contact Number
       </th>
       <th aria-sort="none" class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" role="columnheader" scope="col" tabindex="0">
        <i class="fas fa-calendar-alt mr-1 text-blue-600"></i>Date Applied
       </th>
       <th aria-sort="none" class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wider" role="columnheader" scope="col" tabindex="0">
        <i class="fas fa-tools mr-1 text-blue-600"></i>Actions
       </th>
      </tr>
     </thead>
     <tbody class="divide-y divide-gray-100" id="membersTbody" role="rowgroup">
      <tr role="row">
       <td class="px-4 py-6 text-center text-gray-500" colspan="6" id="noResultsRow" role="cell">
        <i class="fas fa-info-circle mr-2"></i>No pending member applications found.
       </td>
      </tr>
     </tbody>
    </table>
   </div>
  </main>
  <!-- Modal -->
  <div aria-hidden="true" aria-labelledby="modalTitle" aria-modal="true" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" id="memberModal" role="dialog">
   <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content flex flex-col" role="document">
    <header class="flex justify-between items-center border-b border-gray-200 px-6 py-4 sticky top-0 bg-white z-10">
     <h3 class="text-lg font-semibold text-gray-900 flex items-center" id="modalTitle">
      <i class="fas fa-user-circle mr-2 text-blue-600"></i>Member Details
     </h3>
     <button aria-label="Close modal" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" id="modalCloseBtn" type="button">
      <i class="fas fa-times fa-lg"></i>
     </button>
    </header>
    <section class="px-6 py-4 space-y-6 text-gray-700">
     <div class="flex flex-col sm:flex-row sm:space-x-6">
      <img alt="Member profile picture" class="w-32 h-32 rounded-full object-cover border border-gray-300 mx-auto sm:mx-0 flex-shrink-0 hidden" height="160" id="modalProfileImage" loading="lazy" src="https://storage.googleapis.com/a1aa/image/eea901b6-845c-47cb-82a1-6f73ee747b98.jpg" width="160"/>
      <div class="flex-1 mt-4 sm:mt-0">
       <h4 class="text-xl font-semibold text-gray-900 truncate flex items-center">
        <i class="fas fa-id-badge mr-2 text-blue-600"></i><span id="modalFullName">Full Name</span>
       </h4>
       <fieldset>
        <legend>Personal Information</legend>
        <div class="fieldset-row">
         <div class="fieldset-item hidden">
          <strong><i class="fas fa-hashtag"></i> Member ID:</strong>
          <span id="modalMemberId"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user"></i> First Name:</strong>
          <span id="modalFirstName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-edit"></i> Middle Name:</strong>
          <span id="modalMiddleName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user"></i> Last Name:</strong>
          <span id="modalLastName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-birthday-cake"></i> Age:</strong>
          <span id="modalAge"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-venus-mars"></i> Gender:</strong>
          <span id="modalGender"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-phone"></i> Contact Number:</strong>
          <span id="modalContact"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-envelope"></i> Email:</strong>
          <span id="modalEmail"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-circle"></i> Username:</strong>
          <span id="modalUsername"></span>
         </div>
         <div class="fieldset-item hidden">
          <strong><i class="fas fa-signal"></i> Status (Online/Offline):</strong>
          <span id="modalStatus"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-heart"></i> Civil Status:</strong>
          <span id="modalCivilStatus"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-briefcase"></i> Work Status:</strong>
          <span id="modalWorkStatus"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-tie"></i> Position:</strong>
          <span id="modalPosition"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-calendar-alt"></i> Date of Birth:</strong>
          <span id="modalDob"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-map-marker-alt"></i> Place of Birth:</strong>
          <span id="modalPlaceOfBirth"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-hashtag"></i> Social Media:</strong>
          <span id="modalSocialMedia"></span>
         </div>
        </div>
       </fieldset>
       <fieldset>
        <legend>Address</legend>
        <div class="fieldset-row">
         <div class="fieldset-item">
          <strong><i class="fas fa-road"></i> Street:</strong>
          <span id="modalStreet"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-home"></i> Barangay:</strong>
          <span id="modalBarangay"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-city"></i> City:</strong>
          <span id="modalCity"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-map"></i> Province:</strong>
          <span id="modalProvince"></span>
         </div>
        </div>
       </fieldset>
       <fieldset>
        <legend>Parent / Guardian Information</legend>
        <div class="fieldset-row">
         <div class="fieldset-item">
          <strong><i class="fas fa-user-friends"></i> Parent Last Name:</strong>
          <span id="modalParentLastName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-friends"></i> Parent First Name:</strong>
          <span id="modalParentFirstName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-friends"></i> Parent Middle Name:</strong>
          <span id="modalParentMiddleName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-tag"></i> Parent Relationship:</strong>
          <span id="modalParentRelationship"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-phone-alt"></i> Parent Contact:</strong>
          <span id="modalParentContact"></span>
         </div>
        </div>
       </fieldset>
       <fieldset>
        <legend>Education</legend>
        <div class="fieldset-row">
         <div class="fieldset-item">
          <strong><i class="fas fa-school"></i> School:</strong>
          <span id="modalSchool"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-graduation-cap"></i> Education Level:</strong>
          <span id="modalEducationLevel"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-layer-group"></i> Year Level:</strong>
          <span id="modalYearLevel"></span>
         </div>
        </div>
       </fieldset>
       <fieldset>
        <legend>Emergency Contact</legend>
        <div class="fieldset-row">
         <div class="fieldset-item">
          <strong><i class="fas fa-user-shield"></i> Emergency Name:</strong>
          <span id="modalEmergencyName"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-user-tag"></i> Emergency Relationship:</strong>
          <span id="modalEmergencyRelationship"></span>
         </div>
         <div class="fieldset-item">
          <strong><i class="fas fa-phone-square-alt"></i> Emergency Contact:</strong>
          <span id="modalEmergencyContact"></span>
         </div>
        </div>
       </fieldset>
       <fieldset>
        <legend>Documents</legend>
        <div class="space-y-4 mt-2">
         <div>
          <strong><i class="fas fa-id-card"></i> ID Photo:</strong>
          <div class="mt-1" id="modalIdPhoto"></div>
         </div>
         <div>
          <strong><i class="fas fa-file-alt"></i> Birth Certificate:</strong>
          <div class="mt-1" id="modalBirthCertificate"></div>
         </div>
         <div>
          <strong><i class="fas fa-file-alt"></i> Residence Certificate:</strong>
          <div class="mt-1" id="modalResidenceCertificate"></div>
         </div>
        </div>
       </fieldset>
       <fieldset class="hidden">
        <legend class="hidden">Record Info</legend>
        <div class="fieldset-row">
         <div class="fieldset-item hidden">
          <strong><i class="fas fa-calendar-check"></i> Created At:</strong>
          <span id="modalCreatedAt"></span>
         </div>
         <div class="fieldset-item hidden">
          <strong><i class="fas fa-sync-alt"></i> Last Updated:</strong>
          <span id="modalLastUpdated"></span>
         </div>
         <div class="fieldset-item hidden">
          <strong><i class="fas fa-info-circle"></i> Application Status:</strong>
          <span id="modalStatus1"></span>
         </div>
        </div>
       </fieldset>
      </div>
     </div>
    </section>
   </div>
  </div>
<!-- Previous PHP code remains the same until the JavaScript section -->
<script>
    // Sample data array to simulate PHP data (replace with actual PHP data output)
    const membersData = [
      <?php
      $result->data_seek(0);
      while ($row = $result->fetch_assoc()) {
        $rowJson = json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        echo $rowJson . ",";
      }
      ?>
    ];

    const membersTbody = document.getElementById("membersTbody");
    const noResultsRow = document.getElementById("noResultsRow");
    const searchInput = document.getElementById("search");

    function formatDate(dateStr) {
      if (!dateStr) return "N/A";
      const d = new Date(dateStr);
      if (isNaN(d)) return "N/A";
      return d.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    }

    function createMemberRow(member) {
      const tr = document.createElement("tr");
      tr.className = "hover:bg-blue-50 transition";

      // Member cell with list
      const tdMember = document.createElement("td");
      tdMember.className = "px-4 py-4 min-w-[220px]";
      const ulMember = document.createElement("ul");
      ulMember.className = "space-y-1";
      // Profile image and name
      const liName = document.createElement("li");
      liName.className = "flex items-center space-x-2";
      const img = document.createElement("img");
      img.alt = `Profile picture of ${member.first_name} ${member.last_name}`;
      img.className =
        "h-10 w-10 rounded-full object-cover border border-gray-300 flex-shrink-0";
      img.loading = "lazy";
      img.src =
        member.id_photo && member.id_photo !== ""
          ? member.id_photo.includes("uploads/")
            ? member.id_photo
            : "../../uploads/id_photos/" + member.id_photo
          : "https://placehold.co/64x64/png?text=No+Photo";
      const spanName = document.createElement("span");
      spanName.className = "text-gray-900 font-semibold truncate";
      spanName.textContent = `${member.first_name} ${member.last_name}`;
      liName.appendChild(img);
      liName.appendChild(spanName);
      ulMember.appendChild(liName);
      // Address
      const liAddress = document.createElement("li");
      liAddress.className = "text-gray-500 text-sm flex items-center";
      const iconAddress = document.createElement("i");
      iconAddress.className = "fas fa-map-marker-alt mr-1";
      liAddress.appendChild(iconAddress);
      const addressParts = [member.street, member.barangay, member.city, member.province]
        .filter(Boolean)
        .map((s) => s.trim());
      liAddress.appendChild(
        document.createTextNode(addressParts.length ? addressParts.join(", ") : "N/A")
      );
      ulMember.appendChild(liAddress);
      tdMember.appendChild(ulMember);
      tr.appendChild(tdMember);

      // Email cell with list
      const tdEmail = document.createElement("td");
      tdEmail.className = "px-4 py-4 text-gray-700 text-sm break-words max-w-xs truncate";
      const ulEmail = document.createElement("ul");
      ulEmail.className = "space-y-1";
      const liEmail = document.createElement("li");
      liEmail.className = "flex items-center";
      const iconEmail = document.createElement("i");
      iconEmail.className = "fas fa-envelope mr-1 text-gray-500";
      liEmail.appendChild(iconEmail);
      liEmail.appendChild(document.createTextNode(member.email || "N/A"));
      ulEmail.appendChild(liEmail);
      tdEmail.appendChild(ulEmail);
      tr.appendChild(tdEmail);

      // Contact cell with list
      const tdContact = document.createElement("td");
      tdContact.className = "px-4 py-4 text-gray-700 text-sm whitespace-nowrap";
      const ulContact = document.createElement("ul");
      ulContact.className = "space-y-1";
      const liContact = document.createElement("li");
      liContact.className = "flex items-center";
      const iconContact = document.createElement("i");
      iconContact.className = "fas fa-phone mr-1 text-gray-500";
      liContact.appendChild(iconContact);
      liContact.appendChild(document.createTextNode(member.contact_number || "N/A"));
      ulContact.appendChild(liContact);
      tdContact.appendChild(ulContact);
      tr.appendChild(tdContact);

      // Date applied cell with list
      const tdDate = document.createElement("td");
      tdDate.className = "px-4 py-4 text-gray-700 text-sm whitespace-nowrap";
      const ulDate = document.createElement("ul");
      ulDate.className = "space-y-1";
      const liDate = document.createElement("li");
      liDate.className = "flex items-center";
      const iconDate = document.createElement("i");
      iconDate.className = "fas fa-calendar-alt mr-1 text-gray-500";
      liDate.appendChild(iconDate);
      liDate.appendChild(document.createTextNode(formatDate(member.created_at)));
      ulDate.appendChild(liDate);
      tdDate.appendChild(ulDate);
      tr.appendChild(tdDate);

      // Actions cell
      const tdActions = document.createElement("td");
      tdActions.className = "px-4 py-4 text-center space-x-1 whitespace-nowrap min-w-[180px]";

      // View button
      const btnView = document.createElement("button");
      btnView.type = "button";
      btnView.className =
        "inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-semibold text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500";
      btnView.title = "View Details";
      btnView.setAttribute(
        "aria-label",
        `View details of member ${member.first_name} ${member.last_name}`
      );
      btnView.innerHTML = '<i class="fas fa-eye"></i>';
      btnView.addEventListener("click", () => openModal(member));
      tdActions.appendChild(btnView);

      // Approve form
      const formApprove = document.createElement("form");
      formApprove.className = "inline";
      formApprove.method = "POST";
      formApprove.onsubmit = () => confirm("Are you sure you want to approve this member?");
      const inputApproveId = document.createElement("input");
      inputApproveId.type = "hidden";
      inputApproveId.name = "member_id";
      inputApproveId.value = member.member_id;
      const inputApproveAction = document.createElement("input");
      inputApproveAction.type = "hidden";
      inputApproveAction.name = "action";
      inputApproveAction.value = "approve";
      const btnApprove = document.createElement("button");
      btnApprove.type = "submit";
      btnApprove.className =
        "inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500";
      btnApprove.setAttribute(
        "aria-label",
        `Approve member ${member.first_name} ${member.last_name}`
      );
      btnApprove.innerHTML = '<i class="fas fa-check mr-1"></i> Approve';
      formApprove.appendChild(inputApproveId);
      formApprove.appendChild(inputApproveAction);
      formApprove.appendChild(btnApprove);
      tdActions.appendChild(formApprove);

      // Deny form
      const formDeny = document.createElement("form");
      formDeny.className = "inline";
      formDeny.method = "POST";
      formDeny.onsubmit = () => confirm("Are you sure you want to deny this member?");
      const inputDenyId = document.createElement("input");
      inputDenyId.type = "hidden";
      inputDenyId.name = "member_id";
      inputDenyId.value = member.member_id;
      const inputDenyAction = document.createElement("input");
      inputDenyAction.type = "hidden";
      inputDenyAction.name = "action";
      inputDenyAction.value = "deny";
      const btnDeny = document.createElement("button");
      btnDeny.type = "submit";
      btnDeny.className =
        "inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500";
      btnDeny.setAttribute(
        "aria-label",
        `Deny member ${member.first_name} ${member.last_name}`
      );
      btnDeny.innerHTML = '<i class="fas fa-times mr-1"></i> Deny';
      formDeny.appendChild(inputDenyId);
      formDeny.appendChild(inputDenyAction);
      formDeny.appendChild(btnDeny);
      tdActions.appendChild(formDeny);

      tr.appendChild(tdActions);

      return tr;
    }

    function renderTable(data) {
      membersTbody.innerHTML = "";
      if (data.length === 0) {
        noResultsRow.style.display = "";
        membersTbody.appendChild(noResultsRow);
      } else {
        noResultsRow.style.display = "none";
        data.forEach((member) => {
          membersTbody.appendChild(createMemberRow(member));
        });
      }
    }

    function filterMembers(query) {
      query = query.trim().toLowerCase();
      if (!query) return membersData;
      return membersData.filter((member) => {
        const fullName = (member.first_name + " " + member.last_name).toLowerCase();
        const email = (member.email || "").toLowerCase();
        const contact = (member.contact_number || "").toLowerCase();
        return fullName.includes(query) || email.includes(query) || contact.includes(query);
      });
    }

    searchInput.addEventListener("input", () => {
      const filtered = filterMembers(searchInput.value);
      renderTable(filtered);
    });

    // Initial render
    renderTable(membersData);

    // Modal open/close logic
    const modal = document.getElementById("memberModal");
    const modalCloseBtn = document.getElementById("modalCloseBtn");

    function openModal(memberData) {
      // Helper for fallback
      function show(val) {
        return val !== undefined && val !== null && val !== "" ? val : "N/A";
      }

      // Fill modal fields
      document.getElementById("modalFullName").textContent =
        show(memberData.first_name) + " " + show(memberData.last_name);
      document.getElementById("modalMemberId").textContent = show(memberData.member_id);
      document.getElementById("modalFirstName").textContent = show(memberData.first_name);
      document.getElementById("modalMiddleName").textContent = show(memberData.middle_name);
      document.getElementById("modalLastName").textContent = show(memberData.last_name);
      document.getElementById("modalAge").textContent = show(memberData.age);
      document.getElementById("modalGender").textContent = show(memberData.gender);
      document.getElementById("modalContact").textContent = show(memberData.contact_number);
      document.getElementById("modalEmail").textContent = show(memberData.email);
      document.getElementById("modalUsername").textContent = show(memberData.username);
      document.getElementById("modalStatus").textContent = show(memberData.status);
      document.getElementById("modalCivilStatus").textContent = show(memberData.civil_status);
      document.getElementById("modalWorkStatus").textContent = show(memberData.work_status);
      document.getElementById("modalPosition").textContent = show(memberData.position);
      document.getElementById("modalDob").textContent = memberData.dob
        ? new Date(memberData.dob).toLocaleDateString()
        : "N/A";
      document.getElementById("modalPlaceOfBirth").textContent = show(memberData.place_of_birth);
      document.getElementById("modalSocialMedia").textContent = show(memberData.social_media);
      document.getElementById("modalStreet").textContent = show(memberData.street);
      document.getElementById("modalBarangay").textContent = show(memberData.barangay);
      document.getElementById("modalCity").textContent = show(memberData.city);
      document.getElementById("modalProvince").textContent = show(memberData.province);
      document.getElementById("modalParentLastName").textContent = show(memberData.parent_last_name);
      document.getElementById("modalParentFirstName").textContent = show(memberData.parent_first_name);
      document.getElementById("modalParentMiddleName").textContent = show(memberData.parent_middle_name);
      document.getElementById("modalParentRelationship").textContent = show(memberData.parent_relationship);
      document.getElementById("modalParentContact").textContent = show(memberData.parent_contact);
      document.getElementById("modalSchool").textContent = show(memberData.school);
      document.getElementById("modalEducationLevel").textContent = show(memberData.education_level);
      document.getElementById("modalYearLevel").textContent = show(memberData.year_level);
      document.getElementById("modalEmergencyName").textContent = show(memberData.emergency_name);
      document.getElementById("modalEmergencyRelationship").textContent = show(memberData.emergency_relationship);
      document.getElementById("modalEmergencyContact").textContent = show(memberData.emergency_contact);

      // Render images for id_photo, birth_certificate, residence_certificate
      const idPhotoSpan = document.getElementById("modalIdPhoto");
      idPhotoSpan.innerHTML =
        memberData.id_photo && memberData.id_photo !== ""
          ? `<img src="${
              memberData.id_photo.includes("uploads/")
                ? memberData.id_photo
                : "/uploads/id_photos/" + memberData.id_photo
            }" alt="ID Photo of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300 cursor-pointer" />`
          : "N/A";

      const birthCertSpan = document.getElementById("modalBirthCertificate");
      birthCertSpan.innerHTML =
        memberData.birth_certificate && memberData.birth_certificate !== ""
          ? `<img src="${
              memberData.birth_certificate.includes("uploads/")
                ? memberData.birth_certificate
                : "/uploads/birth_certs/" + memberData.birth_certificate
            }" alt="Birth Certificate of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300 cursor-pointer" />`
          : "N/A";

      const residenceCertSpan = document.getElementById("modalResidenceCertificate");
      residenceCertSpan.innerHTML =
        memberData.residence_certificate && memberData.residence_certificate !== ""
          ? `<img src="${
              memberData.residence_certificate.includes("uploads/")
                ? memberData.residence_certificate
                : "/uploads/residence_certs/" + memberData.residence_certificate
            }" alt="Residence Certificate of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300 cursor-pointer" />`
          : "N/A";

      document.getElementById("modalCreatedAt").textContent = memberData.created_at
        ? new Date(memberData.created_at).toLocaleString()
        : "N/A";
      document.getElementById("modalLastUpdated").textContent = memberData.last_updated
        ? new Date(memberData.last_updated).toLocaleString()
        : "N/A";
      document.getElementById("modalStatus1").textContent = show(memberData.status1);

      // Profile image placeholder (could be replaced with real image if available)
      document.getElementById("modalProfileImage").src =
        memberData.id_photo && memberData.id_photo !== ""
          ? memberData.id_photo.includes("uploads/")
            ? memberData.id_photo
            : "/uploads/id_photos/" + memberData.id_photo
          : "https://placehold.co/160x160/png?text=No+Photo";

      // Show modal
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open

      // Trap focus inside modal for accessibility
      trapFocus(modal);
      
      // Add event listeners for images in modal
      addImageOverlayListeners();
    }

    function closeModal() {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto'; // Re-enable scrolling
    }

    modalCloseBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modal.style.display === 'flex') {
        closeModal();
      }
    });

    // Focus trap helper
    function trapFocus(element) {
      const focusableElements = element.querySelectorAll(
        'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]'
      );
      if (focusableElements.length === 0) return;
      const firstFocusable = focusableElements[0];
      const lastFocusable = focusableElements[focusableElements.length - 1];

      element.addEventListener("keydown", function (e) {
        if (e.key === "Tab") {
          if (e.shiftKey) {
            if (document.activeElement === firstFocusable) {
              e.preventDefault();
              lastFocusable.focus();
            }
          } else {
            if (document.activeElement === lastFocusable) {
              e.preventDefault();
              firstFocusable.focus();
            }
          }
        }
      });

      firstFocusable.focus();
    }

    // Image overlay logic
    const imageOverlay = document.getElementById("imageOverlay");
    const imageOverlayImg = document.getElementById("imageOverlayImg");
    const imageOverlayClose = document.getElementById("imageOverlayClose");

    function openImageOverlay(src, alt) {
      imageOverlayImg.src = src;
      imageOverlayImg.alt = alt || "Full size image";
      imageOverlay.style.display = "flex";
      imageOverlayClose.focus();
      document.body.style.overflow = 'hidden';
    }

    function closeImageOverlay() {
      imageOverlay.style.display = "none";
      imageOverlayImg.src = "";
      imageOverlayImg.alt = "";
      document.body.style.overflow = 'auto';
    }

    imageOverlayClose.addEventListener("click", closeImageOverlay);
    imageOverlay.addEventListener("click", (e) => {
      if (e.target === imageOverlay) {
        closeImageOverlay();
      }
    });

    // Add click listeners to images in modal
    function addImageOverlayListeners() {
      const idPhotoImg = document.querySelector("#modalIdPhoto img");
      const birthCertImg = document.querySelector("#modalBirthCertificate img");
      const residenceCertImg = document.querySelector("#modalResidenceCertificate img");

      if (idPhotoImg) {
        idPhotoImg.style.cursor = "pointer";
        idPhotoImg.addEventListener("click", () => {
          openImageOverlay(idPhotoImg.src, idPhotoImg.alt);
        });
      }
      if (birthCertImg) {
        birthCertImg.style.cursor = "pointer";
        birthCertImg.addEventListener("click", () => {
          openImageOverlay(birthCertImg.src, birthCertImg.alt);
        });
      }
      if (residenceCertImg) {
        residenceCertImg.style.cursor = "pointer";
        residenceCertImg.addEventListener("click", () => {
          openImageOverlay(residenceCertImg.src, residenceCertImg.alt);
        });
      }
    }

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById("mobileMenuButton");
    const mobileMenu = document.getElementById("mobileMenu");

    mobileMenuButton.addEventListener("click", () => {
      const expanded = mobileMenuButton.getAttribute("aria-expanded") === "true";
      mobileMenuButton.setAttribute("aria-expanded", !expanded);
      mobileMenu.classList.toggle("hidden");
    });
</script>
  <div aria-label="Image preview" aria-modal="true" class="image-overlay" id="imageOverlay" role="dialog" style="display:none;">
   <div class="image-overlay-content">
    <button aria-label="Close image preview" id="imageOverlayClose" type="button">
     ×
    </button>
    <img alt="Full size image" id="imageOverlayImg" src="" />
   </div>
  </div>
  <style>
    /* Image floating overlay styles */
    .image-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.8);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1100;
    }
    .image-overlay-content {
      position: relative;
      max-width: 90vw;
      max-height: 90vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .image-overlay-content img {
      max-width: 50%;
      max-height: 50%;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }
    #imageOverlayClose {
      position: absolute;
      top: -10px;
      right: -10px;
      background: #ef4444;
      border: none;
      color: white;
      font-size: 28px;
      font-weight: bold;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      cursor: pointer;
      line-height: 1;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
      transition: background-color 0.3s ease;
    }
    #imageOverlayClose:hover {
      background: #b91c1c;
    }
  </style>
  <!-- Removed duplicate image overlay logic and mobile menu toggle script to prevent redeclaration errors -->
 </body>
</html>