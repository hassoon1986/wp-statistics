<?php

namespace WP_STATISTICS\Api\v2;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Option;

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
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'hit_callback' ),
				'permission_callback' => function () {
					return ( Option::get( 'use_cache_plugin' ) == 1 ? true : false );
				},
				'args'                => array(
					Hits::$rest_hits_key => array(
						'required'          => true,
						'validate_callback' => function ( $value, $request, $key ) {
							return ( Helper::json_to_array( $value ) === false ? false : true );
						}
					)
				)
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
	 * @throws \Exception
	 */
	public function hit_callback( \WP_REST_Request $request ) {
		$param = $request->get_param( Hits::$rest_hits_key );

		// Check List OF Require Parameter
		$list           = Helper::json_to_array( $param );
		$require_params = array( 'referred', 'ip', 'hash_ip', 'exclude', 'exclude_reason', 'ua', 'track_all', 'timestamp', 'current_page_type', 'current_page_id', 'page_uri', 'user_id' );
		foreach ( $require_params as $parameter ) {
			if ( ! array_key_exists( $parameter, $list ) ) {
				return self::response( 'Missing ' . $parameter . ' parameter.', 400 );
			}
		}

		// Run Hit Record
		Hits::record();
		return self::response( __( 'Visitor Hit was recorded successfully.', 'wp-statistics' ) );
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

		return self::response( 'Missing connect parameter.', 400 );
	}
}

new Hit;