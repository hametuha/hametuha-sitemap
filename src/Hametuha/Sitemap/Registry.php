<?php

namespace Hametuha\Sitemap;


use Hametuha\Sitemap\Pattern\Singleton;
use Hametuha\Sitemap\Pattern\SitemapIndexProvider;
use Hametuha\Sitemap\Utility\OptionAccessor;

/**
 * Sitemap registry.
 *
 * @package hsm
 */
class Registry extends Singleton {

	use OptionAccessor;

	/**
	 * @return void
	 */
	protected function init() {
		add_filter( 'wp_sitemaps_enabled', [ $this, 'disable_sitemap' ] );
		add_filter( 'rewrite_rules_array', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	/**
	 * Disabled sitemap
	 *
	 * @param bool $enabled Is sitemap enabled?
	 * @return bool
	 */
	public function disable_sitemap( $enabled ) {
		return ! $this->option()->disable_core;
	}

	/**
	 * Get sitemap indices.
	 *
	 * @return string[]
	 */
	public function get_sitemap_indices() {
		return apply_filters( 'hsm_sitemap_indices', [] );
	}

	/**
	 * Add query vars.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function query_vars( $vars ) {
		$vars[] = 'sitemap_type';
		$vars[] = 'sitemap_target';
		return $vars;
	}

	/**
	 * Get rewrite rules.
	 *
	 * @return array
	 */
	public function add_rewrite_rules( $rules ) {
		return array_merge( [
			'^sitemap_index_([^/]+)\.xml$'                => 'index.php?sitemap_type=index&sitemap_target=$matches[1]',
			'^sitemap_([^/]+)_(\d{4})(\d{2})_(\d+)\.xml$' => 'index.php?sitemap_type=map&sitemap_target=$matches[1]&year=$matches[2]&monthnum=$matches[3]&paged=$matches[4]',
			'^sitemap_news_(\d+)\.xml$'                   => 'index.php?sitemap_type=news&sitemap_target=news&paged=$matches[1]',
			'^sitemap_taxonomy_(\d+)\.xml$'               => 'index.php?sitemap_type=map&sitemap_target=taxonomy&paged=$matches[1]',
			'^sitemap_style_([^/]+)\.xsl$'                => 'index.php?sitemap_type=sitemap_style&sitemap_target=$matches[1]',
		], $rules );
	}

	/**
	 * Hijack main query to render XML sitemap.
	 *
	 * @param \WP_Query $wp_query
	 * @return void
	 */
	public function pre_get_posts( $wp_query ) {
		if ( ! $wp_query->is_main_query() ) {
			return;
		}
		$sitemap = $wp_query->get( 'sitemap_type' );
		if ( ! $sitemap ) {
			// This is not sitemap request.
			return;
		}
		do_action( 'hsm_do_sitemap_' . $sitemap, $wp_query->get( 'sitemap_target' ) );
	}
}
