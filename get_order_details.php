<?php
include('dashboard/session.php');
include_once('dashboard/db.php');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access";
    exit();
}

// Function to get order details
function getOrderDetails($order_id) {
    global $conn;
    $sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
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

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $order = getOrderDetails($order_id);
    $order_items = getOrderItems($order_id);

    if ($order) {
        ?>
        <h3>Order #<?php echo $order['id']; ?></h3>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
        <p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>

        <h4>Order Items</h4>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "Order not found";
    }
} else {
    echo "Invalid request";
}
?>