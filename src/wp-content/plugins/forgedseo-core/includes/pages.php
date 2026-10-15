<?php
/**
 * Inner pages and blog/404 templates render plugin-owned patterns.
 *
 * Same contract as includes/home.php: on init, page bodies become a single
 * core/pattern reference (Privacy and Terms are published or created), and the
 * Twenty Twenty-Five `home`, `single` and `404` templates become header +
 * pattern + footer. Previous page bodies stay in revisions. Saving one of these
 * pages in the block editor expands the pattern and detaches it until the
 * `forgedseo_core_pages` option is cleared.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_pages_maybe_migrate', 21);
add_filter('body_class', 'forgedseo_core_pages_body_class');
add_filter('query_loop_block_query_vars', 'forgedseo_core_pages_exclude_current', 10, 2);

/**
 * @return string
 */
function forgedseo_core_pages_option()
{
    return 'forgedseo_core_pages';
}

/**
 * @return string
 */
function forgedseo_core_pages_target_version()
{
    return '0.3.0';
}

/**
 * Page path => pattern slug, title, and whether to publish/create the page.
 *
 * @return array<string, array{pattern:string, title:string, create?:bool, privacy?:bool}>
 */
function forgedseo_core_pages_map()
{
    return array(
        'service'        => array('pattern' => 'forgedseo/page-service', 'title' => 'Managed Service'),
        'enterprise'     => array('pattern' => 'forgedseo/page-enterprise', 'title' => 'Enterprise PaaS'),
        'about'          => array('pattern' => 'forgedseo/page-about', 'title' => 'About'),
        'privacy-policy' => array('pattern' => 'forgedseo/page-privacy', 'title' => 'Privacy Policy', 'create' => true, 'privacy' => true),
        'terms'          => array('pattern' => 'forgedseo/page-terms', 'title' => 'Terms of Service', 'create' => true),
    );
}

/**
 * Template slug => pattern slug.
 *
 * @return array<string, string>
 */
function forgedseo_core_pages_templates()
{
    return array(
        'home'   => 'forgedseo/template-blog',
        'single' => 'forgedseo/template-single',
        '404'    => 'forgedseo/template-404',
    );
}

/**
 * @param string $slug
 * @return string
 */
function forgedseo_core_pattern_ref($slug)
{
    return '<!-- wp:pattern {"slug":"' . $slug . '"} /-->';
}

/**
 * @param string $pattern
 * @return string
 */
function forgedseo_core_pages_template_content($pattern)
{
    $theme = forgedseo_core_page_h1_theme_slug();

    return '<!-- wp:template-part {"slug":"header","theme":"' . $theme . '"} /-->' . "\n\n"
        . '<!-- wp:group {"tagName":"main","className":"fseo-main","layout":{"type":"default"}} -->' . "\n"
        . '<main class="wp-block-group fseo-main">' . forgedseo_core_pattern_ref($pattern) . '</main>' . "\n"
        . '<!-- /wp:group -->' . "\n\n"
        . '<!-- wp:template-part {"slug":"footer","theme":"' . $theme . '"} /-->';
}

/**
 * @return void
 */
function forgedseo_core_pages_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_pages_option(), '');
    if (version_compare($done, forgedseo_core_pages_target_version(), '>=')) {
        return;
    }

    if (!class_exists('WP_Block_Patterns_Registry')
        || !function_exists('get_block_template')
        || get_stylesheet() !== forgedseo_core_page_h1_theme_slug()
    ) {
        return;
    }

    $registry = WP_Block_Patterns_Registry::get_instance();
    $patterns = array_merge(wp_list_pluck(forgedseo_core_pages_map(), 'pattern'), array_values(forgedseo_core_pages_templates()));
    foreach ($patterns as $pattern) {
        if (!$registry->is_registered($pattern)) {
            return;
        }
    }

    $ok = true;
    foreach (forgedseo_core_pages_map() as $path => $page) {
        $ok = forgedseo_core_pages_migrate_page($path, $page) && $ok;
    }
    foreach (forgedseo_core_pages_templates() as $slug => $pattern) {
        $ok = forgedseo_core_pages_migrate_template($slug, $pattern) && $ok;
    }

    if ($ok) {
        update_option(forgedseo_core_pages_option(), forgedseo_core_pages_target_version(), true);
    }
}

/**
 * @param string $path
 * @param array  $page
 * @return WP_Post|null
 */
function forgedseo_core_pages_find($path, $page)
{
    if (!empty($page['privacy'])) {
        $privacy = get_post((int) get_option('wp_page_for_privacy_policy'));
        if ($privacy instanceof WP_Post && $privacy->post_type === 'page' && $privacy->post_status !== 'trash') {
            return $privacy;
        }
    }

    $post = get_page_by_path($path, OBJECT, 'page');

    return $post instanceof WP_Post ? $post : null;
}

/**
 * @param string $path
 * @param array  $page
 * @return bool
 */
function forgedseo_core_pages_migrate_page($path, $page)
{
    $content = forgedseo_core_pattern_ref($page['pattern']);
    $create = !empty($page['create']);
    $post = forgedseo_core_pages_find($path, $page);

    $data = array();
    if (!$post) {
        if (!$create) {
            return true;
        }
        $data = array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_name'      => $path,
            'post_title'     => $page['title'],
            'post_content'   => $content,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        );
    } else {
        if (trim((string) $post->post_content) !== $content) {
            $data['post_content'] = $content;
        }
        if ($create && $post->post_status !== 'publish') {
            $data['post_status'] = 'publish';
        }
        if (empty($data)) {
            return true;
        }
        $data['ID'] = $post->ID;
    }

    if (function_exists('kses_remove_filters')) {
        kses_remove_filters();
    }

    $result = isset($data['ID']) ? wp_update_post(wp_slash($data), true) : wp_insert_post(wp_slash($data), true);

    if (function_exists('kses_init_filters')) {
        kses_init_filters();
    }

    return !is_wp_error($result) && absint($result) > 0;
}

/**
 * @param string $slug
 * @param string $pattern
 * @return bool
 */
function forgedseo_core_pages_migrate_template($slug, $pattern)
{
    $template = get_block_template(forgedseo_core_page_h1_theme_slug() . '//' . $slug, 'wp_template');
    if (!$template) {
        return false;
    }

    $content = forgedseo_core_pages_template_content($pattern);
    if (trim((string) $template->content) === $content) {
        return true;
    }

    return forgedseo_core_page_h1_write_template_content($template, $content);
}

/**
 * Views whose whole body comes from a forgedseo pattern.
 *
 * @return bool
 */
function forgedseo_core_pages_is_designed()
{
    if (is_front_page() || is_home() || is_404() || is_singular('post')) {
        return true;
    }

    if (is_page()) {
        $post = get_queried_object();

        return $post instanceof WP_Post
            && strpos(ltrim((string) $post->post_content), '<!-- wp:pattern {"slug":"forgedseo/') === 0;
    }

    return false;
}

/**
 * @param string[] $classes
 * @return string[]
 */
function forgedseo_core_pages_body_class($classes)
{
    if (forgedseo_core_pages_is_designed()) {
        $classes[] = 'fseo-designed';
    }

    return $classes;
}

/**
 * Query Loops flagged with `fseoExcludeCurrent` skip the post being viewed.
 *
 * @param array    $query
 * @param WP_Block $block
 * @return array
 */
function forgedseo_core_pages_exclude_current($query, $block)
{
    if (!empty($block->context['query']['fseoExcludeCurrent']) && is_singular()) {
        $exclude = isset($query['post__not_in']) ? (array) $query['post__not_in'] : array();
        $exclude[] = get_queried_object_id();
        $query['post__not_in'] = $exclude;
    }

    return $query;
}
