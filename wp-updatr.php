<?php
/**
 * Plugin Name: WP Updatr
 * Description: Making it easy to launch and support paid WordPress products by integrating with WooCommerce, Paid Memberships Pro & Easy Digital Downloads to generate API Keys/Product Licenses for your customers.
 * Author: WP Updatr
 * Author URI: https://wpupdatr.com/
 * Version: 1.0.0
 */

require_once plugin_dir_path( __FILE__ ).'class.wp_updatr.php';

function wp_updatr_load_integration(){

	switch( get_option( 'wp_updatr_integration' ) ){
		case 'woocommerce':
			require_once plugin_dir_path( __FILE__ ).'compatibility/woocommerce.php';
			break;
		case 'paid-memberships-pro':
			require_once plugin_dir_path( __FILE__ ).'compatibility/paid-memberships-pro.php';
			break;
		case 'easy-digital-downloads':
			require_once plugin_dir_path( __FILE__ ).'compatibility/easy-digital-downloads.php';
			break;
	}

	load_plugin_textdomain( 'wp-updatr', false, basename( dirname( __FILE__ ) ) . '/languages' );

}
add_action( 'plugins_loaded', 'wp_updatr_load_integration' );

function wpupdatr_admin_menu(){

	add_submenu_page( 'options-general.php', __( 'WP Updatr', 'wp-updatr' ), __( 'WP Updatr', 'wp-updatr' ), 'manage_options', 'wp-updatr-settings', 'wp_updatr_menu_content' );

}
add_action( 'admin_menu', 'wpupdatr_admin_menu', 99 );

function wp_updatr_menu_content(){

	require_once plugin_dir_path( __FILE__ ).'settings.php';

}

function wp_updatr_validate_api_key(){

	$wpupdatr = new WP_Updatr();

	$valid = $wpupdatr->validate_client_api_key();

	return $valid;

}

function wp_updatr_save_settings(){

	if( isset( $_POST['wpur_save_settings'] ) ){

		$api_key = isset( $_POST['wpur_api_key'] ) ? $_POST['wpur_api_key'] : '';
		update_option( 'wp_updatr_api_key', $api_key );

		$integration = isset( $_POST['wpur_integration'] ) ? $_POST['wpur_integration'] : '';
		update_option( 'wp_updatr_integration', $integration );

	}

}
add_action( 'admin_init', 'wp_updatr_save_settings' );

function wp_updatr_descriptions( $key ){

	$descriptions = array(
		'key' 		=> __('Login to your WP Updatr account and navigate to "Products" to obtain a product key.', 'wp-updatr'),
		'limit' 	=> __('The maximum number of sites allowed to use a licence key. Leave empty or set to 0 for unlimited.', 'wp-updatr'),
		'lifespan' 	=> __('How long will a license key be valid for. Specify in days only.', 'wp-updatr'),
	);

	if( !empty( $descriptions[$key] ) ){
		return $descriptions[$key];
	}

	return;

}

/**
 * Function to add links to the plugin row meta
 *
 * @param array  $links Array of links to be shown in plugin meta.
 * @param string $file Filename of the plugin meta is being shown for.
 */
function pmproama_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'wp-updatr.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'http://wpupdatr.com/' ) . '" title="' . esc_attr( __( 'View Documentation', 'wp-updatr' ) ) . '">' . __( 'Docs', 'wp-updatr' ) . '</a>',
			'<a href="' . esc_url( 'http://wpupdatr.com/' ) . '" title="' . esc_attr( __( 'Support', 'wp-updatr' ) ) . '">' . __( 'Support', 'wp-updatr' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmproama_plugin_row_meta', 10, 2 );

/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function wp_updatr_add_plugin_action_link( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . get_admin_url( null, 'options-general.php?page=wp-updatr-settings' ) . '">' . __( 'Settings', 'wp-updatr' ) . '</a>',
		);
		if( !get_option( 'wp_updatr_api_key' ) ){
			$new_links[] = '<a href="" target="_BLANK">' . __( 'Get 30% Off Our Unlimited Plan', 'wp-updatr' ) . '</a>';
		}
	}
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wp_updatr_add_plugin_action_link' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */
function wp_updatr_admin_notice() {

	if ( !get_option( 'wp_updatr_api_key' ) ) { ?>
		<div class="updated is-dismissible">
			<p><?php 
				_e( 'Thank you for using WP Updatr. <a href="'.get_admin_url( null, 'options-general.php?page=wp-updatr-settings' ).'">Get Started</a> by linking your website and products to the WP Updatr service. <a href="" target="_BLANK">Get 30% Off Our Unlimited Plan</a>', 'wp-updatr' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'wp_updatr_admin_notice' );