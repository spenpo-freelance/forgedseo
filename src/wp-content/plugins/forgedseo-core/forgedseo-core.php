<?php
/**
 * Plugin Name: ForgedSEO Core
 * Description: Design tokens, typography, and marketing layout for forgedseo.com.
 * Version: 0.1.2
 * Author: ForgedSEO
 * Text Domain: forgedseo-core
 * Requires at least: 6.7
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FORGEDSEO_CORE_VERSION', '0.1.2');
define('FORGEDSEO_CORE_FILE', __FILE__);
define('FORGEDSEO_CORE_DIR', plugin_dir_path(__FILE__));
define('FORGEDSEO_CORE_URL', plugin_dir_url(__FILE__));

require_once FORGEDSEO_CORE_DIR . 'includes/enqueue.php';
require_once FORGEDSEO_CORE_DIR . 'includes/shortcodes.php';
