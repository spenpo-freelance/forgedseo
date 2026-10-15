<?php
/**
 * Home pilot proof strip: three stats from the public pilot.
 */

if (!defined('ABSPATH')) {
    exit;
}

$stats = array(
    array('42', 'high-intent posts published on a consistent schedule', ''),
    array('350+', 'organic clicks every 28 days, starting from zero visibility', ' fseo-stat--hot'),
    array('3 / wk', 'posts per week. Velocity is the variable that compounds', ''),
);
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--dark fseo-proof","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--dark fseo-proof"><!-- wp:group {"className":"fseo-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Pilot client, first 90 days</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"fseo-stats","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-stats"><?php foreach ($stats as $stat) : ?><!-- wp:group {"className":"fseo-stat<?php echo esc_attr($stat[2]); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-stat<?php echo esc_attr($stat[2]); ?>"><!-- wp:paragraph {"className":"fseo-stat__num"} -->
<p class="fseo-stat__num"><?php echo esc_html($stat[0]); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"fseo-stat__label"} -->
<p class="fseo-stat__label"><?php echo esc_html($stat[1]); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
