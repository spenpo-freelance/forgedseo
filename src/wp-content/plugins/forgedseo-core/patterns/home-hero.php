<?php
/**
 * Home hero: headline, CTAs, Command Center product window.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo forgedseo_core_s_hero( // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
    array(
        'kicker'    => 'The agentic content engine',
        'title'     => 'Consistent authority.<br>Zero overhead.',
        'lede'      => 'ForgedSEO researches, writes and publishes search-ready posts grounded in your own business knowledge. We run it for you, or your team runs it on our platform.',
        'buttons'   => forgedseo_core_trial_buttons(),
        'fineprint' => '$1,000/month after a 30-day free trial. Cancel anytime in month one.',
        'aside'     => forgedseo_core_s_window('Cluster run · Researcher → Blueprinter → Illustrator → Publisher'),
    )
);
