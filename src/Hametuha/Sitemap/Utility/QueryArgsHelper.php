<?php

namespace Hametuha\Sitemap\Utility;


/**
 * Get query args.
 */
trait QueryArgsHelper {

	use OptionAccessor;

	/**
	 * Get query arguments.
	 *
	 * @param string $type Hook type.
	 * @param array  $args Arguments to override.
	 * @return array
	 */
	protected function news_query_args( $type, $args = [] ) {
		return apply_filters( 'hms_news_sitemap_query_args', array_merge( [
			'post_status'         => 'publish',
			'post_type'           => $this->option()->news_post_types,
			'orderby'             => [ 'date' => 'DESC' ],
			'posts_per_page'      => $this->default_news_per_page(),
			"ignore_sticky_posts" => true,
			'date_query'          => [
				[
					'after'     => '48 hours ago',
					'inclusive' => true,
				],
			],
		], $args ), $type );
	}

	/**
	 * News posts per page.
	 *
	 * @return int
	 */
	protected function default_news_per_page() {
		return min( 1000, (int) apply_filters( 'hsm_news_sitemap_per_page', 1000 ) );
	}

	/**
	 * Get lastmod.
	 *
	 * @return string
	 */
	protected function get_last_mod( $post_date ) {
		return mysql2date( \DateTime::W3C, $post_date );
	}

	/**
	 * Get news name.
	 *
	 * @return string
	 */
	public function news_name() {
		static $news_name = '';
		if ( empty( $news_name ) ) {
			$news_name = apply_filters( 'hsm_news_name', get_bloginfo( 'title' ) );
		}
		return $news_name;
	}

	/**
	 * Get default locale.
	 *
	 * @return string
	 */
	public function get_site_lang() {
		static $default_locale = '';
		if ( empty( $default_locale ) ) {
			$locale = array_map( 'strtolower', explode( '_', get_locale() ) );
			if ( 'zh' === $locale[0] ) {
				$default_locale = implode( '-', $locale );
			} else {
				$default_locale = $locale[0];
			}
		}
		return $default_locale;
	}
}
