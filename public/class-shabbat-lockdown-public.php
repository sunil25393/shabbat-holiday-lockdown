<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       desite.co.il
 * @since      1.0.0
 *
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Shabbat_Lockdown
 * @subpackage Shabbat_Lockdown/public
 * @author     Dor Meljon <office@desite.co.il>
 */
class Shabbat_Lockdown_Public {

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
    public $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('init', [$this, 'disable_woocomemrce_shop_func'], 1000);
        $this->plugin_admin = new Shabbat_Lockdown_Admin($this->plugin_name, $this->version);
        $this->options = get_option('sl_plugin_options');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/shabbat-lockdown-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/shabbat-lockdown-public.js', array('jquery'), $this->version, false);
    }

    public function disable_woocomemrce_shop_func() {

        if ($this->options['enable_lockdown'] == 'no') {
            return;
        }

        if (!$this->plugin_admin->get_current_status_of_lockdown()) {
            return;
        }

        if (is_checkout()) {
            header('Location:' . wc_get_cart_url());
            exit;
        }

        if ($this->is_hide_add_to_cart()) {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
        }

        remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        add_action('woocommerce_proceed_to_checkout', [$this, 'get_alertbutton']);

        add_action('woocommerce_widget_shopping_cart_before_buttons', [$this, 'render_widget_shopping_cart_before_buttons']);

        add_action('wp_footer', [$this, 'get_alertbar']);
    }

    public function is_hide_add_to_cart() {
        if ($this->options['shl_hide_add_to_cart'] == 'on') {
            return true;
        }
        return false;
    }

    public function get_alertbutton() {

        $text = $this->options['shl_placeorder_button_text'];
        $color = $this->options['shl_alert_bar_textcolor'];
        $bg_color = $this->options['shl_alert_bar_bg'];

        $color = ($color) ? $color : 'black';
        $bg_color = ($bg_color) ? $bg_color : '#fff';
        if (!$text) {
            return;
        }
        ?>
        <style>
            .zhours_alertbutton {
                color: <?= $color; ?>;
                background-color: <?= $bg_color; ?>;
                padding: <?= $size; ?>px; 
            }
        </style>
        <div class="zhours_alertbutton">
            <?= $text; ?>
        </div>
        <?php
    }

    public function get_position_styles() {
        $position = $this->options['shl_alert_bar_pos'];
        if ($position === 'top') {
            echo 'top: 0;';
        } else {
            echo 'bottom: 0;';
        }
    }

    public function get_alertbar() {

        $hide_alert_bar = $this->options['shl_enable_alert_bar'];
        if ($hide_alert_bar != 'on' || isset($_COOKIE['not_show_alert_bar'])) {
            return;
        }

        $message = $this->options['shl_alert_bar_msgs'];
        $color = $this->options['shl_alert_bar_textcolor'];
        $bg_color = $this->options['shl_alert_bar_bg'];
        $alert_position = $this->options['shl_alert_bar_pos'];


        $color = ($color) ? $color : 'black';
        $bg_color = ($bg_color) ? $bg_color : 'white';
        ?>
        <style>
            .zhours_alertbar {
                <?php
                $this->get_position_styles();
                ?>
                z-index: 1000;
                position: fixed;
                width: 100%;
                color: <?= $color; ?>;
                background-color: <?= $bg_color; ?>;  
                line-height: 1;
                text-align: center;
            } 
        </style>
        <div class="zhours_alertbar-space"></div>
        <div class="zhours_alertbar"> 
            <div class="zhours_alertbar-message">
                <?= $message; ?>
            </div>

            <div class="zhours_alertbar-close-box">
                <span class="close-box-icon"></span> 
            </div> 
        </div>
        <style>
            .zhours_alertbar {
                display: flex;
            }
            .zhours_alertbar-close-box {
                display: inline-block;
                float: right;
            }
            .close-box-icon {
                position: relative;
                right: 5px;
            }
            .zhours_alertbar-close-box {
                flex-grow: 1;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
            }
            .zhours_alertbar-branding {
                display: flex;
            }
            .zhours_alertbar-branding a {
                display: flex;
                align-items: center;
                color: <?= $color; ?>;
            }
            .zhours_alertbar-close-box img{
                cursor: pointer;
                width: 20px;
                display: inline-block !important;
            }
            .zhours_alertbar-message {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-grow: 300;
                padding: 10px 20px;
            }
            .zhours_alertbar-branding img {
                margin: 0 0.3rem;
                display: inline-block; 
                background-color: #ffffff;
                padding: 2px;
                border-radius: 50%;
            }
            @media (max-width: 600px) {
                .zhours_alertbar-branding-label {
                    display: none;
                }
            }
        </style>
        <script>
            jQuery(document).ready(function ($) {
                $('#zhours_alertbar-close').on('click', function () {
                    $('.zhours_alertbar').fadeOut();
                    $('.zhours_alertbar-space').fadeOut();
                    var now = new Date();
                    now.setTime(now.getTime() + 7 * 24 * 3600 * 1000);
                    document.cookie = "not_show_alert_bar=true; expires=" + now.toUTCString() + "; domain=<?= $this->get_formatted_site_url() ?>;path=/";
                });
            });
        </script>
        </script>
        <?php
    }

    public function get_formatted_site_url() {
        $url = get_site_url();
        $host = parse_url($url, PHP_URL_HOST);
        $names = explode(".", $host);

        if (count($names) == 1) {
            return $names[0];
        }

        $names = array_reverse($names);
        return $names[1] . '.' . $names[0];
    }

}
