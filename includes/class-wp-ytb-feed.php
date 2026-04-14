<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_YTB_Feed {

    /**
     * Get or resolve Channel ID from URL/Handle
     */
    public static function get_channel_id( $input ) {
        if ( empty( $input ) ) {
            return false;
        }

        // 1. If it looks like a standard Channel ID
        if ( preg_match( '/^UC[-a-zA-Z0-9_]{22}$/', $input ) ) {
            return $input;
        }

        // 2. Transpose into a valid channel URL
        $url = '';
        if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
            $url = $input;
        } elseif ( strpos( $input, '@' ) === 0 ) {
            $url = 'https://www.youtube.com/' . $input;
        }

        if ( empty( $url ) ) {
            return false;
        }

        // 3. Try to get from caching first
        $cache_key = 'wp_ytb_cid_' . md5( $url );
        $cached_id = get_transient( $cache_key );
        if ( $cached_id ) {
            return $cached_id;
        }

        // 4. Remote request to get Channel ID from source code
        $response = wp_remote_get( $url, [
            'timeout'    => 15,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        
        $channel_id = false;
        // Attempt match via ExternalId JSON
        if ( preg_match( '/"externalId":"(UC[-a-zA-Z0-9_]{22})"/', $body, $matches ) ) {
            $channel_id = $matches[1];
        } 
        // Attempt match via Itemprop
        elseif ( preg_match( '/<meta itemprop="channelId" content="(UC[-a-zA-Z0-9_]{22})">/', $body, $matches ) ) {
            $channel_id = $matches[1];
        }
        // Attempt alternatively via browser channel ID meta
        elseif ( preg_match( '/<meta itemprop="identifier" content="(UC[-a-zA-Z0-9_]{22})">/', $body, $matches ) ) {
            $channel_id = $matches[1];
        }

        if ( $channel_id ) {
            // Save in transient forever (1 year) because channel ID does not change.
            set_transient( $cache_key, $channel_id, YEAR_IN_SECONDS );
            return $channel_id;
        }

        return false;
    }

    /**
     * Get videos from RSS using Channel ID
     */
    public static function get_videos( $channel_id, $limit = 6 ) {
        
        $limit = absint($limit);
        $cache_hours = get_option('wp_ytb_cache_hours', 12);
        
        $cache_key = 'wp_ytb_videos_' . $channel_id . '_' . $limit;
        
        $cached_videos = get_transient($cache_key);
        if ( false !== $cached_videos ) {
            return $cached_videos;
        }

        $rss_url = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel_id;

        $response = wp_remote_get( $rss_url, [ 'timeout' => 15 ] );

        if ( is_wp_error( $response ) ) {
            return [];
        }

        $body = wp_remote_retrieve_body( $response );
        if ( empty( $body ) ) {
            return [];
        }

        $xml = @simplexml_load_string( $body );
        if ( ! $xml || ! isset( $xml->entry ) ) {
            return [];
        }

        $videos = [];
        $count = 0;

        foreach ( $xml->entry as $entry ) {
            if ( $count >= $limit ) {
                break;
            }

            // Using media namespace
            $media = $entry->children('http://search.yahoo.com/mrss/');

            $videos[] = [
                'title'     => (string) $entry->title,
                'link'      => (string) $entry->link['href'],
                'published' => date_i18n( get_option('date_format'), strtotime( (string) $entry->published ) ),
                'thumbnail' => isset($media->group->thumbnail[0]) ? (string) $media->group->thumbnail[0]['url'] : '',
            ];

            $count++;
        }

        // Cache the parsed array
        set_transient( $cache_key, $videos, $cache_hours * HOUR_IN_SECONDS );

        return $videos;
    }
}
