<?php
$orderID = (!empty($_REQUEST['post'])) ? sanitize_text_field($_REQUEST['post']) : '';

$orderSellerID = '';

if (! empty($orderID)) {
    $order              = wc_get_order($orderID);
    $orderSellerID      = $order->get_meta('ap4l_order_id');
    $ap4l_seller        = $order->get_meta('ap4l_seller');
    $ap4l_shipping_info = $order->get_meta('ap4l_shipping_info');
}
?>
<div class="woocommerce_options_panel_ap4l ap4lOrdersShipping">
    <p class="form-field required" >
        <label for="track_number">Track Number:</label>
        <input type="text" id="track_number" name="track_number" value="" />
    </p>
    <p class="form-field required">
        <label for="carrier_code">Carrier Code:</label>
        <select name="carrier_code" id="carrier_code">
            <option value="">--Select--</option>
            <option value="FedEx">FedEx</option>
            <option value="UPS">UPS</option>
            <option value="USPS">USPS</option>
            <option value="Other">Other</option>
        </select>
    </p>
    <p class="form-field otherChoice" style="display: none">
        <label for="shipping_service">Other Shipping Service:</label>
        <input type="text" id="shipping_service" name="shipping_service" value=""/>
    </p>
    <p class="form-field">
        <label for="comment">Comment:</label>
        <input type="text" id="comment" name="comment" value=""/>
    </p>
    <p class="form-field">
        <input type="hidden" name="wp_order_id" value="<?php echo esc_attr($orderID); ?>"/>
    <p>
    <p class="form-field">
        <input type="hidden" name="seller_order_id" value="<?php echo esc_attr($orderSellerID); ?>"/>
    <p>
    <p class="form-field">
        <input type="hidden" name="ap4l_seller" value="<?php echo esc_attr($ap4l_seller); ?>"/>
    <p>
    <p>
        <a class="ap4lAddShipping submit button-primary">Add Shipping Method</a>
        <img class="loaderImage" src="<?php echo esc_url(AP4L_URL . 'assets/images/loader.gif'); ?>"/>
    </p>
    <p class="responceMsg"></p>
    <?php
    if (! empty($ap4l_shipping_info)) {
        $ap4l_shipping_info = json_decode($ap4l_shipping_info, true);
        ?>
        <div class="shippingInfo">
            <?php
            foreach ($ap4l_shipping_info as $key => $value) {
                ?>
                <hr>
                <div class="singleShipping">
                    <p><strong>Carrier Name: </strong><?php echo wp_kses($value['carrier_name'], AP4L_ALLOWED_HTML); ?></p>
                    <p><strong>Track Number: </strong><?php echo wp_kses($value['track_number'], AP4L_ALLOWED_HTML); ?></p>
                    <p><strong>Comment: </strong><?php echo wp_kses($value['shipment_comment'], AP4L_ALLOWED_HTML); ?></p>
                </div>

            <?php } ?>
        </div>
    <?php } ?>
</div>