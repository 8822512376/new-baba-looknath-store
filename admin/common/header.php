<?php
// admin/common/header.php
include __DIR__ . '/../../common/config.php';

// Admin session check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        body, html { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; overflow-x: hidden; }
        .sidebar-active { background-color: #4f46e5; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include 'sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center lg:hidden">
                <button id="menu-btn" class="text-gray-700 text-xl"><i class="fas fa-bars"></i></button>
                <h1 class="font-bold text-indigo-600">Admin Panel</h1>
                <div></div>
            </header>
            <main class="flex-1 p-4 md:p-6">
