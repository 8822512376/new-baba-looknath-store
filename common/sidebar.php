<?php
// common/sidebar.php
?>
<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

<!-- Sidebar Menu -->
<div id="sidebar" class="fixed top-0 left-0 h-full w-72 bg-white shadow-lg z-50 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    
    <!-- Header -->
    <div class="p-4 border-b">
        <h2 class="text-2xl font-bold text-indigo-600">Menu</h2>
    </div>

    <!-- Main Navigation -->
    <nav class="flex-grow mt-4 space-y-1">
        <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2"><i class="fas fa-home w-6 text-center text-lg"></i><span class="ml-3 font-medium">Home</span></a>
        <a href="product.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2"><i class="fas fa-tshirt w-6 text-center text-lg"></i><span class="ml-3 font-medium">All Products</span></a>
        <a href="order.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2"><i class="fas fa-box w-6 text-center text-lg"></i><span class="ml-3 font-medium">My Orders</span></a>
        <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg mx-2"><i class="fas fa-user-circle w-6 text-center text-lg"></i><span class="ml-3 font-medium">Profile</span></a>
    </nav>
    
    <!-- About Shop Section -->
    <div class="px-4 py-3 mt-2 border-t">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">About Our Store</h3>
        <p class="text-sm text-gray-600">Welcome to New Baba LookNath Cloth Store, your one-stop shop for the latest fashion trends. We pride ourselves on quality and customer satisfaction.</p>
    </div>

    <!-- ============================================= -->
    <!-- ========== YOUR LIVE MAP CODE HERE ========== -->
    <!-- ============================================= -->
    <div class="px-4 py-3 border-t">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Our Location</h3>
        <div class="w-full rounded-lg overflow-hidden border">
            <!-- This is your exact iframe code, with width adjusted to fit -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3579.9716127242405!2d92.3125453!3d26.197601700000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x375acf75a5ede2dd%3A0x814a8af2f97e71ee!2sNew%20Baba%20Looknath%20Cloth%20Store!5e0!3m2!1sen!2sin!4v1755577349603!5m2!1sen!2sin" 
                width="100%" 
                height="250" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>

    <!-- Social Media Links -->
    <div class="px-4 py-3 border-t">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Follow Us</h3>
        <div class="flex items-center justify-around text-2xl">
            <a href="https://wa.me/918822512376" target="_blank" class="text-green-500 hover:text-green-600"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.instagram.com/YOUR_USERNAME" target="_blank" class="text-pink-500 hover:text-pink-600"><i class="fab fa-instagram"></i></a>
            <a href="https://www.facebook.com/YOUR_PAGE" target="_blank" class="text-blue-600 hover:text-blue-700"><i class="fab fa-facebook"></i></a>
            <a href="https://t.me/YOUR_CHANNEL" target="_blank" class="text-blue-400 hover:text-blue-500"><i class="fab fa-telegram-plane"></i></a>
        </div>
    </div>
    
    <!-- Logout/Login Section -->
    <div class="p-2 border-t mt-2">
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php?logout=true" class="flex items-center px-4 py-3 text-red-500 bg-red-50 hover:bg-red-100 rounded-lg mx-2"><i class="fas fa-sign-out-alt w-6 text-center text-lg"></i><span class="ml-3 font-semibold">Logout</span></a>
        <?php else: ?>
        <a href="login.php" class="flex items-center px-4 py-3 text-green-600 bg-green-50 hover:bg-green-100 rounded-lg mx-2"><i class="fas fa-sign-in-alt w-6 text-center text-lg"></i><span class="ml-3 font-semibold">Login</span></a>
        <?php endif; ?>
    </div>
</div>
