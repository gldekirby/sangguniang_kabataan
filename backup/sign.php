<?php
session_start(); // Start the session

// ==================== DATABASE CONFIGURATION ====================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "youth_sk";

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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitization function
    function sanitize($data) {
        global $conn;
        return $conn->real_escape_string(trim(stripslashes(htmlspecialchars($data))));
    }

    // Check if username and password are submitted
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $_SESSION['username'] = sanitize($_POST['username']);
        $_SESSION['password'] = sanitize($_POST['password']);
        
        // Redirect to the next section
        header("Location: sign.php?step=2");
        exit();
    }

    // Process the rest of the form
    foreach ($_POST as $key => $value) {
        $_SESSION[$key] = sanitize($value);
    }

    // Handle file uploads
    try {
        $id_photo_path = handleFileUpload('id_photo', $id_photo_dir, ['image/jpeg', 'image/png'], 2048000, true); // Enable dimension check
        $birth_cert_path = handleFileUpload('birth_cert', $birth_cert_dir, ['image/jpeg', 'image/png', 'application/pdf'], 5120000); // 5MB
        $residence_cert_path = handleFileUpload('residence_cert', $residence_cert_dir, ['image/jpeg', 'image/png', 'application/pdf'], 5120000); // 5MB

        // Calculate age from date of birth
        $age = null;
        if (!empty($_SESSION['dob'])) {
            $dob = new DateTime($_SESSION['dob']);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO members (
            member_id, first_name, middle_name, last_name, age, gender, contact_number, email, username, password, created_at, status,
            civil_status, work_status, position, dob, place_of_birth, social_media, street, barangay, city, province,
            parent_last_name, parent_first_name, parent_middle_name, parent_relationship, parent_contact, school,
            education_level, year_level, emergency_name, emergency_relationship, emergency_contact,
            id_photo, birth_certificate, residence_certificate
        ) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'inactive', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
            session_destroy(); // Clear session data after successful submission
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleFileUpload($fieldName, $targetDir, $allowedTypes, $maxSize, $checkDimensions = false) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] == UPLOAD_ERR_NO_FILE) {
        throw new Exception("Please upload $fieldName");
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

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Youth Membership Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"></link>
    <style>
        .file-upload {
            margin: 15px 0;
        }
        .file-upload label {
            display: block;
            margin-bottom: 5px;
        }
        .file-requirements {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6">SK Youth Membership Registration</h1>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">🎉 Registration Successful!</strong>
                <span class="block sm:inline">Thank you for joining SK Youth.</span>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">❌ Error:</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!isset($_GET['step']) || $_GET['step'] == 1): ?>
            <!-- Step 1: Username and Password -->
            <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Account Information</h2>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Username:</label>
                        <input type="text" name="username" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Password:</label>
                        <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>
                <div class="form-navigation">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
                </div>
            </form>
        <?php else: ?>
            <!-- Step 2: Full Form -->
            <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Personal Information</h2>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Full Name:</label>
                        <input type="text" name="last_name" placeholder="Last Name" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="text" name="first_name" placeholder="First Name" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="text" name="middle_name" placeholder="Middle Name" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Gender:</label>
                        <select name="gender" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Date of Birth:</label>
                        <input type="date" name="dob" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Place of Birth:</label>
                        <input type="text" name="place_of_birth" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Contact Information:</label>
                        <input type="tel" name="mobile" placeholder="Mobile Number" pattern="09[0-9]{9}" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="email" name="email" placeholder="Email Address" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="text" name="social_media" placeholder="Social Media Handle (e.g., @username)" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Address:</label>
                        <select name="street" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                            <option value="">Select Purok</option>
                            <option value="Purok 1">Purok 1</option>
                            <option value="Purok 1 - A">Purok 1 - A</option>
                            <option value="Purok 2">Purok 2</option>
                            <option value="Purok 2 - A">Purok 2 - A</option>
                            <option value="Purok 3">Purok 3</option>
                            <option value="Purok 3 - A">Purok 3 - A</option>
                            <option value="Purok 4">Purok 4</option>
                            <option value="Purok 4 - A">Purok 4 - A</option>
                            <option value="Purok 5">Purok 5</option>
                            <option value="Purok 6">Purok 6</option>
                            <option value="Purok 7">Purok 7</option>
                            <option value="Purok 8">Purok 8</option>
                            <option value="Purok 9">Purok 9</option>
                            <option value="Purok 10">Purok 10</option>
                        </select>
                        <label class="block text-gray-700">Barangay:</label>
                        <select name="barangay" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                            <option value="">Select Barangay</option>
                            <option value="Poblacion">Poblacion</option>
                        </select>
                        <label class="block text-gray-700">City/Municipality:</label>
                        <select name="city" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                            <option value="">Select City/Municipality</option>
                            <option value="Tupi">Tupi</option>
                        </select>
                        <label class="block text-gray-700">Province:</label>
                        <select name="province" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Province</option>
                            <option value="South Cotabato">South Cotabato</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Civil Status:</label>
                        <select name="civil_status" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Work Status:</label>
                        <select name="work_status" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Work Status</option>
                            <option value="Employed">Employed</option>
                            <option value="Self-employed">Self-employed</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Position:</label>
                        <select name="position" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Position</option>
                            <option value="Youth Residence">Youth Residence</option>
                            <option value="Sk Chairperson">Sk Chairperson</option>
                            <option value="Sk Secretary">Sk Secretary</option>
                            <option value="Sk Treasurer">Sk Treasurer</option>
                            <option value="Sk Member">Sk Member</option>
                        </select>
                    </div>
                </div>

                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Family/Guardian Information</h2>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Parent/Guardian Name:</label>
                        <input type="text" name="parent_last_name" placeholder="Last Name" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="text" name="parent_first_name" placeholder="First Name" class="w-full p-2 border border-gray-300 rounded mb-2" required>
                        <input type="text" name="parent_middle_name" placeholder="Middle Name" class="w-full p-2 border border-gray-300 rounded">
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Relationship:</label>
                        <input type="text" name="parent_relationship" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Contact Number:</label>
                        <input type="tel" name="parent_contact" pattern="09[0-9]{9}" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>

                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Educational Background</h2>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">School/Institution:</label>
                        <input type="text" name="school" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Education Level:</label>
                        <select name="education_level" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Level</option>
                            <option value="Elementary">Elementary</option>
                            <option value="Junior High School">Junior High School</option>
                            <option value="Senior High School">Senior High School</option>
                            <option value="College">College</option>
                            <option value="Vocational">Vocational</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Year/Grade Level:</label>
                        <input type="text" name="year_level" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>

                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Emergency Contact</h2>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Emergency Contact Name:</label>
                        <input type="text" name="emergency_name" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Relationship:</label>
                        <input type="text" name="emergency_relationship" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="block text-gray-700">Contact Number:</label>
                        <input type="tel" name="emergency_contact" pattern="09[0-9]{9}" class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>

                <div class="form-section mb-4">
                    <h2 class="text-2xl font-semibold mb-4">Required Attachments</h2>
                    <div class="file-upload mb-4">
                        <label class="block text-gray-700">ID Photo (1x1 or 2x2):</label>
                        <input type="file" name="id_photo" accept="image/*" class="w-full p-2 border border-gray-300 rounded" required>
                        <div class="file-requirements">JPEG/PNG, max 2MB</div>
                    </div>
                    <div class="file-upload mb-4">
                        <label class="block text-gray-700">Birth Certificate:</label>
                        <input type="file" name="birth_cert" accept="image/*,application/pdf" class="w-full p-2 border border-gray-300 rounded" required>
                        <div class="file-requirements">JPEG/PNG/PDF, max 5MB</div>
                    </div>
                    <div class="file-upload mb-4">
                        <label class="block text-gray-700">Certification of Residence:</label>
                        <input type="file" name="residence_cert" accept="image/*,application/pdf" class="w-full p-2 border border-gray-300 rounded" required>
                        <div class="file-requirements">JPEG/PNG/PDF, max 5MB</div>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Application</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>