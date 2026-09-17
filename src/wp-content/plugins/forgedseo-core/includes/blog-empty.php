<?php
/**
 * Branded Query Loop empty state on the Twenty Twenty-Five posts index.
 *
 * `/blog/` is the posts page (static front + posts page), so visitors see the
 * `home` template (index as fallback), not page 37’s body. Rsync deploys skip
 * activation hooks, so this lives on init and retries until customized
 * home/index `wp_template` posts have ForgedSEO no-results copy instead of
 * TT5’s stock “Sorry, but nothing was found…”.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_blog_empty_maybe_migrate', 20);

/**
 * Option that records the last completed blog empty-state migrate.
 *
 * @return string
 */
function forgedseo_core_blog_empty_option()
{
    return 'forgedseo_core_blog_empty';
}

/**
 * Active block theme stylesheet this migrate writes posts templates for.
 *
 * @return string
 */
function forgedseo_core_blog_empty_theme_slug()
{
    return 'twentytwentyfive';
}

/**
 * Version written to the option after a successful migrate.
 *
 * @return string
 */
function forgedseo_core_blog_empty_target_version()
{
    return '0.1.9';
}

/**
 * Posts-index template slugs: `home` when a static front page is set, `index` as fallback.
 *
 * @return array<int, string>
 */
function forgedseo_core_blog_empty_template_slugs()
{
    return array('home', 'index');
}

/**
 * Phrase that identifies TT5’s stock query-no-results copy.
 *
 * @return string
 */
function forgedseo_core_blog_empty_stock_copy_needle()
{
    return 'Sorry, but nothing was found.';
}

/**
 * Lead heading inside query-no-results (H2 so the template H1 “Blog” stays unique).
 *
 * @return string
 */
function forgedseo_core_blog_empty_heading()
{
    return 'Posts coming soon';
}

/**
 * One-liner matching Home / AIOSEO blog voice.
 *
 * @return string
 */
function forgedseo_core_blog_empty_lede()
{
    return 'Insights and playbooks from the ForgedSEO agentic content engine — research, publish, and compound.';
}

/**
 * Primary + outline CTAs, same destinations as Home.
 *
 * @return array<int, array{text:string, href:string, style:string}>
 */
function forgedseo_core_blog_empty_ctas()
{
    return array(
        array(
            'text'  => 'Managed Service',
            'href'  => '/service/',
            'style' => 'primary',
        ),
        array(
            'text'  => 'Enterprise',
            'href'  => '/enterprise/',
            'style' => 'outline',
        ),
        array(
            'text'  => 'Email us',
            'href'  => 'mailto:forgedseo@spenpo.com',
            'style' => 'outline',
        ),
    );
}

/**
 * One-shot migrate after deploy/upgrade: branded query-no-results on home/index.
 *
 * @return void
 */
function forgedseo_core_blog_empty_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_blog_empty_option(), '');
    if (version_compare($done, forgedseo_core_blog_empty_target_version(), '>=')) {
        return;
    }

    if (!function_exists('get_block_template') || !function_exists('get_stylesheet')) {
        return;
    }

    if (get_stylesheet() !== forgedseo_core_blog_empty_theme_slug()) {
        return;
    }

    if (!forgedseo_core_blog_empty_migrate_posts_templates()) {
        return;
    }

    update_option(forgedseo_core_blog_empty_option(), forgedseo_core_blog_empty_target_version(), true);
}

/**
 * Customize each present home/index template that still has a Query Loop.
 *
 * @return bool
 */
function forgedseo_core_blog_empty_migrate_posts_templates()
{
    $attempted = false;

    foreach (forgedseo_core_blog_empty_template_slugs() as $slug) {
        $slug = sanitize_key($slug);
        if ($slug === '') {
            continue;
        }

        $id = forgedseo_core_blog_empty_theme_slug() . '//' . $slug;
        $template = get_block_template($id, 'wp_template');
        if (!$template || empty($template->content)) {
            continue;
        }

        $attempted = true;
        if (!forgedseo_core_blog_empty_migrate_template($template, $id)) {
            return false;
        }
    }

    return $attempted;
}

/**
 * Expand patterns, swap query-no-results inner blocks, persist customized template.
 *
 * @param object $template WP_Block_Template
 * @param string $id       theme//slug
 * @return bool
 */
function forgedseo_core_blog_empty_migrate_template($template, $id)
{
    if (!is_object($template) || !is_string($id) || $id === '') {
        return false;
    }

    $content = forgedseo_core_blog_empty_expand_patterns((string) $template->content);
    if ($content === '') {
        return false;
    }

    if (forgedseo_core_blog_empty_content_is_migrated($content)) {
        return true;
    }

    $updated = forgedseo_core_blog_empty_prepare_template_content($content);
    if (!forgedseo_core_blog_empty_content_is_migrated($updated)) {
        return false;
    }

    if ($updated === $content) {
        return true;
    }

    if (!function_exists('forgedseo_core_page_h1_write_template_content')) {
        return false;
    }

    if (!forgedseo_core_page_h1_write_template_content($template, $updated)) {
        return false;
    }

    $saved = get_block_template($id, 'wp_template');
    if (!$saved || empty($saved->content)) {
        return false;
    }

    $saved_content = forgedseo_core_blog_empty_expand_patterns((string) $saved->content);

    return forgedseo_core_blog_empty_content_is_migrated($saved_content);
}

/**
 * Expand theme patterns then replace query-no-results inner blocks.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_blog_empty_prepare_template_content($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $updated = forgedseo_core_blog_empty_expand_patterns($content);
    $updated = forgedseo_core_blog_empty_replace_no_results($updated);

    return is_string($updated) ? $updated : '';
}

/**
 * Resolve core/pattern includes (home.html uses hidden-blog-heading + template-query-loop).
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_blog_empty_expand_patterns($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    if (function_exists('forgedseo_core_chrome_expand_patterns')) {
        $expanded = forgedseo_core_chrome_expand_patterns($content);

        return is_string($expanded) && $expanded !== '' ? $expanded : $content;
    }

    return $content;
}

/**
 * True when markup keeps the Query Loop and has branded no-results (no stock copy).
 *
 * @param string $content
 * @return bool
 */
function forgedseo_core_blog_empty_content_is_migrated($content)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    if (forgedseo_core_blog_empty_content_has_stock_copy($content)) {
        return false;
    }

    if (!forgedseo_core_blog_empty_content_has_query_loop($content)) {
        return false;
    }

    if (!preg_match('/<!--\s+wp:query-no-results\b/', $content)) {
        return false;
    }

    if (false === strpos($content, forgedseo_core_blog_empty_heading())) {
        return false;
    }

    if (false === strpos($content, forgedseo_core_blog_empty_lede())) {
        return false;
    }

    foreach (forgedseo_core_blog_empty_ctas() as $cta) {
        if (false === strpos($content, $cta['href']) || false === strpos($content, $cta['text'])) {
            return false;
        }
    }

    return true;
}

/**
 * @param string $content
 * @return bool
 */
function forgedseo_core_blog_empty_content_has_stock_copy($content)
{
    return is_string($content) && false !== strpos($content, forgedseo_core_blog_empty_stock_copy_needle());
}

/**
 * Query, post-template, and pagination must remain; the outer Blog H1 is separate.
 *
 * @param string $content
 * @return bool
 */
function forgedseo_core_blog_empty_content_has_query_loop($content)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    return (bool) preg_match('/<!--\s+wp:query\b/', $content)
        && (bool) preg_match('/<!--\s+wp:post-template\b/', $content)
        && (bool) preg_match('/<!--\s+wp:query-pagination\b/', $content);
}

/**
 * Replace inner blocks of every core/query-no-results; keep the wrapper.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_blog_empty_replace_no_results($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $inner = forgedseo_core_blog_empty_inner_blocks();
    $replacement = "<!-- wp:query-no-results -->\n" . $inner . "\n<!-- /wp:query-no-results -->";

    $updated = preg_replace(
        '/<!--\s+wp:query-no-results\b[^>]*-->.*?<!--\s+\/wp:query-no-results\s+-->/s',
        $replacement,
        $content,
        -1,
        $count
    );
    if (is_string($updated) && $count > 0) {
        return $updated;
    }

    $updated = preg_replace(
        '/<!--\s+wp:query-no-results\b[^>]*\/-->/',
        $replacement,
        $content,
        -1,
        $count
    );

    return (is_string($updated) && $count > 0) ? $updated : $content;
}

/**
 * Inner blocks for query-no-results (heading, lede, primary + outline buttons).
 *
 * Uses default TT5 button markup so forgedseo.css tokens apply — no one-off hex.
 *
 * @return string
 */
function forgedseo_core_blog_empty_inner_blocks()
{
    $heading = forgedseo_core_blog_empty_heading();
    $lede = forgedseo_core_blog_empty_lede();

    $buttons = '';
    foreach (forgedseo_core_blog_empty_ctas() as $cta) {
        $text = $cta['text'];
        $href = $cta['href'];
        if ($cta['style'] === 'outline') {
            $buttons .= sprintf(
                "<!-- wp:button {\"className\":\"is-style-outline\"} -->\n"
                . "<div class=\"wp-block-button is-style-outline\"><a class=\"wp-block-button__link wp-element-button\" href=\"%s\">%s</a></div>\n"
                . "<!-- /wp:button -->\n\n",
                $href,
                $text
            );
        } else {
            $buttons .= sprintf(
                "<!-- wp:button -->\n"
                . "<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"%s\">%s</a></div>\n"
                . "<!-- /wp:button -->\n\n",
                $href,
                $text
            );
        }
    }

    return "<!-- wp:heading {\"level\":2} -->\n"
        . '<h2 class="wp-block-heading">' . $heading . "</h2>\n"
        . "<!-- /wp:heading -->\n\n"
        . "<!-- wp:paragraph -->\n"
        . '<p>' . $lede . "</p>\n"
        . "<!-- /wp:paragraph -->\n\n"
        . "<!-- wp:buttons -->\n"
        . '<div class="wp-block-buttons">' . trim($buttons) . "</div>\n"
        . '<!-- /wp:buttons -->';
}
