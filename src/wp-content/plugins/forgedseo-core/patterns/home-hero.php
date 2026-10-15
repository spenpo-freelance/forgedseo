<?php
/**
 * Home hero: headline, CTAs, Command Center product window.
 */

if (!defined('ABSPATH')) {
    exit;
}

$trial = esc_url(forgedseo_core_trial_url());
$shot = esc_url(forgedseo_core_asset_url('img/forgedseo-command-center.webp'));
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--dark fseo-hero2","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--dark fseo-hero2"><!-- wp:group {"className":"fseo-wrap fseo-hero2__grid","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap fseo-hero2__grid"><!-- wp:group {"className":"fseo-hero2__copy","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-hero2__copy"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">The agentic content engine</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"fseo-display"} -->
<h1 class="wp-block-heading fseo-display">Consistent authority.<br>Zero overhead.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"fseo-lede"} -->
<p class="fseo-lede">ForgedSEO researches, writes and publishes search-ready posts grounded in your own business knowledge. We run it for you, or your team runs it on our platform.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"fseo-ctas"} -->
<div class="wp-block-buttons fseo-ctas"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $trial; ?>">Start 30-day free trial</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#contact">Book a demo</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"fseo-fineprint"} -->
<p class="fseo-fineprint">$1,000/month after a 30-day free trial. Cancel anytime in month one.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-window","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-window"><!-- wp:paragraph {"className":"fseo-window__bar"} -->
<p class="fseo-window__bar">Command Center</p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"fseo-window__shot"} -->
<figure class="wp-block-image size-full fseo-window__shot"><img src="<?php echo $shot; ?>" alt="ForgedSEO Command Center dashboard with Project Finder, Add Client, Upload Context, Create Pillar and Prompt Builder wizards"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"fseo-window__status"} -->
<p class="fseo-window__status">Cluster run · Researcher → Blueprinter → Illustrator → Publisher</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
