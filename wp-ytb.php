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

// Add Dashboard Widget
add_action( 'wp_dashboard_setup', 'wp_ytb_add_dashboard_widgets' );
function wp_ytb_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wp_ytb_dashboard_widget',
        __( 'دليل استخدام إضافة WP YouTube', 'wp-ytb' ),
        'wp_ytb_dashboard_widget_display'
    );
}

function wp_ytb_dashboard_widget_display() {
    ?>
    <div class="wp-ytb-widget-wrap" style="text-align: right; direction: rtl;">
        <p><strong>مرحباً بك في إضافة عرض فيديوهات اليوتيوب 🚀</strong></p>
        <p>لطباعة آخر الفيديوهات في أي مكان في الموقع (الصفحات، المقالات، أو الويدجت)، يمكنك استخدام الشورت كود التالي:</p>
        <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-bottom: 8px;">[youtube_latest]</code>
        
        <p>لتخصيص قناة معينة أو عدد فيديوهات محدد، استخدم:</p>
        <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-bottom: 8px;">[youtube_latest channel="@username" limit="3"]</code>
        
        <hr style="margin: 15px 0;">
        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-ytb' ) ); ?>" class="button button-primary">الذهاب للإعدادات العامة</a>
    </div>
    <?php
}
