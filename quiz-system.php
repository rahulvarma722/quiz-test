<?php
/**
 * Plugin Name: Quiz System
 * Plugin URI: https://example.com/quiz-system
 * Description: A custom WordPress plugin that adds a quiz system to your website.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: quiz-system
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QUIZ_SYSTEM_VERSION', '1.0.0');
define('QUIZ_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QUIZ_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/post-types.php';
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/admin/admin-functions.php';
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/frontend/shortcodes.php';
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/frontend/quiz-display.php';
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/frontend/quiz-submission.php';
require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/frontend/quiz-ajax.php';

// Plugin activation
register_activation_hook(__FILE__, 'quiz_system_activate');
function quiz_system_activate() {
    // Create custom post types
    require_once QUIZ_SYSTEM_PLUGIN_DIR . 'includes/post-types.php';
    quiz_system_register_post_types();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Plugin deactivation
register_deactivation_hook(__FILE__, 'quiz_system_deactivate');
function quiz_system_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Initialize the plugin
add_action('plugins_loaded', 'quiz_system_init');
function quiz_system_init() {
    // Load text domain for translations
    load_plugin_textdomain('quiz-system', false, dirname(plugin_basename(__FILE__)) . '/languages');
}