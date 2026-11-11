<?php
/**
 * WPConsent importer.
 *
 * Maps the WPConsent "Cookies Banner & Privacy Suite" configuration (stored in
 * the single `wpconsent_settings` option) onto SureCookie's settings keys. No new
 * tables or options are created: everything lands in `surecookie_settings` via the
 * shared sanitized write path. See docs/migration-from-wpconsent.md for the full
 * key map and transforms.
 *
 * @package SureCookie\Inc\Modules\Migration\Importers
 * @since 1.8.0
 */

namespace SureCookie\Inc\Modules\Migration\Importers;

use SureCookie\Inc\Database\ConsentLog;
use SureCookie\Inc\Functions\Cookie_Identity;
use SureCookie\Inc\Functions\Get;
use SureCookie\Inc\Functions\Sanitize;
use SureCookie\Inc\Modules\Migration\Base_Importer;
use SureCookie\Inc\Modules\Services\Installed_Services;
use SureCookie\Inc\Modules\Services\Services_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WPConsent
 *
 * @since 1.8.0
 */
class WPConsent extends Base_Importer {
	/**
	 * Option WPConsent stores all of its settings under.
	 *
	 * @since 1.8.0
	 */
	private const SOURCE_OPTION = 'wpconsent_settings';

	/**
	 * WPConsent cookie-category taxonomy (categories are top-level terms; services
	 * are child terms; cookies are CPT posts attached to either).
	 *
	 * @since 1.8.0
	 */
	private const SOURCE_TAXONOMY = 'wpconsent_category';

	/**
	 * WPConsent cookie post type.
	 *
	 * @since 1.8.0
	 */
	private const SOURCE_CPT = 'wpconsent_cookie';

	/**
	 * WPConsent default category slug -> SureCookie category id. WPConsent ships
	 * essential/statistics/marketing; "statistics" is SureCookie's "analytics".
	 * Any other (custom) slug becomes a new `wpc_<slug>` SureCookie category.
	 *
	 * @since 1.8.0
	 */
	private const CATEGORY_SLUG_MAP = [
		'essential'  => 'essential',
		'statistics' => 'analytics',
		'marketing'  => 'marketing',
	];

	/**
	 * WPConsent floating-corner -> SureCookie box position. SureCookie's box has
	 * no top corners, so a top corner keeps its side and drops to the bottom.
	 *
	 * @since 1.8.0
	 */
	private const FLOATING_POSITION_MAP = [
		'left-top'     => 'bottom-left',
		'right-top'    => 'bottom-right',
		'left-bottom'  => 'bottom-left',
		'right-bottom' => 'bottom-right',
	];

	/**
	 * SureCookie default category ids. Their required-flag stays governed by
	 * SureCookie's own rule (only essential is mandatory) rather than the source.
	 *
	 * @since 1.8.0
	 */
	private const DEFAULT_CATEGORY_IDS = [ 'essential', 'functional', 'analytics', 'marketing', 'uncategorized' ];

	/**
	 * WPConsent Pro consent-log table (without the site prefix).
	 *
	 * @since 1.8.0
	 */
	private const SOURCE_LOGS_TABLE = 'wpconsent_consent_logs';

	/**
	 * Consent-log rows read + written per batch.
	 *
	 * @since 1.8.0
	 */
	private const LOGS_BATCH_SIZE = 500;

	/**
	 * Cookie posts read per batch.
	 *
	 * @since 1.8.0
	 */
	private const POSTS_BATCH_SIZE = 200;

	/**
	 * Option holding the highest WPConsent consent_id already imported, so a
	 * re-run resumes from there instead of rescanning the whole table.
	 *
	 * @since 1.8.0
	 */
	private const LOGS_MARKER_OPTION = 'surecookie_migrated_wpconsent_logs_max_id';

	/**
	 * Whether the last consent-log run stopped before the end of the source.
	 *
	 * @since 1.8.0
	 * @var bool
	 */
	private $consent_logs_incomplete = false;

	/**
	 * WPConsent consent_data keys that are not consent categories, so they are
	 * skipped when mapping to SureCookie preferences.
	 *
	 * @since 1.8.0
	 */
	private const NON_CATEGORY_KEYS = [ 'respect_gpc' ];

	/**
	 * {@inheritDoc}
	 */
	public function get_slug(): string {
		return 'wpconsent';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label(): string {
		return 'WPConsent';
	}

	/**
	 * {@inheritDoc}
	 *
	 * WPConsent exposes a `wpconsent()` accessor; the constant is a fallback.
	 */
	public function is_plugin_active(): bool {
		return function_exists( 'wpconsent' ) || defined( 'WPCONSENT_VERSION' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_plugin_dir(): string {
		return 'wpconsent-cookies-banner-privacy-suite';
	}

	/**
	 * {@inheritDoc}
	 */
	public function has_data(): bool {
		return get_option( self::SOURCE_OPTION, false ) !== false;
	}

	/**
	 * Settings WPConsent has but SureCookie can only honor with Pro / cannot map
	 * 1:1, surfaced to the UI as "review / may not be migrated completely".
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	public function get_review_keys(): array {
		$source = get_option( self::SOURCE_OPTION, [] );
		$source = is_array( $source ) ? $source : [];

		$keys = [];

		// Only when the source actually had it off, so the summary says this on
		// the one import where the banner would otherwise have been turned off.
		if ( array_key_exists( 'enable_consent_banner', $source ) && empty( $source['enable_consent_banner'] ) ) {
			$keys[] = 'banner_left_enabled';
		}

		return array_merge(
			$keys,
			[
				'banner_layout',
				'region_targeting',
				'do_not_sell',
				'gpc',
				'shared_consent',
				// WPConsent 1.1.6 GCM v2 toggles. SureCookie's GCM has no equivalent,
				// so they are reported rather than silently dropped.
				'gcm_url_passthrough',
				'gcm_ads_data_redaction',
			]
		);
	}

	/**
	 * SureCookie setting keys this importer can write/reset.
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	public function get_managed_keys(): array {
		return [
			'banner_enabled',
			'message_description',
			'preferences_modal_heading',
			'preferences_modal_description',
			'banner_logo',
			'notice_type',
			'notice_position',
			'accept_all_btn_text',
			'decline_btn_text',
			'preferences_btn_text',
			'accept_all_enabled',
			'button_order',
			'consent_model',
			'consent_duration_days',
			'blocking_enabled',
			'cookie_policy_page_id',
			'delete_data_on_uninstall',
			'gcm_enabled',
			'remove_branding',
			'floating_widget_enabled',
			'custom_colors',
			'cookie_categories',
			'custom_cookies',
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * Builds the full patch: the flat settings map plus the merged cookie
	 * categories and cookies. Categories/cookies are folded into the same patch
	 * because both are registered `array` settings keys, so they flow through the
	 * exact sanitized write/preview/partition path the rest of the import uses.
	 * Each is added only when it actually changes, so an idempotent re-run that
	 * finds nothing new leaves those keys out of the patch entirely.
	 */
	public function build_settings_patch(): array {
		$source = get_option( self::SOURCE_OPTION, [] );
		$patch  = $this->map_settings( is_array( $source ) ? $source : [] );

		$current = get_option( SURECOOKIE_SETTINGS_OPTION, [] );
		$current = is_array( $current ) ? $current : [];

		$current_categories = isset( $current['cookie_categories'] ) && is_array( $current['cookie_categories'] )
			? $current['cookie_categories']
			: Get::default_cookie_categories();
		$categories         = $this->map_categories( $this->read_source_categories(), $current_categories );
		if ( $categories !== $current_categories ) {
			$patch['cookie_categories'] = $categories;
		}

		$current_cookies = isset( $current['custom_cookies'] ) && is_array( $current['custom_cookies'] )
			? $current['custom_cookies']
			: [];
		$cookies         = $this->map_cookies( $this->read_source_cookies(), $current_cookies );
		if ( $cookies !== $current_cookies ) {
			$patch['custom_cookies'] = $cookies;
		}

		return $patch;
	}

	/**
	 * Pure mapping: WPConsent settings array -> SureCookie settings patch.
	 *
	 * Only keys that have a meaningful source value are included, so SureCookie
	 * defaults are never overwritten with blanks. No WordPress calls here, so this
	 * is fully unit-testable.
	 *
	 * @param array<string, mixed> $s WPConsent settings.
	 * @since 1.8.0
	 * @return array<string, mixed> SureCookie-keyed patch.
	 */
	public function map_settings( array $s ): array {
		$patch = [];

		// Banner display and content. An import may switch the banner ON but never
		// OFF: WPConsent ships enable_consent_banner => 0, so a faithful copy would
		// silently stop a live SureCookie site collecting consent. Reported under
		// `banner_left_enabled` instead of being applied.
		if ( ! empty( $s['enable_consent_banner'] ) ) {
			$patch['banner_enabled'] = true;
		}
		$this->set_string( $patch, $s, 'banner_message', 'message_description' );
		$this->set_string( $patch, $s, 'preferences_panel_title', 'preferences_modal_heading' );
		$this->set_string( $patch, $s, 'preferences_panel_description', 'preferences_modal_description' );
		$this->set_string( $patch, $s, 'banner_logo', 'banner_logo' );

		$this->map_position( $patch, $s );

		// Buttons. WPConsent "accept" is the accept-all action.
		$this->set_string( $patch, $s, 'accept_button_text', 'accept_all_btn_text' );
		$this->set_string( $patch, $s, 'cancel_button_text', 'decline_btn_text' );
		$this->set_string( $patch, $s, 'preferences_button_text', 'preferences_btn_text' );
		$this->set_bool( $patch, $s, 'accept_button_enabled', 'accept_all_enabled' );

		if ( isset( $s['button_order'] ) && is_array( $s['button_order'] ) ) {
			$patch['button_order'] = $this->map_button_order( $s['button_order'] );
		}

		// Consent behavior.
		if ( array_key_exists( 'default_allow', $s ) ) {
			$patch['consent_model'] = $s['default_allow'] ? 'opt-out' : 'opt-in';
		}

		// A zero or unparseable duration would mean consent never persists, so a
		// corrupt source value leaves SureCookie's own default standing.
		if ( isset( $s['consent_duration'] ) && (int) $s['consent_duration'] > 0 ) {
			$patch['consent_duration_days'] = (int) $s['consent_duration'];
		}

		// SureCookie has one umbrella blocking flag; WPConsent splits script vs
		// content blocking. Enable ours if either source flag is on.
		if ( array_key_exists( 'enable_script_blocking', $s ) || array_key_exists( 'enable_content_blocking', $s ) ) {
			$patch['blocking_enabled'] = (bool) ( ! empty( $s['enable_script_blocking'] ) || ! empty( $s['enable_content_blocking'] ) );
		}

		// A stored 0 is "no page picked", not a selection, so copying it would
		// clear a cookie policy page SureCookie already has.
		if ( ! empty( $s['cookie_policy_page'] ) ) {
			$patch['cookie_policy_page_id'] = (int) $s['cookie_policy_page'];
		}
		$this->set_bool( $patch, $s, 'uninstall_data', 'delete_data_on_uninstall' );

		// Google Consent Mode (free GCM module setting).
		$this->set_bool( $patch, $s, 'google_consent_mode', 'gcm_enabled' );

		// Pro-only targets. Included in the patch so the UI can report them; the
		// apply step writes them only when the key is actually registered (Pro
		// active), otherwise a switched-on one is reported under `needs_pro`.
		$this->set_bool( $patch, $s, 'hide_powered_by', 'remove_branding' );
		$this->set_bool( $patch, $s, 'enable_consent_floating', 'floating_widget_enabled' );

		// Pro's custom palette (needs_pro on Free). Text and background must both
		// carry over, or neither sits on a color it was chosen for. Primary is also
		// SureCookie's link and focus color on that background, so an accept color
		// too faint to read there takes the text color, as WPConsent's links did.
		$foreground = Sanitize::css_color( $s['banner_text_color'] ?? '' );
		$background = Sanitize::css_color( $s['banner_background_color'] ?? '' );
		if ( $foreground !== '' && $background !== '' ) {
			$primary = Sanitize::css_color( $s['banner_accept_bg'] ?? '' );
			$ratio   = Get::contrast_ratio( $primary, $background );
			if ( $ratio === null || $ratio < 4.5 ) {
				$primary = $foreground;
			}

			$patch['custom_colors'] = [
				'mode'   => 'custom',
				'colors' => [
					'primary'    => $primary,
					'secondary'  => Sanitize::css_color( $s['banner_cancel_bg'] ?? '' ),
					'foreground' => $foreground,
					'background' => $background,
				],
			];
		}

		return $patch;
	}

	/**
	 * Merge WPConsent categories into the current SureCookie categories.
	 *
	 * Pure (no WordPress calls): standard slugs fold onto SureCookie's defaults
	 * (carrying the user's name/description), custom slugs become new `wpc_<slug>`
	 * categories. Default categories keep SureCookie's required rule; custom ones
	 * carry the source flag. Re-running maps to the same ids, so it never
	 * duplicates and only ever updates in place.
	 *
	 * @param array<int, array{slug?: string, name?: string, description?: string, required?: bool}> $source  Normalized WPConsent categories.
	 * @param array<string, mixed>                                                                   $current Current `cookie_categories` (assoc by id).
	 * @since 1.8.0
	 * @return array<string, mixed> Merged categories.
	 */
	public function map_categories( array $source, array $current ): array {
		$merged = $current;

		foreach ( $source as $cat ) {
			$slug = isset( $cat['slug'] ) ? (string) $cat['slug'] : '';
			$name = isset( $cat['name'] ) ? trim( (string) $cat['name'] ) : '';

			if ( $slug === '' && $name === '' ) {
				continue;
			}

			$id         = $this->resolve_category_id( $slug !== '' ? $slug : $name );
			$is_default = in_array( $id, self::DEFAULT_CATEGORY_IDS, true );
			$entry      = isset( $merged[ $id ] ) && is_array( $merged[ $id ] ) ? $merged[ $id ] : [];

			$entry['id']   = $id;
			$entry['name'] = $name !== '' ? $name : ( $entry['name'] ?? ucfirst( $id ) );

			if ( isset( $cat['description'] ) && trim( (string) $cat['description'] ) !== '' ) {
				$entry['description'] = (string) $cat['description'];
			} elseif ( ! isset( $entry['description'] ) ) {
				$entry['description'] = '';
			}

			// Defaults: only essential is required, and that is fixed. Custom
			// categories carry whatever the source marked.
			if ( $is_default ) {
				$entry['required'] = isset( $entry['required'] ) ? (bool) $entry['required'] : ( $id === 'essential' );
			} else {
				$entry['required'] = ! empty( $cat['required'] );
			}

			$merged[ $id ] = $entry;
		}

		return $merged;
	}

	/**
	 * Merge WPConsent cookies into the current SureCookie custom cookies.
	 *
	 * Pure (no WordPress calls). Each cookie is keyed by a stable id hashed from
	 * its cookie identity, so a re-run overwrites its own prior entry instead of
	 * duplicating, and never touches the user's other custom cookies. `expires`
	 * stays null: the banner asks again once a custom cookie's `expires` passes,
	 * and WPConsent never did.
	 *
	 * @param array<int, array{name?: string, description?: string, duration?: string, provider?: string, domain?: string, category_slug?: string}> $source  Normalized WPConsent cookies.
	 * @param array<string, mixed>                                                                                                                  $current Current `custom_cookies` (assoc by id).
	 * @since 1.8.0
	 * @return array<string, mixed> Merged custom cookies.
	 */
	public function map_cookies( array $source, array $current ): array {
		$merged = $current;

		foreach ( $source as $cookie ) {
			$name = isset( $cookie['name'] ) ? trim( (string) $cookie['name'] ) : '';
			if ( $name === '' ) {
				continue;
			}

			// The key is also the stored `id`, and the settings writer sanitizes
			// values but not keys, so hash the cookie identity: a readable id is
			// neither sanitize-stable nor injective (the slug folded `_ga` onto `ga`).
			$id = 'wpc_' . md5( Cookie_Identity::normalize_name( $name ) );

			$merged[ $id ] = [
				'id'          => $id,
				'name'        => $name,
				'description' => isset( $cookie['description'] ) ? (string) $cookie['description'] : '',
				'category'    => $this->resolve_category_id( isset( $cookie['category_slug'] ) ? (string) $cookie['category_slug'] : '' ),
				'duration'    => $this->map_duration( isset( $cookie['duration'] ) ? (string) $cookie['duration'] : '' ),
				'provider'    => isset( $cookie['provider'] ) ? (string) $cookie['provider'] : '',
				'purpose'     => '',
				'domain'      => isset( $cookie['domain'] ) ? (string) $cookie['domain'] : '',
				'type'        => 'custom',
				'expires'     => null,
			];
		}

		return $merged;
	}

	/**
	 * WPConsent's free-text duration as SureCookie's whole-day count, or '' when it
	 * states no lifetime we can read. '0' is a session or sub-day lifetime.
	 *
	 * @param string $duration WPConsent duration, e.g. "2 years" or "Session".
	 * @since 1.8.0
	 * @return string
	 */
	private function map_duration( string $duration ): string {
		$duration = strtolower( trim( $duration ) );

		if ( ctype_digit( $duration ) ) {
			return strlen( $duration ) <= 6 ? (string) (int) $duration : '';
		}

		// English "<n> <unit>" pairs, trailing words ignored. A stated lifetime wins
		// over a mention of a session ("14 days if remembered, otherwise session").
		$pattern = '/(?<![\d.,])(\d{1,6}(?:\.\d+)?)\h*(years?|yrs?|months?|weeks?|wks?|days?|hours?|hrs?|minutes?|mins?|seconds?|secs?|milliseconds?|ms)\b/u';
		if ( ! preg_match( $pattern, $duration ) ) {
			return strpos( $duration, 'session' ) !== false || preg_match( '/^a few (?:second|minute|hour)s\b/', $duration ) ? '0' : '';
		}

		$days_per = [
			'year'        => 365,
			'yr'          => 365,
			'month'       => 30,
			'week'        => 7,
			'wk'          => 7,
			'day'         => 1,
			'hour'        => 1 / 24,
			'hr'          => 1 / 24,
			'minute'      => 1 / 1440,
			'min'         => 1 / 1440,
			'second'      => 1 / 86400,
			'sec'         => 1 / 86400,
			'millisecond' => 0,
		];

		// Alternatives ("1 year or 6 months") never add up: the longest wins. Within one,
		// pairs add up only as a compound ("1 year 6 months"), each worth less than one
		// of the unit before it; a restatement ("13 months (400 days)") keeps the largest.
		$longest      = 0.0;
		$alternatives = preg_split( '/\b(?:or|otherwise)\b|\//u', $duration );
		foreach ( false === $alternatives ? [ $duration ] : $alternatives as $alternative ) {
			preg_match_all( $pattern, $alternative, $pairs, PREG_SET_ORDER );
			$total    = 0.0;
			$largest  = 0.0;
			$compound = true;
			$limit    = INF;
			foreach ( $pairs as $pair ) {
				$unit     = $pair[2] === 'ms' ? 'millisecond' : rtrim( $pair[2], 's' );
				$days     = (float) $pair[1] * $days_per[ $unit ];
				$compound = $compound && $days < $limit;
				$limit    = $days_per[ $unit ];
				$total   += $days;
				$largest  = max( $largest, $days );
			}
			$longest = max( $longest, $compound ? $total : $largest );
		}

		// Rounded first so 1.2 * 365 lands on 438 and 24 / 24 on 1, not just below.
		return (string) (int) floor( round( $longest, 6 ) );
	}

	/**
	 * Resolve a WPConsent category slug to a SureCookie category id. Standard
	 * slugs map onto SureCookie's defaults; an empty slug falls back to
	 * `uncategorized`; anything else becomes a stable `wpc_<slug>` id.
	 *
	 * @param string $slug WPConsent slug (or name when no slug is available).
	 * @since 1.8.0
	 * @return string
	 */
	public function resolve_category_id( string $slug ): string {
		$normalized = $this->slugify( $slug );

		if ( isset( self::CATEGORY_SLUG_MAP[ $normalized ] ) ) {
			return self::CATEGORY_SLUG_MAP[ $normalized ];
		}

		if ( $normalized === '' ) {
			return 'uncategorized';
		}

		// Hashed for the same reason the cookie id is: the id is also an array
		// key and slugify() folds `-`, `.`, spaces and case onto `_`, so
		// `ad-tracking` and `ad_tracking` became one category, took each other's
		// cookies, and turned a recorded refusal into a grant on imported rows.
		return 'wpc_' . md5( strtolower( trim( $slug ) ) );
	}

	/**
	 * Count consent-log rows and known services that a run would import.
	 *
	 * @since 1.8.0
	 * @return array<string, int>
	 */
	public function preview_extras(): array {
		global $wpdb;

		$extras = [];
		$table  = $this->get_logs_table();

		if ( $table !== '' ) {
			$cursor = (int) get_option( self::LOGS_MARKER_OPTION, 0 );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Admin-only count; %i is a valid WP 6.2+ identifier placeholder.
			$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE consent_id > %d', $table, $cursor ) );

			if ( $count > 0 ) {
				$extras['consent_logs'] = $count;
			}
		}

		$pending = $this->pending_known_services();
		if ( $pending !== [] ) {
			$extras['known_services'] = count( $pending );
		}

		return $extras;
	}

	/**
	 * Match WPConsent service names to SureCookie catalog slugs. Pure: a kebab
	 * slug of each name is matched against catalog slugs, then catalog labels.
	 *
	 * @param array<int, string>                  $names   Source service names.
	 * @param array<string, array<string, mixed>> $catalog Services catalog.
	 * @since 1.8.0
	 * @return array<int, string> Matched catalog slugs (unique).
	 */
	public function match_known_services( array $names, array $catalog ): array {
		$aliases = [ 'meta-pixel' => 'facebook-pixel' ];

		$by_label = [];
		foreach ( $catalog as $slug => $service ) {
			if ( is_array( $service ) && isset( $service['label'] ) ) {
				$by_label[ $this->kebab( (string) $service['label'] ) ] = (string) $slug;
			}
		}

		$matched = [];
		foreach ( $names as $name ) {
			$key  = $this->kebab( (string) $name );
			$slug = $aliases[ $key ] ?? ( isset( $catalog[ $key ] ) ? $key : ( $by_label[ $key ] ?? '' ) );

			if ( $slug !== '' && isset( $catalog[ $slug ] ) && ! in_array( $slug, $matched, true ) ) {
				$matched[] = $slug;
			}
		}

		return $matched;
	}

	/**
	 * Convert WPConsent consent_data into a SureCookie preferences map + action.
	 *
	 * Pure (no WordPress calls). Category slugs are mapped to SureCookie ids
	 * (statistics -> analytics, etc.); non-category keys (e.g. respect_gpc) are
	 * skipped; essential is always granted. Action is derived from the
	 * non-essential choices: all granted -> accepted, none -> declined, else
	 * partially_accepted.
	 *
	 * @param array<string, mixed> $consent_data Decoded WPConsent consent_data.
	 * @since 1.8.0
	 * @return array{preferences: array<string, bool>, action: string}
	 */
	public function derive_consent( array $consent_data ): array {
		$preferences = [ 'essential' => true ];
		$total       = 0;
		$granted     = 0;

		foreach ( $consent_data as $slug => $value ) {
			$slug = (string) $slug;
			if ( in_array( $slug, self::NON_CATEGORY_KEYS, true ) ) {
				continue;
			}

			$id = $this->resolve_category_id( $slug );
			if ( $id === 'essential' ) {
				continue;
			}

			$is_granted         = $this->is_granted( $value );
			$preferences[ $id ] = $is_granted;
			++$total;
			$granted += $is_granted ? 1 : 0;
		}

		if ( $total === 0 || $granted === $total ) {
			$action = 'accepted';
		} elseif ( $granted === 0 ) {
			$action = 'declined';
		} else {
			$action = 'partially_accepted';
		}

		return [
			'preferences' => $preferences,
			'action'      => $action,
		];
	}

	/**
	 * Deterministic UUID-shaped session id from a WPConsent consent_id, so
	 * re-imports map to the same row (dedup via the table's session+timestamp
	 * unique key) instead of duplicating.
	 *
	 * @param string $consent_id WPConsent consent_id.
	 * @since 1.8.0
	 * @return string 36-char id.
	 */
	public function derive_session_id( string $consent_id ): string {
		$hash = md5( 'wpconsent-consent-' . $consent_id );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hash, 0, 8 ),
			substr( $hash, 8, 4 ),
			substr( $hash, 12, 4 ),
			substr( $hash, 16, 4 ),
			substr( $hash, 20, 12 )
		);
	}

	/**
	 * Import WPConsent Pro consent-log rows and known services.
	 *
	 * @since 1.8.0
	 * @return array<string, int>
	 */
	protected function import_extras(): array {
		$consent_logs = $this->import_consent_logs();

		return [
			'consent_logs'            => $consent_logs,
			// A stopped run leaves the rest of the source eligible, so the summary
			// can offer a re-run instead of reporting a complete history.
			'consent_logs_incomplete' => $this->consent_logs_incomplete ? 1 : 0,
			'known_services'          => $this->import_known_services(),
		];
	}

	/**
	 * Map WPConsent's layout + position pair onto SureCookie's notice_type +
	 * notice_position pair.
	 *
	 * Both targets must be written together: notice_position's legal values
	 * depend on notice_type, and a mismatched pair is not rejected, it silently
	 * renders somewhere else. WPConsent's "long" layout is our full-width
	 * "banner" (top/bottom); its "floating" layout is our "box", whose only
	 * corners are along the bottom, so a floating top corner keeps its side and
	 * is reported under the `banner_layout` review key.
	 *
	 * @param array<string, mixed> $patch Patch being built (by reference).
	 * @param array<string, mixed> $s     WPConsent settings.
	 * @since 1.8.0
	 * @return void
	 */
	private function map_position( array &$patch, array $s ): void {
		$position = isset( $s['banner_position'] ) && is_scalar( $s['banner_position'] )
			? strtolower( trim( (string) $s['banner_position'] ) )
			: '';
		if ( $position === '' ) {
			return;
		}

		$layout = isset( $s['banner_layout'] ) && is_scalar( $s['banner_layout'] )
			? strtolower( trim( (string) $s['banner_layout'] ) )
			: '';

		// A corner value means floating even when banner_layout is absent.
		$is_floating = $layout === 'floating' || isset( self::FLOATING_POSITION_MAP[ $position ] );

		if ( ! $is_floating ) {
			$patch['notice_type']     = 'banner';
			$patch['notice_position'] = $position === 'top' ? 'top' : 'bottom';

			return;
		}

		$patch['notice_type']     = 'box';
		$patch['notice_position'] = self::FLOATING_POSITION_MAP[ $position ] ?? 'bottom-right';
	}

	/**
	 * Remap WPConsent's button-order tokens to SureCookie's, keeping every
	 * SureCookie button present (WPConsent has no "only essential" button, so
	 * SureCookie's `accept` token is appended at its default spot).
	 *
	 * @param array<int, mixed> $order WPConsent order tokens.
	 * @since 1.8.0
	 * @return string Comma-joined SureCookie order.
	 */
	private function map_button_order( array $order ): string {
		$token_map = [
			'accept'      => 'accept_all',
			'cancel'      => 'decline',
			'preferences' => 'preferences',
		];

		$mapped = [];
		foreach ( $order as $token ) {
			$token = (string) $token;
			if ( isset( $token_map[ $token ] ) && ! in_array( $token_map[ $token ], $mapped, true ) ) {
				$mapped[] = $token_map[ $token ];
			}
		}

		// Append any SureCookie tokens the source did not cover, in SureCookie's
		// default relative order, so no button silently disappears.
		foreach ( [ 'accept_all', 'accept', 'preferences', 'decline' ] as $token ) {
			if ( ! in_array( $token, $mapped, true ) ) {
				$mapped[] = $token;
			}
		}

		return implode( ',', $mapped );
	}

	/**
	 * Lowercase + underscore slug, WordPress-free so the mappers stay unit-testable.
	 *
	 * @param string $value Raw value.
	 * @since 1.8.0
	 * @return string
	 */
	private function slugify( string $value ): string {
		$value = strtolower( $value );
		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative -- No /e modifier; simple character-class collapse.
		$value = (string) preg_replace( '/[^a-z0-9_]+/', '_', $value );

		return trim( $value, '_' );
	}

	/**
	 * Read WPConsent's top-level category terms into a normalized array. Returns
	 * empty when the taxonomy is not registered (source plugin inactive), so
	 * settings still migrate even though categories/cookies cannot.
	 *
	 * @since 1.8.0
	 * @return array<int, array{slug: string, name: string, description: string, required: bool}>
	 */
	private function read_source_categories(): array {
		if ( ! taxonomy_exists( self::SOURCE_TAXONOMY ) ) {
			return [];
		}

		$terms = get_terms(
			[
				'taxonomy'   => self::SOURCE_TAXONOMY,
				'hide_empty' => false,
				'parent'     => 0,
			]
		);

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return [];
		}

		$out = [];
		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}

			$out[] = [
				'slug'        => $term->slug,
				'name'        => $term->name,
				'description' => $term->description,
				'required'    => $term->slug === 'essential' ? true : (bool) get_term_meta( $term->term_id, 'wpconsent_required', true ),
			];
		}

		return $out;
	}

	/**
	 * Read WPConsent cookie posts into a normalized array. The cookie's display
	 * name prefers the stored cookie key (`wpconsent_cookie_id`); the category is
	 * resolved by walking a service child-term up to its parent category, and the
	 * service term's name becomes the provider.
	 *
	 * @since 1.8.0
	 * @return array<int, array{name: string, description: string, duration: string, provider: string, category_slug: string}>
	 */
	private function read_source_cookies(): array {
		if ( ! post_type_exists( self::SOURCE_CPT ) ) {
			return [];
		}

		// Paged rather than unbounded: a long-running site's cookie list is
		// arbitrarily large and the whole set would otherwise load at once.
		$posts  = [];
		$offset = 0;
		do {
			$batch = get_posts(
				[
					'post_type'        => self::SOURCE_CPT,
					'post_status'      => 'publish',
					'posts_per_page'   => self::POSTS_BATCH_SIZE,
					'offset'           => $offset,
					'suppress_filters' => false,
				]
			);

			if ( ! is_array( $batch ) || $batch === [] ) {
				break;
			}

			$found   = count( $batch );
			$posts   = array_merge( $posts, $batch );
			$offset += self::POSTS_BATCH_SIZE;
		} while ( $found === self::POSTS_BATCH_SIZE );

		$out = [];
		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$cookie_key = (string) get_post_meta( $post->ID, 'wpconsent_cookie_id', true );

			$category_slug = '';
			$provider      = '';
			$terms         = wp_get_object_terms( $post->ID, self::SOURCE_TAXONOMY );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) && $terms[0] instanceof \WP_Term ) {
				$term = $terms[0];

				if ( $term->parent > 0 ) {
					$provider      = $term->name;
					$parent        = get_term( $term->parent, self::SOURCE_TAXONOMY );
					$category_slug = $parent instanceof \WP_Term ? $parent->slug : '';
				} else {
					$category_slug = $term->slug;
				}
			}

			$out[] = [
				'name'          => $cookie_key !== '' ? $cookie_key : (string) $post->post_title,
				'description'   => (string) $post->post_content,
				'duration'      => (string) get_post_meta( $post->ID, 'wpconsent_cookie_duration', true ),
				'provider'      => $provider,
				'category_slug' => $category_slug,
			];
		}

		return $out;
	}

	/**
	 * Catalog slugs for WPConsent services not yet installed or suppressed.
	 * Suppressed services were removed by the user on purpose, so a migration
	 * never re-adds them.
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	private function pending_known_services(): array {
		$registry = Installed_Services::get_instance();
		$matched  = $this->match_known_services(
			$this->read_source_services(),
			Services_Source::get_instance()->get_catalog()
		);

		return array_values(
			array_filter(
				$matched,
				static function ( string $slug ) use ( $registry ): bool {
					return ! $registry->is_installed( $slug ) && ! $registry->is_suppressed( $slug );
				}
			)
		);
	}

	/**
	 * Register matched WPConsent services in the known-services registry.
	 * Runs after the cookies patch, so install() dedups against the cookies
	 * that just migrated instead of duplicating them.
	 *
	 * @since 1.8.0
	 * @return int Services installed this run.
	 */
	private function import_known_services(): int {
		$registry = Installed_Services::get_instance();
		$imported = 0;

		foreach ( $this->pending_known_services() as $slug ) {
			$result = $registry->install( $slug );
			if ( ! empty( $result['success'] ) ) {
				++$imported;
			}
		}

		return $imported;
	}

	/**
	 * Read WPConsent service child-terms (services live under their category
	 * term) into a flat name list.
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	private function read_source_services(): array {
		if ( ! taxonomy_exists( self::SOURCE_TAXONOMY ) ) {
			return [];
		}

		$terms = get_terms(
			[
				'taxonomy'   => self::SOURCE_TAXONOMY,
				'hide_empty' => false,
			]
		);

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return [];
		}

		$out = [];
		foreach ( $terms as $term ) {
			if ( $term instanceof \WP_Term && $term->parent > 0 ) {
				$out[] = $term->name;
			}
		}

		return $out;
	}

	/**
	 * Lowercase kebab slug matching the services catalog's slug style.
	 *
	 * @param string $value Raw value.
	 * @since 1.8.0
	 * @return string
	 */
	private function kebab( string $value ): string {
		$value = strtolower( $value );
		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative -- No /e modifier; simple character-class collapse.
		$value = (string) preg_replace( '/[^a-z0-9]+/', '-', $value );

		return trim( $value, '-' );
	}

	/**
	 * Interpret a WPConsent consent value as granted. Handles bool, numeric and
	 * common string forms; unknown values are treated as not granted.
	 *
	 * @param mixed $value Source value.
	 * @since 1.8.0
	 * @return bool
	 */
	private function is_granted( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			return (int) $value !== 0;
		}

		return in_array( strtolower( trim( (string) $value ) ), [ 'true', 'granted', 'yes', 'on', 'allow', 'accepted' ], true );
	}

	/**
	 * Batch-read WPConsent consent logs and upsert them into SureCookie's table,
	 * preserving the original timestamp and advancing a high-water marker so
	 * re-runs resume. Idempotent: the deterministic session id + the table's
	 * unique (session_id, timestamp) key prevent duplicates.
	 *
	 * @since 1.8.0
	 * @return int Number of rows imported this run.
	 */
	private function import_consent_logs(): int {
		global $wpdb;

		$this->consent_logs_incomplete = false;

		$table = $this->get_logs_table();
		if ( $table === '' ) {
			return 0;
		}

		$cursor    = (int) get_option( self::LOGS_MARKER_OPTION, 0 );
		$imported  = 0;
		$row_count = 0;

		do {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Batched admin migration read; %i is a valid WP 6.2+ identifier placeholder.
			$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT consent_id, user_id, ip_address, country_code, consent_data, created_at FROM %i WHERE consent_id > %d ORDER BY consent_id ASC LIMIT %d', $table, $cursor, self::LOGS_BATCH_SIZE ), ARRAY_A );

			if ( empty( $rows ) || ! is_array( $rows ) ) {
				break;
			}

			$row_count = count( $rows );

			foreach ( $rows as $row ) {
				// The cursor is the retry marker, so a row whose write failed has to
				// stay behind it or `consent_id > cursor` never offers it again. The
				// outer loop stops too: an unadvanced cursor re-reads the same batch.
				if ( ! $this->import_consent_row( $row ) ) {
					$this->consent_logs_incomplete = true;
					break;
				}

				$consent_id = (int) ( $row['consent_id'] ?? 0 );
				if ( $consent_id > $cursor ) {
					$cursor = $consent_id;
				}

				++$imported;
			}

			update_option( self::LOGS_MARKER_OPTION, $cursor, false );
		} while ( ! $this->consent_logs_incomplete && $row_count === self::LOGS_BATCH_SIZE );

		return $imported;
	}

	/**
	 * Map + upsert a single WPConsent consent-log row.
	 *
	 * @param array<string, mixed> $row WPConsent row (assoc).
	 * @since 1.8.0
	 * @return bool True when the row was written.
	 */
	private function import_consent_row( array $row ): bool {
		$raw  = $row['consent_data'] ?? '';
		$data = is_array( $raw ) ? $raw : json_decode( is_string( $raw ) ? $raw : '', true );
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		$derived     = $this->derive_consent( $data );
		$preferences = wp_json_encode( $derived['preferences'] );
		if ( ! is_string( $preferences ) ) {
			return false;
		}

		$country = strtoupper( trim( (string) ( $row['country_code'] ?? '' ) ) );
		if ( $country === '' ) {
			$country = 'Unknown';
		}

		$timestamp = (string) ( $row['created_at'] ?? '' );
		if ( $timestamp === '' || $timestamp === '0000-00-00 00:00:00' ) {
			$timestamp = null;
		}

		$result = ConsentLog::upsert(
			wp_privacy_anonymize_ip( (string) ( $row['ip_address'] ?? '' ) ),
			(int) ( $row['user_id'] ?? 0 ),
			$this->derive_session_id( (string) ( $row['consent_id'] ?? '' ) ),
			$preferences,
			$derived['action'],
			$country,
			$timestamp
		);

		return $result !== false;
	}

	/**
	 * Prefixed WPConsent consent-log table name, or '' when it does not exist
	 * (WPConsent Pro inactive / never logged), so settings still migrate.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	private function get_logs_table(): string {
		global $wpdb;

		$table = $wpdb->prefix . self::SOURCE_LOGS_TABLE;
		$found = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
		);

		return $found === $table ? $table : '';
	}
}
