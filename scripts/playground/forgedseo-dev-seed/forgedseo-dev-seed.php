<?php
/**
 * Plugin Name: ForgedSEO Dev Seed
 * Description: Seeds Home, Managed Service, Enterprise, About, Blog, and the main nav for the local Playground site. Not shipped to Hostinger.
 * Version: 1
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FORGEDSEO_DEV_SEED_VERSION', '1');
define('FORGEDSEO_DEV_SEED_OPTION', 'forgedseo_dev_seeded');
define('FORGEDSEO_DEV_SEED_MEDIA_DIR', '/wordpress/wp-content/uploads/forgedseo-seed-media');

add_action('init', 'forgedseo_dev_seed_maybe_run', 50);
add_action('init', 'forgedseo_dev_seed_handle_reseed', 45);

/**
 * Admin-only reseed: visit /?forgedseo_reseed=1 while logged in.
 */
function forgedseo_dev_seed_handle_reseed()
{
    if (!isset($_GET['forgedseo_reseed'])) {
        return;
    }
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }
    delete_option(FORGEDSEO_DEV_SEED_OPTION);
}

/**
 * @return void
 */
function forgedseo_dev_seed_maybe_run()
{
    if (get_option(FORGEDSEO_DEV_SEED_OPTION) === FORGEDSEO_DEV_SEED_VERSION) {
        return;
    }
    if (function_exists('wp_installing') && wp_installing()) {
        return;
    }

    forgedseo_dev_seed_run();
    update_option(FORGEDSEO_DEV_SEED_OPTION, FORGEDSEO_DEV_SEED_VERSION, false);
}

/**
 * @return void
 */
function forgedseo_dev_seed_run()
{
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

    $user = get_user_by('login', 'admin');
    if ($user) {
        wp_set_password('admin', $user->ID);
    }

    forgedseo_dev_seed_drop_default_content();
    $media = forgedseo_dev_seed_media();

    $home_id = forgedseo_dev_seed_page(
        'home',
        'Home',
        forgedseo_dev_seed_home_content(),
        4
    );
    forgedseo_dev_seed_page(
        'service',
        'Managed Service',
        forgedseo_dev_seed_service_content(),
        25
    );
    forgedseo_dev_seed_page(
        'enterprise',
        'Enterprise',
        forgedseo_dev_seed_enterprise_content(),
        29
    );
    forgedseo_dev_seed_page(
        'about',
        'About',
        forgedseo_dev_seed_about_content(),
        0
    );
    $blog_id = forgedseo_dev_seed_page(
        'blog',
        'Blog',
        forgedseo_dev_seed_blog_content(),
        37
    );

    update_option('show_on_front', 'page');
    if ($home_id) {
        update_option('page_on_front', $home_id);
    }
    if ($blog_id) {
        update_option('page_for_posts', $blog_id);
    }
    update_option('blogname', 'ForgedSEO');
    update_option('blogdescription', 'Agentic Content Engine');
    update_option('permalink_structure', '/%postname%/');

    if (!empty($media['logo'])) {
        set_theme_mod('custom_logo', (int) $media['logo']);
    }
    if (!empty($media['favicon'])) {
        update_option('site_icon', (int) $media['favicon']);
    }

    forgedseo_dev_seed_navigation();

    if (function_exists('flush_rewrite_rules')) {
        flush_rewrite_rules(false);
    }
}

/**
 * Drop Hello World / Sample Page so /blog/ shows the branded empty state.
 *
 * @return void
 */
function forgedseo_dev_seed_drop_default_content()
{
    $hello = get_page_by_path('hello-world', OBJECT, 'post');
    if ($hello instanceof WP_Post) {
        wp_delete_post($hello->ID, true);
    }

    $sample = get_page_by_path('sample-page');
    if ($sample instanceof WP_Post) {
        wp_delete_post($sample->ID, true);
    }
}

/**
 * Sideload production lockup (attachment 33) and org mark (attachment 22).
 *
 * @return array<string, int>
 */
function forgedseo_dev_seed_media()
{
    $ids = array();

    $ids['logo'] = forgedseo_dev_seed_sideload(
        'header-lockup.png',
        'https://forgedseo.com/wp-content/uploads/2026/03/cropped-forged-seo-app-logo.png',
        33,
        'ForgedSEO'
    );
    $ids['org'] = forgedseo_dev_seed_sideload(
        'org-logo.png',
        'https://forgedseo.com/wp-content/uploads/2026/03/forged-seo-s-logo-dim-1.png',
        22,
        'ForgedSEO organization mark'
    );
    $ids['favicon'] = forgedseo_dev_seed_sideload(
        'favicon.png',
        'https://forgedseo.com/wp-content/uploads/2026/03/cropped-forged-seo-favicon-192x192.png',
        21,
        'ForgedSEO'
    );

    return array_filter($ids);
}

/**
 * @param string $filename File under the mounted media cache.
 * @param string $fallback_url Public production URL if the cache file is missing.
 * @param int    $import_id Preferred attachment ID (matches forgedseo-core constants).
 * @param string $title
 * @return int
 */
function forgedseo_dev_seed_sideload($filename, $fallback_url, $import_id, $title)
{
    $local = rtrim(FORGEDSEO_DEV_SEED_MEDIA_DIR, '/') . '/' . $filename;
    if (is_readable($local)) {
        return forgedseo_dev_seed_sideload_file($local, $filename, $import_id, $title);
    }

    $existing = get_post($import_id);
    if ($existing && $existing->post_type === 'attachment') {
        return $import_id;
    }

    $tmp = download_url($fallback_url, 30);
    if (is_wp_error($tmp)) {
        return 0;
    }

    return forgedseo_dev_seed_sideload_tmp($tmp, $filename, $import_id, $title);
}

/**
 * @param string $path
 * @param string $name
 * @param int    $import_id
 * @param string $title
 * @return int
 */
function forgedseo_dev_seed_sideload_file($path, $name, $import_id, $title)
{
    $existing = get_post($import_id);
    if ($existing && $existing->post_type === 'attachment') {
        return $import_id;
    }

    $tmp = wp_tempnam($name);
    if (!$tmp || !copy($path, $tmp)) {
        return 0;
    }

    return forgedseo_dev_seed_sideload_tmp($tmp, $name, $import_id, $title);
}

/**
 * @param string $tmp
 * @param string $name
 * @param int    $import_id
 * @param string $title
 * @return int
 */
function forgedseo_dev_seed_sideload_tmp($tmp, $name, $import_id, $title)
{
    $file_array = array(
        'name'     => $name,
        'tmp_name' => $tmp,
    );
    $post_data = array(
        'import_id'  => (int) $import_id,
        'post_title' => $title,
    );
    $id = media_handle_sideload($file_array, 0, $title, $post_data);
    if (is_wp_error($id)) {
        @unlink($tmp);
        return 0;
    }
    return (int) $id;
}

/**
 * @param string $slug
 * @param string $title
 * @param string $content
 * @param int    $import_id
 * @return int
 */
function forgedseo_dev_seed_page($slug, $title, $content, $import_id = 0)
{
    $existing = get_page_by_path($slug);
    $data = array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_name'    => $slug,
        'post_title'   => $title,
        'post_content' => $content,
        'post_author'  => 1,
    );
    if ($existing instanceof WP_Post) {
        $data['ID'] = $existing->ID;
        $id = wp_update_post($data, true);
        return is_wp_error($id) ? 0 : (int) $id;
    }
    if ($import_id > 0 && !get_post($import_id)) {
        $data['import_id'] = (int) $import_id;
    }
    $id = wp_insert_post($data, true);
    return is_wp_error($id) ? 0 : (int) $id;
}

/**
 * @param string $text
 * @return string
 */
function forgedseo_dev_seed_p($text)
{
    return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
}

/**
 * @param string $text
 * @param int    $level
 * @return string
 */
function forgedseo_dev_seed_h($text, $level = 2)
{
    $level = (int) $level;
    if ($level < 1 || $level > 6) {
        $level = 2;
    }
    $tag = 'h' . $level;
    return '<!-- wp:heading {"level":' . $level . "} -->\n"
        . '<' . $tag . ' class="wp-block-heading">' . $text . '</' . $tag . ">\n"
        . "<!-- /wp:heading -->\n\n";
}

/**
 * @param string[] $items
 * @return string
 */
function forgedseo_dev_seed_list($items)
{
    $lis = '';
    foreach ($items as $item) {
        $lis .= '<li>' . $item . '</li>';
    }
    return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $lis . "</ul>\n<!-- /wp:list -->\n\n";
}

/**
 * @param string $text
 * @param string $href
 * @param string $style
 * @return string
 */
function forgedseo_dev_seed_cta($text, $href, $style = 'primary')
{
    return "<!-- wp:shortcode -->\n[forgedseo_cta text=\"" . $text . '" href="' . $href . '" style="' . $style . "\"]\n<!-- /wp:shortcode -->\n\n";
}

/**
 * Live homepage headings/copy from forgedseo.com.
 *
 * @return string
 */
function forgedseo_dev_seed_home_content()
{
    $out = '';
    $out .= forgedseo_dev_seed_h('Consistent authority. Zero overhead.', 1);
    $out .= forgedseo_dev_seed_p('ForgedSEO is the agentic content engine for teams that need search authority without an in-house SEO army. Managed service or Enterprise PaaS — research, publish, and compound.');
    $out .= forgedseo_dev_seed_cta('Get started', '/service/');
    $out .= forgedseo_dev_seed_cta('Email us', 'mailto:forgedseo@spenpo.com', 'secondary');

    $out .= forgedseo_dev_seed_h('Proven growth in record time');
    $out .= forgedseo_dev_seed_p('A pilot client went from zero to 350 organic clicks/month in 90 days — 42 high-intent posts on a consistent schedule. The depth of an in-house team at a fraction of the cost.');

    $out .= forgedseo_dev_seed_h('Why ForgedSEO');
    $out .= forgedseo_dev_seed_p('About ForgedSEO — who we are, how Managed and Enterprise differ, and the public pilot proof.');
    $out .= forgedseo_dev_seed_list(
        array(
            'Proprietary agentic research builds a knowledge graph of your business — not generic AI filler.',
            'Every piece is fact-checked and aligned to your brand voice.',
            'We research, write, and publish straight to WordPress so you watch traffic grow.',
        )
    );

    $out .= forgedseo_dev_seed_h('Who it’s for');
    $out .= forgedseo_dev_seed_h('Lean marketing teams', 3);
    $out .= forgedseo_dev_seed_p('Need search authority without hiring an SEO squad — Managed Service runs research, writing, and publishing for you.');
    $out .= forgedseo_dev_seed_h('Founder-led B2B', 3);
    $out .= forgedseo_dev_seed_p('Want compounding organic demand while you ship product — we handle the content engine end to end.');
    $out .= forgedseo_dev_seed_h('Agencies &amp; white-label', 3);
    $out .= forgedseo_dev_seed_p('Need depth at scale for client campaigns — Enterprise PaaS puts your team on the same agentic stack.');

    $out .= forgedseo_dev_seed_h('Two ways to run it');
    $out .= forgedseo_dev_seed_h('Managed Service', 3);
    $out .= forgedseo_dev_seed_p('We run the engine for you — strategy, content, publishing, reporting.');
    $out .= forgedseo_dev_seed_cta('See managed', '/service/');
    $out .= forgedseo_dev_seed_h('Enterprise PaaS', 3);
    $out .= forgedseo_dev_seed_p('Your team orchestrates campaigns on our platform — from goal to optimized content in minutes.');
    $out .= forgedseo_dev_seed_cta('See PaaS', '/enterprise/');

    $out .= forgedseo_dev_seed_h('Ready to compound search authority?');
    $out .= forgedseo_dev_seed_p('Email the team or explore Managed Service — we will map the first 90 days with you.');
    $out .= forgedseo_dev_seed_cta('Explore Managed Service', '/service/');
    $out .= forgedseo_dev_seed_p('<a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a>');

    return $out;
}

/**
 * Live /service/ headings/copy from forgedseo.com.
 *
 * @return string
 */
function forgedseo_dev_seed_service_content()
{
    $out = '';
    $out .= forgedseo_dev_seed_h('Managed Service', 1);
    $out .= forgedseo_dev_seed_p('Consistent authority. Zero overhead. We run ForgedSEO for you — research, content, publishing, and compounding traffic while you stay focused on the product.');
    $out .= forgedseo_dev_seed_cta('Get started', 'mailto:forgedseo@spenpo.com');
    $out .= forgedseo_dev_seed_cta('Compare PaaS', '/enterprise/', 'secondary');

    $out .= forgedseo_dev_seed_h('Who it’s for');
    $out .= forgedseo_dev_seed_p('Founders and marketing leads who want SEO outcomes without hiring a full content org — or babysitting freelancers. Best fit when you need a steady publish cadence, brand-aligned voice, and reporting you can hand to leadership.');
    $out .= forgedseo_dev_seed_list(
        array(
            'Lean marketing teams without an in-house SEO squad',
            'Founder-led B2B that must compound organic demand while shipping product',
            'Teams that tried freelancers or generic AI drafts and need consistency',
        )
    );

    $out .= forgedseo_dev_seed_h('What you get');
    $out .= forgedseo_dev_seed_list(
        array(
            'Agentic research graph of your business and market',
            'High-intent content calendar executed on schedule',
            'Direct WordPress publishing with brand-aligned voice',
            'Traffic and authority reporting you can actually use',
        )
    );

    $out .= forgedseo_dev_seed_h('The first 90 days');
    $out .= forgedseo_dev_seed_p('A typical managed onboarding maps to three phases. Exact cadence and volume are scoped with you — no fixed prices or SLAs on this page.');
    $out .= forgedseo_dev_seed_list(
        array(
            'Days 1–30: knowledge intake, voice/guardrails, topic graph, first publish wave',
            'Days 31–60: cadence locked, coverage expands into adjacent intents, early ranking signals reviewed',
            'Days 61–90: compounding publishes, refresh passes on winners, reporting you can act on',
        )
    );

    $out .= forgedseo_dev_seed_h('What you own vs what we own');
    $out .= forgedseo_dev_seed_h('You own', 3);
    $out .= forgedseo_dev_seed_list(
        array(
            'Business goals, constraints, and product truth',
            'Brand voice approvals and factual sign-off where needed',
            'WordPress access / publish permissions for your site',
            'Final call on topics that touch legal, pricing, or roadmap',
        )
    );
    $out .= forgedseo_dev_seed_h('We own', 3);
    $out .= forgedseo_dev_seed_list(
        array(
            'Research graph, calendar, and publish operations',
            'Drafting, fact-check pass, and brand-aligned edits',
            'WordPress publishing and on-page hygiene for shipped posts',
            'Traffic and authority reporting on a steady cadence',
        )
    );

    $out .= forgedseo_dev_seed_h('Deliverables &amp; cadence');
    $out .= forgedseo_dev_seed_p('You get a living content calendar, posts published to your WordPress on schedule, and reporting that tracks organic traction — not vanity dashboards. Volume and meeting rhythm are set together during onboarding.');

    $out .= forgedseo_dev_seed_h('Proof');
    $out .= forgedseo_dev_seed_p('A pilot client went from zero to 350 organic clicks/month in 90 days — 42 high-intent posts on a consistent schedule. Consistency compounds.');

    $out .= forgedseo_dev_seed_h('Next step');
    $out .= forgedseo_dev_seed_p('Tell us about your site and goals. We’ll map a first-90-days outline before anything ships.');
    $out .= forgedseo_dev_seed_cta('Request managed access', 'mailto:forgedseo@spenpo.com');

    return $out;
}

/**
 * Live /enterprise/ headings/copy from forgedseo.com.
 *
 * @return string
 */
function forgedseo_dev_seed_enterprise_content()
{
    $out = '';
    $out .= forgedseo_dev_seed_h('Enterprise PaaS', 1);
    $out .= forgedseo_dev_seed_p('From a single goal to optimized content in minutes. Orchestrate keyword campaigns on ForgedSEO’s agentic engine — automate research, drafting, and publishing without drowning in prompts.');
    $out .= forgedseo_dev_seed_cta('Request access', 'mailto:forgedseo@spenpo.com');
    $out .= forgedseo_dev_seed_cta('Prefer managed?', '/service/', 'secondary');

    $out .= forgedseo_dev_seed_h('Who it’s for');
    $out .= forgedseo_dev_seed_p('Marketing ops, growth, and content teams that need leverage — not another chat window. Best fit when your team already owns strategy and wants the same agentic stack we run for Managed Service clients.');
    $out .= forgedseo_dev_seed_list(
        array(
            'In-house content/SEO teams scaling campaign volume',
            'Agencies and white-label operators running multiple client sites',
            'Ops leaders who need governance (voice, facts, publish rights) without slowing drafts',
        )
    );

    $out .= forgedseo_dev_seed_h('Stop prompting. Start orchestrating.');
    $out .= forgedseo_dev_seed_p('ForgedSEO bridges proprietary business knowledge and your website. It doesn’t just write — it architects campaigns that build search authority.');

    $out .= forgedseo_dev_seed_h('Platform capabilities');
    $out .= forgedseo_dev_seed_list(
        array(
            'Knowledge-graph research tied to your domain',
            'Campaign-level keyword and intent planning',
            'WordPress-native publish pipeline',
            'Governance for brand voice and factual accuracy',
        )
    );

    $out .= forgedseo_dev_seed_h('The first 90 days');
    $out .= forgedseo_dev_seed_p('Enterprise onboarding focuses on getting your team productive on the platform. Timelines below are a typical pattern — exact scope is set with your account lead.');
    $out .= forgedseo_dev_seed_list(
        array(
            'Days 1–30: workspace setup, knowledge ingest, voice/governance rules, first campaign live',
            'Days 31–60: team workflows settled, multi-campaign orchestration, publish pipeline tuned to your CMS',
            'Days 61–90: scale coverage, refine scoring/guardrails, operational reporting for stakeholders',
        )
    );

    $out .= forgedseo_dev_seed_h('What you own vs what we own');
    $out .= forgedseo_dev_seed_h('You own', 3);
    $out .= forgedseo_dev_seed_list(
        array(
            'Campaign goals, prioritization, and publish decisions',
            'Day-to-day orchestration inside the platform',
            'Brand, legal, and factual approvals for your org',
            'CMS credentials and environment access',
        )
    );
    $out .= forgedseo_dev_seed_h('We own', 3);
    $out .= forgedseo_dev_seed_list(
        array(
            'The agentic research and drafting engine',
            'Platform reliability, upgrades, and core workflows',
            'Onboarding playbooks and enablement for your team',
            'Support for WordPress-native publish integration',
        )
    );

    $out .= forgedseo_dev_seed_h('Deliverables &amp; cadence');
    $out .= forgedseo_dev_seed_p('Your team runs campaigns on a shared cadence you define — research, draft, review, then publish. Seat counts, rate limits, and support terms are scoped when you request access.');

    $out .= forgedseo_dev_seed_h('Proof (same engine)');
    $out .= forgedseo_dev_seed_p('The Managed Service stack that took a pilot from zero to 350 organic clicks/month in 90 days with 42 high-intent posts is the same agentic engine Enterprise teams orchestrate themselves.');

    $out .= forgedseo_dev_seed_h('Next step');
    $out .= forgedseo_dev_seed_p('Request Enterprise access and we’ll walk through workspace setup, governance, and your first campaign path. Prefer hands-off? See <a href="/service/">Managed Service</a>.');
    $out .= forgedseo_dev_seed_cta('Request Enterprise access', 'mailto:forgedseo@spenpo.com');

    return $out;
}

/**
 * Live /about/ headings/copy from forgedseo.com.
 *
 * @return string
 */
function forgedseo_dev_seed_about_content()
{
    $out = '';
    $out .= forgedseo_dev_seed_h('About ForgedSEO', 1);
    $out .= forgedseo_dev_seed_p('ForgedSEO is the agentic content engine for teams that need search authority without an in-house SEO army. Managed service or Enterprise PaaS.');
    $out .= forgedseo_dev_seed_p('We build a knowledge graph of your business, then research, write, and publish brand-aligned content straight to WordPress — so authority compounds on a schedule.');

    $out .= forgedseo_dev_seed_h('Two ways to run it');
    $out .= forgedseo_dev_seed_p('<strong>Managed Service</strong> — we run research, content, publishing, and reporting for you while you stay focused on the product.');
    $out .= forgedseo_dev_seed_p('<strong>Enterprise PaaS</strong> — your team orchestrates keyword campaigns on our platform, from a single goal to optimized content in minutes.');

    $out .= forgedseo_dev_seed_h('Proof');
    $out .= forgedseo_dev_seed_p('In 90 days a pilot client went from zero to 350 organic clicks per month with 42 high-intent posts on a consistent schedule. Consistency compounds.');

    $out .= forgedseo_dev_seed_h('Talk with us');
    $out .= forgedseo_dev_seed_cta('Managed Service', '/service/');
    $out .= forgedseo_dev_seed_cta('Enterprise PaaS', '/enterprise/', 'secondary');
    $out .= forgedseo_dev_seed_p('<a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a>');

    return $out;
}

/**
 * Blog page body is unused when it is the posts page; keep a short placeholder.
 *
 * @return string
 */
function forgedseo_dev_seed_blog_content()
{
    return forgedseo_dev_seed_p('Insights and playbooks from the ForgedSEO agentic content engine.');
}

/**
 * Main nav: Home, Service, Enterprise, About, Blog.
 *
 * @return void
 */
function forgedseo_dev_seed_navigation()
{
    $markup = <<<'HTML'
<!-- wp:navigation-link {"label":"Home","url":"/","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Service","url":"/service/","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Enterprise","url":"/enterprise/","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"About","url":"/about/","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Blog","url":"/blog/","kind":"custom"} /-->
HTML;

    $existing = get_posts(
        array(
            'post_type'   => 'wp_navigation',
            'post_status' => 'publish',
            'title'       => 'Primary',
            'numberposts' => 1,
        )
    );

    $data = array(
        'post_type'    => 'wp_navigation',
        'post_status'  => 'publish',
        'post_title'   => 'Primary',
        'post_name'    => 'primary',
        'post_content' => $markup,
    );

    if ($existing) {
        $data['ID'] = $existing[0]->ID;
        wp_update_post($data);
        $nav_id = (int) $existing[0]->ID;
    } else {
        $nav_id = (int) wp_insert_post($data);
    }

    if ($nav_id) {
        forgedseo_dev_seed_bind_header_nav($nav_id);
    }
}

/**
 * Point Twenty Twenty-Five's header navigation at the seeded menu when possible.
 *
 * @param int $nav_id
 * @return void
 */
function forgedseo_dev_seed_bind_header_nav($nav_id)
{
    if (!function_exists('get_block_templates') || !function_exists('wp_update_post')) {
        return;
    }

    $parts = get_block_templates(array('slug__in' => array('header')), 'wp_template_part');
    if (!is_array($parts)) {
        return;
    }

    foreach ($parts as $part) {
        if (empty($part->content) || empty($part->wp_id)) {
            continue;
        }
        $updated = preg_replace(
            '/<!-- wp:navigation(\s+\{[^}]*\})? \/\-->/',
            '<!-- wp:navigation {"ref":' . (int) $nav_id . '} /-->',
            $part->content,
            1
        );
        if (is_string($updated) && $updated !== $part->content) {
            wp_update_post(
                array(
                    'ID'           => (int) $part->wp_id,
                    'post_content' => $updated,
                )
            );
        }
    }
}
