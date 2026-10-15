<?php
/**
 * Home plans: Managed Service pricing and Enterprise PaaS early access.
 */

if (!defined('ABSPATH')) {
    exit;
}

$trial = esc_url(forgedseo_core_trial_url());
$managed = array(
    '3 SEO-optimized posts per week',
    'Proprietary research and fact-checking',
    'AI-generated featured images',
    'Direct WordPress publishing and formatting',
);
$enterprise = array(
    'Local or cloud deployment, set up with you',
    'Your proprietary data stays under your control',
    '1-on-1 demo of the Command Center and Strategist',
    'A say in which agents we build next',
);

$list = static function ($items) {
    $out = '<!-- wp:list {"className":"fseo-checks"} -->' . "\n" . '<ul class="wp-block-list fseo-checks">';
    foreach ($items as $item) {
        $out .= '<!-- wp:list-item -->' . "\n" . '<li>' . esc_html($item) . '</li>' . "\n" . '<!-- /wp:list-item -->';
    }

    return $out . '</ul>' . "\n" . '<!-- /wp:list -->';
};
?>
<!-- wp:group {"tagName":"section","align":"full","className":"fseo-s fseo-s--paper fseo-plans","layout":{"type":"default"}} -->
<section class="wp-block-group alignfull fseo-s fseo-s--paper fseo-plans"><!-- wp:group {"className":"fseo-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap"><!-- wp:group {"className":"fseo-head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-head"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Two ways to run it</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"fseo-h2"} -->
<h2 class="wp-block-heading fseo-h2">Invest in authority, not hourly rates.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-panels","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-panels"><!-- wp:group {"className":"fseo-panel","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-panel"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Managed Service</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"fseo-h3"} -->
<h3 class="wp-block-heading fseo-h3">We run the engine for you</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"fseo-price"} -->
<p class="fseo-price"><strong>$1,000</strong> / month</p>
<!-- /wp:paragraph -->

<?php echo $list($managed); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per item. ?>

<!-- wp:buttons {"className":"fseo-ctas"} -->
<div class="wp-block-buttons fseo-ctas"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo $trial; ?>">Start 30-day free trial</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"fseo-fineprint"} -->
<p class="fseo-fineprint">30-day free trial includes 12+ optimized posts. Cancel anytime in your first month. <a href="/service/">How Managed Service works</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-panel fseo-panel--dark","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-panel fseo-panel--dark"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Enterprise PaaS · Early access</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"fseo-h3"} -->
<h3 class="wp-block-heading fseo-h3">Your team runs the engine</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Deploy ForgedSEO locally or in the cloud and orchestrate campaigns from the Command Center. Built for agencies and technical SEOs.</p>
<!-- /wp:paragraph -->

<?php echo $list($enterprise); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per item. ?>

<!-- wp:buttons {"className":"fseo-ctas"} -->
<div class="wp-block-buttons fseo-ctas"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#contact">Inquire for early access</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"fseo-fineprint"} -->
<p class="fseo-fineprint"><a href="/enterprise/">Explore Enterprise PaaS</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
