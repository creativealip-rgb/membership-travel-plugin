<?php
/**
 * Plugin Name: TravelShip
 * Plugin URI: https://travelship.id
 * Description: Sistem membership travel dengan dashboard user (React) dan panel admin (PHP).
 * Version: 2.5.0
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
define('TRAVELSHIP_VERSION', '2.5.0');
define('TRAVELSHIP_PLUGIN_FILE', wp_normalize_path(__FILE__));
define('TRAVELSHIP_PLUGIN_DIR', wp_normalize_path(plugin_dir_path(__FILE__)));
define('TRAVELSHIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRAVELSHIP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'TravelShip\\') !== 0) {
        return;
    }

    $class_path = str_replace('TravelShip\\', '', $class);
    $parts = explode('\\', $class_path);
    $class_name = array_pop($parts);
    
    $sub_dir = !empty($parts) ? strtolower(implode('/', $parts)) . '/' : '';
    
    // Check WordPress style: class-name.php
    $hyphenated = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name));
    $hyphenated = str_replace('_', '-', $hyphenated);
    
    $file = TRAVELSHIP_PLUGIN_DIR . 'includes/' . $sub_dir . 'class-' . $hyphenated . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Check simple lowercase style: class-classname.php
    $file = TRAVELSHIP_PLUGIN_DIR . 'includes/' . $sub_dir . 'class-' . strtolower($class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Activation
register_activation_hook(__FILE__, 'travelship_activate');
function travelship_activate() {
    $activator = TRAVELSHIP_PLUGIN_DIR . 'includes/class-activator.php';
    if (file_exists($activator)) {
        require_once $activator;
    }
    if (class_exists('TravelShip\\Activator')) {
        \TravelShip\Activator::activate();
    }
}

// Deactivation
register_deactivation_hook(__FILE__, 'travelship_deactivate');
function travelship_deactivate() {
    $deactivator = TRAVELSHIP_PLUGIN_DIR . 'includes/class-deactivator.php';
    if (file_exists($deactivator)) {
        require_once $deactivator;
    }
    if (class_exists('TravelShip\\Deactivator')) {
        \TravelShip\Deactivator::deactivate();
    }
}

// Initialize plugin
add_action('plugins_loaded', function () {
    $plugin = new TravelShip\TravelShip();
    $plugin->run();
});
