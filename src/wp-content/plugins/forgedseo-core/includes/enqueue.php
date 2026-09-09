<?php
/**
 * Front-end assets and body class for ForgedSEO Core.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('body_class', 'forgedseo_core_body_class');
add_action('wp_enqueue_scripts', 'forgedseo_core_enqueue', 100);

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
    wp_enqueue_style(
        'forgedseo-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap',
        array(),
        null
    );

    $deps = array('forgedseo-fonts');
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
