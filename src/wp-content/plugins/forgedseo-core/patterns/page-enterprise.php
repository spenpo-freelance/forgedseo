<?php
/**
 * Enterprise PaaS page.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sections = array(
    forgedseo_core_s_hero(
        array(
            'class'       => 'fseo-hero2--page',
            'kicker'      => 'Enterprise PaaS · Early access',
            'title'       => 'Your team runs<br>the engine.',
            'title_class' => 'fseo-display fseo-display--l',
            'lede'        => 'Orchestrate keyword campaigns on the same agentic stack we run for Managed clients. Go from a single goal to published, interlinked posts in minutes.',
            'buttons'     => array(
                array('text' => 'Request early access', 'href' => '#contact'),
                array('text' => 'Prefer managed?', 'href' => '/service/', 'style' => 'outline'),
            ),
            'fineprint'   => 'Seats, rate limits and support terms are scoped during early access.',
            'aside'       => forgedseo_core_s_window('Campaign “Local SEO pillar” · 12 posts planned · 4 in review'),
        )
    ),
    forgedseo_core_s_grid(
        array(
            'class'  => 'fseo-audience',
            'kicker' => 'Who it’s for',
            'title'  => 'For teams that own<br>their strategy.',
            'items'  => array(
                array('users', 'In-house content and SEO teams', 'Scale campaign volume without scaling headcount or prompt wrangling.'),
                array('building', 'Agencies and white-label operators', 'Run campaigns across many client sites from one Command Center.'),
                array('shield', 'Ops and governance leads', 'Rules for voice, facts and publish rights that do not slow drafts down.'),
            ),
        )
    ),
    forgedseo_core_s_agents(
        array(
            'class'  => 'fseo-agents fseo-platform',
            'kicker' => 'The platform',
            'title'  => 'Every agent,<br>under your control.',
            'lede'   => 'Your team runs the same versioned pipeline we use for Managed clients, with governance for brand voice, facts and publish rights.',
        )
    ),
    forgedseo_core_s_steps(
        array(
            'class'  => 'fseo-timeline',
            'kicker' => 'The first 90 days',
            'title'  => 'Productive in<br>one quarter.',
            'lede'   => 'Exact scope is set with your account lead. A typical onboarding looks like this.',
            'steps'  => array(
                array('calendar', 'Days 1–30', 'Workspace live', 'Workspace setup, knowledge ingest, governance rules and your first campaign.'),
                array('cluster', 'Days 31–60', 'Team workflows', 'Multi-campaign orchestration and a publish pipeline tuned to your CMS.'),
                array('chart', 'Days 61–90', 'Scale', 'Broader coverage, refined guardrails and operational reporting for stakeholders.'),
            ),
        )
    ),
    forgedseo_core_s_ownership(
        array(
            'kicker' => 'Who owns what',
            'title'  => 'Clear lines. No babysitting.',
            'you'    => array(
                'Strategy and the publish button',
                array(
                    'Campaign goals, priorities and publish decisions',
                    'Day-to-day orchestration in the Command Center',
                    'Brand, legal and factual approvals',
                    'CMS credentials and environment access',
                ),
            ),
            'us'     => array(
                'The engine and its reliability',
                array(
                    'The agentic research and drafting engine',
                    'Platform reliability, upgrades and core workflows',
                    'Onboarding playbooks and team enablement',
                    'WordPress publish integration support',
                ),
            ),
        )
    ),
    forgedseo_core_s_faq(
        array(
            'sub'   => 'Want a walkthrough? <a href="#contact">Request early access</a> and we will demo the Command Center and Strategist.',
            'items' => array(
                array('Can we deploy it ourselves?', 'Yes. Run ForgedSEO locally or in the cloud; we set it up with you during onboarding.'),
                array('How is it priced?', 'Seats, rate limits and support terms are scoped with each early-access team.'),
                array('Is it the same engine as Managed Service?', 'Yes. The stack that took our pilot client from zero to 350+ organic clicks a month is the one your team runs.'),
                array('Where does our data live?', 'Your knowledge base and proprietary data stay under your control.'),
            ),
        )
    ),
    forgedseo_core_pattern_markup('home-contact'),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
