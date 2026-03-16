<?php
/**
 * Plugin Name: TravelShip
 * Plugin URI: https://travelship.id
 * Description: Sistem membership travel dengan dashboard user (React) dan panel admin (PHP).
 * Version: 1.0.1
 * Author: TravelShip
 * Author URI: https://travelship.id
 * Text Domain: travelship
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('TRAVELSHIP_VERSION', '1.0.1');
define('TRAVELSHIP_PLUGIN_FILE', __FILE__);
define('TRAVELSHIP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRAVELSHIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRAVELSHIP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'TravelShip\\';
    $base_dir = TRAVELSHIP_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    $class_name = array_pop($parts);

    // Default: convert namespace parts to lowercase directories
    $sub_dir = !empty($parts) ? strtolower(implode('/', $parts)) . '/' : '';
    // Convert CamelCase to hyphenated-lowercase
    $class_file = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name));
    $file = $base_dir . $sub_dir . 'class-' . str_replace('_', '-', $class_file) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Activation
register_activation_hook(__FILE__, function () {
    TravelShip\Activator::activate();
});

// Deactivation
register_deactivation_hook(__FILE__, function () {
    TravelShip\Deactivator::deactivate();
});

// Initialize plugin
add_action('plugins_loaded', function () {
    $plugin = new TravelShip\TravelShip();
    $plugin->run();
});
