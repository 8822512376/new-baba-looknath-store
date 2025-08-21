<?php
include 'common/config.php';
check_login(); // Make sure user is logged in

// --- AJAX HANDLER: This block runs in the background when "Place Order" is clicked ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An error occurred.'];
    $user_id = $_SESSION['user_id'];

    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    if (empty($_SESSION['cart'])) {
        $response['message'] = 'Your cart is empty.';
        echo json_encode($response);
        exit();
    }
    
    // Server-side recalculation of total price
    $total_price = 0;
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $sql_products = "SELECT id, price FROM products WHERE id IN ($product_ids)";
    $result_products = $conn->query($sql_products);
    $products = [];
    while($row = $result_products->fetch_assoc()) { $products[$row['id']] = $row; }
    foreach ($_SESSION['cart'] as $pid => $qty) { $total_price += $products[$pid]['price'] * $qty; }

    $order_status = ($payment_method === 'QR') ? 'Awaiting Payment' : 'Placed';
    
    $conn->begin_transaction();
    try {
        $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) VALUES (?, ?, ?, ?, ?)");
        $stmt_order->bind_param("idsss", $user_id, $total_price, $address, $payment_method, $order_status);
        if (!$stmt_order->execute()) { throw new Exception("Failed to create order."); }
        $order_id = $conn->insert_id;
        $stmt_order->close();

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $price = $products[$pid]['price'];
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')");
            $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
        }
        
        $conn->commit();
        unset($_SESSION['cart']);

        $response = ['success' => true, 'payment_method' => $payment_method];
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'A database error occurred.';
    }
    
    echo json_encode($response);
    exit();
}

// --- HTML DISPLAY LOGIC ---
if (empty($_SESSION['cart'])) {
    header('Location: cart.php'); exit();
}
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT name, phone, address FROM users WHERE id = $user_id";
$user = $conn->query($user_sql)->fetch_assoc();
$total_price = 0;
$product_ids = implode(',', array_keys($_SESSION['cart']));
$sql_price = "SELECT id, price FROM products WHERE id IN ($product_ids)";
$result_price = $conn->query($sql_price);
$products_prices = [];
while($row = $result_price->fetch_assoc()) { $products_prices[$row['id']] = $row; }
foreach ($_SESSION['cart'] as $pid => $qty) { $total_price += $products_prices[$pid]['price'] * $qty; }

include 'common/header.php';
?>

<div class="p-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Checkout</h1>

code
Code
download
content_copy
expand_less

<form class="space-y-4" id="checkout-form">
    <!-- Shipping Details -->
    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full p-3 mt-1 border rounded-md bg-gray-50" required>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Phone</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full p-3 mt-1 border rounded-md bg-gray-50" required>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Shipping Address</label>
            <textarea name="address" rows="3" class="w-full p-3 mt-1 border rounded-md" required><?= htmlspecialchars($user['address']) ?></textarea>
        </div>
    </div>
    
    <!-- Payment Method Selection -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Select Payment Method</h3>
        <div class="space-y-2">
            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-500 cursor-pointer">
                <input type="radio" name="payment_method" value="COD" class="h-4 w-4 text-indigo-600 payment-method-radio" checked>
                <span class="ml-3 font-medium">Cash on Delivery (COD)</span>
            </label>
            <label class="flex items-center p-3 border rounded-md has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-500 cursor-pointer">
                <input type="radio" name="payment_method" value="QR" class="h-4 w-4 text-indigo-600 payment-method-radio">
                <span class="ml-3 font-medium">Pay with QR Code (UPI)</span>
            </label>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- ===== THIS IS THE NEW QR CODE DISPLAY SECTION ===== -->
    <!-- ======================================================= -->
    <div id="qr-payment-details" class="hidden mt-4 space-y-4 p-4 border-2 border-dashed border-indigo-400 rounded-lg text-center">
        <p class="font-semibold text-gray-800">Scan to pay using any UPI app</p>
        
        <!-- This displays your QR code image -->
        <img src="assets/qr-code.png" alt="Scan to pay" class="mx-auto border-4 border-gray-200 rounded-lg w-48 h-48">
        
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-3 text-left text-sm rounded-r-lg">
            <p class="font-bold">Important Instructions:</p>
            <ol class="list-decimal list-inside mt-1">
                <li>Complete your payment.</li>
                <li>Click the "Place Order" button below.</li>
                <li>After the order is placed, send the payment screenshot to us on WhatsApp at <strong class="font-mono">8822512376</strong>.</li>
            </ol>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold mb-2">Order Summary</h3>
        <div class="flex justify-between font-bold text-lg"><span>Total</span><span>₹<?= number_format($total_price) ?></span></div>
    </div>
    
    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-semibold hover:bg-indigo-700">
        Place Order
    </button>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('.payment-method-radio');
    const qrDetailsDiv = document.getElementById('qr-payment-details');

    // Add event listener to each radio button
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'QR' && this.checked) {
                qrDetailsDiv.classList.remove('hidden'); // Show the QR section
            } else {
                qrDetailsDiv.classList.add('hidden'); // Hide the QR section
            }
        });
    });

    // Handle form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoader();

        fetch('checkout.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            hideLoader();
            if (data.success) {
                updateCartIcon(0); // Clear cart icon
                // Redirect to orders page with a specific message based on payment method
                if (data.payment_method === 'QR') {
                    window.location.href = 'order.php?success=qr';
                } else {
                    window.location.href = 'order.php?success=cod';
                }
            } else {
                showToast(data.message || 'Failed to place order.', false);
            }
        })
        .catch(error => {
            hideLoader();
            showToast('A network error occurred.', false);
            console.error('Fetch Error:', error);
        });
    });
});

// Helper function to update cart icon (should already exist)
function updateCartIcon(count) {
    const cartLink = document.querySelector('header a[href="cart.php"]');
    if (!cartLink) return;
    let bubble = cartLink.querySelector('span');
    if (!bubble && count > 0) {
        bubble = document.createElement('span');
        bubble.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
        cartLink.appendChild(bubble);
    }
    if (bubble) {
        if (count > 0) { bubble.textContent = count; } else { bubble.remove(); }
    }
}
</script>

<?php
include 'common/bottom.php';
$conn->close();
?>
