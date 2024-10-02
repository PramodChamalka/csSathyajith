<?php
include('userSession.php');
include_once('dashboard/db.php');

// Fetch products from the database with all fields
$sql = "SELECT id, name, price, image_path, stock, description, light_requirement, water_requirement, max_growth FROM products";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Get cart count
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    $cart_count = $cart_result->fetch_assoc()['count'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Showcase - Plantex</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .cart-icon {
            position: relative;
            cursor: pointer;
        }
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .product__stock {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .product__description,
        .product__details {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <nav class="nav container">
            <a href="#" class="nav__logo">
                <i class="ri-leaf-line nav__logo-icon"></i> Plantex
            </a>

            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="#home" class="nav__link active-link">Home</a>
                    </li>
                    <li class="nav__item">
                        <a href="#products" class="nav__link">Products</a>
                    </li>
                    <li class="nav__item">
                        <a href="#faqs" class="nav__link">FAQs</a>
                    </li>
                    <li class="nav__item">
                        <a href="#contact" class="nav__link">Contact Us</a>
                    </li>
                </ul>

                <div class="nav__close" id="nav-close">
                    <i class="ri-close-line"></i>
                </div>
            </div>

            <div class="nav__btns">
                <div class="cart-icon" id="cart-icon">
                    <i class="ri-shopping-cart-line"></i>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </div>
                <i class="ri-moon-line change-theme" id="theme-button"></i>
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-menu-line"></i>
                </div>
            </div>
        </nav>
    </header>

    <main class="main">
        <section class="product section container" id="products">
            <h2 class="section__title-center">
                Check out our <br> products
            </h2>

            <p class="product__description">
                Here are some selected plants from our showroom, all are in excellent 
                shape and has a long life span. Buy and enjoy best quality.
            </p>

            <div class="product__container grid">
                <?php foreach ($products as $product): ?>
                <article class="product__card" data-product-id="<?php echo $product['id']; ?>">
                    <div class="product__circle"></div>

                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product__img">

                    <h3 class="product__title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <span class="product__price">$<?php echo number_format($product['price'], 2); ?></span>
                    <p class="product__stock">In stock: <?php echo $product['stock']; ?></p>
                    <p class="product__description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="product__details">Light: <?php echo htmlspecialchars($product['light_requirement']); ?></p>
                    <p class="product__details">Water: <?php echo htmlspecialchars($product['water_requirement']); ?></p>
                    <p class="product__details">Max Growth: <?php echo htmlspecialchars($product['max_growth']); ?></p>

                    <button class="button--flex product__button add-to-cart-btn" <?php echo $product['stock'] > 0 ? '' : 'disabled'; ?>>
                        <i class="ri-shopping-bag-line"></i>
                    </button>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="footer section">
        <!-- Footer content here -->
    </footer>
    
    <a href="#" class="scrollup" id="scroll-up"> 
        <i class="ri-arrow-up-fill scrollup__icon"></i>
    </a>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('.add-to-cart-btn').on('click', function() {
                var $btn = $(this);
                var productId = $btn.closest('.product__card').data('product-id');
                var $stockElement = $btn.siblings('.product__stock');
                var currentStock = parseInt($stockElement.text().split(': ')[1]);

                if (currentStock > 0) {
                    $.ajax({
                        url: 'add_to_cart.php',
                        method: 'POST',
                        data: { product_id: productId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                // Update cart count
                                var $cartCount = $('.cart-count');
                                $cartCount.text(parseInt($cartCount.text()) + 1);
                                // Update stock
                                $stockElement.text('In stock: ' + (currentStock - 1));
                                if (currentStock - 1 === 0) {
                                    $btn.prop('disabled', true);
                                }
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error adding product to cart. Please try again.');
                        }
                    });
                } else {
                    alert('This product is out of stock.');
                }
            });

            $('#cart-icon').on('click', function() {
                window.location.href = 'cart.php';
            });
        });
    </script>
</body>
</html>