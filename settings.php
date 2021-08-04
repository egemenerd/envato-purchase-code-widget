<?php
defined( 'ABSPATH' ) || exit;

class EpcwSettings {
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
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_menu', array($this, 'register_options_page'));
        }
    }

    public function register_settings() {
        add_option( 'epcw_api_key', '');
        register_setting( 'epcw_options_group', 'epcw_api_key', 'epcw_callback' );
    }

    public function register_options_page() {
        add_options_page(esc_html__( 'Envato Purchase Code Widget', 'epcw' ), esc_html__( 'EPCW', 'epcw' ), 'manage_options', 'epcw', array($this, 'options_page'));
    }

    public function options_page() {
    ?>
    <div style="background: #fff;padding: 30px;max-width: 480px;margin-top: 20px;">
        <h1><?php esc_html_e( 'Envato API Key', 'epcw' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'epcw_options_group' ); ?>
            <div style="margin-top:30px;">
            <input type="text" style="width:100%;padding: 5px;" id="epcw_api_key" name="epcw_api_key" value="<?php echo esc_html(get_option('epcw_api_key')); ?>" />
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    }

}

/**
 * Returns the main instance of EpcwSettings.
 */
function EpcwSettings() {  
	return EpcwSettings::instance();
}
// Global for backwards compatibility.
$GLOBALS['EpcwSettings'] = EpcwSettings();