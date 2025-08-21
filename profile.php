<?php
// STEP 1: Include the master configuration. This is essential.
include 'common/config.php';

// STEP 2: Ensure the user is logged in. If not, redirect to the login page.
check_login();

// STEP 3: Handle server-side actions (AJAX requests from the forms).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json'); // Set the correct header for JSON response
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];

    // --- ACTION: Update User Profile Information ---
    if ($action === 'update_profile') {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        
        $sql = "UPDATE users SET name=?, phone=?, address=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        // Bind parameters: 's' for string, 'i' for integer
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name; // Keep session name updated
            $response = ['success' => true, 'message' => 'Profile Updated Successfully!'];
        } else {
            $response['message'] = 'Error: Failed to update profile in database.';
        }
        $stmt->close();
    } 
    // --- ACTION: Change User Password ---
    elseif ($action === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];

        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result && password_verify($current_pass, $result['password'])) {
            // Current password is correct, proceed to update
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_new_pass, $user_id);
            
            if($update_stmt->execute()){
                $response = ['success' => true, 'message' => 'Password Changed Successfully!'];
            } else {
                $response['message'] = 'Error: Failed to update password.';
            }
            $update_stmt->close();
        } else {
            // Incorrect current password
            $response['message'] = 'Incorrect Current Password.';
        }
    }
    
    echo json_encode($response);
    exit(); // IMPORTANT: Stop the script here to prevent sending any HTML with the JSON.
}

// STEP 4: Handle Logout. This must be outside the POST block.
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// STEP 5: Fetch user data to display on the page if it's not an AJAX request.
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, phone, address FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// STEP 6: Include the HTML header
include 'common/header.php';
?>

<div class="p-4 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>

    <!-- Edit Profile Form -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Personal Information</h2>
        <form id="profile-form" class="space-y-4">
            <input type="hidden" name="action" value="update_profile">
            <div>
                <label class="text-sm font-medium">Email (cannot be changed)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-2 mt-1 border rounded-md bg-gray-100" readonly>
            </div>
            <div>
                <label class="text-sm font-medium">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <div>
                <label class="text-sm font-medium">Phone</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <div>
                <label class="text-sm font-medium">Address</label>
                <textarea name="address" rows="3" class="w-full p-2 mt-1 border rounded-md"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md font-semibold hover:bg-indigo-700">Save Changes</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Change Password</h2>
        <form id="password-form" class="space-y-4">
            <input type="hidden" name="action" value="change_password">
            <div>
                <label class="text-sm font-medium">Current Password</label>
                <input type="password" name="current_password" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <div>
                <label class="text-sm font-medium">New Password</label>
                <input type="password" name="new_password" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <button type="submit" class="w-full bg-gray-700 text-white py-2 rounded-md font-semibold hover:bg-gray-800">Update Password</button>
        </form>
    </div>

    <!-- Logout Button -->
    <div class="mt-6">
        <a href="profile.php?logout=true" class="block text-center w-full bg-red-500 text-white py-2 rounded-md font-semibold hover:bg-red-600">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </a>
    </div>
</div>

<script>
// Attach event listener to the profile update form
document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this);
});

// Attach event listener to the password change form
document.getElementById('password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this);
});

// Reusable function to handle AJAX form submission
function submitForm(form) {
    showLoader();
    const formData = new FormData(form);
    
    fetch('profile.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if (data.success && form.id === 'password-form') {
            form.reset(); // Clear password form on success
        }
    }).catch(error => {
        hideLoader();
        showToast('A network error occurred. Please try again.', false);
        console.error('Fetch Error:', error);
    });
}
</script>

<?php 
// STEP 7: Include the footer and close the database connection
include 'common/bottom.php'; 
$conn->close();
?>
