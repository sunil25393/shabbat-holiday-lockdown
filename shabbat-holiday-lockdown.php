<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              desite.co.il
 * @since             1.0.0
 * @package           Shabbat_Lockdown
 *
 * @wordpress-plugin
 * Plugin Name:       Shabbat Holiday Lockdown
 * Plugin URI:        desite.co.il
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Dor Meljon
 * Author URI:        desite.co.il
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       shabbat-lockdown
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SHABBAT_LOCKDOWN_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shabbat-lockdown-activator.php
 */

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shabbat-lockdown-deactivator.php
 */
function deactivate_shabbat_lockdown() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-shabbat-lockdown-deactivator.php';
    Shabbat_Lockdown_Deactivator::deactivate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-shabbat-lockdown.php';
require plugin_dir_path(__FILE__) . 'includes/class-shabbat-lockdown-setting.php';


if (file_exists(plugin_dir_path(__FILE__) . 'includes/cmb2/init.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/cmb2/init.php';
    require_once plugin_dir_path(__FILE__) . 'includes/cmb2-tabs/CMB2-Tabs.php';
} elseif (file_exists(plugin_dir_path(__FILE__) . 'includes/CMB2/init.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/CMB2/init.php';
    require_once plugin_dir_path(__FILE__) . 'includes/cmb2-tabs/CMB2-Tabs.php';
}

function activate_shabbat_lockdown() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-shabbat-lockdown-activator.php';
    Shabbat_Lockdown_Activator::activate();

    do_action('activated_shabbat_lockdown');
}

register_activation_hook(__FILE__, 'activate_shabbat_lockdown');
register_deactivation_hook(__FILE__, 'deactivate_shabbat_lockdown');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */