<?php
/**
 * Plugin Name: WP YouTube Latest
 * Plugin URI: https://github.com/x414i/wp-ytb
 * Description: Display the latest YouTube videos from any channel using RSS feed without API key. Small, agile, and cache-ready.
 * Version: 1.0.0
 * Author: Mohammed Belaid
 * Author URI: https://github.com/x414i
 * Text Domain: wp-ytb
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define( 'WP_YTB_VERSION', '1.0.0' );
define( 'WP_YTB_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_YTB_URL', plugin_dir_url( __FILE__ ) );

// Include necessary classes
require_once WP_YTB_DIR . 'includes/class-wp-ytb-feed.php';
require_once WP_YTB_DIR . 'includes/class-wp-ytb-shortcode.php';

if ( is_admin() ) {
    require_once WP_YTB_DIR . 'includes/class-wp-ytb-settings.php';
}

// Activation Hook
register_activation_hook( __FILE__, 'wp_ytb_activate' );
function wp_ytb_activate() {
    // We could clear transient cache here
    delete_transient( 'wp_ytb_videos' );
}

// Deactivation Hook
register_deactivation_hook( __FILE__, 'wp_ytb_deactivate' );
function wp_ytb_deactivate() {
    delete_transient( 'wp_ytb_videos' );
}
