<?php
/**
 * Plugin-owned block patterns for marketing pages.
 *
 * Section markup lives in `patterns/*.php` so layout ships with the plugin
 * (rsync deploy) instead of being edited in the DB. The Home page body is a
 * single `core/pattern` reference to `forgedseo/home` (see includes/home.php);
 * inner pages and blog/404 templates reference `forgedseo/page-*` and
 * `forgedseo/template-*` (see includes/pages.php).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_register_patterns', 9);

/**
 * First-month promo code applied at Stripe checkout.
 *
 * @return string
 */
function forgedseo_core_promo_code()
{
    return 'FORGED75';
}

/**
 * First-month promotional price (75% off $1,000).
 *
 * @return string
 */
function forgedseo_core_first_month_price()
{
    return '$250';
}

/**
 * Ongoing Managed Service price after the first month.
 *
 * @return string
 */
function forgedseo_core_monthly_price()
{
    return '$1,000';
}

/**
 * Stripe Payment Link for Managed Service, with FORGED75 prefilled.
 *
 * @return string
 */
function forgedseo_core_checkout_url()
{
    return 'https://buy.stripe.com/fZu7sL9wo2JYdiBbhd6Zy01?prefilled_promo_code=forged75';
}

/**
 * @return string
 */
function forgedseo_core_trial_url()
{
    return forgedseo_core_checkout_url();
}

/**
 * Header or body checkout button label.
 *
 * @param string $context header|body
 * @return string
 */
function forgedseo_core_checkout_label($context = 'body')
{
    return $context === 'header'
        ? __('Get 75% off', 'forgedseo-core')
        : __('Get 75% off first month', 'forgedseo-core');
}

/**
 * Hero fine print under the primary CTA.
 *
 * @return string
 */
function forgedseo_core_hero_fineprint()
{
    return sprintf(
        /* translators: 1: first-month price, 2: promo code, 3: monthly price */
        __('%1$s first month with code %2$s, then %3$s/month. Cancel anytime in month one.', 'forgedseo-core'),
        forgedseo_core_first_month_price(),
        forgedseo_core_promo_code(),
        forgedseo_core_monthly_price()
    );
}

/**
 * Plans-panel fine print under the Managed Service CTA.
 *
 * @return string
 */
function forgedseo_core_plans_fineprint()
{
    return sprintf(
        /* translators: 1: first-month price, 2: promo code, 3: monthly price */
        __('First month is %1$s with code %2$s (75%% off). Then %3$s/month. Cancel anytime in your first month.', 'forgedseo-core'),
        forgedseo_core_first_month_price(),
        forgedseo_core_promo_code(),
        forgedseo_core_monthly_price()
    );
}

/**
 * FORGED75 chip markup used next to first-month prices.
 *
 * @return string
 */
function forgedseo_core_promo_chip()
{
    return '<span class="fseo-promo-chip">' . esc_html(forgedseo_core_promo_code()) . '</span>';
}

/**
 * WPForms "Contact Form" embedded in the Home contact section.
 *
 * @return int
 */
function forgedseo_core_contact_form_id()
{
    return 74;
}

/**
 * @param string $path Relative to assets/
 * @return string
 */
function forgedseo_core_asset_url($path)
{
    return FORGEDSEO_CORE_URL . 'assets/' . ltrim((string) $path, '/');
}

/**
 * Home sections in render order: slug suffix => title.
 *
 * @return array<string, string>
 */
function forgedseo_core_home_sections()
{
    return array(
        'hero'     => __('Home: hero', 'forgedseo-core'),
        'proof'    => __('Home: pilot proof', 'forgedseo-core'),
        'pipeline' => __('Home: how it works', 'forgedseo-core'),
        'agents'   => __('Home: agents', 'forgedseo-core'),
        'why'      => __('Home: why ForgedSEO', 'forgedseo-core'),
        'plans'    => __('Home: plans', 'forgedseo-core'),
        'contact'  => __('Home: contact', 'forgedseo-core'),
    );
}

/**
 * Render a pattern file to block markup.
 *
 * @param string $name File name without extension under patterns/.
 * @return string
 */
function forgedseo_core_pattern_markup($name)
{
    $file = FORGEDSEO_CORE_DIR . 'patterns/' . sanitize_file_name($name) . '.php';
    if (!is_readable($file)) {
        return '';
    }

    ob_start();
    include $file;

    return trim((string) ob_get_clean());
}

/**
 * @return void
 */
function forgedseo_core_register_patterns()
{
    if (!function_exists('register_block_pattern')) {
        return;
    }

    if (function_exists('register_block_pattern_category')) {
        register_block_pattern_category('forgedseo', array('label' => __('ForgedSEO', 'forgedseo-core')));
    }

    $home = array();
    foreach (forgedseo_core_home_sections() as $slug => $title) {
        $markup = forgedseo_core_pattern_markup('home-' . $slug);
        if ($markup === '') {
            continue;
        }
        $home[] = $markup;

        register_block_pattern(
            'forgedseo/home-' . $slug,
            array(
                'title'      => $title,
                'categories' => array('forgedseo'),
                'content'    => $markup,
            )
        );
    }

    if (empty($home)) {
        return;
    }

    register_block_pattern(
        forgedseo_core_home_pattern_slug(),
        array(
            'title'      => __('Home page', 'forgedseo-core'),
            'categories' => array('forgedseo'),
            'inserter'   => false,
            'content'    => implode("\n\n", $home),
        )
    );

    foreach (forgedseo_core_page_pattern_files() as $file => $title) {
        $markup = forgedseo_core_pattern_markup($file);
        if ($markup === '') {
            continue;
        }

        register_block_pattern(
            'forgedseo/' . $file,
            array(
                'title'      => $title,
                'categories' => array('forgedseo'),
                'inserter'   => false,
                'content'    => $markup,
            )
        );
    }
}

/**
 * Whole-page and template patterns: file name under patterns/ => title.
 *
 * @return array<string, string>
 */
function forgedseo_core_page_pattern_files()
{
    return array(
        'page-service'    => __('Managed Service page', 'forgedseo-core'),
        'page-enterprise' => __('Enterprise PaaS page', 'forgedseo-core'),
        'page-about'      => __('About page', 'forgedseo-core'),
        'page-privacy'    => __('Privacy Policy page', 'forgedseo-core'),
        'page-terms'      => __('Terms of Service page', 'forgedseo-core'),
        'template-blog'   => __('Blog index template', 'forgedseo-core'),
        'template-single' => __('Single post template', 'forgedseo-core'),
        'template-404'    => __('Not found template', 'forgedseo-core'),
    );
}

/**
 * @return string
 */
function forgedseo_core_home_pattern_slug()
{
    return 'forgedseo/home';
}
