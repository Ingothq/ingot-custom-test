<?php

/**
 * Class my_ingot_custom_test
 *
 * Creates a test group the first time and saves its ID, after that it should just return that ID:)
 */
class my_ingot_custom_test {

	/**
	 * Test ID
	 *
	 * @var int
	 */
	private  $ID;

	/**
	 * Construct object and set ID
	 */
	public function __construct(){
		$this->set_id();
	}

	/**
	 * Get the group's ID
	 *
	 * @return int
	 */
	public function get_id(){
		return $this->ID;
	}

	/**
	 * Set ID property
	 *
	 * First we try and set using saved ID
	 *
	 * Else, we create group.
	 */
	private function set_id(){
		$_id = get_option( 'my_custom_ingot_group_id', 0 );
		if( 0 < $_id ) {
			$this->ID = $_id;
		}else{
			$this->create();
		}
	}

	/**
	 * Create group and variants
	 */
	private function create(){
		//going to bypass capability checks so we can write the group variants this time even if not logged in with proper capability
		add_filter( 'ingot_user_can', '__return_true' );

		$group_id = $this->create_group();

		//this should be the group ID
		//inspect this variable/trigger error to see why it's not working to see why
		if( is_numeric( $group_id ) ){

			//this should also be the group ID
			//inspect this variable/trigger error to see why it's not working to see why
			$variants_added = $this->add_variants( $group_id );

			//if all is well these two variables will be the same.
			if( $variants_added === $group_id ) {
				//save the ID
				update_option( 'my_custom_ingot_group_id', $group_id, false );
				$this->ID = $group_id;
			}

		}

	}

	/**
	 * Create the group
	 *
	 * @return int|WP_Error Should return group ID. May return WP_Error if there was an error
	 */
	protected  function create_group(){
		/**
		 * @TODO -- Set up the group arguments here. You should modify the name and link as needed
		 */
		$group_args  = [
			'name' => 'My Custom Banner',
			'type'     => 'click',
			'sub_type' => 'my_custom_banner',
			'meta'     => [
				'link' => get_permalink( 42 )
			],
		];

		//create the group
		$group_id = \ingot\testing\crud\group::create( $group_args );


		return $group_id;

	}

	/**
	 * Create the variants and save in group
	 *
	 * @param int $group_id The group ID
	 *
	 * @return int|WP_Error Should return group ID. May return WP_Error if there was an error creating variants or saving them in group.
	 */
	protected function add_variants( $group_id ) {
		$variant_args = [
			'type'     => 'click',
			'group_ID' => $group_id,
		];

		/**
		 * This array will be the HTML we output for each variant.
		 *
		 * As an example I'm using URLs of cute cate photos
		 *
		 * @TODO -- You should use something meaningful here.
		 */
		$variant_contents = [
			'https://placekitten.com/900/300',
			'https://placekitten.com/900/200',
			'https://placekitten.com/800/400'
		];

		$variant_ids = [];

		//create the variants
		foreach( $variant_contents as $content ){
			$variant_args[ 'content' ] = $content;
			$variant_id = \ingot\testing\crud\variant::create( $variant_args );
			if (  is_numeric( $variant_id) ) {
				$variant_ids[] = $variant_id;
			}else{
				return $variant_id;
			}

		}

		//save variant IDs in group
		$group = \ingot\testing\crud\group::read( $group_id );
		$group[ 'variants' ] = $variant_ids;
		$saved = \ingot\testing\crud\group::update( $group, $group_id );

		//this should be the group ID, could be an error.
		return $saved;

	}

}
