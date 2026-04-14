<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_YTB_Shortcode {

    public function __construct() {
        add_shortcode( 'youtube_latest', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    public function enqueue_styles() {
        global $post;
        
        // Enqueue only if we have posts and the shortcode is present
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'youtube_latest' ) ) {
            wp_enqueue_style( 'wp-ytb-style', WP_YTB_URL . 'assets/css/wp-ytb-style.css', [], WP_YTB_VERSION );
        }
    }

    public function render_shortcode( $atts ) {
        // Parse attributes
        $atts = shortcode_atts( [
            'channel' => get_option( 'wp_ytb_channel_input', '' ),
            'limit'   => get_option( 'wp_ytb_default_limit', 6 ),
        ], $atts, 'youtube_latest' );

        $channel_input = sanitize_text_field( $atts['channel'] );
        $limit         = absint( $atts['limit'] );

        if ( empty( $channel_input ) ) {
            return '<div class="wp-ytb-error">' . esc_html__( 'الرجاء توفير رابط القناة أو اليوزر الخاص بها.', 'wp-ytb' ) . '</div>';
        }

        $channel_id = WP_YTB_Feed::get_channel_id( $channel_input );

        if ( ! $channel_id ) {
            return '<div class="wp-ytb-error">' . esc_html__( 'لم يتم العثور على القناة. تأكد من صحة الرابط أو الاسم.', 'wp-ytb' ) . '</div>';
        }

        $videos = WP_YTB_Feed::get_videos( $channel_id, $limit );

        if ( empty( $videos ) ) {
            return '<div class="wp-ytb-error">' . esc_html__( 'لا توجد فيديوهات أو تعذر جلبها.', 'wp-ytb' ) . '</div>';
        }

        // Fetch settings for inline CSS & visibility
        $gap           = esc_attr( get_option('wp_ytb_gap', '24') );
        $radius        = esc_attr( get_option('wp_ytb_border_radius', '12') );
        $title_size    = esc_attr( get_option('wp_ytb_title_size', '16') );
        $title_weight  = esc_attr( get_option('wp_ytb_title_weight', '600') );
        $text_color    = esc_attr( get_option('wp_ytb_text_color', '#202124') );
        $col_desk      = absint( get_option('wp_ytb_col_desktop', 3) );
        $col_tab       = absint( get_option('wp_ytb_col_tablet', 2) );
        $col_mob       = absint( get_option('wp_ytb_col_mobile', 1) );
        
        $font_family   = esc_attr( get_option('wp_ytb_font_family', 'inherit') );
        $padding       = esc_attr( get_option('wp_ytb_padding', '0') );
        $margin        = esc_attr( get_option('wp_ytb_margin', '20px 0') );
        $max_width     = esc_attr( get_option('wp_ytb_max_width', '100%') );

        $show_title    = get_option('wp_ytb_show_title', 1);
        $show_date     = get_option('wp_ytb_show_date', 1);
        $show_icon     = get_option('wp_ytb_show_icon', 1);
        $enable_hover  = get_option('wp_ytb_enable_hover', 1);
        
        $custom_class  = esc_attr( get_option('wp_ytb_custom_class', '') );
        $custom_css    = wp_strip_all_tags( get_option('wp_ytb_custom_css', '') );

        $container_classes = 'wp-ytb-container wp-ytb-rtl';
        if ( $enable_hover ) {
            $container_classes .= ' wp-ytb-hover-enabled';
        }
        if ( ! empty( $custom_class ) ) {
            $container_classes .= ' ' . $custom_class;
        }

        // Render HTML
        ob_start();
        
        if ( ! empty( $custom_css ) ) {
            echo '<style>' . wp_strip_all_tags( $custom_css ) . '</style>';
        }
        ?>
        <div class="<?php echo esc_attr($container_classes); ?>" style="
            --ytb-gap: <?php echo $gap; ?>px;
            --ytb-radius: <?php echo $radius; ?>px;
            --ytb-title-size: <?php echo $title_size; ?>px;
            --ytb-title-weight: <?php echo $title_weight; ?>;
            --ytb-text-color: <?php echo $text_color; ?>;
            --ytb-font-family: <?php echo $font_family; ?>;
            --ytb-padding: <?php echo $padding; ?>;
            --ytb-margin: <?php echo $margin; ?>;
            --ytb-max-width: <?php echo $max_width; ?>;
            --ytb-col-desk: <?php echo $col_desk; ?>;
            --ytb-col-tab: <?php echo $col_tab; ?>;
            --ytb-col-mob: <?php echo $col_mob; ?>;
        ">
            <div class="wp-ytb-grid">
                <?php foreach ( $videos as $video ) : ?>
                    <a href="<?php echo esc_url( $video['link'] ); ?>" target="_blank" rel="noopener noreferrer" class="wp-ytb-item">
                        <div class="wp-ytb-thumb">
                            <?php 
                                $img_src = '';
                                $fallback_src = '';
                                
                                if ( ! empty( $video['videoId'] ) ) {
                                    $img_src = 'https://i.ytimg.com/vi/' . $video['videoId'] . '/maxresdefault.jpg';
                                    $fallback_src = 'https://i.ytimg.com/vi/' . $video['videoId'] . '/hqdefault.jpg';
                                } elseif ( ! empty( $video['thumbnail'] ) ) {
                                    $img_src = $video['thumbnail'];
                                }
                            ?>
                            <?php if ( ! empty( $img_src ) ) : ?>
                                <img src="<?php echo esc_url( $img_src ); ?>" <?php echo ( ! empty( $fallback_src ) ) ? 'onerror="this.onerror=null; this.src=\'' . esc_url( $fallback_src ) . '\';"' : ''; ?> alt="<?php echo esc_attr( $video['title'] ); ?>" loading="lazy" />
                            <?php endif; ?>
                            
                            <?php if ( $show_icon ) : ?>
                            <div class="wp-ytb-play-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ( $show_title || $show_date ) : ?>
                        <div class="wp-ytb-content">
                            <?php if ( $show_title ) : ?>
                            <h3 class="wp-ytb-title" title="<?php echo esc_attr( $video['title'] ); ?>"><?php echo esc_html( $video['title'] ); ?></h3>
                            <?php endif; ?>
                            
                            <?php if ( $show_date ) : ?>
                            <span class="wp-ytb-date"><?php echo esc_html( $video['published'] ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new WP_YTB_Shortcode();
