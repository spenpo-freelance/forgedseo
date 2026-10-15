<?php
/**
 * About page.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sections = array(
    forgedseo_core_s_masthead(
        array(
            'kicker' => 'About ForgedSEO',
            'title'  => 'Search authority,<br>engineered.',
            'lede'   => 'ForgedSEO is an agentic content engine for teams that need search authority without an in-house SEO army.',
        )
    ),
    forgedseo_core_s_story(
        array(
            'kicker'     => 'Why we built it',
            'title'      => 'Authority should compound, not depend on headcount.',
            'paragraphs' => array(
                'Search rewards consistency: useful, accurate posts published on a schedule, for months. Most teams can’t staff that, and generic AI drafts don’t earn trust.',
                'ForgedSEO builds a knowledge graph of your business, then researches, writes and publishes brand-aligned content straight to WordPress. Every claim is grounded in your documents or a cited source, and nothing ships without approval.',
                'It is built and maintained by an engineer, on a versioned pipeline rather than a chat window. Run it as a <a href="/service/">managed service</a>, or put your own team in the Command Center with <a href="/enterprise/">Enterprise PaaS</a>.',
            ),
        )
    ),
    forgedseo_core_s_grid(
        array(
            'class'  => 'fseo-principles',
            'kicker' => 'What we believe',
            'title'  => 'Four principles behind<br>every post.',
            'items'  => array(
                array('eye', 'Facts before fluency', 'Drafts are grounded in your documents and live, cited sources.'),
                array('calendar', 'Consistency compounds', 'A steady schedule beats a burst of posts. We publish on cadence.'),
                array('shield', 'Humans approve', 'Review gates mean nothing goes live without your sign-off.'),
                array('code', 'Built by engineers', 'A versioned pipeline on Node.js, BullMQ and PostgreSQL, maintained in-house.'),
            ),
        )
    ),
    forgedseo_core_s_proof(),
    forgedseo_core_pattern_markup('home-plans'),
    forgedseo_core_pattern_markup('home-contact'),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
