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
class Shabbat_Lockdown_Options {

    private $shabbat_lockdown_options;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        add_action('cmb2_admin_init', [$this, 'hsl_register_theme_options_metabox']);

        if (defined('SHABBAT_LOCKDOWN_VERSION')) {
            $this->version = SHABBAT_LOCKDOWN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'shabbat-lockdown';


        $this->plugin_admin = new Shabbat_Lockdown_Admin($this->plugin_name, $this->version);
    }

    public function hsl_register_theme_options_metabox() {
        $prefix = 'shl_';

        // print_r(get_option("sl_plugin_options"));


        /**
         * Registers options page menu item and form.
         */
        $cmb_options = new_cmb2_box(array(
            'id' => 'sl_plugin_options_page',
            'title' => esc_html__('Shabbat Lockdown Options', 'cmb2'),
            'object_types' => array('options-page'),
            /*
             * The following parameters are specific to the options-page box
             * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
             */
            'option_key' => 'sl_plugin_options', // The option key and admin menu page slug.
            'icon_url' => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
            // 'menu_title'      => esc_html__( 'Options', 'cmb2' ), // Falls back to 'title' (above).
            // 'parent_slug'     => 'themes.php', // Make options page a submenu item of the themes menu.
            // 'capability'      => 'manage_options', // Cap required to view options-page.
            // 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
            // 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
            // 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
            // 'save_button'     => esc_html__( 'Save Theme Options', 'cmb2' ), // The text for the options-page save button. Defaults to 'Save'.
            // 'disable_settings_errors' => true, // On settings pages (not options-general.php sub-pages), allows disabling.
            // 'message_cb'      => 'yourprefix_options_page_message_callback',
            'tabs' => array(
                'registration' => array(
                    'label' => __('Basic Setting', 'cmb2'),
                    'icon' => ''
                ),
                'api' => array(
                    'label' => __('API Setting', 'cmb2'),
                ),
                'shop' => array(
                    'label' => __('Shop Setting', 'cmb2'),
                    'icon' => ''
                ),
//                'log' => array(
//                    'label' => __('Log', 'cmb2'),
//                ),
            )
        ));

        /**
         * Options fields ids only need
         * to be unique within this box.
         * Prefix is not needed.
         */
        $cmb_options->add_field(array(
            'name' => 'Plugin Registration',
            'type' => 'title',
            'id' => 'wiki_test_title6',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('License Key', 'cmb2'),
            'id' => $prefix . 'license_key',
            'type' => 'text',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'desc' => $this->plugin_admin->license_key_validation_message(),
        ));

        $cmb_options->add_field(array(
            'name' => 'Plugin Activation',
            'type' => 'title',
            'id' => 'wiki_test_title9',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'desc' => 'Add purchase code here.',
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Enable Lockdown functionality', 'cmb2'),
            'id' => 'enable_lockdown',
            'type' => 'select',
            'show_option_none' => true,
            'options' => array(
                'yes' => esc_html__('Yes', 'cmb2'),
                'no' => esc_html__('No', 'cmb2'),
            ),
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'desc' => 'Enable or disable lockdown functionality. Lockdown will apply during sabbat time.',
        ));


        $cmb_options->add_field(array(
            'name' => 'Timezone',
            'type' => 'title',
            'id' => 'wiki_test_title785',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('current Time', 'cmb2'),
            'id' => $prefix . 'current_time',
            'type' => 'text',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'readonly' => 'readonly',
            ),
            'desc' => $this->plugin_admin->render_current_status_of_lockdown(),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Next Candle lighting time', 'cmb2'),
            'id' => $prefix . 'next_candle_lighting_time',
            'type' => 'text',
            'tab' => 'registration',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'readonly' => 'readonly',
            ),
            'desc' => $this->plugin_admin->render_next_candle_lighting_time(),
        ));

        $cmb_options->add_field(array(
            'name' => 'Location Setting',
            'desc' => 'Setting used for api ',
            'type' => 'title',
            'id' => 'wiki_test_title5',
            'tab' => 'api',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Latitude', 'cmb2'),
            'id' => $prefix . 'lat',
            'tab' => 'location',
            'type' => 'text',
            'tab' => 'api',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'required' => 'required',
            ),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Longitude', 'cmb2'),
            'id' => $prefix . 'long',
            'type' => 'text',
            'tab' => 'api',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'required' => 'required',
            ),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Timezone', 'cmb2'),
            'id' => $prefix . 'timezone',
            'type' => 'select_timezone',
            'tab' => 'api',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'required' => 'required',
            ),
        ));


        $cmb_options->add_field(array(
            'name' => 'Alert Bar Options',
            'desc' => 'Notification bar will be display throughout the site',
            'type' => 'title',
            'id' => 'wiki_test_title',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Enable Alert Bar', 'cmb2'),
            'id' => $prefix . 'enable_alert_bar',
            'desc' => 'Enable Alert bar',
            'type' => 'checkbox',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Display Close Button', 'cmb2'),
            'id' => $prefix . 'alert_bar_close',
            'desc' => 'Allow Customer to Hide Alert Bar',
            'type' => 'checkbox',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Alert Bar Message', 'cmb2'),
            'id' => $prefix . 'alert_bar_msgs',
            'type' => 'text',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Alert Bar background', 'cmb2'),
            'id' => $prefix . 'alert_bar_bg',
            'type' => 'colorpicker',
            'default' => '#ffffff',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Alert Bar text color', 'cmb2'),
            'id' => $prefix . 'alert_bar_textcolor',
            'type' => 'colorpicker',
            'default' => '#ffffff',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Alert Bar position', 'cmb2'),
            'id' => $prefix . 'alert_bar_pos',
            'type' => 'radio',
            'options' => array(
                'top' => __('Top', 'cmb2'),
                'bottom' => __('Bottom', 'cmb2'),
            ),
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => 'Add to Cart Functionality',
            'type' => 'title',
            'id' => 'wiki_test_title1',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Hide Add to cart', 'cmb2'),
            'id' => $prefix . 'hide_add_to_cart',
            'desc' => 'Hide Add To cart button while lockdown is active',
            'type' => 'checkbox',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => esc_html__('Hide payment Options', 'cmb2'),
            'id' => $prefix . 'hide_payment_option',
            'desc' => 'Hide peyment gateway while lockdown is active',
            'type' => 'checkbox',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));

        $cmb_options->add_field(array(
            'name' => 'Alert Button Options for Checkout',
            'type' => 'title',
            'id' => 'wiki_test_title2',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Alert Button Text', 'cmb2'),
            'id' => $prefix . 'placeorder_button_text',
            'type' => 'text',
            'tab' => 'shop',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
        ));


        $cmb_options->add_field(array(
            'name' => esc_html__('Api log', 'cmb2'),
            'id' => 'yourprefix_demo_textarea_code',
            'type' => 'textarea_code',
            'default' => $this->get_log_entry(),
            'tab' => 'log',
            'render_row_cb' => array('CMB2_Tabs', 'tabs_render_row_cb'),
            'attributes' => array(
                'disabled' => 'disabled',
                'readonly' => 'readonly',
            ),
        ));
    }

    function get_log_entry() {
        ob_start();
        if (!empty($log)) {
            echo $log['time'];
            echo var_dump($log['result']);
        }

        echo ob_get_clean();
    }

}

if (is_admin())
    $shabbat_lockdown = new Shabbat_Lockdown_Options();
  