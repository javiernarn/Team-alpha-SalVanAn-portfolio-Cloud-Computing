<?php
/**
 * Base importer.
 *
 * Shared write/idempotency plumbing for migration importers. Concrete importers
 * supply the source-specific reading + mapping; this class applies the resulting
 * SureCookie-keyed patch through the same sanitized path the admin settings save
 * uses, and never creates new tables/options beyond a one-shot migrated marker.
 *
 * @package SureCookie\Inc\Modules\Migration
 * @since 1.8.0
 */

namespace SureCookie\Inc\Modules\Migration;

use SureCookie\Inc\Functions\Sanitize;
use SureCookie\Inc\Functions\Update;
use SureCookie\Inc\Utils\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Base_Importer
 *
 * @since 1.8.0
 */
abstract class Base_Importer implements Importer {
	/**
	 * {@inheritDoc}
	 */
	abstract public function get_slug(): string;

	/**
	 * {@inheritDoc}
	 */
	abstract public function get_label(): string;

	/**
	 * {@inheritDoc}
	 */
	abstract public function is_plugin_active(): bool;

	/**
	 * {@inheritDoc}
	 */
	abstract public function has_data(): bool;

	/**
	 * {@inheritDoc}
	 */
	abstract public function build_settings_patch(): array;

	/**
	 * Source plugin directory slug (e.g. 'wpconsent-cookies-banner-privacy-suite').
	 * Used to detect installation. Override in the concrete importer.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_plugin_dir(): string {
		return '';
	}

	/**
	 * Whether the source plugin is installed on disk (active or inactive).
	 *
	 * Detection is based on the installed-plugins list, not on leftover options,
	 * so a deleted plugin (whose settings may still sit in the DB) is correctly
	 * reported as unavailable.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public function is_plugin_installed(): bool {
		if ( $this->is_plugin_active() ) {
			return true;
		}

		$dir = $this->get_plugin_dir();
		if ( $dir === '' ) {
			return false;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( get_plugins() ) as $plugin_file ) {
			if ( strpos( (string) $plugin_file, $dir . '/' ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve the source plugin's main file (e.g. 'wpconsent-.../wpconsent.php')
	 * from its directory slug, or '' when not installed.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_plugin_file(): string {
		$dir = $this->get_plugin_dir();
		if ( $dir === '' ) {
			return '';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( get_plugins() ) as $plugin_file ) {
			if ( strpos( (string) $plugin_file, $dir . '/' ) === 0 ) {
				return (string) $plugin_file;
			}
		}

		return '';
	}

	/**
	 * Deactivate the source plugin. No-op when it cannot be resolved, or when the
	 * current user may not manage plugins.
	 *
	 * The migration route is gated on the SureCookie capability alone, so the
	 * plugin-management check belongs here, where every importer inherits it.
	 * That makes the method current-user sensitive: a WP-CLI or cron caller has
	 * no user and would be refused.
	 *
	 * @since 1.8.0
	 * @return bool True if a deactivation was performed.
	 */
	public function deactivate_plugin(): bool {
		$file = $this->get_plugin_file();
		if ( $file === '' ) {
			return false;
		}

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$network_wide = is_multisite() && is_plugin_active_for_network( $file );

		if ( ! current_user_can( 'activate_plugins' )
			|| ( $network_wide && ! current_user_can( 'manage_network_plugins' ) ) ) {
			return false;
		}

		// Scope is explicit: WordPress reads the omitted default as "the network
		// too", so a site-only deactivation would strip the network activation.
		deactivate_plugins( [ $file ], false, $network_wide );

		return true;
	}

	/**
	 * Settings keys that this importer can map but that require a feature not
	 * present on this site (e.g. a Pro-only setting). Reported in the summary so
	 * the UI can list "may not be migrated completely" without silently dropping.
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	public function get_review_keys(): array {
		return [];
	}

	/**
	 * Run the import: split the patch into writable vs unavailable keys, write the
	 * writable ones through the sanitized settings path, stamp the migrated marker.
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function import(): array {
		$patch  = $this->build_settings_patch();
		$result = $this->apply( $patch );
		$extras = $this->import_extras();
		$now    = time();

		update_option(
			$this->get_migrated_option_key(),
			[
				'version' => SURECOOKIE_VERSION,
				'applied' => $result['applied'],
				'at'      => $now,
			],
			false
		);

		return [
			'success'     => true,
			'slug'        => $this->get_slug(),
			'applied'     => $result['applied'],
			'needs_pro'   => $result['needs_pro'],
			'review'      => $this->get_review_keys(),
			'extras'      => $extras,
			'migrated'    => true,
			'migrated_at' => $this->format_timestamp( $now ),
		];
	}

	/**
	 * Preview counts for non-settings datasets (e.g. how many consent-log rows
	 * would import), without writing. Override in the concrete importer.
	 *
	 * @since 1.8.0
	 * @return array<string, int>
	 */
	public function preview_extras(): array {
		return [];
	}

	/**
	 * Reset every setting this importer manages back to its SureCookie default,
	 * and clear the migrated marker. Removing the keys from the saved option lets
	 * the registered defaults apply again (Settings::get() merges defaults).
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function reset(): array {
		$registered = Options::get_all_configurations();
		$settings   = get_option( SURECOOKIE_SETTINGS_OPTION, [] );
		$settings   = is_array( $settings ) ? $settings : [];
		$reset      = [];

		foreach ( $this->get_managed_keys() as $key ) {
			if ( array_key_exists( $key, $registered ) && array_key_exists( $key, $settings ) ) {
				unset( $settings[ $key ] );
				$reset[] = $key;
			}
		}

		Update::option( SURECOOKIE_SETTINGS_OPTION, $settings );
		do_action( 'surecookie_admin_settings_after_processing', $settings );
		delete_option( $this->get_migrated_option_key() );

		return [
			'success'  => true,
			'slug'     => $this->get_slug(),
			'reset'    => $reset,
			'migrated' => false,
		];
	}

	/**
	 * SureCookie setting keys this importer manages (writes/resets). Override in
	 * the concrete importer.
	 *
	 * @since 1.8.0
	 * @return array<int, string>
	 */
	public function get_managed_keys(): array {
		return [];
	}

	/**
	 * Persisted migration status for this source (drives the "last migrated"
	 * label so it survives page changes).
	 *
	 * @since 1.8.0
	 * @return array{migrated: bool, migrated_at: string, applied: array<int, string>}
	 */
	public function get_status(): array {
		$marker = get_option( $this->get_migrated_option_key(), [] );
		$marker = is_array( $marker ) ? $marker : [];
		$at     = isset( $marker['at'] ) ? (int) $marker['at'] : 0;

		return [
			'migrated'    => $at > 0,
			'migrated_at' => $at > 0 ? $this->format_timestamp( $at ) : '',
			'applied'     => isset( $marker['applied'] ) && is_array( $marker['applied'] ) ? array_values( $marker['applied'] ) : [],
		];
	}

	/**
	 * Option key recording that this source has been migrated (idempotency).
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_migrated_option_key(): string {
		return 'surecookie_migrated_' . $this->get_slug();
	}

	/**
	 * Preview what an import would do, without writing anything. Includes the
	 * actual values (`data`) that would be written so the UI can show them.
	 *
	 * @since 1.8.0
	 * @return array{applied: array<int, string>, needs_pro: array<int, string>, review: array<int, string>, data: array<string, mixed>}
	 */
	public function preview(): array {
		$parts = $this->partition( $this->build_settings_patch() );

		return [
			'applied'   => array_keys( $parts['writable'] ),
			'needs_pro' => $parts['needs_pro'],
			'review'    => $this->get_review_keys(),
			'data'      => $parts['writable'],
			'extras'    => $this->preview_extras(),
		];
	}

	/**
	 * Import data that does not live in the settings option (e.g. consent-log
	 * rows written straight to their own table). Runs after the settings patch is
	 * applied. Returns a map of dataset => count for the summary. Override in the
	 * concrete importer; default is a no-op so settings-only importers are unaffected.
	 *
	 * @since 1.8.0
	 * @return array<string, int>
	 */
	protected function import_extras(): array {
		return [];
	}

	/**
	 * Format a Unix timestamp in the site's date + time format and timezone.
	 *
	 * @param int $timestamp Unix timestamp.
	 * @since 1.8.0
	 * @return string
	 */
	protected function format_timestamp( int $timestamp ): string {
		$format = trim( (string) get_option( 'date_format' ) . ' ' . (string) get_option( 'time_format' ) );
		if ( $format === '' ) {
			$format = 'Y-m-d H:i';
		}

		return (string) wp_date( $format, $timestamp );
	}

	/**
	 * Apply a SureCookie-keyed patch.
	 *
	 * Writable keys are written via Sanitize::settings() (which merges into the
	 * current option and type-cleans each value, exactly like the admin save).
	 * Keys not registered on this site (Pro-only settings when Pro is inactive)
	 * are not written; the switched-on ones are reported under `needs_pro`.
	 *
	 * @param array<string, mixed> $patch SureCookie-keyed values.
	 * @since 1.8.0
	 * @return array{applied: array<int, string>, needs_pro: array<int, string>}
	 */
	protected function apply( array $patch ): array {
		$parts = $this->partition( $patch );

		if ( ! empty( $parts['writable'] ) ) {
			// The admin save's pipeline: without it no page cache was purged, so cached pages kept the old banner.
			do_action( 'surecookie_admin_settings_before_processing', $parts['writable'] );
			$writable          = apply_filters( 'surecookie_update_admin_settings_data', $parts['writable'] );
			$parts['writable'] = is_array( $writable ) ? $writable : [];
			$settings          = Sanitize::settings( $parts['writable'] );

			Update::option( SURECOOKIE_SETTINGS_OPTION, $settings );
			do_action( 'surecookie_admin_settings_after_processing', $settings );
		}

		return [
			'applied'   => array_keys( $parts['writable'] ),
			'needs_pro' => $parts['needs_pro'],
		];
	}

	/**
	 * Copy a string value into the patch only when the source value is non-empty,
	 * so a SureCookie default is never overwritten with a blank.
	 *
	 * @param array<string, mixed> $patch       Patch being built (by reference).
	 * @param array<string, mixed> $source      Source settings.
	 * @param string               $source_key  Key in the source array.
	 * @param string               $target_key  SureCookie settings key.
	 * @since 1.8.0
	 * @return void
	 */
	protected function set_string( array &$patch, array $source, string $source_key, string $target_key ): void {
		if ( ! isset( $source[ $source_key ] ) ) {
			return;
		}

		$value = (string) $source[ $source_key ];
		if ( trim( $value ) === '' ) {
			return;
		}

		$patch[ $target_key ] = $value;
	}

	/**
	 * Copy a boolean value into the patch when the source key is present (an
	 * explicit 0 is meaningful, so presence -- not truthiness -- is the gate).
	 *
	 * @param array<string, mixed> $patch      Patch being built (by reference).
	 * @param array<string, mixed> $source     Source settings.
	 * @param string               $source_key Key in the source array.
	 * @param string               $target_key SureCookie settings key.
	 * @since 1.8.0
	 * @return void
	 */
	protected function set_bool( array &$patch, array $source, string $source_key, string $target_key ): void {
		if ( ! array_key_exists( $source_key, $source ) ) {
			return;
		}

		$patch[ $target_key ] = (bool) $source[ $source_key ];
	}

	/**
	 * Split a patch into keys writable on this site (registered in the settings
	 * dataset) and Pro-only keys the source turned on while Pro is off. An off
	 * feature loses nothing without Pro, so it is not reported as skipped.
	 *
	 * @param array<string, mixed> $patch SureCookie-keyed values.
	 * @since 1.8.0
	 * @return array{writable: array<string, mixed>, needs_pro: array<int, string>}
	 */
	private function partition( array $patch ): array {
		$registered = Options::get_all_configurations();
		$writable   = [];
		$needs_pro  = [];

		foreach ( $patch as $key => $value ) {
			if ( array_key_exists( $key, $registered ) ) {
				$writable[ $key ] = $value;
			} elseif ( ! empty( $value ) ) {
				$needs_pro[] = $key;
			}
		}

		return [
			'writable'  => $writable,
			'needs_pro' => $needs_pro,
		];
	}
}
