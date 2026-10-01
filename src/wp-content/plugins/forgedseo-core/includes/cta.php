<?php
/**
 * Normalize Managed Service / Enterprise CTAs to fseo-btn (0.1.10).
 *
 * Page bodies live in post content (Service 25, Enterprise 29). Home (4) is
 * not rewritten — it is edited separately. Rsync deploys skip activation
 * hooks, so this lives on init and retries until both product pages have
 * fseo-btn markup, no core/button blocks, and the canonical mailto.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_cta_maybe_migrate', 20);

/**
 * Option that records the last completed product-CTA migrate.
 *
 * @return string
 */
function forgedseo_core_cta_option()
{
    return 'forgedseo_core_product_cta';
}

/**
 * Version written to the option after a successful migrate.
 *
 * @return string
 */
function forgedseo_core_cta_target_version()
{
    return '0.1.10';
}

/**
 * Managed Service and Enterprise page IDs. Home (4) is intentionally omitted.
 *
 * @return int[]
 */
function forgedseo_core_cta_target_post_ids()
{
    return array(25, 29);
}

/**
 * Stale mailbox previously used on product CTAs.
 *
 * @return string
 */
function forgedseo_core_cta_stale_email()
{
    return 'hello@forgedseo.com';
}

/**
 * Dead in-page hero target that never existed on /service/.
 *
 * @return string
 */
function forgedseo_core_cta_dead_hash()
{
    return '#start';
}

/**
 * @param int $post_id
 * @return string Relative path with trailing slash, or empty.
 */
function forgedseo_core_cta_sibling_path($post_id)
{
    $post_id = absint($post_id);
    if ($post_id === 25) {
        return '/enterprise/';
    }
    if ($post_id === 29) {
        return '/service/';
    }

    return '';
}

/**
 * One-shot migrate after deploy/upgrade: rewrite product-page CTA markup.
 *
 * @return void
 */
function forgedseo_core_cta_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_cta_option(), '');
    if (version_compare($done, forgedseo_core_cta_target_version(), '>=')) {
        return;
    }

    if (!forgedseo_core_cta_migrate_pages()) {
        return;
    }

    update_option(forgedseo_core_cta_option(), forgedseo_core_cta_target_version(), true);
}

/**
 * Rewrite each target page; retry later if a page is missing or still stale.
 *
 * @return bool
 */
function forgedseo_core_cta_migrate_pages()
{
    $ok = true;

    foreach (forgedseo_core_cta_target_post_ids() as $post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'page') {
            return false;
        }

        if (!forgedseo_core_cta_migrate_page($post)) {
            $ok = false;
        }
    }

    return $ok;
}

/**
 * @param WP_Post|object $post
 * @return bool
 */
function forgedseo_core_cta_migrate_page($post)
{
    if (!is_object($post) || empty($post->ID)) {
        return false;
    }

    $post_id = absint($post->ID);
    if ($post_id === forgedseo_core_homepage_post_id_safe()) {
        return true;
    }

    $content = isset($post->post_content) ? (string) $post->post_content : '';
    $updated = forgedseo_core_cta_prepare_content($content, $post_id);
    if (!forgedseo_core_cta_content_is_migrated($updated, $post_id)) {
        return false;
    }

    if ($updated === $content) {
        return true;
    }

    if (!forgedseo_core_cta_write_page_content($post_id, $updated)) {
        return false;
    }

    $saved = get_post($post_id);
    if (!$saved) {
        return false;
    }

    return forgedseo_core_cta_content_is_migrated((string) $saved->post_content, $post_id);
}

/**
 * Homepage id when aioseo.php is loaded; never migrate that page.
 *
 * @return int
 */
function forgedseo_core_homepage_post_id_safe()
{
    if (function_exists('forgedseo_core_homepage_post_id')) {
        return (int) forgedseo_core_homepage_post_id();
    }

    return 4;
}

/**
 * @param string $content
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_prepare_content($content, $post_id)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $post_id = absint($post_id);
    $updated = forgedseo_core_cta_replace_buttons_blocks($content, $post_id);
    $updated = forgedseo_core_cta_replace_loose_button_blocks($updated, $post_id);
    $updated = forgedseo_core_cta_rewrite_shortcodes($updated, $post_id);
    $updated = forgedseo_core_cta_rewrite_fseo_btn_anchors($updated, $post_id);
    $updated = forgedseo_core_cta_wrap_orphan_html_buttons($updated);
    $updated = forgedseo_core_cta_rewrite_stale_hrefs($updated);

    return is_string($updated) ? $updated : '';
}

/**
 * True when markup has fseo-btn CTAs, canonical mailto, sibling secondary, no core buttons.
 *
 * @param string $content
 * @param int    $post_id
 * @return bool
 */
function forgedseo_core_cta_content_is_migrated($content, $post_id)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    if (preg_match('/<!--\s+wp:button\b/', $content)) {
        return false;
    }

    if (preg_match('/wp-block-button(?!s)/', $content)) {
        return false;
    }

    if (false !== stripos($content, forgedseo_core_cta_stale_email())) {
        return false;
    }

    if (false !== strpos($content, forgedseo_core_cta_dead_hash())) {
        return false;
    }

    if (false === strpos($content, 'fseo-cta-row') || false === strpos($content, 'fseo-btn')) {
        return false;
    }

    $mailto = forgedseo_core_contact_mailto();
    if (false === strpos($content, $mailto)) {
        return false;
    }

    $sibling = forgedseo_core_cta_sibling_path($post_id);
    if ($sibling !== '' && false === strpos($content, $sibling)) {
        return false;
    }

    return true;
}

/**
 * Replace each core/buttons group with an HTML fseo-cta-row.
 *
 * @param string $content
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_replace_buttons_blocks($content, $post_id)
{
    $updated = preg_replace_callback(
        '/<!--\s+wp:buttons\b[^>]*-->.*?<!--\s+\/wp:buttons\s+-->/s',
        static function ($match) use ($post_id) {
            $items = forgedseo_core_cta_extract_button_items($match[0], $post_id);
            if (empty($items)) {
                return $match[0];
            }

            $row = forgedseo_core_cta_row_html($items);
            if ($row === '') {
                return $match[0];
            }

            return forgedseo_core_cta_html_block($row);
        },
        $content
    );

    return is_string($updated) ? $updated : $content;
}

/**
 * Convert leftover unpaired core/button blocks (not already inside a replaced group).
 *
 * @param string $content
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_replace_loose_button_blocks($content, $post_id)
{
    $updated = preg_replace_callback(
        '/<!--\s+wp:button\b[^>]*-->.*?<!--\s+\/wp:button\s+-->/s',
        static function ($match) use ($post_id) {
            $items = forgedseo_core_cta_extract_button_items($match[0], $post_id);
            if (empty($items)) {
                return $match[0];
            }

            $item = $items[0];
            $row = forgedseo_core_cta_row_html(array($item));
            if ($row === '') {
                return $match[0];
            }
            return forgedseo_core_cta_html_block($row);
        },
        $content
    );

    return is_string($updated) ? $updated : $content;
}

/**
 * Keep lone fseo-btn HTML blocks in a block-level row so constrained layout applies.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_cta_wrap_orphan_html_buttons($content)
{
    $updated = preg_replace_callback(
        '/<!--\s+wp:html\s+-->(.*?)<!--\s+\/wp:html\s+-->/s',
        static function ($match) {
            $inner = trim($match[1]);
            if ($inner === '' || false === strpos($inner, 'fseo-btn')) {
                return $match[0];
            }
            if (false !== strpos($inner, 'fseo-cta-row')) {
                return $match[0];
            }

            return forgedseo_core_cta_html_block('<div class="fseo-cta-row">' . $inner . '</div>');
        },
        $content
    );

    return is_string($updated) ? $updated : $content;
}

/**
 * @param string $html
 * @return string
 */
function forgedseo_core_cta_html_block($html)
{
    $html = is_string($html) ? trim($html) : '';
    if ($html === '') {
        return '';
    }

    return "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
}

/**
 * Parse wp:button inner markup into normalized CTA items.
 *
 * @param string $markup
 * @param int    $post_id
 * @return array<int, array{text:string, href:string, style:string}>
 */
function forgedseo_core_cta_extract_button_items($markup, $post_id)
{
    if (!is_string($markup) || $markup === '') {
        return array();
    }

    $items = array();
    if (!preg_match_all(
        '/<!--\s+wp:button(\s+(\{.*?\}))?\s+-->(.*?)<!--\s+\/wp:button\s+-->/s',
        $markup,
        $matches,
        PREG_SET_ORDER
    )) {
        return array();
    }

    foreach ($matches as $match) {
        $attrs_json = isset($match[2]) ? trim($match[2]) : '';
        $inner = isset($match[3]) ? $match[3] : '';
        $hint = 'primary';
        if ($attrs_json !== '') {
            $attrs = json_decode($attrs_json, true);
            if (is_array($attrs) && !empty($attrs['className']) && is_string($attrs['className'])) {
                if (false !== strpos($attrs['className'], 'is-style-outline')
                    || false !== strpos($attrs['className'], 'is-style-secondary')
                ) {
                    $hint = 'secondary';
                }
            }
        }

        $href = '';
        $text = '';
        if (preg_match('/<a\b[^>]*\bhref=([\'"])(.*?)\1[^>]*>(.*?)<\/a>/is', $inner, $anchor)) {
            $href = html_entity_decode($anchor[2], ENT_QUOTES, 'UTF-8');
            $text = forgedseo_core_cta_plain_text($anchor[3]);
        }

        if ($text === '') {
            continue;
        }

        $style = forgedseo_core_cta_classify($text, $href, $hint, $post_id);
        $items[] = array(
            'text'  => $text,
            'href'  => forgedseo_core_cta_href_for_style($style, $post_id),
            'style' => $style,
        );
    }

    return $items;
}

/**
 * @param string $text
 * @param string $href
 * @param string $hint
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_classify($text, $href, $hint, $post_id)
{
    $hint = forgedseo_core_btn_modifier($hint);
    if ($hint === 'secondary' || $hint === 'ghost') {
        return $hint;
    }

    $href = is_string($href) ? $href : '';
    $text = is_string($text) ? $text : '';
    $sibling = forgedseo_core_cta_sibling_path($post_id);

    if ($sibling !== '' && forgedseo_core_cta_href_points_at_path($href, $sibling)) {
        return 'secondary';
    }

    if (preg_match('/compare|prefer managed|see (managed|paas|enterprise)|see paas/i', $text)) {
        return 'secondary';
    }

    return 'primary';
}

/**
 * @param string $href
 * @param string $path
 * @return bool
 */
function forgedseo_core_cta_href_points_at_path($href, $path)
{
    if (!is_string($href) || !is_string($path) || $path === '') {
        return false;
    }

    $path = strtolower($path);
    $href = strtolower(trim($href));
    if ($href === $path) {
        return true;
    }

    $parts = function_exists('wp_parse_url') ? wp_parse_url($href) : parse_url($href);
    if (!is_array($parts) || empty($parts['path'])) {
        return false;
    }

    $parsed = $parts['path'];
    if (substr($parsed, -1) !== '/') {
        $parsed .= '/';
    }

    return $parsed === $path;
}

/**
 * @param string $style
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_href_for_style($style, $post_id)
{
    $style = forgedseo_core_btn_modifier($style);
    if ($style === 'secondary') {
        $sibling = forgedseo_core_cta_sibling_path($post_id);
        return $sibling !== '' ? $sibling : '/service/';
    }

    return forgedseo_core_contact_mailto();
}

/**
 * Rebuild [forgedseo_cta] as an HTML fseo-cta-row (constrained-layout safe).
 *
 * @param string $content
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_rewrite_shortcodes($content, $post_id)
{
    $updated = preg_replace_callback(
        '/(?:<!--\s+wp:shortcode\s+-->\s*)?\[forgedseo_cta([^\]]*)\](?:\s*<!--\s+\/wp:shortcode\s+-->)?/i',
        static function ($match) use ($post_id) {
            $atts = forgedseo_core_cta_parse_shortcode_atts($match[1]);
            $text = isset($atts['text']) ? $atts['text'] : 'Get started';
            $href = isset($atts['href']) ? $atts['href'] : '';
            $hint = isset($atts['style']) ? $atts['style'] : 'primary';
            $style = forgedseo_core_cta_classify($text, $href, $hint, $post_id);
            $href = forgedseo_core_cta_href_for_style($style, $post_id);
            $row = forgedseo_core_cta_row_html(
                array(
                    array(
                        'text'  => $text,
                        'href'  => $href,
                        'style' => $style,
                    ),
                )
            );

            return $row !== '' ? forgedseo_core_cta_html_block($row) : $match[0];
        },
        $content
    );

    return is_string($updated) ? $updated : $content;
}

/**
 * @param string $attr_string
 * @return array<string, string>
 */
function forgedseo_core_cta_parse_shortcode_atts($attr_string)
{
    $atts = array();
    if (!is_string($attr_string) || $attr_string === '') {
        return $atts;
    }

    if (preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attr_string, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $atts[$match[1]] = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
        }
    }

    if (preg_match_all("/(\w+)\s*=\s*'([^']*)'/", $attr_string, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if (!isset($atts[$match[1]])) {
                $atts[$match[1]] = html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');
            }
        }
    }

    return $atts;
}

/**
 * @param string $value
 * @return string
 */
function forgedseo_core_cta_shortcode_escape($value)
{
    $value = is_string($value) ? $value : '';
    return str_replace('"', '', $value);
}

/**
 * Normalize existing <a class="fseo-btn"> hrefs without changing copy.
 *
 * @param string $content
 * @param int    $post_id
 * @return string
 */
function forgedseo_core_cta_rewrite_fseo_btn_anchors($content, $post_id)
{
    $updated = preg_replace_callback(
        '/<a\b([^>]*\bclass=(["\'])([^"\']*\bfseo-btn\b[^"\']*)\2[^>]*)>(.*?)<\/a>/is',
        static function ($match) use ($post_id) {
            $open = $match[1];
            $class = $match[3];
            $text = forgedseo_core_cta_plain_text($match[4]);
            $href = '';
            if (preg_match('/\bhref=(["\'])(.*?)\1/i', $open, $href_match)) {
                $href = html_entity_decode($href_match[2], ENT_QUOTES, 'UTF-8');
            }

            $hint = 'primary';
            if (false !== strpos($class, 'fseo-btn--secondary') || false !== strpos($class, 'fseo-btn--ghost')) {
                $hint = false !== strpos($class, 'fseo-btn--ghost') ? 'ghost' : 'secondary';
            }

            $style = forgedseo_core_cta_classify($text, $href, $hint, $post_id);
            $href = forgedseo_core_cta_href_for_style($style, $post_id);

            return forgedseo_core_btn_html($text, $href, $style);
        },
        $content
    );

    return is_string($updated) ? $updated : $content;
}

/**
 * Last-pass href cleanup for leftover hello@ and #start in these pages.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_cta_rewrite_stale_hrefs($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $mailto = forgedseo_core_contact_mailto();
    $stale = 'mailto:' . forgedseo_core_cta_stale_email();
    $content = str_ireplace($stale, $mailto, $content);
    $content = str_replace(
        array('/service/#start', '/service#start'),
        $mailto,
        $content
    );

    return $content;
}

/**
 * @param string $html
 * @return string
 */
function forgedseo_core_cta_plain_text($html)
{
    $html = is_string($html) ? $html : '';
    if (function_exists('wp_strip_all_tags')) {
        return trim(wp_strip_all_tags($html));
    }

    return trim(strip_tags($html));
}

/**
 * Persist page content from unauthenticated front-end init (rsync deploy).
 *
 * @param int    $post_id
 * @param string $content
 * @return bool
 */
function forgedseo_core_cta_write_page_content($post_id, $content)
{
    $post_id = absint($post_id);
    if ($post_id <= 0 || !is_string($content) || $content === '') {
        return false;
    }

    if (function_exists('kses_remove_filters')) {
        kses_remove_filters();
    }

    $result = wp_update_post(
        wp_slash(
            array(
                'ID'           => $post_id,
                'post_content' => $content,
            )
        ),
        true
    );

    if (function_exists('kses_init_filters')) {
        kses_init_filters();
    }

    return !is_wp_error($result) && absint($result) > 0;
}
