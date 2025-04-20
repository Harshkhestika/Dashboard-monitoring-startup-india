<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'startup_india';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Startup India Monitoring Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <style>
        .sidebar-active {
            background-color: rgba(237, 137, 54, 0.1);
            border-left: 4px solid #ed8936;
            color: #ed8936;
        }
        .sidebar-item:hover {
            background-color: rgba(237, 137, 54, 0.05);
        }
        .notification-badge {
            top: 8px;
            right: 8px;
        }
        .section {
            display: none;
        }
        .active-section {
            display: block;
        }
        .user-interface {
            display: none;
        }
        .team-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ed8936, #3b82f6);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .team-card:hover::before {
            transform: scaleX(1);
        }
        .skill-tag {
            transition: all 0.2s ease;
        }
        .skill-tag:hover {
            transform: scale(1.05);
        }
        .timeline-item {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e5e7eb;
        }
        .timeline-dot {
            position: absolute;
            left: -6px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #3b82f6;
            border: 2px solid white;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .timeline-item:hover .timeline-dot {
            transform: scale(1.2);
            background-color: #ed8936;
        }
        .tech-card {
            transition: all 0.3s ease;
        }
        .tech-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        @media print {
            .no-print {
                display: none;
            }
            .print-show {
                display: block !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div class="flex h-screen overflow-hidden">
        <!-- Admin Interface -->
        <div id="admin-interface" class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div class="admin-sidebar bg-white shadow-md w-64 h-screen overflow-y-auto fixed left-0 top-0 no-print">
                <div class="p-4">
                    <div class="flex items-center justify-center">
                        <img src="india.png" class="h-8 w-8 mr-2" alt="Startup India Logo">
                        <h1 class="text-xl font-bold text-gray-800">Startup India<span class="text-orange-500">Admin</span></h1>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="index.php">
                        <div id="admin-dashboard-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-tachometer-alt w-6"></i>
                            <span class="ml-2">Dashboard</span>
                        </div>
                    </a>
                    <!-- <a href="user-management.php">
                        <div id="admin-user-management-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-users w-6"></i>
                            <span class="ml-2">User Management</span>
                        </div>
                    </a> -->
                    <a href="startup-monitoring.php">
                        <div id="admin-startup-monitoring-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-chart-line w-6"></i>
                            <span class="ml-2">Startup Monitoring</span>
                        </div>
                    </a>
                    <a href="applications.php">
                        <div id="admin-applications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-file-alt w-6"></i>
                            <span class="ml-2">Applications</span>
                        </div>
                    </a>
                    <a href="resources.php">
                        <div id="admin-resources-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-book w-6"></i>
                            <span class="ml-2">Resources</span>
                        </div>
                    </a>
                    <a href="notification.php">
                        <div id="admin-notifications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-bell w-6"></i>
                            <span class="ml-2">Notifications</span>
                            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">5</span>
                        </div>
                    </a>
                    <a href="profile.php">
                        <div id="admin-profile-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-user w-6"></i>
                            <span class="ml-2">Profile</span>
                        </div>
                    </a>
                    
                    <a href="newstartup.html">
                        <div class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <button class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center space-x-2 transition-all">
                                <i class="fas fa-plus"></i>
                                <span>Add Startup</span>
                            </button>
                        </div>
                    </a>
                    <a href="about-us.php">
                        <div id="admin-about-link" class="sidebar-item sidebar-active flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                            <i class="fas fa-info-circle w-6"></i>
                            <span class="ml-2">About Us</span>
                        </div>
                    </a>
                    <a href="login.php">
                        <div class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer mt-8">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span class="ml-2">
                                Logout</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-x-hidden overflow-y-auto ml-64 bg-gray-100">
                <!-- User Header -->
                <header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between no-print">
                    <div class="flex items-center">
                        <i class="fas fa-bars text-gray-500 text-xl cursor-pointer mr-6"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h3>
                    </div>

                    <div class="flex items-center">
                        <!-- <div class="relative mr-4">
                            <i class="fas fa-search text-gray-500"></i>
                        </div> -->
                        
                        <a href="notification.php">
                            <div class="relative mr-4">
                                <i class="fas fa-bell text-gray-500"></i>
                                <span class="absolute bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center notification-badge">5</span>
                            </div>
                        </a>
                    
                        <div class="flex items-center cursor-pointer hover:text-orange-500 transition-colors" onclick="window.location.href='profile.php'">
                            <img class="h-8 w-8 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
                            <span class="ml-2 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </div>
                    </div>
                </header>

                <!-- About Us Content Section -->
                <div class="p-6">
                    <!-- Hero Section with Purpose Statement -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
    <div class="relative">
        <div class="h-60 bg-gradient-to-r from-blue-500 to-orange-500 flex items-center justify-center">
            <div class="absolute inset-0 bg-black opacity-30"></div>
            <div class="relative z-10 text-center px-4">
                <h1 class="text-4xl font-bold text-white mb-2">Empowering India's Startup Ecosystem</h1>
                <p class="text-xl text-white max-w-3xl mx-auto">Data-driven insights to fuel your entrepreneurial journey</p>
            </div>
        </div>
    </div>
    <div class="p-8 bg-white">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Vision for Startup India</h2>
                <p class="text-lg text-gray-700 leading-relaxed mb-6">
                    The Startup India Monitoring Dashboard is a comprehensive platform designed to nurture, 
                    track, and accelerate India's growing startup ecosystem. We provide entrepreneurs with 
                    powerful tools to launch their ventures, analyze sector growth, monitor performance metrics, 
                    and access valuable resources - all through an intuitive, data-rich interface.
                </p>
                <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <h3 class="font-semibold text-blue-800 mb-2">Why Our Dashboard Matters</h3>
                    <p class="text-gray-700">
                        In a nation with over 90,000 recognized startups, our platform brings clarity to the 
                        entrepreneurial landscape, helping founders make informed decisions and stakeholders 
                        identify high-potential opportunities.
                    </p>
                </div>
            </div>
            
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Key Functionalities</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-orange-100 p-2 rounded-full mr-4">
                            <i class="fas fa-rocket text-orange-500"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Startup Registration & Tracking</h4>
                            <p class="text-gray-600">Seamlessly register new ventures and monitor their growth trajectory with comprehensive analytics.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-full mr-4">
                            <i class="fas fa-chart-pie text-blue-500"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Sector-wise Analysis</h4>
                            <p class="text-gray-600">Compare performance across industries with interactive visualizations of employment, revenue, and growth metrics.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-full mr-4">
                            <i class="fas fa-book text-green-500"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Entrepreneur Resources</h4>
                            <p class="text-gray-600">Access curated guides, funding opportunities, and regulatory information to accelerate your business growth.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 p-2 rounded-full mr-4">
                            <i class="fas fa-network-wired text-purple-500"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Ecosystem Connectivity</h4>
                            <p class="text-gray-600">Connect startups with investors, mentors, and government schemes to foster collaboration.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Impact Statistics Section -->
<div class="bg-gradient-to-r from-indigo-500 to-blue-600 rounded-lg shadow-lg p-8 mb-12 text-white">
    <h2 class="text-2xl font-bold mb-8 text-center">Transforming India's Entrepreneurial Landscape</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div class="p-4">
            <div class="text-4xl font-bold mb-2">1000+</div>
            <div class="text-sm opacity-90">Startups Registered</div>
        </div>
        <div class="p-4">
            <div class="text-4xl font-bold mb-2">10+</div>
            <div class="text-sm opacity-90">Industry Sectors Tracked</div>
        </div>
        <div class="p-4">
            <div class="text-4xl font-bold mb-2">500+</div>
            <div class="text-sm opacity-90">Resources Available</div>
        </div>
        <div class="p-4">
            <div class="text-4xl font-bold mb-2">15M+</div>
            <div class="text-sm opacity-90">Jobs Facilitated</div>
        </div>
    </div>
    <div class="mt-8 text-center">
        <button class="bg-white text-indigo-600 hover:bg-indigo-100 font-medium py-2 px-6 rounded-full transition-all">
            Explore Dashboard Features
        </button>
    </div>
</div>

<!-- Then continue with the existing "Meet Our Team" section -->
                    <!-- Hero Section -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                        <div class="relative">
                            <div class="h-60 bg-gradient-to-r from-blue-500 to-orange-500 flex items-center justify-center">
                                <div class="absolute inset-0 bg-black opacity-30"></div>
                                <div class="relative z-10 text-center px-4">
                                    <h1 class="text-4xl font-bold text-white mb-2">Meet Our Team</h1>
                                    <p class="text-xl text-white max-w-3xl mx-auto">The talented minds behind the Startup India Monitoring Dashboard</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 bg-white">
                            <div class="max-w-4xl mx-auto">
                                <p class="text-lg text-gray-700 leading-relaxed">
                                    Our team of five passionate developers and designers came together with a shared vision: 
                                    to create a powerful, intuitive dashboard that empowers India's startup ecosystem. 
                                    Each member brings unique skills and perspectives, creating a synergy that enabled us 
                                    to build this comprehensive platform for monitoring and nurturing startups across the nation.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members Section -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Our Development Team</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                        <!-- Team Member 1 -->
                        <div class="team-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-1 bg-gradient-to-r from-orange-500 to-blue-500">
                                <img class="w-full h-58 object-cover object-center" src="harhprofile.png" alt="Harsh Sharma">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900">Harsh Khestika</h3>
                                <p class="text-orange-500 font-medium mb-3">Project Lead & Full-Stack Developer</p>
                                <p class="text-gray-600 mb-4">
                                    I am student of Lovely Professional University in 'B.tech Computer Science'.
                                    Harsh leads our development efforts with over 1 years of experience in web application development.
                                </p>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Skills:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">PHP</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">MySQL</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">JavaScript</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Tailwind CSS</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">System Architecture</span>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="https://www.linkedin.com/in/harsh-khestika-36533a298/" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                    <a href="https://github.com/Harshkhestika/Dashboard-monitoring-startup-india" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-github text-xl"></i>
                                    </a>
                                    <a href="https://gmail.com/" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fas fa-envelope text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Team Member 2 -->
                        <div class="team-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-1 bg-gradient-to-r from-orange-500 to-blue-500">
                                <img class="w-full h-70 object-cover object-center" src="tej1.png" alt="Tej Patel">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900">Tejhasvi</h3>
                                <p class="text-orange-500 font-medium mb-3">UI/UX Designer</p>
                                <p class="text-gray-600 mb-4">
                                    Tejhasvi brings her creative vision to the project, designing an intuitive and user-friendly interface.
                                    Her attention to detail ensures a seamless user experience throughout the dashboard.
                                </p>
                                
                                <div class="flex space-x-3">
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-dribbble text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fas fa-envelope text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Team Member 3 -->
                        <!-- <div class="team-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-1 bg-gradient-to-r from-orange-500 to-blue-500">
                                <img class="w-full h-55 object-cover object-center" src="https://i.pinimg.com/474x/0b/97/6f/0b976f0a7aa1aa43870e1812eee5a55d.jpg" alt="Vikram Singh">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900">Vikram Singh</h3>
                                <p class="text-orange-500 font-medium mb-3">Backend Developer</p>
                                <p class="text-gray-600 mb-4">
                                    Vikram developed the robust backend systems that power the dashboard, including
                                    the database architecture, API endpoints, and data processing functions.
                                </p>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Skills:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">PHP</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">MySQL</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">API Development</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Security</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Performance Optimization</span>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-github text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fas fa-envelope text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <!-- Team Member 4 -->
                        <!-- <div class="team-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-1 bg-gradient-to-r from-orange-500 to-blue-500">
                                <img class="w-full h-55 object-cover object-center" src="https://i.pinimg.com/474x/b3/e5/db/b3e5db5a3bf1399f74500a6209462794.jpg" alt="Anjali Mishra">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900">Anjali Mishra</h3>
                                <p class="text-orange-500 font-medium mb-3">Frontend Developer & Data Visualization</p>
                                <p class="text-gray-600 mb-4">
                                    Anjali specializes in frontend development and data visualization, creating
                                    the interactive charts and graphs that make complex startup data accessible.
                                </p>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Skills:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">JavaScript</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Chart.js</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Tailwind CSS</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Data Visualization</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Responsive Design</span>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-github text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fas fa-envelope text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div> -->

                        <!-- Team Member 5 -->
                        <!-- <div class="team-card bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="p-1 bg-gradient-to-r from-orange-500 to-blue-500">
                                <img class="w-full h-55 object-cover object-center" src="https://i.pinimg.com/474x/7c/d8/14/7cd81479ea9c9d507249c73debd074fa.jpg" alt="Karthik Iyer">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900">Karthik Iyer</h3>
                                <p class="text-orange-500 font-medium mb-3">Quality Assurance & DevOps</p>
                                <p class="text-gray-600 mb-4">
                                    Karthik ensures our dashboard meets the highest quality standards through rigorous testing
                                    and manages our deployment pipelines for seamless updates.
                                </p>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Skills:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Test Automation</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">DevOps</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">CI/CD</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Security Testing</span>
                                        <span class="skill-tag inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Performance Monitoring</span>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-linkedin text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fab fa-github text-xl"></i>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-orange-500 transition-colors">
                                        <i class="fas fa-envelope text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <!-- Mission and Values -->
                    <div class="bg-white rounded-lg shadow-sm p-8 mb-12">
                        <div class="max-w-4xl mx-auto">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Our Mission & Values</h2>
                            
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Mission</h3>
                                <p class="text-gray-700 leading-relaxed mb-4">
                                    Our mission is to empower India's startup ecosystem by providing a comprehensive, 
                                    data-driven monitoring platform that facilitates growth, connects stakeholders, 
                                    and simplifies regulatory compliance. We aim to be the catalyst that transforms 
                                    innovative ideas into successful businesses that drive economic growth and create jobs.
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-chart-line text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Excellence</h4>
                                    <p class="text-gray-600">
                                        We are committed to technical excellence and continuous improvement in all aspects of our platform.
                                    </p>
                                </div>
                                
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-lightbulb text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Innovation</h4>
                                    <p class="text-gray-600">
                                        We embrace creative solutions and forward-thinking approaches to complex problems.
                                    </p>
                                </div>
                                
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-users text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Collaboration</h4>
                                    <p class="text-gray-600">
                                        We believe in the power of teamwork and partnerships to create impactful solutions.
                                    </p>
                                </div>
                                
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-shield-alt text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Integrity</h4>
                                    <p class="text-gray-600">
                                        We uphold the highest standards of ethics, transparency, and data security.
                                    </p>
                                </div>
                                
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-user-friends text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">User-Centered</h4>
                                    <p class="text-gray-600">
                                        We design with empathy, keeping our users' needs at the core of every decision.
                                    </p>
                                </div>
                                
                                <div class="p-5 border border-gray-200 rounded-lg hover:border-orange-500 transition-colors">
                                    <div class="text-orange-500 mb-3">
                                        <i class="fas fa-rocket text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Impact</h4>
                                    <p class="text-gray-600">
                                        We measure our success by the positive difference we make in India's startup landscape.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Technologies Used -->
                    <div class="bg-white rounded-lg shadow-sm p-8 mb-12">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Technologies We Used</h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fab fa-php text-5xl text-blue-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">PHP</h3>
                                <p class="text-gray-600 text-sm">
                                    Server-side scripting language powering our backend logic and data processing.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fas fa-database text-5xl text-orange-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">MySQL</h3>
                                <p class="text-gray-600 text-sm">
                                    Relational database management system for storing and retrieving startup data.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fab fa-js text-5xl text-yellow-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">JavaScript</h3>
                                <p class="text-gray-600 text-sm">
                                    Client-side scripting for interactive elements and dynamic content.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fab fa-css3-alt text-5xl text-blue-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Tailwind CSS</h3>
                                <p class="text-gray-600 text-sm">
                                    Utility-first CSS framework for creating responsive and modern designs.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fas fa-chart-bar text-5xl text-green-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Chart.js</h3>
                                <p class="text-gray-600 text-sm">
                                    JavaScript library for flexible and beautiful data visualization.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fas fa-code-branch text-5xl text-purple-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Git</h3>
                                <p class="text-gray-600 text-sm">
                                    Version control system for collaborative development and code management.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fas fa-lock text-5xl text-red-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Security Tools</h3>
                                <p class="text-gray-600 text-sm">
                                    Advanced security implementations to protect sensitive startup data.
                                </p>
                            </div>
                            
                            <div class="tech-card p-5 border border-gray-200 rounded-lg text-center">
                                <div class="mb-4 flex justify-center">
                                    <i class="fas fa-server text-5xl text-gray-700"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Hosting Infrastructure</h3>
                                <p class="text-gray-600 text-sm">
                                    Secure and scalable cloud infrastructure ensuring high availability.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="bg-gradient-to-r from-blue-500 to-orange-500 rounded-lg shadow-sm p-8 mb-12 relative overflow-hidden">
                        <div class="absolute inset-0 bg-pattern opacity-10"></div>
                        <div class="relative z-10">
                            <div class="max-w-4xl mx-auto text-center">
                                <h2 class="text-2xl font-bold text-white mb-4">Get In Touch With Our Team</h2>
                                <p class="text-white/90 mb-8 max-w-2xl mx-auto">
                                    Have questions, feedback, or interested in collaborating? We'd love to hear from you!
                                    Our team is dedicated to supporting India's startup ecosystem.
                                </p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                                    <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg hover:bg-white/20 transition-colors">
                                        <div class="text-white text-3xl mb-3">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-white mb-2">Email Us</h3>
                                        <p class="text-white/90">hkhestika@gmail.com</p>
                                    </div>
                                    
                                    <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg hover:bg-white/20 transition-colors">
                                        <div class="text-white text-3xl mb-3">
                                            <i class="fas fa-phone-alt"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-white mb-2">Call Us</h3>
                                        <p class="text-white/90">+91 8005560721</p>
                                    </div>
                                    
                                    <div class="bg-white/10 backdrop-blur-sm p-6 rounded-lg hover:bg-white/20 transition-colors">
                                        <div class="text-white text-3xl mb-3">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-white mb-2">Visit Us</h3>
                                        <p class="text-white/90">Startup Hub, LPU University, Phagwara (Punjab) ,India</p>
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex justify-center space-x-6">
                                    <a href="https://www.linkedin.com/in/harsh-khestika-36533a298/" class="bg-white text-orange-500 hover:bg-orange-100 transition-colors rounded-full p-3">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="#" class="bg-white text-orange-500 hover:bg-orange-100 transition-colors rounded-full p-3">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://github.com/Harshkhestika/Dashboard-monitoring-startup-india" class="bg-white text-orange-500 hover:bg-orange-100 transition-colors rounded-full p-3">
                                        <i class="fab fa-github"></i>
                                    </a>
                                    <a href="#" class="bg-white text-orange-500 hover:bg-orange-100 transition-colors rounded-full p-3">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="text-center text-gray-600 mb-8">
                        <p>© 2025 Startup India Monitoring Dashboard. All rights reserved.</p>
                        <p class="mt-2 text-sm">Crafted with <i class="fas fa-heart text-red-500"></i> by our talented team</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add subtle parallax effect to hero section
        window.addEventListener('scroll', function() {
            const heroSection = document.querySelector('.bg-gradient-to-r');
            if (heroSection) {
                const scrollPos = window.scrollY;
                heroSection.style.backgroundPosition = `center ${scrollPos * 0.1}px`;
            }
        });

        // Simple animation for timeline dots on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-pulse');
                    setTimeout(() => {
                        entry.target.classList.remove('animate-pulse');
                    }, 2000);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.timeline-dot').forEach(dot => {
            observer.observe(dot);
        });
    </script>
</body>
</html>
