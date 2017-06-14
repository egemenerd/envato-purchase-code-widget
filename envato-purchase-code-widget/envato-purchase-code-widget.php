<?php
/**
 * Plugin Name: Envato Purchase Code Widget
 * Plugin URI: http://www.egemenerd.com
 * Description: Verify purchase codes right on your WordPress dashboard
 * Version: 1.0
 * Author: Egemenerd
 * Author URI: http://www.egemenerd.com
 * Text Domain: epcw
 * Domain Path: /languages
 * License: MIT License
 * License URI: http://opensource.org/licenses/MIT
 */

// make plugin translation ready
add_action('plugins_loaded', 'epcw_textdomain');
function epcw_textdomain() 
{
    load_plugin_textdomain( 'epcw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function epcw_envato_request($purchase_key) {
	// Get username and api key
	$username = get_option('epcw_username');
	$api_key = get_option('epcw_apikey');
	
	// Open cURL channel
	$ch = curl_init();
	 
	// Set cURL options
	curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $purchase_key .".json");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //Set the user agent
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    
	// Decode returned JSON
	$output = json_decode(curl_exec($ch), true);
	 
	// Close Channel
	curl_close($ch);
	 
	// Return output
	return $output;
}
add_action( 'wp_ajax_epcw_verify_purchase_code', 'epcw_verify_purchase_code' );

function epcw_verify_purchase_code() {
    $purchase_key = $_POST['epcw_purchase_key'];
    $purchase_data = epcw_envato_request( $purchase_key );
    
    if( isset($purchase_data['verify-purchase']['buyer']) ) {	
        echo '<ul class="epcw_output">';
        echo '<li class="epcw_output_title">' . esc_attr__( 'Valid License Key Details', 'epcw') . '</li>'; 
        echo '<li><strong>' . esc_attr__( 'Item ID:', 'epcw') . '</strong> ' . $purchase_data['verify-purchase']['item_id'] . '</li>';
        echo '<li><strong>' . esc_attr__( 'Item Name:', 'epcw') . '</strong> ' . $purchase_data['verify-purchase']['item_name'] . '</li>';
        echo '<li><strong>' . esc_attr__( 'Buyer:', 'epcw') . '</strong> ' . $purchase_data['verify-purchase']['buyer']. '</li>';
        echo '<li><strong>' . esc_attr__( 'License:', 'epcw') . '</strong> ' . $purchase_data['verify-purchase']['licence'] . '</li>';
        echo '<li><strong>' . esc_attr__( 'Created At:', 'epcw') . '</strong> ' . $purchase_data['verify-purchase']['created_at'] . '</li>';
        echo '</ul>';
    } else {
        echo '<p class="epcw_output_error">' . esc_attr__( 'Invalid license key.', 'epcw') . '</p>';
    }
    die();
}

/* ---------------------------------------------------------
Include required files
----------------------------------------------------------- */

include_once('plugin_settings.php');
include_once('dashboard_widget.php');
?>