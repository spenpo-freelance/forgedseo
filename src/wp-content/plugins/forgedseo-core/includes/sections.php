<?php
/**
 * Block-markup builders for the marketing section system.
 *
 * Every builder returns serialized core block markup (groups, headings,
 * paragraphs, buttons, lists, details) carrying `fseo-*` classes, so pattern
 * files under `patterns/` stay short and Home and inner pages share one
 * implementation. Styles live in assets/css/forgedseo.css.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inline HTML allowed in titles and copy passed to builders.
 *
 * @param string $html
 * @return string
 */
function forgedseo_core_inline($html)
{
    return wp_kses(
        (string) $html,
        array(
            'br'     => array(),
            'strong' => array(),
            'em'     => array(),
            'a'      => array('href' => true),
        )
    );
}

/**
 * @param string $name  Core block name without the `core/` prefix.
 * @param array  $attrs
 * @param string $html  Saved inner HTML.
 * @return string
 */
function forgedseo_core_b($name, $attrs, $html)
{
    $open = '<!-- wp:' . $name . ($attrs ? ' ' . serialize_block_attributes($attrs) : '') . ' -->';

    return $open . "\n" . $html . "\n" . '<!-- /wp:' . $name . ' -->';
}

/**
 * @param string $class
 * @param string $inner
 * @param array  $args  tag, align, anchor
 * @return string
 */
function forgedseo_core_b_group($class, $inner, $args = array())
{
    $tag = isset($args['tag']) ? $args['tag'] : 'div';
    $attrs = array();
    if ($tag !== 'div') {
        $attrs['tagName'] = $tag;
    }
    if (!empty($args['anchor'])) {
        $attrs['anchor'] = $args['anchor'];
    }
    if (!empty($args['align'])) {
        $attrs['align'] = $args['align'];
    }
    $attrs['className'] = $class;
    $attrs['layout'] = array('type' => 'default');

    $classes = 'wp-block-group' . (!empty($args['align']) ? ' align' . $args['align'] : '') . ' ' . $class;
    $id = !empty($args['anchor']) ? ' id="' . esc_attr($args['anchor']) . '"' : '';

    return forgedseo_core_b(
        'group',
        $attrs,
        '<' . $tag . $id . ' class="' . esc_attr($classes) . '">' . $inner . '</' . $tag . '>'
    );
}

/**
 * @param string $html
 * @param string $class
 * @return string
 */
function forgedseo_core_b_p($html, $class = '')
{
    $attrs = $class !== '' ? array('className' => $class) : array();
    $cls = $class !== '' ? ' class="' . esc_attr($class) . '"' : '';

    return forgedseo_core_b('paragraph', $attrs, '<p' . $cls . '>' . forgedseo_core_inline($html) . '</p>');
}

/**
 * @param string $html
 * @param int    $level
 * @param string $class
 * @return string
 */
function forgedseo_core_b_h($html, $level = 2, $class = 'fseo-h2')
{
    $attrs = array();
    if ($level !== 2) {
        $attrs['level'] = $level;
    }
    if ($class !== '') {
        $attrs['className'] = $class;
    }

    return forgedseo_core_b(
        'heading',
        $attrs,
        '<h' . $level . ' class="wp-block-heading' . ($class !== '' ? ' ' . esc_attr($class) : '') . '">'
        . forgedseo_core_inline($html) . '</h' . $level . '>'
    );
}

/**
 * @param array  $buttons Each: text, href, style ('outline' or '').
 * @param string $class
 * @return string
 */
function forgedseo_core_b_buttons($buttons, $class = 'fseo-ctas')
{
    $inner = '';
    foreach ($buttons as $button) {
        $outline = !empty($button['style']) && $button['style'] === 'outline';
        $inner .= forgedseo_core_b(
            'button',
            $outline ? array('className' => 'is-style-outline') : array(),
            '<div class="wp-block-button' . ($outline ? ' is-style-outline' : '') . '"><a class="wp-block-button__link wp-element-button" href="'
            . esc_url($button['href']) . '">' . esc_html($button['text']) . '</a></div>'
        );
    }

    return forgedseo_core_b(
        'buttons',
        array('className' => $class),
        '<div class="wp-block-buttons ' . esc_attr($class) . '">' . $inner . '</div>'
    );
}

/**
 * @param string[] $items
 * @param string   $class
 * @return string
 */
function forgedseo_core_b_list($items, $class = 'fseo-checks')
{
    $inner = '';
    foreach ($items as $item) {
        $inner .= forgedseo_core_b('list-item', array(), '<li>' . forgedseo_core_inline($item) . '</li>');
    }

    return forgedseo_core_b(
        'list',
        array('className' => $class),
        '<ul class="wp-block-list ' . esc_attr($class) . '">' . $inner . '</ul>'
    );
}

/**
 * Empty group that CSS paints as a masked icon (`fseo-icon`) or icon tile (`fseo-tile`).
 *
 * @param string $icon
 * @param string $kind
 * @return string
 */
function forgedseo_core_b_icon($icon, $kind = 'fseo-icon')
{
    return forgedseo_core_b_group($kind . ' fseo-i-' . sanitize_html_class($icon), '');
}

/**
 * Full-width band with an inner `fseo-wrap`.
 *
 * @param string $tone   paper|dark
 * @param string $class  Section modifier class.
 * @param string $inner
 * @param array  $args   wrap (extra wrap class), anchor
 * @return string
 */
function forgedseo_core_s_band($tone, $class, $inner, $args = array())
{
    $wrap = trim('fseo-wrap ' . (isset($args['wrap']) ? $args['wrap'] : ''));

    return forgedseo_core_b_group(
        trim('fseo-s fseo-s--' . $tone . ' ' . $class),
        forgedseo_core_b_group($wrap, $inner),
        array(
            'tag'    => 'section',
            'align'  => 'full',
            'anchor' => isset($args['anchor']) ? $args['anchor'] : '',
        )
    );
}

/**
 * Kicker, heading and optional lede.
 *
 * @param string $kicker
 * @param string $title
 * @param string $lede
 * @param array  $args level, class (heading class)
 * @return string
 */
function forgedseo_core_s_head($kicker, $title, $lede = '', $args = array())
{
    $level = isset($args['level']) ? (int) $args['level'] : 2;
    $class = isset($args['class']) ? $args['class'] : 'fseo-h2';

    $inner = forgedseo_core_b_p(esc_html($kicker), 'fseo-kicker') . "\n\n" . forgedseo_core_b_h($title, $level, $class);
    if ($lede !== '') {
        $inner .= "\n\n" . forgedseo_core_b_p($lede, 'fseo-lede');
    }

    return forgedseo_core_b_group('fseo-head', $inner);
}

/**
 * Hero: copy column plus an aside (product window or price card).
 *
 * @param array $a tone, class, kicker, title, lede, buttons, fineprint, aside, title_class
 * @return string
 */
function forgedseo_core_s_hero($a)
{
    $copy = forgedseo_core_b_p(esc_html($a['kicker']), 'fseo-kicker') . "\n\n"
        . forgedseo_core_b_h($a['title'], 1, isset($a['title_class']) ? $a['title_class'] : 'fseo-display') . "\n\n"
        . forgedseo_core_b_p($a['lede'], 'fseo-lede') . "\n\n"
        . forgedseo_core_b_buttons($a['buttons']);
    if (!empty($a['fineprint'])) {
        $copy .= "\n\n" . forgedseo_core_b_p($a['fineprint'], 'fseo-fineprint');
    }

    return forgedseo_core_s_band(
        isset($a['tone']) ? $a['tone'] : 'dark',
        trim('fseo-hero2 ' . (isset($a['class']) ? $a['class'] : '')),
        forgedseo_core_b_group('fseo-hero2__copy', $copy) . "\n\n" . $a['aside'],
        array('wrap' => 'fseo-hero2__grid')
    );
}

/**
 * Command Center product window.
 *
 * @param string $status
 * @return string
 */
function forgedseo_core_s_window($status)
{
    $shot = esc_url(forgedseo_core_asset_url('img/forgedseo-command-center.webp'));
    $image = forgedseo_core_b(
        'image',
        array('sizeSlug' => 'full', 'linkDestination' => 'none', 'className' => 'fseo-window__shot'),
        '<figure class="wp-block-image size-full fseo-window__shot"><img src="' . $shot
        . '" alt="ForgedSEO Command Center dashboard with Project Finder, Add Client, Upload Context, Create Pillar and Prompt Builder wizards"/></figure>'
    );

    return forgedseo_core_b_group(
        'fseo-window',
        forgedseo_core_b_p('Command Center', 'fseo-window__bar') . "\n\n" . $image . "\n\n"
        . forgedseo_core_b_p(esc_html($status), 'fseo-window__status')
    );
}

/**
 * Managed Service price card (dark, used as a hero aside).
 *
 * @return string
 */
function forgedseo_core_s_price_card()
{
    return forgedseo_core_b_group(
        'fseo-price-card',
        forgedseo_core_b_p('One plan, everything included', 'fseo-kicker') . "\n\n"
        . forgedseo_core_b_p('<strong>$1,000</strong> / month', 'fseo-price') . "\n\n"
        . forgedseo_core_b_p('after a 30-day free trial with 12+ posts', 'fseo-price-card__sub') . "\n\n"
        . forgedseo_core_b_list(
            array(
                '3 SEO-optimized posts per week',
                'Research graph of your business',
                'AI-generated featured images',
                'WordPress publishing and reporting',
            )
        ) . "\n\n"
        . forgedseo_core_b_p('Cancel anytime in your first month.', 'fseo-fineprint')
    );
}

/**
 * Pilot client proof strip.
 *
 * @return string
 */
function forgedseo_core_s_proof()
{
    $stats = array(
        array('42', 'high-intent posts published on a consistent schedule', ''),
        array('350+', 'organic clicks every 28 days, starting from zero visibility', ' fseo-stat--hot'),
        array('3 / wk', 'posts per week. Velocity is the variable that compounds', ''),
    );

    $inner = '';
    foreach ($stats as $stat) {
        $inner .= forgedseo_core_b_group(
            'fseo-stat' . $stat[2],
            forgedseo_core_b_p(esc_html($stat[0]), 'fseo-stat__num') . "\n\n" . forgedseo_core_b_p(esc_html($stat[1]), 'fseo-stat__label')
        );
    }

    return forgedseo_core_s_band(
        'dark',
        'fseo-proof',
        forgedseo_core_b_p('Pilot client, first 90 days', 'fseo-kicker') . "\n\n" . forgedseo_core_b_group('fseo-stats', $inner)
    );
}

/**
 * Numbered steps (pipeline, first 90 days).
 *
 * @param array $a tone, class, kicker, title, lede, steps [[icon, num, title, body]]
 * @return string
 */
function forgedseo_core_s_steps($a)
{
    $inner = '';
    foreach ($a['steps'] as $step) {
        $head = forgedseo_core_b_icon($step[0], 'fseo-tile') . "\n\n" . forgedseo_core_b_p(esc_html($step[1]), 'fseo-step__num');
        $inner .= forgedseo_core_b_group(
            'fseo-step',
            forgedseo_core_b_group('fseo-step__head', $head) . "\n\n"
            . forgedseo_core_b_h(esc_html($step[2]), 3, 'fseo-h3') . "\n\n"
            . forgedseo_core_b_p(esc_html($step[3]))
        );
    }

    return forgedseo_core_s_band(
        isset($a['tone']) ? $a['tone'] : 'paper',
        isset($a['class']) ? $a['class'] : 'fseo-pipeline',
        forgedseo_core_s_head($a['kicker'], $a['title'], isset($a['lede']) ? $a['lede'] : '') . "\n\n"
        . forgedseo_core_b_group('fseo-steps', $inner)
    );
}

/**
 * Icon grid (differentiators, audiences, principles).
 *
 * @param array $a tone, class, kicker, title, lede, items [[icon, title, body]]
 * @return string
 */
function forgedseo_core_s_grid($a)
{
    $inner = '';
    foreach ($a['items'] as $item) {
        $inner .= forgedseo_core_b_group(
            'fseo-reason',
            forgedseo_core_b_icon($item[0]) . "\n\n"
            . forgedseo_core_b_h(esc_html($item[1]), 3, 'fseo-h3') . "\n\n"
            . forgedseo_core_b_p(esc_html($item[2]))
        );
    }

    $grid = 'fseo-reasons' . (count($a['items']) === 3 ? ' fseo-reasons--3' : '');

    return forgedseo_core_s_band(
        isset($a['tone']) ? $a['tone'] : 'paper',
        isset($a['class']) ? $a['class'] : 'fseo-why',
        forgedseo_core_s_head($a['kicker'], $a['title'], isset($a['lede']) ? $a['lede'] : '') . "\n\n"
        . forgedseo_core_b_group($grid, $inner)
    );
}

/**
 * Specialist agents list with a sticky head.
 *
 * @param array $a kicker, title, lede, class
 * @return string
 */
function forgedseo_core_s_agents($a)
{
    $agents = array(
        array('search', '01', 'The Researcher', 'Scours the live web for real-time facts and citations specific to your industry.'),
        array('blueprint', '02', 'The Blueprinter', 'Structures the narrative paragraph by paragraph before a single word is drafted.'),
        array('image', '03', 'The Illustrator', 'Generates context-aware featured images that match the post, not stock filler.'),
        array('publish', '04', 'The Publisher', 'Handles WordPress categories, tags, media uploads and formatting for you.'),
    );

    $list = '';
    foreach ($agents as $agent) {
        $list .= forgedseo_core_b_group(
            'fseo-agent',
            forgedseo_core_b_icon($agent[0], 'fseo-tile') . "\n\n"
            . forgedseo_core_b_group(
                'fseo-agent__text',
                forgedseo_core_b_p('Agent ' . esc_html($agent[1]), 'fseo-kicker') . "\n\n"
                . forgedseo_core_b_h(esc_html($agent[2]), 3, 'fseo-h3') . "\n\n"
                . forgedseo_core_b_p(esc_html($agent[3]))
            )
        );
    }

    return forgedseo_core_s_band(
        'dark',
        isset($a['class']) ? $a['class'] : 'fseo-agents',
        forgedseo_core_s_head($a['kicker'], $a['title'], $a['lede']) . "\n\n" . forgedseo_core_b_group('fseo-agents__list', $list),
        array('wrap' => 'fseo-split')
    );
}

/**
 * Two checklist panels: what the customer owns (light) vs what ForgedSEO owns (dark).
 *
 * @param array $a kicker, title, you [title, items], us [title, items]
 * @return string
 */
function forgedseo_core_s_ownership($a)
{
    $panel = static function ($class, $kicker, $title, $items) {
        return forgedseo_core_b_group(
            $class,
            forgedseo_core_b_p(esc_html($kicker), 'fseo-kicker') . "\n\n"
            . forgedseo_core_b_h(esc_html($title), 3, 'fseo-h3') . "\n\n"
            . forgedseo_core_b_list($items)
        );
    };

    return forgedseo_core_s_band(
        'dark',
        'fseo-ownership',
        forgedseo_core_s_head($a['kicker'], $a['title']) . "\n\n"
        . forgedseo_core_b_group(
            'fseo-panels',
            $panel('fseo-panel', 'You own', $a['you'][0], $a['you'][1]) . "\n\n"
            . $panel('fseo-panel fseo-panel--dark', 'We own', $a['us'][0], $a['us'][1])
        )
    );
}

/**
 * FAQ: sticky head plus core/details items (first one open).
 *
 * @param array $a sub, items [[question, answer]]
 * @return string
 */
function forgedseo_core_s_faq($a)
{
    $items = '';
    foreach (array_values($a['items']) as $i => $item) {
        $open = $i === 0;
        $attrs = array('className' => 'fseo-faq__item');
        if ($open) {
            $attrs['showContent'] = true;
        }
        $items .= forgedseo_core_b(
            'details',
            $attrs,
            '<details class="wp-block-details fseo-faq__item"' . ($open ? ' open' : '') . '><summary>' . esc_html($item[0]) . '</summary>'
            . forgedseo_core_b_p($item[1]) . '</details>'
        );
    }

    return forgedseo_core_s_band(
        'paper',
        'fseo-faq',
        forgedseo_core_s_head('FAQ', 'Questions,<br>answered.', $a['sub']) . "\n\n" . forgedseo_core_b_group('fseo-faq__list', $items),
        array('wrap' => 'fseo-faq__grid')
    );
}

/**
 * Dark masthead for About, Blog, legal and 404.
 *
 * @param array $a kicker, title, lede, buttons, class, title_class
 * @return string
 */
function forgedseo_core_s_masthead($a)
{
    $inner = forgedseo_core_b_p(esc_html($a['kicker']), 'fseo-kicker') . "\n\n"
        . forgedseo_core_b_h($a['title'], 1, isset($a['title_class']) ? $a['title_class'] : 'fseo-display fseo-display--l');
    if (!empty($a['lede'])) {
        $inner .= "\n\n" . forgedseo_core_b_p($a['lede'], 'fseo-lede');
    }
    if (!empty($a['buttons'])) {
        $inner .= "\n\n" . forgedseo_core_b_buttons($a['buttons']);
    }

    return forgedseo_core_s_band('dark', trim('fseo-masthead ' . (isset($a['class']) ? $a['class'] : '')), $inner);
}

/**
 * Head beside a prose column.
 *
 * @param array $a kicker, title, paragraphs
 * @return string
 */
function forgedseo_core_s_story($a)
{
    $prose = '';
    foreach ($a['paragraphs'] as $paragraph) {
        $prose .= forgedseo_core_b_p($paragraph) . "\n\n";
    }

    return forgedseo_core_s_band(
        'paper',
        'fseo-story',
        forgedseo_core_s_head($a['kicker'], $a['title']) . "\n\n" . forgedseo_core_b_group('fseo-prose', trim($prose)),
        array('wrap' => 'fseo-story__grid')
    );
}

/**
 * Narrow prose band (legal pages).
 *
 * @param array $blocks Each: [type, html] where type is h2|p|list.
 * @return string
 */
function forgedseo_core_s_prose($blocks)
{
    $inner = '';
    foreach ($blocks as $block) {
        if ($block[0] === 'h2') {
            $inner .= forgedseo_core_b_h($block[1], 2, '');
        } elseif ($block[0] === 'list') {
            $inner .= forgedseo_core_b_list($block[1], '');
        } else {
            $inner .= forgedseo_core_b_p($block[1]);
        }
        $inner .= "\n\n";
    }

    return forgedseo_core_s_band('paper', 'fseo-legal', forgedseo_core_b_group('fseo-prose', trim($inner)));
}

/**
 * Closing call-to-action band.
 *
 * @param array $a title, lede, buttons
 * @return string
 */
function forgedseo_core_s_cta_band($a)
{
    return forgedseo_core_s_band(
        'dark',
        'fseo-cta-band',
        forgedseo_core_b_group(
            'fseo-cta-band__copy',
            forgedseo_core_b_h($a['title'], 2, 'fseo-h2') . "\n\n" . forgedseo_core_b_p($a['lede'], 'fseo-lede')
        ) . "\n\n" . forgedseo_core_b_buttons($a['buttons']),
        array('wrap' => 'fseo-cta-band__grid')
    );
}

/**
 * Query Loop rendered as post cards.
 *
 * @param array  $query      Query block `query` attribute.
 * @param int    $query_id
 * @param string $after      Extra inner blocks (pagination, no-results).
 * @return string
 */
function forgedseo_core_s_post_cards($query, $query_id, $after = '')
{
    $card = forgedseo_core_b_group(
        'fseo-card',
        forgedseo_core_b_group('fseo-card__media', '<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->') . "\n\n"
        . forgedseo_core_b_group(
            'fseo-card__body',
            '<!-- wp:post-terms {"term":"category","className":"fseo-kicker"} /-->' . "\n\n"
            . '<!-- wp:post-title {"level":3,"isLink":true,"className":"fseo-h3"} /-->' . "\n\n"
            . '<!-- wp:post-excerpt {"excerptLength":24,"className":"fseo-card__excerpt"} /-->' . "\n\n"
            . '<!-- wp:post-date {"className":"fseo-card__date"} /-->'
        )
    );

    $template = '<!-- wp:post-template ' . serialize_block_attributes(array('className' => 'fseo-cards', 'layout' => array('type' => 'default'))) . ' -->'
        . "\n" . $card . "\n" . '<!-- /wp:post-template -->';

    return forgedseo_core_b(
        'query',
        array(
            'queryId'   => $query_id,
            'query'     => $query,
            'className' => 'fseo-query',
            'layout'    => array('type' => 'default'),
        ),
        '<div class="wp-block-query fseo-query">' . $template . ($after !== '' ? "\n\n" . $after : '') . '</div>'
    );
}

/**
 * Primary trial and demo buttons.
 *
 * @param string $demo_href
 * @return array
 */
function forgedseo_core_trial_buttons($demo_href = '#contact')
{
    return array(
        array('text' => 'Start 30-day free trial', 'href' => forgedseo_core_trial_url()),
        array('text' => 'Book a demo', 'href' => $demo_href, 'style' => 'outline'),
    );
}
