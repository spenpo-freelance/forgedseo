<?php
/**
 * Front-end assets and body class for ForgedSEO Core.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('body_class', 'forgedseo_core_body_class');
add_action('wp_enqueue_scripts', 'forgedseo_core_enqueue', 100);
add_action('wp_enqueue_scripts', 'forgedseo_core_dequeue_unused_site_kit', 999);

/**
 * @param string[] $classes
 * @return string[]
 */
function forgedseo_core_body_class($classes)
{
    $classes[] = 'forgedseo-core';
    return $classes;
}

function forgedseo_core_enqueue()
{
    $deps = array();
    if (wp_style_is('hostinger-ai-style', 'registered')) {
        $deps[] = 'hostinger-ai-style';
    }

    wp_enqueue_style(
        'forgedseo-core',
        FORGEDSEO_CORE_URL . 'assets/css/forgedseo.css',
        $deps,
        FORGEDSEO_CORE_VERSION
    );

    wp_enqueue_script(
        'forgedseo-core',
        FORGEDSEO_CORE_URL . 'assets/js/forgedseo.js',
        array(),
        FORGEDSEO_CORE_VERSION,
        true
    );
}

/**
 * Script handles for unused Site Kit conversion event providers.
 *
 * @return string[]
 */
function forgedseo_core_unused_site_kit_handles()
{
    return array(
        'googlesitekit-events-provider-wpforms',
        'googlesitekit-events-provider-content-events',
    );
}

/**
 * Drop unused Site Kit event-provider JS after Site Kit enqueues (priority 30).
 *
 * Leaves googlesitekit-gtag / Analytics base (GT-5DCPNJTF) in place. Marketing
 * pages have no WPForms, and the content-events provider is unused weight.
 */
function forgedseo_core_dequeue_unused_site_kit()
{
    foreach (forgedseo_core_unused_site_kit_handles() as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}
