<?php
/**
 * Home page body is the plugin-owned `forgedseo/home` pattern.
 *
 * Rsync deploys skip activation hooks, so this lives on init and retries
 * until page 4's post_content is the single core/pattern reference. The
 * previous body stays in post revisions. Opening Home in the block editor
 * expands the pattern into editable blocks; saving that detaches the page
 * from the plugin until this option is cleared.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_home_maybe_migrate', 20);
add_filter('body_class', 'forgedseo_core_home_body_class');

/**
 * @return string
 */
function forgedseo_core_home_option()
{
    return 'forgedseo_core_home';
}

/**
 * @return string
 */
function forgedseo_core_home_target_version()
{
    return '0.2.0';
}

/**
 * @return string
 */
function forgedseo_core_home_content()
{
    return '<!-- wp:pattern {"slug":"' . forgedseo_core_home_pattern_slug() . '"} /-->';
}

/**
 * @return void
 */
function forgedseo_core_home_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_home_option(), '');
    if (version_compare($done, forgedseo_core_home_target_version(), '>=')) {
        return;
    }

    if (!class_exists('WP_Block_Patterns_Registry')
        || !WP_Block_Patterns_Registry::get_instance()->is_registered(forgedseo_core_home_pattern_slug())
    ) {
        return;
    }

    $post_id = forgedseo_core_homepage_post_id();
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'page') {
        return;
    }

    if (trim((string) $post->post_content) !== forgedseo_core_home_content()) {
        if (function_exists('kses_remove_filters')) {
            kses_remove_filters();
        }

        $result = wp_update_post(
            wp_slash(
                array(
                    'ID'           => $post_id,
                    'post_content' => forgedseo_core_home_content(),
                )
            ),
            true
        );

        if (function_exists('kses_init_filters')) {
            kses_init_filters();
        }

        if (is_wp_error($result) || absint($result) !== $post_id) {
            return;
        }
    }

    update_option(forgedseo_core_home_option(), forgedseo_core_home_target_version(), true);
}

/**
 * @param string[] $classes
 * @return string[]
 */
function forgedseo_core_home_body_class($classes)
{
    if (is_front_page()) {
        $classes[] = 'fseo-home';
    }

    return $classes;
}
