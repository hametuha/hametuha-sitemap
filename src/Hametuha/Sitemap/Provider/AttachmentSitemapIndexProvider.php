<?php

namespace Hametuha\Sitemap\Provider;

use Hametuha\Sitemap\Pattern\SitemapIndexProvider;

/**
 * Sitemap index for posts.
 */
class AttachmentSitemapIndexProvider extends SitemapIndexProvider {

	/**
	 * {@inheritdoc}
	 */
	protected function target_name() {
		return 'attachment';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active() {
		return 'attachment' === $this->option()->attachment_sitemap;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_urls() {
		global $wpdb;
		$query = <<<SQL
			SELECT
			    EXTRACT( YEAR_MONTH from p1.post_date ) as date,
			    COUNT( p1.ID ) AS total
			FROM {$wpdb->posts} AS p1
			LEFT JOIN {$wpdb->posts} AS p2
			ON p1.post_parent = p2.ID
			WHERE p1.post_type = 'attachment'
			  AND p1.post_mime_type LIKE 'image%'
			  AND p2.post_status = 'publish'
			GROUP BY EXTRACT( YEAR_MONTH from p1.post_date )
SQL;
		$urls = [];
		foreach ( $wpdb->get_results( $query ) as $row ) {
			$pages = ceil( $row->total / $this->option()->posts_per_page );
			for ( $i = 1; $i <= $pages; $i++ ) {
				$urls[] = home_url( sprintf( 'sitemap_attachment_%06d_%d.xml', $row->date, $i ) );
			}
		}
		return $urls;
	}
}