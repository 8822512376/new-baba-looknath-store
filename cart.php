<?php
// Ensure session is started at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'common/config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- AJAX HANDLER ---
// This block runs ONLY for POST requests (like Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Invalid request'];

    // Action: Add to cart
    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0 && $product_id > 0) {
            // Check current stock
            $stock_check = $conn->query("SELECT stock FROM products WHERE id = $product_id");
            $current_stock = $stock_check->fetch_assoc()['stock'];
            $current_cart_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
            
            if (($current_cart_qty + $quantity) > $current_stock) {
                 $response = ['success' => false, 'message' => 'Not enough stock available!'];
            } else {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                $response = ['success' => true, 'message' => 'Added to cart!', 'cart_count' => count($_SESSION['cart'])];
            }
        }
    }
    // Action: Update quantity
    elseif (isset($_POST['update_id']) && isset($_POST['update_qty'])) {
        $product_id = intval($_POST['update_id']);
        $quantity = intval($_POST['update_qty']);
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
            $response = ['success' => true, 'message' => 'Cart updated.'];
        } else {
            unset($_SESSION['cart'][$product_id]);
            $response = ['success' => true, 'message' => 'Item removed.'];
        }
    }
    // Action: Delete from cart
    elseif (isset($_POST['delete_id'])) {
        $product_id = intval($_POST['delete_id']);
        unset($_SESSION['cart'][$product_id]);
        $response = ['success' => true, 'message' => 'Item removed from cart.'];
    }
    
    echo json_encode($response);
    exit(); // IMPORTANT: Stop script execution to prevent sending HTML
}


// --- HTML DISPLAY LOGIC ---
// This part runs ONLY for GET requests (when you visit cart.php directly)
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($product_ids)";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$row['id']];
        $row['quantity'] = $quantity;
        $row['subtotal'] = $row['price'] * $quantity;
        $cart_items[] = $row;
        $total_price += $row['subtotal'];
    }
}

include 'common/header.php';
?>

<!-- The rest of the cart.php HTML remains the same as before -->
<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Your Cart</h1>
    
    <div id="cart-items-container" class="space-y-4">
        <?php if (empty($cart_items)): ?>
            <p id="empty-cart-msg" class="text-gray-500 text-center py-10">Your cart is empty.</p>
        <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
            <div class="flex items-center bg-white p-3 rounded-lg shadow-sm" id="item-<?= $item['id'] ?>">
                <img src="<?= htmlspecialchars($item['image']) ?>" class="w-20 h-20 rounded-md object-cover mr-4">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-sm text-gray-500">Price: ₹<?= number_format($item['price']) ?></p>
                    <div class="flex items-center mt-2">
                        <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" 
                               onchange="updateCart(<?= $item['id'] ?>, this.value)"
                               class="w-16 border text-center rounded-md p-1">
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-indigo-600">₹<?= number_format($item['subtotal']) ?></p>
                    <button onclick="deleteFromCart(<?= $item['id'] ?>)" class="text-red-500 mt-2"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- Checkout Bar -->
<div class="fixed bottom-16 left-0 right-0 w-full max-w-md mx-auto bg-white p-4 border-t shadow-t">
    <div class="flex justify-between items-center mb-3">
        <span class="text-lg font-semibold text-gray-700">Total:</span>
        <span class="text-2xl font-bold text-indigo-600">₹<?= number_format($total_price) ?></span>
    </div>
    <a href="checkout.php" class="block w-full text-center bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition-colors <?= empty($cart_items) ? 'opacity-50 pointer-events-none' : '' ?>">
        Proceed to Checkout
    </a>
</div>

<script>
// ... JavaScript functions for update/delete remain the same ...
</script>

<?php include 'common/bottom.php'; ?>
