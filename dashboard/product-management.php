<?php
include('includes/header.php');
include('session.php');
include_once('db.php');

// Function to get all products
function getAllProducts() {
    global $conn;
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Create product
if (isset($_POST['create_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $light_requirement = $_POST['light_requirement'];
    $water_requirement = $_POST['water_requirement'];
    $max_growth = $_POST['max_growth'];

    $sql = "INSERT INTO products (name, price, description, light_requirement, water_requirement, max_growth) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssss", $name, $price, $description, $light_requirement, $water_requirement, $max_growth);
    if ($stmt->execute()) {
        echo "<script>alert('Product created successfully');</script>";
    } else {
        echo "<script>alert('Error creating product: " . $stmt->error . "');</script>";
    }
    $stmt->close();
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

    $sql = "UPDATE products SET name = ?, price = ?, description = ?, light_requirement = ?, water_requirement = ?, max_growth = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssssi", $name, $price, $description, $light_requirement, $water_requirement, $max_growth, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating product: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Delete product
if (isset($_POST['delete_product'])) {
    $id = $_POST['product_id'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Product deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting product: " . $stmt->error . "');</script>";
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
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Light Requirement</th>
                        <th>Water Requirement</th>
                        <th>Max Growth</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['light_requirement']); ?></td>
                        <td><?php echo htmlspecialchars($product['water_requirement']); ?></td>
                        <td><?php echo htmlspecialchars($product['max_growth']); ?></td>
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
        </div>
    </main>
</div>

<!-- Create Product Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <span class="close-create">&times;</span>
        <h2>Add New Product</h2>
        <form method="post" class="product-form">
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" name="price" placeholder="Price" step="0.01" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="text" name="light_requirement" placeholder="Light Requirement" required>
            <input type="text" name="water_requirement" placeholder="Water Requirement" required>
            <input type="text" name="max_growth" placeholder="Maximum Growth" required>
            <button type="submit" name="create_product">Add Product</button>
        </form>
    </div>
</div>

<!-- Update Product Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close-update">&times;</span>
        <h2>Update Product</h2>
        <form id="updateForm" method="post" class="product-form">
            <input type="hidden" id="update_product_id" name="product_id">
            <input type="text" id="update_name" name="name" placeholder="Product Name" required>
            <input type="number" id="update_price" name="price" placeholder="Price" step="0.01" required>
            <textarea id="update_description" name="description" placeholder="Description" required></textarea>
            <input type="text" id="update_light_requirement" name="light_requirement" placeholder="Light Requirement" required>
            <input type="text" id="update_water_requirement" name="water_requirement" placeholder="Water Requirement" required>
            <input type="text" id="update_max_growth" name="max_growth" placeholder="Maximum Growth" required>
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

<?php include('includes/footer.php'); ?>