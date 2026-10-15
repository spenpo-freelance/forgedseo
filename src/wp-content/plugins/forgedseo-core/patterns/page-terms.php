<?php
/**
 * Terms of Service page.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sections = array(
    forgedseo_core_s_masthead(
        array(
            'class'       => 'fseo-masthead--compact',
            'kicker'      => 'Legal',
            'title'       => 'Terms of Service',
            'title_class' => 'fseo-display fseo-display--m',
            'lede'        => 'Last updated October 15, 2026',
        )
    ),
    forgedseo_core_s_prose(
        array(
            array('p', 'These terms govern your use of forgedseo.com and the ForgedSEO Managed Service and Enterprise PaaS (the “Service”). By starting a trial or using the Service, you agree to them.'),
            array('h2', 'The Service'),
            array('p', 'ForgedSEO researches, drafts and publishes content to your website using an agentic pipeline, either operated by us (Managed Service) or by your team (Enterprise PaaS). Scope, cadence and volume are agreed during onboarding.'),
            array('h2', 'Trial, billing and cancellation'),
            array(
                'list',
                array(
                    'Managed Service starts with a 30-day free trial. Unless you cancel before it ends, your subscription renews monthly at the price shown at checkout.',
                    'Payments are processed by Stripe. Fees are billed in advance and are non-refundable except where required by law.',
                    'You can cancel anytime; cancellation takes effect at the end of the current billing period.',
                    'Enterprise PaaS pricing and terms are agreed separately in writing.',
                ),
            ),
            array('h2', 'Your content and materials'),
            array('p', 'You own the content we publish for you and the materials you provide. You grant us permission to use those materials to deliver the Service, and you confirm you have the rights to share them.'),
            array('h2', 'Our platform'),
            array('p', 'We own the ForgedSEO software, pipeline, prompts and know-how. These terms do not transfer any rights in them to you beyond using the Service.'),
            array('h2', 'Your responsibilities'),
            array('p', 'You are responsible for the accuracy of information you provide, for approving content before publication where review gates are enabled, and for keeping your website credentials secure.'),
            array('h2', 'No ranking guarantees'),
            array('p', 'Search rankings and traffic depend on factors outside our control. We do not guarantee specific rankings, traffic or revenue.'),
            array('h2', 'Limitation of liability'),
            array('p', 'To the extent permitted by law, the Service is provided “as is”, and our total liability for any claim is limited to the fees you paid in the three months before the claim arose.'),
            array('h2', 'Changes'),
            array('p', 'We may update these terms. If changes are material, we will tell you before they take effect.'),
            array('h2', 'Contact'),
            array('p', 'Questions about these terms? Email <a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a>.'),
        )
    ),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
