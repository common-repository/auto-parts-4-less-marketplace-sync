<?php
/**
 * Plugin Name: Auto Parts 4 Less Marketplace Sync
 * Plugin URI: https://www.autoparts4less.com/
 * Description: This plugin will list your WooCommerce products to Auto Parts 4 Less marketplace.
 * Version: 1.0.0
 * Author: Auto Parts 4 Less
 * Author URI: https://profiles.wordpress.org/autopart4less/
 * Text Domain: ap4l
 */

/*
 * =======================================
 * If this file is called directly, abort.
 * =======================================
 */
if (! defined('WPINC')) {
    die;
}

define('AP4L_DIR', plugin_dir_path(__FILE__));

/*
 * ================
 * Define variables
 * ================
 */
include_once AP4L_DIR . 'includes/config.php';

/*
 * ============================================
 * The code that runs during plugin activation.
 * ============================================
 */
if (! function_exists('ap4l_activation')) {
    function ap4l_activation()
    {
        include_once AP4L_DIR . 'classes/Activator.php';
        $activator = new Ap4l\Activator();
        $activator->activate();
    }
}

/*
 * ==============================================
 * The code that runs during plugin deactivation.
 * ==============================================
 */
if (! function_exists('ap4l_deactivation')) {
    function ap4l_deactivation()
    {
        include_once AP4L_DIR . 'classes/Deactivator.php';
        $deactivator = new Ap4l\Deactivator();
        $deactivator->deactivate();
    }
}

/*
 * ===========================================
 * The code that runs during plugin uninstall.
 * ===========================================
 */
if (! function_exists('ap4l_uninstall')) {
    function ap4l_uninstall()
    {
        include_once AP4L_DIR . 'classes/Uninstaller.php';
        $deactivator = new Ap4l\Uninstaller();
        $deactivator->uninstall();
    }
}

/*
 * =====================
 * The core plugin class
 * =====================
 */
if (! function_exists('ap4l_run')) {
    function ap4l_run()
    {
        include_once AP4L_DIR . 'classes/Main.php';
        $plugin = new Ap4l\Main();
    }
}

register_activation_hook(__FILE__, 'ap4l_activation');
register_deactivation_hook(__FILE__, 'ap4l_deactivation');
register_uninstall_hook(__FILE__, 'ap4l_uninstall');
ap4l_run();
