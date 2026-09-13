<?php
/**
 * Sync `_aioseo_*` post meta into AIOSEO's post model / aioseo_posts table.
 *
 * AIOSEO 4+/5+ stores titles and social tags in wp_aioseo_posts. Duplicate
 * `_aioseo_*` post meta is for localization (WPML etc.) and is ignored on
 * output unless saved through Models\Post.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_aioseo_maybe_migrate', 5);
add_action('added_post_meta', 'forgedseo_core_aioseo_on_meta_change', 10, 4);
add_action('updated_post_meta', 'forgedseo_core_aioseo_on_meta_change', 10, 4);

/**
 * WordPress page ID for forgedseo.com homepage.
 */
function forgedseo_core_homepage_post_id()
{
    return 4;
}

/**
 * Option that records the last completed AIOSEO table migrate.
 */
function forgedseo_core_aioseo_sync_option()
{
    return 'forgedseo_core_aioseo_sync';
}

/**
 * Canonical homepage title, description, and social fields.
 *
 * @return array<string, string>
 */
function forgedseo_core_homepage_seo_fields()
{
    $title = "ForgedSEO \u{2014} Agentic Content Engine";
    $description = 'ForgedSEO is the agentic content engine for teams that need search authority without an in-house SEO army. Managed service or Enterprise PaaS.';

    return array(
        'title'                => $title,
        'description'          => $description,
        'og_title'             => $title,
        'og_description'       => $description,
        'twitter_title'        => $title,
        'twitter_description'  => $description,
    );
}

/**
 * Map of localization post meta keys to AIOSEO model columns.
 *
 * @return array<string, string>
 */
function forgedseo_core_aioseo_meta_map()
{
    return array(
        '_aioseo_title'               => 'title',
        '_aioseo_description'         => 'description',
        '_aioseo_og_title'            => 'og_title',
        '_aioseo_og_description'      => 'og_description',
        '_aioseo_twitter_title'       => 'twitter_title',
        '_aioseo_twitter_description' => 'twitter_description',
    );
}

/**
 * Columns allowed in the $wpdb fallback (title/description/social only).
 *
 * @return array<string, bool>
 */
function forgedseo_core_aioseo_allowed_columns()
{
    return array(
        'title'               => true,
        'description'         => true,
        'og_title'            => true,
        'og_description'      => true,
        'twitter_title'       => true,
        'twitter_description' => true,
    );
}

/**
 * Whether AIOSEO's public Post model is available.
 *
 * @return bool
 */
function forgedseo_core_aioseo_has_model()
{
    return function_exists('aioseo')
        && class_exists('AIOSEO\\Plugin\\Common\\Models\\Post');
}

/**
 * @return string
 */
function forgedseo_core_aioseo_posts_table()
{
    global $wpdb;
    return $wpdb->prefix . 'aioseo_posts';
}

/**
 * One-shot migrate after deploy/upgrade: force homepage, then any title mismatches.
 *
 * Rsync deploys do not re-run activation hooks, so this lives on init and
 * retries until AIOSEO is present and the write succeeds.
 */
function forgedseo_core_aioseo_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_aioseo_sync_option(), '');
    if (version_compare($done, '0.1.3', '>=')) {
        return;
    }

    if (!forgedseo_core_aioseo_has_model() && !forgedseo_core_aioseo_table_exists()) {
        return;
    }

    $home_ok = forgedseo_core_aioseo_force_homepage();
    forgedseo_core_aioseo_sync_mismatched();

    if ($home_ok) {
        update_option(forgedseo_core_aioseo_sync_option(), '0.1.3', true);
    }
}

/**
 * Write canonical homepage SEO through AIOSEO so title and og:title stick.
 *
 * @return bool
 */
function forgedseo_core_aioseo_force_homepage()
{
    $post_id = forgedseo_core_homepage_post_id();
    if (!get_post($post_id)) {
        return false;
    }

    return forgedseo_core_aioseo_save_fields($post_id, forgedseo_core_homepage_seo_fields());
}

/**
 * Sync posts whose `_aioseo_title` meta differs from aioseo_posts.title.
 */
function forgedseo_core_aioseo_sync_mismatched()
{
    $post_ids = forgedseo_core_aioseo_mismatched_post_ids();
    foreach ($post_ids as $post_id) {
        $fields = forgedseo_core_aioseo_fields_from_meta($post_id);
        if (empty($fields)) {
            continue;
        }
        forgedseo_core_aioseo_save_fields($post_id, $fields);
    }
}

/**
 * @param int    $meta_id
 * @param int    $post_id
 * @param string $meta_key
 * @param mixed  $meta_value
 */
function forgedseo_core_aioseo_on_meta_change($meta_id, $post_id, $meta_key, $meta_value)
{
    $map = forgedseo_core_aioseo_meta_map();
    if (!isset($map[$meta_key]) || !is_string($meta_value)) {
        return;
    }

    $value = trim($meta_value);
    if ($value === '') {
        return;
    }

    forgedseo_core_aioseo_save_fields((int) $post_id, array($map[$meta_key] => $value));
}

/**
 * @param int $post_id
 * @return array<string, string>
 */
function forgedseo_core_aioseo_fields_from_meta($post_id)
{
    $fields = array();
    foreach (forgedseo_core_aioseo_meta_map() as $meta_key => $column) {
        $value = get_post_meta($post_id, $meta_key, true);
        if (!is_string($value)) {
            continue;
        }
        $value = trim($value);
        if ($value === '') {
            continue;
        }
        $fields[$column] = $value;
    }
    return $fields;
}

/**
 * Persist title/description/social fields via AIOSEO's model, else $wpdb.
 *
 * @param int                  $post_id
 * @param array<string, string> $data
 * @return bool
 */
function forgedseo_core_aioseo_save_fields($post_id, $data)
{
    static $syncing = array();

    $post_id = absint($post_id);
    $data = forgedseo_core_aioseo_sanitize_fields($data);
    if ($post_id <= 0 || empty($data) || isset($syncing[$post_id])) {
        return false;
    }

    $syncing[$post_id] = true;

    if (forgedseo_core_aioseo_has_model()) {
        $result = \AIOSEO\Plugin\Common\Models\Post::savePost($post_id, $data);
        $ok = !is_string($result) || $result === '';
        unset($syncing[$post_id]);
        return $ok;
    }

    $ok = forgedseo_core_aioseo_wpdb_update($post_id, $data);
    unset($syncing[$post_id]);
    return $ok;
}

/**
 * Drop anything that is not a non-empty string on an allowed column.
 *
 * @param array<string, mixed> $data
 * @return array<string, string>
 */
function forgedseo_core_aioseo_sanitize_fields($data)
{
    $allowed = forgedseo_core_aioseo_allowed_columns();
    $clean = array();
    foreach ($data as $column => $value) {
        if (!isset($allowed[$column]) || !is_string($value)) {
            continue;
        }
        $value = trim($value);
        if ($value === '') {
            continue;
        }
        $clean[$column] = $value;
    }
    return $clean;
}

/**
 * Targeted UPDATE of title/description/og/twitter columns for one post_id.
 *
 * @param int                   $post_id
 * @param array<string, string> $data
 * @return bool
 */
function forgedseo_core_aioseo_wpdb_update($post_id, $data)
{
    global $wpdb;

    if (!forgedseo_core_aioseo_table_exists()) {
        return false;
    }

    $table = forgedseo_core_aioseo_posts_table();
    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$table} WHERE post_id = %d LIMIT 1",
            $post_id
        )
    );
    if (!$exists) {
        return false;
    }

    $updated = $wpdb->update(
        $table,
        $data,
        array('post_id' => $post_id),
        array_fill(0, count($data), '%s'),
        array('%d')
    );

    return false !== $updated;
}

/**
 * @return bool
 */
function forgedseo_core_aioseo_table_exists()
{
    global $wpdb;
    $table = forgedseo_core_aioseo_posts_table();
    $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    return $found === $table;
}

/**
 * @return int[]
 */
function forgedseo_core_aioseo_mismatched_post_ids()
{
    global $wpdb;

    if (!forgedseo_core_aioseo_table_exists()) {
        return array();
    }

    $table = forgedseo_core_aioseo_posts_table();
    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT pm.post_id
             FROM {$wpdb->postmeta} pm
             LEFT JOIN {$table} ap ON ap.post_id = pm.post_id
             WHERE pm.meta_key = %s
               AND pm.meta_value <> ''
               AND (ap.post_id IS NULL OR ap.title IS NULL OR ap.title <> pm.meta_value)",
            '_aioseo_title'
        )
    );

    if (!is_array($ids)) {
        return array();
    }

    return array_map('intval', $ids);
}
