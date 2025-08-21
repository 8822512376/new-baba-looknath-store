<?php
include 'common/header.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(id) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'Delivered'")->fetch_assoc()['sum'];
$active_products = $conn->query("SELECT COUNT(id) as count FROM products WHERE stock > 0")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Placed'")->fetch_assoc()['count'];
$shipped_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Dispatched'")->fetch_assoc()['count'];
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-full">
            <i class="fas fa-users fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total_users ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-green-100 text-green-600 p-4 rounded-full">
            <i class="fas fa-shopping-cart fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900"><?= $total_orders ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-indigo-100 text-indigo-600 p-4 rounded-full">
            <i class="fas fa-rupee-sign fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">₹<?= number_format($total_revenue ?? 0) ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-yellow-100 text-yellow-600 p-4 rounded-full">
            <i class="fas fa-box-open fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Active Products</p>
            <p class="text-2xl font-bold text-gray-900"><?= $active_products ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-red-100 text-red-600 p-4 rounded-full">
            <i class="fas fa-clock fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Pending Orders</p>
            <p class="text-2xl font-bold text-gray-900"><?= $pending_orders ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <div class="bg-purple-100 text-purple-600 p-4 rounded-full">
            <i class="fas fa-truck fa-2x"></i>
        </div>
        <div class="ml-4">
            <p class="text-gray-500">Shipments</p>
            <p class="text-2xl font-bold text-gray-900"><?= $shipped_orders ?></p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
    <div class="flex space-x-4">
        <a href="product.php" class="bg-indigo-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus-circle mr-2"></i>Add Product
        </a>
        <a href="user.php" class="bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg hover:bg-gray-800">
            <i class="fas fa-users-cog mr-2"></i>Manage Users
        </a>
    </div>
</div>

<?php
include 'common/bottom.php';
?>
