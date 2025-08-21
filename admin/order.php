<?php
include 'common/header.php';

$orders = $conn->query("SELECT o.id, u.name as user_name, o.total_amount, o.status, o.created_at 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC");
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Orders</h1>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Order ID</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Customer</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Total Amount</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Status</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Date</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-4 font-medium text-gray-800">#<?= $order['id'] ?></td>
                        <td class="p-4"><?= htmlspecialchars($order['user_name']) ?></td>
                        <td class="p-4">₹<?= number_format($order['total_amount']) ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                    switch ($order['status']) {
                                        case 'Placed': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'Dispatched': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Delivered': echo 'bg-green-100 text-green-800'; break;
                                        case 'Cancelled': echo 'bg-red-100 text-red-800'; break;
                                    }
                                ?>">
                                <?= $order['status'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-sm text-gray-600"><?= date('d M, Y', strtotime($order['created_at'])) ?></td>
                        <td class="p-4">
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="text-indigo-600 hover:text-indigo-800">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
