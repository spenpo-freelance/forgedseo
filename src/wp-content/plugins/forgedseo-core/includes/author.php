<?php
/**
 * One-shot migrate for the ForgedSEO author profile (Spencer Pope).
 *
 * Rsync deploys do not re-run activation hooks, so this lives on init and
 * retries until the user exists (looked up by email, never by ID) and the
 * write succeeds — same contract as the AIOSEO 0.1.5 marketing-page migrate.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'forgedseo_core_author_maybe_migrate', 20);
add_action('template_redirect', 'forgedseo_core_author_old_slug_redirect');

/**
 * Option that records the last completed author-profile migrate.
 *
 * @return string
 */
function forgedseo_core_author_option()
{
    return 'forgedseo_core_author_profile';
}

/**
 * Option storing the previous author nicename for a 301.
 *
 * @return string
 */
function forgedseo_core_author_old_nicename_option()
{
    return 'forgedseo_core_author_old_nicename';
}

/**
 * Version written to the option after a successful migrate.
 *
 * @return string
 */
function forgedseo_core_author_target_version()
{
    return '0.4.3';
}

/**
 * Production author email (lookup key; not an ID).
 *
 * @return string
 */
function forgedseo_core_author_email()
{
    return 'spenpo@spenpo.com';
}

/**
 * Canonical author archive slug.
 *
 * @return string
 */
function forgedseo_core_author_nicename()
{
    return 'spencer-pope';
}

/**
 * Canonical author biography.
 *
 * @return string
 */
function forgedseo_core_author_bio()
{
    return 'Spencer Pope builds ForgedSEO, an agentic system for keeping WordPress sites updated, optimized, and publishing. A former 4th grade teacher turned software engineer, he led an engineering team at Blockchains, Inc. before going independent, and now runs Shayano Labs, a studio building AI-assisted web operations for small businesses.';
}

/**
 * Canonical profile fields written by the migrate.
 *
 * @return array<string, string>
 */
function forgedseo_core_author_desired_fields()
{
    return array(
        'first_name'    => 'Spencer',
        'last_name'     => 'Pope',
        'display_name'  => 'Spencer Pope',
        'nickname'      => 'Spencer Pope',
        'user_nicename' => forgedseo_core_author_nicename(),
        'description'   => forgedseo_core_author_bio(),
    );
}

/**
 * One-shot migrate after deploy/upgrade: Spencer Pope profile + old-slug 301.
 *
 * @return void
 */
function forgedseo_core_author_maybe_migrate()
{
    $done = (string) get_option(forgedseo_core_author_option(), '');
    if (version_compare($done, forgedseo_core_author_target_version(), '>=')) {
        return;
    }

    if (!forgedseo_core_author_migrate_profile()) {
        return;
    }

    update_option(forgedseo_core_author_option(), forgedseo_core_author_target_version(), true);
}

/**
 * Look the author up by email and write canonical name, slug, and bio.
 *
 * @return bool True when the user exists and fields match (or the write succeeded).
 */
function forgedseo_core_author_migrate_profile()
{
    $user = get_user_by('email', forgedseo_core_author_email());
    if (!$user instanceof WP_User) {
        return false;
    }

    forgedseo_core_author_remember_old_nicename((string) $user->user_nicename);

    if (forgedseo_core_author_profile_is_current($user)) {
        return true;
    }

    $fields = forgedseo_core_author_desired_fields();
    $fields['ID'] = (int) $user->ID;

    $result = wp_update_user($fields);
    if (is_wp_error($result) || absint($result) <= 0) {
        return false;
    }

    $updated = get_userdata(absint($result));

    return $updated instanceof WP_User && forgedseo_core_author_profile_is_current($updated);
}

/**
 * Persist the previous nicename once so /author/{old}/ can 301.
 *
 * @param string $nicename
 * @return void
 */
function forgedseo_core_author_remember_old_nicename($nicename)
{
    $nicename = sanitize_title((string) $nicename);
    $desired = forgedseo_core_author_nicename();
    if ($nicename === '' || $nicename === $desired) {
        return;
    }

    $stored = (string) get_option(forgedseo_core_author_old_nicename_option(), '');
    if ($stored !== '') {
        return;
    }

    update_option(forgedseo_core_author_old_nicename_option(), $nicename, true);
}

/**
 * @param WP_User $user
 * @return bool
 */
function forgedseo_core_author_profile_is_current($user)
{
    if (!$user instanceof WP_User) {
        return false;
    }

    foreach (forgedseo_core_author_desired_fields() as $key => $value) {
        $current = (string) $user->get($key);
        if ($current !== $value) {
            return false;
        }
    }

    return true;
}

/**
 * 301 the previous author archive URL to /author/spencer-pope/.
 *
 * Runs on 404 as well: after the nicename change the old slug is not an author.
 *
 * @return void
 */
function forgedseo_core_author_old_slug_redirect()
{
    $from = sanitize_title((string) get_option(forgedseo_core_author_old_nicename_option(), ''));
    $to = forgedseo_core_author_nicename();
    if ($from === '' || $from === $to) {
        return;
    }

    global $wp;
    $request = isset($wp->request) ? trim((string) $wp->request, '/') : '';
    if ($request === '') {
        return;
    }

    $prefix = 'author/' . $from;
    if ($request !== $prefix && strpos($request, $prefix . '/') !== 0) {
        return;
    }

    $suffix = ($request === $prefix) ? '' : substr($request, strlen($prefix));
    $target = user_trailingslashit(home_url('/author/' . $to . $suffix));
    wp_safe_redirect($target, 301);
    exit;
}
