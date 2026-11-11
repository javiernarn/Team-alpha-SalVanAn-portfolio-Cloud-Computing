<?php
/**
 * Onboarding page for SureCookie plugin.
 *
 * @package    SureCookie
 * @subpackage SureCookie\Admin
 * @since      0.0.1
 */

namespace SureCookie\Admin;

use SureCookie\Inc\Functions\Get;
use SureCookie\Inc\Functions\Helper;
use SureCookie\Inc\Functions\Sanitize;
use SureCookie\Inc\Functions\Update;
use SureCookie\Inc\Traits\GetInstance;
use SureCookie\Inc\Utils\LocalizeData;

defined( 'ABSPATH' ) || exit;

/**
 * Onboarding page for SureCookie plugin.
 *
 * This file renders the onboarding page content.
 *
 * @since 0.0.1
 */
class Onboarding {
	use GetInstance;

	/**
	 * The wizard this build ships. Bump when the wizard is replaced, never when it
	 * is merely restyled - the value exists to compare one flow against another.
	 *
	 * @since 1.8.0
	 */
	public const FLOW_VERSION = 'v2';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup initializer.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'register_onboarding_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_onboarding_assets' ] );
	}

	/**
	 * Register the onboarding page.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function register_onboarding_page(): void {
		if ( ! current_user_can( Helper::capability() ) ) {
			return;
		}

		add_submenu_page(
			'surecookie-hidden',
			__( 'SureCookie Setup', 'surecookie' ),
			'',
			Helper::capability(),
			'surecookie-onboarding',
			[ $this, 'render_hidden_settings_page' ]
		);
	}

	/**
	 * Render the hidden settings page.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function render_hidden_settings_page(): void {
		if ( ! current_user_can( Helper::capability() ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'surecookie' ) );
		}

		// Stamped once, so analytics can separate a site that never opened the
		// wizard from one that opened it and stopped (@since 1.5.0).
		if ( (int) get_option( 'surecookie_onboarding_opened', 0 ) === 0 ) {
			Update::option( 'surecookie_onboarding_opened', time() );
		}

		// Which wizard this site actually saw, recorded at render rather than read
		// live: once a redesigned wizard ships, a site that onboarded on the old one
		// must keep reporting the old one. First write wins for the same reason.
		if ( ! is_string( get_option( SURECOOKIE_ONBOARDING_FLOW_OPTION, false ) ) ) {
			// A site that already finished the old wizard and is now re-entering the
			// new one is a third cohort, not a fresh install: counting it as `v2`
			// would inflate the new flow with users the old one had already
			// converted, and deflate the baseline it is measured against.
			$completed = (bool) get_option( SURECOOKIE_ONBOARDING_COMPLETED_OPTION, false );

			Update::option(
				SURECOOKIE_ONBOARDING_FLOW_OPTION,
				$completed ? self::FLOW_VERSION . '_rerun' : self::FLOW_VERSION
			);
		}

		?>
		<div id="surecookie-admin-root" class="surecookie-styles"></div>
		<?php
	}

	/**
	 * Enqueue onboarding assets.
	 *
	 * @since 0.0.1
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_onboarding_assets( $hook = '' ): void {
		$allowed_hooks = [
			'admin_page_surecookie-onboarding',
		];

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		$asset_file = SURECOOKIE_DIR . 'build/onboarding.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'surecookie-onboarding-script',
			SURECOOKIE_URL . 'build/onboarding.js',
			$asset['dependencies'] ?? [],
			$asset['version'] ?? SURECOOKIE_VERSION,
			true
		);
		wp_set_script_translations( 'surecookie-onboarding-script', 'surecookie', SURECOOKIE_DIR . 'languages' );

		wp_enqueue_style(
			'surecookie-onboarding-shared-styles',
			SURECOOKIE_URL . 'assets/css/' . Get::shared_style_css(),
			[],
			SURECOOKIE_VERSION
		);

		wp_enqueue_style(
			'surecookie-onboarding-style',
			SURECOOKIE_URL . 'build/' . Get::onboarding_style_css(),
			[ 'surecookie-onboarding-shared-styles' ],
			$asset['version'] ?? SURECOOKIE_VERSION
		);

		// The banner step previews the real banner, whose colours resolve against
		// these variables. Without them the preview renders in the fallback
		// palette and the swatch the user picked appears to do nothing.
		$palette_css = Sanitize::stylesheet( Get::palette_root_css() );
		if ( ! empty( $palette_css ) ) {
			wp_add_inline_style( 'surecookie-onboarding-style', wp_strip_all_tags( $palette_css ) );
		}

		// Localize onboarding data using utility.
		LocalizeData::localize_onboarding_script( 'surecookie-onboarding-script' );

		/**
		 * Let add-ons enqueue against the onboarding bundle, the way
		 * `surecookie_admin_enqueue_scripts_after` already lets them enqueue
		 * against the admin one. Pro uses it to register the custom palette the
		 * banner step offers.
		 *
		 * @since 1.8.0
		 *
		 * @param string $hook Current admin page hook suffix.
		 */
		do_action( 'surecookie_onboarding_enqueue_scripts_after', $hook );
	}
}
