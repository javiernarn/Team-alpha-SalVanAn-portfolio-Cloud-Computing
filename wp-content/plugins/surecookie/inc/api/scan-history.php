<?php
/**
 * ScanHistory class
 *
 * Handles installed products related REST API endpoints for the SureCookie plugin.
 *
 * @package SureCookie\Inc\API
 */

namespace SureCookie\Inc\API;

use SureCookie\Inc\Functions\Get;
use SureCookie\Inc\Functions\SendJson;
use SureCookie\Inc\Traits\GetInstance;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ScanHistory
 *
 * Handles this related REST API endpoints.
 */
class ScanHistory extends Base {
	use GetInstance;

	/**
	 * Route Get Logs
	 */
	protected const GET_SCANNED_HISTORY = '/scan-history/get';

	/**
	 * Route the dashboard notice uses to remember it has been read.
	 */
	protected const DISMISS_SCAN_NOTICE = '/scan-history/dismiss-notice';

	/**
	 * Per-user marker holding the stamp of the scan whose notice has been read.
	 *
	 * One key, overwritten: storing a row per scan would grow usermeta for ever
	 * on a site that scans daily, and only the newest scan can have an unread
	 * notice anyway.
	 */
	protected const NOTICE_SEEN_OPTION_KEY = 'surecookie_scan_notice_seen';

	/**
	 * Register API routes.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->get_api_namespace(),
			self::GET_SCANNED_HISTORY,
			[
				'methods'             => WP_REST_Server::READABLE, // GET method.
				'callback'            => [ $this, 'get_scanned_history' ],
				'permission_callback' => [ $this, 'validate_permission' ],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			self::DISMISS_SCAN_NOTICE,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'dismiss_scan_notice' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => [
					'stamp' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Mark the dashboard's scan notice read for the current user.
	 *
	 * Per user, not per site: one administrator dismissing it should not hide it
	 * from the next. Per scan, not for ever, so the next scan reports itself.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Full data about the request.
	 * @since 1.8.0
	 * @return void
	 */
	public function dismiss_scan_notice( $request ): void {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			SendJson::error( [ 'message' => __( 'No user to remember this for.', 'surecookie' ) ] );
			return;
		}

		update_user_option(
			$user_id,
			self::NOTICE_SEEN_OPTION_KEY,
			(string) $request->get_param( 'stamp' )
		);

		SendJson::success( [ 'message' => __( 'Noted.', 'surecookie' ) ] );
	}

	/**
	 * The scan stamp this user has already read the notice for.
	 *
	 * @param int $user_id WordPress user ID.
	 * @since 1.8.0
	 * @return string Empty when they have read none.
	 */
	public static function get_notice_seen_stamp( int $user_id ): string {
		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return '';
		}

		return (string) get_user_option( self::NOTICE_SEEN_OPTION_KEY, $user_id );
	}

	/**
	 * Get scanned history
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Full data about the request.
	 */
	public function get_scanned_history( $request ): void {
		$scanned_history = Get::option( SURECOOKIE_SCANNED_DETAILS_OPTION, [], 'array' );
		SendJson::success(
			[
				'message' => __( 'Scanned history retrieved successfully.', 'surecookie' ),
				'history' => $scanned_history,
			]
		);
	}
}
