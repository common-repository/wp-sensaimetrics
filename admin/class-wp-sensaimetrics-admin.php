<?php

// No direct access to this file
defined( 'ABSPATH' ) or die();

class Wp_Sensaimetrics_Admin {
  public function create_nav_page() {
    add_menu_page(
      esc_html__( 'SensaiMetrics', 'wp-sensaimetrics' ), 
      esc_html__( 'SensaiMetrics', 'wp-sensaimetrics' ), 
      'manage_options',
      'wp_sensaimetrics_settings',
      'Wp_Sensaimetrics_Admin::build_view',
      plugin_dir_url( __FILE__ ) . '../images/20x20.png'
    );
  }

  public function register_my_setting() {
    register_setting( 'wp_sensaimetrics', 'wp_sensaimetrics' );
  }

  public static function build_view() {
    require_once plugin_dir_path( __FILE__ ) . 'views/wp-sensaimetrics-view.php';
  }
}
?>
