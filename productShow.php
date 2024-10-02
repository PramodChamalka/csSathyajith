<?php
include('userSession.php');
include_once('dashboard/db.php');

// Fetch products from the database
$sql = "SELECT id, name, price, image_path, stock, description, light_requirement, water_requirement, max_growth FROM products  WHERE is_active = TRUE";
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
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
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

                 
                    <img src="<?php echo htmlspecialchars('dashboard/' . $product['image_path']); ?>" alt="" class="product__img">

                    <h3 class="product__title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <span class="product__price">$<?php echo number_format($product['price'], 2); ?></span>

                    <button class="button--flex product__button add-to-cart-btn" <?php echo $product['stock'] > 0 ? '' : 'disabled'; ?>>
                        <i class="ri-shopping-bag-line"></i>
                    </button>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="footer section">
        <div class="footer__container container grid">
            <div class="footer__content">
                <a href="#" class="footer__logo">
                    <i class="ri-leaf-line footer__logo-icon"></i> Plantex
                </a>

                <h3 class="footer__title">
                    Subscribe to our newsletter <br> to stay updated
                </h3>

                <div class="footer__subscribe">
                    <input type="email" placeholder="Enter your email" class="footer__input">

                    <button class="button button--flex footer__button">
                        Subscribe
                        <i class="ri-arrow-right-up-line button__icon"></i>
                    </button>
                </div>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">Our Address</h3>

                <ul class="footer__data">
                    <li class="footer__information">1234 - Botanical Street</li>
                    <li class="footer__information">Green City - 43210</li>
                    <li class="footer__information">123-456-789</li>
                </ul>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">Contact Us</h3>

                <ul class="footer__data">
                    <li class="footer__information">+999 888 777</li>
                    
                    <div class="footer__social">
                        <a href="https://www.facebook.com/" class="footer__social-link">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="https://www.instagram.com/" class="footer__social-link">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="https://twitter.com/" class="footer__social-link">
                            <i class="ri-twitter-fill"></i>
                        </a>
                    </div>
                </ul>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">
                    We accept all credit cards
                </h3>

                <div class="footer__cards">
                    <img src="assets/img/card1.png" alt="" class="footer__card">
                    <img src="assets/img/card2.png" alt="" class="footer__card">
                    <img src="assets/img/card3.png" alt="" class="footer__card">
                    <img src="assets/img/card4.png" alt="" class="footer__card">
                </div>
            </div>
        </div>

        <p class="footer__copy">&#169; Plantex. All rights reserved</p>
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
                            var currentCount = parseInt($cartCount.text() || '0');
                            $cartCount.text(currentCount + 1);
                            if (currentCount === 0) {
                                $cartCount.show();
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error adding product to cart. Please try again.');
                    }
                });
            });

            $('#cart-icon').on('click', function() {
                window.location.href = 'cart.php';
            });
        });
    </script>
</body>
</html>