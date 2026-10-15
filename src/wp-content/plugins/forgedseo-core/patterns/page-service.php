<?php
/**
 * Managed Service page.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sections = array(
    forgedseo_core_s_hero(
        array(
            'tone'        => 'paper',
            'class'       => 'fseo-hero2--page',
            'kicker'      => 'Managed Service',
            'title'       => 'We run the engine.<br>You get the authority.',
            'title_class' => 'fseo-display fseo-display--l',
            'lede'        => 'ForgedSEO researches, writes and publishes three search-ready posts a week to your WordPress site, grounded in your business knowledge. You approve, we handle the rest.',
            'buttons'     => forgedseo_core_trial_buttons(),
            'aside'       => forgedseo_core_s_price_card(),
        )
    ),
    forgedseo_core_s_proof(),
    forgedseo_core_s_grid(
        array(
            'class'  => 'fseo-audience',
            'kicker' => 'Who it’s for',
            'title'  => 'Built for teams without<br>an SEO department.',
            'items'  => array(
                array('users', 'Lean marketing teams', 'You need a steady publish cadence without hiring a content org or managing freelancers.'),
                array('building', 'Founder-led B2B', 'Organic demand has to compound while you ship product. We keep the calendar moving.'),
                array('layers', 'Teams burned by AI drafts', 'You want consistency, your brand voice and facts you can defend, not generic filler.'),
            ),
        )
    ),
    forgedseo_core_s_steps(
        array(
            'class'  => 'fseo-timeline',
            'kicker' => 'The first 90 days',
            'title'  => 'From intake to<br>compounding traffic.',
            'lede'   => 'Cadence and volume are scoped with you during onboarding. A typical first quarter looks like this.',
            'steps'  => array(
                array('calendar', 'Days 1–30', 'Intake and first wave', 'Knowledge intake, voice and guardrails, your topic graph, then the first wave of posts goes live.'),
                array('cluster', 'Days 31–60', 'Cadence locked', 'Coverage expands into adjacent search intents and we review early ranking signals with you.'),
                array('chart', 'Days 61–90', 'Compounding', 'Refresh passes on the posts that win, wider coverage, and reporting you can act on.'),
            ),
        )
    ),
    forgedseo_core_s_ownership(
        array(
            'kicker' => 'Who owns what',
            'title'  => 'Clear lines. No babysitting.',
            'you'    => array(
                'The truth about your business',
                array(
                    'Business goals, constraints and product facts',
                    'Brand voice and factual sign-off',
                    'WordPress access and publish permissions',
                    'The final call on legal, pricing or roadmap topics',
                ),
            ),
            'us'     => array(
                'The engine and the operations',
                array(
                    'Research graph, content calendar and publishing',
                    'Drafting, fact-checking and brand-aligned edits',
                    'On-page hygiene for every post we ship',
                    'Traffic and authority reporting on a steady cadence',
                ),
            ),
        )
    ),
    forgedseo_core_s_faq(
        array(
            'sub'   => 'Something else on your mind? <a href="#contact">Book a demo</a> or email <a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a>.',
            'items' => array(
                array('What happens after the 30-day trial?', 'If you stay, it is $1,000 a month for three posts a week. Cancel anytime in your first month.'),
                array('Do I need to write anything?', 'No. Send brand guidelines and any documents that describe your product. The Researcher handles sources, and you approve before anything goes live.'),
                array('What access do you need?', 'A WordPress user that can publish posts and upload media. You can revoke it at any time.'),
                array('Can I review posts before they publish?', 'Yes. Review gates are on by default, so nothing ships without your sign-off.'),
                array('Do you work outside WordPress?', 'Publishing is WordPress-native today. On another CMS? Book a demo and we will talk it through.'),
            ),
        )
    ),
    forgedseo_core_pattern_markup('home-contact'),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
