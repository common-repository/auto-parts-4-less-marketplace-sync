<?php
/*
 * Custom Payment Method AP4L
 */
if (! class_exists('Ap4lPaymentMethod')) {
    class Ap4lPaymentMethod extends WC_Payment_Gateway
    {
        /**
         * ===========================
         * Constructor for the gateway.
         * ===========================
         */
        public function __construct()
        {
            $this->setup_properties();
            $this->init_form_fields();
            $this->init_settings();
            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->instructions = $this->get_option('instructions');
            // Actions.
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
        }
        /**
         * =========================================
         * Setup general properties for the gateway.
         * =========================================
         */
        protected function setup_properties()
        {
            $this->id                 = 'ap4l_payment';
            $this->icon               = apply_filters('woocommerce_cod_icon', '');
            $this->method_title       = __('AP4L Payment', 'woocommerce');
            $this->method_description = __('Transaction completed in AP4L.', 'woocommerce');
            $this->has_fields         = false;
        }
        /**
         * =========================================
         * Initialise Gateway Settings Form Fields.
         * =========================================
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled'      => array(
                    'title'       => __('Enable/Disable', 'woocommerce'),
                    'label'       => __('Enable AP4L Payment', 'woocommerce'),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes',
                ),
                'title'        => array(
                    'title'       => __('Title', 'woocommerce'),
                    'type'        => 'safe_text',
                    'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce'),
                    'default'     => __('AP4L Payment', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'description'  => array(
                    'title'       => __('Description', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Payment method description that the customer will see on your website.', 'woocommerce'),
                    'default'     => __('Pay with AP4L Payment', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __('Instructions', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Instructions that will be added to the thank you page.', 'woocommerce'),
                    'default'     => __('Pay with AP4L Payment', 'woocommerce'),
                    'desc_tip'    => true,
                ),
            );
        }
    }
}
