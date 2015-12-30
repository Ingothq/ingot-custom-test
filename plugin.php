<?php
/**
Plugin Name: Ingot Custom Test
 */

/**
 * This function includes the actual test file.
 *
 * Keep this in a separate file so you can safely use functions that only work if:
 * Ingot is loaded
 * WordPress is 4.4+
 * PHP is 5.5+
 *
 * This prevents errors or loading uneeeded stuff
 *
 * PLEASE SEE INSTRUCTIONS AT TOP OF ingot-custom-test.php
 *
 */
add_action( 'init', 'my_ingot_custom_test_init' );
function my_ingot_custom_test_init(){
	if( did_action( 'ingot_loaded' ) ){
		include_once( dirname( __FILE__ ) . '/ingot-custom-test.php' );
	}

}
