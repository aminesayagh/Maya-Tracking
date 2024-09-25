<?php
/*
Plugin Name: Maya Tracking Integration
Description: Integrates Maya tracking information with WooCommerce orders.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('MAYA_TRACKING_PLUGIN_FILE', __FILE__);
define('MAYA_TRACKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAYA_TRACKING_PLUGIN_URL', plugin_dir_url(__FILE__));

// _maya_tracking_url meta key define
define('MAYA_TRACKING_URL_META_KEY', '_maya_tracking_url');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'MayaTracking\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function () {
    $mayaTracking = MayaTracking\MayaTracking::getInstance();
    $mayaTracking->initHooks();
});

add_action('admin_notices', function () {
    if (!defined('MAYA_API_URL') || !defined('MAYA_API_USERNAME') || !defined('MAYA_API_PASSWORD')) {
        echo '<div class="error"><p>Maya Tracking plugin requires API credentials to be set in wp-config.php. Please configure MAYA_API_URL, MAYA_API_USERNAME, and MAYA_API_PASSWORD.</p></div>';
    }
});