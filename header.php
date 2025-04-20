<header class="bg-white shadow-sm py-4 px-6 flex items-center justify-between no-print">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="text-gray-500 text-xl cursor-pointer mr-6">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="text-lg font-medium text-gray-800">
            <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
        </h2>
    </div>
    <div class="flex items-center">
       
        <a href="notification.php">
        <div class="relative mr-4">
            <i class="fas fa-bell text-gray-500 cursor-pointer"></i>
            <span class="absolute bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center notification-badge">3</span>
        </div></a>
        <a href="profile.php" class="flex items-center hover:text-blue-500 transition-colors">
            <img class="h-8 w-8 rounded-full object-cover" src="<?php echo !empty($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : 'harhprofile.png'; ?>" alt="User Profile">
            <span class="ml-2 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </a>
    </div>
</header>

<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('hidden');
        document.querySelector('.admin-sidebar').classList.toggle('md:block');
    });
</script>