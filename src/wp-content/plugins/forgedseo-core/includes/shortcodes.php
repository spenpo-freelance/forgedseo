<?php
/**
 * Marketing shortcodes for ForgedSEO Core.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('forgedseo_cta', 'forgedseo_core_cta_shortcode');

/**
 * Canonical contact mailbox (matches footer and Organization schema).
 *
 * @return string
 */
function forgedseo_core_contact_email()
{
    if (function_exists('forgedseo_core_organization_email')) {
        return forgedseo_core_organization_email();
    }

    return 'forgedseo@spenpo.com';
}

/**
 * mailto: href for primary marketing CTAs.
 *
 * @return string
 */
function forgedseo_core_contact_mailto()
{
    return 'mailto:' . forgedseo_core_contact_email();
}

/**
 * Allowed fseo-btn modifiers.
 *
 * @return string[]
 */
function forgedseo_core_btn_modifiers()
{
    return array('primary', 'secondary', 'ghost');
}

/**
 * Normalize a CTA style token to an fseo-btn modifier.
 *
 * @param string $style
 * @return string
 */
function forgedseo_core_btn_modifier($style)
{
    $style = is_string($style) ? sanitize_key($style) : 'primary';
    if ($style === 'outline') {
        return 'secondary';
    }
    if (in_array($style, forgedseo_core_btn_modifiers(), true)) {
        return $style;
    }

    return 'primary';
}

/**
 * Shared fseo-btn markup used by the shortcode and content migrates.
 *
 * @param string $text
 * @param string $href
 * @param string $style primary, secondary, ghost, or outline
 * @return string
 */
function forgedseo_core_btn_html($text, $href, $style = 'primary')
{
    $text = is_string($text) ? $text : '';
    $href = is_string($href) ? $href : '';
    $modifier = forgedseo_core_btn_modifier($style);

    return sprintf(
        '<a class="fseo-btn fseo-btn--%1$s" href="%2$s">%3$s</a>',
        esc_attr($modifier),
        esc_url($href),
        esc_html($text)
    );
}

/**
 * Flex row of fseo-btn links (replaces core/buttons groups).
 *
 * @param array<int, array{text:string, href:string, style:string}> $items
 * @return string
 */
function forgedseo_core_cta_row_html($items)
{
    if (!is_array($items) || empty($items)) {
        return '';
    }

    $buttons = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $text = isset($item['text']) ? (string) $item['text'] : '';
        $href = isset($item['href']) ? (string) $item['href'] : '';
        $style = isset($item['style']) ? (string) $item['style'] : 'primary';
        if ($text === '' || $href === '') {
            continue;
        }
        $buttons[] = forgedseo_core_btn_html($text, $href, $style);
    }

    if (empty($buttons)) {
        return '';
    }

    return '<div class="fseo-cta-row">' . implode("\n", $buttons) . '</div>';
}

/**
 * Primary marketing CTA.
 *
 * [forgedseo_cta text="Book a strategy call" href="/service/" style="primary"]
 *
 * @param array<string, string>|string $atts
 * @return string
 */
function forgedseo_core_cta_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'text'  => __('Book a strategy call', 'forgedseo-core'),
            'href'  => '/service/',
            'style' => 'primary',
        ),
        $atts,
        'forgedseo_cta'
    );

    return forgedseo_core_btn_html($atts['text'], $atts['href'], $atts['style']);
}
