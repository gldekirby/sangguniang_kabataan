<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Youth Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 3rem;
        }

        #home {
            background-image: linear-gradient(rgba(243, 244, 246, 0.9), rgba(243, 244, 246, 0.9)), url('management/bgi/sk_background.jpg');
            background-size: cover;
            background-position: center;
        }

        #about {
            background-image: linear-gradient(rgba(243, 244, 246, 0.9), rgba(243, 244, 246, 0.9)), url('management/bgi/sk_background.jpg');
            background-size: cover;
            background-position: center;
        }

        #services {
            background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('management/bgi/sk_background.jpg');
            background-size: cover;
            background-position: center;
        }

        #contact {
            background-image: linear-gradient(rgba(243, 244, 246, 0.9), rgba(243, 244, 246, 0.9)), url('management/bgi/sk_background.jpg');
            background-size: cover;
            background-position: center;
        }

        /* Loading Screen Styles */
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Spinner Styles */
        .spinner {
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid #3498db; /* Change this color to your preferred spinner color */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        function showLoading(url) {
            document.getElementById('loading').style.display = 'flex';
            setTimeout(function() {
                window.location.href = url;
            }, 2000); // Delay of 2000 milliseconds (2 seconds)
        }
    </script>
</head>
<body class="font-roboto">
    <!-- Loading Screen -->
    <div id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <header class="bg-blue-600 text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="management/bgi/sk_logo.png" alt="SK Youth Management Logo" class="h-8 w-8 object-contain">
                <h1 class="text-2xl font-bold">SK Youth Management</h1>
            </div>
            <nav>
                <ul class="flex space-x-4">
                    <li><a class="hover:underline" href="#home">Home</a></li>
                    <li><a class="hover:underline" href="#about">About</a></li>
                    <li><a class="hover:underline" href="#services">Services</a></li>
                    <li><a class="hover:underline" href="#contact">Contact</a></li>
                    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
                    <li>
                        <a class="hover:underline text-1xl font-bold" href="management/signup_member.php" onclick="showLoading('management/signup_member.php'); return false;">Sign Up</a>/
                        <a class="hover:underline text-1xl font-bold" href="management/login_member.php" onclick="showLoading('management/login_member.php'); return false;">Login Up</a> 
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Layer 1: Home -->
    <section class="text-white h-screen flex items-center relative" id="home">
        <div class="container mx-auto text-center px-1 pb-8">
            <div class="flex flex-col justify-between h-[calc(100vh-8rem)]">
                <div>
                    <h1 class="text-4xl text-black md:text-5xl font-bold mb-2">Welcome to SK Youth Management</h1>
                    <p class="mb-4 text-black text-lg md:text-xl max-w-2xl mx-auto">We provide the best services to support youth initiatives and empower communities.</p>
                </div>
                
                <div class="flex flex-col items-center">
                    <img alt="Youth management imagery" 
                        class="mx-auto mb-6 object-contain w-full max-w-4xl h-[50vh]" 
                        src="management/bgi/sk_logo.png"/>
                    
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-all"
                            onclick="showLoading('management/login_member.php'); return false;">
                        Get Started
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="min-h-screen flex items-center" id="about">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img alt="Our team" class="rounded-lg shadow-xl w-full" 
                         src="management/bgi/sk_logo2.png">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">About Us</h2>
                    <p class="text-lg md:text-xl text-gray-700 mb-8">
                        We are a youth-focused organization dedicated to creating opportunities for personal growth, 
                        leadership development, and community engagement. Our programs are designed to empower young 
                        individuals to reach their full potential.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="min-h-screen flex items-center" id="services">
        <div class="container mx-auto px-4 py-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-gray-50 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                    <img alt="Leadership training" class="mx-auto mb-6 h-32 w-32 object-cover rounded-full" 
                         src="https://storage.googleapis.com/a1aa/image/2kMpp05-1LXKkZFx2d_1bYQQD1jAUDGDbCclDaxFfgs.jpg">
                    <h3 class="text-xl font-bold mb-4 text-center">Leadership Development</h3>
                    <p class="text-gray-600 text-center">Comprehensive programs to build leadership skills and team management capabilities.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                    <img alt="Workshop" class="mx-auto mb-6 h-32 w-32 object-cover rounded-full" 
                         src="https://storage.googleapis.com/a1aa/image/mt56Aj1AhgP9vLOEJsVlsl7Ghe8PKuWZrdsFlZKl1B8.jpg">
                    <h3 class="text-xl font-bold mb-4 text-center">Skill Workshops</h3>
                    <p class="text-gray-600 text-center">Practical workshops covering digital literacy, communication, and career development.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                    <img alt="Networking" class="mx-auto mb-6 h-32 w-32 object-cover rounded-full" 
                         src="https://storage.googleapis.com/a1aa/image/ljLVLnDa5_l0fSCDNLUIaKTbzc9GyOJUQ2zGnSBxwTM.jpg">
                    <h3 class="text-xl font-bold mb-4 text-center">Community Building</h3>
                    <p class="text-gray-600 text-center">Networking events and community projects to foster collaboration and social responsibility.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="min-h-screen flex items-center" id="contact">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img alt="Contact illustration" class="rounded-lg shadow-xl w-full" 
                        src="https://storage.googleapis.com/a1aa/image/GNFgUVKjcu3Nviy8WSjneQCeoqMq2ae72HQyjMiSwHs.jpg">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-8">Contact Us</h2>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold">Address</h3>
                                <p class="text-gray-700">Purok 3-A, Brgy. Poblacion, Tupi, South Cotabato </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="fas fa-phone-alt text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold">Phone</h3>
                                <p class="text-gray-700">+6393-6110-2342</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="fas fa-envelope text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold">Email</h3>
                                <p class="text-gray-700">info@skyouthmanagement.com</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="fas fa-clock text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold">Working Hours</h3>
                                <p class="text-gray-700">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                <p class="text-gray-700">Saturday: 10:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <h3 class="text-lg font-semibold mb-3">Follow Us</h3>
                            <div class="flex space-x-4">
                                <a href="#" class="bg-blue-100 text-blue-600 p-3 rounded-full hover:bg-blue-200 transition-colors">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="bg-blue-100 text-blue-600 p-3 rounded-full hover:bg-blue-200 transition-colors">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="bg-blue-100 text-blue-600 p-3 rounded-full hover:bg-blue-200 transition-colors">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="bg-blue-100 text-blue-600 p-3 rounded-full hover:bg-blue-200 transition-colors">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-blue-600 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="mb-4">© 2025 SK Youth Management. All rights reserved.</p>
            <div class="flex justify-center space-x-6">
                <a href="#" class="hover:text-blue-200 transition-colors"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="hover:text-blue-200 transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="hover:text-blue-200 transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-blue-200 transition-colors"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>