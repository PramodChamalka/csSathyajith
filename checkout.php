
include('session.php');
include_once('dashboard/db.php');

// Function to get cart items for the current user
function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to create an order
function createOrder($user_id, $total_amount) {
    global $conn;
    $sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $total_amount);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    return false;
}

// Function to add order items
function addOrderItems($order_id, $cart_items) {
    global $conn;
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($cart_items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
}

// Function to update product stock
function updateProductStock($product_id, $quantity) {
    global $conn;
    $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $product_id);
    return $stmt->execute();
}

// Function to clear user's cart
function clearCart($user_id) {
    global $conn;
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

$cart_items = getCartItems($_SESSION['user_id']);
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $conn->begin_transaction();
    try {
        $order_id = createOrder($_SESSION['user_id'], $total);
        if ($order_id) {
            addOrderItems($order_id, $cart_items);
            foreach ($cart_items as $item) {
                updateProductStock($item['product_id'], $item['quantity']);
            }
            clearCart($_SESSION['user_id']);
            $conn->commit();
            echo "<script>alert('Order placed successfully!'); window.location.href = 'order_confirmation.php?order_id=" . $order_id . "';</script>";
            exit;
        } else {
            throw new Exception("Failed to create order");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error placing order: " . $e->getMessage() . "');</script>";
    }
}
?>

<div class="container">
    <h1>Checkout</h1>
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="checkout-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php $total += $item['price'] * $item['quantity']; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                    <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <form method="post" class="order-form">
            <h2>Shipping Information</h2>
            <!-- Add shipping information fields here -->
            <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
        </form>
    <?php endif; ?>
</div>

<style>
    .checkout-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .checkout-table th, .checkout-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .checkout-table th {
        background-color: #f2f2f2;
    }
    .order-form {
        max-width: 500px;
        margin: 0 auto;
    }
    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
</style>

<?php include('includes/footer.php'); ?>