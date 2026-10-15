<?php
/**
 * Home how-it-works: three-stage pipeline.
 */

if (!defined('ABSPATH')) {
    exit;
}

$steps = array(
    array('database', '01', 'Build your private knowledge base', 'Upload brand guidelines, whitepapers and case studies. A private retrieval index grounds every word in your actual business facts.'),
    array('cluster', '02', 'Generate clusters with one click', 'Pick the pillar you want to rank for. The Strategist agent plans a cluster of supporting posts that drive authority back to it.'),
    array('terminal', '03', 'Run it from the Command Center', 'Watch specialist agents research, outline, draft and illustrate in real time, then publish to WordPress with one click.'),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--paper fseo-pipeline","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--paper fseo-pipeline"><!-- wp:group {"className":"fseo-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap"><!-- wp:group {"className":"fseo-head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-head"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">How it works</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"fseo-h2"} -->
<h2 class="wp-block-heading fseo-h2">Stop prompting.<br>Start orchestrating.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"fseo-lede"} -->
<p class="fseo-lede">ForgedSEO replaces hours of prompting with a three-stage pipeline that turns one goal into a cluster of published, interlinked posts.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-steps","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-steps"><?php foreach ($steps as $step) : ?><!-- wp:group {"className":"fseo-step","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-step"><!-- wp:group {"className":"fseo-step__head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-step__head"><!-- wp:group {"className":"fseo-tile fseo-i-<?php echo esc_attr($step[0]); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-tile fseo-i-<?php echo esc_attr($step[0]); ?>"></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"fseo-step__num"} -->
<p class="fseo-step__num"><?php echo esc_html($step[1]); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"className":"fseo-h3"} -->
<h3 class="wp-block-heading fseo-h3"><?php echo esc_html($step[2]); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html($step[3]); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
