<?php
/*
 * Custom Shipping Method AP4L
 */
if (! class_exists('Ap4lShippingMethod')) {
    class Ap4lShippingMethod extends WC_Shipping_Method
    {
        /**
         * =========================================
         * Constructor for your shipping class
         * =========================================
         */
        public function __construct()
        {
            $this->id                 = 'ap4l_shipping';
            $this->method_title       = __('AP4L Shipping', 'ap4l');
            $this->method_description = __('Shipping Handle By AP4L', 'ap4l');
            $this->init();
            $this->enabled            = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
            $this->title              = isset($this->settings['title']) ? $this->settings['title'] : __('Wcblogs Shipping', 'ap4l');
        }
        /**
         * ==================
         * Init your settings
         * ==================
         */
        function init()
        {
            // Load the settings API
            $this->init_form_fields();
            $this->init_settings();
            // Save settings in admin if you have any defined
            add_action('woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ));
        }
        /**
         * =======================================
         * Define settings field for this shipping
         * =======================================
         */
        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __('Enable', 'ap4l'),
                    'type'        => 'checkbox',
                    'description' => __('Enable this shipping.', 'ap4l'),
                    'default'     => 'yes'
                ),
                'title'   => array(
                    'title'       => __('Title', 'ap4l'),
                    'type'        => 'text',
                    'description' => __('Title to be display on site', 'ap4l'),
                    'default'     => __('Shipping Handle By AP4L', 'ap4l')
                ),
            );
        }
    }
}
