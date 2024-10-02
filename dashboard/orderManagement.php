<?php
include('includes/header.php');
include('session.php');
include_once('db.php');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to get all orders
function getAllOrders() {
    global $conn;
    $sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get order items
function getOrderItems($order_id) {
    global $conn;
    $sql = "SELECT oi.product_id, oi.quantity, oi.price, p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to update order status
function updateOrderStatus($order_id, $status) {
    global $conn;
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    if (updateOrderStatus($order_id, $new_status)) {
        echo "<script>alert('Order status updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating order status');</script>";
    }
}

$orders = getAllOrders();
?>

<div class="container">
    <h1>Order Management</h1>
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo ucfirst($order['status']); ?></td>
                    <td><?php echo $order['created_at']; ?></td>
                    <td>
                        <button onclick="showOrderDetails(<?php echo $order['id']; ?>)">View Details</button>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="new_status">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status">Update Status</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="orderDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Order Details</h2>
        <div id="orderDetailsContent"></div>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    var modal = document.getElementById("orderDetailsModal");
    var span = document.getElementsByClassName("close")[0];
    var content = document.getElementById("orderDetailsContent");

    // AJAX request to get order details
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            content.innerHTML = this.responseText;
            modal.style.display = "block";
        }
    };
    xhr.open("GET", "get_order_details.php?order_id=" + orderId, true);
    xhr.send();

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }}
}
</script>

<style>
.order-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
.order-table th, .order-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.order-table th {
    background-color: #f2f2f2;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<?php include('includes/footer.php'); ?>