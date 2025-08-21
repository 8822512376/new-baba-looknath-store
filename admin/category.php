<?php
include 'common/header.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    // Add or Update Category
    if ($action === 'save_category') {
        $name = $conn->real_escape_string($_POST['name']);
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $image_path = $_POST['existing_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // CORRECTED PATH: Added ../ to go up one directory
            $target_dir = "../uploads/categories/"; 
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old image if updating
                if ($id > 0 && !empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                // Store the path without ../ for database consistency
                $image_path = "uploads/categories/" . $image_name;
            }
        }
        
        if ($id > 0) { // Update
            $sql = "UPDATE categories SET name = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $image_path, $id);
        } else { // Insert
            $sql = "INSERT INTO categories (name, image) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $image_path);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category saved successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save category.']);
        }
    }
    // Delete Category
    elseif ($action === 'delete_category') {
        $id = intval($_POST['id']);
        $sql = "SELECT image FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($row = $result->fetch_assoc()){
            if(!empty($row['image']) && file_exists('../' . $row['image'])){
                unlink('../' . $row['image']);
            }
        }
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
        }
    }
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!-- The rest of the HTML and JavaScript in this file remains exactly the same -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Categories</h1>
    <button onclick="openCategoryModal()" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add New Category
    </button>
</div>

<!-- Category Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Image</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Name</th>
                <th class="p-4 text-left text-sm font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody id="category-table-body">
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr class="border-b" id="cat-row-<?= $cat['id'] ?>">
                    <!-- CORRECTED IMAGE SRC PATH -->
                    <td class="p-4"><img src="../<?= $cat['image'] ?>" class="w-12 h-12 rounded-md object-cover"></td>
                    <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($cat['name']) ?></td>
                    <td class="p-4">
                        <button onclick='openCategoryModal(<?= json_encode($cat) ?>)' class="text-blue-500 hover:text-blue-700 mr-3"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Category Modal -->
<div id="category-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h2 id="modal-title" class="text-2xl font-bold mb-4">Add Category</h2>
        <form id="category-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_category">
            <input type="hidden" id="category-id" name="id">
            <input type="hidden" id="existing-image" name="existing_image">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Category Name</label>
                    <input type="text" id="category-name" name="name" class="w-full p-2 mt-1 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Category Image</label>
                    <input type="file" name="image" class="w-full mt-1">
                    <img id="image-preview" src="" class="w-20 h-20 mt-2 object-cover hidden">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeCategoryModal()" class="bg-gray-300 py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCategoryModal(category = null) {
    const form = document.getElementById('category-form');
    form.reset();
    const preview = document.getElementById('image-preview');
    preview.classList.add('hidden');
    
    if (category) {
        document.getElementById('modal-title').innerText = 'Edit Category';
        document.getElementById('category-id').value = category.id;
        document.getElementById('category-name').value = category.name;
        document.getElementById('existing-image').value = category.image;
        if (category.image) {
            // CORRECTED IMAGE PREVIEW PATH
            preview.src = '../' + category.image;
            preview.classList.remove('hidden');
        }
    } else {
        document.getElementById('modal-title').innerText = 'Add Category';
    }
    document.getElementById('category-modal').classList.remove('hidden');
}
// ... the rest of the javascript is the same ...
function closeCategoryModal() {
    document.getElementById('category-modal').classList.add('hidden');
}

document.getElementById('category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    const formData = new FormData(this);
    
    fetch('category.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if(data.success) {
            closeCategoryModal();
            setTimeout(() => location.reload(), 1000);
        }
    }).catch(err => { hideLoader(); showToast('An error occurred.', false); });
});

function deleteCategory(id) {
    if(!confirm('Are you sure you want to delete this category? All related products will also be removed.')) return;
    
    showLoader();
    const formData = new FormData();
    formData.append('action', 'delete_category');
    formData.append('id', id);

    fetch('category.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        hideLoader();
        showToast(data.message, data.success);
        if(data.success) {
            document.getElementById('cat-row-' + id).remove();
        }
    }).catch(err => { hideLoader(); showToast('An error occurred.', false); });
}
</script>

<?php include 'common/bottom.php'; ?>
