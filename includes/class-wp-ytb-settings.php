<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_YTB_Settings {

    private $settings_tabs = [];

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_init', [ $this, 'clear_cache_handler' ] );

        $this->settings_tabs = [
            'general'    => __( 'إعدادات عامة وتتحكم (General)', 'wp-ytb' ),
            'layout'     => __( 'تصميم الشبكة (Layout)', 'wp-ytb' ),
            'typography' => __( 'تخصيص النصوص (Typography)', 'wp-ytb' ),
            'advanced'   => __( 'إعدادات متقدمة (Advanced)', 'wp-ytb' ),
            'guide'      => __( 'دليل الاستخدام', 'wp-ytb' )
        ];
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
        // General Group
        register_setting( 'wp_ytb_general_group', 'wp_ytb_channel_input', 'sanitize_text_field' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_default_limit', 'absint' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_cache_hours', 'absint' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_show_title', 'absint' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_show_date', 'absint' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_show_icon', 'absint' );
        register_setting( 'wp_ytb_general_group', 'wp_ytb_enable_hover', 'absint' );

        // Layout Group
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_col_desktop', 'absint' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_col_tablet', 'absint' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_col_mobile', 'absint' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_gap', 'sanitize_text_field' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_border_radius', 'sanitize_text_field' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_padding', 'sanitize_text_field' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_margin', 'sanitize_text_field' );
        register_setting( 'wp_ytb_layout_group', 'wp_ytb_max_width', 'sanitize_text_field' );

        // Typography Group
        register_setting( 'wp_ytb_typography_group', 'wp_ytb_title_size', 'sanitize_text_field' );
        register_setting( 'wp_ytb_typography_group', 'wp_ytb_text_color', 'sanitize_hex_color' );
        register_setting( 'wp_ytb_typography_group', 'wp_ytb_title_weight', 'sanitize_text_field' );
        register_setting( 'wp_ytb_typography_group', 'wp_ytb_font_family', 'sanitize_text_field' );

        // Advanced Group
        register_setting( 'wp_ytb_advanced_group', 'wp_ytb_custom_class', 'sanitize_text_field' );
        register_setting( 'wp_ytb_advanced_group', 'wp_ytb_custom_css', 'wp_strip_all_tags' );
        
        // Define Default values
        $defaults = [
            'wp_ytb_default_limit' => 6,
            'wp_ytb_cache_hours'   => 12,
            'wp_ytb_show_title'    => 1,
            'wp_ytb_show_date'     => 1,
            'wp_ytb_show_icon'     => 1,
            'wp_ytb_enable_hover'  => 1,
            'wp_ytb_col_desktop'   => 3,
            'wp_ytb_col_tablet'    => 2,
            'wp_ytb_col_mobile'    => 1,
            'wp_ytb_gap'           => '24',
            'wp_ytb_border_radius' => '12',
            'wp_ytb_padding'       => '0',
            'wp_ytb_margin'        => '20px 0',
            'wp_ytb_max_width'     => '100%',
            'wp_ytb_title_size'    => '16',
            'wp_ytb_text_color'    => '#202124',
            'wp_ytb_title_weight'  => '600',
            'wp_ytb_font_family'   => 'inherit'
        ];

        foreach ($defaults as $opt => $val) {
            if ( false === get_option( $opt ) ) {
                add_option( $opt, $val );
            }
        }
    }

    public function create_admin_page() {
        $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->settings_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        ?>
        <div class="wrap" style="direction: rtl;">
            <h1><?php esc_html_e( 'إعدادات إضافة WP YouTube', 'wp-ytb' ); ?></h1>
            
            <?php settings_errors( 'wp_ytb_messages' ); ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $this->settings_tabs as $tab_id => $tab_name ) : ?>
                    <a href="?page=wp-ytb&tab=<?php echo esc_attr( $tab_id ); ?>" class="nav-tab <?php echo $active_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_name ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <div style="display: flex; gap: 30px; align-items: flex-start; margin-top: 20px;">
                
                <!-- Settings Form -->
                <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 6px;">
                    <form method="post" action="options.php" id="wp_ytb_settings_form">
                        
                        <?php if ( $active_tab === 'general' ) : ?>
                            <?php settings_fields( 'wp_ytb_general_group' ); ?>
                            <h3>1. الإعدادات الأساسية (الافتراضية)</h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'القناة الافتراضية', 'wp-ytb' ); ?></th>
                                    <td>
                                        <input type="text" name="wp_ytb_channel_input" value="<?php echo esc_attr( get_option( 'wp_ytb_channel_input' ) ); ?>" placeholder="e.g. @username" class="regular-text" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'الحد الأقصى الافتراضي', 'wp-ytb' ); ?></th>
                                    <td>
                                        <input type="number" name="wp_ytb_default_limit" value="<?php echo esc_attr( get_option( 'wp_ytb_default_limit', 6 ) ); ?>" class="small-text" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php esc_html_e( 'مدة الكاش (ساعات)', 'wp-ytb' ); ?></th>
                                    <td>
                                        <input type="number" name="wp_ytb_cache_hours" value="<?php echo esc_attr( get_option( 'wp_ytb_cache_hours', 12 ) ); ?>" class="small-text" />
                                    </td>
                                </tr>
                            </table>

                            <hr>
                            <h3>2. التحكم بعناصر العرض (Visibility)</h3>
                            <table class="form-table">
                                <?php
                                $toggles = [
                                    'wp_ytb_show_title' => 'عرض عنوان الفيديو',
                                    'wp_ytb_show_date' => 'عرض تاريخ النشر',
                                    'wp_ytb_show_icon' => 'عرض أيقونة التشغيل عند الوقوف عليه',
                                    'wp_ytb_enable_hover' => 'تفعيل الأنيمشن والظل عند التمرير (Hover Effects)'
                                ];
                                foreach($toggles as $opt => $label): ?>
                                <tr valign="top">
                                    <th scope="row"><?php echo esc_html($label); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="<?php echo esc_attr($opt); ?>" name="<?php echo esc_attr($opt); ?>" value="1" <?php checked( 1, get_option( $opt, 1 ) ); ?> />
                                            تفعيل
                                        </label>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>

                        <?php elseif ( $active_tab === 'layout' ) : ?>
                            <?php settings_fields( 'wp_ytb_layout_group' ); ?>
                            <h3>تخطيط الشبكة (Grid Layout)</h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">مدة الأعمدة - أجهزة كمبيوتر</th>
                                    <td><input type="number" id="wp_ytb_col_desktop" name="wp_ytb_col_desktop" value="<?php echo esc_attr( get_option( 'wp_ytb_col_desktop', 3 ) ); ?>" min="1" max="6" class="small-text" /></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">عدد الأعمدة - أجهزة التابلت</th>
                                    <td><input type="number" id="wp_ytb_col_tablet" name="wp_ytb_col_tablet" value="<?php echo esc_attr( get_option( 'wp_ytb_col_tablet', 2 ) ); ?>" min="1" max="4" class="small-text" /></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">عدد الأعمدة - الهواتف الذكية</th>
                                    <td><input type="number" id="wp_ytb_col_mobile" name="wp_ytb_col_mobile" value="<?php echo esc_attr( get_option( 'wp_ytb_col_mobile', 1 ) ); ?>" min="1" max="3" class="small-text" /></td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">المسافة بين العناصر (Gap بـ px)</th>
                                    <td><input type="number" id="wp_ytb_gap" name="wp_ytb_gap" value="<?php echo esc_attr( get_option( 'wp_ytb_gap', '24' ) ); ?>" class="small-text" /> px</td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">نعومة الحواف (Border Radius بـ px)</th>
                                    <td><input type="number" id="wp_ytb_border_radius" name="wp_ytb_border_radius" value="<?php echo esc_attr( get_option( 'wp_ytb_border_radius', '12' ) ); ?>" class="small-text" /> px</td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">الهوامش الخارجية (Margin)</th>
                                    <td>
                                        <input type="text" id="wp_ytb_margin" name="wp_ytb_margin" value="<?php echo esc_attr( get_option( 'wp_ytb_margin', '20px 0' ) ); ?>" class="regular-text" />
                                        <p class="description">مثل: `20px` لجميع الجهات أو `20px 0` للأعلى والأسفل.</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">الهوامش الداخلية (Padding)</th>
                                    <td>
                                        <input type="text" id="wp_ytb_padding" name="wp_ytb_padding" value="<?php echo esc_attr( get_option( 'wp_ytb_padding', '0' ) ); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">أقصى عرض للحاوية (Max Width)</th>
                                    <td>
                                        <input type="text" id="wp_ytb_max_width" name="wp_ytb_max_width" value="<?php echo esc_attr( get_option( 'wp_ytb_max_width', '100%' ) ); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>

                        <?php elseif ( $active_tab === 'typography' ) : ?>
                            <?php settings_fields( 'wp_ytb_typography_group' ); ?>
                            <h3>تخصيص النصوص والخطوط</h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">نوع الخط (Font Family)</th>
                                    <td>
                                        <input type="text" id="wp_ytb_font_family" name="wp_ytb_font_family" value="<?php echo esc_attr( get_option( 'wp_ytb_font_family', 'inherit' ) ); ?>" class="regular-text" placeholder="مثال: Cairo, Arial, sans-serif" />
                                        <p class="description">اتركه `inherit` ليأخذ خط القالب الافتراضي.</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">لون النص الأساسي</th>
                                    <td>
                                        <input type="color" id="wp_ytb_text_color" name="wp_ytb_text_color" value="<?php echo esc_attr( get_option( 'wp_ytb_text_color', '#202124' ) ); ?>" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">حجم خط العنوان (px)</th>
                                    <td><input type="number" id="wp_ytb_title_size" name="wp_ytb_title_size" value="<?php echo esc_attr( get_option( 'wp_ytb_title_size', '16' ) ); ?>" class="small-text" /> px</td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">سماكة الخط العادية (Weight)</th>
                                    <td>
                                        <select id="wp_ytb_title_weight" name="wp_ytb_title_weight">
                                            <?php foreach([400=>'عادي (400)', 500=>'متوسط (500)', 600=>'شبه غامق (600)', 700=>'غامق (700)', 800=>'غامق جدا (800)'] as $val => $label): ?>
                                                <option value="<?php echo esc_attr($val); ?>" <?php selected( get_option('wp_ytb_title_weight', '600'), $val ); ?>><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>

                        <?php elseif ( $active_tab === 'advanced' ) : ?>
                            <?php settings_fields( 'wp_ytb_advanced_group' ); ?>
                            <h3>إعدادات متطورة (للمطورين)</h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">كلاسات CSS مخصصة (Custom Classes)</th>
                                    <td>
                                        <input type="text" id="wp_ytb_custom_class" name="wp_ytb_custom_class" value="<?php echo esc_attr( get_option( 'wp_ytb_custom_class', '' ) ); ?>" class="regular-text" placeholder="my-custom-grid featured-videos" />
                                        <p class="description">سيتم دمج هذا الكلاس مع الحاوية الرئيسية `wp-ytb-container` لتمرير تنسيقات القالب الخاص بك.</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">صندوق CSS المخصص (Custom CSS Box)</th>
                                    <td>
                                        <textarea id="wp_ytb_custom_css" name="wp_ytb_custom_css" rows="6" class="large-text code" placeholder=".wp-ytb-item { border: 2px solid #ff0000; }"><?php echo esc_textarea( get_option( 'wp_ytb_custom_css', '' ) ); ?></textarea>
                                        <p class="description">أضف كود CSS الخاص بك هنا. سيتم طباعته تلقائياً داخل الصفحة.</p>
                                    </td>
                                </tr>
                            </table>

                        <?php elseif ( $active_tab === 'guide' ) : ?>
                            <h3>كيف تستخدم الإضافة؟</h3>
                            <p>أبسط طريقة لطباعة صندوق الفيديوهات هو وضع الكود المختصر الأساسي:</p>
                            <code>[youtube_latest]</code>
                            <p>أو لتعديل القناة المخصصة:</p>
                            <code>[youtube_latest channel="@username" limit="3"]</code>
                            <hr>
                            <h3 style="color:#d32f2f">إفراغ الكاش يدوياً</h3>
                            <p>في حال قمت بنشر فيديو ولم يظهر بعد، يمكنك تحديثه باستخدام الزر أسفل الإعدادات لجلبه فوراً بدل انتظار الـ 12 ساعة المخصصة للكاش.</p>

                        <?php endif; ?>

                        <?php if ( $active_tab !== 'guide' ) : ?>
                            <?php submit_button( 'حفظ الإعدادات والتحديث' ); ?>
                        <?php endif; ?>
                    </form>

                    <?php if ( $active_tab === 'general' || $active_tab === 'guide' ) : ?>
                    <form method="post" action="" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <?php wp_nonce_field( 'wp_ytb_clear_cache_action', 'wp_ytb_clear_cache_nonce' ); ?>
                        <input type="submit" name="wp_ytb_clear_cache" class="button button-secondary" value="إفراغ الكاش يدوياً" onclick="return confirm('هل أنت متأكد؟');" />
                    </form>
                    <?php endif; ?>

                </div>

                <!-- Live Preview Pane -->
                <?php if ( $active_tab !== 'guide' ) : ?>
                <div style="width: 400px; padding: 20px; background: #fafafa; border: 1px solid #ccd0d4; border-radius: 6px;">
                    <h3><span class="dashicons dashicons-visibility"></span> معاينة حية للطريقة العرض (Live Preview)</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 20px;">هذه المعاينة توضح التنسيقات والأبعاد المطبقة فوريا وفقا لإعداداتك واختياراتك.</p>
                    
                    <div id="ytb_preview_container" style="
                        --ytb-gap: <?php echo esc_attr( get_option('wp_ytb_gap', '24') ); ?>px;
                        --ytb-radius: <?php echo esc_attr( get_option('wp_ytb_border_radius', '12') ); ?>px;
                        --ytb-title-size: <?php echo esc_attr( get_option('wp_ytb_title_size', '16') ); ?>px;
                        --ytb-title-weight: <?php echo esc_attr( get_option('wp_ytb_title_weight', '600') ); ?>;
                        --ytb-text-color: <?php echo esc_attr( get_option('wp_ytb_text_color', '#202124') ); ?>;
                        --ytb-font-family: <?php echo esc_attr( get_option('wp_ytb_font_family', 'inherit') ); ?>;
                        --ytb-padding: <?php echo esc_attr( get_option('wp_ytb_padding', '0') ); ?>;
                        /* Mock the container width to 100% inside preview instead of respecting max-width, or just apply it */
                        max-width: <?php echo esc_attr( get_option('wp_ytb_max_width', '100%') ); ?>;
                        font-family: var(--ytb-font-family);
                        padding: var(--ytb-padding);
                    ">
                        
                        <!-- Mock Card -->
                        <div id="ytb_preview_card" style="
                            background: #fff;
                            border-radius: var(--ytb-radius);
                            box-shadow: 0 4px 20px rgba(0,0,0,0.08); /* Mock base shadow */
                            overflow: hidden;
                            position: relative;
                            transition: all 0.4s ease;
                        ">
                            <div style="width: 100%; aspect-ratio: 16/9; background: #ddd; position: relative;">
                                <div id="ytb_preview_icon" style="
                                    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                    width: 48px; height: 48px; background: rgba(0,0,0,0.6); border-radius: 50%;
                                    display: <?php echo get_option('wp_ytb_show_icon', 1) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center;
                                ">
                                    <svg viewBox="0 0 24 24" fill="#fff" style="width: 24px; height: 24px; margin-left: 3px;"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div style="padding: 16px;">
                                <h4 id="ytb_preview_title" style="
                                    margin: 0 0 8px 0;
                                    color: var(--ytb-text-color);
                                    font-size: var(--ytb-title-size);
                                    font-weight: var(--ytb-title-weight);
                                    display: <?php echo get_option('wp_ytb_show_title', 1) ? 'block' : 'none'; ?>;
                                ">مثال على عنوان الفيديو المعروض</h4>
                                <span id="ytb_preview_date" style="
                                    font-size: 13px; color: #5f6368;
                                    display: <?php echo get_option('wp_ytb_show_date', 1) ? 'block' : 'none'; ?>;
                                ">12 نوفمبر 2026</span>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $active_tab !== 'guide' ) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('ytb_preview_container');
            const card = document.getElementById('ytb_preview_card');
            const title = document.getElementById('ytb_preview_title');
            const date = document.getElementById('ytb_preview_date');
            const icon = document.getElementById('ytb_preview_icon');

            // Form inputs
            const inputRadius = document.getElementById('wp_ytb_border_radius');
            const inputTitleSize = document.getElementById('wp_ytb_title_size');
            const inputTitleWeight = document.getElementById('wp_ytb_title_weight');
            const inputTextColor = document.getElementById('wp_ytb_text_color');
            const inputFontFamily = document.getElementById('wp_ytb_font_family');
            const inputPadding = document.getElementById('wp_ytb_padding');
            const inputMaxWidth = document.getElementById('wp_ytb_max_width');
            const toggleTitle = document.getElementById('wp_ytb_show_title');
            const toggleDate = document.getElementById('wp_ytb_show_date');
            const toggleIcon = document.getElementById('wp_ytb_show_icon');

            if(inputRadius) inputRadius.addEventListener('input', e => container.style.setProperty('--ytb-radius', e.target.value + 'px'));
            if(inputTitleSize) inputTitleSize.addEventListener('input', e => container.style.setProperty('--ytb-title-size', e.target.value + 'px'));
            if(inputTitleWeight) inputTitleWeight.addEventListener('change', e => container.style.setProperty('--ytb-title-weight', e.target.value));
            if(inputTextColor) inputTextColor.addEventListener('input', e => container.style.setProperty('--ytb-text-color', e.target.value));
            if(inputFontFamily) inputFontFamily.addEventListener('input', e => container.style.setProperty('--ytb-font-family', e.target.value));
            if(inputPadding) inputPadding.addEventListener('input', e => container.style.setProperty('--ytb-padding', e.target.value));
            if(inputMaxWidth) inputMaxWidth.addEventListener('input', e => container.style.maxWidth = e.target.value);
            
            if(toggleTitle) toggleTitle.addEventListener('change', e => title.style.display = e.target.checked ? 'block' : 'none');
            if(toggleDate) toggleDate.addEventListener('change', e => date.style.display = e.target.checked ? 'block' : 'none');
            if(toggleIcon) toggleIcon.addEventListener('change', e => icon.style.display = e.target.checked ? 'flex' : 'none');
        });
        </script>
        <?php endif; ?>
        <?php
    }
}

new WP_YTB_Settings();
