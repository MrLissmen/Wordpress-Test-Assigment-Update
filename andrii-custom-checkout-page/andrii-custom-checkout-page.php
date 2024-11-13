<?php
/**
 * Plugin Name: Andrii Custom Checkout Page
 * Description: A custom page for the Woocommerce plugin was created to complete a test task
 * Version: 1.1
 * Author: Andrii
 */

 if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Заміна оригінального checkout на кастом
    add_filter( 'template_include', 'custom_checkout_page_template' );

    function custom_checkout_page_template( $template ) {
        // Перевірка, що ця сторінка — це checkout WooCommerce
        if ( is_checkout() && !is_order_received_page() ) {
            //заміняємо на кастом
            return plugin_dir_path( __FILE__ ) . 'templates/andrii-custom-checkout-template.php';
        }
        return $template;
    }

    // Кастомні стилі checkout сторінки
    function custom_checkout_styles() {
        if ( is_checkout() ) {  // Підключення стилів тільки на кастомній сторінці checkout
            wp_enqueue_style( 'custom-checkout-styles', plugin_dir_url( __FILE__ ) . '/css/andrii-custom-checkout-styles.css' );
        }
    }
    add_action( 'wp_enqueue_scripts', 'custom_checkout_styles' );

    // JavaScript для функціоналу збереження даних
    function my_custom_checkout_scripts() {
        if ( is_checkout() ) {
            
            ?>
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
        }
    }
    add_action('wp_footer', 'my_custom_checkout_scripts');

    // AJAX для оформлення замовлення
    function process_custom_checkout() {
        $name = sanitize_text_field($_POST['name']);
        $address = sanitize_text_field($_POST['address']);
        $email = sanitize_email($_POST['email']);
        $payment_method = sanitize_text_field($_POST['payment_method']);

        if ( empty($name) || empty($address) || empty($email) || empty($payment_method) ) {
            wp_send_json(array('success' => false, 'message' => 'Please fill in all required fields.'));
        }

        $order = wc_create_order();
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $order->add_product( wc_get_product($product_id), $quantity );
        }

        $order->set_address(array(
            'first_name' => $name,
            'address_1' => $address,
            'email' => $email,
        ), 'billing');

        $order->set_payment_method($payment_method);
        $order->calculate_totals();
        $order->save();

        WC()->cart->empty_cart();
        $redirect_url = $order->get_checkout_order_received_url();

        wp_send_json(array('success' => true, 'redirect_url' => $redirect_url));
    }
    add_action('wp_ajax_process_custom_checkout', 'process_custom_checkout');
    add_action('wp_ajax_nopriv_process_custom_checkout', 'process_custom_checkout');
}



