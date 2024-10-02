<?php

include('dashboard/session.php');
include_once('dashboard/db.php');

// Function to get cart items for the current user
function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_path, p.stock
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to update cart item quantity
function updateCartItemQuantity($cart_id, $quantity) {
    global $conn;
    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $cart_id);
    return $stmt->execute();
}

// Function to remove item from cart
function removeCartItem($cart_id) {
    global $conn;
    $sql = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    return $stmt->execute();
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_id = $_POST['cart_id'];
        $quantity = $_POST['quantity'];
        if (updateCartItemQuantity($cart_id, $quantity)) {
            echo "<script>alert('Cart updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating cart');</script>";
        }
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = $_POST['cart_id'];
        if (removeCartItem($cart_id)) {
            echo "<script>alert('Item removed from cart');</script>";
        } else {
            echo "<script>alert('Error removing item from cart');</script>";
        }
    }
}

$cart_items = getCartItems($_SESSION['user_id']);
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .cart-table th {
            background-color: #f2f2f2;
        }
        .cart-image {
            max-width: 100px;
            height: auto;
        }
        .quantity-input {
            width: 50px;
        }
        .update-btn, .remove-btn {
            padding: 5px 10px;
            margin: 2px;
        }
        .checkout-btn {
            display: block;
            width: 200px;
            margin: 20px 0;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Shopping Cart</h1>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-image"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="post" class="update-form">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="quantity-input">
                                    <button type="submit" name="update_quantity" class="update-btn">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form method="post" class="remove-form">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php $total += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                        <td colspan="2"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        <?php endif; ?>
    </div>

    <script>
        // You can add JavaScript here for dynamic updates if needed
    </script>
</body>
</html>

