<?php
/**
 * Home why ForgedSEO: four differentiators.
 */

if (!defined('ABSPATH')) {
    exit;
}

$reasons = array(
    array('search', 'No generic AI filler', 'The Researcher cites live sources specific to your industry, so every post is factually grounded.'),
    array('graph', 'You own the context', 'We build a permanent knowledge graph of your business. The longer we work together, the sharper it gets.'),
    array('code', 'Developer-led reliability', 'A proprietary stack on Node.js, BullMQ and PostgreSQL with pgvector, built and maintained by an engineer.'),
    array('eye', 'You keep the publish button', 'Manual review gates mean nothing ships without your approval. Optional sync to your own Markdown vault.'),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--paper fseo-why","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--paper fseo-why"><!-- wp:group {"className":"fseo-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap"><!-- wp:group {"className":"fseo-head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-head"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Why ForgedSEO</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"fseo-h2"} -->
<h2 class="wp-block-heading fseo-h2">Better than a content mill.<br>Safer than a prompt.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-reasons","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-reasons"><?php foreach ($reasons as $reason) : ?><!-- wp:group {"className":"fseo-reason","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-reason"><!-- wp:group {"className":"fseo-icon fseo-i-<?php echo esc_attr($reason[0]); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-icon fseo-i-<?php echo esc_attr($reason[0]); ?>"></div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"className":"fseo-h3"} -->
<h3 class="wp-block-heading fseo-h3"><?php echo esc_html($reason[1]); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html($reason[2]); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
