<?php

  // No direct access to this file
  defined( 'ABSPATH' ) or die();

  $default_settings = array('sensaimetrics_id' => '', 'disable_for_admin' => 'yes');

  $settings = get_option( 'wp_sensaimetrics' );
  $settings = is_array($settings) ? $settings : $default_settings;
?>
<div class="wrap">
  <div id="icon-plugins" class="icon32"></div>
  <h2>Your Plugin Name</h2>
</div>
<div id="business-info-wrap" class="wrap">

  <h1 class="wp-heading-inline"><?php esc_html_e( 'Sensaimetrics', 'wp-sensaimetrics' ); ?></h1>

  <hr class="wp-header-end">

  <?php if ( isset( $_GET['settings-updated'] ) ) : ?>

    <div id="message" class="notice notice-success is-dismissible">
      <p><strong><?php esc_html_e( 'Settings saved.' ); ?></strong></p>
    </div>

  <?php endif; ?>

  <p><?php
    $url = 'https://sensaimetrics.io';
    $link = sprintf( wp_kses( __( 'Visit your <a href="%s" target="_blank">Sensaimetrics site list</a> and get the unique ID.', 'wp-sensaimetrics' ), array(  'a' => array( 'href' => array(), 'target' =>  '_blank' ) ) ), esc_url( $url ) );
    echo $link;
  ?></p>

  <form method="post" action="options.php">
    <?php settings_fields( 'wp_sensaimetrics' ); ?>

    <table class="form-table">

      <tbody>

        <tr>

          <th scope="row">
            <label for="wp_sensaimetrics_id"><?php esc_html_e( 'Sensaimetrics ID', 'wp-sensaimetrics' ); ?></label>
          </th>

          <td>
            <input type="text" name="wp_sensaimetrics[sensaimetrics_id]" id="wp_sensaimetrics_id" value="<?php echo $settings['sensaimetrics_id']; ?>" maxlength="10" />
            <p class="description" id="wp_sensaimetrics_id_description"><?php esc_html_e( '(Leave blank to disable)', 'wp-sensaimetrics' ); ?></p>
          </td>

        </tr>

        <tr>

          <th scope="row">
            <label for="wp_sensaimetrics_disable_for_admin"><?php esc_html_e( 'Disable for admin?', 'wp-sensaimetrics' ); ?></label>
          </th>

          <td>
            <input type="hidden" name="wp_sensaimetrics[disable_for_admin]" value="no">
            <input type="checkbox" name="wp_sensaimetrics[disable_for_admin]" id="wp_sensaimetrics_disable_for_admin" value="yes" <?php if('yes' === $settings['disable_for_admin']) { ?>checked="checked"<?php } ?> />
          </td>

        </tr>

      </tbody>

    </table>
    <input type="submit" class="button-primary" id="submit_button">

  </form>

</div>
