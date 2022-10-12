<?php
/*
Plugin Name:Hametuha Sitemap
Plugin URI: https://wordpress.org/extend/plugins/hamelp
Description: Yet another sitemap plugin with more than 200,000 posts.
Version: nightly
Author: Hametuha INC
Author URI: https://hametuah.co.jp
Text Domain: hsm
Domain Path: /languages
License: GPL3 or Later
*/

/**
 * @package hsm
 */

// Do not load directory.
defined( 'ABSPATH' ) || die();

/**
 * Initialize plugin.
 *
 * @return void
 */
function hsm_init() {
	load_plugin_textdomain( 'hsm', false, basename( __DIR__ ) . '/languages' );
	require __DIR__ . '/vendor/autoload.php';
	// Root Controllers.
	\Hametuha\Sitemap\Setting::get_instance();
	\Hametuha\Sitemap\Registry::get_instance();
	// Posts sitemap.
	\Hametuha\Sitemap\Provider\PostSitemapIndexProvider::get_instance();
	\Hametuha\Sitemap\Provider\PostSitemapProvider::get_instance();
	// News sitemaps.
	\Hametuha\Sitemap\Provider\NewsSitemapIndexProvider::get_instance();
	\Hametuha\Sitemap\Provider\NewsSitemapProvider::get_instance();
	// Attachment sitemap.
	\Hametuha\Sitemap\Provider\AttachmentSitemapIndexProvider::get_instance();
	\Hametuha\Sitemap\Provider\AttachmentSitemapProvider::get_instance();
	// Taxonomy sitemap.
	\Hametuha\Sitemap\Provider\TaxonomySitemapIndexProvider::get_instance();
	\Hametuha\Sitemap\Provider\TaxonomySitemapProvider::get_instance();
	// Sitemap style
	\Hametuha\Sitemap\Styles\SitemapIndexStyle::get_instance();
	\Hametuha\Sitemap\Styles\SitemapStyle::get_instance();
	\Hametuha\Sitemap\Styles\NewsStyle::get_instance();

	add_action( 'init', 'hsm_register_assets' );
}

// Register hook.
add_action( 'plugins_loaded', 'hsm_init' );

/**
 * Register assets.
 *
 * @return void
 */
function hsm_register_assets() {
	$json = __DIR__ . '/wp-dependencies.json';
	if ( ! file_exists( $json ) ) {
		return;
	}
	$settings = json_decode( file_get_contents( $json ), true );
	if ( empty( $settings ) ) {
		return;
	}
	foreach ( $settings as $setting ) {
		if ( empty( $setting['path'] ) ) {
			continue;
		}
		$url = plugins_url( $setting['path'], __FILE__ );
		switch ( $setting['ext'] ) {
			case 'js':
				wp_register_script( $setting['handle'], $url, $setting['deps'], $setting['hash'], $setting['footer'] );
				break;
			case 'css':
				wp_register_style( $setting['handle'], $url, $setting['deps'], $setting['hash'], $setting['media'] );
				break;
		}
	}
}
