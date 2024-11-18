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

// Enqueue the CSS file
function retroeh_enqueue_styles() {
    wp_enqueue_style(
        'retroeh-google-font',
        'https://fonts.googleapis.com/css2?family=Tiny5&display=swap',
        array(),
        '1.0.0'
    );
    wp_enqueue_style(
        'retroeh-style',
        plugin_dir_url(__FILE__) . 'src/style.css',
        array(),
        '1.0.0'
    );

}
add_action('wp_enqueue_scripts', 'retroeh_enqueue_styles');

// Shortcode to display the latest played game
function retroeh_last_game_display_shortcode($atts) {
    // Extract attributes from the shortcode
    $atts = shortcode_atts(array(
        'api_key' => '',
        'username' => '',
        'game_id' => ''
    ), $atts, 'retroachievements_game_display');
    
    $api_key = sanitize_text_field($atts['api_key']);
    $username = sanitize_text_field($atts['username']);
    $game_id = sanitize_text_field($atts['game_id']);

    if (empty($api_key) || (empty($username) && empty($game_id))) {
        return '<p style="color: red;">Error: API key and either a username or a game ID are required.</p>';
    }

    // Determine the API endpoint
    if (!empty($game_id)) {
        // Use the Game ID endpoint
        $api_url = "https://retroachievements.org/API/API_GetGame.php";
        $request_url = add_query_arg(array(
            'i' => $game_id,
            'y' => $api_key
        ), $api_url);
    } else {
        // Use the Recently Played Games endpoint
        $api_url = "https://retroachievements.org/API/API_GetUserRecentlyPlayedGames.php";
        $request_url = add_query_arg(array(
            'u' => $username,
            'y' => $api_key
        ), $api_url);
    }

    // Fetch data from the API
    $response = wp_remote_get($request_url);

    // Handle errors
    if (is_wp_error($response)) {
        return '<p style="color: red;">Error: ' . $response->get_error_message() . '</p>';
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        return '<p style="color: red;">Error: API returned status code ' . $status_code . '</p>';
    }

    // Parse the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Handle data based on the API used
    if (!empty($game_id)) {
        // For the Game ID API, extract data directly
        $game_title = $data['Title'] ?? 'Unknown';
        $console_name = $data['ConsoleName'] ?? 'Unknown';
        $box_art = $data['ImageBoxArt'] ?? '';
        $image_ingame = $data['ImageIngame'] ?? '';
        $last_played = ''; // Not available in this endpoint
        $time_ago = ''; // Not applicable
        // Generate box art URL (using RetroAchievements image server)
        $box_art_url = "https://media.retroachievements.org/" . $box_art;
        $image_ingam_url = "https://media.retroachievements.org/" . $image_ingame;

    } else {
        // For the Recently Played Games API, get the first game
        if (empty($data) || !is_array($data)) {
            return '<p style="color: red;">No games found in the API response.</p>';
        }
        $last_game = $data[0];
        $game_title = $last_game['Title'] ?? 'Unknown';
        $console_name = $last_game['ConsoleName'] ?? 'Unknown';
        $box_art = $last_game['ImageBoxArt'] ?? '';
        $image_ingame = $last_game['ImageIngame'] ?? '';
        $last_played = $last_game['LastPlayed'] ?? 'Unknown';

        // Generate box art URL (using RetroAchievements image server)
        $box_art_url = "https://media.retroachievements.org/" . $box_art;
        $image_ingam_url = "https://media.retroachievements.org/" . $image_ingame;

        // Calculate time difference
        $last_played_time = new DateTime($last_played);
        $current_time = new DateTime();
        $interval = $last_played_time->diff($current_time);

        if ($interval->y > 0) {
            $time_ago = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            $time_ago = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            $time_ago = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            $time_ago = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            $time_ago = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            $time_ago = 'Just now';
        }
    }

    // Display the game information
    ob_start();
    ?>
    <div class="retroeh-container" style="background-image: url('<?php echo esc_url($image_ingam_url); ?>');">
        <div class="retroeh-box-art">
            <img src="<?php echo esc_url($box_art_url); ?>" alt="Box Art for <?php echo esc_attr($game_title); ?>">
        </div>
        <div class="retroeh-details">
            <h2><?php echo esc_html($game_title); ?></h2>
            <p><strong>Console:</strong> <?php echo esc_html($console_name); ?></p>
            <?php if (!empty($time_ago)) : ?>
                <p><strong>Last Played:</strong> <?php echo esc_html($time_ago); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('retroeh_game_display', 'retroeh_last_game_display_shortcode');

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
        'editor_script' => 'retroeh-block',
        'editor_style'  => 'retroeh-block-style',
        'render_callback' => 'retroeh_render_game_block',
        'attributes' => array(
            'api_key' => array(
                'type' => 'string',
                'default' => '',
            ),
            'username' => array(
                'type' => 'string',
                'default' => '',
            ),
            'game_id' => array(
                'type' => 'string',
                'default' => '',
            ),
        ),
    ) );
}
add_action( 'init', 'retroeh_register_block' );

// Render callback for the block
function retroeh_render_game_block( $attributes ) {
    ob_start();
    echo do_shortcode('[retroeh_game_display api_key="' . esc_attr($attributes['api_key']) . '" username="' . esc_attr($attributes['username']) . '" game_id="' . esc_attr($attributes['game_id']) . '"]');
    return ob_get_clean();
}
