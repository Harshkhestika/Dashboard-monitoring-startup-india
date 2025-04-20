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

// Get notifications
// In a real implementation, you would fetch notifications from the database
// with pagination, filtering, etc.
/*
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$where_clause = "";
if ($filter === 'unread') {
    $where_clause = " AND is_read = 0";
} else if ($filter === 'system') {
    $where_clause = " AND type = 'system'";
} else if ($filter === 'applications') {
    $where_clause = " AND type = 'application'";
} else if ($filter === 'important') {
    $where_clause = " AND priority = 'high'";
}

$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? OR user_id IS NULL $where_clause
    ORDER BY created_at DESC LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count total notifications for pagination
$stmt = $conn->prepare("
    SELECT COUNT(*) as total FROM notifications 
    WHERE user_id = ? OR user_id IS NULL $where_clause
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Count unread notifications
$stmt = $conn->prepare("
    SELECT COUNT(*) as unread FROM notifications 
    WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread'];
$stmt->close();
*/

// Since we don't have actual database, we'll simulate notifications
$unread_count = 8;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Startup India Monitoring Dashboard</title>
    
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    
    <!-- Alpine.js for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    
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
        .table-container {
            overflow-x: auto;
        }
        .chart-container {
            height: 300px;
            width: 100%;
        }
        
        /* Notification specific styles */
        .notification-card {
            transition: all 0.3s ease;
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .notification-unread {
            border-left-width: 4px;
        }
        .notification-read {
            opacity: 0.8;
        }
        .tab-active {
            color: #ed8936;
            border-bottom: 2px solid #ed8936;
        }
        
        /* Animation for new notifications */
        @keyframes newNotificationPulse {
            0% {
                background-color: rgba(237, 137, 54, 0.1);
            }
            50% {
                background-color: rgba(237, 137, 54, 0.2);
            }
            100% {
                background-color: rgba(237, 137, 54, 0.1);
            }
        }
        .new-notification {
            animation: newNotificationPulse 2s ease-in-out;
        }
        
        /* For PDF optimization */
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            .sidebar, header {
                display: none;
            }
            .ml-64 {
                margin-left: 0 !important;
            }
            body {
                width: 100%;
                height: auto;
                overflow: visible;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans" x-data="notificationSystem()">
    <div class="flex h-screen overflow-hidden">
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

                <div id="admin-user-management-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-2"><a href="user-management.php">User Management</a></span>
                </div>

                <a href="startup-monitoring.php">
                <div id="admin-startup-monitoring-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-chart-line w-6"></i>
                    <span class="ml-2">Startup Monitoring</span>
                </div>
                </a>
                <a href="application.php">
                <div id="admin-applications-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-file-alt w-6"></i>
                    <span class="ml-2"><a href="applications.php">Applications</span>
                </div>
                </a>
                <a href="resources.php">
                <div id="admin-resources-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-book w-6"></i>
                    <span class="ml-2">Resources</span>
                </div>
                
        </a>

        <a href="notification.php">
                <div id="admin-notifications-link" class="sidebar-item sidebar-active flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-bell w-6"></i>
                    <span class="ml-2">Notifications</span>
                    <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto" x-text="unreadCount"></span>
                </div>
                </a>
                <a href="profile.php"> 
                    <div id="admin-profile-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                        <i class="fas fa-user w-6"></i>
                        <span class="ml-2">Profile</span>
                    </div>
                </a>
                <!-- <div id="admin-settings-link" class="sidebar-item flex items-center py-3 px-4 text-gray-700 cursor-pointer">
                    <i class="fas fa-cog w-6"></i>
                    <span class="ml-2">Settings</span>
                </div> -->

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
                    <span class="ml-2">Logout</span>
                </div>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto ml-64 bg-gray-100">
            <!-- Header -->
            <header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between no-print">
                <div class="flex items-center">
                    <i class="fas fa-bars text-gray-500 text-xl cursor-pointer mr-6"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h3>
                </div>
                <div class="flex items-center">
                    <div class="relative mr-4">
                        <i class="fas fa-search text-gray-500"></i>
                    </div>
                    <div class="relative mr-4">
                        <i class="fas fa-bell text-gray-500"></i>
                        <span class="absolute bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center notification-badge" x-text="unreadCount"></span>
                    </div>
                    <div class="flex items-center cursor-pointer hover:text-orange-500 transition-colors" onclick="window.location.href='profile.php'">
                        <img class="h-8 w-8 rounded-full object-cover" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
                        <span class="ml-2 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin User'); ?></span>
                    </div>
                </div>
            </header>
            
            <!-- Notification Center Content -->
            <div class="p-6">
                <!-- Header with Stats -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Notification Center</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage your notifications and preferences</p>
                    </div>
                    <div class="flex space-x-3">


                        <!-- <button @click="markAllAsRead" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg transition-all flex items-center">
                            <i class="fas fa-check-double mr-2"></i> Mark All as Read
                        </button> -->


                        <button @click="showNotificationPreferences = !showNotificationPreferences" 
                                class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg transition-all flex items-center">
                            <i class="fas fa-cog mr-2"></i> Preferences
                        </button>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">All Notifications</p>
                                <h3 class="text-2xl font-bold text-gray-800" x-text="totalNotifications"></h3>
                            </div>
                            <div class="p-3 rounded-full bg-gray-100 text-gray-500">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Main Notification Panel -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Filter Tabs -->
                    <div class="flex border-b p-4">
                        <button @click="currentFilter = 'all'" 
                                :class="{'tab-active': currentFilter === 'all'}"
                                class="px-4 py-2 font-medium text-gray-600 hover:text-orange-500 focus:outline-none transition-colors">
                            All
                        </button>
                        
                    </div>
                    
                    
     
                    </div>
                    
                    <!-- Notifications List -->
                    <div class="p-6 space-y-4">
                        <!-- Empty State -->
                        <div x-show="filteredNotifications.length === 0" class="text-center py-12">
                            <div class="mx-auto w-24 h-24 flex items-center justify-center rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-bell-slash text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-800 mb-2">No notifications found</h3>
                            <p class="text-gray-500">There are no notifications matching your current filter.<?php echo ($user->$name); ?></p>
                        </div>
                        
                        <!-- Notifications -->
                        <template x-for="notification in filteredNotifications" :key="notification.id">
                            <div :class="{
                                    'notification-card rounded-lg shadow-sm mb-4 bg-white': true,
                                    'notification-unread border-l-4': !notification.is_read,
                                    'notification-read': notification.is_read,
                                    'border-orange-500': notification.type === 'system' && !notification.is_read,
                                    'border-blue-500': notification.type === 'application' && !notification.is_read,
                                    'border-green-500': notification.type === 'success' && !notification.is_read,
                                    'border-red-500': notification.type === 'alert' && !notification.is_read,
                                    'border-yellow-500': notification.type === 'warning' && !notification.is_read,
                                    'border-purple-500': notification.type === 'event' && !notification.is_read,
                                    'new-notification': notification.isNew
                                }">
                                <div class="p-4 flex items-start">
                                    <!-- Checkbox -->
                                    <div class="flex-shrink-0 mr-3">
                                        <input type="checkbox" :value="notification.id" x-model="selectedNotifications" 
                                               class="form-checkbox text-orange-500 h-5 w-5">
                                    </div>
                                    
                                    <!-- Icon -->
                                    <div class="flex-shrink-0 mr-4">
                                        <div :class="{
                                                'rounded-full p-2 flex items-center justify-center': true,
                                                'bg-orange-100 text-orange-500': notification.type === 'system',
                                                'bg-blue-100 text-blue-500': notification.type === 'application',
                                                'bg-green-100 text-green-500': notification.type === 'success',
                                                'bg-red-100 text-red-500': notification.type === 'alert',
                                                'bg-yellow-100 text-yellow-500': notification.type === 'warning',
                                                'bg-purple-100 text-purple-500': notification.type === 'event'
                                            }">
                                            <i :class="{
                                                    'fas': true,
                                                    'fa-bell': notification.type === 'system',
                                                    'fa-file-alt': notification.type === 'application',
                                                    'fa-check-circle': notification.type === 'success',
                                                    'fa-exclamation-triangle': notification.type === 'alert',
                                                    'fa-exclamation-circle': notification.type === 'warning',
                                                    'fa-calendar-alt': notification.type === 'event'
                                                }">
                                            </i>
                                        </div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="text-sm font-medium text-gray-900" x-text="notification.title"></h4>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2" x-text="formatTimeAgo(notification.created_at)"></span>
                                                <template x-if="notification.priority === 'high'">
                                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded">High</span>
                                                </template>
                                                <template x-if="notification.priority === 'medium'">
                                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded">Medium</span>
                                                </template>
                                                <button @click="toggleNotificationOptions(notification.id)" class="ml-2 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600" x-text="notification.message"></p>
                                        
                                        <!-- Action Buttons -->
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <template x-if="notification.type === 'application'">
                                                <button class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-2 py-1 rounded transition-colors">
                                                    View Application
                                                </button>
                                            </template>
                                            <template x-if="notification.type === 'alert' || notification.type === 'warning'">
                                                <button class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-2 py-1 rounded transition-colors">
                                                    Review Issue
                                                </button>
                                            </template>
                                            <template x-if="notification.type === 'event'">
                                                <button class="text-xs bg-purple-50 text-purple-600 hover:bg-purple-100 px-2 py-1 rounded transition-colors">
                                                    View Event
                                                </button>
                                            </template>
                                            <template x-if="notification.type === 'success'">
                                                <button class="text-xs bg-green-50 text-green-600 hover:bg-green-100 px-2 py-1 rounded transition-colors">
                                                    See Details
                                                </button>
                                            </template>
                                            <button @click="markAsRead(notification.id)" x-show="!notification.is_read" 
                                                    class="text-xs bg-gray-50 text-gray-600 hover:bg-gray-100 px-2 py-1 rounded transition-colors">
                                                Mark as Read
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Options Dropdown -->
                                    <div x-show="activeOptions === notification.id" 
                                         @click.away="activeOptions = null"
                                         class="absolute right-8 mt-8 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                        <button @click="markAsRead(notification.id)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Mark as Read
                                        </button>
                                        <button @click="snoozeNotification(notification.id)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Snooze for 24 hours
                                        </button>
                                        <button @click="deleteNotification(notification.id)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-white px-6 py-4 border-t flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing <span class="font-medium" x-text="paginationStart"></span> to 
                            <span class="font-medium" x-text="paginationEnd"></span> of 
                            <span class="font-medium" x-text="totalNotifications"></span> notifications
                        </div>
                        <div class="flex space-x-2">
                            <button @click="previousPage" :disabled="currentPage === 1" 
                                    :class="{'opacity-50 cursor-not-allowed': currentPage === 1}"
                                    class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg">
                                Previous
                            </button>
                            <button @click="nextPage" :disabled="currentPage >= totalPages" 
                                    :class="{'opacity-50 cursor-not-allowed': currentPage >= totalPages}"
                                    class="bg-orange-500 text-white py-2 px-4 rounded-lg">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Preferences Panel -->
                <div x-show="showNotificationPreferences" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="mt-6 bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-800">Notification Preferences</h3>
                        <button @click="showNotificationPreferences = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Notification Types -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Notification Types</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.system" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">System Updates & Announcements</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.applications" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Application Status Changes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.users" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">User Activity</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.events" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Events & Webinars</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.security" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Security Alerts</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Delivery Methods -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Delivery Methods</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.in_app" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">In-App Notifications</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.email" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Email</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.sms" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">SMS</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.push" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Push Notifications</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Frequency Settings -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Frequency</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Email Digest</label>
                                    <select x-model="notificationPreferences.email_frequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                        <option value="realtime">Real-time</option>
                                        <option value="daily">Daily Digest</option>
                                        <option value="weekly">Weekly Digest</option>
                                        <option value="disabled">Disabled</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Quiet Hours</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">From</label>
                                            <select x-model="notificationPreferences.quiet_hours_start" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                                <option value="18">6:00 PM</option>
                                                <option value="19">7:00 PM</option>
                                                <option value="20">8:00 PM</option>
                                                <option value="21">9:00 PM</option>
                                                <option value="22">10:00 PM</option>
                                                <option value="23">11:00 PM</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">To</label>
                                            <select x-model="notificationPreferences.quiet_hours_end" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                                <option value="6">6:00 AM</option>
                                                <option value="7">7:00 AM</option>
                                                <option value="8">8:00 AM</option>
                                                <option value="9">9:00 AM</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Priority Settings -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Priority Settings</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="notificationPreferences.bypass_quiet_hours" class="form-checkbox text-orange-500 h-5 w-5">
                                    <span class="ml-2 text-sm text-gray-700">Allow high priority notifications during quiet hours</span>
                                </label>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Default Priority for System Notifications</label>
                                    <select x-model="notificationPreferences.system_priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1">Default Priority for Application Notifications</label>
                                    <select x-model="notificationPreferences.application_priority" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button @click="resetPreferences" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg mr-3">
                            Reset to Defaults
                        </button>
                        <button @click="savePreferences" class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">
                            Save Preferences
                        </button>
                    </div>
                </div>
                
                <!-- Real-time Notification Preview -->
                <div x-show="showPreview" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform translate-y-4"
                     class="fixed bottom-4 right-4 max-w-sm bg-white rounded-lg shadow-lg overflow-hidden border-l-4 border-orange-500">
                    <div class="p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="p-2 rounded-full bg-orange-100 text-orange-500">
                                    <i class="fas fa-bell"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900" x-text="previewNotification.title"></h3>
                                <p class="mt-1 text-sm text-gray-500" x-text="previewNotification.message"></p>
                                <div class="mt-2">
                                    <button @click="viewPreviewNotification" class="text-sm font-medium text-orange-500 hover:text-orange-600">
                                        View
                                    </button>
                                </div>
                            </div>
                            <button @click="dismissPreview" class="ml-auto text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function notificationSystem() {
            return {
                notifications: [
                    // Mock data for notifications - in a real app, this would come from an API/PHP backend
                    {
                        id: 1,
                        title: "New Application Submitted",
                        message: "TechSprint Solutions has submitted a new application for startup registration.",
                        type: "application",
                        priority: "high",
                        is_read: false,
                        created_at: new Date(new Date().getTime() - 30 * 60000), // 30 minutes ago
                        isNew: false
                    },
                    {
                        id: 2,
                        title: "System Update Completed",
                        message: "The system has been updated to version 2.4.5. New features include enhanced reporting and analytics.",
                        type: "system",
                        priority: "medium",
                        is_read: false,
                        created_at: new Date(new Date().getTime() - 2 * 3600000), // 2 hours ago
                        isNew: false
                    },
                    {
                        id: 3,
                        title: "Application Approved",
                        message: "GreenEarth's application has been approved. An email notification has been sent to the applicant.",
                        type: "success",
                        priority: "medium",
                        is_read: false,
                        created_at: new Date(new Date().getTime() - 5 * 3600000), // 5 hours ago
                        isNew: false
                    },
                    {
                        id: 4,
                        title: "Upcoming Webinar",
                        message: "Reminder: Startup Funding Webinar scheduled for tomorrow at 2:00 PM. 45 participants have registered so far.",
                        type: "event",
                        priority: "medium",
                        is_read: false,
                        created_at: new Date(new Date().getTime() - 24 * 3600000), // 1 day ago
                        isNew: false
                    },
                    {
                        id: 5,
                        title: "Application Rejected",
                        message: "CryptoSafe's application has been rejected due to incomplete documentation. An email notification has been sent.",
                        type: "alert",
                        priority: "high",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 2 * 24 * 3600000), // 2 days ago
                        isNew: false
                    },
                    {
                        id: 6,
                        title: "New User Registered",
                        message: "Priya Sharma from EduTech has created a new account. Profile verification pending.",
                        type: "system",
                        priority: "low",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 3 * 24 * 3600000), // 3 days ago
                        isNew: false
                    },
                    {
                        id: 7,
                        title: "Payment Processing Error",
                        message: "Failed to process payment for MediHelp's premium plan. Please check payment gateway logs.",
                        type: "alert",
                        priority: "high",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 4 * 24 * 3600000), // 4 days ago
                        isNew: false
                    },
                    {
                        id: 8,
                        title: "Server Maintenance",
                        message: "Scheduled maintenance will be performed on June 15th from 2:00 AM to 4:00 AM. The system may be temporarily unavailable.",
                        type: "warning",
                        priority: "medium",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 5 * 24 * 3600000), // 5 days ago
                        isNew: false
                    },
                    {
                        id: 9,
                        title: "Resource Updated",
                        message: "The 'Startup India Registration Guide' has been updated with new information about tax benefits.",
                        type: "system",
                        priority: "low",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 6 * 24 * 3600000), // 6 days ago
                        isNew: false
                    },
                    {
                        id: 10,
                        title: "New Feature: Analytics Dashboard",
                        message: "We've launched an improved analytics dashboard with more detailed metrics for startups.",
                        type: "system",
                        priority: "medium",
                        is_read: true,
                        created_at: new Date(new Date().getTime() - 7 * 24 * 3600000), // 7 days ago
                        isNew: false
                    }
                ],
                currentFilter: 'all',
                searchQuery: '',
                showNotificationPreferences: false,
                notificationPreferences: {
                    system: true,
                    applications: true,
                    users: true,
                    events: true,
                    security: true,
                    in_app: true,
                    email: true,
                    sms: false,
                    push: true,
                    email_frequency: 'daily',
                    quiet_hours_start: '22',
                    quiet_hours_end: '7',
                    bypass_quiet_hours: true,
                    system_priority: 'medium',
                    application_priority: 'high'
                },
                selectedNotifications: [],
                selectAll: false,
                activeOptions: null,
                currentPage: 1,
                itemsPerPage: 5,
                showPreview: false,
                previewNotification: null,
                
                init() {
                    // Simulate receiving a new notification after 10 seconds
                    setTimeout(() => {
                        this.receiveNewNotification();
                    }, 10000);
                },
                
                get unreadCount() {
                    return this.notifications.filter(n => !n.is_read).length;
                },
                
                get importantCount() {
                    return this.notifications.filter(n => n.priority === 'high').length;
                },
                
                get todayCount() {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return this.notifications.filter(n => new Date(n.created_at) >= today).length;
                },
                
                get totalNotifications() {
                    return this.notifications.length;
                },
                
                get filteredNotifications() {
                    // Apply filters and search
                    let filtered = [...this.notifications];
                    
                    // Apply category filter
                    if (this.currentFilter === 'unread') {
                        filtered = filtered.filter(n => !n.is_read);
                    } else if (this.currentFilter === 'system') {
                        filtered = filtered.filter(n => n.type === 'system');
                    } else if (this.currentFilter === 'applications') {
                        filtered = filtered.filter(n => n.type === 'application');
                    } else if (this.currentFilter === 'important') {
                        filtered = filtered.filter(n => n.priority === 'high');
                    }
                    
                    // Apply search
                    if (this.searchQuery.trim() !== '') {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(n => 
                            n.title.toLowerCase().includes(query) || 
                            n.message.toLowerCase().includes(query)
                        );
                    }
                    
                    // Apply pagination
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return filtered.slice(start, end);
                },
                
                get paginationStart() {
                    return Math.min((this.currentPage - 1) * this.itemsPerPage + 1, this.totalNotifications);
                },
                
                get paginationEnd() {
                    return Math.min(this.currentPage * this.itemsPerPage, this.totalNotifications);
                },
                
                get totalPages() {
                    return Math.ceil(this.totalFilteredNotifications / this.itemsPerPage);
                },
                
                get totalFilteredNotifications() {
                    // Count total filtered notifications without pagination
                    let filtered = [...this.notifications];
                    
                    if (this.currentFilter === 'unread') {
                        filtered = filtered.filter(n => !n.is_read);
                    } else if (this.currentFilter === 'system') {
                        filtered = filtered.filter(n => n.type === 'system');
                    } else if (this.currentFilter === 'applications') {
                        filtered = filtered.filter(n => n.type === 'application');
                    } else if (this.currentFilter === 'important') {
                        filtered = filtered.filter(n => n.priority === 'high');
                    }
                    
                    if (this.searchQuery.trim() !== '') {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(n => 
                            n.title.toLowerCase().includes(query) || 
                            n.message.toLowerCase().includes(query)
                        );
                    }
                    
                    return filtered.length;
                },
                
                receiveNewNotification() {
                    // Simulate receiving a new notification
                    const newNotification = {
                        id: this.notifications.length + 1,
                        title: "New Application Update",
                        message: "FastCash has updated their startup application with new financial documents.",
                        type: "application",
                        priority: "high",
                        is_read: false,
                        created_at: new Date(),
                        isNew: true
                    };
                    
                    this.notifications.unshift(newNotification);
                    this.showNotificationPreview(newNotification);
                    
                    // Update UI to show it's a new notification
                    setTimeout(() => {
                        const index = this.notifications.findIndex(n => n.id === newNotification.id);
                        if (index !== -1) {
                            this.notifications[index].isNew = false;
                        }
                    }, 5000);
                    
                    /* In a real implementation, you would use WebSockets or Server-Sent Events:
                    
                    // Using WebSockets
                    const socket = new WebSocket('wss://your-server.com/ws');
                    socket.onmessage = (event) => {
                        const notification = JSON.parse(event.data);
                        this.notifications.unshift({
                            ...notification,
                            isNew: true
                        });
                        this.showNotificationPreview(notification);
                    };
                    
                    // Or using Server-Sent Events (SSE)
                    const eventSource = new EventSource('https://your-server.com/events');
                    eventSource.onmessage = (event) => {
                        const notification = JSON.parse(event.data);
                        this.notifications.unshift({
                            ...notification,
                            isNew: true
                        });
                        this.showNotificationPreview(notification);
                    };
                    */
                },
                
                showNotificationPreview(notification) {
                    this.previewNotification = notification;
                    this.showPreview = true;
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        this.showPreview = false;
                    }, 5000);
                },
                
                viewPreviewNotification() {
                    // In a real app, this would navigate to the relevant page
                    this.showPreview = false;
                    
                    // Mark as read
                    if (this.previewNotification) {
                        this.markAsRead(this.previewNotification.id);
                    }
                },
                
                dismissPreview() {
                    this.showPreview = false;
                },
                
                markAsRead(id) {
                    // In a real app, this would make an AJAX call to the backend
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index !== -1) {
                        this.notifications[index].is_read = true;
                    }
                    
                    /* 
                    PHP implementation would be something like:
                    
                    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("ii", $notification_id, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                markAllAsRead() {
                    // Mark all notifications as read
                    this.notifications.forEach(notification => {
                        notification.is_read = true;
                    });
                    
                    /*
                    PHP implementation:
                    
                    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? OR user_id IS NULL");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                markSelectedAsRead() {
                    if (this.selectedNotifications.length === 0) return;
                    
                    this.notifications.forEach(notification => {
                        if (this.selectedNotifications.includes(notification.id)) {
                            notification.is_read = true;
                        }
                    });
                    
                    this.selectedNotifications = [];
                    this.selectAll = false;
                    
                    /*
                    PHP implementation:
                    
                    $ids = implode(',', array_map('intval', $selected_ids));
                    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($ids) AND (user_id = ? OR user_id IS NULL)");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                deleteNotification(id) {
                    // Delete a single notification
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index !== -1) {
                        this.notifications.splice(index, 1);
                    }
                    this.activeOptions = null;
                    
                    /*
                    PHP implementation:
                    
                    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("ii", $notification_id, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                deleteSelected() {
                    if (this.selectedNotifications.length === 0) return;
                    
                    this.notifications = this.notifications.filter(
                        notification => !this.selectedNotifications.includes(notification.id)
                    );
                    
                    this.selectedNotifications = [];
                    this.selectAll = false;
                    
                    /*
                    PHP implementation:
                    
                    $ids = implode(',', array_map('intval', $selected_ids));
                    $stmt = $conn->prepare("DELETE FROM notifications WHERE id IN ($ids) AND user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                snoozeNotification(id) {
                    // In a real app, this would update the notification to reappear later
                    alert(`Notification snoozed for 24 hours`);
                    this.activeOptions = null;
                    
                    /*
                    PHP implementation:
                    
                    $snooze_until = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $stmt = $conn->prepare("UPDATE notifications SET snoozed_until = ? WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("sii", $snooze_until, $notification_id, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                toggleSelectAll() {
                    if (this.selectAll) {
                        // Select all visible notifications
                        this.selectedNotifications = this.filteredNotifications.map(n => n.id);
                    } else {
                        // Deselect all
                        this.selectedNotifications = [];
                    }
                },
                
                toggleNotificationOptions(id) {
                    this.activeOptions = this.activeOptions === id ? null : id;
                },
                
                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },
                
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },
                
                resetPreferences() {
                    this.notificationPreferences = {
                        system: true,
                        applications: true,
                        users: true,
                        events: true,
                        security: true,
                        in_app: true,
                        email: true,
                        sms: false,
                        push: true,
                        email_frequency: 'daily',
                        quiet_hours_start: '22',
                        quiet_hours_end: '7',
                        bypass_quiet_hours: true,
                        system_priority: 'medium',
                        application_priority: 'high'
                    };
                },
                
                savePreferences() {
                    // In a real app, this would save to the database
                    alert('Notification preferences saved successfully!');
                    this.showNotificationPreferences = false;
                    
                    /*
                    PHP implementation:
                    
                    $preferences = json_encode($notificationPreferences);
                    $stmt = $conn->prepare("UPDATE user_preferences SET notification_settings = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $preferences, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    */
                },
                
                formatTimeAgo(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diffMs = now - date;
                    const diffSec = Math.floor(diffMs / 1000);
                    const diffMin = Math.floor(diffSec / 60);
                    const diffHour = Math.floor(diffMin / 60);
                    const diffDay = Math.floor(diffHour / 24);
                    
                    if (diffSec < 60) {
                        return 'just now';
                    } else if (diffMin < 60) {
                        return diffMin + (diffMin === 1 ? ' minute ago' : ' minutes ago');
                    } else if (diffHour < 24) {
                        return diffHour + (diffHour === 1 ? ' hour ago' : ' hours ago');
                    } else if (diffDay < 7) {
                        return diffDay + (diffDay === 1 ? ' day ago' : ' days ago');
                    } else {
                        return date.toLocaleDateString();
                    }
                }
            };
        }
    </script>
</body>
</html>
