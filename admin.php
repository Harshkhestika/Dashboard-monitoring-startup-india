<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StartUp Hub - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        .sidebar-active {
            background-color: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="bg-white shadow-lg w-64 h-screen fixed flex flex-col">
            <div class="p-6 flex items-center space-x-3">
                <div class="bg-blue-500 text-white p-2 rounded-lg">
                    <i class="fas fa-rocket text-xl"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-800">StartUp Hub</h1>
            </div>
            <div class="flex-1 overflow-y-auto mt-6">
                <nav>
                    <div class="sidebar-item sidebar-active px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-tachometer-alt text-blue-500 w-5"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-lightbulb text-blue-400 w-5"></i>
                        <span>My Startups</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-chart-line text-green-500 w-5"></i>
                        <span>Analytics</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-handshake text-purple-500 w-5"></i>
                        <span>Investors</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-users text-yellow-500 w-5"></i>
                        <span>Network</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-book text-red-500 w-5"></i>
                        <span>Resources</span>
                    </div>
                    <div class="sidebar-item px-6 py-3 flex items-center space-x-3 cursor-pointer">
                        <i class="fas fa-cog text-gray-500 w-5"></i>
                        <span>Settings</span>
                    </div>
                </nav>
            </div>
            <div class="p-6 border-t">
                <div class="flex items-center space-x-3">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile" class="w-10 h-10 rounded-full">
                    <div>
                        <p class="font-medium">Rajesh Kumar</p>
                        <p class="text-sm text-gray-500">Founder & CEO</p>
                    </div>
                </div>
                <button class="mt-4 w-full py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-all">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative cursor-pointer">
                        <i class="fas fa-bell text-gray-500 text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                    </div>
                    <button class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center space-x-2 transition-all">
                        <i class="fas fa-plus"></i>
                        <span>New Startup</span>
                    </button>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-500">Active Startups</p>
                                <h3 class="text-2xl font-bold mt-2">3</h3>
                            </div>
                            <div class="bg-blue-100 text-blue-500 p-3 rounded-lg">
                                <i class="fas fa-rocket"></i>
                            </div>
                        </div>
                        <p class="text-sm text-green-500 mt-4"><i class="fas fa-arrow-up mr-1"></i> 1 new this month</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-500">Investor Meetings</p>
                                <h3 class="text-2xl font-bold mt-2">12</h3>
                            </div>
                            <div class="bg-green-100 text-green-500 p-3 rounded-lg">
                                <i class="fas fa-handshake"></i>
                            </div>
                        </div>
                        <p class="text-sm text-green-500 mt-4"><i class="fas fa-arrow-up mr-1"></i> 3 scheduled</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-500">Total Funding</p>
                                <h3 class="text-2xl font-bold mt-2">₹2.5M</h3>
                            </div>
                            <div class="bg-purple-100 text-purple-500 p-3 rounded-lg">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                        <p class="text-sm text-green-500 mt-4"><i class="fas fa-arrow-up mr-1"></i> 15% from last quarter</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-500">Network Growth</p>
                                <h3 class="text-2xl font-bold mt-2">87</h3>
                            </div>
                            <div class="bg-yellow-100 text-yellow-500 p-3 rounded-lg">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <p class="text-sm text-green-500 mt-4"><i class="fas fa-arrow-up mr-1"></i> 8 new connections</p>
                    </div>
                </div>

                <!-- Charts and Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Revenue Chart -->
                    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2 card-hover transition-all">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Revenue Growth</h3>
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                                <option>All Time</option>
                            </select>
                        </div>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <h3 class="text-lg font-semibold mb-6">Quick Actions</h3>
                        <div class="space-y-4">
                            <button class="w-full flex items-center space-x-3 bg-blue-50 hover:bg-blue-100 text-blue-600 p-3 rounded-lg transition-all">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <span>Create Pitch Deck</span>
                            </button>
                            <button class="w-full flex items-center space-x-3 bg-green-50 hover:bg-green-100 text-green-600 p-3 rounded-lg transition-all">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <i class="fas fa-search-dollar"></i>
                                </div>
                                <span>Find Investors</span>
                            </button>
                            <button class="w-full flex items-center space-x-3 bg-purple-50 hover:bg-purple-100 text-purple-600 p-3 rounded-lg transition-all">
                                <div class="bg-purple-100 p-2 rounded-lg">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Generate Report</span>
                            </button>
                            <button class="w-full flex items-center space-x-3 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-3 rounded-lg transition-all">
                                <div class="bg-yellow-100 p-2 rounded-lg">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span>Invite Team Members</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Startups -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2 card-hover transition-all">
                        <h3 class="text-lg font-semibold mb-6">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="bg-blue-100 text-blue-500 p-2 rounded-full">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Meeting with ABC Ventures</p>
                                    <p class="text-sm text-gray-500">Scheduled for tomorrow at 2:00 PM</p>
                                    <p class="text-xs text-gray-400 mt-1">Today, 10:30 AM</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="bg-green-100 text-green-500 p-2 rounded-full">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Application Approved</p>
                                    <p class="text-sm text-gray-500">Your startup "TechNova" has been approved</p>
                                    <p class="text-xs text-gray-400 mt-1">Yesterday, 4:15 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="bg-purple-100 text-purple-500 p-2 rounded-full">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div>
                                    <p class="font-medium">New Connection</p>
                                    <p class="text-sm text-gray-500">Priya Sharma connected with you</p>
                                    <p class="text-xs text-gray-400 mt-1">2 days ago</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="bg-yellow-100 text-yellow-500 p-2 rounded-full">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Upcoming Event</p>
                                    <p class="text-sm text-gray-500">Startup Funding Webinar in 3 days</p>
                                    <p class="text-xs text-gray-400 mt-1">3 days ago</p>
                                </div>
                            </div>
                        </div>
                        <button class="mt-6 text-blue-500 hover:text-blue-700 font-medium text-sm">
                            View All Activity <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>

                    <!-- My Startups -->
                    <div class="bg-white rounded-xl shadow-sm p-6 card-hover transition-all">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">My Startups</h3>
                            <button class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i> Add New
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="border rounded-lg p-4 hover:border-blue-300 transition-all">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-blue-100 text-blue-500 p-3 rounded-lg">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">TechNova</h4>
                                        <p class="text-sm text-gray-500">Mobile Technology</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                                            <span class="text-xs text-gray-500">₹1.2M funding</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border rounded-lg p-4 hover:border-green-300 transition-all">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-green-100 text-green-500 p-3 rounded-lg">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">GreenHarvest</h4>
                                        <p class="text-sm text-gray-500">AgriTech</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Pending</span>
                                            <span class="text-xs text-gray-500">₹0.8M funding</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border rounded-lg p-4 hover:border-purple-300 transition-all">
                                <div class="flex items-start space-x-3">
                                    <div class="bg-purple-100 text-purple-500 p-3 rounded-lg">
                                        <i class="fas fa-heartbeat"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">MediCare AI</h4>
                                        <p class="text-sm text-gray-500">HealthTech</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">In Review</span>
                                            <span class="text-xs text-gray-500">₹0.5M funding</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: [25000, 42000, 35000, 58000, 45000, 75000],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Sidebar navigation
            const sidebarItems = document.querySelectorAll('.sidebar-item');
            sidebarItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebarItems.forEach(i => i.classList.remove('sidebar-active'));
                    this.classList.add('sidebar-active');
                });
            });

            // Card hover effects
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('shadow-lg');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('shadow-lg');
                });
            });
        });
    </script>
</body>
</html>