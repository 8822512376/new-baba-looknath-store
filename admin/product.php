<?php
include 'common/header.php';

// --- This entire AJAX handling block at the top remains the same ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    // Add or Update Product
    if ($action === 'save_product') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $cat_id = intval($_POST['cat_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $image_path = $_POST['existing_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir_check = "../uploads/products/";
            if (!is_dir($upload_dir_check)) {
                echo json_encode(['success' => false, 'message' => 'Error: Upload directory does not exist.']);
                exit();
            }
            if (!is_writable($upload_dir_check)) {
                echo json_encode(['success' => false, 'message' => 'Error: Upload directory is not writable. Check permissions.']);
                exit();
            }
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $upload_dir_check . $image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                if ($id > 0 && !empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = "uploads/products/" . $image_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: Failed to move uploaded file.']);
                exit();
            }
        }
        
        if ($id > 0) {
            $sql = "UPDATE products SET cat_id=?, name=?, description=?, price=?, stock=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issdisi", $cat_id, $name, $description, $price, $stock, $image_path, $id);
        } else {
            $sql = "INSERT INTO products (cat_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issdis", $cat_id, $name, $description, $price, $stock, $image_path);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product saved successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    }
    // Delete Product
    elseif ($action === 'delete_product') {
        $id = intval($_POST['id']);
        $sql = "SELECT image FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if($row && !empty($row['image']) && file_exists('../' . $row['image'])){
            unlink('../' . $row['image']);
        }
        
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
        }
    }
    exit();
}
// --- End of AJAX block ---

// Fetch data for display
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC");
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$category_options = [];
while($cat = $categories->fetch_assoc()) {
    $category_options[] = $cat;
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Products</h1>
    <button onclick="openProductModal()" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add New Product
    </button>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Image</th>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Name</th>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Category</th>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Price</th>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Stock</th>
                <th class="p-3 text-left text-sm font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php // --- CORRECTED DISPLAY LOGIC --- ?>
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($prod = $products->fetch_assoc()): ?>
                    <tr class="border-b" id="prod-row-<?= $prod['id'] ?>">
                        <td class="p-3"><img src="../<?= $prod['image'] ?>" class="w-12 h-12 rounded-md object-cover"></td>
                        <td class="p-3 font-medium"><?= htmlspecialchars($prod['name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($prod['category_name']) ?></td>
                        <td class="p-3">₹<?= number_format($prod['price']) ?></td>
                        <td class="p-3"><?= $prod['stock'] ?></td>
                        <td class="p-3">
                            <button onclick='openProductModal(<?= json_encode($prod) ?>)' class="text-blue-500 hover:text-blue-700 mr-3"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteProduct(<?= $prod['id'] ?>)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <?php // This message will now appear if no products are found ?>
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">
                        No products have been added yet. Click "Add New Product" to get started.
                    </td>
                </tr>
            <?php endif; ?>
            <?php // --- END OF CORRECTION --- ?>
        </tbody>
    </table>
</div>

<!-- Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center hidden overflow-y-auto p-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <h2 id="modal-title" class="text-2xl font-bold mb-4">Add Product</h2>
        <form id="product-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_product">
            <input type="hidden" id="product-id" name="id">
            <input type="hidden" id="existing-image" name="existing_image">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Product Name</label>
                        <input type="text" id="product-name" name="name" class="w-full p-2 mt-1 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Category</label>
                        <select id="product-category" name="cat_id" class="w-full p-2 mt-1 border rounded-md" required>
                            <option value="">Select Category</option>
                            <?php foreach($category_options as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Price (₹)</label>
                        <input type="number" step="0.01" id="product-price" name="price" class="w-full p-2 mt-1 border rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Stock</label>
                        <input type="number" id="product-stock" name="stock" class="w-full p-2 mt-1 border rounded-md" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Description</label>
                    <textarea id="product-description" name="description" rows="3" class="w-full p-2 mt-1 border rounded-md"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Product Image</label>
                    <input type="file" name="image" class="w-full mt-1">
                    <img id="image-preview" src="" class="w-20 h-20 mt-2 object-cover hidden">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeProductModal()" class="bg-gray-300 py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- The JavaScript part of this file remains the same ---
function openProductModal(product = null) {
    const form = document.getElementById('product-form');
    form.reset();
    const preview = document.getElementById('image-preview');
    preview.classList.add('hidden');
    
    if (product) {
        document.getElementById('modal-title').innerText = 'Edit Product';
        document.getElementById('product-id').value = product.id;
        document.getElementById('product-name').value = product.name;
        document.getElementById('product-category').value = product.cat_id;
        document.getElementById('product-price').value = product.price;
        document.getElementById('product-stock').value = product.stock;
        document.getElementById('product-description').value = product.description;
        document.getElementById('existing-image').value = product.image;
        if (product.image) {
            preview.src = '../' + product.image;
            preview.classList.remove('hidden');
        }
    } else {
        document.getElementById('modal-title').innerText = 'Add Product';
    }
    document.getElementById('product-modal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('product-modal').classList.add('hidden');
}

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    fetch('product.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if(data.success) {
            closeProductModal();
            setTimeout(() => location.reload(), 1000);
        }
    }).catch(err => { 
        hideLoader(); 
        showToast('A network error occurred.', false);
        console.error('Fetch Error:', err);
    });
});

function deleteProduct(id) {
    if(!confirm('Are you sure you want to delete this product?')) return;
    showLoader();
    const formData = new FormData();
    formData.append('action', 'delete_product');
    formData.append('id', id);
    fetch('product.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if(data.success) {
            document.getElementById('prod-row-' + id).remove();
        }
    }).catch(err => { 
        hideLoader(); 
        showToast('An error occurred.', false);
        console.error('Fetch Error:', err);
    });
}
</script>

<?php include 'common/bottom.php'; ?>
