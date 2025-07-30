<?php
require '../config.php'; // Include your database connection file

// Verify user is logged in and active
if (!isset($_SESSION['member_id'])) {
    header("Location: login_member.php");
    exit();
}

// Get the member ID from the session
$member_id = $_SESSION['member_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // Fetch current member data
    $sql = "SELECT * FROM members WHERE member_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        $stmt->close();
    } else {
        echo "Error fetching member data: " . $conn->error;
    }

    // Initialize an array to hold the fields to update
    $fields = [];
    $params = [];
    $types = '';

    // Check each field and add to the update array if it's set
    if (!empty($_POST['first_name'])) {
        $fields[] = "first_name = ?";
        $params[] = trim($_POST['first_name']);
        $types .= 's';
    }
    if (!empty($_POST['middle_name'])) {
        $fields[] = "middle_name = ?";
        $params[] = trim($_POST['middle_name']);
        $types .= 's';
    }
    if (!empty($_POST['last_name'])) {
        $fields[] = "last_name = ?";
        $params[] = trim($_POST['last_name']);
        $types .= 's';
    }
    if (!empty($_POST['gender'])) {
        $fields[] = "gender = ?";
        $params[] = $_POST['gender'];
        $types .= 's';
    }
    if (!empty($_POST['dob'])) {
        $fields[] = "dob = ?";
        $params[] = $_POST['dob'];
        $types .= 's';
    }
    if (!empty($_POST['place_of_birth'])) {
        $fields[] = "place_of_birth = ?";
        $params[] = trim($_POST['place_of_birth']);
        $types .= 's';
    }
    if (!empty($_POST['contact_number'])) {
        $fields[] = "contact_number = ?";
        $params[] = trim($_POST['contact_number']);
        $types .= 's';
    }
    if (!empty($_POST['email'])) {
        $fields[] = "email = ?";
        $params[] = trim($_POST['email']);
        $types .= 's';
    }
    if (!empty($_POST['social_media'])) {
        $fields[] = "social_media = ?";
        $params[] = trim($_POST['social_media']);
        $types .= 's';
    }
    if (!empty($_POST['street'])) {
        $fields[] = "street = ?";
        $params[] = trim($_POST['street']);
        $types .= 's';
    }
    if (!empty($_POST['barangay'])) {
        $fields[] = "barangay = ?";
        $params[] = trim($_POST['barangay']);
        $types .= 's';
    }
    if (!empty($_POST['city'])) {
        $fields[] = "city = ?";
        $params[] = trim($_POST['city']);
        $types .= 's';
    }
    if (!empty($_POST['province'])) {
        $fields[] = "province = ?";
        $params[] = trim($_POST['province']);
        $types .= 's';
    }
    if (!empty($_POST['parent_last_name'])) {
        $fields[] = "parent_last_name = ?";
        $params[] = trim($_POST['parent_last_name']);
        $types .= 's';
    }
    if (!empty($_POST['parent_first_name'])) {
        $fields[] = "parent_first_name = ?";
        $params[] = trim($_POST['parent_first_name']);
        $types .= 's';
    }
    if (!empty($_POST['parent_middle_name'])) {
        $fields[] = "parent_middle_name = ?";
        $params[] = trim($_POST['parent_middle_name']);
        $types .= 's';
    }
    if (!empty($_POST['parent_relationship'])) {
        $fields[] = "parent_relationship = ?";
        $params[] = trim($_POST['parent_relationship']);
        $types .= 's';
    }
    if (!empty($_POST['parent_contact'])) {
        $fields[] = "parent_contact = ?";
        $params[] = trim($_POST['parent_contact']);
        $types .= 's';
    }
    if (!empty($_POST['school'])) {
        $fields[] = "school = ?";
        $params[] = trim($_POST['school']);
        $types .= 's';
    }
    if (!empty($_POST['education_level'])) {
        $fields[] = "education_level = ?";
        $params[] = trim($_POST['education_level']);
        $types .= 's';
    }
    if (!empty($_POST['year_level'])) {
        $fields[] = "year_level = ?";
        $params[] = trim($_POST['year_level']);
        $types .= 's';
    }
    if (!empty($_POST['emergency_name'])) {
        $fields[] = "emergency_name = ?";
        $params[] = trim($_POST['emergency_name']);
        $types .= 's';
    }
    if (!empty($_POST['emergency_relationship'])) {
        $fields[] = "emergency_relationship = ?";
        $params[] = trim($_POST['emergency_relationship']);
        $types .= 's';
    }
    if (!empty($_POST['emergency_contact'])) {
        $fields[] = "emergency_contact = ?";
        $params[] = trim($_POST['emergency_contact']);
        $types .= 's';
    }

    // If no fields to update, do nothing
    if (empty($fields)) {
        echo "No fields to update.";
        exit();
    }

    // Prepare SQL statement to update the member's profile
    $sql = "UPDATE members SET " . implode(", ", $fields) . " WHERE member_id = ?";
    $params[] = $member_id; // Add member_id to the parameters
    $types .= 'i'; // Add type for member_id

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters dynamically
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Display success alert and redirect to profile page
            echo "<div id='successAlert' class='fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50'>
                    <div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg flex items-start'>
                      <div class='flex-1'>Profile updated successfully!</div>
                      <button onclick=\"dismissAlert('successAlert')\" class='ml-4 text-green-500 hover:text-green-700'>
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
                    
                    // Auto-dismiss after delay and redirect
                    setTimeout(function() {
                      dismissAlert('successAlert', 'dashboard_member.php?page=profile');
                    }, 3000);
                    
                    function dismissAlert(id, redirectUrl) {
                      var element = document.getElementById(id);
                      if (element) {
                        element.classList.add('translate-x-full');
                        setTimeout(function(){ 
                          element.remove(); 
                          window.location.href = redirectUrl; // Redirect after alert is dismissed
                        }, 300);
                      }
                    }
                  </script>";
            exit();
        } else {
            // Handle error
            echo "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    // Prepare SQL statement to delete the member's account
    $sql = "DELETE FROM members WHERE member_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $member_id);
        
        if ($stmt->execute()) {
            // Account deleted successfully, destroy the session
            session_destroy();
            // Display success alert and redirect to profile page
            echo "<div id='successAlert' class='fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50'>
                    <div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg flex items-start'>
                      <div class='flex-1'>Your account has been deleted successfully.</div>
                      <button onclick=\"dismissAlert('successAlert')\" class='ml-4 text-green-500 hover:text-green-700'>
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
                    
                    // Auto-dismiss after delay and redirect
                    setTimeout(function() {
                      dismissAlert('successAlert', 'dashboard_member.php?page=profile');
                    }, 3000);
                    
                    function dismissAlert(id, redirectUrl) {
                      var element = document.getElementById(id);
                      if (element) {
                        element.classList.add('translate-x-full');
                        setTimeout(function(){ 
                          element.remove(); 
                          window.location.href = redirectUrl; // Redirect after alert is dismissed
                        }, 300);
                      }
                    }
                  </script>";
            exit();
        } else {
            // Handle error
            echo "Error deleting account: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Fetch current member data
$sql = "SELECT * FROM members WHERE member_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    $stmt->close();
} else {
    echo "Error fetching member data: " . $conn->error;
}

$conn->close();
?>

<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Member Profile
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
      font-family: 'Inter', sans-serif;
    }
  </style>
 </head>
 <body class="bg-gray-900 bg-opacity-80 min-h-screen w-full flex items-center justify-center">
  <div class="w-full bg-white rounded-lg shadow-xl p-8">
   <header class="mb-8 flex justify-between items-center">
    <a class="flex items-center space-x-2" href="dashboard_member.php">
     <span class="text-xl font-semibold text-blue-700">
      Member Dashboard
     </span>
    </a>
    <nav class="hidden md:flex space-x-6 text-gray-700 font-medium">
     <a class="text-blue-600 border-b-2 border-blue-600 pb-1 hidden" href="dashboard_member.php?page=profile">
      Profile
     </a>
     <a class="hover:text-blue-600 transition hidden" href="dashboard_member.php?page=settings">
      Settings
     </a>
     <a class="hover:text-red-600 transition hidden" href="logout.php">
      <i class="fas fa-sign-out-alt">
      </i>
      Logout
     </a>
    </nav>
   </header>
   <h1 class="text-3xl font-semibold text-gray-900 mb-8 text-center">
    Edit Your Profile
   </h1>
   <form class="space-y-8" method="POST" novalidate="">
    <section>
     <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
      Personal Information
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="first_name">
        First Name
       </label>
       <input autocomplete="given-name" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="first_name" name="first_name" required="" type="text" value="<?php echo htmlspecialchars($member['first_name'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="middle_name">
        Middle Name
       </label>
       <input autocomplete="additional-name" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="middle_name" name="middle_name" type="text" value="<?php echo htmlspecialchars($member['middle_name'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="last_name">
        Last Name
       </label>
       <input autocomplete="family-name" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="last_name" name="last_name" required="" type="text" value="<?php echo htmlspecialchars($member['last_name'] ?? ''); ?>"/>
      </div>
     </div>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="gender">
        Gender
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="gender" name="gender" required="">
        <option value="" <?php if(empty($member['gender'])) echo 'selected'; ?>>
         Select gender
        </option>
        <option value="Male" <?php if(($member['gender'] ?? '') === 'Male') echo 'selected'; ?>>
         Male
        </option>
        <option value="Female" <?php if(($member['gender'] ?? '') === 'Female') echo 'selected'; ?>>
         Female
        </option>
        <option value="Other" <?php if(($member['gender'] ?? '') === 'Other') echo 'selected'; ?>>
         Other
        </option>
       </select>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="dob">
        Date of Birth
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="dob" name="dob" required="" type="date" value="<?php echo htmlspecialchars($member['dob'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="place_of_birth">
        Place of Birth
       </label>
       <input autocomplete="off" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="place_of_birth" name="place_of_birth" type="text" value="<?php echo htmlspecialchars($member['place_of_birth'] ?? ''); ?>"/>
      </div>
     </div>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="contact_number">
        Contact Number
       </label>
       <input autocomplete="tel" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="contact_number" name="contact_number" type="tel" value="<?php echo htmlspecialchars($member['contact_number'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="email">
        Email
       </label>
       <input autocomplete="email" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="email" name="email" required="" type="email" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="social_media">
        Social Media Handle
       </label>
       <input autocomplete="off" class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="social_media" name="social_media" placeholder="@username" type="text" value="<?php echo htmlspecialchars($member['social_media'] ?? ''); ?>"/>
      </div>
     </div>
    </section>
    <section>
     <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
      Address
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="street">
        Street
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="street" name="street" required>
        <option value="">Select Purok</option>
        <option value="Purok 1"   <?php if(($member['street'] ?? '') === 'Purok 1') echo 'selected'; ?>>Purok 1</option>
        <option value="Purok 2"   <?php if(($member['street'] ?? '') === 'Purok 2') echo 'selected'; ?>>Purok 2</option>
        <option value="Purok 2 - A"   <?php if(($member['street'] ?? '') === 'Purok 2 - A') echo 'selected'; ?>>Purok 2 - A</option>
        <option value="Purok 3"   <?php if(($member['street'] ?? '') === 'Purok 3') echo 'selected'; ?>>Purok 3</option>
        <option value="Purok 3 - A"   <?php if(($member['street'] ?? '') === 'Purok 3 - A') echo 'selected'; ?>>Purok 3 - A</option>
        <option value="Purok 4"   <?php if(($member['street'] ?? '') === 'Purok 4') echo 'selected'; ?>>Purok 4</option>
        <option value="Purok 5"   <?php if(($member['street'] ?? '') === 'Purok 5') echo 'selected'; ?>>Purok 5</option>
        <option value="Purok 6"   <?php if(($member['street'] ?? '') === 'Purok 6') echo 'selected'; ?>>Purok 6</option>
        <option value="Purok 7"   <?php if(($member['street'] ?? '') === 'Purok 7') echo 'selected'; ?>>Purok 7</option>
        <option value="Purok 8"   <?php if(($member['street'] ?? '') === 'Purok 8') echo 'selected'; ?>>Purok 8</option>
        <option value="Purok 8 - A"   <?php if(($member['street'] ?? '') === 'Purok 8 - A') echo 'selected'; ?>>Purok 8 - A</option>
        <option value="Purok 9"   <?php if(($member['street'] ?? '') === 'Purok 9') echo 'selected'; ?>>Purok 9</option>
        <option value="Purok 9 - A"   <?php if(($member['street'] ?? '') === 'Purok 9 - A') echo 'selected'; ?>>Purok 9 - A</option>
        <option value="Purok 10"   <?php if(($member['street'] ?? '') === 'Purok 10') echo 'selected'; ?>>Purok 10</option>
        <option value="Purok 10 - A"   <?php if(($member['street'] ?? '') === 'Purok 10 - A') echo 'selected'; ?>>Purok 10 - A</option>
        <option value="Purok 10 - B"   <?php if(($member['street'] ?? '') === 'Purok 10 - B') echo 'selected'; ?>>Purok 10 - B</option>
        <option value="Purok 11"   <?php if(($member['street'] ?? '') === 'Purok 11') echo 'selected'; ?>>Purok 11</option>
        <option value="Purok 11 - A"   <?php if(($member['street'] ?? '') === 'Purok 11 - A') echo 'selected'; ?>>Purok 11 - A</option>
        <option value="Purok 11 - B"   <?php if(($member['street'] ?? '') === 'Purok 11 - B') echo 'selected'; ?>>Purok 11 - B</option>
        <option value="Purok 11 - C"   <?php if(($member['street'] ?? '') === 'Purok 11 - C') echo 'selected'; ?>>Purok 11 - C</option>
        <option value="Purok 11 - D"   <?php if(($member['street'] ?? '') === 'Purok 11 - D') echo 'selected'; ?>>Purok 11 - D</option>
        <option value="Purok 12"   <?php if(($member['street'] ?? '') === 'Purok 12') echo 'selected'; ?>>Purok 12</option>
        <option value="Purok 13"   <?php if(($member['street'] ?? '') === 'Purok 13') echo 'selected'; ?>>Purok 13</option>
        <option value="Purok 14"   <?php if(($member['street'] ?? '') === 'Purok 14') echo 'selected'; ?>>Purok 14</option>
        <option value="Candelaria"   <?php if(($member['street'] ?? '') === 'Candelaria') echo 'selected'; ?>>Candelaria</option>
       </select>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="barangay">
        Barangay
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="barangay" name="barangay" required>
        <option value="">Select Barangay</option>
        <option value="Poblacion" <?php if(($member['barangay'] ?? '') === 'Poblacion') echo 'selected'; ?>>Poblacion</option>
       </select>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="city">
        City
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="city" name="city" required>
        <option value="">Select City/Municipality</option>
        <option value="Tupi" <?php if(($member['city'] ?? '') === 'Tupi') echo 'selected'; ?>>Tupi</option>
       </select>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="province">
        Province
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="province" name="province" required>
        <option value="">Select Province</option>
        <option value="South Cotabato" <?php if(($member['province'] ?? '') === 'South Cotabato') echo 'selected'; ?>>South Cotabato</option>
       </select>
      </div>
     </div>
    </section>
    <section>
     <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
      Parent / Guardian Information
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="parent_last_name">
        Parent Last Name
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="parent_last_name" name="parent_last_name" type="text" value="<?php echo htmlspecialchars($member['parent_last_name'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="parent_first_name">
        Parent First Name
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="parent_first_name" name="parent_first_name" type="text" value="<?php echo htmlspecialchars($member['parent_first_name'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="parent_middle_name">
        Parent Middle Name
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="parent_middle_name" name="parent_middle_name" type="text" value="<?php echo htmlspecialchars($member['parent_middle_name'] ?? ''); ?>"/>
      </div>
     </div>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="parent_relationship">
        Relationship
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="parent_relationship" name="parent_relationship" placeholder="e.g. Father, Mother, Guardian" type="text" value="<?php echo htmlspecialchars($member['parent_relationship'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="parent_contact">
        Contact Number
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="parent_contact" name="parent_contact" type="tel" value="<?php echo htmlspecialchars($member['parent_contact'] ?? ''); ?>"/>
      </div>
      <div>
      </div>
     </div>
    </section>
    <section>
     <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
      Education
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="school">
        School
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="school" name="school" type="text" value="<?php echo htmlspecialchars($member['school'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="education_level">
        Education Level
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="education_level" name="education_level">
        <option value="">Select Level</option>
        <option value="Elementary" <?php if(($member['education_level'] ?? '') === 'Elementary') echo 'selected'; ?>>Elementary</option>
        <option value="Junior High School" <?php if(($member['education_level'] ?? '') === 'Junior High School') echo 'selected'; ?>>Junior High School</option>
        <option value="Senior High School" <?php if(($member['education_level'] ?? '') === 'Senior High School') echo 'selected'; ?>>Senior High School</option>
        <option value="College" <?php if(($member['education_level'] ?? '') === 'College') echo 'selected'; ?>>College</option>
        <option value="Vocational" <?php if(($member['education_level'] ?? '') === 'Vocational') echo 'selected'; ?>>Vocational</option>
       </select>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="year_level">
        Year Level
       </label>
       <select class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="year_level" name="year_level" required>
        <option value="">Select Year/Grade Level</option>
       </select>
      </div>
     </div>
    </section>
    <section>
     <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">
      Emergency Contact
     </h2>
     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="emergency_name">
        Name
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="emergency_name" name="emergency_name" type="text" value="<?php echo htmlspecialchars($member['emergency_name'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="emergency_relationship">
        Relationship
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="emergency_relationship" name="emergency_relationship" type="text" value="<?php echo htmlspecialchars($member['emergency_relationship'] ?? ''); ?>"/>
      </div>
      <div>
       <label class="block text-gray-700 font-medium mb-1" for="emergency_contact">
        Contact Number
       </label>
       <input class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="emergency_contact" name="emergency_contact" type="tel" value="<?php echo htmlspecialchars($member['emergency_contact'] ?? ''); ?>"/>
      </div>
     </div>
    </section>
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-10">
     <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-md shadow-md transition" name="update_profile" type="submit">
      <i class="fas fa-save mr-2">
      </i>
      Update Profile
     </button>
     <button class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-md shadow-md transition" name="delete_account" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')" type="submit">
      <i class="fas fa-user-slash mr-2">
      </i>
      Delete Account
     </button>
    </div>
  </form>
  </div>
  <footer class="bg-white border-t border-gray-200 py-6 mt-8 text-center text-gray-500 text-sm rounded-b-lg mx-auto w-full">
   ©
   <?php echo date('Y'); ?>
   Member Management System. All rights reserved.
  </footer>
  <script>
   const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileMenuButton?.addEventListener('click', () => {
      mobileMenu?.classList.toggle('hidden');
    });
  </script>
  <script>
// --- Dynamic Year/Grade Level options based on Education Level ---
const educationLevel = document.getElementById('education_level');
const yearLevel = document.getElementById('year_level');
const yearLevelOptions = {
  'Elementary': [
    'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'
  ],
  'Junior High School': [
    'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'
  ],
  'Senior High School': [
    'Grade 11', 'Grade 12'
  ],
  'College': [
    '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'
  ],
  'Vocational': [
    '1st Year', '2nd Year', '3rd Year'
  ]
};
function populateYearLevel() {
  const selected = educationLevel.value;
  yearLevel.innerHTML = '<option value="">Select Year/Grade Level</option>';
  if (yearLevelOptions[selected]) {
    yearLevel.disabled = false;
    yearLevelOptions[selected].forEach(function(opt) {
      const option = document.createElement('option');
      option.value = opt;
      option.textContent = opt;
      if ('<?php echo isset($member['year_level']) ? addslashes($member['year_level']) : ''; ?>' === opt) option.selected = true;
      yearLevel.appendChild(option);
    });
  } else {
    yearLevel.disabled = true;
  }
}
educationLevel.addEventListener('change', populateYearLevel);
window.addEventListener('DOMContentLoaded', populateYearLevel);
// --- Auto-select city, province, barangay when purok/street is selected ---
const streetSelect = document.getElementById('street');
const barangaySelect = document.getElementById('barangay');
const citySelect = document.getElementById('city');
const provinceSelect = document.getElementById('province');
if (streetSelect && barangaySelect && citySelect && provinceSelect) {
  streetSelect.addEventListener('change', function() {
    if (streetSelect.value) {
      barangaySelect.value = 'Poblacion';
      citySelect.value = 'Tupi';
      provinceSelect.value = 'South Cotabato';
    } else {
      barangaySelect.value = '';
      citySelect.value = '';
      provinceSelect.value = '';
    }
  });
}
</script>
 </body>
</html>