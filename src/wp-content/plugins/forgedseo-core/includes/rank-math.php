<?php
/**
 * Expose Rank Math SEO post meta on the WordPress REST API.
 *
 * Rank Math stores focus keyword and meta description as ordinary post meta
 * but does not register those keys for /wp/v2. REST clients (application
 * passwords, MCP connectors) can then read and write them via the post/page
 * `meta` object without going through Rank Math's own admin routes.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'forgedseo_core_register_rank_math_rest_meta');

/**
 * Meta keys Rank Math reads for focus keyword and meta description.
 *
 * @return string[]
 */
function forgedseo_core_rank_math_rest_meta_keys()
{
    return array(
        'rank_math_focus_keyword',
        'rank_math_description',
    );
}

/**
 * Post types that expose Rank Math REST meta.
 *
 * @return string[]
 */
function forgedseo_core_rank_math_rest_meta_post_types()
{
    return array('post', 'page');
}

/**
 * Register Rank Math meta keys as REST-visible string fields.
 *
 * Hooked on rest_api_init (not init) so registration applies only on REST
 * requests. Rank Math's metabox and Gutenberg saves still use
 * update_post_meta on these same keys; we do not replace Rank Math's
 * sanitization on non-REST admin loads.
 *
 * @return void
 */
function forgedseo_core_register_rank_math_rest_meta()
{
    $args = array(
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => 'forgedseo_core_rank_math_rest_meta_auth',
    );

    foreach (forgedseo_core_rank_math_rest_meta_post_types() as $type) {
        foreach (forgedseo_core_rank_math_rest_meta_keys() as $key) {
            register_post_meta($type, $key, $args);
        }
    }
}

/**
 * Whether the current user may write Rank Math REST meta.
 *
 * @return bool
 */
function forgedseo_core_rank_math_rest_meta_auth()
{
    return current_user_can('edit_posts') || current_user_can('edit_pages');
}
