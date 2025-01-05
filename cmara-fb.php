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



/**	
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */
 class CMARAFacebookIntegration {
	private $cmarafb_options;
	// private $encryption;

	public function __construct() {
		// $encryption = new Data_Encryption();
		
		add_action( 'admin_menu', array( $this, 'cmarafb_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'cmarafb_page_init' ) );
	}

	public function cmarafb_add_plugin_page() {
		add_options_page(
			'CMARA Facebook Integration', // page_title
			'CMARA Facebook Integration', // menu_title
			'manage_options', // capability
			'cmarafb', // menu_slug
			array( $this, 'cmarafb_create_admin_page' ) // function
		);
	}

	private function cmarafb_get_options( $name ) {
		$encryption = new Data_Encryption();

		$db_data = get_option( 'cmarafb_option_name' ); // Array of All Options
		foreach ($db_data as &$option) {
			$option = $encryption->decrypt($option);
		}

		return $db_data;
	}

	public function cmarafb_create_admin_page() {
		$this->cmarafb_options = $this->cmarafb_get_options( 'cmarafb_option_name' ); 
		?>
		<div class="wrap">
			<h2>CMARA Facebook Integration</h2>
			<p>Use this page to set (or re-set) the Page ID and Access Tokens for posting to Facebook automatically. Values are not shown after being set to maintain privacy and security.</p>
			<p>WARNING: Clicking "Save" will IMMEDIATELY OVERWRITE the Page ID and Access Token stored in the database. This CANNOT be undone!</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'cmarafb_option_group' );
					do_settings_sections( 'cmarafb-admin' );
					submit_button('Save');
				?>
			</form>
		</div>
	<?php }

	public function cmarafb_page_init() {
		register_setting(
			'cmarafb_option_group', // option_group
			'cmarafb_option_name', // option_name
			array( $this, 'cmarafb_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'cmarafb_setting_section', // id
			'Settings', // title
			array( $this, 'cmarafb_section_info' ), // callback
			'cmarafb-admin' // page
		);

		add_settings_field(
			'page_id_0', // id
			'Page ID', // title
			array( $this, 'page_id_0_callback' ), // callback
			'cmarafb-admin', // page
			'cmarafb_setting_section' // section
		);

		add_settings_field(
			'page_access_token_1', // id
			'Page Access Token', // title
			array( $this, 'page_access_token_1_callback' ), // callback
			'cmarafb-admin', // page
			'cmarafb_setting_section' // section
		);
	}

	public function cmarafb_sanitize($input) {
		$encryption = new Data_Encryption();
		$sanitary_values = array();
		if ( isset( $input['page_id_0'] ) ) {
			// $sanitary_values['page_id_0'] = sanitize_text_field( $input['page_id_0'] );
			$sanitary_values['page_id_0'] = $encryption->encrypt( $input['page_id_0'] );
		}

		if ( isset( $input['page_access_token_1'] ) ) {
			// $sanitary_values['page_access_token_1'] = sanitize_text_field( $input['page_access_token_1'] );
			$sanitary_values['page_access_token_1'] = $encryption->encrypt( $input['page_access_token_1'] );
		}

		return $sanitary_values;
	}

	public function cmarafb_section_info() {
		
	}

	public function page_id_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="cmarafb_option_name[page_id_0]" id="page_id_0" value="%s">',
			isset( $this->cmarafb_options['page_id_0'] ) ? '********' : ''
		);
	}

	public function page_access_token_1_callback() {
		printf(
			'<input class="regular-text" type="password" name="cmarafb_option_name[page_access_token_1]" id="page_access_token_1" value="%s">',
			isset( $this->cmarafb_options['page_access_token_1'] ) ? '****************' : ''
		);
	}

}
if ( is_admin() )
	$cmarafb = new CMARAFacebookIntegration();

/* 
 * Retrieve this value with:
 * $cmarafb_options = get_option( 'cmarafb_option_name' ); // Array of All Options
 * $page_id_0 = $cmarafb_options['page_id_0']; // Page ID
 * $page_access_token_1 = $cmarafb_options['page_access_token_1']; // Page Access Token
 */


// hook to post to facebook any time there's a new post PUBLISHED
function post_to_facebook( $new_status, $old_status, $post ) {
  if ( !( 'publish' === $new_status && 'publish' !== $old_status ) ) {
    return false;
  }

	$encryption = new Data_Encryption();

	// Take the encrypted values and decrypt them
	$db_data = get_option( 'cmarafb_option_name' ); // Array of All Options
	$page_id = $encryption->decrypt( $db_data['page_id_0'] ); // Page ID
	$page_access_token = $encryption->decrypt( $db_data['page_access_token_1'] ); // Page Access Token

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