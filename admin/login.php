<?php
include '../common/config.php';

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            echo json_encode(['success' => true, 'message' => 'Login successful! Redirecting...']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
    </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Admin Panel Login</h1>
        <form id="admin-login-form" class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-3 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-semibold hover:bg-indigo-700">Login</button>
        </form>
        <div id="error-message" class="mt-4 text-center text-red-500 text-sm"></div>
    </div>
    
    <script>
    document.getElementById('admin-login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const errorMessageDiv = document.getElementById('error-message');
        errorMessageDiv.textContent = '';
        
        fetch('login.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                errorMessageDiv.textContent = data.message;
            }
        })
        .catch(error => {
            errorMessageDiv.textContent = 'An unexpected error occurred.';
            console.error('Error:', error);
        });
    });
    </script>
</body>
</html>
