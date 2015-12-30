<?php

/*
This is an example custom test for Ingot -- http://IngotHQ.com

This test will vary banners run on the home page of the site and will count users reaching a certain page -- such as a sign up page -- as a conversion.
Since conversions are tied to a hook, you can adapt this to register conversions on any hook.

Please read all @TODO in this file and in my_ingot_custom_test.php

 This plugin does the following things:
 1) Sets a filter to allow a new type of click test type to be used
 2) A function and class that will make the test group and return the group ID. It will save the group ID for future use.
 3) A function to output the test. We can also make it work with Ingot shortcode.
 4) A way to record conversions
 */

/**
 * We are adding a new type of click test. This is a sub_type. We must allow this sub_type
 */
add_filter( 'ingot_allowed_click_types' , function( $types ){
	$types[ 'my_custom_banner' ] = array(
		'name' => __( 'Custom Banner Test', 'ingot-custom-test' ),
		'description' =>  __( 'Change banner, see if user reaches a page', 'ingot-custom-test' )
	);

	return $types;

});

/**
 * If user is on front page and cookie isn't set choose a variant and set it
 *
 * @TODO -- If you will need to adjust your condition for starting test. In this case we start test if is_front_page()
 *
 * //NOTE: Ingot 1.1 will handle cookie tracking for you
 */
add_action( 'template_redirect', function(){
	if( is_front_page()  ) {
		if( ! isset( $_COOKIE['my_ingot_custom_test_variant'] ) || 0 == absint( $_COOKIE['my_ingot_custom_test_variant'] )  ){
			//get group ID
			$id = my_ingot_custom_test_group();

			//set up test and select a variant
			$test       = new \ingot\testing\bandit\content( $id );
			$variant_id = $test->choose();
			//a test instance is now registered


			//set variant ID in a cookie
			setcookie( 'my_ingot_custom_test_variant', $variant_id, time()+3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false);

			//put ID in a global -- only need this the first time since the cookie will not be set yet
			global $my_custom_test_variant_id;
			$my_custom_test_variant_id = $variant_id;
		}

	}
});


/**
 * Get HTML for our test. Use this function to output the test.
 *
 * Note that because this function has the same name as the test sub_type we registered, it can be called by the Ingot shortcode.
 *
 * @return string
 */
function my_custom_banner(){

	$variant = [];

	//if cookie isset get variant without registering as a new test instance
	if( isset( $_COOKIE['my_ingot_custom_test_variant'] ) && 0 < absint( $_COOKIE['my_ingot_custom_test_variant'] )  ){
		$variant_id = absint( $_COOKIE['my_ingot_custom_test_variant'] );
		$variant = \ingot\testing\crud\variant::read( $variant_id );

	}elseif( isset( $my_custom_test_variant_id ) && 0 < absint( $my_custom_test_variant_id ) ){
		//if is same session as cookie was set, we use the global variable to get the ID
		$variant_id = absint( $my_custom_test_variant_id );
		$variant = \ingot\testing\crud\variant::read( $variant_id );


	} else {
		//this should never be reached since, the cookie should have been set at template_redirect
		//just in case, let's grab a random banner so we have some HTML
		$id = my_ingot_custom_test_group();
		if ( is_numeric( $id ) ) {
			$group = \ingot\testing\crud\group::read( $id );
			if (  is_array( $group ) && isset( $group[ 'variants' ] ) && ! empty( $group[ 'variants' ] ) ) {
				$variant = array_rand( $group[ 'variants' ] );
				//set up test and select a variant
				$test       = new \ingot\testing\bandit\content( $id );
				$variant_id = $test->choose();
				//a test instance is now registered
				$variant = \ingot\testing\crud\variant::read( $variant_id );
			}

		}

	}

	//get chosen image URL and return it in an image tag
	if ( is_array( $variant )  ) {

		$content = $variant[ 'content' ];

	}else{
		//something is very wrong, let's show a picture of a cat in a flower pot
		$content = 'http://hellogiggles.com/wp-content/uploads/2014/07/22/you-popular-cute-cat-the-very-creative_113506.jpg';
	}

	return sprintf( '<img src="%s" />', esc_url( $content ) );

}

/**
 * This function should return ID of the group.
 *
 * The first time this runs it will create the group and save its ID in an option. After that, it will just return the ID from the option.
 *
 * It may also return a WP_Error with information on why it couldn't create the group
 *
 * @return int|WP_Error
 */
function my_ingot_custom_test_group(){
	include_once( dirname( __FILE__ ) . '/my_ingot_custom_test.php' );
	$group = new my_ingot_custom_test();
	$id = $group->get_id();
	return $id;
}


/**
 * Register a conversion whenever anyone reaches page 42 and we have a variant ID in the cookie to track.
 *
 * @TODO -- You should change your condition for registering a conversion. Could be a different page, a different hook. Or you could use the `/variants/<id>/conversion` endpoint to register via AJAX -- see http://ingothq.com/documentation/ingot-rest-api/
 */
add_action( 'template_redirect', function(){
	if( is_page( 42 ) && isset( $_COOKIE['my_ingot_custom_test_variant'] ) && 0 < absint( $_COOKIE['my_ingot_custom_test_variant'] )  ) {
		$variant_id = absint( $_COOKIE['my_ingot_custom_test_variant'] );
		ingot_register_conversion( $variant_id  );
	}

});
