<?php
// common/header.php

// Ensure the session is started before accessing session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Securely define the cart count variable
$cart_count = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>New Baba LookNath Cloth Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        /* Disable text selection and other user interactions */
        body, html {
            overflow-x: hidden;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        /* Custom scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .active-nav { color: #4f46e5; }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Main container -->
    <div id="app-container" class="w-full max-w-md mx-auto bg-white min-h-screen shadow-lg">

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main content -->
        <main id="main-content" class="pb-20 transition-transform duration-300">
            <!-- Top Header -->
            <header class="sticky top-0 bg-white z-40 shadow-sm p-4 flex items-center justify-between">
                <button id="menu-btn" class="text-gray-700 text-xl">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-lg font-bold text-indigo-600">New Baba LookNath Cloth Store</h1>
                <a href="cart.php" class="relative text-gray-700 text-xl">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </header>
