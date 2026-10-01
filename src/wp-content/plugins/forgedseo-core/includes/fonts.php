<?php
/**
 * Drop unused Fira Code from Twenty Twenty-Five; keep Manrope and Inter.
 *
 * TT5 registers Manrope + Fira Code via theme.json font faces. ForgedSEO
 * body/display type is self-hosted Inter (0.1.2). Fira Code is never used in
 * plugin CSS or marketing templates, so the variable woff2 is wasted weight.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('wp_theme_json_data_theme', 'forgedseo_core_theme_json_strip_fira_code');
add_filter('wp_theme_json_data_default', 'forgedseo_core_theme_json_strip_fira_code');
add_filter('wp_theme_json_data_user', 'forgedseo_core_theme_json_strip_fira_code');

/**
 * System stack for leftover mono / code styles.
 *
 * @return string
 */
function forgedseo_core_system_mono_stack()
{
    return 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace';
}

/**
 * @param mixed $family
 * @return bool
 */
function forgedseo_core_font_family_is_fira_code($family)
{
    if (!is_array($family)) {
        return is_string($family) && forgedseo_core_string_is_fira_code($family);
    }

    foreach (array('slug', 'name', 'fontFamily') as $key) {
        if (isset($family[$key]) && is_string($family[$key]) && forgedseo_core_string_is_fira_code($family[$key])) {
            return true;
        }
    }

    if (isset($family['fontFace']) && is_array($family['fontFace'])) {
        foreach ($family['fontFace'] as $face) {
            if (!is_array($face)) {
                continue;
            }
            if (isset($face['fontFamily']) && is_string($face['fontFamily']) && forgedseo_core_string_is_fira_code($face['fontFamily'])) {
                return true;
            }
            if (isset($face['src']) && forgedseo_core_src_is_fira_code($face['src'])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Fira Code only — do not match Fira Sans from unused TT5 style variations.
 *
 * @param string $value
 * @return bool
 */
function forgedseo_core_string_is_fira_code($value)
{
    if (!is_string($value) || $value === '') {
        return false;
    }

    $value = strtolower($value);
    if (false !== strpos($value, 'fira sans')) {
        return false;
    }

    return false !== strpos($value, 'fira-code')
        || false !== strpos($value, 'fira code')
        || false !== strpos($value, 'firacode');
}

/**
 * @param mixed $src
 * @return bool
 */
function forgedseo_core_src_is_fira_code($src)
{
    if (is_string($src)) {
        return forgedseo_core_string_is_fira_code($src);
    }
    if (!is_array($src)) {
        return false;
    }

    foreach ($src as $item) {
        if (forgedseo_core_src_is_fira_code($item)) {
            return true;
        }
    }

    return false;
}

/**
 * Remove Fira Code fontFamily/fontFace entries from a theme.json fontFamilies node.
 *
 * Handles both a flat list and origin-keyed maps (theme / custom / default).
 *
 * @param mixed $families
 * @return mixed
 */
function forgedseo_core_filter_font_families($families)
{
    if (!is_array($families)) {
        return $families;
    }

    $is_list = array_keys($families) === range(0, count($families) - 1);
    if ($is_list) {
        $out = array();
        foreach ($families as $family) {
            if (forgedseo_core_font_family_is_fira_code($family)) {
                continue;
            }
            $out[] = $family;
        }
        return $out;
    }

    $out = array();
    foreach ($families as $origin => $group) {
        $out[$origin] = forgedseo_core_filter_font_families($group);
    }

    return $out;
}

/**
 * Walk theme.json data: drop Fira Code faces and remap style refs to system mono.
 *
 * @param mixed $node
 * @return mixed
 */
function forgedseo_core_theme_json_without_fira_code($node)
{
    if (!is_array($node)) {
        return $node;
    }

    if (isset($node['fontFamilies'])) {
        $node['fontFamilies'] = forgedseo_core_filter_font_families($node['fontFamilies']);
    }

    if (isset($node['fontFamily']) && is_string($node['fontFamily']) && forgedseo_core_string_is_fira_code($node['fontFamily'])) {
        $node['fontFamily'] = forgedseo_core_system_mono_stack();
    }

    if (isset($node['fontFace']) && is_array($node['fontFace'])) {
        $faces = array();
        foreach ($node['fontFace'] as $face) {
            if (forgedseo_core_font_family_is_fira_code(array('fontFace' => array($face)))) {
                continue;
            }
            $faces[] = $face;
        }
        $node['fontFace'] = $faces;
    }

    foreach ($node as $key => $value) {
        if (is_array($value)) {
            $node[$key] = forgedseo_core_theme_json_without_fira_code($value);
        }
    }

    return $node;
}

/**
 * Flatten origin-keyed or nested fontFamilies to a list for update_with().
 *
 * @param mixed $families
 * @return array<int, mixed>
 */
function forgedseo_core_font_families_as_list($families)
{
    if (!is_array($families) || empty($families)) {
        return array();
    }

    $keys = array_keys($families);
    $is_list = $keys === range(0, count($families) - 1);
    if ($is_list) {
        return array_values($families);
    }

    $list = array();
    foreach ($families as $group) {
        if (is_array($group) && isset($group['slug'])) {
            $list[] = $group;
            continue;
        }
        $list = array_merge($list, forgedseo_core_font_families_as_list($group));
    }

    return $list;
}

/**
 * @param mixed $theme_json WP_Theme_JSON_Data
 * @return mixed
 */
function forgedseo_core_theme_json_strip_fira_code($theme_json)
{
    if (!is_object($theme_json) || !method_exists($theme_json, 'get_data') || !method_exists($theme_json, 'update_with')) {
        return $theme_json;
    }

    $data = $theme_json->get_data();
    if (!is_array($data)) {
        return $theme_json;
    }

    $updated = forgedseo_core_theme_json_without_fira_code($data);
    $payload = array(
        'version' => isset($updated['version']) ? $updated['version'] : 3,
    );

    if (isset($updated['settings']['typography']['fontFamilies'])) {
        $payload['settings'] = array(
            'typography' => array(
                'fontFamilies' => forgedseo_core_font_families_as_list(
                    $updated['settings']['typography']['fontFamilies']
                ),
            ),
        );
    }

    if (isset($updated['styles'])) {
        $payload['styles'] = $updated['styles'];
    }

    return $theme_json->update_with($payload);
}
