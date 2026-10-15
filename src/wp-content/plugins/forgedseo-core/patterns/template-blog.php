<?php
/**
 * Blog posts index (wp_template `home`).
 */

if (!defined('ABSPATH')) {
    exit;
}

$no_results = '<!-- wp:query-no-results -->' . "\n"
    . forgedseo_core_b_group(
        'fseo-empty',
        forgedseo_core_b_h(esc_html(forgedseo_core_blog_empty_heading()), 2, 'fseo-h2') . "\n\n"
        . forgedseo_core_b_p(esc_html(forgedseo_core_blog_empty_lede()), 'fseo-lede') . "\n\n"
        . forgedseo_core_b_buttons(
            array(
                array('text' => 'Start 30-day free trial', 'href' => forgedseo_core_trial_url()),
                array('text' => 'How Managed Service works', 'href' => '/service/', 'style' => 'outline'),
            )
        )
    )
    . "\n" . '<!-- /wp:query-no-results -->';

$pagination = '<!-- wp:query-pagination {"paginationArrow":"arrow","className":"fseo-pagination","layout":{"type":"flex","justifyContent":"space-between"}} -->' . "\n"
    . '<!-- wp:query-pagination-previous {"label":"Newer posts"} /-->' . "\n"
    . '<!-- wp:query-pagination-next {"label":"Older posts"} /-->' . "\n"
    . '<!-- /wp:query-pagination -->';

$sections = array(
    forgedseo_core_s_masthead(
        array(
            'class'  => 'fseo-masthead--blog',
            'kicker' => 'Blog',
            'title'  => 'Field notes from the engine.',
            'lede'   => 'Playbooks on agentic SEO, content clusters and compounding search authority.',
        )
    ),
    forgedseo_core_s_band(
        'paper',
        'fseo-posts',
        forgedseo_core_s_post_cards(
            array(
                'perPage'  => 9,
                'pages'    => 0,
                'offset'   => 0,
                'postType' => 'post',
                'order'    => 'desc',
                'orderBy'  => 'date',
                'inherit'  => true,
            ),
            1,
            $pagination . "\n\n" . $no_results
        )
    ),
    forgedseo_core_s_cta_band(
        array(
            'title'   => 'Want this running on your site?',
            'lede'    => 'Three search-ready posts a week, researched and published for you.',
            'buttons' => forgedseo_core_trial_buttons('/#contact'),
        )
    ),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
