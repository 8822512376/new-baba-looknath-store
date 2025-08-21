<?php
include 'common/header.php';

$users = $conn->query("SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC");
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Registered Users</h1>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">ID</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Name</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Email</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Phone</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Registered On</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-4 font-medium text-gray-800"><?= $user['id'] ?></td>
                        <td class="p-4"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($user['phone']) ?></td>
                        <td class="p-4 text-sm text-gray-600"><?= date('d M, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
