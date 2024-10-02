<?php
include('userSession.php');
include_once('dashboard/db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to add items to cart'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Check if product exists and has stock
$check_product = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$check_product->bind_param("i", $product_id);
$check_product->execute();
$result = $check_product->get_result();
$product = $result->fetch_assoc();

if (!$product || $product['stock'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product is out of stock'
    ]);
    exit;
}

// Check if product is already in cart
$check_cart = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check_cart->bind_param("ii", $user_id, $product_id);
$check_cart->execute();
$cart_result = $check_cart->get_result();
$cart_item = $cart_result->fetch_assoc();

if ($cart_item) {
    // Update quantity
    $new_quantity = $cart_item['quantity'] + 1;
    $update_cart = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update_cart->bind_param("ii", $new_quantity, $cart_item['id']);
    
    if ($update_cart->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating cart'
        ]);
    }
} else {
    // Add new item to cart
    $add_to_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $add_to_cart->bind_param("ii", $user_id, $product_id);
    
    if ($add_to_cart->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding item to cart'
        ]);
    }
}

// Update product stock
$new_stock = $product['stock'] - 1;
$update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
$update_stock->bind_param("ii", $new_stock, $product_id);
$update_stock->execute();
?>