<?php
/**
 * Home contact: closing pitch and WPForms contact form (#contact).
 */

if (!defined('ABSPATH')) {
    exit;
}

$form_id = (int) forgedseo_core_contact_form_id();
?>
<!-- wp:group {"tagName":"section","anchor":"contact","align":"full","className":"fseo-s fseo-s--dark fseo-contact","layout":{"type":"default"}} -->
<section id="contact" class="wp-block-group alignfull fseo-s fseo-s--dark fseo-contact"><!-- wp:group {"className":"fseo-wrap fseo-contact__grid","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-wrap fseo-contact__grid"><!-- wp:group {"className":"fseo-head","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-head"><!-- wp:paragraph {"className":"fseo-kicker"} -->
<p class="fseo-kicker">Get in touch</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"fseo-display fseo-display--l"} -->
<h2 class="wp-block-heading fseo-display fseo-display--l">Let’s build your authority on autopilot.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"fseo-lede"} -->
<p class="fseo-lede">We take on a limited number of managed clients so every knowledge graph is meticulously maintained. Tell us about your site and we’ll map out your first 90 days.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"fseo-contact__email"} -->
<p class="fseo-contact__email">Prefer email? <a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"fseo-form-card","layout":{"type":"default"}} -->
<div class="wp-block-group fseo-form-card"><!-- wp:shortcode -->
[wpforms id="<?php echo $form_id; ?>" title="false"]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
