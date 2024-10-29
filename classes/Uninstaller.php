<?php
/**
 * This class defines all code necessary to run during the plugin's deactivation.
 */
namespace Ap4l;

if (! class_exists('Uninstaller')) {

    class Uninstaller
    {
        public function __construct()
        {
            // NA
        }

        public function uninstall()
        {
            global $wpdb;

            /*
             * ===================================================
             * Drop All the table that were created by this plugin.
             * ===================================================
             */
            $tables = array(
                'accounts',
                'cat_mapping_attributes',
                'listing_logs',
                'listings',
                'orders',
                'orders_logs',
                'parent_policies',
                'product_attributes',
                'selling_policy',
                'shipping_policy',
                'sync_policy',
                'wc_mapping_attributes',
                'quantity_to_wp',
                'quantity_to_wp_products',
            );

            foreach ($tables as $table) {
                $wpdb->query("DROP TABLE IF EXISTS " . AP4L_TABLE_PREFIX . $table);
            }

            // Delete ap4l meta keys from options table
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE '" . AP4L_SLUG . "%'");

            // Delete ap4l meta keys from postmeta table
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key LIKE '" . AP4L_SLUG . "%'");

            // Delete ap4l meta keys from termmeta table
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "termmeta WHERE meta_key LIKE '" . AP4L_SLUG . "%'");
        }
    }
}
