<?php
session_start();
include '../config.php';

// Check if there's an alert message to display
if (isset($_SESSION['alert'])) {
    echo "<script>alert('" . $_SESSION['alert'] . "');</script>";
    unset($_SESSION['alert']); // Clear the alert after displaying it
}

// Initialize error message
$error = '';
$login_method = 'username'; // Default login method

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'login' and 'password' keys exist in $_POST
    if (isset($_POST['login']) && isset($_POST['password'])) {
        // Get the login method if set, otherwise default to username
        $login_method = $_POST['login_method'] ?? 'username';
        
        // Validate and sanitize inputs
        $login = htmlspecialchars(trim($_POST['login']));
        $password = $_POST['password'];

        // Check if login and password are provided
        if (empty($login) || empty($password)) {
            $error = "Please fill in both login and password fields.";
        } else {
            // Validate based on selected login method
            $valid = false;
            switch ($login_method) {
                case 'email':
                    $valid = filter_var($login, FILTER_VALIDATE_EMAIL);
                    if (!$valid) {
                        $error = "Please enter a valid email address.";
                    }
                    break;
                case 'contact_number':
                    $valid = is_numeric($login) && strlen($login) >= 8;
                    if (!$valid) {
                        $error = "Please enter a valid contact number (digits only, at least 8 characters).";
                    }
                    break;
                case 'username':
                    $valid = preg_match('/^[a-zA-Z0-9_]+$/', $login);
                    if (!$valid) {
                        $error = "Username can only contain letters, numbers, and underscores.";
                    }
                    break;
            }

            if ($valid) {
                // Prepare the query based on login method
                $query = "SELECT * FROM members WHERE $login_method = ?";
                
                // Prepare and execute the query
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    die("Database query failed: " . $conn->error);
                }
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                // Check if a member was found
                if ($result->num_rows > 0) {
                    $member = $result->fetch_assoc();

                    // Verify the password
                    if ($password === $member['password']) {
                        // Update status to 'active' in the database
                        $update_query = "UPDATE members SET status = 'active' WHERE member_id = ?";
                        $update_stmt = $conn->prepare(query: $update_query);
                        $update_stmt->bind_param("i", $member['member_id']);
                        $update_stmt->execute();

                        // Set session variables
                        $_SESSION['member_id'] = $member['member_id'];
                        $_SESSION['username'] = $member['username'];

                        // Redirect to the dashboard
                        header("Location: dashboard_member.php");
                        exit();
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "No member found with that $login_method.";
                }
            }
        }
    } else {
        $error = "Please fill in both login and password fields.";
    }
}
?>
<!DOCTYPE html>
<!-- Rest of your HTML login form -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Loading screen styles */
        #loading {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            text-align: center;
            color: white;
            font-size: 20px;
            padding-top: 20%;
        }
        .input-icon {
            position: relative;
        }
        .input-icon input {
            padding-left: 2.5rem;
        }
        .input-icon i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
        }
        .fade-out {
            animation: fadeOut 1s forwards;
        }
        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
        body {
            background-image: url('bgi/sk_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .login-method-btn {
            transition: all 0.3s ease;
        }
        .login-method-btn.active {
            color: #3B82F6;
            transform: scale(1.2);
        }
        .login-method-btn:hover {
            transform: scale(1.1);
        }
        .error-message {
            transition: opacity 0.5s ease;
        }
        .error-hidden {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            border: 0;
            overflow: hidden;
        }
    </style>
    <script>
        let currentLoginMethod = '<?php echo $login_method; ?>';
        let errorTimeout;
        
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function handleSubmit(event) {
            // Clear any existing timeout
            clearTimeout(errorTimeout);
            
            // Validate based on current method before submitting
            const loginInput = document.getElementById('login');
            const value = loginInput.value.trim();
            let valid = true;
            let error = '';
            
            switch (currentLoginMethod) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        valid = false;
                        error = "Please enter a valid email address.";
                    }
                    break;
                case 'contact_number':
                    if (!/^\d+$/.test(value) || value.length < 8) {
                        valid = false;
                        error = "Please enter a valid contact number (digits only, at least 8 characters).";
                    }
                    break;
                case 'username':
                    if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                        valid = false;
                        error = "Username can only contain letters, numbers, and underscores.";
                    }
                    break;
            }
            
            if (!valid) {
                event.preventDefault();
                showError(error);
                return;
            }
            
            event.preventDefault();
            showLoading();
            setTimeout(() => {
                document.querySelector('form').classList.add('fade-out');
                setTimeout(() => {
                    event.target.submit();
                }, 1000);
            }, 500);
        }

        function switchLoginMethod(method) {
            // Clear any existing error timeout and hide error
            clearTimeout(errorTimeout);
            hideError();
            
            currentLoginMethod = method;
            const input = document.getElementById('login');
            const icon = document.querySelector('.login-icon i');
            const hiddenInput = document.getElementById('login_method');
            const buttons = document.querySelectorAll('.login-method-btn');
            
            // Update active button
            buttons.forEach(btn => {
                if (btn.getAttribute('data-method') === method) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            // Update placeholder and icon
            if (method === 'username') {
                input.placeholder = 'Username';
                icon.className = 'fas fa-user';
            } else if (method === 'contact_number') {
                input.placeholder = 'Contact Number';
                icon.className = 'fas fa-phone';
            } else if (method === 'email') {
                input.placeholder = 'Email';
                icon.className = 'fas fa-envelope';
            }
            
            // Update hidden input
            hiddenInput.value = method;
        }
        
        function showError(message) {
            const errorDiv = document.getElementById('error-display');
            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.remove('error-hidden');
                
                // Set timeout to hide error after 5 seconds
                errorTimeout = setTimeout(hideError, 5000);
            }
        }
        
        function hideError() {
            const errorDiv = document.getElementById('error-display');
            if (errorDiv) {
                errorDiv.classList.add('error-hidden');
            }
        }
        
        // Initialize the form with the current login method
        document.addEventListener('DOMContentLoaded', function() {
            switchLoginMethod(currentLoginMethod);
            
            // If there's an error from PHP, show it and set timeout to hide
            const errorDiv = document.getElementById('error-display');
            if (errorDiv && errorDiv.textContent.trim() !== '') {
                errorTimeout = setTimeout(hideError, 5000);
            }
        });
    </script>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div id="loading">Entering...</div>
    <div class="bg-white bg-opacity-50 backdrop-blur-lg p-8 rounded-lg shadow-lg w-full max-w-sm" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="flex justify-center mb-6">
            <img alt="Company Logo" class="w-24 h-24 rounded-full" src="bgi/sk_logo.png"/>
        </div>
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Member Login</h2>
        
        <div id="error-display" class="error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center transition-all duration-500 <?php echo empty($error) ? 'error-hidden' : ''; ?>">
            <?php echo htmlspecialchars($error); ?>
        </div>

        <form method="post" action="" onsubmit="handleSubmit(event)">
            <input type="hidden" id="login_method" name="login_method" value="<?php echo $login_method; ?>">
            
            <div class="mb-4 input-icon">
                <div class="login-icon">
                    <i class="fas fa-user"></i>
                </div>
                <input type="text" id="login" name="login" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="Username"/>
            </div>
            
            <div class="mb-6 input-icon">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="Password"/>
            </div>
            
            <div class="flex justify-center space-x-6 mb-6">
                <button type="button" data-method="username" onclick="switchLoginMethod('username')" class="login-method-btn text-gray-600 hover:text-blue-500 active" title="Username">
                    <i class="fas fa-user text-xl"></i>
                </button>
                <button type="button" data-method="contact_number" onclick="switchLoginMethod('contact_number')" class="login-method-btn text-gray-600 hover:text-blue-500" title="Phone">
                    <i class="fas fa-phone text-xl"></i>
                </button>
                <button type="button" data-method="email" onclick="switchLoginMethod('email')" class="login-method-btn text-gray-600 hover:text-blue-500" title="Email">
                    <i class="fas fa-envelope text-xl"></i>
                </button>
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300">
                Login
            </button>
            
            <p class="text-center text-sm text-gray-600 mt-4">
                Don't have an account? 
                <a href="signup_member.php" class="text-blue-500 hover:text-blue-700 font-medium">Register</a>
            </p>
        </form>
    </div>
</body>
</html>