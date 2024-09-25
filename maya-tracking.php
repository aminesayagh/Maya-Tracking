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

// Check PHP version
if (version_compare(PHP_VERSION, '7.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>Maya Tracking plugin requires PHP 7.0 or higher.</p></div>';
    });
    return;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>Maya Tracking plugin requires WooCommerce to be installed and active.</p></div>';
    });
    return;
}

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
function maya_tracking_init() {
    // Check if API credentials are set
    if (!defined('MAYA_API_URL') || !defined('MAYA_API_USERNAME') || !defined('MAYA_API_PASSWORD')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Maya Tracking plugin requires API credentials to be set in wp-config.php. Please configure MAYA_API_URL, MAYA_API_USERNAME, and MAYA_API_PASSWORD.</p></div>';
        });
        return;
    }

    // Initialize the main plugin class
    $mayaTracking = MayaTracking\MayaTracking::getInstance();
    $mayaTracking->initHooks();
}
add_action('plugins_loaded', 'maya_tracking_init');

add_action('admin_notices', function () {
    if (!defined('MAYA_API_URL') || !defined('MAYA_API_USERNAME') || !defined('MAYA_API_PASSWORD')) {
        echo '<div class="error"><p>Maya Tracking plugin requires API credentials to be set in wp-config.php. Please configure MAYA_API_URL, MAYA_API_USERNAME, and MAYA_API_PASSWORD.</p></div>';
    }
});


// Activation hook
register_activation_hook(__FILE__, 'maya_tracking_activate');
function maya_tracking_activate() {
    // Perform any necessary setup on activation
    // For example, create custom database tables if needed
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'maya_tracking_deactivate');
function maya_tracking_deactivate() {
    // Perform any necessary cleanup on deactivation
}

// Uninstall hook (optional)
register_uninstall_hook(__FILE__, 'maya_tracking_uninstall');
function maya_tracking_uninstall() {
    // Perform any necessary cleanup on uninstall
    // For example, remove plugin-specific options and database tables
}

function get_maya_tracking_url(string $order_id) {
    // Check if the order exists
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("Maya Tracking: Order not found for ID: " . $order_id);
        return null;
    }

    try {
        // Get the MayaTracking instance
        $maya_tracking = MayaTracking\MayaTracking::getInstance();

        // Use the existing method to get or generate the tracking URL
        $tracking_url = $maya_tracking->getOrGenerateMayaTrackingUrl($order_id);

        return $tracking_url;

    } catch (\Exception $e) {
        error_log("Maya Tracking: Error getting tracking URL for order #" . $order_id . ": " . $e->getMessage());
        return null;
    }
}