---
applyTo: "**/*.php"
---

# WordPress PHP Best Practices for Retro Eh!

Follow these conventions in all PHP files in this repository.

## Naming & Namespacing
- Prefix every function, hook callback, option name, and transient key with `retroeh_`.
- Use `snake_case` for PHP function and variable names, matching WordPress core conventions.

## Security — Input Sanitisation
- Sanitize all external input before use.
- Use `sanitize_text_field()` for plain-text strings from `$_POST`, shortcode attributes, or option values.
- Use `absint()` for integer IDs (never cast with `(int)` alone when the value comes from user input).
- Use `esc_url_raw()` when storing a URL in the database.
- Never trust data returned from external APIs without validating structure and type.

## Security — Output Escaping
- Escape output as late as possible, immediately before rendering.
- Use `esc_html()` for plain text in HTML context.
- Use `esc_attr()` for HTML attribute values.
- Use `esc_url()` for URLs in `href`, `src`, and CSS `url()` values.
- Use `wp_kses_post()` only when a limited set of HTML tags is intentionally allowed.

## Security — Settings & API Keys
- Store sensitive values (e.g., API keys) in `wp_options` via `register_setting()` with a `sanitize_callback`.
- Never store sensitive data in post meta, block attributes, or shortcode attributes as the primary path.
- Capability-check all admin callbacks: `if ( ! current_user_can( 'manage_options' ) ) { return; }`.

## HTTP Requests
- Use `wp_remote_get()` / `wp_remote_post()` for all outbound HTTP calls.
- Always pass `[ 'timeout' => 10 ]` (or an appropriate value) in the args array.
- Check for `WP_Error` with `is_wp_error()` before accessing response data.
- Validate the HTTP response code with `wp_remote_retrieve_response_code()`.
- Guard `json_decode` results: check for `null` before accessing decoded keys.

## Caching
- Cache all external API responses with `set_transient()` / `get_transient()`.
- Use `HOUR_IN_SECONDS`, `DAY_IN_SECONDS`, etc. for TTL values.
- Scope cache keys to the relevant data identifiers and an API key hash so that different configurations cannot share stale data.

## Asset Enqueueing
- Register and enqueue scripts/styles with `wp_enqueue_script()` / `wp_enqueue_style()`.
- Enqueue assets conditionally: check `has_block()` or `has_shortcode()` in a `wp_enqueue_scripts` callback rather than loading assets globally.
- Use `plugin_dir_url( __FILE__ )` for asset URL paths.
- Pass `null` as the `$ver` argument for third-party CDN resources to preserve CDN caching headers.

## Internationalisation
- Wrap every user-facing string in `__()`, `_e()`, `esc_html__()`, or `esc_html_e()` using the `'retroeh'` text domain.

## Error Handling
- Wrap `DateTime` / `DateInterval` code in `try/catch( Exception $e )` blocks.
- Return descriptive, escaped error strings from shortcode callbacks rather than letting PHP warnings surface to users.

## README Updates
**After any change to plugin behaviour, update `README.md`:**
- Features section → reflects current capabilities.
- Configuration section → reflects current settings UI.
- Block Attributes section → reflects current block parameters.
- Shortcode section → reflects current shortcode name and parameters.
- Security section → reflects current sanitisation and escaping approach.
- Performance section → reflects current caching and asset loading strategy.
