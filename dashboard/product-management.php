<?php
include('includes/header.php');
include('session.php');
include_once('db.php');

// Function to get all products
function getAllProducts() {
    global $conn;
    $sql = "SELECT * FROM products where is_active = TRUE";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to handle file upload
function uploadFile($file) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }

    // Check file size
    if ($file["size"] > 50000000) {
        echo "<script>alert('Sorry, your file is too large.');</script>";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        $uploadOk = 0;
    }

    // if everything is ok, try to upload file
    if ($uploadOk == 1) {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            return false;
        }
    }
    return false;
}

// Create product
if (isset($_POST['create_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $light_requirement = $_POST['light_requirement'];
    $water_requirement = $_POST['water_requirement'];
    $max_growth = $_POST['max_growth'];
    $stock = $_POST['stock'];

    $image_path = uploadFile($_FILES["image"]);

    if ($image_path) {
        $sql = "INSERT INTO products (name, price, description, light_requirement, water_requirement, max_growth, image_path, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssssi", $name, $price, $description, $light_requirement, $water_requirement, $max_growth, $image_path, $stock);
        if ($stmt->execute()) {
            echo "<script>alert('Product created successfully');</script>";
        } else {
            echo "<script>alert('Error creating product: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $light_requirement = $_POST['light_requirement'];
    $water_requirement = $_POST['water_requirement'];
    $max_growth = $_POST['max_growth'];
    $stock = $_POST['stock'];

    $image_path = null;
    if ($_FILES["image"]["size"] > 0) {
        $image_path = uploadFile($_FILES["image"]);
    }

    if ($image_path) {
        $sql = "UPDATE products SET name = ?, price = ?, description = ?, light_requirement = ?, water_requirement = ?, max_growth = ?, image_path = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssssi", $name, $price, $description, $light_requirement, $water_requirement, $max_growth, $image_path, $stock, $id);
    } else {
        $sql = "UPDATE products SET name = ?, price = ?, description = ?, light_requirement = ?, water_requirement = ?, max_growth = ?, stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssii", $name, $price, $description, $light_requirement, $water_requirement, $max_growth, $stock, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating product: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Soft delete product
if (isset($_POST['delete_product'])) {
    $id = $_POST['product_id'];
    $sql = "UPDATE products SET is_active = FALSE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Product deactivated successfully');</script>";
    } else {
        echo "<script>alert('Error deactivating product: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

$products = getAllProducts();
?>

<div class="dashboard-container">
    <?php include('includes/sidebar.php'); ?>
    <main>
        <div class="dashboard-content">
            <h1>Product Management</h1>
            
            <!-- Button to Open Create Product Modal -->
            <button class="open-create-modal-btn" onclick="openCreateModal()">Add New Product</button>

            <!-- Product List -->
            <h2>Product List</h2>
            <table class="product-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Light Requirement</th>
            <th>Water Requirement</th>
            <th>Max Growth</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="100"></td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td>$<?php echo htmlspecialchars($product['price']); ?></td>
            <td><?php echo htmlspecialchars($product['description']); ?></td>
            <td><?php echo htmlspecialchars($product['light_requirement']); ?></td>
            <td><?php echo htmlspecialchars($product['water_requirement']); ?></td>
            <td><?php echo htmlspecialchars($product['max_growth']); ?></td>
            <td><?php echo htmlspecialchars($product['stock']); ?></td>
            <td>
                <button class="update-btn" onclick='openUpdateModal(<?php echo json_encode($product); ?>)'>Update</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button class="delete-btn" type="submit" name="delete_product" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Update the create and update modals to include the stock field -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <span class="close-create">&times;</span>
        <h2>Add New Product</h2>
        <form method="post" class="product-form" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" name="price" placeholder="Price" step="0.01" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="text" name="light_requirement" placeholder="Light Requirement" required>
            <input type="text" name="water_requirement" placeholder="Water Requirement" required>
            <input type="text" name="max_growth" placeholder="Maximum Growth" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" name="create_product">Add Product</button>
        </form>
    </div>
</div>

<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close-update">&times;</span>
        <h2>Update Product</h2>
        <form id="updateForm" method="post" class="product-form" enctype="multipart/form-data">
            <input type="hidden" id="update_product_id" name="product_id">
            <input type="text" id="update_name" name="name" placeholder="Product Name" required>
            <input type="number" id="update_price" name="price" placeholder="Price" step="0.01" required>
            <textarea id="update_description" name="description" placeholder="Description" required></textarea>
            <input type="text" id="update_light_requirement" name="light_requirement" placeholder="Light Requirement" required>
            <input type="text" id="update_water_requirement" name="water_requirement" placeholder="Water Requirement" required>
            <input type="text" id="update_max_growth" name="max_growth" placeholder="Maximum Growth" required>
            <input type="number" id="update_stock" name="stock" placeholder="Stock" required>
            <input type="file" name="image" accept="image/*">
            <p>Leave image field empty if you don't want to change the image.</p>
            <button type="submit" name="update_product">Update Product</button>
        </form>
    </div>
</div>

<script>
var createModal = document.getElementById("createModal");
var updateModal = document.getElementById("updateModal");
var closeCreate = document.getElementsByClassName("close-create")[0];
var closeUpdate = document.getElementsByClassName("close-update")[0];

function openCreateModal() {
    createModal.style.display = "block";
}

function openUpdateModal(product) {
    document.getElementById("update_product_id").value = product.id;
    document.getElementById("update_name").value = product.name;
    document.getElementById("update_price").value = product.price;
    document.getElementById("update_description").value = product.description;
    document.getElementById("update_light_requirement").value = product.light_requirement;
    document.getElementById("update_water_requirement").value = product.water_requirement;
    document.getElementById("update_max_growth").value = product.max_growth;
    updateModal.style.display = "block";
}

closeCreate.onclick = function() {
    createModal.style.display = "none";
}

closeUpdate.onclick = function() {
    updateModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == createModal) {
        createModal.style.display = "none";
    }
    if (event.target == updateModal) {
        updateModal.style.display = "none";
    }
}
</script>

<?php 

// include('includes/footer.php');

?>