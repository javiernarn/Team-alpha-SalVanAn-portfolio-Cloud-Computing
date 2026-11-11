<?php
/**
 * Automatic Scanning diff engine.
 *
 * Pure functions that build a compact per-scan snapshot and compute the
 * change set between two scans. Cookies are sticky in storage, so changes are
 * always derived scan-to-scan from these snapshots - never from the
 * accumulated cookie option. No cookie values are ever stored.
 *
 * @package SureCookie\Inc\Modules\AutomaticScanning
 * @since 1.2.0
 */

namespace SureCookie\Inc\Modules\AutomaticScanning;

use SureCookie\Inc\Functions\Cookie_Identity;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * DiffEngine
 *
 * @since 1.2.0
 */
class DiffEngine {
	/**
	 * Build a compact snapshot of a scan's reported cookies and domains.
	 *
	 * @param array<string, array<int, array<string, mixed>>> $cookies_by_category Reported cookies grouped by category.
	 * @param array<int, string>                              $domains             Reported third-party domains.
	 * @since 1.2.0
	 * @return array{signatures: array<string, array{name:string, domain:string, set_via:string, category:string}>, domains: array<int, string>}
	 */
	public static function build_snapshot( array $cookies_by_category, array $domains ): array {
		$signatures = [];

		foreach ( $cookies_by_category as $cookies ) {
			if ( ! is_array( $cookies ) ) {
				continue;
			}

			foreach ( $cookies as $cookie ) {
				$signature = (string) ( $cookie['signature_id'] ?? '' );

				if ( $signature === '' ) {
					continue;
				}

				$signatures[ $signature ] = [
					'name'     => (string) ( $cookie['name'] ?? '' ),
					'domain'   => (string) ( $cookie['domain'] ?? '' ),
					// Carried for Pro's guard, which quarantines from this row:
					// the cookie's scope is not the host that set it. Absent from
					// snapshots written before this, and the identity key is
					// name+domain, so an older one still diffs unchanged.
					'set_via'  => (string) ( $cookie['set_via'] ?? '' ),
					'category' => (string) ( $cookie['category'] ?? '' ),
				];
			}
		}

		$unique_domains = [];
		foreach ( $domains as $domain ) {
			$domain = (string) $domain;
			if ( $domain !== '' ) {
				$unique_domains[ $domain ] = true;
			}
		}

		return [
			'signatures' => $signatures,
			'domains'    => array_keys( $unique_domains ),
		];
	}

	/**
	 * A change set with nothing in it, in the shape {@see self::diff()} returns.
	 * Used for the first recorded scan, which has nothing to compare against.
	 *
	 * @since 1.5.0
	 * @return array{added: array<int, array<string, string>>, removed: array<int, array<string, string>>, recategorized: array<int, array<string, string>>, domains_added: array<int, string>}
	 */
	public static function empty_diff(): array {
		return [
			'added'         => [],
			'removed'       => [],
			'recategorized' => [],
			'domains_added' => [],
		];
	}

	/**
	 * Compute the change set between a previous and a current snapshot.
	 *
	 * @param array<string, mixed> $previous Previous scan snapshot.
	 * @param array<string, mixed> $current  Current scan snapshot.
	 * @since 1.2.0
	 * @return array{added: array<int, array<string, string>>, removed: array<int, array<string, string>>, recategorized: array<int, array<string, string>>, domains_added: array<int, string>}
	 */
	public static function diff( array $previous, array $current ): array {
		$prev_sig     = isset( $previous['signatures'] ) && is_array( $previous['signatures'] ) ? $previous['signatures'] : [];
		$curr_sig     = isset( $current['signatures'] ) && is_array( $current['signatures'] ) ? $current['signatures'] : [];
		$prev_domains = isset( $previous['domains'] ) && is_array( $previous['domains'] ) ? $previous['domains'] : [];
		$curr_domains = isset( $current['domains'] ) && is_array( $current['domains'] ) ? $current['domains'] : [];

		// A signature rotates when the cookie's TTL bucket does, so keying on it reported an
		// unchanged cookie as removed and added again. Compare on the store's identity; the
		// stored snapshot shape is untouched, so a snapshot written before this still diffs.
		$prev = self::by_identity( $prev_sig );
		$curr = self::by_identity( $curr_sig );

		$added         = [];
		$recategorized = [];

		foreach ( $curr as $identity => $data ) {
			if ( ! isset( $prev[ $identity ] ) ) {
				$added[] = $data;
				continue;
			}

			$old_category = (string) ( $prev[ $identity ]['category'] ?? '' );
			$new_category = (string) ( $data['category'] ?? '' );

			if ( $old_category !== $new_category ) {
				$recategorized[] = [
					'signature_id' => $data['signature_id'],
					'name'         => (string) ( $data['name'] ?? '' ),
					'domain'       => (string) ( $data['domain'] ?? '' ),
					'from'         => $old_category,
					'to'           => $new_category,
				];
			}
		}

		$removed = array_values( array_diff_key( $prev, $curr ) );

		return [
			'added'         => $added,
			'removed'       => $removed,
			'recategorized' => $recategorized,
			'domains_added' => array_values( array_diff( $curr_domains, $prev_domains ) ),
		];
	}

	/**
	 * Whether a computed diff contains any change.
	 *
	 * @param array<string, mixed> $diff Diff produced by self::diff().
	 * @since 1.2.0
	 * @return bool
	 */
	public static function has_changes( array $diff ): bool {
		return ! empty( $diff['added'] ) || ! empty( $diff['removed'] ) || ! empty( $diff['recategorized'] ) || ! empty( $diff['domains_added'] );
	}

	/**
	 * Index a snapshot's signature map by cookie identity, keeping each entry's id.
	 *
	 * @param array<string, mixed> $signatures Snapshot `signatures` map.
	 * @since 1.8.0
	 * @return array<string, array<string, string>>
	 */
	private static function by_identity( array $signatures ): array {
		$out = [];

		foreach ( $signatures as $signature => $data ) {
			if ( ! is_array( $data ) ) {
				continue;
			}

			$key   = Cookie_Identity::key_for( $data );
			$entry = array_merge( [ 'signature_id' => (string) $signature ], $data );

			// A snapshot from before the scan folded siblings can still hold several. Fold
			// them the way the store does: `uncategorized` is no verdict over a sibling's.
			if ( ( $entry['category'] ?? '' ) === 'uncategorized' && isset( $out[ $key ] ) ) {
				$entry['category'] = $out[ $key ]['category'];
			}

			$out[ $key ] = $entry;
		}

		return $out;
	}
}
