<?php
session_start(); // Start the session

// ==================== DATABASE CONFIGURATION ====================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sk_youth";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$success = false;
$error = "";

// File upload directories
$id_photo_dir = "uploads/id_photos/";
$birth_cert_dir = "uploads/birth_certs/";
$residence_cert_dir = "uploads/residence_certs/";

// Create directories if they don't exist
if (!file_exists($id_photo_dir)) mkdir($id_photo_dir, 0755, true);
if (!file_exists($birth_cert_dir)) mkdir($birth_cert_dir, 0755, true);
if (!file_exists($residence_cert_dir)) mkdir($residence_cert_dir, 0755, true);

// ==================== FORM PROCESSING ====================
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim(stripslashes(htmlspecialchars($data))));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Server-side validation for required fields on step 1 and step 2
    $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
    $required_fields_step1 = ['username', 'password', 'confirm_password'];
    $required_fields_step2 = [
        'first_name', 'last_name', 'gender', 'dob', 'mobile', 'email',
        'street', 'barangay', 'city', 'province', 'civil_status', 'work_status', 'position',
        'parent_last_name', 'parent_first_name', 'parent_relationship', 'parent_contact',
        'emergency_name', 'emergency_relationship', 'emergency_contact'
    ];

    $missing_fields = [];

    if ($step === 1) {
        foreach ($required_fields_step1 as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        // Check password confirmation match
        if (!empty($_POST['password']) && !empty($_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
            $error = "Passwords do not match.";
        }
    } else {
        foreach ($required_fields_step2 as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
    }

    if (!empty($missing_fields)) {
        $error = "Please fill in all required fields: " . implode(', ', $missing_fields);
    }

    if (empty($error)) {
        if ($step === 1) {
            // Check if username already exists
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $username = sanitize($_POST['username']);
                $password = sanitize($_POST['password']);

                $stmt = $conn->prepare("SELECT COUNT(*) FROM members WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    $error = "Username already exists. Please choose a different username.";
                } else {
                    $_SESSION['username'] = $username;
                    $_SESSION['password'] = $password;

                    // Redirect to step 2
                    header("Location: signup_member.php?step=2");
                    exit();
                }
            }
        } else {
            // Sanitize and store all POST data in session
            foreach ($_POST as $key => $value) {
                $_SESSION[$key] = sanitize($value);
            }

            // Check if username session variable is set before insert
            if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
                header("Location: signup_member.php?step=1");
                exit();
            }

            // Handle file uploads with error feedback
            try {
                $id_photo_path = handleFileUpload('id_photo', $id_photo_dir, ['image/jpeg', 'image/png'], PHP_INT_MAX, true);
                $birth_cert_path = handleFileUpload('birth_cert', $birth_cert_dir, ['image/jpeg', 'image/png', 'application/pdf'], PHP_INT_MAX);
                $residence_cert_path = handleFileUpload('residence_cert', $residence_cert_dir, ['image/jpeg', 'image/png', 'application/pdf'], PHP_INT_MAX);

                // Calculate age from date of birth
                $age = null;
                if (!empty($_SESSION['dob'])) {
                    $dob = new DateTime($_SESSION['dob']);
                    $today = new DateTime();
                    $age = $today->diff($dob)->y;
                }

                // Prepare and bind
                $stmt = $conn->prepare("INSERT INTO members (
                    member_id, first_name, middle_name, last_name, age, gender, contact_number, email, username, password, created_at, status1,
                    civil_status, work_status, position, dob, place_of_birth, social_media, street, barangay, city, province,
                    parent_last_name, parent_first_name, parent_middle_name, parent_relationship, parent_contact, school,
                    education_level, year_level, emergency_name, emergency_relationship, emergency_contact,
                    id_photo, birth_certificate, residence_certificate
                ) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "sssisssssssssssssssssssssssssssss",
                    $_SESSION['first_name'],
                    $_SESSION['middle_name'],
                    $_SESSION['last_name'],
                    $age,
                    $_SESSION['gender'],
                    $_SESSION['mobile'], // contact_number
                    $_SESSION['email'],
                    $_SESSION['username'],
                    $_SESSION['password'], // Store password as is
                    $_SESSION['civil_status'],
                    $_SESSION['work_status'],
                    $_SESSION['position'],
                    $_SESSION['dob'],
                    $_SESSION['place_of_birth'],
                    $_SESSION['social_media'],
                    $_SESSION['street'],
                    $_SESSION['barangay'],
                    $_SESSION['city'],
                    $_SESSION['province'],
                    $_SESSION['parent_last_name'],
                    $_SESSION['parent_first_name'],
                    $_SESSION['parent_middle_name'],
                    $_SESSION['parent_relationship'],
                    $_SESSION['parent_contact'],
                    $_SESSION['school'],
                    $_SESSION['education_level'],
                    $_SESSION['year_level'],
                    $_SESSION['emergency_name'],
                    $_SESSION['emergency_relationship'],
                    $_SESSION['emergency_contact'],
                    $id_photo_path,
                    $birth_cert_path,
                    $residence_cert_path
                );

                if ($stmt->execute()) {
                    $success = true;
                    // Set member_id in session for pending.php
                    $_SESSION['member_id'] = $stmt->insert_id;
                    echo '<script>window.location.href = "pending.php";</script>';
                    header("Location: pending.php");
                    exit();
                } else {
                    $error = "Failed to register member. Please try again.";
                }

                $stmt->close();
            } catch (Exception $e) {
                $error = "File upload error: " . $e->getMessage();
            }
        }
    }
}

function handleFileUpload($fieldName, $targetDir, $allowedTypes, $maxSize, $checkDimensions = false) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] == UPLOAD_ERR_NO_FILE) {
        // Return null instead of throwing an exception
        return null;
    }

    $file = $_FILES[$fieldName];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file['error']);
    }

    // Verify file size
    if ($file['size'] > $maxSize) {
        throw new Exception("File too large. Max size: " . ($maxSize / 1024 / 1024) . "MB");
    }

    // Verify file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        throw new Exception("Invalid file type for $fieldName. Allowed types: " . implode(', ', $allowedTypes));
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("Failed to move uploaded file");
    }

    return $targetPath;
}
?>

<Doctype html>
<html class="scroll-smooth" lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   SK Youth Membership Registration
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
   body {
        background-image: url('bgi/sk_background.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        position: relative;
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }
    .form-container {
        backdrop-filter: blur(12px);
        background-color: rgba(255, 255, 255, 0.85);
        border-radius: 0.5rem; /* rounded-lg */
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        max-width: 24rem; /* smaller width */
        max-height: 95vh; /* shorter height */
        overflow-y: auto; /* enable vertical scrolling */
        padding: 1.5rem 1.5rem; /* reduce padding */
    }
    /* Reduce vertical spacing between form elements */
    form.space-y-8 > * + *, form.space-y-10 > * + * {
      margin-top: 0.75rem;
    }
    /* Smaller headings */
    h1 {
      font-size: 1.75rem;
      margin-bottom: 1.5rem;
    }
    h2 {
      font-size: 1.125rem;
      margin-bottom: 1rem;
    }
    /* Smaller input padding */
    input, select {
      padding-top: 0.4rem;
      padding-bottom: 0.4rem;
    }
    /* Smaller buttons */
    button {
      padding-top: 0.6rem;
      padding-bottom: 0.6rem;
      font-size: 0.9rem;
    }
    /* Scrollbar styling for overflow */
    .form-container::-webkit-scrollbar {
      width: 6px;
    }
    .form-container::-webkit-scrollbar-thumb {
      background-color: rgba(59, 130, 246, 0.5);
      border-radius: 3px;
    }
    /* Gray out the button when disabled */
    #submitButton:disabled {
        background-color: gray;
        cursor: not-allowed;
    }
  </style>
 </head>
 <body class="bg-gray-50 min-h-screen flex items-center justify-center py-6 px-4 relative">
  <div class="form-container">
  <div class="flex justify-center mb-2">
            <img alt="Company Logo" class="w-24 h-24 rounded-full" src="bgi/sk_logo.png"/>
        </div>
   <h1 class="text-center font-extrabold text-gray-900">SK Youth Membership Registration</h1>
   <?php if ($success): ?>
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('success', '🎉 Registration Successful! Thank you for joining SK Youth. Redirecting to pending page...');
        setTimeout(function() {
          window.location.href = "pending.php";
        }, 2000);
    });
   </script>
   <?php elseif (!empty($error)): ?>
   <script>
    document.addEventListener('DOMContentLoaded', function() {
        // showToast('error', '❌ Error: <?php echo addslashes(htmlspecialchars($error)); ?>');
        const errorSpan = document.getElementById('form-error-message');
        if (errorSpan) {
          errorSpan.textContent = '❌ Error: <?php echo addslashes(htmlspecialchars($error)); ?>';
          errorSpan.style.display = 'block';
        }
    });
   </script>
   <?php endif; ?>
   <span id="form-error-message" style="display:none; color:#dc2626; font-size:0.95em; font-weight:500; margin-bottom:10px;"></span>
   <?php if (!isset($_GET['step']) || $_GET['step'] == 1): ?>
   <form class="space-y-6" method="POST" novalidate>
    <div>
     <h2 class="font-semibold border-b border-gray-300 pb-1">Account Information</h2>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700" for="username">Username:</label>
      <input autocomplete="username" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="username" name="username" required type="text" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700" for="password">Password:</label>
      <input autocomplete="new-password" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="password" name="password" required="" type="password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$" title="Password must be at least 8 characters and include uppercase, lowercase, number, and special character."/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700" for="confirm_password">Confirm Password:</label>
      <input autocomplete="new-password" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="confirm_password" name="confirm_password" required="" type="password" minlength="8"/>
     </div>
    </div>
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition-colors duration-200 text-sm" type="submit">Submit</button>
    <div class="mt-3 text-center">
      <span class="text-gray-700 text-sm">Have already an account?</span>
      <a href="login_member.php" class="text-blue-600 underline hover:text-blue-800 text-sm ml-1">Login</a>
    </div>
   </form>
   <script>
    // Client-side password confirmation validation
    const formStep1 = document.querySelector('form');
    if(formStep1){
      formStep1.addEventListener('submit', function(e){
        const pwd = formStep1.password.value;
        const confirmPwd = formStep1.confirm_password.value;
        if(pwd !== confirmPwd){
          e.preventDefault();
          alert('Passwords do not match. Please confirm your password.');
          formStep1.confirm_password.focus();
        }
      });
    }
   </script>
   <?php else: ?>
   <form class="space-y-8" enctype="multipart/form-data" method="POST" novalidate>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Personal Information</h2>
     <div class="space-y-3">
      <label class="block font-medium text-gray-700 text-sm">Full Name:</label>
      <input autocomplete="family-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="last_name" placeholder="Last Name" required type="text"/>
      <input autocomplete="given-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="first_name" placeholder="First Name" required type="text"/>
      <input autocomplete="additional-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="middle_name" placeholder="Middle Name" type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="gender">Gender:</label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="gender" name="gender" required>
       <option value="">Select Gender</option>
       <option value="Male">Male</option>
       <option value="Female">Female</option>
       <option value="Other">Other</option>
       <option value="Prefer not to say">Prefer not to say</option>
      </select>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="dob">Date of Birth:</label>
      <input class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="dob" name="dob" required type="date" max="<?php echo date('Y-m-d', strtotime('-10 years')); ?>" min="<?php echo date('Y-m-d', strtotime('-30 years')); ?>"/>
      <p class="text-xs text-gray-600 mt-1">Applicants must be between 10 and 30 years old.</p>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="place_of_birth">Place of Birth:</label>
      <input autocomplete="birthplace" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="place_of_birth" name="place_of_birth" type="text"/>
     </div>
     <div class="space-y-3 mt-3">
      <label class="block font-medium text-gray-700 text-sm">Contact Information:</label>
      <input autocomplete="tel" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="mobile" pattern="09[0-9]{9}" placeholder="Mobile Number (e.g., 09123456789)" required type="tel"/>
      <input autocomplete="email" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="email" placeholder="Email Address" required type="email"/>
      <input autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="social_media" placeholder="Social Media Handle (e.g., @username)" type="text"/>
     </div>
     <div class="space-y-3 mt-3">
      <label class="block font-medium text-gray-700 text-sm">Address:</label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="street" id="street" required>
       <option value="">Select Purok</option>
       <option value="Purok 1">Purok 1</option>
       <option value="Purok 2">Purok 2</option>
       <option value="Purok 2 - A">Purok 2 - A</option>
       <option value="Purok 3">Purok 3</option>
       <option value="Purok 3 - A">Purok 3 - A</option>
       <option value="Purok 4">Purok 4</option>
       <option value="Purok 5">Purok 5</option>
       <option value="Purok 6">Purok 6</option>
       <option value="Purok 7">Purok 7</option>
       <option value="Purok 8">Purok 8</option>
        <option value="Purok 8 - A">Purok 8 - A</option>
       <option value="Purok 9">Purok 9</option>
        <option value="Purok 9 - A">Purok 9 - A</option>
       <option value="Purok 10">Purok 10</option>
        <option value="Purok 10 - A">Purok 10 - A</option>
        <option value="Purok 10 - B">Purok 10 - B</option>
        <option value="Purok 11">Purok 11</option>
        <option value="Purok 11 - A">Purok 11 - A</option>
        <option value="Purok 11 - B">Purok 11 - B</option>
        <option value="Purok 11 - C">Purok 11 - C</option>
        <option value="Purok 11 - D">Purok 11 - D</option>
        <option value="Purok 12">Purok 12</option>
        <option value="Purok 13">Purok 13</option>
        <option value="Purok 14">Purok 14</option>
        <option value="Candelaria">Candelaria</option>
      </select>

      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="barangay" id="barangay" required>
       <option value="">Select Barangay</option>
       <option value="Poblacion">Poblacion</option>
      </select>

      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="city" id="city" required>
       <option value="">Select City/Municipality</option>
       <option value="Tupi">Tupi</option>
      </select>

      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="province" id="province" required>
       <option value="">Select Province</option>
       <option value="South Cotabato">South Cotabato</option>
      </select>
     </div>

     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="civil_status">Civil Status:</label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="civil_status" name="civil_status" required>
       <option value="">Select Civil Status</option>
       <option value="Single">Single</option>
       <option value="Married">Married</option>
       <option value="Widowed">Widowed</option>
       <option value="Separated">Separated</option>
      </select>
     </div>

     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="work_status">Work Status:</label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="work_status" name="work_status" required>
       <option value=""> Select Work Status</option>
       <option value="Employed">Employed</option>
       <option value="Self-employed">Self-employed</option>
       <option value="Unemployed">Unemployed</option>
       <option value="Student">Student</option>
      </select>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="position">Position:
      </label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="position" name="position" required>
       <option value="">Select Position</option>
       <option value="Youth Residence">Youth Residence</option>
       <option value="Sk Chairperson">Sk Chairperson</option>
       <option value="Sk Secretary">Sk Secretary</option>
       <option value="Sk Treasurer">Sk Treasurer</option>
       <option value="Sk Member">Sk Member</option>
      </select>
     </div>
    </section>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Family/Guardian Information</h2>
     <div class="space-y-3">
      <label class="block font-medium text-gray-700 text-sm">Parent/Guardian Name:</label>
      <input autocomplete="family-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="parent_last_name" placeholder="Last Name" required type="text"/>
      <input autocomplete="given-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="parent_first_name" placeholder="First Name" required type="text"/>
      <input autocomplete="additional-name" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" name="parent_middle_name" placeholder="Middle Name" type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="parent_relationship">Relationship:</label>
      <input autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="parent_relationship" name="parent_relationship" required type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="parent_contact">Contact Number:</label>
      <input autocomplete="tel" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="parent_contact" name="parent_contact" pattern="09[0-9]{9}" required type="tel"/>
     </div>
    </section>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Educational Background</h2>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="school">School/Institution:</label>
      <input autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="school" name="school" type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="education_level">Education Level:</label>
      <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="education_level" name="education_level">
       <option value="">Select Level</option>
       <option value="Elementary">Elementary</option>
       <option value="Junior High School">Junior High School</option>
       <option value="Senior High School">Senior High School</option>
       <option value="College">College</option>
       <option value="Vocational">Vocational</option>
      </select>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="year_level">Year/Grade Level:</label>
      <select autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="year_level" name="year_level" required disabled>
        <option value="">Select Year/Grade Level</option>
      </select>
     </div>
    </section>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Emergency Contact</h2>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="emergency_name">Emergency Contact Name:</label>
      <input autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="emergency_name" name="emergency_name" required type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="emergency_relationship">Relationship:</label>
      <input autocomplete="off" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="emergency_relationship" name="emergency_relationship" required type="text"/>
     </div>
     <div class="mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="emergency_contact">Contact Number:</label>
      <input autocomplete="tel" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="emergency_contact" name="emergency_contact" pattern="09[0-9]{9}" required type="tel"/>
     </div>
    </section>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Required Attachments</h2>
     <div class="file-upload mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="id_photo">ID Photo (1x1 or 2x2):</label>
      <input accept="image/jpeg,image/png" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="id_photo" name="id_photo" required type="file" aria-describedby="id_photo_help"/>
      <div id="id_photo_help" class="file-requirements text-xs text-gray-600 mt-1">
       JPEG/PNG, max 2MB. Recommended dimensions: 300x300 pixels.
      </div>
     </div>
     <div class="file-upload mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="birth_cert">Birth Certificate:</label>
      <input accept="image/jpeg,image/png,application/pdf" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="birth_cert" name="birth_cert" required type="file" aria-describedby="birth_cert_help"/>
      <div id="birth_cert_help" class="file-requirements text-xs text-gray-600 mt-1">
        JPEG/PNG/PDF, max 5MB
      </div>
     </div>
     <div class="file-upload mt-3">
      <label class="block mb-1 font-medium text-gray-700 text-sm" for="residence_cert">Certification of Residence:</label>
      <input accept="image/jpeg,image/png,application/pdf" class="w-full border border-gray-300 rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" id="residence_cert" name="residence_cert" required type="file" aria-describedby="residence_cert_help"/>
      <div id="residence_cert_help" class="file-requirements text-xs text-gray-600 mt-1">
       JPEG/PNG/PDF, max 5MB
      </div>
     </div>
    </section>
    <section>
     <h2 class="font-semibold border-b border-gray-300 pb-1 mb-3">Terms and Conditions</h2>
     <div class="mb-3 flex items-start">
      <input id="terms" name="terms" required type="checkbox" class="mt-1 mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
      <label for="terms" class="text-gray-700 text-sm">I agree to the <a href="terms.html" target="_blank" class="text-blue-600 underline hover:text-blue-800">terms and conditions</a>.</label>
     </div>
    </section>
    <button type="button" onclick="window.location.href='signup_member.php?step=1'" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 rounded-md transition-colors duration-200 text-sm mb-2">&larr; Back</button>
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition-colors duration-200 text-sm" id="submitButton" type="submit" disabled>Submit Application</button>
    
   </form>
   <script>
    // Client-side validation for file sizes and types
    const formStep2 = document.querySelector('form[enctype="multipart/form-data"]');
    if(formStep2){
      formStep2.addEventListener('submit', function(e){
        // Validate ID Photo
        const idPhoto = formStep2.id_photo.files[0];
        if(idPhoto){
          const allowedIdTypes = ['image/jpeg', 'image/png'];
          if(!allowedIdTypes.includes(idPhoto.type)){
            alert('ID Photo must be JPEG or PNG.');
            e.preventDefault();
            return;
          }
          // Removed size limit check for ID Photo
        }
        // Validate Birth Certificate
        const birthCert = formStep2.birth_cert.files[0];
        if(birthCert){
          const allowedBirthTypes = ['image/jpeg', 'image/png', 'application/pdf'];
          if(!allowedBirthTypes.includes(birthCert.type)){
            alert('Birth Certificate must be JPEG, PNG, or PDF.');
            e.preventDefault();
            return;
          }
          // Removed size limit check for Birth Certificate
        }
        // Validate Residence Certificate
        const residenceCert = formStep2.residence_cert.files[0];
        if(residenceCert){
          const allowedResidenceTypes = ['image/jpeg', 'image/png', 'application/pdf'];
          if(!allowedResidenceTypes.includes(residenceCert.type)){
            alert('Certification of Residence must be JPEG, PNG, or PDF.');
            e.preventDefault();
            return;
          }
          // Removed size limit check for Residence Certificate
        }
      });
    }

    const checkbox = document.getElementById('terms');
    const submitButton = document.getElementById('submitButton');

    // Add an event listener to toggle the button's disabled state
    checkbox.addEventListener('change', () => {
        submitButton.disabled = !checkbox.checked;
    });

    // Dynamic Year/Grade Level options based on Education Level
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
    if (educationLevel && yearLevel) {
      educationLevel.addEventListener('change', function() {
        const selected = educationLevel.value;
        yearLevel.innerHTML = '<option value="">Select Year/Grade Level</option>';
        if (yearLevelOptions[selected]) {
          yearLevel.disabled = false;
          yearLevelOptions[selected].forEach(function(opt) {
            const option = document.createElement('option');
            option.value = opt;
            option.textContent = opt;
            yearLevel.appendChild(option);
          });
        } else {
          yearLevel.disabled = true;
        }
      });
    }

    // Auto-select city, province, barangay when purok/street is selected
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
    // --- Auto-save and restore form fields using localStorage ---
    // Use a different variable name to avoid redeclaration
    const formStep2Local = document.querySelector('form[enctype="multipart/form-data"]');
    if (formStep2Local) {
      const saveFields = [
        'last_name','first_name','middle_name','gender','dob','place_of_birth',
        'mobile','email','social_media','street','barangay','city','province',
        'civil_status','work_status','position','parent_last_name','parent_first_name',
        'parent_middle_name','parent_relationship','parent_contact','school',
        'education_level','year_level','emergency_name','emergency_relationship','emergency_contact'
      ];
      // Restore on load
      saveFields.forEach(function(field) {
        const el = formStep2Local.elements[field];
        if (el && localStorage.getItem('sk_' + field)) {
          el.value = localStorage.getItem('sk_' + field);
          if (el.tagName === 'SELECT') {
            el.dispatchEvent(new Event('change'));
          }
        }
      });
      // Save on change
      saveFields.forEach(function(field) {
        const el = formStep2Local.elements[field];
        if (el) {
          el.addEventListener('change', function() {
            localStorage.setItem('sk_' + field, el.value);
          });
          el.addEventListener('input', function() {
            localStorage.setItem('sk_' + field, el.value);
          });
        }
      });
      // Clear localStorage on successful submit
      formStep2Local.addEventListener('submit', function() {
        saveFields.forEach(function(field) {
          localStorage.removeItem('sk_' + field);
        });
      });
    }
   </script>
   <?php endif; ?>
  </div>

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>

  <script>
    function showToast(type, message) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `max-w-xs w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5 ${
            type === 'success' ? 'border-green-400' : 'border-red-400'
        } border-l-4`;

        toast.innerHTML = `
            <div class="flex-1 w-0 p-4">
                <p class="text-sm font-medium ${
                    type === 'success' ? 'text-green-700' : 'text-red-700'
                }">${message}</p>
            </div>
            <div class="flex border-l border-gray-200">
                <button class="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        const closeButton = toast.querySelector('button');
        closeButton.addEventListener('click', () => {
            container.removeChild(toast);
        });

        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (container.contains(toast)) {
                container.removeChild(toast);
            }
        }, 5000);
    }
  </script>
 </body>
</html>
