<?php
/**
 * Marketing shortcodes for ForgedSEO Core.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('forgedseo_cta', 'forgedseo_core_cta_shortcode');

/**
 * Primary marketing CTA.
 *
 * [forgedseo_cta text="Book a strategy call" href="/contact/" style="primary"]
 *
 * @param array<string, string>|string $atts
 * @return string
 */
function forgedseo_core_cta_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'text'  => __('Book a strategy call', 'forgedseo-core'),
            'href'  => '/contact/',
            'style' => 'primary',
        ),
        $atts,
        'forgedseo_cta'
    );

    $style = sanitize_key($atts['style']);
    $modifier = 'primary';
    if (in_array($style, array('secondary', 'ghost'), true)) {
        $modifier = $style;
    }

    return sprintf(
        '<a class="fseo-btn fseo-btn--%1$s" href="%2$s">%3$s</a>',
        esc_attr($modifier),
        esc_url($atts['href']),
        esc_html($atts['text'])
    );
}
