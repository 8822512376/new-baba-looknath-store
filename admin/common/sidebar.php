<?php
// admin/common/sidebar.php
?>
<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-gray-800 text-gray-200 flex-shrink-0 flex-col fixed lg:relative h-full z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="p-4 text-center border-b border-gray-700">
        <h2 class="text-xl font-bold text-white">New Baba LookNath Cloth Store</h2>
        <p class="text-xs text-gray-400">ADMIN PANEL</p>
    </div>
    <nav class="flex-1 mt-4 space-y-2">
        <a href="index.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= $current_page == 'index.php' ? 'sidebar-active' : '' ?>">
            <i class="fas fa-tachometer-alt w-6"></i><span class="ml-3">Dashboard</span>
        </a>
        <a href="category.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= $current_page == 'category.php' ? 'sidebar-active' : '' ?>">
            <i class="fas fa-tags w-6"></i><span class="ml-3">Categories</span>
        </a>
        <a href="product.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= $current_page == 'product.php' ? 'sidebar-active' : '' ?>">
            <i class="fas fa-box w-6"></i><span class="ml-3">Products</span>
        </a>
        <a href="order.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= in_array($current_page, ['order.php', 'order_detail.php']) ? 'sidebar-active' : '' ?>">
            <i class="fas fa-shopping-cart w-6"></i><span class="ml-3">Orders</span>
        </a>
        <a href="user.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= $current_page == 'user.php' ? 'sidebar-active' : '' ?>">
            <i class="fas fa-users w-6"></i><span class="ml-3">Users</span>
        </a>
        <a href="profile.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?= $current_page == 'profile.php' ? 'sidebar-active' : '' ?>">
            <i class="fas fa-user-cog w-6"></i><span class="ml-3">Profile</span>
        </a>
    </nav>
    <div class="p-4 border-t border-gray-700">
         <a href="login.php?logout=true" class="flex items-center px-4 py-3 hover:bg-red-500 rounded-md">
            <i class="fas fa-sign-out-alt w-6"></i><span class="ml-3">Logout</span>
        </a>
    </div>
</aside>
