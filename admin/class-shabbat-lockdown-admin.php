<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       desite.co.il
 * @since      1.0.0
 *
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/admin
 * @author     Dor Meljon <office@desite.co.il>
 */
class Shabbat_Lockdown_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->options = get_option('sl_plugin_options');
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Shabbat_Lockdown_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Shabbat_Lockdown_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/shabbat-lockdown-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Shabbat_Lockdown_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Shabbat_Lockdown_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/shabbat-lockdown-admin.js', array('jquery'), $this->version, false);
    }

    public function get_current_status_of_lockdown() {
        $status = get_option('shabbat_holiday_lockdown_active');

        if ($status == 'yes') {
            return true;
        }
    }

    public function license_key_validation_message() {
        if (get_option('shl_license_key_validated')) {
            $validate_key = get_option('shl_license_key_validated');
            if ($validate_key == $this->options['shl_license_key']) {
                return '<span class="notice notice-success "><b>License key validated!</b></span>';
            }
        }
        return '<span class="notice notice-error "><b>Please enter valid license key here!</b></span>';
    }

    public function render_current_status_of_lockdown() {

        if ($this->options['enable_lockdown'] == 'yes' && $this->get_current_status_of_lockdown()) {
            return '<style>#shl_current_time{display:none;}</style><input type="text" class="regular-text" name="shl_current_time" id="" value="' . date_i18n('l, jS F Y, H:i:s') . '" data-hash="" readonly="readonly"><p class="sstatus " style="    width: 200px;    padding: 5px;    background: green;    color: #fff;">LOCKDOWN STATUS : ON </p>';
        } else {
            return '<style>#shl_current_time{display:none;}</style><input type="text" class="regular-text" name="shl_current_time" id="" value="' . date_i18n('l, jS F Y, H:i:s') . '" data-hash="" readonly="readonly"><p class="sstatus " style="    width: 200px;    padding: 5px;    background: red;    color: #fff;">LOCKDOWN STATUS : OFF </p>';
        }
    }

    public function render_next_candle_lighting_time() {
        $details = Shabbat_Lockdown::fetchNextSchedule_details();

        ob_start();

        if (!empty($details)) {
            echo '<ul>';
            foreach ($details as $key => $value) {
                if ($key == 'candles' || $key == 'havdalah') {
                    echo '<li><b>' . $key . ' : </b>';

                    $value = explode("+", $value);
                    $value = str_replace('T', ' ', $value[0]);

                    echo date_i18n('l, jS F Y, H:i:s', strtotime($value)) . '<li>';
                }
            }
            echo '</ul>';
        }
        echo '<style>#shl_next_candle_lighting_time{display:none;}</style>';
        return ob_get_clean();
    }

}
