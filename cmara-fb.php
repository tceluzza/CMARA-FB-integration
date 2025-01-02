<?php
/**
 * Plugin Name: CMARA Facebook Integration
 * Plugin URI: https://www.cmara.org/
 * Description: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam sollicitudin elit non molestie tempor. Nam rhoncus eleifend libero in pulvinar. Integer scelerisque diam vitae cursus ornare. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Sed aliquam congue neque vel dignissim. Phasellus auctor tincidunt purus et dictum. Suspendisse quis velit dolor. In commodo consequat metus sed aliquet. Donec mollis odio nec massa dapibus, sed cursus est cursus.
 * Author: Thomas Celuzza
 * Version: 0.01
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/**
 * Activate the plugin.
 */
function cmarafb_activate() { 

}
register_activation_hook( __FILE__, 'cmarafb_activate' );

/**
 * Deactivation hook.
 */
function cmarafb_deactivate() {

}
register_deactivation_hook( __FILE__, 'cmarafb_deactivate' );

/**
 * Options page for the plugin
 */
function cmarafb_options_page_html() {
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }
  ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
      <?php
      // output security fields for the registered setting "wporg_options"
      settings_fields( 'wporg_options' );
      // output setting sections and their fields
      // (sections are registered for "wporg", each field is registered to a specific section)
      do_settings_sections( 'wporg' );
      // output save settings button
      submit_button( __( 'Save Settings', 'textdomain' ) );
      ?>
    </form>
  </div>
  <?php
}

function cmarafb_options_page() {
  add_submenu_page(
    'options-general.php',
    'CMARA FB',
    'CMARA FB Options',
    'manage_options',
    'cmara_fb',
    'cmarafb_options_page_html'
  );
}
add_action( 'admin_menu', 'cmarafb_options_page' );


?>