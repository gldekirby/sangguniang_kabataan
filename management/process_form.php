<?php
// Define upload directories
$uploadDirs = [
    'idPhoto' => 'uploads/id_photos/',
    'birthCertificate' => 'uploads/birth_certs/',
    'residenceCertificate' => 'uploads/residence_certs/'
];

// Create directories if they don't exist
foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Process form data
$response = ['status' => 'error', 'message' => 'Unknown error'];
$errors = [];

try {
    // Basic validation
    if (empty($_POST['username'])) {
        $errors['username'] = 'Username is required';
    }
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password is required';
    }
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        $errors['confirmPassword'] = 'Passwords do not match';
    }
    if (empty($_POST['firstName'])) {
        $errors['firstName'] = 'First name is required';
    }
    if (empty($_POST['lastName'])) {
        $errors['lastName'] = 'Last name is required';
    }

    // Process file uploads
    $uploadedFiles = [];
    foreach ($uploadDirs as $field => $dir) {
        if (!empty($_FILES[$field]['name'])) {
            $targetFile = $dir . basename($_FILES[$field]['name']);
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetFile)) {
                $uploadedFiles[$field] = $targetFile;
            } else {
                $errors[$field] = 'Failed to upload ' . $field;
            }
        } else {
            $errors[$field] = $field . ' is required';
        }
    }

    if (empty($errors)) {
        // Prepare success response
        $formData = $_POST;
        unset($formData['password']);
        unset($formData['confirmPassword']);
        
        $response = [
            'status' => 'success',
            'message' => 'Registration successful!',
            'data' => $formData,
            'files' => $uploadedFiles
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Please correct the errors',
            'errors' => $errors
        ];
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>