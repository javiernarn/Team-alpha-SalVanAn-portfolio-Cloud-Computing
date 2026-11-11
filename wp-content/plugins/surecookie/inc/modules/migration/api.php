<?php
/**
 * Migration REST controller.
 *
 * Exposes the importer registry to the admin UI: list available sources, preview
 * what an import would do, and run it. Registered on the shared
 * `surecookie_api_controllers` filter and gated by the standard capability +
 * nonce (see Base::validate_permission()).
 *
 * @package SureCookie\Inc\Modules\Migration
 * @since 1.8.0
 */

namespace SureCookie\Inc\Modules\Migration;

use SureCookie\Inc\API\Base;
use SureCookie\Inc\Functions\SendJson;
use SureCookie\Inc\Modules\Migration\Importers\WPConsent;
use SureCookie\Inc\Traits\GetInstance;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Api
 *
 * @since 1.8.0
 */
class Api extends Base {
	use GetInstance;

	/**
	 * Importer slug owning a plugin directory, or '' when none does or its settings were already
	 * imported. Lets the conflicting-plugins notice offer an import before deactivation, then only deactivation.
	 *
	 * @param string $plugin_dir Plugin directory slug.
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_slug_for_plugin_dir( string $plugin_dir ): string {
		if ( $plugin_dir === '' ) {
			return '';
		}

		foreach ( self::get_instance()->get_importers() as $importer ) {
			if ( $importer->get_plugin_dir() === $plugin_dir ) {
				return $importer->get_status()['migrated'] ? '' : $importer->get_slug();
			}
		}

		return '';
	}

	/**
	 * Register REST routes.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->get_api_namespace(),
			'/migration/sources',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_sources' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			'/migration/(?P<slug>[a-z0-9-]+)/preview',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'preview' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			'/migration/(?P<slug>[a-z0-9-]+)/run',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'run' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			'/migration/(?P<slug>[a-z0-9-]+)/reset',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'reset' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);
	}

	/**
	 * GET /migration/sources -- list importers with detection status.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request.
	 * @since 1.8.0
	 * @return void
	 */
	public function get_sources( $request ): void {
		unset( $request );

		$sources = [];
		foreach ( $this->get_importers() as $importer ) {
			$sources[] = array_merge(
				[
					'slug'        => $importer->get_slug(),
					'label'       => $importer->get_label(),
					'active'      => $importer->is_plugin_active(),
					'installed'   => $importer->is_plugin_installed(),
					'has_data'    => $importer->has_data(),
					// Lets the UI hand this straight to the existing plugin
					// deactivate route instead of doing its own lookup.
					'plugin_file' => $importer->get_plugin_file(),
				],
				$importer->get_status()
			);
		}

		SendJson::success( [ 'sources' => $sources ] );
	}

	/**
	 * GET /migration/{slug}/preview -- what an import would apply / skip.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request.
	 * @since 1.8.0
	 * @return void
	 */
	public function preview( $request ): void {
		$importer = $this->resolve( $request );
		if ( $importer === null ) {
			SendJson::error( [ 'message' => __( 'Unknown migration source.', 'surecookie' ) ] );
			return;
		}

		SendJson::success(
			array_merge(
				[ 'slug' => $importer->get_slug() ],
				$importer->preview()
			)
		);
	}

	/**
	 * POST /migration/{slug}/run -- import the source settings.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request.
	 * @since 1.8.0
	 * @return void
	 */
	public function run( $request ): void {
		$importer = $this->resolve( $request );
		if ( $importer === null ) {
			SendJson::error( [ 'message' => __( 'Unknown migration source.', 'surecookie' ) ] );
			return;
		}

		if ( ! $importer->is_plugin_installed() ) {
			SendJson::error( [ 'message' => __( 'The source plugin is not installed.', 'surecookie' ) ] );
			return;
		}

		$result = $importer->import();

		// The source plugin is left active by default; deactivation is opt-in via
		// an explicit `deactivate=true` param.
		$deactivate = filter_var( $request->get_param( 'deactivate' ), FILTER_VALIDATE_BOOLEAN );

		$result['deactivated'] = $deactivate && $importer->is_plugin_active()
			? $importer->deactivate_plugin()
			: false;

		SendJson::success( $result );
	}

	/**
	 * POST /migration/{slug}/reset -- reset the source's managed settings to
	 * SureCookie defaults.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request.
	 * @since 1.8.0
	 * @return void
	 */
	public function reset( $request ): void {
		$importer = $this->resolve( $request );
		if ( $importer === null ) {
			SendJson::error( [ 'message' => __( 'Unknown migration source.', 'surecookie' ) ] );
			return;
		}

		SendJson::success( $importer->reset() );
	}

	/**
	 * Registry of available importers, keyed by slug. Add future sources
	 * (CookieYes, Complianz, ...) here -- the routes/UI need no other change.
	 *
	 * @since 1.8.0
	 * @return array<string, Importer>
	 */
	private function get_importers(): array {
		return [
			'wpconsent' => new WPConsent(),
		];
	}

	/**
	 * Resolve the importer for the request's `slug` param.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Request.
	 * @since 1.8.0
	 * @return Importer|null
	 */
	private function resolve( $request ): ?Importer {
		$slug      = (string) $request->get_param( 'slug' );
		$importers = $this->get_importers();

		return $importers[ $slug ] ?? null;
	}
}
