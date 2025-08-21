<?php
// STEP 1: Include the robust configuration file. This connects to the DB.
include 'common/config.php';

// STEP 2: Fetch data from the database.
// Fetch categories
$categories_sql = "SELECT * FROM categories ORDER BY name ASC LIMIT 10";
$categories_result = $conn->query($categories_sql);

// Fetch featured products
$products_sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$products_result = $conn->query($products_sql);

// STEP 3: Include the HTML header.
include 'common/header.php';
?>

<div class="p-4">
    <!-- Search Bar -->
    <div class="relative mb-6">
        <input type="text" placeholder="Search for products..." class="w-full pl-10 pr-4 py-3 bg-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>

    <!-- Categories Section -->
    <section class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-3">Categories</h2>
        <div class="flex space-x-4 overflow-x-auto no-scrollbar pb-2">
            <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                <?php while($cat = $categories_result->fetch_assoc()): ?>
                <a href="product.php?cat_id=<?= $cat['id'] ?>" class="flex-shrink-0 text-center">
                    <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                        <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-full h-full object-cover">
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-700"><?= htmlspecialchars($cat['name']) ?></p>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">No categories found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section>
        <h2 class="text-xl font-bold text-gray-800 mb-3">Featured Products</h2>
        <div class="grid grid-cols-2 gap-4">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php while($prod = $products_result->fetch_assoc()): ?>
                <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                    <a href="product_detail.php?id=<?= $prod['id'] ?>">
                        <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-40 object-cover">
                    </a>
                    <div class="p-3">
                        <h3 class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($prod['name']) ?></h3>
                        <p class="text-indigo-600 font-bold mt-1">₹<?= number_format($prod['price']) ?></p>
                        <button onclick="addToCart(<?= $prod['id'] ?>)" class="w-full mt-2 bg-indigo-500 text-white text-sm py-2 rounded-md hover:bg-indigo-600 transition-colors">
                            <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 col-span-2">No featured products found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
// --- This is the fully working Add to Cart Javascript ---
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
        if (count > 0) {
            bubble.textContent = count;
        } else {
            bubble.remove();
        }
    }
}

function addToCart(productId) {
    showLoader();
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('cart.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if (data.success) {
            updateCartIcon(data.cart_count);
        }
    })
    .catch(error => {
        hideLoader();
        showToast('An error occurred.', false);
        console.error('Error:', error);
    });
}
</script>

<?php
// STEP 4: Include the footer and close the database connection.
include 'common/bottom.php';
$conn->close();
?>
