<?php
/**
 * Home why ForgedSEO: four differentiators.
 */

if (!defined('ABSPATH')) {
    exit;
}

echo forgedseo_core_s_grid( // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
    array(
        'kicker' => 'Why ForgedSEO',
        'title'  => 'Better than a content mill.<br>Safer than a prompt.',
        'items'  => array(
            array('search', 'No generic AI filler', 'The Researcher cites live sources specific to your industry, so every post is factually grounded.'),
            array('graph', 'You own the context', 'We build a permanent knowledge graph of your business. The longer we work together, the sharper it gets.'),
            array('code', 'Developer-led reliability', 'A proprietary stack on Node.js, BullMQ and PostgreSQL with pgvector, built and maintained by an engineer.'),
            array('eye', 'You keep the publish button', 'Manual review gates mean nothing ships without your approval. Optional sync to your own Markdown vault.'),
        ),
    )
);
