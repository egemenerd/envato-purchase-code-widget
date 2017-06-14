<?php
/**
 * Add a widget to the dashboard.
*/
function epcw_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'epcw_purchase_code',
        esc_html__( 'Purchase Code', 'epcw' ),
        'epcw_purchase_code_function'
    );	
}
add_action( 'wp_dashboard_setup', 'epcw_add_dashboard_widgets' );

/*---------------------------------------------------
Add widget scripts
----------------------------------------------------*/

function epcw_widget_scripts($hook) {
    if( 'index.php' != $hook ) {
		return;
    }
    wp_enqueue_style('epcw-widget-style', plugins_url('css/widget.css', __FILE__));
    wp_enqueue_script( 'epcw-widget-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery'));
    wp_localize_script( 'epcw-widget-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'epcw_widget_scripts' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */

function epcw_purchase_code_function() {
?>
<form id="epcw-verify-form" method="post">
<div class="epcw-verify-purchase">
    <input id="epcw_purchase_key" type="text" name="epcw_purchase_key" value="" />
    <input type="submit" value="<?php echo esc_attr__( 'Verify', 'epcw'); ?>" class="button button-primary button-large" /> 
</div>
<div id="epcw-verify-form-output">
</div>    
</form>    
<?php
}
?>