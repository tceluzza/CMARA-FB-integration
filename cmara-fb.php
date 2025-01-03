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


class Data_Encryption {

	private $key;
	private $salt;

	public function __construct() {
		$this->key  = $this->get_default_key();
		$this->salt = $this->get_default_salt();
	}

	public function encrypt( $value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
		if ( ! $raw_value ) {
			return false;
		}

		return base64_encode( $iv . $raw_value ); 
	}

	public function decrypt( $raw_value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		$raw_value = base64_decode( $raw_value, true ); 

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		$value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $this->salt ) ) !== $this->salt ) {
			return false;
		}

		return substr( $value, 0, - strlen( $this->salt ) );
	}

	private function get_default_key() {
		if ( defined( 'FACEBOOKAPI_ENCRYPTION_KEY' ) && '' !== FACEBOOKAPI_ENCRYPTION_KEY ) {
			return FACEBOOKAPI_ENCRYPTION_KEY;
		}

		if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
			return LOGGED_IN_KEY;
		}

		// If this is reached, you're either not on a live site or have a serious security issue.
		return 'das-ist-kein-geheimer-schluessel';
	}

	private function get_default_salt() {
		if ( defined( 'FACEBOOKAPI_ENCRYPTION_SALT' ) && '' !== FACEBOOKAPI_ENCRYPTION_SALT ) {
			return FACEBOOKAPI_ENCRYPTION_SALT;
		}

		if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
			return LOGGED_IN_SALT;
		}

		// If this is reached, you're either not on a live site or have a serious security issue.
		return 'das-ist-kein-geheimes-salz';
	}
}


function cmarafb_activate() { 
  // set up the DB entries
  $data = array(
    'fbpage_access_token' => '',
    'fbpage_id' => ''
  );
  add_option('cmarafb_data', $data);

}
register_activation_hook( __FILE__, 'cmarafb_activate' );

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
			'label_for' => 'cmarafb_pageid_field'
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
  $encryption = new Data_Encryption;

	// get the value of the setting we've registered with register_setting()
	$setting = $encryption->decrypt( get_option('cmara_pageid') );
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

  // if ( isset( $_GET['settings-updated'] ) ) {
	// 	// add settings saved message with the class of "updated"
	// 	// add_settings_error( 'cmara_messages', 'cmara_message', __( 'Settings Saved', 'cmara' ), 'updated' );
	// }

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

// hook to post to facebook any time there's a new post PUBLISHED
function post_to_facebook( $new_status, $old_status, $post ) {
  if ( !( 'publish' === $new_status && 'publish' !== $old_status ) ) {
    return false;
  }


  // These should NOT be put here in plaintext, under ANY circumstances, and especially not
  // committed to GH. I put them here ONLY during dev testing on a LOCAL environment.
  // They should be stored (encrypted) in the database.
  // TODO: encrypt these suckers
  $page_access_token = 'facebook_page_access_token_here';
  $page_id = 'facebook_page_id_here';

  // what SHOULD be there, eventually
  // $page_access_token = decrypt( get from database(fbaccesstoken) );
  // $page_id = decrypt( get from database(fbpageid) );


  // Normal things: where we want to post
  $fb_api = 'https://graph.facebook.com/';
  $fb_endpoint = '/feed';

  // get perma-URL from the WP_Post object
  $post_url = get_permalink( $post );

  // what to send to the Meta API: the link plus our access token
  $data['link'] = $post_url;
  $data['access_token'] = $page_access_token;

  // create the final URL to send our request to
  $fb_url = $fb_api . $page_id . $fb_endpoint;

  // curl it up (post it) 
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $fb_url);       // our endpoint
  curl_setopt($ch, CURLOPT_POST, 1);            // post request
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  // payload
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $return = curl_exec($ch);
  curl_close($ch);
  // echo($return);
}
add_action( 'transition_post_status', 'post_to_facebook', 10, 3 );



?>