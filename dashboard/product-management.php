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
    $stmt->execute();
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
    $stmt->execute();
    $stmt->close();
}

// Delete product
if (isset($_POST['delete_product'])) {
    $id = $_POST['product_id'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$products = getAllProducts();
?>

<div class="dashboard-container">
    <?php include('includes/sidebar.php'); ?>
    <main>
        <div class="dashboard-content">
            <h1>Product Management</h1>
            
            <!-- Create Product Form -->
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

            <!-- Product List -->
            <h2>Product List</h2>
            <div class="product-list">
                <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>Price: $<?php echo $product['price']; ?></p>
                    <p><?php echo $product['description']; ?></p>
                    <button onclick="openUpdateModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">Update</button>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="delete_product" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<!-- Update Product Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
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
var modal = document.getElementById("updateModal");
var span = document.getElementsByClassName("close")[0];

function openUpdateModal(product) {
    document.getElementById("update_product_id").value = product.id;
    document.getElementById("update_name").value = product.name;
    document.getElementById("update_price").value = product.price;
    document.getElementById("update_description").value = product.description;
    document.getElementById("update_light_requirement").value = product.light_requirement;
    document.getElementById("update_water_requirement").value = product.water_requirement;
    document.getElementById("update_max_growth").value = product.max_growth;
    modal.style.display = "block";
}

span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php include('includes/footer.php'); ?>