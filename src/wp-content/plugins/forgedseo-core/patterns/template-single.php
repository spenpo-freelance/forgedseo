<?php
/**
 * Single blog post (wp_template `single`).
 */

if (!defined('ABSPATH')) {
    exit;
}

$header = '<!-- wp:post-terms {"term":"category","className":"fseo-kicker"} /-->' . "\n\n"
    . '<!-- wp:post-title {"level":1,"className":"fseo-display fseo-display--m"} /-->' . "\n\n"
    . forgedseo_core_b_group(
        'fseo-post-meta',
        '<!-- wp:post-date /-->' . "\n\n" . '<!-- wp:post-author-name /-->'
    );

$end_cta = forgedseo_core_b_group(
    'fseo-end-cta',
    forgedseo_core_b_group(
        'fseo-end-cta__copy',
        forgedseo_core_b_h('Put ForgedSEO on your site', 3, 'fseo-h3') . "\n\n"
        . forgedseo_core_b_p('Three search-ready posts a week. 30 days free.')
    ) . "\n\n"
    . forgedseo_core_b_buttons(array(array('text' => 'Start 30-day free trial', 'href' => forgedseo_core_trial_url())))
);

$sections = array(
    forgedseo_core_s_band('dark', 'fseo-post-head', $header),
    forgedseo_core_b_group(
        'fseo-post-feature',
        forgedseo_core_b_group('fseo-wrap', '<!-- wp:post-featured-image {"className":"fseo-post-feature__img"} /-->'),
        array('tag' => 'section', 'align' => 'full')
    ),
    forgedseo_core_s_band(
        'paper',
        'fseo-post-body',
        '<!-- wp:post-content {"className":"fseo-prose","layout":{"type":"default"}} /-->' . "\n\n" . $end_cta
    ),
    forgedseo_core_s_band(
        'paper',
        'fseo-more',
        forgedseo_core_b_p('Keep reading', 'fseo-kicker') . "\n\n"
        . forgedseo_core_s_post_cards(
            array(
                'perPage'            => 3,
                'pages'              => 0,
                'offset'             => 0,
                'postType'           => 'post',
                'order'              => 'desc',
                'orderBy'            => 'date',
                'inherit'            => false,
                'fseoExcludeCurrent' => true,
            ),
            2
        )
    ),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
