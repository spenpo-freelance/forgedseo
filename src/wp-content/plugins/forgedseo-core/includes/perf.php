<?php
/**
 * Front-end perf cleanup: featured-image / Open Graph migrate.
 *
 * Rsync deploys do not re-run activation hooks, so this lives on init and
 * retries until the OG asset is in the Media Library and AIOSEO accepts it.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_perf_maybe_migrate', 6);

/**
 * Option that records the last completed perf cleanup.
 *
 * @return string
 */
function forgedseo_core_perf_cleanup_option()
{
    return 'forgedseo_core_perf_cleanup';
}

/**
 * Option storing the sideloaded 1200x630 OG attachment id.
 *
 * @return string
 */
function forgedseo_core_og_attachment_option()
{
    return 'forgedseo_core_og_attachment_id';
}

/**
 * Thin marketing pages that used the oversized logo as featured_media.
 *
 * @return int[]
 */
function forgedseo_core_perf_target_post_ids()
{
    return array(4, 25, 29, 37);
}

/**
 * Production attachment id for forged-seo-16-9-logo.jpeg.
 *
 * @return int
 */
function forgedseo_core_perf_logo_attachment_id()
{
    return 6;
}

/**
 * @return string
 */
function forgedseo_core_og_asset_basename()
{
    return 'forgedseo-og-1200x630.jpg';
}

/**
 * @return string
 */
function forgedseo_core_og_asset_path()
{
    return FORGEDSEO_CORE_DIR . 'assets/img/' . forgedseo_core_og_asset_basename();
}

/**
 * One-shot migrate after deploy/upgrade: drop logo thumbnails, sideload OG.
 *
 * @return void
 */
function forgedseo_core_perf_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_perf_cleanup_option(), '');
    if (version_compare($done, '0.1.4', '>=')) {
        return;
    }

    forgedseo_core_clear_logo_featured_images();

    $attachment_id = forgedseo_core_ensure_og_attachment();
    if ($attachment_id <= 0) {
        return;
    }

    $url = forgedseo_core_og_image_url();
    if ($url === '') {
        return;
    }

    if (!forgedseo_core_aioseo_has_model() && !forgedseo_core_aioseo_table_exists()) {
        return;
    }

    $home_ok = forgedseo_core_aioseo_force_homepage();
    forgedseo_core_aioseo_set_default_social_image($url);
    if (!$home_ok) {
        return;
    }

    update_option(forgedseo_core_perf_cleanup_option(), '0.1.4', true);
}

/**
 * Remove the oversized logo featured image from target pages when present.
 *
 * @return void
 */
function forgedseo_core_clear_logo_featured_images()
{
    foreach (forgedseo_core_perf_target_post_ids() as $post_id) {
        if (!get_post($post_id)) {
            continue;
        }
        if (!forgedseo_core_thumbnail_is_oversized_logo($post_id)) {
            continue;
        }
        delete_post_thumbnail($post_id);
    }
}

/**
 * @param int $post_id
 * @return bool
 */
function forgedseo_core_thumbnail_is_oversized_logo($post_id)
{
    $thumb_id = (int) get_post_thumbnail_id($post_id);
    if ($thumb_id <= 0) {
        return false;
    }

    return forgedseo_core_attachment_is_oversized_logo($thumb_id);
}

/**
 * True when the attachment is the known logo id or filename.
 *
 * @param int $attachment_id
 * @return bool
 */
function forgedseo_core_attachment_is_oversized_logo($attachment_id)
{
    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return false;
    }

    if ($attachment_id === forgedseo_core_perf_logo_attachment_id()) {
        return true;
    }

    $file = get_attached_file($attachment_id);
    if (!is_string($file) || $file === '') {
        return false;
    }

    return false !== strpos(basename($file), 'forged-seo-16-9-logo');
}

/**
 * Copy the plugin OG jpeg into the Media Library once.
 *
 * @return int Attachment id, or 0 on failure.
 */
function forgedseo_core_ensure_og_attachment()
{
    $existing = absint(get_option(forgedseo_core_og_attachment_option(), 0));
    if ($existing > 0 && forgedseo_core_og_attachment_is_valid($existing)) {
        return $existing;
    }

    $source = forgedseo_core_og_asset_path();
    if (!is_readable($source)) {
        return 0;
    }

    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $filename = forgedseo_core_og_asset_basename();
    $tmp = wp_tempnam($filename);
    if (!is_string($tmp) || $tmp === '') {
        return 0;
    }

    if (!copy($source, $tmp)) {
        @unlink($tmp);
        return 0;
    }

    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );

    $attachment_id = media_handle_sideload(
        $file_array,
        0,
        '',
        array(
            'post_title'   => 'ForgedSEO Open Graph',
            'post_content' => '',
            'post_excerpt' => '',
        )
    );

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        return 0;
    }

    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return 0;
    }

    update_post_meta($attachment_id, '_wp_attachment_image_alt', "ForgedSEO \u{2014} The Agentic Content Engine");
    update_option(forgedseo_core_og_attachment_option(), $attachment_id, true);

    return $attachment_id;
}

/**
 * @param int $attachment_id
 * @return bool
 */
function forgedseo_core_og_attachment_is_valid($attachment_id)
{
    return get_post_type($attachment_id) === 'attachment' && wp_attachment_is_image($attachment_id);
}

/**
 * Public URL of the sideloaded OG image, if any.
 *
 * @return string
 */
function forgedseo_core_og_image_url()
{
    $attachment_id = absint(get_option(forgedseo_core_og_attachment_option(), 0));
    if ($attachment_id <= 0 || !forgedseo_core_og_attachment_is_valid($attachment_id)) {
        return '';
    }

    $url = wp_get_attachment_url($attachment_id);
    return is_string($url) ? $url : '';
}

/**
 * AIOSEO post-model / wpdb fields for the 1200x630 OG default.
 *
 * @return array<string, string>
 */
function forgedseo_core_og_image_fields()
{
    $url = forgedseo_core_og_image_url();
    if ($url === '') {
        return array();
    }

    return array(
        'og_image_type'             => 'custom_image',
        'og_image_custom_url'       => $url,
        'og_image_url'              => $url,
        'og_image_width'            => '1200',
        'og_image_height'           => '630',
        'twitter_image_type'        => 'custom_image',
        'twitter_image_custom_url'  => $url,
        'twitter_image_url'         => $url,
    );
}
