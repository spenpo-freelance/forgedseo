<?php
/**
 * Privacy Policy page.
 */

if (!defined('ABSPATH')) {
    exit;
}

$sections = array(
    forgedseo_core_s_masthead(
        array(
            'class'       => 'fseo-masthead--compact',
            'kicker'      => 'Legal',
            'title'       => 'Privacy Policy',
            'title_class' => 'fseo-display fseo-display--m',
            'lede'        => 'Last updated October 15, 2026',
        )
    ),
    forgedseo_core_s_prose(
        array(
            array('p', 'This policy explains what information ForgedSEO (“we”, “us”) collects when you visit forgedseo.com, contact us or start a trial, and how we use it.'),
            array('h2', 'Information we collect'),
            array(
                'list',
                array(
                    '<strong>Contact details</strong> you submit through our forms, such as your name, email address and message.',
                    '<strong>Billing details</strong> when you start a trial or subscribe. Payments are processed by Stripe; we do not store full card numbers.',
                    '<strong>Usage data</strong> such as pages visited, referring sites and approximate location, collected through Google Analytics.',
                    '<strong>Service data</strong> you share with us as a customer, such as brand guidelines, documents and WordPress access.',
                ),
            ),
            array('h2', 'How we use it'),
            array('p', 'We use this information to reply to you, deliver and improve the service you sign up for, process payments, and understand how the site is used. We do not sell your personal information.'),
            array('h2', 'Who we share it with'),
            array('p', 'We share information only with providers that help us run the business, such as our hosting provider, Stripe for payments and Google for analytics, or when required by law.'),
            array('h2', 'Cookies'),
            array('p', 'The site uses cookies for analytics and to keep forms and checkout working. You can block or delete cookies in your browser settings.'),
            array('h2', 'Retention and your choices'),
            array('p', 'We keep information for as long as we need it for the purposes above or as required by law. You can ask us to access, correct or delete your information at any time.'),
            array('h2', 'Contact'),
            array('p', 'Questions about this policy? Email <a href="mailto:forgedseo@spenpo.com">forgedseo@spenpo.com</a>.'),
        )
    ),
);

echo implode("\n\n", $sections); // phpcs:ignore WordPress.Security.EscapeOutput -- builders escape.
