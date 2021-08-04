<?php
/**
 * Plugin Name: Envato Purchase Code Widget
 * Plugin URI: https://www.thememasters.club
 * Description: Verify purchase codes right on your WordPress dashboard
 * Version: 1.0
 * Author: Egemenerd
 * Text Domain: epcw
 * Domain Path: /languages
 * License: MIT License
 * License URI: http://opensource.org/licenses/MIT
 */

defined( 'ABSPATH' ) || exit;

class EpcwWidget {
    /**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

    /**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * EpcwWidget Constructor.
	 */

    public function __construct() {
        add_action('init', array($this, 'init')); 
    }

    /**
	 * Init.
	 */

    public function init() {
        if (current_user_can('administrator')) {
            add_action('plugins_loaded', array($this, 'textdomain')); 
            add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
            add_action( 'admin_enqueue_scripts', array($this, 'widget_scripts') );
            add_action( 'wp_ajax_epcw_verify_purchase_code', array($this, 'verify_purchase_code') );
        }
    }

    /**
	 * Load textdomain.
	 */

    function textdomain() {
        load_plugin_textdomain( 'epcw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
    * Add a widget to the dashboard.
    */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'epcw_purchase_code',
            esc_html__( 'Envato Purchase Code', 'epcw' ),
            array($this, 'purchase_code_function')
        );	
    }

    /**
    * Add widget scripts.
    */
    public function widget_scripts($hook) {
        if( 'index.php' != $hook ) {
            return;
        }
        wp_enqueue_style('epcw-widget-style', plugins_url('css/widget.css', __FILE__));
        wp_enqueue_script( 'epcw-widget-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery'));
        wp_localize_script( 'epcw-widget-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

    /**
    * Create the function to output the contents of our Dashboard Widget.
    */

    public function purchase_code_function() {
        $personalToken = get_option('epcw_api_key');
        if (!empty($personalToken)) {
        ?>
        <form id="epcw-verify-form" method="post">
        <div class="epcw-verify-purchase">
            <input id="epcw_purchase_key" type="text" name="epcw_purchase_key" value="" />
            <input type="submit" value="<?php echo esc_attr__( 'Verify', 'epcw'); ?>" class="button button-primary button-large" /> 
        </div>
        <div id="epcw-verify-form-output">
        </div>    
        </form>    
    <?php } else { ?>
        <p style="font-weight:bold;color:red;"><?php echo esc_html__( 'Please enter your Envato API key from Settings -> EPCW.', 'epcw'); ?></p>
    <?php }
    }

    /**
    * Envato API request.
    */
    public function envato_request($purchase_key) {
        // api key
        $personalToken = get_option('epcw_api_key');
        $userAgent = "Purchase code verification";
        $error = '';
    
        // If you took $code from user input it's a good idea to trim it
        $code = trim($purchase_key);
    
        // Make sure the code is valid before sending it to Envato
        if (!preg_match("/^(\w{8})-((\w{4})-){3}(\w{12})$/", $code)) {
            $error = "Invalid code";
        }
    
        // Build the request
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => "https://api.envato.com/v3/market/author/sale?code={$code}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
    
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$personalToken}",
                "User-Agent: {$userAgent}"
            )
        ));
    
        // Send the request with warnings supressed
        $response = @curl_exec($ch);
    
        // Handle connection errors (such as an API outage)
        // You should show users an appropriate message asking to try again later
        if (curl_errno($ch) > 0) { 
            $error = "Error connecting to API: " . curl_error($ch);
        }
    
        // If we reach this point in the code, we have a proper response!
        // Let's get the response code to check if the purchase code was found
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        // HTTP 404 indicates that the purchase code doesn't exist
        if ($responseCode === 404) {
            $error = "The purchase code was invalid";
        }
    
        // Anything other than HTTP 200 indicates a request or API error
        // In this case, you should again ask the user to try again later
        if ($responseCode !== 200) {
            $error = "Failed to validate code due to an error: HTTP {$responseCode}";
        }
    
        // Parse the response into an object with warnings supressed
        $body = @json_decode($response);
    
        // Check for errors while decoding the response (PHP 5.3+)
        if ($body === false && json_last_error() !== JSON_ERROR_NONE) {
            $error = "Error parsing response";
        }
        
        $date = new DateTime($body->supported_until);
        $boughtdate = new DateTime($body->sold_at);
        $bresult = $boughtdate->format('d/m/Y');
        $sresult = $date->format('d/m/Y');
    
        $output = array(
            'id' => $body->item->id,
            'name' => $body->item->name,
            'buyer' => $body->buyer,
            'sold_at' => $bresult,
            'supported_until' => $sresult,
            'error' => $error
        );
        
        return $output;
    }

    public function verify_purchase_code() {
        $purchase_key = $_POST['epcw_purchase_key'];
        $purchase_data = $this->envato_request( $purchase_key );
        
        if( empty($purchase_data['error']) ) {	
            echo '<ul class="epcw_output">';
            echo '<li class="epcw_output_title">' . esc_attr__( 'Valid License Key Details', 'epcw') . '</li>'; 
            echo '<li><strong>' . esc_attr__( 'Item ID:', 'epcw') . '</strong> ' . $purchase_data['id'] . '</li>';
            echo '<li><strong>' . esc_attr__( 'Item Name:', 'epcw') . '</strong> ' . $purchase_data['name'] . '</li>';
            echo '<li><strong>' . esc_attr__( 'Buyer:', 'epcw') . '</strong> ' . $purchase_data['buyer']. '</li>';
            echo '<li><strong>' . esc_attr__( 'Sold at:', 'epcw') . '</strong> ' . $purchase_data['sold_at'] . '</li>';
            echo '<li><strong>' . esc_attr__( 'Supported Until:', 'epcw') . '</strong> ' . $purchase_data['supported_until'] . '</li>';
            echo '</ul>';
        } else {
            echo '<p class="epcw_output_error">' . $purchase_data['error'] . '</p>';
        }
        die();
    }

}

/**
 * Returns the main instance of EpcwWidget.
 */
function EpcwWidget() {  
	return EpcwWidget::instance();
}
// Global for backwards compatibility.
$GLOBALS['EpcwWidget'] = EpcwWidget();

/**
 * Include Settings
 */

require_once('settings.php');