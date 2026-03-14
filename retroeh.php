<?php
/**
 * Plugin Name: RetroEh! Plugin
 * Plugin URI: https://bythegram.ca/retro-eh
 * Description: A plugin to display the latest played game from RetroAchievements with a custom background and game details.
 * Version: 1.0.0
 * Author: Adam Graham
 * Author URI: https://bythegram.ca
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Register plugin settings
function retroeh_register_settings() {
    register_setting( 'retroeh_settings', 'retroeh_api_key', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );
}
add_action( 'admin_init', 'retroeh_register_settings' );

// Add settings page under Settings menu
function retroeh_add_settings_page() {
    add_options_page(
        __( 'RetroEh! Settings', 'retroeh' ),
        __( 'RetroEh!', 'retroeh' ),
        'manage_options',
        'retroeh-settings',
        'retroeh_render_settings_page'
    );
}
add_action( 'admin_menu', 'retroeh_add_settings_page' );

// Render the settings page
function retroeh_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'RetroEh! Settings', 'retroeh' ); ?></h1>
        <p><?php esc_html_e( 'Store your RetroAchievements API key here instead of embedding it in shortcodes or block attributes.', 'retroeh' ); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields( 'retroeh_settings' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'API Key', 'retroeh' ); ?></th>
                    <td>
                        <input type="password" name="retroeh_api_key"
                            value="<?php echo esc_attr( get_option( 'retroeh_api_key' ) ); ?>"
                            class="regular-text" autocomplete="off" />
                        <p class="description"><?php esc_html_e( 'Your RetroAchievements API key. Keeping it here prevents it from being stored in post content.', 'retroeh' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enqueue the CSS file only when the block or shortcode is present
function retroeh_enqueue_styles() {
    global $post;
    $should_enqueue = is_a( $post, 'WP_Post' ) && (
        has_block( 'retroeh/game-display', $post ) ||
        has_shortcode( $post->post_content, 'retroeh_game_display' )
    );

    if ( ! $should_enqueue ) {
        return;
    }

    wp_enqueue_style(
        'retroeh-google-font',
        'https://fonts.googleapis.com/css2?family=Tiny5&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'retroeh-style',
        plugin_dir_url( __FILE__ ) . 'src/style.css',
        array(),
        '1.0.0'
    );
}
add_action( 'wp_enqueue_scripts', 'retroeh_enqueue_styles' );

// Shortcode to display the latest played game
function retroeh_last_game_display_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'api_key'  => '',
        'username' => '',
        'game_id'  => '',
    ), $atts, 'retroachievements_game_display' );

    // Prefer the global settings key; fall back to the shortcode attribute for backward compatibility
    $api_key  = sanitize_text_field( get_option( 'retroeh_api_key', '' ) );
    if ( empty( $api_key ) ) {
        $api_key = sanitize_text_field( $atts['api_key'] );
    }
    $username = sanitize_text_field( $atts['username'] );
    $game_id  = absint( $atts['game_id'] ); // Enforce non-negative integer

    if ( empty( $api_key ) || ( empty( $username ) && empty( $game_id ) ) ) {
        return '<p style="color: red;">Error: API key and either a username or a game ID are required.</p>';
    }

    // Build a cache key scoped to the API key so different credentials never share cached data
    $api_key_hash = substr( md5( $api_key ), 0, 8 );
    $cache_key    = $game_id
        ? 'retroeh_game_' . $game_id . '_' . $api_key_hash
        : 'retroeh_user_' . md5( $username ) . '_' . $api_key_hash;
    $data      = get_transient( $cache_key );

    if ( false === $data ) {
        // Determine the API endpoint
        if ( ! empty( $game_id ) ) {
            $api_url     = 'https://retroachievements.org/API/API_GetGame.php';
            $request_url = add_query_arg( array(
                'i' => $game_id,
                'y' => $api_key,
            ), $api_url );
        } else {
            $api_url     = 'https://retroachievements.org/API/API_GetUserRecentlyPlayedGames.php';
            $request_url = add_query_arg( array(
                'u' => $username,
                'y' => $api_key,
            ), $api_url );
        }

        // Fetch data from the API with an explicit timeout
        $response = wp_remote_get( $request_url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return '<p style="color: red;">Error: ' . esc_html( $response->get_error_message() ) . '</p>';
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            return '<p style="color: red;">Error: API returned status code ' . absint( $status_code ) . '</p>';
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( null === $data ) {
            return '<p style="color: red;">Error: Failed to parse API response.</p>';
        }

        // Cache the response for one hour
        set_transient( $cache_key, $data, HOUR_IN_SECONDS );
    }

    // Extract fields based on which endpoint was used
    if ( ! empty( $game_id ) ) {
        $game_title   = isset( $data['Title'] ) ? $data['Title'] : 'Unknown';
        $console_name = isset( $data['ConsoleName'] ) ? $data['ConsoleName'] : 'Unknown';
        $box_art      = isset( $data['ImageBoxArt'] ) ? $data['ImageBoxArt'] : '';
        $image_ingame = isset( $data['ImageIngame'] ) ? $data['ImageIngame'] : '';
        $time_ago     = '';
    } else {
        if ( empty( $data ) || ! is_array( $data ) ) {
            return '<p style="color: red;">No games found in the API response.</p>';
        }
        $last_game    = $data[0];
        $game_title   = isset( $last_game['Title'] ) ? $last_game['Title'] : 'Unknown';
        $console_name = isset( $last_game['ConsoleName'] ) ? $last_game['ConsoleName'] : 'Unknown';
        $box_art      = isset( $last_game['ImageBoxArt'] ) ? $last_game['ImageBoxArt'] : '';
        $image_ingame = isset( $last_game['ImageIngame'] ) ? $last_game['ImageIngame'] : '';
        $last_played  = isset( $last_game['LastPlayed'] ) ? $last_game['LastPlayed'] : '';

        $time_ago = '';
        if ( ! empty( $last_played ) ) {
            try {
                $last_played_time = new DateTime( $last_played );
                $current_time     = new DateTime();
                $interval         = $last_played_time->diff( $current_time );

                if ( $interval->y > 0 ) {
                    $time_ago = $interval->y . ' year' . ( $interval->y > 1 ? 's' : '' ) . ' ago';
                } elseif ( $interval->m > 0 ) {
                    $time_ago = $interval->m . ' month' . ( $interval->m > 1 ? 's' : '' ) . ' ago';
                } elseif ( $interval->d > 0 ) {
                    $time_ago = $interval->d . ' day' . ( $interval->d > 1 ? 's' : '' ) . ' ago';
                } elseif ( $interval->h > 0 ) {
                    $time_ago = $interval->h . ' hour' . ( $interval->h > 1 ? 's' : '' ) . ' ago';
                } elseif ( $interval->i > 0 ) {
                    $time_ago = $interval->i . ' minute' . ( $interval->i > 1 ? 's' : '' ) . ' ago';
                } else {
                    $time_ago = 'Just now';
                }
            } catch ( Exception $e ) {
                $time_ago = '';
            }
        }
    }

    $box_art_url      = 'https://media.retroachievements.org/' . $box_art;
    $image_ingame_url = 'https://media.retroachievements.org/' . $image_ingame;

    ob_start();
    ?>
    <div class="retroeh-container" style="background-image: url('<?php echo esc_url( $image_ingame_url ); ?>');">
        <div class="retroeh-box-art">
            <img src="<?php echo esc_url( $box_art_url ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Box Art for %s', 'retroeh' ), $game_title ) ); ?>">
        </div>
        <div class="retroeh-details">
            <h2><?php echo esc_html( $game_title ); ?></h2>
            <p><strong><?php esc_html_e( 'Console:', 'retroeh' ); ?></strong> <?php echo esc_html( $console_name ); ?></p>
            <?php if ( ! empty( $time_ago ) ) : ?>
                <p><strong><?php esc_html_e( 'Last Played:', 'retroeh' ); ?></strong> <?php echo esc_html( $time_ago ); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode( 'retroeh_game_display', 'retroeh_last_game_display_shortcode' );

// Enqueue block scripts and styles
function retroeh_register_block() {
    wp_register_script(
        'retroeh-block',
        plugins_url( 'build/block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
        '1.0.0',
        true
    );

    register_block_type( 'retroeh/game-display', array(
        'editor_script'   => 'retroeh-block',
        'editor_style'    => 'retroeh-block-style',
        'render_callback' => 'retroeh_render_game_block',
        'attributes'      => array(
            'username' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'game_id'  => array(
                'type'    => 'string',
                'default' => '',
            ),
        ),
    ) );
}
add_action( 'init', 'retroeh_register_block' );

// Render callback for the block
function retroeh_render_game_block( $attributes ) {
    return do_shortcode(
        '[retroeh_game_display username="' . esc_attr( $attributes['username'] ) . '" game_id="' . esc_attr( $attributes['game_id'] ) . '"]'
    );
}
