<?php
/**
 * Single H1 on Twenty Twenty-Five pages (drop template core/post-title).
 *
 * This marketing site owns the page H1 in post content (Home hero, product
 * names on Managed Service / Enterprise). Blog listings use the posts
 * home/index templates, not `page`. Rsync deploys skip activation hooks, so
 * this lives on init and retries until the customized `page` wp_template has
 * no post-title and still has post-content.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_page_h1_maybe_migrate', 20);

/**
 * Option that records the last completed page H1 migrate.
 *
 * @return string
 */
function forgedseo_core_page_h1_option()
{
    return 'forgedseo_core_page_h1';
}

/**
 * Active block theme stylesheet this migrate writes the page template for.
 *
 * @return string
 */
function forgedseo_core_page_h1_theme_slug()
{
    return 'twentytwentyfive';
}

/**
 * Version written to the option after a successful migrate.
 *
 * @return string
 */
function forgedseo_core_page_h1_target_version()
{
    return '0.1.8';
}

/**
 * One-shot migrate after deploy/upgrade: strip page-template post-title.
 *
 * @return void
 */
function forgedseo_core_page_h1_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_page_h1_option(), '');
    if (version_compare($done, forgedseo_core_page_h1_target_version(), '>=')) {
        return;
    }

    if (!function_exists('get_block_template') || !function_exists('get_stylesheet')) {
        return;
    }

    if (get_stylesheet() !== forgedseo_core_page_h1_theme_slug()) {
        return;
    }

    if (!forgedseo_core_page_h1_migrate_page_template()) {
        return;
    }

    update_option(forgedseo_core_page_h1_option(), forgedseo_core_page_h1_target_version(), true);
}

/**
 * Strip post-title, tighten main top spacing, persist customized `page`.
 *
 * @return bool
 */
function forgedseo_core_page_h1_migrate_page_template()
{
    $id = forgedseo_core_page_h1_theme_slug() . '//page';
    $template = get_block_template($id, 'wp_template');
    if (!$template || empty($template->content)) {
        return false;
    }

    $content = (string) $template->content;
    $updated = forgedseo_core_page_h1_prepare_template_content($content);
    if (!forgedseo_core_page_h1_content_is_migrated($updated)) {
        return false;
    }

    if ($updated === $content) {
        return true;
    }

    if (!forgedseo_core_page_h1_write_template_content($template, $updated)) {
        return false;
    }

    $saved = get_block_template($id, 'wp_template');
    if (!$saved || empty($saved->content)) {
        return false;
    }

    return forgedseo_core_page_h1_content_is_migrated((string) $saved->content);
}

/**
 * Remove post-title and set main margin-top / inner padding-top toward 0.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_page_h1_prepare_template_content($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $updated = forgedseo_core_page_h1_strip_post_title($content);
    $updated = forgedseo_core_page_h1_tighten_spacing($updated);

    return is_string($updated) ? $updated : '';
}

/**
 * True when markup has post-content and no post-title.
 *
 * @param string $content
 * @return bool
 */
function forgedseo_core_page_h1_content_is_migrated($content)
{
    if (!is_string($content) || $content === '') {
        return false;
    }

    if (forgedseo_core_page_h1_content_has_post_title($content)) {
        return false;
    }

    return forgedseo_core_page_h1_content_has_post_content($content);
}

/**
 * @param string $content
 * @return bool
 */
function forgedseo_core_page_h1_content_has_post_title($content)
{
    return is_string($content) && (bool) preg_match('/<!--\s+wp:post-title\b/', $content);
}

/**
 * @param string $content
 * @return bool
 */
function forgedseo_core_page_h1_content_has_post_content($content)
{
    return is_string($content) && (bool) preg_match('/<!--\s+wp:post-content\b/', $content);
}

/**
 * Strip self-closing and paired core/post-title blocks.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_page_h1_strip_post_title($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $stripped = preg_replace(
        '/<!--\s+wp:post-title\b[^>]*\/-->\s*/',
        '',
        $content
    );
    if (!is_string($stripped)) {
        $stripped = $content;
    }

    $stripped = preg_replace(
        '/<!--\s+wp:post-title\b[^>]*-->.*?<!--\s+\/wp:post-title\s+-->\s*/s',
        '',
        $stripped
    );

    return is_string($stripped) ? $stripped : $content;
}

/**
 * page-no-title uses margin-top 0 on main; also drop inner padding-top so
 * Home's full-bleed hero is not sitting under an empty paper band.
 *
 * @param string $content
 * @return string
 */
function forgedseo_core_page_h1_tighten_spacing($content)
{
    if (!is_string($content) || $content === '') {
        return '';
    }

    $content = forgedseo_core_page_h1_set_main_margin_top($content, '0');
    $content = forgedseo_core_page_h1_set_inner_padding_top($content, '0');

    return $content;
}

/**
 * Set margin.top on the main group (attrs + wrapper style).
 *
 * @param string $content
 * @param string $value
 * @return string
 */
function forgedseo_core_page_h1_set_main_margin_top($content, $value)
{
    $value = is_string($value) ? $value : '0';
    $content = forgedseo_core_page_h1_update_group_json(
        $content,
        static function ($attrs) {
            return isset($attrs['tagName']) && $attrs['tagName'] === 'main';
        },
        static function ($attrs) use ($value) {
            return forgedseo_core_page_h1_set_spacing_attr($attrs, 'margin', 'top', $value);
        }
    );

    $content = preg_replace(
        '/(<main\b[^>]*\bstyle="[^"]*margin-top:)([^;"]+)/i',
        '${1}' . $value,
        $content,
        1
    );

    return is_string($content) ? $content : '';
}

/**
 * Set padding.top on the first alignfull group (attrs + wrapper style).
 *
 * @param string $content
 * @param string $value
 * @return string
 */
function forgedseo_core_page_h1_set_inner_padding_top($content, $value)
{
    $value = is_string($value) ? $value : '0';
    $content = forgedseo_core_page_h1_update_group_json(
        $content,
        static function ($attrs) {
            return isset($attrs['align']) && $attrs['align'] === 'full';
        },
        static function ($attrs) use ($value) {
            return forgedseo_core_page_h1_set_spacing_attr($attrs, 'padding', 'top', $value);
        }
    );

    $content = preg_replace(
        '/(<div class="wp-block-group alignfull"[^>]*style="[^"]*padding-top:)([^;"]+)/i',
        '${1}' . $value,
        $content,
        1
    );

    return is_string($content) ? $content : '';
}

/**
 * Replace the first matching wp:group JSON object via callbacks.
 *
 * @param string   $content
 * @param callable $match   function(array $attrs): bool
 * @param callable $mutate  function(array $attrs): array
 * @return string
 */
function forgedseo_core_page_h1_update_group_json($content, $match, $mutate)
{
    if (!is_string($content) || $content === '' || !is_callable($match) || !is_callable($mutate)) {
        return is_string($content) ? $content : '';
    }

    $needle = '<!-- wp:group';
    $pos = 0;
    while (($start = strpos($content, $needle, $pos)) !== false) {
        $json_start = strpos($content, '{', $start);
        if ($json_start === false) {
            break;
        }

        $prefix = substr($content, $start, $json_start - $start);
        if (false !== strpos($prefix, '-->')) {
            $pos = $start + strlen($needle);
            continue;
        }

        $json = forgedseo_core_page_h1_read_json_object($content, $json_start);
        if ($json === '') {
            $pos = $start + strlen($needle);
            continue;
        }

        $attrs = json_decode($json, true);
        if (!is_array($attrs) || !call_user_func($match, $attrs)) {
            $pos = $json_start + strlen($json);
            continue;
        }

        $updated_attrs = call_user_func($mutate, $attrs);
        if (!is_array($updated_attrs)) {
            break;
        }

        $encoded = json_encode($updated_attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($encoded) || $encoded === $json) {
            break;
        }

        return substr($content, 0, $json_start) . $encoded . substr($content, $json_start + strlen($json));
    }

    return $content;
}

/**
 * @param array<string, mixed> $attrs
 * @param string               $kind  margin or padding
 * @param string               $edge  top, bottom, left, right
 * @param string               $value
 * @return array<string, mixed>
 */
function forgedseo_core_page_h1_set_spacing_attr($attrs, $kind, $edge, $value)
{
    if (!is_array($attrs)) {
        $attrs = array();
    }

    if (!isset($attrs['style']) || !is_array($attrs['style'])) {
        $attrs['style'] = array();
    }
    if (!isset($attrs['style']['spacing']) || !is_array($attrs['style']['spacing'])) {
        $attrs['style']['spacing'] = array();
    }
    if (!isset($attrs['style']['spacing'][$kind]) || !is_array($attrs['style']['spacing'][$kind])) {
        $attrs['style']['spacing'][$kind] = array();
    }

    $attrs['style']['spacing'][$kind][$edge] = $value;

    return $attrs;
}

/**
 * Read a JSON object starting at `$start` (`{`).
 *
 * @param string $string
 * @param int    $start
 * @return string
 */
function forgedseo_core_page_h1_read_json_object($string, $start)
{
    if (!is_string($string) || !isset($string[$start]) || $string[$start] !== '{') {
        return '';
    }

    $depth = 0;
    $in_string = false;
    $escape = false;
    $len = strlen($string);
    for ($i = $start; $i < $len; $i++) {
        $ch = $string[$i];
        if ($in_string) {
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($ch === '\\') {
                $escape = true;
                continue;
            }
            if ($ch === '"') {
                $in_string = false;
            }
            continue;
        }
        if ($ch === '"') {
            $in_string = true;
            continue;
        }
        if ($ch === '{') {
            $depth++;
            continue;
        }
        if ($ch === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($string, $start, $i - $start + 1);
            }
        }
    }

    return '';
}

/**
 * Create or update a customized wp_template (REST-equivalent).
 *
 * Front-end init is unauthenticated, so this avoids tax_input / kses which
 * would strip block comments or fail to assign wp_theme.
 *
 * @param object $template WP_Block_Template
 * @param string $content  Block markup
 * @return bool
 */
function forgedseo_core_page_h1_write_template_content($template, $content)
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
        $slug = isset($template->slug) ? (string) $template->slug : 'page';
        $post_id = wp_insert_post(
            wp_slash(
                array(
                    'post_type'      => 'wp_template',
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
                : forgedseo_core_page_h1_theme_slug();
            wp_set_post_terms(absint($post_id), array($theme), 'wp_theme');
            $ok = true;
        }
    }

    if (function_exists('kses_init_filters')) {
        kses_init_filters();
    }

    return $ok;
}
