<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_YTB_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_init', [ $this, 'clear_cache_handler' ] );
    }

    public function clear_cache_handler() {
        if ( isset( $_POST['wp_ytb_clear_cache'] ) && check_admin_referer( 'wp_ytb_clear_cache_action', 'wp_ytb_clear_cache_nonce' ) ) {
            global $wpdb;
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_ytb_videos_%' OR option_name LIKE '_transient_timeout_wp_ytb_videos_%'" );
            add_settings_error( 'wp_ytb_messages', 'wp_ytb_cache_cleared', __( 'تم مسح التخزين المؤقت بنجاح. سيتم جلب الفيديوهات المحدثة عند أقرب زيارة للصفحة.', 'wp-ytb' ), 'success' );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'WP YouTube Settings', 'wp-ytb' ),
            __( 'WP YouTube', 'wp-ytb' ),
            'manage_options',
            'wp-ytb',
            [ $this, 'create_admin_page' ],
            'dashicons-video-alt3',
            81
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
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        ?>
        <div class="wrap" style="direction: rtl;">
            <h1><?php esc_html_e( 'إعدادات إضافة WP YouTube', 'wp-ytb' ); ?></h1>
            
            <?php settings_errors( 'wp_ytb_messages' ); ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=wp-ytb&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'الإعدادات العامة', 'wp-ytb' ); ?></a>
                <a href="?page=wp-ytb&tab=guide" class="nav-tab <?php echo $active_tab == 'guide' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'دليل الاستخدام (User Guide)', 'wp-ytb' ); ?></a>
            </h2>

            <?php if ( $active_tab == 'settings' ) : ?>
                <p style="margin-top:20px;"><?php esc_html_e( 'أدخل رابط القناة أو حساب @ لتقوم الإضافة بالباقي. يمكنك أيضاً إعداد هذه القيم كافتراضية واستخدام الشورت كود مباشرة.', 'wp-ytb' ); ?></p>
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

                <hr style="margin: 30px 0;">
                
                <h3><?php esc_html_e('إفراغ ذاكرة التخزين المؤقت (Clear Cache)', 'wp-ytb'); ?></h3>
                <p><?php esc_html_e('استخدم هذا الزر لحذف الفيديوهات المخزنة حالياً لتتمكن من جلب المقاطع الجديدة فوراً دون انتظار وقت التحديث التلقائي.', 'wp-ytb'); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field( 'wp_ytb_clear_cache_action', 'wp_ytb_clear_cache_nonce' ); ?>
                    <input type="submit" name="wp_ytb_clear_cache" class="button button-secondary" value="<?php esc_attr_e( 'إفراغ الكاش', 'wp-ytb' ); ?>" onclick="return confirm('هل أنت متأكد من رغبتك في إفراغ الكاش؟');" />
                </form>
            
            <?php else : ?>
                
                <div style="background: #fff; padding: 25px; border: 1px solid #ccd0d4; margin-top: 20px; border-radius: 4px;">
                    <h3 style="margin-top:0;">دليل الاستخدام الشامل (User Guide)</h3>
                    <p>مرحباً بك في إضافة عرض فيديوهات اليوتيوب 🚀. هذه الإضافة بسيطة للغاية ولا تتطلب مفتاح API من يوتيوب.</p>
                    
                    <h4>1. استخدام الكود المختصر الأساسي (Shortcode)</h4>
                    <p>بعد ضبط القناة الافتراضية في تبويب "الإعدادات العامة"، انسخ الكود التالي وضعه في أي مقال، صفحة، أو حتى Elementor:</p>
                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 4px; display: inline-block;">[youtube_latest]</code>
                    
                    <h4 style="margin-top:20px;">2. تخصيص قنوات محددة في صفحات متعددة</h4>
                    <p>بدلاً من الاعتماد على القناة الافتراضية دائماً، يمكنك تحديد قناة معينة أو يوزر من خلال المتغير <code>channel</code>:</p>
                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 4px; display: inline-block;">[youtube_latest channel="@username"]</code>
                    
                    <h4 style="margin-top:20px;">3. التحكم بعدد الفيديوهات المعروضة</h4>
                    <p>استخدم المتغير <code>limit</code> للتحكم في عدد الفيديوهات بمرونة تامة ليتناسب مع تصميم الصفحة:</p>
                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 4px; display: inline-block;">[youtube_latest channel="https://youtube.com/@channelName" limit="3"]</code>
                    
                    <h4 style="margin-top:20px;">4. تخزين الفيديوهات (Caching) والسرعة</h4>
                    <p>لحماية موقعك من بطء التحميل ولعدم إرهاق سيرفرك بالطلبات الخارجية المكثفة إلى موقع يوتيوب، تقوم الإضافة بحفظ الفيديوهات المستخرجة لمدة افتراضية قدرها 12 ساعة. إذا قمت برفع فيديو جديد وأردت ظهوره فوراً، يجب عليك الضغط على زر "إفراغ الكاش" الموجود في قسم "الإعدادات العامة".</p>
                    
                    <h4 style="margin-top:20px;">دعم المطورين (Developers Guideline)</h4>
                    <p>الإضافة تستخدم كلاسات CSS مرتبة ومنتظمة لدعم لغة CSS بأسلوب BEM البسيط. للتحكم بالتصميم يمكنك وضع التعديلات التالية في ستايل القالب:</p>
                    <ul style="list-style:disc; margin-left: 20px; margin-right: 20px;">
                        <li><code>.wp-ytb-grid</code>: الحاوية الرئيسية (CSS Grid).</li>
                        <li><code>.wp-ytb-item</code>: صندوق الفيديو الفردي المحتوي على الصورة والعنوان.</li>
                        <li><code>.wp-ytb-title</code>: عنوان الفيديو.</li>
                    </ul>
                </div>
                
            <?php endif; ?>
        </div>
        <?php
    }
}

new WP_YTB_Settings();
