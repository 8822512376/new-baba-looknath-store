<?php
include 'common/config.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'signup') {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $check_sql = "SELECT id FROM users WHERE email = '$email' OR phone = '$phone'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email or Phone already registered.']);
        } else {
            $insert_sql = "INSERT INTO users (name, phone, email, password) VALUES ('$name', '$phone', '$email', '$password')";
            if ($conn->query($insert_sql)) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                echo json_encode(['success' => true, 'message' => 'Signup successful!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error during registration.']);
            }
        }
    } elseif ($action === 'login') {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT id, name, password FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                echo json_encode(['success' => true, 'message' => 'Login successful!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No user found with this email.']);
        }
    }
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login / Signup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        body, html { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; -webkit-tap-highlight-color: transparent; }
        .tab-active { border-bottom-color: #4f46e5; color: #4f46e5; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="w-full max-w-md mx-auto bg-white min-h-screen">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-center text-indigo-600 mb-4">Welcome Back!</h1>
            
            <!-- Tabs -->
            <div class="flex border-b mb-6">
                <button id="login-tab-btn" class="flex-1 py-2 text-center font-semibold border-b-2 tab-active" onclick="showTab('login')">Login</button>
                <button id="signup-tab-btn" class="flex-1 py-2 text-center font-semibold text-gray-500" onclick="showTab('signup')">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div id="login-tab">
                <form id="login-form" class="space-y-4">
                    <input type="hidden" name="action" value="login">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-semibold hover:bg-indigo-700">Login</button>
                </form>
            </div>

            <!-- Signup Form -->
            <div id="signup-tab" class="hidden">
                <form id="signup-form" class="space-y-4">
                    <input type="hidden" name="action" value="signup">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-semibold hover:bg-indigo-700">Create Account</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Loading Modal & Toast container -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
        <style>.loader { border-top-color: #4f46e5; animation: spin 1s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    </div>
    <div id="toast-container"></div>
    
    <script>
    function showTab(tabName) {
        document.getElementById('login-tab').classList.toggle('hidden', tabName !== 'login');
        document.getElementById('signup-tab').classList.toggle('hidden', tabName !== 'signup');
        document.getElementById('login-tab-btn').classList.toggle('tab-active', tabName === 'login');
        document.getElementById('signup-tab-btn').classList.toggle('tab-active', tabName === 'signup');
        document.querySelector('#' + tabName + '-tab-btn').classList.add('text-indigo-600');
        document.querySelector('#' + (tabName === 'login' ? 'signup' : 'login') + '-tab-btn').classList.remove('text-indigo-600');
    }

    // AJAX handlers
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, 'login.php');
    });

    document.getElementById('signup-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, 'login.php');
    });

    function submitForm(form, url) {
        showLoader();
        const formData = new FormData(form);
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            showToast(data.message, data.success);
            if (data.success) {
                setTimeout(() => window.location.href = 'index.php', 1500);
            }
        })
        .catch(error => {
            hideLoader();
            showToast('An error occurred.', false);
            console.error('Error:', error);
        });
    }

    // Helper functions
    function showLoader() { document.getElementById('loading-modal').classList.remove('hidden'); }
    function hideLoader() { document.getElementById('loading-modal').classList.add('hidden'); }
    function showToast(message, isSuccess = true) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `fixed bottom-10 left-1/2 -translate-x-1/2 px-6 py-3 rounded-lg shadow-lg text-white ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
        toast.innerText = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    </script>
</body>
</html>
