<?php
include 'common/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: order.php");
    exit();
}
$order_id = intval($_GET['id']);

// Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    exit();
}

// Fetch order details
$sql = "SELECT o.*, u.name as user_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
if ($order_result->num_rows === 0) {
    header("Location: order.php");
    exit();
}
$order = $order_result->fetch_assoc();

// Fetch order items
$items_sql = "SELECT oi.*, p.name as product_name, p.image as product_image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>

<a href="order.php" class="text-indigo-600 hover:underline mb-4 inline-block"><i class="fas fa-arrow-left mr-2"></i>Back to Orders</a>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Order Detail: #<?= $order_id ?></h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Items -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Order Items</h2>
        <div class="space-y-4">
            <?php while($item = $items_result->fetch_assoc()): ?>
            <div class="flex items-center border-b pb-4">
                <img src="<?= $item['product_image'] ?>" class="w-16 h-16 rounded-md object-cover mr-4">
                <div class="flex-1">
                    <p class="font-semibold"><?= htmlspecialchars($item['product_name']) ?></p>
                    <p class="text-sm text-gray-600">Quantity: <?= $item['quantity'] ?></p>
                </div>
                <p class="font-semibold">₹<?= number_format($item['price'] * $item['quantity']) ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="mt-4 text-right">
            <p class="text-lg font-bold">Total: <span class="text-indigo-600">₹<?= number_format($order['total_amount']) ?></span></p>
        </div>
    </div>

    <!-- Customer & Status -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['user_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Update Status</h2>
            <form id="status-form">
                <input type="hidden" name="action" value="update_status">
                <select name="status" id="status-select" class="w-full p-2 border rounded-md">
                    <option value="Placed" <?= $order['status'] == 'Placed' ? 'selected' : '' ?>>Placed</option>
                    <option value="Dispatched" <?= $order['status'] == 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                    <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="w-full mt-4 bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700">Update Status</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('status-form').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    
    fetch('order_detail.php?id=<?= $order_id ?>', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if(data.success) {
            setTimeout(() => location.reload(), 1000);
        }
    }).catch(err => { hideLoader(); showToast('An error occurred.', false); });
});
</script>

<?php include 'common/bottom.php'; ?>
