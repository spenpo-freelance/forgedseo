<?php
/**
 * Vector lockup for core/site-logo, the header trial CTA, and footer legal links.
 *
 * Both are render-time filters, so no SVG upload or template-part rewrite is
 * needed. The Custom Logo attachment (see chrome.php) stays in place for
 * wp-admin, schema, and as the fallback if this file is removed.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('render_block_core/site-logo', 'forgedseo_core_brand_site_logo', 10, 2);
add_filter('render_block_core/navigation', 'forgedseo_core_brand_header_cta', 10, 2);
add_filter('render_block_core/paragraph', 'forgedseo_core_brand_footer_links', 10, 2);

/**
 * viewBox width / height of assets/img/forgedseo-lockup.svg.
 *
 * @return float
 */
function forgedseo_core_brand_lockup_ratio()
{
    return 473.99 / 100;
}

/**
 * Swap the raster Custom Logo for the SVG lockup, keeping the block's width.
 *
 * @param string               $html
 * @param array<string, mixed> $block
 * @return string
 */
function forgedseo_core_brand_site_logo($html, $block)
{
    if (!is_string($html) || $html === '' || !class_exists('WP_HTML_Tag_Processor')) {
        return $html;
    }

    $tags = new WP_HTML_Tag_Processor($html);
    if (!$tags->next_tag('img')) {
        return $html;
    }

    $width = isset($block['attrs']['width']) ? absint($block['attrs']['width']) : 0;
    if ($width <= 0) {
        $width = absint($tags->get_attribute('width'));
    }
    if ($width <= 0) {
        $width = 170;
    }

    $tags->set_attribute('src', forgedseo_core_asset_url('img/forgedseo-lockup.svg'));
    $tags->remove_attribute('srcset');
    $tags->remove_attribute('sizes');
    $tags->set_attribute('width', (string) $width);
    $tags->set_attribute('height', (string) round($width / forgedseo_core_brand_lockup_ratio()));
    $tags->set_attribute('alt', 'ForgedSEO');
    $tags->set_attribute('decoding', 'async');
    $tags->remove_attribute('loading');

    return $tags->get_updated_html();
}

/**
 * Append the trial CTA after the header navigation (the overlay-capable one).
 *
 * The footer menu renders with overlayMenu "never" and is left alone.
 *
 * @param string               $html
 * @param array<string, mixed> $block
 * @return string
 */
function forgedseo_core_brand_header_cta($html, $block)
{
    if (!is_string($html) || $html === '' || is_admin()) {
        return $html;
    }

    $overlay = isset($block['attrs']['overlayMenu']) ? (string) $block['attrs']['overlayMenu'] : 'mobile';
    if ($overlay === 'never') {
        return $html;
    }

    return $html . sprintf(
        '<a class="fseo-btn fseo-btn--primary fseo-header-cta" href="%1$s">%2$s</a>',
        esc_url(forgedseo_core_trial_url()),
        esc_html__('Start free trial', 'forgedseo-core')
    );
}

/**
 * Add About and legal links to the footer copyright line.
 *
 * @param string               $html
 * @param array<string, mixed> $block
 * @return string
 */
function forgedseo_core_brand_footer_links($html, $block)
{
    if (!is_string($html) || strpos($html, '© ForgedSEO') === false || strpos($html, 'fseo-legal-links') !== false) {
        return $html;
    }

    $links = array(
        '/about/'          => __('About', 'forgedseo-core'),
        '/privacy-policy/' => __('Privacy', 'forgedseo-core'),
        '/terms/'          => __('Terms', 'forgedseo-core'),
    );

    $out = '';
    foreach ($links as $path => $label) {
        $out .= ' · <a href="' . esc_url(home_url($path)) . '">' . esc_html($label) . '</a>';
    }

    $pos = strrpos($html, '</p>');
    if ($pos === false) {
        return $html;
    }

    return substr($html, 0, $pos) . '<span class="fseo-legal-links">' . $out . '</span>' . substr($html, $pos);
}
