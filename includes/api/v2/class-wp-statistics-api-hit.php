<?php

namespace WP_STATISTICS\Api\v2;

class Hit extends \WP_STATISTICS\RestApi {
	/**
	 * Hit Endpoint
	 *
	 * @var string
	 */
	public static $endpoint = 'hit';

	/**
	 * Hit constructor.
	 *
	 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 */
	public function __construct() {
		// Use Parent Construct
		parent::__construct();

		// Register routes
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_rest_server/
	 */
	public function register_routes() {

		// Record WP-Statistics when Cache is enable
		register_rest_route( self::$namespace, '/' . self::$endpoint, array(
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'hit_callback' )
			)
		) );

		// Check WP-Statistics Rest API Not disabled
		register_rest_route( self::$namespace, '/enable', array(
			array(
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'check_enable_callback' ),
				'args'     => array(
					'connect' => array(
						'required' => true
					),
				)
			)
		) );
	}

	/**
	 * Record WP-Statistics when Cache is enable
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function hit_callback( \WP_REST_Request $request ) {

	}

	/**
	 * Check WP-Statistics Rest API Not disabled
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function check_enable_callback( \WP_REST_Request $request ) {
		if ( $request->get_param( 'connect' ) == "wp-statistics" ) {
			return self::response( 'enable' );
		}

		return self::response( 'Missing connect parameter', 400 );
	}


}