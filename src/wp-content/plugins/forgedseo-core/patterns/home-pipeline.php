<?php
/**
 * Home how-it-works: three-stage pipeline.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo forgedseo_core_s_steps( // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
    array(
        'kicker' => 'How it works',
        'title'  => 'Stop prompting.<br>Start orchestrating.',
        'lede'   => 'ForgedSEO replaces hours of prompting with a three-stage pipeline that turns one goal into a cluster of published, interlinked posts.',
        'steps'  => array(
            array('database', '01', 'Build your private knowledge base', 'Upload brand guidelines, whitepapers and case studies. A private retrieval index grounds every word in your actual business facts.'),
            array('cluster', '02', 'Generate clusters with one click', 'Pick the pillar you want to rank for. The Strategist agent plans a cluster of supporting posts that drive authority back to it.'),
            array('terminal', '03', 'Run it from the Command Center', 'Watch specialist agents research, outline, draft and illustrate in real time, then publish to WordPress with one click.'),
        ),
    )
);
