<script>
let stock = <?= $product['stock'] ?>;
function updateQty(change) {
    const qtyInput = document.getElementById('quantity');
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;
    if (newQty > 0 && newQty <= stock) {
        qtyInput.value = newQty;
    }
}

// This new function handles creating and updating the cart count bubble perfectly
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

// The new and improved addToCart function for this page
function addToCart(productId) {
    showLoader();
    const quantity = document.getElementById('quantity').value;
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('cart.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if (data.success) {
            updateCartIcon(data.cart_count);
        }
    }).catch(error => {
        hideLoader();
        showToast('An error occurred.', false);
    });
}
</script>
