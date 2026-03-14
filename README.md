Retro Eh!
A WordPress plugin that provides a custom Gutenberg block to display RetroAchievements game details.
The block can be configured to show the latest game played by a user or details of a specific game using its Game ID.

## Features
- **Customizable Gutenberg Block**: Add the block to pages, posts, or sidebars.
- **Game Display Options**: Display the latest game played by a username or specify a Game ID to show details for a particular game.
- **Secure API Key Storage**: Your RetroAchievements API key is stored in WordPress options via a dedicated settings page — never exposed in post content or block attributes.
- **Transient Caching**: API responses are cached for one hour using WordPress transients, minimizing external HTTP calls and improving page load times.
- **Conditional Asset Loading**: Plugin CSS and Google Fonts are only enqueued on pages that actually contain the block or shortcode.
- **Stylish Design**: Neon-themed design with responsive layouts optimized for desktop and mobile devices. Theme developers can disable the built-in stylesheet via the `retroeh_use_default_styles` filter and supply their own CSS.

## Installation
1. Download the zip from the repository.
2. Navigate to your WordPress admin dashboard and go to **Plugins > Add New Plugin > Upload Plugin**.
3. Upload the zip.
4. Locate **Retro Eh!** and click **Activate**.

## Configuration
### Set Your API Key
Before using the block or shortcode, store your RetroAchievements API key in the plugin settings:

1. In your WordPress admin dashboard, go to **Settings > RetroEh!**.
2. Enter your **RetroAchievements API Key** in the field provided.
3. Click **Save Changes**.

Keeping the key in settings prevents it from being stored in post content and keeps it out of the block editor.

## How to Use the Block
### Add the Block to a Page or Post
1. Navigate to **Pages** or **Posts** in the WordPress admin dashboard.
2. Open the desired page/post or create a new one.
3. In the block editor:
   - Click the **Add Block** (`+`) button.
   - Search for **RetroEH Game Display**.
   - Add the block to your page or post.
4. Configure the block in the **Inspector Controls** sidebar:
   - Provide a **Username** to display the latest game played by that user.
   - (Optional) Specify a **Game ID** to show details for a specific game. Game ID takes precedence over username when both are provided.
5. Save or publish the page/post.

### Add the Block to a Sidebar or Widget Area
1. Navigate to **Appearance > Widgets** in the WordPress admin dashboard.
2. Click the **Add Block** (`+`) button in the desired widget area (e.g., Sidebar).
3. Search for **RetroEH Game Display**.
4. Add the block to the widget area.
5. Configure the block in the **Inspector Controls** sidebar:
   - Provide a **Username** to display the latest game played by that user.
   - (Optional) Specify a **Game ID** to show details for a specific game.
6. Save the widget.

## Shortcode
You can also display game details using a shortcode:

```
[retroeh_game_display username="YourUsername"]
[retroeh_game_display game_id="1234"]
```

The API key is read automatically from the plugin settings. The `api_key` attribute is accepted as a backward-compatibility fallback but is no longer recommended.

## Block Attributes
- **Username**: (Optional) The RetroAchievements username to display the latest game played.
- **Game ID**: (Optional) The ID of the game to display specific details. If provided, this takes precedence over the username.

> **Note:** The API key is no longer a block attribute. Configure it once under **Settings > RetroEh!**.

## Theme Developer Customisation

### Overriding the Default Styles

The plugin ships with a built-in stylesheet (`src/style.css`) that applies the neon-themed design.
Theme developers can disable this stylesheet and provide their own CSS by hooking into the
`retroeh_use_default_styles` filter and returning `false`:

```php
add_filter( 'retroeh_use_default_styles', '__return_false' );
```

Once the default stylesheet is disabled, you can style the widget using the plugin's BEM-style class names:

| Class | Element |
|---|---|
| `.retroeh-container` | Outer wrapper / background image container |
| `.retroeh-box-art` | Box art image wrapper |
| `.retroeh-box-art img` | The box art `<img>` element |
| `.retroeh-details` | Game details text wrapper |
| `.retroeh-details h2` | Game title heading |
| `.retroeh-details p` | Console and last-played paragraphs |

Place this snippet in your theme's `functions.php` (or in a site-specific plugin) and enqueue your own stylesheet as normal via `wp_enqueue_style()`.

## Security
- The RetroAchievements API key is stored in `wp_options` via `register_setting` with `sanitize_text_field` as the sanitize callback.
- All shortcode attributes are sanitized (`sanitize_text_field` for strings, `absint` for the game ID).
- All output is escaped using WordPress escaping functions (`esc_html`, `esc_url`, `esc_attr`).
- API error messages and HTTP status codes are sanitized before being displayed.
- Invalid JSON responses and malformed timestamps are handled gracefully with user-facing error messages.

## Performance
- **Transient caching**: API responses are cached for one hour. Cache keys are scoped to an 8-character API key hash so different credentials never share cached data.
- **HTTP timeout**: `wp_remote_get` uses a 10-second timeout to prevent slow API responses from blocking page rendering.
- **Conditional asset loading**: Plugin CSS and Google Fonts are only enqueued on pages containing the `retroeh/game-display` block or `retroeh_game_display` shortcode.

## Screenshots
### Block Configuration in the Editor
(Add your screenshot URL here)
### Game Display on a Page
(Add your screenshot URL here)
### Mobile Layout
(Add your screenshot URL here)

## Contributing
Feel free to fork the repository and submit pull requests. For any issues, please open a ticket in the
[GitHub Issues](https://github.com/bythegram/retroeh/issues) section.
