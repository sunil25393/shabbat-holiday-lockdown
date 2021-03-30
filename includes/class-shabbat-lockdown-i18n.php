<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       desite.co.il
 * @since      1.0.0
 *
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/includes
 * @author     Dor Meljon <office@desite.co.il>
 */
class Shabbat_Lockdown_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'shabbat-lockdown',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
