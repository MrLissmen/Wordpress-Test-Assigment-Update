<?php
defined('ABSPATH') || exit;

get_header();
?>

<div class="custom-checkout-page">
    <h1>Placing an order</h1>
    
    <!-- Показ товарів з корзини -->
    <?php if ( WC()->cart->is_empty() ) : ?>
        <p>Your cart is empty.</p>
    <?php else : ?>
        <ul class="checkout-cart">
            <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : 
                $product = $cart_item['data'];
                $product_price = wc_price($product->get_price()); // Отримуємо ціну товару у форматі WooCommerce
                $total_price = wc_price($cart_item['line_total']); // Загальна ціна за кількість одиниць товару
            ?>
                <li class="checkout-item">
                    <div 
                    class="checkout-image"><?php echo $product->get_image(); ?>
                    </div>
                    <div class="checkout-details" >
                        <span class="product-name"><span class="bold"><?php echo $product->get_name(); ?></span></span><br>
                        <span class="product-quantity">Amount: <?php echo $cart_item['quantity']; ?> pcs.</span><br>
                        <span class="product-price">Price per unit: <?php echo $product_price; ?></span><br>
                        <span class="product-total-price">Total price: <?php echo $total_price; ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Загальна сума корзини -->
        <div class="cart-total">
            <p>Total order amount: <span class="bold"><?php echo WC()->cart->get_cart_total(); ?></span></p>
        </div>
    <?php endif; ?>

    <!-- Форма оформлення замовлення -->
    <form id="custom-checkout-form" method="POST">
        <div><label for="name">First Name:</label>
        <input class="form-field" type="text" id="name" name="name" required></div>
        
        <div><label for="address">Address:</label>
        <input class="form-field" type="text" id="address" name="address" required></div>
        
        <div><label for="email">Email:</label>
        <input class="form-field" type="email" id="email" name="email" required></div>
        
        <div><label for="payment_method">Payment Method:</label>
        <select class="form-field" id="payment_method" name="payment_method" required>
            <?php
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                foreach ( $available_gateways as $gateway ) {
                    echo '<option value="' . esc_attr( $gateway->id ) . '">' . esc_html( $gateway->get_title() ) . '</option>';
                }
            ?>
        </select></div>
        
        <button class="form-button" type="submit">Confirm order</button>
    </form>

    <div id="checkout-message"></div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#custom-checkout-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                type: "POST",
                data: formData + '&action=process_custom_checkout',
                success: function(response) {
                    if(response.success) {
                        window.location.href = response.redirect_url; 
                    } else {
                        $('#checkout-message').html(response.message);
                    }
                }
            });
        });
    });
</script>

<?php

get_footer();
?>