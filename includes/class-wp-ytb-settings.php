<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_YTB_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_admin_menu() {
        add_options_page(
            __( 'WP YouTube Settings', 'wp-ytb' ),
            __( 'WP YouTube', 'wp-ytb' ),
            'manage_options',
            'wp-ytb',
            [ $this, 'create_admin_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'wp_ytb_settings_group', 'wp_ytb_channel_input', 'sanitize_text_field' );
        register_setting( 'wp_ytb_settings_group', 'wp_ytb_default_limit', 'absint' );
        register_setting( 'wp_ytb_settings_group', 'wp_ytb_cache_hours', 'absint' );
        
        // Setup default limits if not exist
        if ( false === get_option( 'wp_ytb_default_limit' ) ) {
            add_option('wp_ytb_default_limit', 6);
        }
        if ( false === get_option( 'wp_ytb_cache_hours' ) ) {
            add_option('wp_ytb_cache_hours', 12);
        }
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'إعدادات إضافة WP YouTube', 'wp-ytb' ); ?></h1>
            <p><?php esc_html_e( 'أدخل رابط القناة أو حساب @ لتقوم الإضافة بالباقي. يمكنك أيضاً إعداد هذه القيم كافتراضية واستخدام الشورت كود مباشرة.', 'wp-ytb' ); ?></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'wp_ytb_settings_group' ); ?>
                <?php do_settings_sections( 'wp_ytb_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'رابط القناة أو الاسم (Handle)', 'wp-ytb' ); ?></th>
                        <td>
                            <input type="text" name="wp_ytb_channel_input" value="<?php echo esc_attr( get_option( 'wp_ytb_channel_input' ) ); ?>" placeholder="e.g. @username or https://youtube.com/@username" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'أدخل اسم المستخدم مثل @username أو رابط القناة.', 'wp-ytb' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'عدد الفيديوهات الافتراضي', 'wp-ytb' ); ?></th>
                        <td>
                            <input type="number" name="wp_ytb_default_limit" value="<?php echo esc_attr( get_option( 'wp_ytb_default_limit', 6 ) ); ?>" class="small-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'تحديث الفيديوهات كل (ساعات)', 'wp-ytb' ); ?></th>
                        <td>
                            <input type="number" name="wp_ytb_cache_hours" value="<?php echo esc_attr( get_option( 'wp_ytb_cache_hours', 12 ) ); ?>" class="small-text" />
                            <p class="description"><?php esc_html_e( 'وقت بقاء الفيديوهات في التخزين المؤقت قبل جلب أحدث المقاطع من الاستوديو.', 'wp-ytb' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <hr>
            <h3><?php esc_html_e('كيفية الاستخدام', 'wp-ytb'); ?></h3>
            <p><?php esc_html_e('يمكنك استخدام الكود المختصر التالي في أي مكان داخل موقعك لعرض الفيديوهات بالإعدادات الافتراضية أعلاه:', 'wp-ytb'); ?></p>
            <code>[youtube_latest]</code>
            <p><?php esc_html_e('أو يمكنك تخصيص القناة والعدد في مكان معين باستخدام:', 'wp-ytb'); ?></p>
            <code>[youtube_latest channel="@another_handle" limit="3"]</code>
        </div>
        <?php
    }
}

new WP_YTB_Settings();
