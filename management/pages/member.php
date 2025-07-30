<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Approved Members by District Cluster</title>
    <link rel="icon" href="bgi/tupi_logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }
        span.active {
            background-color: #10B981;
        }
        span.inactive {
            background-color: #EF4444;
        }
        .member-photo-small {
            width: 36px;
            height: 36px;
            border-radius: 100%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #3b82f6;
            transition: transform 0.2s ease;
        }
        .member-photo-small.active {
            border: #10B981 !important; /* green for active */
        }
        .member-photo-small.inactive {
            border: #EF4444 !important; /* red for inactive */
        }
        .member-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .member-status .status-dot {
            display: none; /* Hide the dot */
        }
        .cluster-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            justify-content: center;
        }
        @media (max-width: 640px) {
            .cluster-container {
                grid-template-columns: 1fr;
            }
        }
        .cluster-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            border: 2px solid transparent;
        }
        .cluster-card:hover {
            box-shadow: 0 8px 24px rgb(0 0 0 / 0.15);
            transform: translateY(-6px);
            border-color: #3b82f6;
        }
        .cluster-header {
            background-color: #3b82f6;
            color: white;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 1.15rem;
            border-bottom: 2px solid #2563eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            border-radius: 12px 12px 0 0;
        }
        .cluster-header .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 1.1rem;
        }
        .cluster-header.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }
        .cluster-members {
            padding: 12px 16px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            max-height: 360px;
            overflow-y: auto;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        .cluster-members.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
        }
        .cluster-members::-webkit-scrollbar {
            width: 6px;
        }
        .cluster-members::-webkit-scrollbar-thumb {
            background-color: #93c5fd;
            border-radius: 3px;
        }
        .cluster-members::-webkit-scrollbar-track {
            background-color: #f3f4f6;
        }
        .member-item {
            background-color: #f9fafb;
            border-radius: 10px;
            padding: 10px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            box-shadow: 0 1px 4px rgb(0 0 0 / 0.05);
            text-align: center;
            font-size: 0.875rem;
            user-select: none;
        }
        .member-item:hover, .member-item:focus-visible {
            background-color: #e0f2fe;
            box-shadow: 0 4px 12px rgb(59 130 246 / 0.3);
            outline: none;
            transform: translateY(-2px);
        }
        .member-item img {
            border: 2px solid #3b82f6;
            width: 64px;
            height: 64px;
            border-radius: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .member-item:hover img, .member-item:focus-visible img {
            transform: scale(1.1);
        }
        .member-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.95rem;
            white-space: normal;
            overflow-wrap: break-word;
        }
        .member-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
        }
        .close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 28px;
            color: #333;
            cursor: pointer;
            background: none;
            border: none;
            line-height: 1;
            transition: color 0.2s ease-in-out;
        }
        .close-btn:hover {
            color: #ef4444;
        }
        .overlay-photo {
            margin-top: 20px;
            width: 160px;
            height: 160px;
            border-radius: 100%;
            object-fit: cover;
            border: 4px solid #3b82f6;
            box-shadow: 0 4px 12px rgb(59 130 246 / 0.4);
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }
        .overlay-content {
            background-color: white;
            padding: 20px 25px 25px 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 1100px;
            max-height: 1000px;
            display: flex;
            flex-direction: row;
            gap: 24px;
            flex-wrap: wrap;
            position: relative;
            font-size: 0.9rem;
            box-shadow: 0 8px 24px rgb(0 0 0 / 0.15);
            overflow-y: auto;
        }
        .member-header {
            flex: 1 1 260px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 260px;
        }
        .member-header h2 {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
            color: #1e40af;
        }
        .member-status-overlay {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 1rem;
            color: #2563eb;
        }
        .member-details {
            flex: 2 1 560px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 28px;
            min-width: 260px;
        }
        .detail-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .detail-label {
            font-weight: 700;
            color: #374151;
            width: 140px;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        .detail-value {
            flex: 1;
            word-break: break-word;
            font-size: 0.9rem;
            color: #1e293b;
        }
        fieldset {
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 1rem 1.25rem 1.25rem 1.25rem;
            margin-bottom: 1.25rem;
            background-color: #f0f9ff;
        }
        legend {
            font-weight: 800;
            font-size: 1.2rem;
            color: #1e40af;
            padding: 0 0.75rem;
            width: auto;
        }
        #noResultsMessage {
            display: none;
            width: 100%;
            text-align: center;
            font-size: 1.2rem;
            color: #6b7280;
            margin-top: 2rem;
        }
        .no-members-message {
            grid-column: 1 / -1;
            text-align: center;
            font-size: 1rem;
            color: #6b7280;
            padding: 20px 0;
            user-select: none;
        }
        @media (max-width: 768px) {
            .overlay-content {
                flex-direction: column;
                padding: 20px 15px 15px 15px;
                max-height: none;
            }
            .member-header, .member-details {
                min-width: 100%;
                flex: none;
            }
            .member-details {
                grid-template-columns: 1fr;
            }
            .member-header h2 {
                font-size: 1.3rem;
            }
            .overlay-photo {
                width: 140px;
                height: 140px;
                margin-top: 15px;
            }
        }
    </style>
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
            max-width: 90%;
            max-height: 90%;
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
</head>
<body>
<?php
require_once '../config.php';

// Fetch only approved members
$result = $conn->query("SELECT * FROM members WHERE status1 = 'approved'");
$members = $result->fetch_all(MYSQLI_ASSOC);

/**
 * District to Puroks mapping as provided
 */
$districtPuroks = [
    'District 1' => ['Purok 1', 'Purok 2', 'Purok 2A'],
    'District 2' => ['Purok 3', 'Purok 4', 'Purok 6'],
    'District 3' => ['Purok 3A', 'Purok 14', 'Purok 12'],
    'District 4' => ['Purok 11A', 'Purok 11C', 'Purok 11D'],
    'District 5' => ['Purok 5', 'Purok 7', 'Purok 13', 'Purok 9'],
    'District 6' => ['Purok 11', 'Purok 11B', 'Purok 10A'],
    'District 7' => ['Purok 8', 'Purok 8A', 'Purok 9A'],
    'District 8' => ['Purok 10', 'Purok 10B', 'Candelaria'],
    'District 9' => [] // For relocate site other Purok or unmatched
];

// Function to find district by purok
function findDistrictByPurok($purok, $districtPuroks) {
    foreach ($districtPuroks as $district => $puroks) {
        if (in_array($purok, $puroks)) {
            return $district;
        }
    }
    return 'District 9'; // Default district for unmatched puroks
}

// Group members by district cluster based on purok extracted from street
$clusters = [];
foreach ($members as $member) {
    $street = $member['street'] ?: '';
    // Extract purok from street (assuming purok is the first word or phrase in street)
    // Normalize street to match keys (trim and uppercase first letter)
    $purok = '';
    if (preg_match('/^(Purok\s*\d+[A-Z]?|Candelaria)/i', trim($street), $matches)) {
        $purok = ucfirst(strtolower($matches[1]));
    }
    $district = findDistrictByPurok($purok, $districtPuroks);
    $clusterKey = $district;
    if (!isset($clusters[$clusterKey])) {
        $clusters[$clusterKey] = [];
    }
    $clusters[$clusterKey][] = $member;
}
?>
<header class="bg-white shadow sticky top-0 z-30">
   <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <div class="flex items-center space-x-3">
     <img alt="Company logo with green background and white text 'Logo'" class="hidden h-10 w-10 rounded" height="40" src="https://storage.googleapis.com/a1aa/image/c47e04aa-8d6b-4f49-a778-002e1fb1fd25.jpg" width="40"/>
     <h1 class="text-xl text-green-900 tracking-tight">
      Youth Members
     </h1>
    </div>
  </header>
<main class="container max-w-full px-4 py-6 bg-white w-full min-h-screen">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <input type="search" id="searchInput" aria-label="Search members by name or address" placeholder="Search members by name or address..." class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base" />
        <select id="statusFilter" aria-label="Filter members by status" class="hidden px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
            <option value="all" selected>All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div id="clustersContainer" class="cluster-container" role="list" aria-label="District clusters of approved members">
        <?php foreach ($clusters as $districtName => $districtMembers): ?>
            <section class="cluster-card" role="listitem" aria-label="District <?= htmlspecialchars($districtName) ?>">
                <header class="cluster-header" tabindex="0" aria-expanded="true" aria-controls="members-<?= md5($districtName) ?>">
                    <span><?= htmlspecialchars($districtName) ?> 
                        <span class="text-xs text-blue-200 font-semibold ml-1">(<?= count($districtMembers) ?> member<?= count($districtMembers) > 1 ? 's' : '' ?>)</span>
                    </span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </header>
                <div id="members-<?= md5($districtName) ?>" class="cluster-members" role="list" tabindex="0" aria-label="Members in district <?= htmlspecialchars($districtName) ?>">
                    <?php foreach ($districtMembers as $member): ?>
                        <div tabindex="0" role="button" aria-label="View details for <?= htmlspecialchars(trim($member['first_name'] . ' ' . $member['last_name'])) ?>" class="member-item" data-member-id="<?= htmlspecialchars($member['member_id']) ?>" data-member-name="<?= htmlspecialchars(trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name'])) ?>" data-member-address="<?= htmlspecialchars($member['street'] . ', ' . $member['barangay'] . ', ' . $member['city'] . ', ' . $member['province']) ?>" data-member-status="<?= strtolower($member['status']) ?>" onclick="showMemberDetails(<?= htmlspecialchars($member['member_id']) ?>)" onkeypress="if(event.key==='Enter'){showMemberDetails(<?= htmlspecialchars($member['member_id']) ?>);}">
                            <img src="<?= htmlspecialchars($member['id_photo'] ?: 'https://placehold.co/64x64/png?text=No+Photo') ?>" alt="Photo of <?= htmlspecialchars(trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name'])) ?>" class="member-photo-small" />
                            <div class="member-name"><?= htmlspecialchars(trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name'])) ?></div>
                            <div class="member-status ">
                                <span class="status-dot <?= strtolower($member['status']) === 'active' ? 'active' : 'inactive' ?>" aria-hidden="true"></span>
                                <span class="sr-only"><?= htmlspecialchars($member['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <div id="noResultsMessage" role="alert" aria-live="polite" class="text-center text-gray-500 text-lg mt-8 hidden">No members found matching your search and filter criteria.</div>
</main>

<!-- Member Details Overlay -->
<div id="memberOverlay" class="overlay" role="dialog" aria-modal="true" aria-labelledby="overlayName" aria-describedby="overlayDescription" tabindex="-1">
    <div class="overlay-content" role="document">
        <button class="close-btn" aria-label="Close member details" onclick="closeOverlay()">&times;</button>
        
        <div class="member-header">
            <img id="overlayPhoto" src="" class="overlay-photo cursor-pointer" alt="Member photo" />
            <h2 class="font-bold mb-2" id="overlayName"></h2>
            <div class="member-status-overlay mb-4 hidden" id="overlayStatus"></div>
            <div class="flex justify-center space-x-4 mt-4">
                <img id="overlayBirthCertificate" src="" alt="Birth Certificate" class="rounded border border-gray-300 max-w-[120px] max-h-[120px] object-contain cursor-pointer" />
                <img id="overlayResidenceCertificate" src="" alt="Residence Certificate" class="rounded border border-gray-300 max-w-[120px] max-h-[120px] object-contain cursor-pointer" />
            </div>
        </div>

        <!-- Image Floating Overlay -->
        <div id="imageOverlay" class="image-overlay" style="display:none;">
            <div class="image-overlay-content">
                <button id="imageOverlayClose" aria-label="Close image preview">&times;</button>
                <img id="imageOverlayImg" src="" alt="Full size image" />
            </div>
        </div>
        
        <div class="member-details" id="overlayDescription">
            <fieldset>
                <legend>Personal Information</legend>
                <div class="detail-row">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value" id="overlayAddress"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Age:</div>
                    <div class="detail-value" id="overlayAge"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Gender:</div>
                    <div class="detail-value" id="overlayGender"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value" id="overlayDOB"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Place of Birth:</div>
                    <div class="detail-value" id="overlayPlaceOfBirth"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Civil Status:</div>
                    <div class="detail-value" id="overlayCivilStatus"></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Contact Information</legend>
                <div class="detail-row">
                    <div class="detail-label">Contact:</div>
                    <div class="detail-value" id="overlayContact"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value" id="overlayEmail"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Social Media:</div>
                    <div class="detail-value" id="overlaySocialMedia"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Emergency Contact:</div>
                    <div class="detail-value" id="overlayEmergencyContact"></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Family & Education</legend>
                <div class="detail-row">
                    <div class="detail-label">Parent Details:</div>
                    <div class="detail-value" id="overlayParentDetails"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">School:</div>
                    <div class="detail-value" id="overlaySchool"></div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Work & Registration</legend>
                <div class="detail-row">
                    <div class="detail-label">Work Status:</div>
                    <div class="detail-value" id="overlayWorkStatus"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Position:</div>
                    <div class="detail-value" id="overlayPosition"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Registered:</div>
                    <div class="detail-value" id="overlayRegistered"></div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<script>
    const membersData = <?php echo json_encode($members); ?>;
    let currentMemberIndex = 0;

    function showMemberDetails(memberId) {
        currentMemberIndex = membersData.findIndex(member => member.member_id == memberId);
        if (currentMemberIndex === -1) return;

        updateOverlayContent();
        const overlay = document.getElementById('memberOverlay');
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
        overlay.focus();
        trapFocus(overlay);
    }

    function updateOverlayContent() {
        const member = membersData[currentMemberIndex];

        document.getElementById('overlayPhoto').src = member.id_photo || 'https://placehold.co/160x160/png?text=No+Photo';
        document.getElementById('overlayPhoto').alt = `Photo of ${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
        document.getElementById('overlayBirthCertificate').src = member.birth_certificate && member.birth_certificate !== '' ? (member.birth_certificate.includes('uploads/') ? member.birth_certificate : '/uploads/birth_certs/' + member.birth_certificate) : 'https://placehold.co/120x120/png?text=No+Birth+Cert';
        document.getElementById('overlayBirthCertificate').alt = `Birth Certificate of ${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
        document.getElementById('overlayResidenceCertificate').src = member.residence_certificate && member.residence_certificate !== '' ? (member.residence_certificate.includes('uploads/') ? member.residence_certificate : '/uploads/residence_certs/' + member.residence_certificate) : 'https://placehold.co/120x120/png?text=No+Residence+Cert';
        document.getElementById('overlayResidenceCertificate').alt = `Residence Certificate of ${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
        document.getElementById('overlayName').textContent = `${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
        document.getElementById('overlayStatus').innerHTML = 
            `<span class="status-dot ${member.status.toLowerCase() === 'active' ? 'active' : 'inactive'}" aria-hidden="true"></span>${member.status}`;
        document.getElementById('overlayAddress').textContent = `${member.street || 'N/A'}, ${member.barangay || 'N/A'}, ${member.city || 'N/A'}, ${member.province || 'N/A'}`;
        document.getElementById('overlayAge').textContent = member.age || 'N/A';
        document.getElementById('overlayGender').textContent = member.gender || 'N/A';
        document.getElementById('overlayContact').textContent = member.contact_number || 'N/A';
        document.getElementById('overlayEmail').textContent = member.email || 'N/A';
        document.getElementById('overlayRegistered').textContent = member.created_at || 'N/A';
        document.getElementById('overlayCivilStatus').textContent = member.civil_status || 'N/A';
        document.getElementById('overlayWorkStatus').textContent = member.work_status || 'N/A';
        document.getElementById('overlayPosition').textContent = member.position || 'N/A';
        document.getElementById('overlayDOB').textContent = member.dob || 'N/A';
        document.getElementById('overlayPlaceOfBirth').textContent = member.place_of_birth || 'N/A';
        document.getElementById('overlaySocialMedia').textContent = member.social_media || 'N/A';
        document.getElementById('overlayParentDetails').textContent = `${member.parent_first_name || ''} ${member.parent_middle_name || ''} ${member.parent_last_name || ''} (${member.parent_relationship || 'N/A'}) - ${member.parent_contact || 'N/A'}`.trim();
        document.getElementById('overlaySchool').textContent = `${member.school || 'N/A'} (${member.education_level || 'N/A'}, ${member.year_level || 'N/A'})`;
        document.getElementById('overlayEmergencyContact').textContent = `${member.emergency_name || 'N/A'} (${member.emergency_relationship || 'N/A'}) - ${member.emergency_contact || 'N/A'}`;
    }

    function closeOverlay() {
        const overlay = document.getElementById('memberOverlay');
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
        releaseFocusTrap();
    }

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
        const overlay = document.getElementById('memberOverlay');
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
            closeOverlay();
        }
    }

    // Close overlay on click outside content
    document.getElementById('memberOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOverlay();
        }
    });

    // Collapse/Expand clusters
    function addClusterToggleListeners() {
        document.querySelectorAll('.cluster-header').forEach(header => {
            header.addEventListener('click', () => {
                const clusterMembers = header.nextElementSibling;
                const isCollapsed = clusterMembers.classList.toggle('collapsed');
                header.classList.toggle('collapsed', isCollapsed);
                header.setAttribute('aria-expanded', !isCollapsed);
            });
            header.addEventListener('keypress', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    header.click();
                }
            });
        });
    }

    // Search and filter functionality without pagination
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const clustersContainer = document.getElementById('clustersContainer');
    const noResultsMessage = document.getElementById('noResultsMessage');

    function groupMembersByDistrict(members) {
        const grouped = {};
        members.forEach(member => {
            // Determine district by purok from street
            const street = member.street || '';
            let purok = '';
            const match = street.match(/^(Purok\s*\d+[A-Z]?|Candelaria)/i);
            if (match) {
                purok = match[1].toLowerCase().replace(/\s+/g, ' ').trim();
                purok = purok.charAt(0).toUpperCase() + purok.slice(1);
            }
            // District mapping from JS side (same as PHP)
            const districtPuroks = {
                'District 1': ['Purok 1', 'Purok 2', 'Purok 2A'],
                'District 2': ['Purok 3', 'Purok 4', 'Purok 6'],
                'District 3': ['Purok 3A', 'Purok 14', 'Purok 12'],
                'District 4': ['Purok 11A', 'Purok 11C', 'Purok 11D'],
                'District 5': ['Purok 5', 'Purok 7', 'Purok 13', 'Purok 9'],
                'District 6': ['Purok 11', 'Purok 11B', 'Purok 10A'],
                'District 7': ['Purok 8', 'Purok 8A', 'Purok 9A'],
                'District 8': ['Purok 10', 'Purok 10B', 'Candelaria'],
                'District 9': []
            };
            let district = 'District 9';
            for (const dist in districtPuroks) {
                if (districtPuroks[dist].includes(purok)) {
                    district = dist;
                    break;
                }
            }
            if (!grouped[district]) grouped[district] = [];
            grouped[district].push(member);
        });
        return grouped;
    }

    function renderClusters(clustersObj) {
        clustersContainer.innerHTML = '';

        const allMembers = Object.values(clustersObj).flat();

        if (allMembers.length === 0) {
            noResultsMessage.classList.remove('hidden');
            return;
        } else {
            noResultsMessage.classList.add('hidden');
        }

        for (const districtName in clustersObj) {
            const districtMembers = clustersObj[districtName] || [];
            const clusterId = 'members-' + md5(districtName);

            const section = document.createElement('section');
            section.className = 'cluster-card';
            section.setAttribute('role', 'listitem');
            section.setAttribute('aria-label', `District ${districtName}`);

            const header = document.createElement('header');
            header.className = 'cluster-header';
            header.setAttribute('tabindex', '0');
            header.setAttribute('aria-expanded', 'true');
            header.setAttribute('aria-controls', clusterId);
            header.innerHTML = `<span>${districtName} <span class="text-xs text-blue-200 font-semibold ml-1">(${districtMembers.length} member${districtMembers.length !== 1 ? 's' : ''})</span></span><i class="fas fa-chevron-down toggle-icon"></i>`;
            header.addEventListener('click', () => {
                const membersDiv = section.querySelector('.cluster-members');
                const isCollapsed = membersDiv.classList.toggle('collapsed');
                header.classList.toggle('collapsed', isCollapsed);
                header.setAttribute('aria-expanded', !isCollapsed);
            });
            header.addEventListener('keypress', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    header.click();
                }
            });

            const membersDiv = document.createElement('div');
            membersDiv.className = 'cluster-members';
            membersDiv.id = clusterId;
            membersDiv.setAttribute('role', 'list');
            membersDiv.setAttribute('tabindex', '0');
            membersDiv.setAttribute('aria-label', `Members in district ${districtName}`);

            if (districtMembers.length === 0) {
                const noMemberDiv = document.createElement('div');
                noMemberDiv.className = 'no-members-message';
                noMemberDiv.textContent = 'No member found in this district';
                membersDiv.appendChild(noMemberDiv);
            } else {
                districtMembers.forEach(member => {
                    const memberDiv = document.createElement('div');
                    memberDiv.className = 'member-item';
                    memberDiv.setAttribute('tabindex', '0');
                    memberDiv.setAttribute('role', 'button');
                    memberDiv.setAttribute('aria-label', `View details for ${member.first_name} ${member.last_name}`);
                    memberDiv.dataset.memberId = member.member_id;
                    memberDiv.dataset.memberName = `${member.first_name} ${member.middle_name || ''} ${member.last_name}`.trim();
                    memberDiv.dataset.memberAddress = `${member.street}, ${member.barangay}, ${member.city}, ${member.province}`;
                    memberDiv.dataset.memberStatus = (member.status || '').toLowerCase();
                    memberDiv.innerHTML = `
                        <img src="${member.id_photo || 'https://placehold.co/64x64/png?text=No+Photo'}" alt="Photo of ${member.first_name} ${member.middle_name || ''} ${member.last_name}" class="member-photo-small" />
                        <div class="member-name">${member.first_name} ${member.middle_name || ''} ${member.last_name}</div>
                        <div class="member-status">
                            <span class="status-dot ${member.status && member.status.toLowerCase() === 'active' ? 'active' : 'inactive'}" aria-hidden="true"></span>
                            <span class="sr-only">${member.status}</span>
                        </div>
                    `;
                    memberDiv.addEventListener('click', () => showMemberDetails(member.member_id));
                    memberDiv.addEventListener('keypress', e => {
                        if (e.key === 'Enter') showMemberDetails(member.member_id);
                    });
                    membersDiv.appendChild(memberDiv);
                });
            }

            section.appendChild(header);
            section.appendChild(membersDiv);
            clustersContainer.appendChild(section);
        }

        addClusterToggleListeners();
    }

    // Simple MD5 hash function for IDs (to avoid collisions)
    function md5(str) {
        let hash = 0, i, chr;
        if (str.length === 0) return hash.toString();
        for (i = 0; i < str.length; i++) {
            chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return hash.toString();
    }

    function filterAndRender() {
        const query = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;

        let filtered = membersData.filter(member => {
            const fullName = `${member.first_name} ${member.middle_name || ''} ${member.last_name}`.toLowerCase();
            const address = `${member.street}, ${member.barangay}, ${member.city}, ${member.province}`.toLowerCase();
            const statusLower = (member.status || '').toLowerCase();

            const matchesSearch = fullName.includes(query) || address.includes(query);
            const matchesStatus = (status === 'all') || (status === statusLower);

            return matchesSearch && matchesStatus;
        });

        const filteredClusters = groupMembersByDistrict(filtered);
        renderClusters(filteredClusters);
    }

    searchInput.addEventListener('input', () => {
        filterAndRender();
    });

    statusFilter.addEventListener('change', () => {
        filterAndRender();
    });

    // Initial render
    const initialClusters = groupMembersByDistrict(membersData);
    renderClusters(initialClusters);
</script>

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

    document.getElementById('overlayPhoto').addEventListener('click', () => {
        openImageOverlay(document.getElementById('overlayPhoto').src, document.getElementById('overlayPhoto').alt);
    });
    document.getElementById('overlayBirthCertificate').addEventListener('click', () => {
        openImageOverlay(document.getElementById('overlayBirthCertificate').src, document.getElementById('overlayBirthCertificate').alt);
    });
    document.getElementById('overlayResidenceCertificate').addEventListener('click', () => {
        openImageOverlay(document.getElementById('overlayResidenceCertificate').src, document.getElementById('overlayResidenceCertificate').alt);
    });
</script>

<?php $conn->close(); ?>
</body>
</html>