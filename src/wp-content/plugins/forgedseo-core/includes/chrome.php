<?php
/**
 * Custom Logo in TT5 header/footer chrome (not text site-title).
 *
 * Rsync deploys do not re-run activation hooks, so this lives on init and
 * retries until the lockup is the Custom Logo and both template parts render
 * core/site-logo. Runs after theme patterns register (init 10).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_chrome_maybe_migrate', 20);

/**
 * Option that records the last completed chrome logo migrate.
 *
 * @return string
 */
function forgedseo_core_chrome_logo_option()
{
    return 'forgedseo_core_chrome_logo';
}

/**
 * Production attachment id for cropped-forged-seo-app-logo.png (214×43).
 *
 * @return int
 */
function forgedseo_core_chrome_logo_attachment_id()
{
    return 33;
}

/**
 * site-logo block width for the header lockup (~32–36px tall).
 *
 * @return int
 */
function forgedseo_core_chrome_header_logo_width()
{
    return 170;
}

/**
 * site-logo block width for the footer lockup (~40px tall).
 *
 * @return int
 */
function forgedseo_core_chrome_footer_logo_width()
{
    return 200;
}

/**
 * Active block theme stylesheet this migrate writes template parts for.
 *
 * @return string
 */
function forgedseo_core_chrome_theme_slug()
{
    return 'twentytwentyfive';
}

/**
 * Alt text for the header/footer lockup.
 *
 * @return string
 */
function forgedseo_core_chrome_logo_alt()
{
    return 'ForgedSEO';
}

/**
 * @return string
 */
function forgedseo_core_chrome_logo_block($width)
{
    return sprintf(
        '<!-- wp:site-logo {"width":%d,"shouldSyncIcon":false} /-->',
        (int) $width
    );
}

/**
 * One-shot migrate after deploy/upgrade: Custom Logo + header/footer site-logo.
 *
 * @return void
 */
function forgedseo_core_chrome_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_chrome_logo_option(), '');
    if (version_compare($done, '0.1.6', '>=')) {
        return;
    }

    if (!function_exists('get_block_template') || !function_exists('get_stylesheet')) {
        return;
    }

    if (get_stylesheet() !== forgedseo_core_chrome_theme_slug()) {
        return;
    }

    forgedseo_core_chrome_ensure_logo_alt();

    if (!forgedseo_core_chrome_ensure_custom_logo()) {
        return;
    }

    $header_ok = forgedseo_core_chrome_migrate_template_part(
        'header',
        forgedseo_core_chrome_header_logo_width()
    );
    $footer_ok = forgedseo_core_chrome_migrate_template_part(
        'footer',
        forgedseo_core_chrome_footer_logo_width()
    );
    if (!$header_ok || !$footer_ok) {
        return;
    }

    update_option(forgedseo_core_chrome_logo_option(), '0.1.6', true);
}

/**
 * Point custom_logo at attachment 33 when empty, missing, or the 16:9 JPEG.
 *
 * @return bool True when the theme_mod is a usable lockup (33, or another
 *              valid image that is not the oversized marketing JPEG).
 */
function forgedseo_core_chrome_ensure_custom_logo()
{
    $desired = forgedseo_core_chrome_logo_attachment_id();
    $current = absint(get_theme_mod('custom_logo', 0));

    if ($current === $desired && forgedseo_core_chrome_attachment_is_image($desired)) {
        return true;
    }

    if (!forgedseo_core_chrome_should_set_custom_logo($current)) {
        return forgedseo_core_chrome_attachment_is_image($current);
    }

    if (!forgedseo_core_chrome_attachment_is_image($desired)) {
        return false;
    }

    set_theme_mod('custom_logo', $desired);

    return absint(get_theme_mod('custom_logo', 0)) === $desired;
}

/**
 * Whether custom_logo should be forced to the 214×43 lockup.
 *
 * @param int $current_id
 * @return bool
 */
function forgedseo_core_chrome_should_set_custom_logo($current_id)
{
    $current_id = absint($current_id);
    $desired = forgedseo_core_chrome_logo_attachment_id();

    if ($current_id === $desired) {
        return false;
    }

    if ($current_id <= 0) {
        return true;
    }

    if (!forgedseo_core_chrome_attachment_is_image($current_id)) {
        return true;
    }

    if (function_exists('forgedseo_core_attachment_is_oversized_logo')
        && forgedseo_core_attachment_is_oversized_logo($current_id)) {
        return true;
    }

    return false;
}

/**
 * @param int $attachment_id
 * @return bool
 */
function forgedseo_core_chrome_attachment_is_image($attachment_id)
{
    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return false;
    }

    return get_post_type($attachment_id) === 'attachment' && wp_attachment_is_image($attachment_id);
}

/**
 * Set attachment 33 alt to ForgedSEO when empty.
 *
 * @return void
 */
function forgedseo_core_chrome_ensure_logo_alt()
{
    $attachment_id = forgedseo_core_chrome_logo_attachment_id();
    if (!forgedseo_core_chrome_attachment_is_image($attachment_id)) {
        return;
    }

    $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    if (is_string($alt) && trim($alt) !== '') {
        return;
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', forgedseo_core_chrome_logo_alt());
}

/**
 * Expand patterns, replace site-title with site-logo, and persist the part.
 *
 * @param string $slug  header or footer
 * @param int    $width site-logo width
 * @return bool
 */
function forgedseo_core_chrome_migrate_template_part($slug, $width)
{
    $slug = sanitize_key($slug);
    $width = (int) $width;
    if ($slug === '' || $width <= 0) {
        return false;
    }

    $id = forgedseo_core_chrome_theme_slug() . '//' . $slug;
    $template = get_block_template($id, 'wp_template_part');
    if (!$template || empty($template->content)) {
        return false;
    }

    $content = forgedseo_core_chrome_expand_patterns((string) $template->content);
    if ($content === '') {
        return false;
    }

    if (forgedseo_core_chrome_content_is_migrated($content, $width)) {
        return true;
    }

    $updated = forgedseo_core_chrome_replace_brand_mark($content, $width);
    if ($updated === '' || $updated === $content) {
        return false;
    }

    if (!forgedseo_core_chrome_content_is_migrated($updated, $width)) {
        return false;
    }

    if (!forgedseo_core_chrome_write_template_part_content($template, $updated)) {
        return false;
    }

    $saved = get_block_template($id, 'wp_template_part');
    if (!$saved || empty($saved->content)) {
        return false;
    }

    $saved_content = forgedseo_core_chrome_expand_patterns((string) $saved->content);

    return forgedseo_core_chrome_content_is_migrated($saved_content, $width);
}

/**
 * True when markup has the sized site-logo and no site-title brand text.
 *
 * @param string $content
 * @param int    $width
 * @return bool
 */
function forgedseo_core_chrome_content_is_migrated($content, $width)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    if (forgedseo_core_chrome_content_has_site_title($content)) {
        return false;
    }

    return forgedseo_core_chrome_content_has_logo_width($content, $width);
}

/**
 * @param string $content
 * @return bool
 */
function forgedseo_core_chrome_content_has_site_title($content)
{
    return is_string($content) && (bool) preg_match('/<!--\s+wp:site-title\b/', $content);
}

/**
 * @param string $content
 * @param int    $width
 * @return bool
 */
function forgedseo_core_chrome_content_has_logo_width($content, $width)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    $width = (int) $width;
    if ($width <= 0) {
        return false;
    }

    if (!preg_match('/<!--\s+wp:site-logo\b[^>]*\/-->/', $content, $match)) {
        return false;
    }

    $comment = $match[0];

    return false !== strpos($comment, '"width":' . $width)
        && false !== strpos($comment, '"shouldSyncIcon":false');
}

/**
 * Replace site-title (or a mismatched site-logo) with the lockup block.
 *
 * Surrounding groups, nav, tagline, and contact blocks are left intact.
 *
 * @param string $content
 * @param int    $width
 * @return string Unchanged content when no brand mark block is found.
 */
function forgedseo_core_chrome_replace_brand_mark($content, $width)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $logo = forgedseo_core_chrome_logo_block($width);
    $replaced = preg_replace(
        '/<!--\s+wp:site-title\b(?:\s+\{.*?\})?\s+\/-->/s',
        $logo,
        $content,
        1,
        $count
    );
    if (is_string($replaced) && $count > 0) {
        return $replaced;
    }

    $replaced = preg_replace(
        '/<!--\s+wp:site-title\b.*?-->.*?<!--\s+\/wp:site-title\s+-->/s',
        $logo,
        $content,
        1,
        $count
    );
    if (is_string($replaced) && $count > 0) {
        return $replaced;
    }

    $replaced = preg_replace(
        '/<!--\s+wp:site-logo\b[^>]*\/-->/',
        $logo,
        $content,
        1,
        $count
    );
    if (is_string($replaced) && $count > 0) {
        return $replaced;
    }

    return $content;
}

/**
 * Resolve core/pattern includes so theme header.html becomes real blocks.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_chrome_expand_patterns($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    if (false === strpos($content, 'wp:pattern')) {
        return $content;
    }

    if (!function_exists('parse_blocks') || !function_exists('serialize_blocks')) {
        return $content;
    }

    $blocks = parse_blocks($content);
    if (!is_array($blocks)) {
        return $content;
    }

    if (function_exists('resolve_pattern_blocks')) {
        $blocks = resolve_pattern_blocks($blocks);
    } else {
        $blocks = forgedseo_core_chrome_expand_pattern_blocks($blocks);
    }

    $serialized = serialize_blocks($blocks);

    return is_string($serialized) ? $serialized : $content;
}

/**
 * Fallback pattern expansion when resolve_pattern_blocks() is unavailable.
 *
 * @param array<int, array<string, mixed>> $blocks
 * @return array<int, array<string, mixed>>
 */
function forgedseo_core_chrome_expand_pattern_blocks($blocks)
{
    if (!is_array($blocks)) {
        return array();
    }

    $out = array();
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $name = isset($block['blockName']) ? (string) $block['blockName'] : '';
        if ($name === 'core/pattern') {
            $slug = '';
            if (isset($block['attrs']['slug']) && is_string($block['attrs']['slug'])) {
                $slug = $block['attrs']['slug'];
            }
            $inner = forgedseo_core_chrome_pattern_content($slug);
            if ($inner !== '') {
                $parsed = parse_blocks($inner);
                $out = array_merge($out, forgedseo_core_chrome_expand_pattern_blocks($parsed));
                continue;
            }
        }

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $block['innerBlocks'] = forgedseo_core_chrome_expand_pattern_blocks($block['innerBlocks']);
        }

        $out[] = $block;
    }

    return $out;
}

/**
 * @param string $slug
 * @return string
 */
function forgedseo_core_chrome_pattern_content($slug)
{
    if (!is_string($slug) || $slug === '' || !class_exists('WP_Block_Patterns_Registry')) {
        return '';
    }

    $registry = WP_Block_Patterns_Registry::get_instance();
    if (!is_object($registry) || !method_exists($registry, 'is_registered') || !$registry->is_registered($slug)) {
        return '';
    }

    $registered = $registry->get_registered($slug);
    if (!is_array($registered) || empty($registered['content']) || !is_string($registered['content'])) {
        return '';
    }

    return $registered['content'];
}

/**
 * Create or update a customized wp_template_part (REST-equivalent).
 *
 * Front-end init is unauthenticated, so this avoids tax_input / kses which
 * would strip block comments or fail to assign wp_theme.
 *
 * @param object $template WP_Block_Template
 * @param string $content  Block markup
 * @return bool
 */
function forgedseo_core_chrome_write_template_part_content($template, $content)
{
    if (!is_object($template) || !is_string($content) || $content === '') {
        return false;
    }

    if (function_exists('kses_remove_filters')) {
        kses_remove_filters();
    }

    $ok = false;

    if (isset($template->source, $template->wp_id)
        && $template->source === 'custom'
        && absint($template->wp_id) > 0
    ) {
        $result = wp_update_post(
            wp_slash(
                array(
                    'ID'           => absint($template->wp_id),
                    'post_content' => $content,
                )
            ),
            true
        );
        $ok = !is_wp_error($result) && absint($result) > 0;
    } else {
        $title = isset($template->title) ? (string) $template->title : '';
        $excerpt = isset($template->description) ? (string) $template->description : '';
        $slug = isset($template->slug) ? (string) $template->slug : '';
        $post_id = wp_insert_post(
            wp_slash(
                array(
                    'post_type'      => 'wp_template_part',
                    'post_status'    => 'publish',
                    'post_name'      => $slug,
                    'post_title'     => $title,
                    'post_excerpt'   => $excerpt,
                    'post_content'   => $content,
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                )
            ),
            true
        );

        if (!is_wp_error($post_id) && absint($post_id) > 0) {
            $theme = !empty($template->theme)
                ? (string) $template->theme
                : forgedseo_core_chrome_theme_slug();
            wp_set_post_terms(absint($post_id), array($theme), 'wp_theme');
            $area = !empty($template->area) ? (string) $template->area : $slug;
            if ($area !== '') {
                wp_set_post_terms(absint($post_id), array($area), 'wp_template_part_area');
            }
            $ok = true;
        }
    }

    if (function_exists('kses_init_filters')) {
        kses_init_filters();
    }

    return $ok;
}
