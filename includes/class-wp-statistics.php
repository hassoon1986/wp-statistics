<?php

# Exit if accessed directly
use WP_STATISTICS\Country;

defined( 'ABSPATH' ) || exit;

/**
 * Main bootstrap class for WP Statistics
 *
 * @package WP Statistics
 */
final class WP_Statistics {
	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WP-Statistics
	 */
	protected static $_instance = null;

	/**
	 * Main WP-Statistics Instance.
	 * Ensures only one instance of WP-Statistics is loaded or can be loaded.
	 *
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WP_Statistics constructor.
	 */
	public function __construct() {
		/**
		 * Check PHP Support
		 */
		if ( ! $this->require_php_version() ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
			return;
		}

		/**
		 * Plugin Loaded Action
		 */
		add_action( 'plugins_loaded', array( $this, 'plugin_setup' ) );

		/**
		 * Install And Upgrade plugin
		 */
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

		/**
		 * wp-statistics loaded
		 */
		do_action( 'wp_statistics_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 13.0
	 */
	public function __clone() {
		\WP_STATISTICS\Helper::doing_it_wrong( __CLASS__, esc_html__( 'Cloning is forbidden.', 'wp-statisitcs' ), '13.0' );
	}

	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $key
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->container[ $key ];
	}

	/**
	 * Constructors plugin Setup
	 *
	 * @throws Exception
	 */
	public function plugin_setup() {
		/**
		 * Load Text Domain
		 */
		add_action( 'init', array( $this, 'load_textdomain' ) );

		/**
		 * Include Require File
		 */
		$this->includes();

		/**
		 * instantiate Plugin
		 */
		$this->instantiate();
	}

	/**
	 * Includes plugin files
	 */
	public function includes() {

		// third-party Libraries
		require_once WP_STATISTICS_DIR . 'includes/vendor/autoload.php';

		// Utility classes.
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-meta-box.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-menus.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-admin-bar.php';

		// Hits Class
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-country.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-agent.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-pages.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-historical.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visit.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-hits.php';

		// Admin classes
		if ( is_admin() ) {

			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-templates.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-pages.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-ajax.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-dashboard.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-editor.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-export.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-uninstall.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-purge.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-notices.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

			// Admin Pages List
			require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-welcome.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-settings.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-optimization.php';
			require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-plugins.php';
		}

		// Meta Box List
		\WP_STATISTICS\Meta_Box::load();

		// Rest-Api
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-rest-api.php';
		require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-hit.php';
		require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-meta-box.php';

		// Front Class.
		if ( ! is_admin() ) {
			require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
		}

		// WP-Cli
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-cli.php';
		}

		// Template functions.
		include WP_STATISTICS_DIR . 'includes/template-functions.php';
	}

	/**
	 * Loads the load plugin text domain code.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-statistics', false, WP_STATISTICS_DIR . 'languages' );
	}

	/**
	 * Check PHP Version
	 */
	public function require_php_version() {
		if ( ! version_compare( phpversion(), WP_STATISTICS_REQUIRE_PHP_VERSION, ">=" ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	function php_version_notice() {

		$error = __( 'Your installed PHP Version is: ', 'wp-statistics' ) . PHP_VERSION . '. ';
		$error .= __( 'The <strong>WP-Statistics</strong> plugin requires PHP version <strong>', 'wp-statistics' ) . WP_STATISTICS_REQUIRE_PHP_VERSION . __( '</strong> or greater.', 'wp-statistics' );
		?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
		<?php
	}

	/**
	 * The main logging function
	 *
	 * @uses error_log
	 * @param string $type type of the error. e.g: debug, error, info
	 * @param string $msg
	 */
	public static function log( $type = '', $msg = '' ) {
		$msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg );
		error_log( $msg, 3, dirname( __FILE__ ) . '/log.txt' );
	}

	/**
	 * Create tables on plugin activation
	 *
	 * @global object $wpdb
	 */
	public static function install() {
		require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
		$installer = new \WP_STATISTICS\Install();
		$installer->install();
	}

	/**
	 * Manage task on plugin deactivation
	 *
	 * @return void
	 */
	public static function uninstall() {
		delete_option( 'wp_statistics_removal' );
	}

	/**
	 * Instantiate the classes
	 *
	 * @return void
	 * @throws Exception
	 */
	public function instantiate() {

		# Sanitize WP-Statistics Data
		$this->container['hits'] = new \WP_STATISTICS\Hits();

		# Get Country Codes
		$this->container['country_codes'] = Country::getList();

		# Get User Detail
		$this->container['user_id'] = \WP_STATISTICS\User::get_user_id();

		# Set Options
		$this->container['option'] = \WP_STATISTICS\Option::getOptions();

		# User IP
		$this->container['ip'] = \WP_STATISTICS\IP::getIP();

		# User Agent
		$this->container['agent'] = \WP_STATISTICS\UserAgent::getUserAgent();

		# User Online
		$this->container['users_online'] = new \WP_STATISTICS\UserOnline();

		# Visitor
		$this->container['visitor'] = new \WP_STATISTICS\Visitor();

		# Referer
		$this->container['referred'] = \WP_STATISTICS\Referred::get();

		# Load WordPress ShortCode
		new \WP_STATISTICS\Shortcode;

		# Load WordPress Cron
		new \WP_STATISTICS\Schedule;

		# Admin Bar
		new \WP_STATISTICS\AdminBar;

		# Run in Admin
		if ( is_admin() ) {

			// TODO Seperate All Classes
			new \WP_STATISTICS\Admin;

			# Admin Menu
			new \WP_STATISTICS\Menus;

			# Admin Asset
			new \WP_STATISTICS\Admin_Assets;

			# Admin Export Class
			new \WP_STATISTICS\Export;

			# Admin Ajax
			new \WP_STATISTICS\Ajax;

			# Admin Meta Box
			new \WP_STATISTICS\Meta_Box;

			# Admin Dashboard Widget
			new \WP_STATISTICS\Admin_Dashboard;

			# MultiSite Admin
			if ( is_multisite() ) {
				$this->container['admin_network'] = new \WP_STATISTICS\Network;
			}

			# Welcome Screen
			new \WP_STATISTICS\Welcome;

			# Setting Pages
			new \WP_STATISTICS\settings_page;

			# optimization Page
			new \WP_STATISTICS\optimization_page;

			# Admin Notice
			new \WP_STATISTICS\Admin_Notices;
		}

		# Rest API
		new \WP_STATISTICS\RestApi;
		new \WP_STATISTICS\Api\v2\Hit;
		new \WP_STATISTICS\Api\v2\Meta_Box;


		# Run in Frontend
		if ( ! is_admin() ) {
			new WP_STATISTICS\Frontend;
		}
	}

}
