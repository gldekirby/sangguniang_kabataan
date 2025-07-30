<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon" />
    <title>Pending Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
        rel="stylesheet"
    />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Modal max width and width */
        #modal > div.bg-white {
            max-width: 95vw !important;
            width: 95vw !important;
            max-height: 90vh;
            overflow-y: auto;
        }
        /* Modal content container flex */
        #modalContent > form > div {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        /* Fieldsets sizing and flex */
        #modalContent fieldset {
            flex: 1 1 280px;
            min-width: 280px;
            max-width: 320px;
            border-radius: 0.375rem; /* rounded */
            border-width: 1px;
            padding: 1rem;
            box-sizing: border-box;
        }
        /* Fieldset legend styling */
        #modalContent fieldset legend {
            font-weight: 600;
            color: #4f46e5; /* indigo-700 */
            margin-bottom: 0.75rem;
            padding: 0 0.25rem;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #modalContent > form > div {
                flex-direction: column;
            }
            #modalContent fieldset {
                max-width: 100%;
                min-width: auto;
            }
        }
        /* Images inside documents fieldset */
        #modalContent fieldset img {
            border-radius: 0.375rem;
            max-height: 128px;
            object-fit: cover;
            width: 100%;
            margin-top: 0.5rem;
            margin-bottom: 0.75rem;
        }
        /* Overlay and modal styles from member.php */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }
        .overlay-content {
            background-color: white;
            padding: 0;
            border-radius: 8px;
            width: 100%;
            max-width: 700px;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            position: relative;
            font-size: 0.9rem;
        }
        .member-header {
            flex: 1 1 240px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 240px;
            padding: 20px 16px 20px 20px;
        }
        .member-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .member-details {
            flex: 2 1 520px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-width: 240px;
            padding: 20px 20px 20px 0;
            gap: 0;
        }
        .detail-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
            width: 120px;
            flex-shrink: 0;
            font-size: 0.85rem;
        }
        .detail-value {
            flex: 1;
            word-break: break-word;
            font-size: 0.85rem;
        }
        .close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
            background: none;
            border: none;
            line-height: 1;
            transition: color 0.2s ease-in-out;
            z-index: 10;
        }
        .close-btn:hover {
            color: #ef4444;
        }
        .overlay-photo {
            margin-top: 0;
            width: 160px;
            height: 160px;
            border-radius: 100%;
            object-fit: cover;
        }
        @media (max-width: 768px) {
            .overlay-content {
                flex-direction: column;
                padding: 15px 15px 15px 15px;
            }
            .member-header, .member-details {
                min-width: 100%;
                flex: none;
                padding: 10px 0;
            }
            .member-details {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .member-header h2 {
                font-size: 1.25rem;
            }
            .overlay-photo {
                width: 140px;
                height: 140px;
                margin-top: 0;
            }
        }
        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 4px;
        }
        .status-dot.active {
            background-color: #10B981;
        }
        .status-dot.inactive {
            background-color: #EF4444;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col">
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

<header class="bg-white shadow">
    <div
        class="container mx-auto px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between">
        <h1 class="text-3xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-users text-indigo-600"></i>Pending Applications
        </h1>
        <form method="GET" action="" class="mt-3 md:mt-0">
            <label for="search" class="sr-only">Search applications</label>
            <div class="relative text-gray-600 focus-within:text-gray-400">
                <input type="search" name="search" id="search" placeholder="Search by name, email, or contact" value="<?php echo htmlspecialchars($search); ?>" class="py-2 pl-10 pr-4 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-64" onkeydown="if(event.key === 'Enter'){this.form.submit();}"/>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </form>
    </div>
</header>

<main class="bg-white container mx-auto px-4 py-6 flex-grow h-full max-w-full">
    <?php if ($total_rows === 0): ?>
    <div
        class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded"
        role="alert">
        <p class="font-bold">
            <i class="fas fa-exclamation-triangle"></i> No pending applications found.
        </p>
        <p>Try adjusting your search or check back later.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto shadow-md rounded bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-indigo-600">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Contact
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"
                    >
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                    </td>
                    <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:underline"
                    >
                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                            <?php echo htmlspecialchars($row['email']); ?>
                        </a>
                    </td>
                    <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:underline"
                    >
                        <a href="tel:<?php echo htmlspecialchars($row['contact_number']); ?>">
                            <?php echo htmlspecialchars($row['contact_number']); ?>
                        </a>
                    </td>
                    <td
                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2"
                    >
                        <form
                            method="POST"
                            class="inline"
                            onsubmit="return confirm('Are you sure you want to approve this application?');"
                        >
                            <input
                                type="hidden"
                                name="member_id"
                                value="<?php echo $row['member_id']; ?>"
                            />
                            <button
                                type="submit"
                                name="action"
                                value="approve"
                                aria-label="Approve application for <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                                class="inline-flex items-center px-3 py-1 rounded bg-green-600 hover:bg-green-700 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                            >
                                <i class="fas fa-check mr-1"></i> Approve
                            </button>
                        </form>
                        <form
                            method="POST"
                            class="inline"
                            onsubmit="return confirm('Are you sure you want to deny this application?');"
                        >
                            <input
                                type="hidden"
                                name="member_id"
                                value="<?php echo $row['member_id']; ?>"
                            />
                            <button
                                type="submit"
                                name="action"
                                value="deny"
                                aria-label="Deny application for <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                                class="inline-flex items-center px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                            >
                                <i class="fas fa-times mr-1"></i> Deny
                            </button>
                        </form>
                        <button
                            type="button"
                            onclick="openModal(<?php echo $row['member_id']; ?>)"
                            aria-label="View details for <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>"
                            class="inline-flex items-center px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        >
                            <i class="fas fa-info-circle mr-1"></i> Details
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav class="mt-6 flex justify-center" aria-label="Pagination">
        <ul class="inline-flex items-center -space-x-px text-sm">
            <li>
                <a
                    href="?page=1<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                    class="px-3 py-1 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>"
                    aria-label="First page"
                    ><i class="fas fa-angle-double-left"></i
                ></a>
            </li>
            <li>
                <a
                    href="?page=<?php echo max(1, $page - 1) . ($search !== '' ? '&search=' . urlencode($search) : ''); ?>"
                    class="px-3 py-1 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>"
                    aria-label="Previous page"
                    ><i class="fas fa-angle-left"></i
                ></a>
            </li>
            <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                if ($start > 1) {
                    echo '<li><span class="px-3 py-1 border border-gray-300 bg-white text-gray-700">...</span></li>';
                }
                for ($i = $start; $i <= $end; $i++): ?>
            <li>
                <a
                    href="?page=<?php echo $i . ($search !== '' ? '&search=' . urlencode($search) : ''); ?>"
                    class="px-3 py-1 border border-gray-300 <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>"
                    aria-label="Page <?php echo $i; ?>"
                    ><?php echo $i; ?></a
                >
            </li>
            <?php endfor;
                if ($end < $total_pages) {
                    echo '<li><span class="px-3 py-1 border border-gray-300 bg-white text-gray-700">...</span></li>';
                }
                ?>
            <li>
                <a
                    href="?page=<?php echo min($total_pages, $page + 1) . ($search !== '' ? '&search=' . urlencode($search) : ''); ?>"
                    class="px-3 py-1 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 <?php echo $page == $total_pages ? 'pointer-events-none opacity-50' : ''; ?>"
                    aria-label="Next page"
                    ><i class="fas fa-angle-right"></i
                ></a>
            </li>
            <li>
                <a
                    href="?page=<?php echo $total_pages . ($search !== '' ? '&search=' . urlencode($search) : ''); ?>"
                    class="px-3 py-1 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 <?php echo $page == $total_pages ? 'pointer-events-none opacity-50' : ''; ?>"
                    aria-label="Last page"
                    ><i class="fas fa-angle-double-right"></i
                ></a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</main>

<!-- Member Details Overlay -->
<div id="modal" class="overlay fixed inset-0 bg-black bg-opacity-70 hidden items-center z-50" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalContent" tabindex="-1">
    <div class="overlay-content bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4" role="document">
        <button onclick="closeModal()" aria-label="Close details modal" class="close-btn absolute top-3 right-3 text-gray-600 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-times fa-lg"></i>
        </button>
        <div class="member-header flex flex-col items-center text-center min-w-[210px] flex-1">
            <img id="modalPhoto" src="" class="overlay-photo" alt="Member photo" />
            <h2 id="modalTitle" class="font-bold mt-2 mb-2 text-2xl text-indigo-700"></h2>
            <div class="status-display mb-4" id="modalStatus"></div>
        </div>
        <div class="member-details grid grid-cols-1 md:grid-cols-2 gap-0 flex-2 min-w-[220px]" id="modalContent">
            <!-- Fieldsets will be injected here by JS -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store member data in JS object for modal display
    const members = {};
    <?php
    foreach ($members_data as $member_id => $row) {
        // Safely encode all fields for JS
        $fields = [
            'first_name', 'middle_name', 'last_name', 'age', 'gender', 'contact_number', 'email', 'username', 'created_at', 'status', 'civil_status', 'work_status', 'position', 'dob', 'place_of_birth', 'social_media', 'street', 'barangay', 'city', 'province', 'parent_last_name', 'parent_first_name', 'parent_middle_name', 'parent_relationship', 'parent_contact', 'school', 'education_level', 'year_level', 'emergency_name', 'emergency_relationship', 'emergency_contact', 'id_photo', 'birth_certificate', 'residence_certificate', 'last_updated', 'status1'
        ];
        echo "members[$member_id] = {\n";
        foreach ($fields as $field) {
            $val = isset($row[$field]) ? htmlspecialchars($row[$field], ENT_QUOTES) : '';
            // Escape backticks and backslashes for JS template literals
            $val = str_replace(['\\', '`', '${'], ['\\\\', '\\`', '\\${'], $val);
            echo "  $field: `$val`,\n";
        }
        // Compose full name
        $full_name = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $full_name = str_replace(['\\', '`', '${'], ['\\\\', '\\`', '\\${'], $full_name);
        echo "  name: `$full_name`\n";
        echo "};\n";
    }
    ?>
    window.openModal = function(memberId) {
        const member = members[memberId];
        if (!member) return;
        document.getElementById('modalPhoto').src = member.id_photo && member.id_photo.trim() !== '' ? member.id_photo : 'https://placehold.co/160x160/png?text=No+Photo';
        document.getElementById('modalPhoto').alt = `Photo of ${member.name}`;
        document.getElementById('modalTitle').textContent = member.name;
        const statusText = member.status && member.status.trim() !== '' ? member.status : 'Unknown';
        const statusClass = member.status && member.status.toLowerCase() === 'active' ? 'active' : 'inactive';
        document.getElementById('modalStatus').innerHTML = `<span class="status-dot ${statusClass}" aria-hidden="true"></span> ${statusText}`;
        let html = '';
        html += `<fieldset><legend>Personal Information</legend>
            <div class='detail-row'><div class='detail-label'>Address:</div><div class='detail-value'>${member.street || 'N/A'}, ${member.barangay || 'N/A'}, ${member.city || 'N/A'}, ${member.province || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Age:</div><div class='detail-value'>${member.age || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Gender:</div><div class='detail-value'>${member.gender || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Date of Birth:</div><div class='detail-value'>${member.dob || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Place of Birth:</div><div class='detail-value'>${member.place_of_birth || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Civil Status:</div><div class='detail-value'>${member.civil_status || 'N/A'}</div></div>
        </fieldset>`;
        html += `<fieldset><legend>Contact Information</legend>
            <div class='detail-row'><div class='detail-label'>Contact:</div><div class='detail-value'>${member.contact_number || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Email:</div><div class='detail-value'>${member.email || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Social Media:</div><div class='detail-value'>${member.social_media || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Emergency Contact:</div><div class='detail-value'>${member.emergency_name || 'N/A'} (${member.emergency_relationship || 'N/A'}) - ${member.emergency_contact || 'N/A'}</div></div>
        </fieldset>`;
        html += `<fieldset><legend>Family & Education</legend>
            <div class='detail-row'><div class='detail-label'>Parent Details:</div><div class='detail-value'>${member.parent_first_name || ''} ${member.parent_middle_name || ''} ${member.parent_last_name || ''} (${member.parent_relationship || 'N/A'}) - ${member.parent_contact || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>School:</div><div class='detail-value'>${member.school || 'N/A'} (${member.education_level || 'N/A'}, ${member.year_level || 'N/A'})</div></div>
        </fieldset>`;
        html += `<fieldset><legend>Work & Registration</legend>
            <div class='detail-row'><div class='detail-label'>Work Status:</div><div class='detail-value'>${member.work_status || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Position:</div><div class='detail-value'>${member.position || 'N/A'}</div></div>
            <div class='detail-row'><div class='detail-label'>Registered:</div><div class='detail-value'>${member.created_at || 'N/A'}</div></div>
        </fieldset>`;
        document.getElementById('modalContent').innerHTML = html;
        const modal = document.getElementById('modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        modal.setAttribute('aria-hidden', 'false');
        modal.focus();
        trapFocus(modal);
    };

    // Accessibility: trap focus inside overlay
    let focusableElementsString = 'a[href], area[href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';
    let firstTabStop = null;
    let lastTabStop = null;
    let overlayFocused = false;

    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(focusableElementsString);
        if (focusableElements.length === 0) return;
        firstTabStop = focusableElements[0];
        lastTabStop = focusableElements[focusableElements.length - 1];
        element.addEventListener('keydown', handleTrapFocus);
        firstTabStop.focus();
        overlayFocused = true;
    }

    function releaseFocusTrap() {
        const overlay = document.getElementById('modal');
        overlay.removeEventListener('keydown', handleTrapFocus);
        overlayFocused = false;
    }

    function handleTrapFocus(e) {
        if (!overlayFocused) return;
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstTabStop) {
                    e.preventDefault();
                    lastTabStop.focus();
                }
            } else {
                if (document.activeElement === lastTabStop) {
                    e.preventDefault();
                    firstTabStop.focus();
                }
            }
        }
        if (e.key === 'Escape') {
            closeModal();
        }
    }

    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    window.closeModal = function() {
        const modal = document.getElementById('modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        modal.setAttribute('aria-hidden', 'true');
        releaseFocusTrap();
    }
});
</script>
<?php
$stmt->close();
$conn->close();
?>
</body>
</html>