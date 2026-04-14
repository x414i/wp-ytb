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

        // Render HTML
        ob_start();
        ?>
        <div class="wp-ytb-container wp-ytb-rtl">
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
                            <div class="wp-ytb-play-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="wp-ytb-content">
                            <h3 class="wp-ytb-title" title="<?php echo esc_attr( $video['title'] ); ?>"><?php echo esc_html( $video['title'] ); ?></h3>
                            <span class="wp-ytb-date"><?php echo esc_html( $video['published'] ); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new WP_YTB_Shortcode();
