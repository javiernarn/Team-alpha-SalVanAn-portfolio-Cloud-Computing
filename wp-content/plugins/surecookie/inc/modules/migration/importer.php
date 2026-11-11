<?php
/**
 * Migration importer contract.
 *
 * Every source-plugin importer (WPConsent, and future CookieYes/Complianz/etc.)
 * implements this so the REST layer can treat them uniformly. Modelled on
 * SureRank's importer interface, narrowed to global settings (cookie consent has
 * no per-post data).
 *
 * @package SureCookie\Inc\Modules\Migration
 * @since 1.8.0
 */

namespace SureCookie\Inc\Modules\Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Importer interface.
 *
 * @since 1.8.0
 */
interface Importer {
	/**
	 * Machine slug used in the registry and REST routes (e.g. 'wpconsent').
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Human-readable source plugin name.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * Whether the source plugin is active on this site.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public function is_plugin_active(): bool;

	/**
	 * Whether the source plugin is installed (present on disk), active or not.
	 * This is the gate for offering a migration -- leftover options from a
	 * deleted plugin must not keep it listed.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public function is_plugin_installed(): bool;

	/**
	 * Whether the source plugin has saved data worth importing.
	 *
	 * @since 1.8.0
	 * @return bool
	 */
	public function has_data(): bool;

	/**
	 * Source plugin directory slug (e.g. 'wpconsent-cookies-banner-privacy-suite').
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_plugin_dir(): string;

	/**
	 * Source plugin's main file relative to the plugins directory, or '' when it
	 * is not installed. This is what the deactivate route takes.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_plugin_file(): string;

	/**
	 * Deactivate the source plugin (used after a successful import when the
	 * admin opts to switch fully to SureCookie). No-op if not installed/active.
	 *
	 * @since 1.8.0
	 * @return bool True if a deactivation was performed.
	 */
	public function deactivate_plugin(): bool;

	/**
	 * Build the SureCookie-keyed settings patch from the source, without writing.
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function build_settings_patch(): array;

	/**
	 * Preview what an import would apply / skip, without writing.
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function preview(): array;

	/**
	 * Run the import (write settings) and return a summary.
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function import(): array;

	/**
	 * Reset every managed setting back to its SureCookie default.
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function reset(): array;

	/**
	 * Persisted migration status (migrated flag + last-migrated time).
	 *
	 * @since 1.8.0
	 * @return array<string, mixed>
	 */
	public function get_status(): array;
}
