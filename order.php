<?php
// STEP 1: Include config and check login status
include 'common/config.php';
check_login();

$user_id = $_SESSION['user_id'];

// STEP 2: A robust SQL query to get all order items and details for the user
$sql = "SELECT 
            o.id, 
            o.total_amount, 
            o.status, 
            o.created_at, 
            oi.quantity, 
            p.name as product_name, 
            p.image as product_image
        FROM orders AS o
        JOIN order_items AS oi ON o.id = oi.order_id
        JOIN products AS p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// STEP 3: Correctly group items by their Order ID
$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'details' => [
                    'total_amount' => $row['total_amount'],
                    'status'       => $row['status'],
                    'created_at'   => $row['created_at']
                ],
                'items' => []
            ];
        }
        
        $orders[$order_id]['items'][] = [
            'product_name'  => $row['product_name'],
            'product_image' => $row['product_image'],
            'quantity'      => $row['quantity']
        ];
    }
}

// STEP 4: Filter the correctly grouped orders into "Active" and "History"
$active_orders = array_filter($orders, function($order) {
    $active_stati = ['Placed', 'Dispatched', 'Awaiting Payment'];
    return in_array($order['details']['status'], $active_stati);
});

$history_orders = array_filter($orders, function($order) {
    $history_stati = ['Delivered', 'Cancelled'];
    return in_array($order['details']['status'], $history_stati);
});

// STEP 5: Include the header to start rendering the page
include 'common/header.php';
?>

<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">My Orders</h1>
    
    <?php if(isset($_GET['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
          <p class="font-bold">Order Placed Successfully!</p>
          <p>Thank you for your purchase. You can track your order status below.</p>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex border-b mb-4">
        <button class="flex-1 py-2 text-center font-semibold border-b-2 tab-active" onclick="showTab('active')">Active Orders</button>
        <button class="flex-1 py-2 text-center font-semibold text-gray-500" onclick="showTab('history')">Order History</button>
    </div>

    <!-- Active Orders Tab -->
    <div id="active-tab" class="space-y-4">
        <?php if (empty($active_orders)): ?>
            <p class="text-gray-500 text-center py-10">You have no active orders.</p>
        <?php else: ?>
            <?php foreach ($active_orders as $order_id => $order): ?>
                <div class="bg-white rounded-lg shadow-md border overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center">
                            <img src="<?= htmlspecialchars($order['items'][0]['product_image']) ?>" class="w-16 h-16 rounded-md object-cover mr-4">
                            <div>
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($order['items'][0]['product_name']) ?></h3>
                                <p class="text-sm text-gray-500">Order ID: #<?= $order_id ?></p>
                                <?php if (count($order['items']) > 1): ?>
                                    <p class="text-xs text-gray-500">+ <?= count($order['items']) - 1 ?> more item(s)</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Progress Tracker -->
                    <?php
                    $status = $order['details']['status'];
                    $placed_active = in_array($status, ['Placed', 'Dispatched', 'Delivered', 'Awaiting Payment']);
                    $dispatched_active = in_array($status, ['Dispatched', 'Delivered']);
                    $delivered_active = in_array($status, ['Delivered']);
                    ?>
                    <div class="px-4 pb-4">
                        <div class="flex items-center">
                            <div class="text-center"><div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center <?= $placed_active ? 'bg-indigo-600 text-white' : 'bg-gray-300' ?>"><i class="fas fa-check"></i></div><p class="text-xs mt-1 <?= $placed_active ? 'text-indigo-600 font-semibold' : '' ?>">Placed</p></div>
                            <div class="flex-1 h-1 <?= $dispatched_active ? 'bg-indigo-600' : 'bg-gray-300' ?>"></div>
                            <div class="text-center"><div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center <?= $dispatched_active ? 'bg-indigo-600 text-white' : 'bg-gray-300' ?>"><i class="fas fa-truck"></i></div><p class="text-xs mt-1 <?= $dispatched_active ? 'text-indigo-600 font-semibold' : '' ?>">Dispatched</p></div>
                            <div class="flex-1 h-1 <?= $delivered_active ? 'bg-indigo-600' : 'bg-gray-300' ?>"></div>
                            <div class="text-center"><div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center <?= $delivered_active ? 'bg-indigo-600 text-white' : 'bg-gray-300' ?>"><i class="fas fa-box-open"></i></div><p class="text-xs mt-1 <?= $delivered_active ? 'text-indigo-600 font-semibold' : '' ?>">Delivered</p></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Order History Tab -->
    <div id="history-tab" class="hidden space-y-4">
        <?php if (empty($history_orders)): ?>
            <p class="text-gray-500 text-center py-10">You have no past orders.</p>
        <?php else: ?>
            <?php foreach ($history_orders as $order_id => $order): ?>
                <div class="bg-white rounded-lg shadow-sm border p-4">
                    <div class="flex items-center">
                        <img src="<?= htmlspecialchars($order['items'][0]['product_image']) ?>" class="w-16 h-16 rounded-md object-cover mr-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-700"><?= htmlspecialchars($order['items'][0]['product_name']) ?></h3>
                            <p class="text-sm text-gray-500">Order #<?= $order_id ?></p>
                        </div>
                        <span class="text-lg font-bold <?= $order['details']['status'] == 'Cancelled' ? 'text-red-500' : 'text-green-600' ?>">
                            <?= htmlspecialchars($order['details']['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .tab-active { border-bottom-color: #4f46e5; color: #4f46e5; }
</style>

<script>
function showTab(tabName) {
    const isActiveTab = tabName === 'active';
    document.getElementById('active-tab').classList.toggle('hidden', !isActiveTab);
    document.getElementById('history-tab').classList.toggle('hidden', isActiveTab);
    document.querySelector('button[onclick="showTab(\'active\')"]').classList.toggle('tab-active', isActiveTab);
    document.querySelector('button[onclick="showTab(\'history\')"]').classList.toggle('tab-active', !isActiveTab);
}
</script>

<?php 
include 'common/bottom.php'; 
$conn->close();
?>
