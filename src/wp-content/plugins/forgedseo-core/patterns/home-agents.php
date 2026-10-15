<?php
/**
 * Home agentic difference: specialist agents list.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo forgedseo_core_s_agents( // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
    array(
        'kicker' => 'The agentic difference',
        'title'  => 'A pipeline of specialists, not a wall of text.',
        'lede'   => 'Traditional AI writers hand you one long draft. ForgedSEO runs a versioned pipeline where each agent does one job well, and you approve the result before anything goes live.',
    )
);
