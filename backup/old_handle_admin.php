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
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Pending Member List - SK Youth
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   main {
      flex-grow: 1;
      margin-top: 0.9rem;
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
  </style>
 </head>
 <body class="bg-gray-50 min-h-screen flex flex-col">
  <main class="flex-grow">
   <h2 class="text-2xl font-semibold text-gray-900 mb-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    Pending Member Applications
   </h2>
   <form action="" class="mb-6 max-w-md mx-auto px-4 sm:px-6 lg:px-8" id="searchForm" method="GET">
    <label class="sr-only" for="search">
     Search members
    </label>
    <div class="relative text-gray-400 focus-within:text-gray-600">
     <input autocomplete="off" class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="search" name="search" placeholder="Search by name, email, or contact number" type="search" value=""/>
     <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
      <i class="fas fa-search">
      </i>
     </div>
    </div>
   </form>
   <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <table class="min-w-full divide-y divide-gray-200" id="membersTable">
     <thead class="bg-blue-50">
      <tr>
       <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" scope="col">
        Member
       </th>
       <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" scope="col">
        Email
       </th>
       <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" scope="col">
        Contact Number
       </th>
       <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider" scope="col">
        Date Applied
       </th>
       <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wider" scope="col">
        Actions
       </th>
      </tr>
     </thead>
     <tbody class="divide-y divide-gray-100" id="membersTbody">
      <tr>
       <td class="px-4 py-6 text-center text-gray-500" colspan="6" id="noResultsRow">
        No pending member applications found.
       </td>
      </tr>
     </tbody>
    </table>
   </div>
  </main>
  <footer class="bg-white border-t border-gray-200 py-4 text-center text-gray-500 text-sm">
   © 2024 Sangguniang Kabataan Youth Portal. All rights reserved.
  </footer>
  <!-- Modal -->
  <div aria-hidden="true" aria-labelledby="modalTitle" aria-modal="true" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" id="memberModal" role="dialog">
   <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto modal-content flex flex-col">
    <header class="flex justify-between items-center border-b border-gray-200 px-6 py-4 sticky top-0 bg-white z-10">
     <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">
      Member Details
     </h3>
     <button aria-label="Close modal" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" id="modalCloseBtn">
      <i class="fas fa-times fa-lg">
      </i>
     </button>
    </header>
    <section class="px-6 py-4 space-y-6 text-gray-700">
     <div class="flex flex-col sm:flex-row sm:space-x-6">
      <img alt="Member profile picture" class="w-32 h-32 rounded-full object-cover border border-gray-300 mx-auto sm:mx-0 flex-shrink-0" height="160" id="modalProfileImage" loading="lazy" src="https://storage.googleapis.com/a1aa/image/eea901b6-845c-47cb-82a1-6f73ee747b98.jpg" width="160"/>
      <div class="flex-1 mt-4 sm:mt-0">
       <h4 class="text-xl font-semibold text-gray-900 truncate" id="modalFullName">
        Full Name
       </h4>
       <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 mt-3 text-sm max-w-full">
        <div>
         <strong>
          Member ID:
         </strong>
         <span class="break-words" id="modalMemberId">
         </span>
        </div>
        <div>
         <strong>
          First Name:
         </strong>
         <span class="break-words" id="modalFirstName">
         </span>
        </div>
        <div>
         <strong>
          Middle Name:
         </strong>
         <span class="break-words" id="modalMiddleName">
         </span>
        </div>
        <div>
         <strong>
          Last Name:
         </strong>
         <span class="break-words" id="modalLastName">
         </span>
        </div>
        <div>
         <strong>
          Age:
         </strong>
         <span class="break-words" id="modalAge">
         </span>
        </div>
        <div>
         <strong>
          Gender:
         </strong>
         <span class="break-words" id="modalGender">
         </span>
        </div>
        <div>
         <strong>
          Contact Number:
         </strong>
         <span class="break-words" id="modalContact">
         </span>
        </div>
        <div>
         <strong>
          Email:
         </strong>
         <span class="break-words" id="modalEmail">
         </span>
        </div>
        <div>
         <strong>
          Username:
         </strong>
         <span class="break-words" id="modalUsername">
         </span>
        </div>
        <div>
         <strong>
          Status (Online/Offline):
         </strong>
         <span class="break-words" id="modalStatus">
         </span>
        </div>
        <div>
         <strong>
          Civil Status:
         </strong>
         <span class="break-words" id="modalCivilStatus">
         </span>
        </div>
        <div>
         <strong>
          Work Status:
         </strong>
         <span class="break-words" id="modalWorkStatus">
         </span>
        </div>
        <div>
         <strong>
          Position:
         </strong>
         <span class="break-words" id="modalPosition">
         </span>
        </div>
        <div>
         <strong>
          Date of Birth:
         </strong>
         <span class="break-words" id="modalDob">
         </span>
        </div>
        <div>
         <strong>
          Place of Birth:
         </strong>
         <span class="break-words" id="modalPlaceOfBirth">
         </span>
        </div>
        <div>
         <strong>
          Social Media:
         </strong>
         <span class="break-words" id="modalSocialMedia">
         </span>
        </div>
        <div>
         <strong>
          Street:
         </strong>
         <span class="break-words" id="modalStreet">
         </span>
        </div>
        <div>
         <strong>
          Barangay:
         </strong>
         <span class="break-words" id="modalBarangay">
         </span>
        </div>
        <div>
         <strong>
          City:
         </strong>
         <span class="break-words" id="modalCity">
         </span>
        </div>
        <div>
         <strong>
          Province:
         </strong>
         <span class="break-words" id="modalProvince">
         </span>
        </div>
        <div>
         <strong>
          Parent Last Name:
         </strong>
         <span class="break-words" id="modalParentLastName">
         </span>
        </div>
        <div>
         <strong>
          Parent First Name:
         </strong>
         <span class="break-words" id="modalParentFirstName">
         </span>
        </div>
        <div>
         <strong>
          Parent Middle Name:
         </strong>
         <span class="break-words" id="modalParentMiddleName">
         </span>
        </div>
        <div>
         <strong>
          Parent Relationship:
         </strong>
         <span class="break-words" id="modalParentRelationship">
         </span>
        </div>
        <div>
         <strong>
          Parent Contact:
         </strong>
         <span class="break-words" id="modalParentContact">
         </span>
        </div>
        <div>
         <strong>
          School:
         </strong>
         <span class="break-words" id="modalSchool">
         </span>
        </div>
        <div>
         <strong>
          Education Level:
         </strong>
         <span class="break-words" id="modalEducationLevel">
         </span>
        </div>
        <div>
         <strong>
          Year Level:
         </strong>
         <span class="break-words" id="modalYearLevel">
         </span>
        </div>
        <div>
         <strong>
          Emergency Name:
         </strong>
         <span class="break-words" id="modalEmergencyName">
         </span>
        </div>
        <div>
         <strong>
          Emergency Relationship:
         </strong>
         <span class="break-words" id="modalEmergencyRelationship">
         </span>
        </div>
        <div>
         <strong>
          Emergency Contact:
         </strong>
         <span class="break-words" id="modalEmergencyContact">
         </span>
        </div>
        <div>
         <strong>
          ID Photo:
         </strong>
         <span class="block mt-1" id="modalIdPhoto">
         </span>
        </div>
        <div>
         <strong>
          Birth Certificate:
         </strong>
         <span class="block mt-1" id="modalBirthCertificate">
         </span>
        </div>
        <div>
         <strong>
          Residence Certificate:
         </strong>
         <span class="block mt-1" id="modalResidenceCertificate">
         </span>
        </div>
        <div>
         <strong>
          Created At:
         </strong>
         <span class="break-words" id="modalCreatedAt">
         </span>
        </div>
        <div>
         <strong>
          Last Updated:
         </strong>
         <span class="break-words" id="modalLastUpdated">
         </span>
        </div>
        <div>
         <strong>
          Application Status:
         </strong>
         <span class="break-words" id="modalStatus1">
         </span>
        </div>
       </div>
      </div>
     </div>
     <div class="mt-6 flex flex-wrap gap-3 justify-end">
      <form class="inline" id="modalApproveForm" method="POST" onsubmit="return confirm('Are you sure you want to approve this member?');">
       <input id="modalApproveMemberId" name="member_id" type="hidden" value=""/>
       <input name="action" type="hidden" value="approve"/>
       <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500" type="submit">
        <i class="fas fa-check mr-2">
        </i>
        Approve
       </button>
      </form>
      <form class="inline" id="modalDenyForm" method="POST" onsubmit="return confirm('Are you sure you want to deny this member?');">
       <input id="modalDenyMemberId" name="member_id" type="hidden" value=""/>
       <input name="action" type="hidden" value="deny"/>
       <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500" type="submit">
        <i class="fas fa-times mr-2">
        </i>
        Deny
       </button>
      </form>
     </div>
    </section>
   </div>
  </div>
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
     return d.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });
   }

   function createMemberRow(member) {
     const tr = document.createElement("tr");
     tr.className = "hover:bg-blue-50 transition";

     // Member cell
     const tdMember = document.createElement("td");
     tdMember.className = "px-4 py-4 flex items-center space-x-3 min-w-[220px]";
     const img = document.createElement("img");
     img.alt = `Profile picture of ${member.first_name} ${member.last_name}`;
     img.className = "h-12 w-12 rounded-full object-cover border border-gray-300 flex-shrink-0";
     img.loading = "lazy";
img.src = member.id_photo && member.id_photo !== "" ? (member.id_photo.includes('uploads/') ? member.id_photo : '../../uploads/id_photos/' + member.id_photo) : "https://placehold.co/64x64/png?text=No+Photo";
     tdMember.appendChild(img);
     const divInfo = document.createElement("div");
     divInfo.className = "flex flex-col min-w-0";
     const spanName = document.createElement("span");
     spanName.className = "text-gray-900 font-semibold truncate";
     spanName.textContent = `${member.first_name} ${member.last_name}`;
     divInfo.appendChild(spanName);
     const spanAddress = document.createElement("span");
     spanAddress.className = "text-gray-500 text-sm truncate";
     const addressParts = [member.street, member.barangay, member.city, member.province].filter(Boolean).map(s => s.trim());
     spanAddress.textContent = addressParts.length ? addressParts.join(", ") : "N/A";
     spanAddress.title = spanAddress.textContent;
     divInfo.appendChild(spanAddress);
     tdMember.appendChild(divInfo);
     tr.appendChild(tdMember);

     // Email cell
     const tdEmail = document.createElement("td");
     tdEmail.className = "px-4 py-4 text-gray-700 text-sm break-words max-w-xs truncate";
     tdEmail.title = member.email || "N/A";
     tdEmail.textContent = member.email || "N/A";
     tr.appendChild(tdEmail);

     // Contact cell
     const tdContact = document.createElement("td");
     tdContact.className = "px-4 py-4 text-gray-700 text-sm whitespace-nowrap";
     tdContact.textContent = member.contact_number || "N/A";
     tr.appendChild(tdContact);

     // Date applied cell
     const tdDate = document.createElement("td");
     tdDate.className = "px-4 py-4 text-gray-700 text-sm whitespace-nowrap";
     tdDate.textContent = formatDate(member.created_at);
     tr.appendChild(tdDate);

     // Actions cell
     const tdActions = document.createElement("td");
     tdActions.className = "px-4 py-4 text-center space-x-1 whitespace-nowrap min-w-[180px]";

     // View button
     const btnView = document.createElement("button");
     btnView.type = "button";
     btnView.className = "inline-flex items-center px-2 py-1 border border-gray-300 rounded-md text-xs font-semibold text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500";
     btnView.title = "View Details";
     btnView.setAttribute("aria-label", `View details of member ${member.first_name} ${member.last_name}`);
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
     btnApprove.className = "inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500";
     btnApprove.setAttribute("aria-label", `Approve member ${member.first_name} ${member.last_name}`);
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
     btnDeny.className = "inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500";
     btnDeny.setAttribute("aria-label", `Deny member ${member.first_name} ${member.last_name}`);
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
       data.forEach(member => {
         membersTbody.appendChild(createMemberRow(member));
       });
     }
   }

   function filterMembers(query) {
     query = query.trim().toLowerCase();
     if (!query) return membersData;
     return membersData.filter(member => {
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
       return val !== undefined && val !== null && val !== '' ? val : 'N/A';
     }

     // Fill modal fields
     document.getElementById("modalFullName").textContent = show(memberData.first_name) + " " + show(memberData.last_name);
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
     document.getElementById("modalDob").textContent = memberData.dob ? new Date(memberData.dob).toLocaleDateString() : 'N/A';
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
     idPhotoSpan.innerHTML = memberData.id_photo && memberData.id_photo !== ''
       ? `<img src="${memberData.id_photo.includes('uploads/') ? memberData.id_photo : '/uploads/id_photos/' + memberData.id_photo}" alt="ID Photo of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300" />`
       : 'N/A';

     const birthCertSpan = document.getElementById("modalBirthCertificate");
     birthCertSpan.innerHTML = memberData.birth_certificate && memberData.birth_certificate !== ''
       ? `<img src="${memberData.birth_certificate.includes('uploads/') ? memberData.birth_certificate : '/uploads/birth_certs/' + memberData.birth_certificate}" alt="Birth Certificate of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300" />`
       : 'N/A';

     const residenceCertSpan = document.getElementById("modalResidenceCertificate");
     residenceCertSpan.innerHTML = memberData.residence_certificate && memberData.residence_certificate !== ''
       ? `<img src="${memberData.residence_certificate.includes('uploads/') ? memberData.residence_certificate : '/uploads/residence_certs/' + memberData.residence_certificate}" alt="Residence Certificate of ${show(memberData.first_name)} ${show(memberData.last_name)}" class="max-w-full h-auto rounded border border-gray-300" />`
       : 'N/A';

     document.getElementById("modalCreatedAt").textContent = memberData.created_at ? new Date(memberData.created_at).toLocaleString() : 'N/A';
     document.getElementById("modalLastUpdated").textContent = memberData.last_updated ? new Date(memberData.last_updated).toLocaleString() : 'N/A';
     document.getElementById("modalStatus1").textContent = show(memberData.status1);

     // Profile image placeholder (could be replaced with real image if available)
     document.getElementById("modalProfileImage").src =
       memberData.id_photo && memberData.id_photo !== ''
         ? (memberData.id_photo.includes('uploads/') ? memberData.id_photo : '/uploads/id_photos/' + memberData.id_photo)
         : "https://placehold.co/160x160/png?text=No+Photo";

     // Set member_id for approve/deny forms
     document.getElementById("modalApproveMemberId").value = memberData.member_id;
     document.getElementById("modalDenyMemberId").value = memberData.member_id;

     // Show modal
     modal.classList.remove("hidden");
     modal.classList.add("flex");

     // Trap focus inside modal for accessibility
     trapFocus(modal);
   }

   modalCloseBtn.addEventListener("click", closeModal);
   modal.addEventListener("click", (e) => {
     if (e.target === modal) {
       closeModal();
     }
   });

   document.addEventListener("keydown", (e) => {
     if (e.key === "Escape" && !modal.classList.contains("hidden")) {
       closeModal();
     }
   });

   function closeModal() {
     modal.classList.add("hidden");
     modal.classList.remove("flex");
   }

   // Focus trap helper
   function trapFocus(element) {
     const focusableElements = element.querySelectorAll(
       'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]'
     );
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
  </script>
  <!-- Image Floating Overlay -->
  <div id="imageOverlay" class="image-overlay" style="display:none;">
    <div class="image-overlay-content">
      <button id="imageOverlayClose" aria-label="Close image preview">&times;</button>
      <img id="imageOverlayImg" src="" alt="Full size image" />
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
      background-color: rgba(0,0,0,0.8);
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
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
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
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      transition: background-color 0.3s ease;
    }
    #imageOverlayClose:hover {
      background: #b91c1c;
    }
  </style>

  <script>
    // Image overlay logic
    const imageOverlay = document.getElementById('imageOverlay');
    const imageOverlayImg = document.getElementById('imageOverlayImg');
    const imageOverlayClose = document.getElementById('imageOverlayClose');

    function openImageOverlay(src, alt) {
      imageOverlayImg.src = src;
      imageOverlayImg.alt = alt || 'Full size image';
      imageOverlay.style.display = 'flex';
      imageOverlayClose.focus();
    }

    function closeImageOverlay() {
      imageOverlay.style.display = 'none';
      imageOverlayImg.src = '';
      imageOverlayImg.alt = '';
    }

    imageOverlayClose.addEventListener('click', closeImageOverlay);
    imageOverlay.addEventListener('click', (e) => {
      if (e.target === imageOverlay) {
        closeImageOverlay();
      }
    });

    // Add click listeners to images in modal
    function addImageOverlayListeners() {
      const idPhotoImg = document.querySelector('#modalIdPhoto img');
      const birthCertImg = document.querySelector('#modalBirthCertificate img');
      const residenceCertImg = document.querySelector('#modalResidenceCertificate img');

      if (idPhotoImg) {
        idPhotoImg.style.cursor = 'pointer';
        idPhotoImg.addEventListener('click', () => {
          openImageOverlay(idPhotoImg.src, idPhotoImg.alt);
        });
      }
      if (birthCertImg) {
        birthCertImg.style.cursor = 'pointer';
        birthCertImg.addEventListener('click', () => {
          openImageOverlay(birthCertImg.src, birthCertImg.alt);
        });
      }
      if (residenceCertImg) {
        residenceCertImg.style.cursor = 'pointer';
        residenceCertImg.addEventListener('click', () => {
          openImageOverlay(residenceCertImg.src, residenceCertImg.alt);
        });
      }
    }

    // Call addImageOverlayListeners after modal content is updated
    // Override openModal to add listeners after content update
    const originalOpenModal = openModal;
    openModal = function(memberData) {
      originalOpenModal(memberData);
      addImageOverlayListeners();
    };
  </script>
 </body>
</html>
