<?php
/**
 * Home agentic difference: specialist agents list.
 */

if (!defined('ABSPATH')) {
    exit;
}

$agents = array(
    array('search', '01', 'The Researcher', 'Scours the live web for real-time facts and citations specific to your industry.'),
    array('blueprint', '02', 'The Blueprinter', 'Structures the narrative paragraph by paragraph before a single word is drafted.'),
    array('image', '03', 'The Illustrator', 'Generates context-aware featured images that match the post, not stock filler.'),
    array('publish', '04', 'The Publisher', 'Handles WordPress categories, tags, media uploads and formatting for you.'),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--dark fseo-agents","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--dark fseo-agents"><!-- wp:group {"className":"fseo-wrap fseo-split","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap fseo-split"><!-- wp:group {"className":"fseo-head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-head"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">The agentic difference</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"fseo-h2"} -->
<h2 class="wp-block-heading fseo-h2">A pipeline of specialists, not a wall of text.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"fseo-lede"} -->
<p class="fseo-lede">Traditional AI writers hand you one long draft. ForgedSEO runs a versioned pipeline where each agent does one job well, and you approve the result before anything goes live.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-agents__list","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-agents__list"><?php foreach ($agents as $agent) : ?><!-- wp:group {"className":"fseo-agent","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-agent"><!-- wp:group {"className":"fseo-tile fseo-i-<?php echo esc_attr($agent[0]); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-tile fseo-i-<?php echo esc_attr($agent[0]); ?>"></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-agent__text","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-agent__text"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Agent <?php echo esc_html($agent[1]); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"fseo-h3"} -->
<h3 class="wp-block-heading fseo-h3"><?php echo esc_html($agent[2]); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html($agent[3]); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
