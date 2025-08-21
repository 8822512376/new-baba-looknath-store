<?php
include 'common/header.php';

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];

// Handle password update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    header('Content-Type: application/json');
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];

    $sql = "SELECT password FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($current_pass, $result['password'])) {
        $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_sql = "UPDATE admin SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_new_pass, $admin_id);
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
    }
    exit();
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Profile</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Profile Info -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-500">Username</label>
                <p class="text-lg text-gray-800 font-semibold"><?= htmlspecialchars($admin_username) ?></p>
            </div>
             <div>
                <label class="text-sm font-medium text-gray-500">Role</label>
                <p class="text-lg text-gray-800 font-semibold">Administrator</p>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Change Password</h2>
        <form id="password-form" class="space-y-4">
            <input type="hidden" name="action" value="update_password">
            <div>
                <label class="block text-sm font-medium">Current Password</label>
                <input type="password" name="current_password" class="w-full p-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium">New Password</label>
                <input type="password" name="new_password" class="w-full p-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Confirm New Password</label>
                <input type="password" name="confirm_password" class="w-full p-2 mt-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Update Password</button>
        </form>
    </div>
</div>

<script>
document.getElementById('password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const newPass = this.elements['new_password'].value;
    const confirmPass = this.elements['confirm_password'].value;

    if (newPass.length < 6) {
        showToast("New password must be at least 6 characters long.", false);
        return;
    }

    if (newPass !== confirmPass) {
        showToast("New passwords do not match.", false);
        return;
    }

    showLoader();
    const formData = new FormData(this);

    fetch('profile.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if (data.success) {
            this.reset();
        }
    }).catch(err => { 
        hideLoader(); 
        showToast('An error occurred.', false);
        console.error(err);
    });
});
</script>

<?php include 'common/bottom.php'; ?>
