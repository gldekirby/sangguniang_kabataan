<?php
session_start();

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded admin credentials
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
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
    </style>
    <script>
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function handleSubmit(event) {
            event.preventDefault();
            showLoading();
            setTimeout(() => {
                document.querySelector('form').classList.add('fade-out');
                setTimeout(() => {
                    event.target.submit();
                }, 1000);
            }, 500);
        }

        // Show error message from PHP variable as alert if exists
        window.onload = function() {
            var error = <?php echo isset($error) ? json_encode($error) : 'null'; ?>;
            if (error) {
                alert(error);
            }
        };
    </script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div id="loading">
        <div class="flex justify-center items-center">
            <i class="fas fa-spinner fa-spin text-4xl"></i>
        </div>
        <div>Entering...</div>
    </div>
    <div class="bg-white bg-opacity-50 backdrop-blur-lg p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h6 class="text-1xl font-bold mb-6 text-center text-gray-800">Sangguniang Kabataan Youth Management</h6>
        <div class="flex justify-center mb-6">
            <img alt="Placeholder image of a company logo" class="w-24 h-24 rounded-full" height="100" src="bgi/sk_logo.png" width="100"/>
        </div>
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Admin Login</h2>
        <!-- Removed inline error message div as alerts will be shown via script -->
        <form action="" method="post" onsubmit="handleSubmit(event)">
            <div class="mb-4 input-icon">
                <i class="fas fa-user"></i>
                <input class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="username" name="username" placeholder="Username" required="" type="text"/>
            </div>
            <div class="mb-6 input-icon">
                <i class="fas fa-lock"></i>
                <input class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="password" name="password" placeholder="Password" required="" type="password"/>
            </div>
            <button class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500" type="submit">Login</button>
        </form>
    </div>
</body>
</html>