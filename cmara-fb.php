<?php
/**
 * Plugin Name: CMARA Facebook Integration
 * Plugin URI: https://www.cmara.org/
 * Description: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam sollicitudin elit non molestie tempor. Nam rhoncus eleifend libero in pulvinar. Integer scelerisque diam vitae cursus ornare. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Sed aliquam congue neque vel dignissim. Phasellus auctor tincidunt purus et dictum. Suspendisse quis velit dolor. In commodo consequat metus sed aliquet. Donec mollis odio nec massa dapibus, sed cursus est cursus.
 * Author: Thomas Celuzza
 * Author URI:  https://github.com/tceluzza/CMARA-FB-integration
 * Version: 0.01
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/**
 * Activate the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Invalid request.' );
}

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


function cmarafb_settings_init() {
  register_setting(
    'cmara',
    'cmara_pageid'
  );

  add_settings_section(
		'cmarafb_settings_section',
		__('CMARA Facebook Settings', 'cmara'),
    'cmarafb_section_callback',
		'cmara'
	);

  add_settings_field(
		'cmarafb_pageid_field',
		__('Facebook Page ID', 'cmara'),
    'cmarafb_pageid_callback',
		'cmara',
		'cmarafb_settings_section',
    array(
			'label_for'         => 'cmarafb_pageid_field'
		)
	);
}

/**
 * register wporg_settings_init to the admin_init action hook
 */
add_action('admin_init', 'cmarafb_settings_init');

/**
 * callback functions
 */

// section content cb
function cmarafb_section_callback() {
	echo '<p>CMARA FB Introduction.</p>';
}

// field content cb
function cmarafb_pageid_callback() {
	// get the value of the setting we've registered with register_setting()
	$setting = get_option('cmara_pageid');
	// output the field
	?>
	<input type="text" name="cmara_pageid" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <?php
}






function cmarafb_options_page_html() {
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'cmara_messages', 'cmara_message', __( 'Settings Saved', 'cmara' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'cmara_messages' );

  ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
      <?php
      // output security fields for the registered setting "wporg_options"
      settings_fields( 'cmara' );
      // output setting sections and their fields
      // (sections are registered for "wporg", each field is registered to a specific section)
      do_settings_sections( 'cmara' );
      // output save settings button
      submit_button( __( 'Save Settings' ) );
      ?>
    </form>
  </div>
  <?php
}




function cmarafb_options_page() {
  add_options_page(
    'CMARA Facebook Integration',
    'CMARA FB Options',
    'manage_options',
    'cmara',
    'cmarafb_options_page_html',
    20
  );
}
add_action( 'admin_menu', 'cmarafb_options_page' );


?>