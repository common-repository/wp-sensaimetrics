<?php
/**
* Plugin Name: WP Sensaimetrics
* Plugin URI: https://sensaimetrics.io/
* Description: Sensaimetrics connector that avoids connections when logged in wp-admin.
* Author: SensaiMetrics
* Author URI:
* Version: 1.2.7
* Text Domain: wp-sensaimetrics
* Domain Path: /languages/
*
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*
* @package   WP-Sensaimetrics
* @author    sensaimetrics
* @category  Analytics
* @copyright SensaiMetrics
* @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
*/
define('WP_DEBUG', true);
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
define('FS_METHOD', 'direct');

require_once plugin_dir_path( __FILE__ ) . 'classes/class-wp-sensaimetrics-connector.php';

/**
* # WP Sensaimetrics Main Plugin Class
*
* ## Plugin Overview
*
* Plugin to connect your site with sensaimetrics changing the Sensaimetrics ID from wp-admin.
* Also it does not connect with SensaiMetrics while you are logged.
*
*/
class WP_Sensaimetrics {
  /** plugin version number */
  public static $version = '1.2.3';

  /** @var string the plugin file */
  public static $plugin_file = __FILE__;

  /** @var string the plugin file */
  public static $plugin_dir;

  /**
  * Initializes the plugin
  *
  * @since 0.0.1
  */
  public static function init() {
    self::$plugin_dir = dirname(__FILE__);

    $connector = new WP_Sensaimetrics_Connector();
    $connector->load();

    // Load translation files
    add_action('plugins_loaded', __CLASS__ . '::load_plugin_textdomain');

  }

  /**
  * Load our language settings for internationalization
  *
  * @since 0.0.1
  */
  public static function load_plugin_textdomain() {
    load_plugin_textdomain('wp-sensaimetrics', false, basename(self::$plugin_dir) . '/languages');
  }

}

WP_Sensaimetrics::init();