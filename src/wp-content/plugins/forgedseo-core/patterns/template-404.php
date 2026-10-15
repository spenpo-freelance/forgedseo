<?php
/**
 * Not found (wp_template `404`).
 */

if (!defined('ABSPATH')) {
    exit;
}

echo forgedseo_core_s_masthead( // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
    array(
        'class'       => 'fseo-masthead--404',
        'kicker'      => 'Error 404',
        'title'       => 'This page wandered<br>off the map.',
        'title_class' => 'fseo-display',
        'lede'        => 'The link may be old, or the page may have moved. Try the homepage or the blog.',
        'buttons'     => array(
            array('text' => 'Back to home', 'href' => '/'),
            array('text' => 'Read the blog', 'href' => '/blog/', 'style' => 'outline'),
        ),
    )
);
