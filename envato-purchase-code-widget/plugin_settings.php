<?php
/*---------------------------------------------------
Add Settings Page
----------------------------------------------------*/

function epcw_add_settings_page() {
    global $epcw_settings_page;
    $epcw_settings_page = add_plugins_page( esc_html__( 'Purchase Code', 'epcw'), esc_html__( 'Purchase Code', 'epcw'), 'manage_options', 'epcwsettings', 'epcw_plugin_settings_page');
}
add_action( 'admin_menu', 'epcw_add_settings_page' );

/*---------------------------------------------------
Add Settings Page Scripts
----------------------------------------------------*/

function epcw_admin_scripts($hook) {
    global $epcw_settings_page;
    if( $hook != $epcw_settings_page ) {
		return;
    }
    wp_enqueue_style('epcw-admin-style', plugins_url('css/admin.css', __FILE__));
}
add_action( 'admin_enqueue_scripts', 'epcw_admin_scripts' );
    
/* ---------------------------------------------------------
Create Settings
----------------------------------------------------------- */

$epcw_plugin_options = array (

array( "name" => esc_attr__( 'Envato Purchase Code Widget Settings', 'epcw'),    
"type" => "section"),
array( "type" => "open"),
    
array( "name" => esc_attr__( 'Username', 'epcw'),
"id" => "epcw_username",
"type" => "text",
"std" => ""), 
    
array( "name" => esc_attr__( 'API Key', 'epcw'),
"id" => "epcw_apikey",
"type" => "text",
"std" => ""),    
    
array( "type" => "close")
);
   
if ( ! function_exists( 'epcw_plugin_settings_page' ) ) {
function epcw_plugin_settings_page() {  
    global $epcw,$epcw_plugin_options;
    $i=0;
    $message='';
    if ( 'save' == @$_REQUEST['action'] ) {
        foreach ($epcw_plugin_options as $value) {
            update_option( @$value['id'], @$_REQUEST[ $value['id'] ] ); 
        }
        foreach ($epcw_plugin_options as $value) {
            if ( !empty( $_REQUEST[ @$value['id'] ] )) {  
                if( isset( $_REQUEST[ @$value['id'] ] ) ) { 
                    update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); 
                } else { 
                    delete_option( $value['id'] ); 
                } 
            }
        }
        $message='saved';
    }
    if ( $message=='saved' ) {
?>
<div id="epcw-message" class="updated notice notice-success is-dismissible">
    <p><strong><?php echo esc_attr__( 'Plugin settings saved', 'epcw'); ?></strong></p>
</div>
<?php } ?>

<div id="epcw-panel-wrapper">
    
<div class="epcw_options_wrap"> 
<div>
<form method="post">
 
<?php foreach ($epcw_plugin_options as $value) {
 
switch ( $value['type'] ) {
 
case "open": ?>
<?php break;
 
case "close": ?>
</div>
</div>

<?php break;
 
case 'text': ?>
<div class="epcw_option_input">
    <div class="epcw-option-caption">
        <label for="<?php echo esc_attr($value['id']); ?>"><?php echo esc_attr($value['name']); ?></label>
    </div>
    <div class="epcw-option">
        <input id="<?php echo esc_attr($value['id']); ?>" type="text" name="<?php echo esc_attr($value['id']); ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo stripslashes(esc_attr(get_option( $value['id']))); } else { echo esc_attr($value['std']); } ?>" />
    </div>
</div>
<?php break;
 
case "section":
$i++; ?>
<div class="epcw_input_section">
<div class="epcw_input_title">
 
<h1><?php echo esc_attr($value['name']); ?></h1>

</div>
<div class="epcw_all_options">
<?php break;
}
} ?>
<input name="save<?php echo esc_attr($i); ?>" type="submit" value="<?php echo esc_attr( 'Save Changes', 'epcw') ?>" class="button button-primary button-large" />     
<input type="hidden" name="action" value="save" />
</form>
</div>
</div>
</div>
<?php
}
}
?>