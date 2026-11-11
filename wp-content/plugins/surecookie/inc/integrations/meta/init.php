<?php
/**
 * Meta Pixel / Conversions API hold-release integration.
 *
 * @package SureCookie\Inc\Integrations\Meta
 * @since 1.8.0
 */

namespace SureCookie\Inc\Integrations\Meta;

use SureCookie\Inc\Functions\Settings;
use SureCookie\Inc\Traits\GetInstance;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Holds Meta's signals on every server render and leaves the release to the browser.
 *
 * Meta for WooCommerce and Meta Pixel for WordPress write `_fbp` and queue
 * Conversions API events while building the page, which a full-page cache then
 * replays to everyone, so one visitor's Meta ID was handed to all of them before
 * any consent (#1460). Neither plugin reads the WP Consent API, so the hold has
 * to go through Meta's own `facebook_signals_held` filter, and it has to be
 * unconditional: a per-visitor answer decides what gets stored in that cache.
 * `updateMetaSignals()` in `src/utils/consentManager.js` releases per page.
 *
 * It needs no wp-admin or cron carve-out: both plugins already refuse to
 * suppress a Conversions API send on those requests whatever this answers, so a
 * merchant's admin-created order still reports.
 */
class Init {
	use GetInstance;

	/**
	 * Meta's own hold/release admin-ajax actions, for both plugins.
	 *
	 * These responses are per visitor and never cached, and they are the only
	 * way a release reaches Meta's PHP, so holding them would strand the pixel.
	 *
	 * @var string[]
	 */
	private const SIGNAL_ACTIONS = [
		'wc_facebook_update_signals_state',
		'facebook_release_signals',
		'fbpix_set_pixel_signals',
		'fbpix_release_signals',
	];

	/**
	 * Meta's signal-state holders, one per plugin. Both expose a public static
	 * `hold()` that sets the request flag and writes the shared state cookie.
	 *
	 * @var string[]
	 */
	private const SIGNAL_STATE_CLASSES = [
		'\\WooCommerce\\Facebook\\Events\\FacebookSignalsState',
		'\\FacebookPixelPlugin\\Core\\FacebookSignalState',
	];

	/**
	 * Register the hold.
	 *
	 * Constructed from `SureCookie_Loader::load_routes()` on `plugins_loaded`,
	 * not from the `init:999` integrations hub: Meta for WooCommerce writes
	 * `_fbp` from its integration constructor at `init:10`.
	 */
	private function __construct() {
		add_filter( 'facebook_signals_held', [ $this, 'hold_signals' ] );
		add_filter( 'surecookie_frontend_localize_data', [ $this, 'add_signal_switch' ] );
		add_action( 'template_redirect', [ $this, 'align_signal_cookie' ] );
	}

	/**
	 * Tell the browser whether this site holds Meta at all.
	 *
	 * The browser is the only releaser, so it has to read the same raw,
	 * cache-invariant answer the hold does - never the per-visitor `blocking`
	 * value, which Pro's geo layer rewrites while the server still holds.
	 *
	 * @param mixed $data Frontend localize payload.
	 * @return mixed The payload, carrying the switch when it is an array.
	 * @since 1.8.0
	 */
	public function add_signal_switch( $data ) {
		if ( is_array( $data ) ) {
			$data['meta_signals_enabled'] = self::blocking_is_on();
		}

		return $data;
	}

	/**
	 * Hold Meta's signals for every render a page cache could store.
	 *
	 * @param mixed $held Meta's own held state.
	 * @return mixed True to hold, otherwise the value untouched.
	 */
	public function hold_signals( $held ) {
		if ( self::is_signal_exchange() || ! self::blocking_is_on() ) {
			return $held;
		}

		return true;
	}

	/**
	 * Make Meta's own state cookie agree with the hold we just forced.
	 *
	 * `facebook_signals_held` only answers `is_held()`. The cookie is written
	 * by Meta's own `hold()`, which it never calls on our behalf, so a visitor
	 * released on an earlier page carried `active` into every later render the
	 * server held. Both runtimes resolve from that cookie in preference to the
	 * state PHP rendered, and Meta for WooCommerce DISCARDS the CAPI payload in
	 * `queueEvent()` when it reads active, so the server send we had already
	 * suppressed was left with no carrier and the conversion lost its CAPI half.
	 *
	 * Their own public API rather than a cookie of our own, so the name, path,
	 * domain and expiry stay theirs. Cache-invariant: the value is `held` for
	 * every visitor whenever this site holds at all, which is what the
	 * unconditional hold already promises.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function align_signal_cookie(): void {
		if ( is_admin() || ! self::blocking_is_on() ) {
			return;
		}

		foreach ( self::SIGNAL_STATE_CLASSES as $state ) {
			if ( method_exists( $state, 'hold' ) ) {
				$state::hold();
			}
		}
	}

	/**
	 * Whether this request is Meta's own hold/release exchange.
	 *
	 * @return bool
	 */
	private static function is_signal_exchange(): bool {
		if ( ! wp_doing_ajax() ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Meta verifies its own request; this only picks the branch.
		$action = isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

		return in_array( $action, self::SIGNAL_ACTIONS, true );
	}

	/**
	 * Both halves of `Utils::is_blocking_enabled()`, read raw.
	 *
	 * That resolver memoizes its filtered answer, and this filter is consulted
	 * at `init:10`, before Pro's per-visitor geo rule registers at `init:999` -
	 * priming it here would freeze the blocker's own verdict for the request.
	 * A per-visitor answer has no business in a cacheable render anyway.
	 *
	 * @return bool
	 */
	private static function blocking_is_on(): bool {
		return (bool) Settings::get( 'banner_enabled' ) && (bool) Settings::get( 'blocking_enabled' );
	}
}
