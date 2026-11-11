<?php
/**
 * Migration module init.
 *
 * Auto-discovered by the parent Modules\Init glob. Registers the migration REST
 * controller so the admin UI (onboarding step + Tools submenu) can list sources,
 * preview, and run an import.
 *
 * @package SureCookie\Inc\Modules\Migration
 * @since 1.8.0
 */

namespace SureCookie\Inc\Modules\Migration;

use SureCookie\Inc\Traits\GetInstance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Init class.
 *
 * @since 1.8.0
 */
class Init {
	use GetInstance;

	/**
	 * Constructor.
	 *
	 * @since 1.8.0
	 */
	private function __construct() {
		add_filter( 'surecookie_api_controllers', [ $this, 'register_api_controller' ], 20 );
	}

	/**
	 * Register the migration REST controller.
	 *
	 * @param array<int, string> $controllers Registered controller class names.
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	public function register_api_controller( $controllers ) {
		if ( is_array( $controllers ) ) {
			$controllers[] = Api::class;
		}

		return $controllers;
	}
}
