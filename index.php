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

// Fetch startup counts grouped by name
$sql = "SELECT sector, COUNT(*) as total FROM startups GROUP BY sector";
$result = $conn->query($sql);

$labels = [];
$data = [];

if ($result->num_rows > 0) {
    $totalStartups = 0;
    $rawData = [];

    while ($row = $result->fetch_assoc()) {
        $rawData[] = $row;
        $totalStartups += $row['total'];
    }

    foreach ($rawData as $row) {
        $labels[] = $row['sector'];
        $data[] = round(($row['total'] / $totalStartups) * 100, 2); // % calculation
    }
}

// Count total applications (number of rows in startups)
$total_applications = 0;
$app_sql = "SELECT COUNT(startup_name) AS total_applications FROM startups";
$app_result = $conn->query($app_sql);
if ($app_result && $row = $app_result->fetch_assoc()) {
    $total_applications = $row['total_applications'];
}

// Count total users (based on email)
$total_users = 0;
$user_sql = "SELECT COUNT(email) AS total_users FROM user";
$user_result = $conn->query($user_sql);
if ($user_result && $row = $user_result->fetch_assoc()) {
    $total_users = $row['total_users'];
}

// Sum total revenue
$total_revenue = 0;
$revenue_sql = "SELECT SUM(revenue) AS total_revenue FROM startups";
$revenue_result = $conn->query($revenue_sql);
if ($revenue_result && $row = $revenue_result->fetch_assoc()) {
    $total_revenue = $row['total_revenue'];
}

$sectorJobs = [];

$sql = "SELECT sector, SUM(job) as total_jobs FROM startups GROUP BY sector";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sectorJobs[$row['sector']] = $row['total_jobs'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startup India Monitoring Dashboard</title>
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
        .table-container {
            overflow-x: auto;
        }
        .chart-container {
            height: 300px;
            width: 100%;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 50;
            background-color: rgba(0, 0, 0, 0.5);
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
                    <div id="admin-dashboard-link" class="sidebar-item sidebar-active flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-2">Dashboard</span>
                    </div>
                    
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
                    <div id="admin-resources-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-book w-6"></i>
                        <span class="ml-2"><a href="resources.php">Resources</a></span>
                    </div>
                    
                    

                    <a href="notification.php" ><div id="admin-notifications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-bell w-6"></i>
                        <span class="ml-2">Notifications</span>
                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">5</span>
                    </div>
                </a>
                
                <a href="profile.php"> <div id="admin-profile-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-user w-6" ></i>
                    <span class="ml-2">Profile</span>
                </div></a>
                <a href="user-management.php">
                <div id="admin-user-management-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2">About us</span>
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

                    <!-- search bar in header -->

                    <div class="flex items-center">
                        <!-- <div class="relative mr-4">
                            <i class="fas fa-search text-gray-500"></i>
                        </div> -->
                        
                        <a href="profile.php">
                        <div class="relative mr-4">
                            <!-- <i class="fas fa-bell text-gray-500"></i> -->
                            <!-- <span class="absolute bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center notification-badge">3</span> -->
                        </div>
                        </a>
                    
                        <div class="flex items-center cursor-pointer hover:text-orange-500 transition-colors" onclick="window.location.href='profile.php'">
    <img class="h-8 w-8 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
    <span class="ml-2 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
</div>
                    </div>
                </header>
                        
                <!-- Admin Dashboard Section -->
                <div id="admin-dashboard" class="section active-section p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Applications</p>
                                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_applications; ?></h3>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 12.5% increase from last month</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Startups User</p>
                                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_users; ?></h3>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 8.2% increase from last month</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Funding</p>
                                    <h3 class="text-2xl font-bold text-gray-800">₹<?php echo number_format($total_revenue); ?></h3>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 15.7% increase from last month</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Startup Growth Trend Card -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Startup Growth Trend</h3>
                            <div class="chart-container">
                                <canvas id="startupGrowthChart"></canvas>
                            </div>
                        </div>
                            
                        <!-- Sector Distribution Card -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Sector Distribution</h3>
                            <div class="chart-container">
                                <canvas id="sectorDistributionChart"></canvas>
                            </div>
                        </div>
                            
                        <!-- Include Chart.js ONCE -->
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            
                        <!-- Chart Scripts -->
                        <script>
                            // Line Chart: Startup Growth Trend
                            const ctx1 = document.getElementById('startupGrowthChart').getContext('2d');
                            const startupGrowthChart = new Chart(ctx1, {
                                type: 'line',
                                data: {
                                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun' ],
                                    datasets: [{
                                        label: 'Growth (%)',
                                        data: [5, 10, 20, 30, 45, 60],
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        fill: true,
                                        tension: 0.4,
                                        pointBackgroundColor: '#3b82f6',
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return value + '%';
                                                }
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: true,
                                            labels: {
                                                color: '#4b5563'
                                            }
                                        }
                                    }
                                }
                            });
                        
                            // Bar Chart: Sector Distribution
                            const ctx2 = document.getElementById('sectorDistributionChart').getContext('2d');
                            const sectorDistributionChart = new Chart(ctx2, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo json_encode($labels); ?>,
                                    datasets: [{
                                        label: 'Percentage of Startups (%)',
                                        data: <?php echo json_encode($data); ?>,
                                        backgroundColor: '#34d399',
                                        borderColor: '#10b981',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            ticks: {
                                                callback: function(value) {
                                                    return value + '%';
                                                }
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: true
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return context.parsed.y + '%';
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        </script>
                        
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Recent Applications</h3>
                            <div class="table-container">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Startup</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">TechSprint</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">E-commerce</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">15 June 2023</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">GreenEarth</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">CleanTech</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">12 June 2023</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">MediHelp</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">Healthcare</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">10 June 2023</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">EduTech Pro</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">Education</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">8 June 2023</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex justify-center items-center w-full">
                            <div class="bg-white rounded-lg shadow-sm p-6 w-full max-w-2xl">
                                <h3 class="text-lg font-medium text-gray-800 mb-4 text-center">Employment Created</h3>
                                <div class="chart-container w-full flex justify-center items-center mx-auto">
                                    <canvas id="employmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <script>
                            const sectorLabels = <?= json_encode(array_keys($sectorJobs)) ?>;
                            const jobData = <?= json_encode(array_values($sectorJobs)) ?>;

                            const ctx = document.getElementById('employmentChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: sectorLabels,
                                    datasets: [{
                                        label: 'Jobs Created',
                                        data: jobData,
                                        backgroundColor: [
                                            '#60A5FA', '#A78BFA', '#F472B6', '#34D399', '#FBBF24', '#F87171', '#818CF8', '#10B981', '#F59E0B'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return `${context.label}: ${context.raw} jobs`;
                                                }
                                            }
                                        },
                                        legend: {
                                            position: 'bottom'
                                        },
                                        title: {
                                            display: false
                                        }
                                    }
                                }
                            });
                        </script>                        
                    </div>
                </div>

                <!-- Admin User Management Section -->
                <div id="admin-user-management" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">User Management</h2>
                        <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add New User
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div class="relative">
                                <input type="text" placeholder="Search users..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <div class="flex space-x-2">
                                <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                    <i class="fas fa-filter mr-2"></i> Filter
                                </button>
                                <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                    <i class="fas fa-download mr-2"></i> Export
                                </button>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/1.jpg" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Rajesh Kumar</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">rajesh@techstartup.com</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Startup Owner</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            15 Jan 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/2.jpg" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Priya Sharma</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">priya@edutech.io</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Entrepreneur</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            22 Feb 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/3.jpg" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Amit Patel</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">amit@medihealthtech.com</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Investor</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            05 Mar 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/4.jpg" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Neha Gupta</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">neha@fintech.in</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Startup Owner</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            18 Apr 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/5.jpg" alt="">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Vijay Singh</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">vijay@agritech.org</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Entrepreneur</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            30 Apr 2023
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">120</span> results
                            </div>
                            <div class="flex space-x-2">
                                <button class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg disabled:opacity-50">
                                    Previous
                                </button>
                                <button class="bg-orange-500 text-white py-2 px-4 rounded-lg">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Startup Monitoring Section -->
                <div id="admin-startup-monitoring" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Startup Monitoring</h2>
                        <div class="flex space-x-3">
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                <i class="fas fa-download mr-2"></i> Export
                            </button>
                            <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">
                                <i class="fas fa-chart-line mr-2"></i> Generate Report
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                                    <i class="fas fa-rocket"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Active Startups</p>
                                    <h3 class="text-2xl font-bold text-gray-800">8,765</h3>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Growth Rate</p>
                                    <h3 class="text-2xl font-bold text-gray-800">+18.3%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Job Creation</p>
                                    <h3 class="text-2xl font-bold text-gray-800">45,621</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Startup Performance Metrics</h3>
                        <div class="chart-container">
                            <canvas id="startupPerformanceChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-800">Top Performing Startups</h3>
                            <div class="relative">
                                <input type="text" placeholder="Search startups..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Startup</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funding</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jobs Created</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                    <span class="text-orange-500 font-bold">TP</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">TechPrime Solutions</div>
                                                    <div class="text-sm text-gray-500">Bangalore</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">AI & Machine Learning</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">₹45M</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-green-600">+32.7%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            187
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-blue-500 font-bold">GH</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">GreenHarvest</div>
                                                    <div class="text-sm text-gray-500">Pune</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">AgriTech</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">₹28M</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-green-600">+27.5%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            142
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <span class="text-purple-500 font-bold">FC</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">FastCash</div>
                                                    <div class="text-sm text-gray-500">Mumbai</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">FinTech</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">₹60M</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-green-600">+25.2%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            215
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <span class="text-green-500 font-bold">MD</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">MediDirect</div>
                                                    <div class="text-sm text-gray-500">Hyderabad</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">HealthTech</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">₹35M</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-green-600">+22.8%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            156
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">250</span> startups
                            </div>
                            <div class="flex space-x-2">
                                <button class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg disabled:opacity-50">
                                    Previous
                                </button>
                                <button class="bg-orange-500 text-white py-2 px-4 rounded-lg">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Applications Section -->
                <div id="admin-applications" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Applications</h2>
                        <div class="flex space-x-3">
                            <div class="relative">
                                <input type="text" placeholder="Search applications..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Pending</h3>
                            <div class="flex items-center">
                                <div class="text-3xl font-bold text-gray-800">45</div>
                                <div class="ml-4 text-xs text-gray-500 bg-gray-100 rounded px-2 py-1">+12% from last week</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Approved</h3>
                            <div class="flex items-center">
                                <div class="text-3xl font-bold text-gray-800">128</div>
                                <div class="ml-4 text-xs text-gray-500 bg-gray-100 rounded px-2 py-1">+8% from last week</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Rejected</h3>
                            <div class="flex items-center">
                                <div class="text-3xl font-bold text-gray-800">18</div>
                                <div class="ml-4 text-xs text-gray-500 bg-gray-100 rounded px-2 py-1">-5% from last week</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                            <h3 class="text-lg font-medium text-gray-800 mb-2">In Review</h3>
                            <div class="flex items-center">
                                <div class="text-3xl font-bold text-gray-800">32</div>
                                <div class="ml-4 text-xs text-gray-500 bg-gray-100 rounded px-2 py-1">+15% from last week</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="mb-4">
                            <ul class="flex border-b">
                                <li class="-mb-px mr-1">
                                    <a class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-orange-500 font-semibold" href="#">All Applications</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">Pending</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">Approved</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">Rejected</a>
                                </li>
                            </ul>
                        </div>

                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application ID</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Startup Name</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">#APP-7845</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">LearnTech Solutions</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">EdTech</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">25 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 mr-3">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">#APP-7844</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">GroceryExpress</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">E-commerce</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">24 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">In Review</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 mr-3">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">#APP-7843</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">SwiftRide</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Transportation</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">23 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">#APP-7842</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">CryptoSafe</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">FinTech</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">22 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-orange-500 hover:text-orange-700">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">223</span> applications
                            </div>
                            <div class="flex space-x-2">
                                <button class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg disabled:opacity-50">
                                    Previous
                                </button>
                                <button class="bg-orange-500 text-white py-2 px-4 rounded-lg">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Resources Section -->
                <div id="admin-resources" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Resources</h2>
                        <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add New Resource
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="bg-blue-500 h-3"></div>
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-800 mb-2">Funding Opportunities</h3>
                                        <p class="text-sm text-gray-500 mb-4">Guide to various funding options available for startups</p>
                                    </div>
                                    <div class="bg-blue-100 p-2 rounded-full">
                                        <i class="fas fa-rupee-sign text-blue-500"></i>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Last updated: 3 days ago</span>
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="bg-green-500 h-3"></div>
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-800 mb-2">Legal Compliance</h3>
                                        <p class="text-sm text-gray-500 mb-4">Essential legal and regulatory information for startups</p>
                                    </div>
                                    <div class="bg-green-100 p-2 rounded-full">
                                        <i class="fas fa-gavel text-green-500"></i>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Last updated: 1 week ago</span>
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="bg-purple-500 h-3"></div>
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-800 mb-2">Mentorship Programs</h3>
                                        <p class="text-sm text-gray-500 mb-4">Connect with industry experts and mentors</p>
                                    </div>
                                    <div class="bg-purple-100 p-2 rounded-full">
                                        <i class="fas fa-users text-purple-500"></i>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Last updated: 2 days ago</span>
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-800">Resource Library</h3>
                            <div class="relative">
                                <input type="text" placeholder="Search resources..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Startup India Registration Guide</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Documentation</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">PDF</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">15 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">2,456</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Business Model Canvas Template</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Templates</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">XLSX</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">12 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">1,879</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Pitch Deck Best Practices</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Guides</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Video</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">8 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">3,542</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Tax Benefits for Startups</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">Finance</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">PDF</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">5 May 2023</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">2,187</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Admin Notifications Section -->
                <div id="admin-notifications" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Notifications</h2>
                        <div class="flex space-x-3">
                            <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-bell mr-2"></i> Send Notification
                            </button>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                                <i class="fas fa-check-double mr-2"></i> Mark All as Read
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <div class="mb-4">
                            <ul class="flex border-b">
                                <li class="-mb-px mr-1">
                                    <a class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-orange-500 font-semibold" href="#">All</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">Unread</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">System</a>
                                </li>
                                <li class="mr-1">
                                    <a class="bg-white inline-block py-2 px-4 text-gray-600 hover:text-orange-500 font-semibold" href="#">Applications</a>
                                </li>
                            </ul>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 border-l-4 border-orange-500 bg-orange-50 rounded-r-lg flex items-start">
                                <div class="flex-shrink-0 bg-orange-100 rounded-full p-2 mr-4">
                                    <i class="fas fa-bell text-orange-500"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="text-sm font-medium text-gray-900">New Application Submitted</h4>
                                        <span class="text-xs text-gray-500">30 minutes ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600">TechSprint Solutions has submitted a new application for startup registration.</p>
                                    <div class="mt-2">
                                        <button class="text-sm text-orange-500 hover:text-orange-700">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-lg flex items-start">
                                <div class="flex-shrink-0 bg-blue-100 rounded-full p-2 mr-4">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="text-sm font-medium text-gray-900">System Update Completed</h4>
                                        <span class="text-xs text-gray-500">2 hours ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600">The system has been updated to version 2.4.5. New features include enhanced reporting and analytics.</p>
                                    <div class="mt-2">
                                        <button class="text-sm text-blue-500 hover:text-blue-700">
                                            Read More
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 border-l-4 border-green-500 bg-green-50 rounded-r-lg flex items-start">
                                <div class="flex-shrink-0 bg-green-100 rounded-full p-2 mr-4">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="text-sm font-medium text-gray-900">Application Approved</h4>
                                        <span class="text-xs text-gray-500">5 hours ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600">GreenEarth's application has been approved. An email notification has been sent to the applicant.</p>
                                    <div class="mt-2">
                                        <button class="text-sm text-green-500 hover:text-green-700">
                                            See Details
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 border-l-4 border-purple-500 bg-purple-50 rounded-r-lg flex items-start">
                                <div class="flex-shrink-0 bg-purple-100 rounded-full p-2 mr-4">
                                    <i class="fas fa-calendar-alt text-purple-500"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="text-sm font-medium text-gray-900">Upcoming Webinar</h4>
                                        <span class="text-xs text-gray-500">1 day ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Reminder: Startup Funding Webinar scheduled for tomorrow at 2:00 PM. 45 participants have registered so far.</p>
                                    <div class="mt-2">
                                        <button class="text-sm text-purple-500 hover:text-purple-700">
                                            View Event
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-r-lg flex items-start">
                                <div class="flex-shrink-0 bg-red-100 rounded-full p-2 mr-4">
                                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <h4 class="text-sm font-medium text-gray-900">Application Rejected</h4>
                                        <span class="text-xs text-gray-500">2 days ago</span>
                                    </div>
                                    <p class="text-sm text-gray-600">CryptoSafe's application has been rejected due to incomplete documentation. An email notification has been sent.</p>
                                    <div class="mt-2">
                                        <button class="text-sm text-red-500 hover:text-red-700">
                                            Review Decision
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded-lg">
                                Load More
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Send Notification</h3>
                        <form>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                                <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="all">All Users</option>
                                    <option value="startups">Startup Owners</option>
                                    <option value="mentors">Mentors</option>
                                    <option value="investors">Investors</option>
                                    <option value="custom">Custom Selection</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Enter notification subject">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                <textarea rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Type your message here..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-orange-500" name="priority" value="low">
                                        <span class="ml-2">Low</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-orange-500" name="priority" value="medium" checked>
                                        <span class="ml-2">Medium</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-orange-500" name="priority" value="high">
                                        <span class="ml-2">High</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox text-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">Send email notification as well</span>
                                </label>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg mr-2">
                                    Cancel
                                </button>
                                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">
                                    Send Notification
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin Profile Section -->
                <div id="admin-profile" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">Admin Profile</h2>
                        <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <div class="flex items-center">
                            <div class="relative">
                                <img class="h-24 w-24 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/1.jpg" alt="Admin Profile">
                                <button class="absolute bottom-0 right-0 bg-gray-800 text-white p-1 rounded-full">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <div class="ml-6">
                                <h3 class="text-xl font-semibold text-gray-800">Admin User</h3>
                                <p class="text-sm text-gray-500">System Administrator</p>
                                <div class="mt-2 flex space-x-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Super Admin</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Personal Information</h3>
                            <form>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <input type="text" value="Admin" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <input type="text" value="User" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                    <input type="email" value="admin@startupindia.gov.in" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" value="+91 9876543210" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Designation</label>
                                    <input type="text" value="System Administrator" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                    <input type="text" value="IT Administration" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                            </form>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Account Settings</h3>
                            <form>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                    <input type="text" value="admin_user" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Change Password</label>
                                    <input type="password" placeholder="••••••••" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                                    <input type="password" placeholder="••••••••" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="super_admin">Super Admin</option>
                                        <option value="admin">Admin</option>
                                        <option value="moderator">Moderator</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Two-Factor Authentication</label>
                                    <div class="flex items-center">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                            <span class="ml-2">Enable two-factor authentication</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                            <span class="ml-2">New application submissions</span>
                                        </label>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">System updates</span>
                                            </label>
                                        </div>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Security alerts</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Admin Settings Section -->
                <div id="admin-settings" class="section p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800">System Settings</h2>
                        <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-orange-100 text-orange-500 mr-3">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">Appearance</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="light">Light</option>
                                        <option value="dark">Dark</option>
                                        <option value="system">System Default</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                                    <div class="flex space-x-2">
                                        <button class="w-8 h-8 rounded-full bg-orange-500 ring-2 ring-orange-300"></button>
                                        <button class="w-8 h-8 rounded-full bg-blue-500"></button>
                                        <button class="w-8 h-8 rounded-full bg-green-500"></button>
                                        <button class="w-8 h-8 rounded-full bg-purple-500"></button>
                                        <button class="w-8 h-8 rounded-full bg-red-500"></button>
                                        <button class="w-8 h-8 rounded-full bg-gray-500"></button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Font Size</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="small">Small</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="large">Large</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sidebar Position</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-orange-500" name="sidebar_position" value="left" checked>
                                            <span class="ml-2">Left</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio text-orange-500" name="sidebar_position" value="right">
                                            <span class="ml-2">Right</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">Notifications</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                            <span class="ml-2">New application submissions</span>
                                        </label>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Application status changes</span>
                                            </label>
                                        </div>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">System updates</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Push Notifications</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                        <span class="ml-2">Enable push notifications</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notification Sound</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                        <span class="ml-2">Enable notification sound</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">Security</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Two-Factor Authentication</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                        <span class="ml-2">Require for all admin users</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="15">15 minutes</option>
                                        <option value="30" selected>30 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="120">2 hours</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Policy</label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                            <span class="ml-2">Require uppercase letters</span>
                                        </label>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Require numbers</span>
                                            </label>
                                        </div>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Require special characters</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                                    <i class="fas fa-database"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">Data Management</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Backup</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Retention</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="6">6 months</option>
                                        <option value="12" selected>1 year</option>
                                        <option value="24">2 years</option>
                                        <option value="36">3 years</option>
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg w-full">
                                        <i class="fas fa-download mr-2"></i> Export All Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-red-100 text-red-500 mr-3">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">System</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Maintenance</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="form-checkbox text-orange-500">
                                        <span class="ml-2">Enable maintenance mode</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Analytics</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                        <span class="ml-2">Enable usage analytics</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Logs</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="errors">Errors only</option>
                                        <option value="warnings">Warnings and errors</option>
                                        <option value="all" selected>All activities</option>
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <button class="bg-orange-100 hover:bg-orange-200 text-orange-700 py-2 px-4 rounded-lg w-full">
                                        <i class="fas fa-sync-alt mr-2"></i> Check for Updates
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center mb-4">
                                <div class="p-2 rounded-full bg-yellow-100 text-yellow-500 mr-3">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-800">User Permissions</h3>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default User Role</label>
                                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                        <option value="member" selected>Member</option>
                                        <option value="editor">Editor</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">User Registration</label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                            <span class="ml-2">Allow new registrations</span>
                                        </label>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Require email verification</span>
                                            </label>
                                        </div>
                                        <div class="block">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" class="form-checkbox text-orange-500" checked>
                                                <span class="ml-2">Admin approval required</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Interface -->
        <div id="user-interface" class="user-interface flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div class="user-sidebar bg-white shadow-md w-64 h-screen overflow-y-auto fixed left-0 top-0 no-print">
                <div class="p-4">
                    <div class="flex items-center justify-center">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" class="h-8 w-8 mr-2" alt="Startup India Logo">
                        <h1 class="text-xl font-bold text-gray-800">Startup <span class="text-blue-500">India</span></h1>
                    </div>
                </div>
                <div class="mt-4">
                    <div id="user-dashboard-link" class="sidebar-item sidebar-active flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-2">Dashboard</span>
                    </div>
                    <div id="user-profile-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-user w-6"></i>
                        <span class="ml-2">Profile</span>
                    </div>
                    <div id="user-applications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-file-alt w-6"></i>
                        <span class="ml-2">Applications</span>
                    </div>
                    <div id="user-resources-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-book w-6"></i>
                        <span class="ml-2">Resources</span>
                    </div>
                    <div id="user-notifications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-bell w-6"></i>
                        <span class="ml-2">Notifications</span>
                        <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">3</span>
                    </div>
                    <div id="user-settings-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-cog w-6"></i>
                        <span class="ml-2">Settings</span>
                    </div>
                    <div class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer mt-8">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="ml-2">Logout</span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-x-hidden overflow-y-auto ml-64 bg-gray-100">
                <!-- User Header -->
                <header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between no-print">
                    <div class="flex items-center">
                        <i class="fas fa-bars text-gray-500 text-xl cursor-pointer mr-6"></i>
                        <h2 class="text-lg font-medium text-gray-800">Dashboard</h2>
                    </div>
                    <div class="flex items-center">
                        <div class="relative mr-4">
                            <i class="fas fa-search text-gray-500"></i>
                        </div>
                        <div class="relative mr-4">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center notification-badge">3</span>
                        </div>
                        <div class="flex items-center">
                            <img class="h-8 w-8 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
                            <span class="ml-2 text-sm font-medium text-gray-800">Rajesh Kumar</span>
                        </div>
                    </div>
                </header>

                <!-- User Dashboard Section -->
                <div id="user-dashboard" class="section active-section p-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                            <div class="flex items-center mb-4 md:mb-0">
                                <div class="mr-4">
                                    <img class="h-16 w-16 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Welcome back, Rajesh!</h3>
                                    <p class="text-sm text-gray-500">TechSprint Solutions | CEO & Founder</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i> New Application
                                </button>
                                <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-download mr-2"></i> Download Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Application Status</p>
                                    <h3 class="text-xl font-bold text-gray-800">Approved</h3>
                                </div>
                                <div class="p-3 rounded-full bg-green-100 text-green-500">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t">
                                <p class="text-sm text-gray-500">Registration ID: <span class="font-medium">DIPP-12345</span></p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Benefits Availed</p>
                                    <h3 class="text-xl font-bold text-gray-800">3 of 5</h3>
                                </div>
                                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                    <i class="fas fa-gift"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Next Steps</p>
                                    <h3 class="text-xl font-bold text-gray-800">Tax Exemption</h3>
                                </div>
                                <div class="p-3 rounded-full bg-orange-100 text-orange-500">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t">
                                <button class="text-sm text-blue-500 hover:text-blue-700 font-medium">
                                    View Requirements <i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Your Startup Performance</h3>
                            <div class="chart-container">
                                <canvas id="userPerformanceChart"></canvas>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Upcoming Events</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-blue-100 text-blue-500 rounded-lg p-3 mr-4">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-base font-medium text-gray-900">Virtual Mentorship Session</h4>

</body>
</html>