<?php

/**
 * Plugin Name: Shabbat Lockdown 111
 * Version:     1.0.4
 * Author:      Ido Friedlander
 * Author URI:  https://github.com/idofri
 */
class Shabbat_Lockdown {

    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    const FORMAT = 'json';
    const GEO_CITY = 'Jerusalem';
    const HAVDALAH_MINUTES = 50;
    const API_BASE_URL = 'https://www.hebcal.com/shabbat/';
    const TRANSIENT = 'candle_lighting_time';
    const LM_HOST = 'https://woocommerce-512477-1626209.cloudwaysapps.com';
    const LM_consumer_key = 'ck_66b9107d5daf888c8bcae8b7da981b513f8c05ce';
    const LM_secret_key = 'cs_549940a44890fa7d1b143949645a1bc763567943';

    private $LATITUDE;
    private $LONGITUDE;
    private $TIMEZONE;

    public static function instance() {
        static $instance;
        return $instance ?? ($instance = new static);
    }

    public function __construct() {

//  add_action('shabbat_schedule', [$this, 'schedule']);
//  add_action('template_redirect', [$this, 'observe']);
//   add_action('deleted_transient', [$this, 'activate']);
// add_action('run_shabbat_api', [$this, 'hsl_activate']);
//   add_action('init', [$this, 'hsl_activate']);
        /**
         * Hook into the 'admin_notices' action to render
         * a generic HTML notice.
         */
        add_action('admin_notices', [$this, 'myguten_admin_notice']);
        add_action('init', [$this, 'wi_create_daily_backup_schedule']);

        add_action('wi_create_daily_backup28', [$this, 'hsl_activate']);

        if (defined('SHABBAT_LOCKDOWN_VERSION')) {
            $this->version = SHABBAT_LOCKDOWN_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_name = 'shabbat-lockdown';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        $this->options = get_option('sl_plugin_options');


        $this->LATITUDE = 40.7128;
        $this->LONGITUDE = 74.0060;
        $this->TIMEZONE = 'America/New_York';
        if (isset($this->options['shl_lat']) && isset($this->options['shl_long']) && isset($this->options['shl_timezone']) && isset($this->options['shl_license_key']) && $this->options['shl_lat'] != '' && $this->options['shl_long'] != '' && $this->options['shl_timezone'] != '') {

            $this->LATITUDE = $this->options['shl_lat'];
            $this->LONGITUDE = $this->options['shl_long'];
            $this->TIMEZONE = $this->options['shl_timezone'];
        }
    }

    public function myguten_admin_notice() {
        $screen = get_current_screen();


        // Only render this notice in the post editor.
        if (!$screen || 'sl_plugin_options' !== $screen->parent_base) {
            return;
        }
        // Render the notice's HTML.
        // Each notice should be wrapped in a <div>
        // with a 'notice' class.

        if (get_option('shl_license_key_validated')) {
            $validate_key = get_option('shl_license_key_validated');

            if (!$this->is_licesence_key_empty() && $validate_key == $this->options['shl_license_key']) {
                return;
            }
        }

        $validation = $this->validate_shl_license_key();
        if (!$validation['status']) {

            echo '<div class="notice notice-error is-dismissible"><p>';
            echo $validation['message'];
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo $validation['message'];
            echo '</p></div>';
        }
    }

    public function is_licesence_key_empty() {
        if (empty($this->options)) {
            return true;
        }
        if (!isset($this->options['shl_license_key']) || $this->options['shl_license_key'] == '') {
            return true;
        }
        return false;
    }

    public function validate_shl_license_key() {
        if ($this->is_licesence_key_empty()) {
            return array('status' => false, 'message' => 'Please enter valid license key');
        }
        $license_key = $this->options['shl_license_key'];
        if ($this->validate_inputed_license_key()) {
            if ($this->activate_validated_license_key()) {
                update_option('shl_license_key_validated', $license_key);
                return array('status' => true, 'message' => 'License key activated!');
            } else {
                update_option('shl_license_key_validated', '');
                return array('status' => false, 'message' => 'License key is already activated! If license key isnt activated by you. Please contact plugin Author.');
            }
        } else {
            update_option('shl_license_key_validated', '');
            return array('status' => false, 'message' => 'Invalid license key');
        }
        return array('status' => false, 'message' => 'Error in validating license key');
    }

    public function validate_inputed_license_key() {
        $license_key = $this->options['shl_license_key'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::LM_HOST . "/wp-json/lmfwc/v2/licenses/validate/$license_key?consumer_key=" . self::LM_consumer_key . "&consumer_secret=" . self::LM_secret_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return array('status' => false, 'message' => 'Error in validating license key');
        } else {
            $response = json_decode($response);

            if (isset($response->success) && $response->success) {
                return true;
            }
        }
        return false;
    }

    public function activate_validated_license_key() {
        $curl = curl_init();
        $license_key = $this->options['shl_license_key'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::LM_HOST . "/wp-json/lmfwc/v2/licenses/activate/$license_key?consumer_key=" . self::LM_consumer_key . "&consumer_secret=" . self::LM_secret_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return array('status' => false, 'message' => 'Error in validating license key');
        } else {
            $response = json_decode($response);

            if (isset($response->success) && $response->success) {
                return true;
            }
        }
        return false;
    }

    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shabbat-lockdown-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shabbat-lockdown-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-shabbat-lockdown-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-shabbat-lockdown-public.php';

        $this->loader = new Shabbat_Lockdown_Loader();
    }

    private function set_locale() {

        $plugin_i18n = new Shabbat_Lockdown_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Shabbat_Lockdown_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Shabbat_Lockdown_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Shabbat_Lockdown_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function wi_create_daily_backup_schedule() {
//Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled('wi_create_daily_backup28');

//If $timestamp == false schedule daily backups since it hasn't been done previously
        if ($timestamp == false) {
//Schedule the event for right now, then to repeat daily using the hook 'wi_create_daily_backup'
            wp_schedule_event(time(), 'every_minute', 'wi_create_daily_backup28');
        }
    }

    public function hsl_activate($transient = '') {

        list($startTime, $endTime) = $this->fetchNextSchedule();
        $currentTime = strtotime(date("Y-m-d h:i:sa"));

        if ($startTime < $currentTime && $currentTime < $endTime) {
            update_option('shabbat_holiday_lockdown_active', 'yes');
        } else {
            update_option('shabbat_holiday_lockdown_active', 'no');
        }
    }

    public function fetchNextSchedule() {

        if (isset($this->options['shl_lat']) && isset($this->options['shl_long']) && isset($this->options['shl_timezone'])) {
            $response = wp_remote_get(
                    add_query_arg([
                'cfg' => self::FORMAT,
                'geo' => 'pos',
                'latitude' => $this->LATITUDE,
                'longitude' => $this->LONGITUDE,
                'tzid' => $this->TIMEZONE,
                'm' => self::HAVDALAH_MINUTES
                            ], self::API_BASE_URL)
            );

            if (is_wp_error($response)) {
                return [false, false];
            }

            $result = json_decode(wp_remote_retrieve_body($response), true);
            $schedule = wp_list_pluck($result['items'] ?? [], 'date', 'category');
            /*
              return [
              strtotime('2020-11-03T16:05:00+02:00' ?? null),
              strtotime($schedule['havdalah'] ?? null),
              ];

             */
            $schedule['candles'] = explode("+", $schedule['candles']);
            $schedule['candles'] = str_replace('T', ' ', $schedule['candles'][0]);

            $schedule['havdalah'] = explode("+", $schedule['havdalah']);
            $schedule['havdalah'] = str_replace('T', ' ', $schedule['havdalah'][0]);

            return [
                strtotime($schedule['candles'] ?? null),
                strtotime($schedule['havdalah'] ?? null),
            ];
        }
    }

    public static function fetchNextSchedule_details() {
        $options = get_option('sl_plugin_options');
        if (isset($options['shl_lat']) && isset($options['shl_long']) && isset($options['shl_timezone'])) {

            $response = wp_remote_get(
                    add_query_arg([
                'cfg' => self::FORMAT,
                'geo' => 'pos',
                'latitude' => $options['shl_lat'],
                'longitude' => $options['shl_long'],
                'tzid' => $options['shl_timezone'],
                'm' => self::HAVDALAH_MINUTES
                            ], self::API_BASE_URL)
            );

            if (is_wp_error($response)) {
                return [false, false];
            }

            $result = json_decode(wp_remote_retrieve_body($response), true);

            $schedule = wp_list_pluck($result['items'] ?? [], 'date', 'category');

            return $schedule;
        }
    }

}

$plugin = Shabbat_Lockdown::instance();
add_action('activated_shabbat_lockdown', [$plugin, 'hsl_activate']);
