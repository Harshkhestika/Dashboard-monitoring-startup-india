<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="admin-sidebar bg-white shadow-md w-64 h-screen overflow-y-auto fixed left-0 top-0 no-print">
    <div class="p-4">
        <div class="flex items-center justify-center">
            <img src="india.png" class="h-8 w-8 mr-2" alt="Startup India Logo">
            <h1 class="text-xl font-bold text-gray-800">Startup <span class="text-blue-500">India</span></h1>
        </div>
    </div>
    <div class="mt-4">
        <a href="index.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span class="ml-2">Dashboard</span>
        </a>
        <a href="profile.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-user w-6"></i>
            <span class="ml-2">Profile</span>
        </a>
        <a href="applications.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-file-alt w-6"></i>
            <span class="ml-2">Applications</span>
        </a>
        <a href="resources.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-book w-6"></i>
            <span class="ml-2">Resources</span>
        </a>
        <a href="notification.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-bell w-6"></i>
            <span class="ml-2">Notifications</span>
            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-auto">3</span>
        </a>
        <a href="settings.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-cog w-6"></i>
            <span class="ml-2">Settings</span>
        </a>
        <a href="logout.php" class="sidebar-item flex items-center py-3 px-4 text-gray-700 hover:bg-gray-50 mt-8">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="ml-2">Logout</span>
        </a>
    </div>
</div>