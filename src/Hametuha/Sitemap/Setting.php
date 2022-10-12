<?php

namespace Hametuha\Sitemap;


use Hametuha\Sitemap\Pattern\Singleton;

/**
 * Singleton pattern.
 *
 * @package hsm
 * @property-read bool     $disable_core
 * @property-read int      $posts_per_page
 * @property-read string[] $post_types
 * @property-read string[] $news_post_types
 * @property-read string[] $taxonomies
 * @property-read string   $attachment_sitemap
 */
class Setting extends Singleton {

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Register submenu.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page( 'tools.php', __( 'Sitemap', 'hsm' ), __( 'Sitemap', 'hsm' ), 'manage_options', 'hsm', [ $this, 'render_page' ] );
	}

	/**
	 * Render setting page.
	 *
	 * @return void
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sitemap Setting', 'hsm' ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				settings_fields( 'hsm' );
				do_settings_sections( 'hsm' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register setting fields.
	 *
	 * @return void
	 */
	public function add_settings() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		// Register sections.
		add_settings_section( 'hsm_setting_default', __( 'Setting', 'hsm' ), function() {

		}, 'hsm' );
		// Register setting.
		foreach ( [
			[
				'id' => 'disable_core',
				'title' => __( 'Core Sitemap', 'hsm' ),
				'type'  => 'bool',
				'label' => __( 'Disable core sitemap', 'hsm' ),
			],
			[
				'id' => 'post_types',
				'title' => __( 'Post types in Sitemap', 'hsm' ),
				'type'  => 'checkbox',
				'label' => __( 'Please check post type to be included in site map.', 'hsm' ),
				'options' => array_map( function( $post_type ) {
					return [
						'value' => $post_type->name,
						'label' => $post_type->label,
					];
				}, get_post_types( [ 'public' => true ], OBJECT ) ),
			],
			[
				'id'          => 'posts_per_page',
				'title'       => __( 'Posts per page', 'hsm' ),
				'type'        => 'number',
				'label'       => __( 'Number of posts per each sitemap. Should be under 5,000.', 'hsm' ),
				'placeholder' => '1000',
			],
			[
				'id' => 'attachment_sitemap',
				'title' => __( 'Attachment', 'hsm' ),
				'type'  => 'radio',
				'label' => __( 'How attachments appear in sitemap.', 'hsm' ),
				'options' => [
					[
						'value' => '',
						'label' => __( 'Not displayed.', 'hsm' )
					],
					[
						'value' => 'post',
						'label' => __( 'In post sitemap', 'hsm' )
					],
					[
						'value' => 'attachment',
						'label' => __( 'Create attachment page sitemap', 'hsm' )
					],
				],
			],
			[
				'id'    => 'news_post_types',
				'title' => __( 'Post types in news sitemap', 'hsm' ),
				'type'  => 'checkbox',
				'label' => __( 'Please check post type to be included in news site map.', 'hsm' ),
				'options' => array_map( function( $post_type ) {
					  return [
						  'value' => $post_type->name,
						  'label' => $post_type->label,
					  ];
				}, get_post_types( [ 'public' => true ], OBJECT ) ),
			],
			[
				'id'      => 'taxonomies',
				'title'   => __( 'Taxonomies in Sitemap', 'hsm' ),
				'type'    => 'checkbox',
				'label'   => __( 'Please check taxonomy archive in site map.', 'hsm' ),
				'options' => array_map( function( \WP_Taxonomy $taxonomy ) {
					return [
						'value' => $taxonomy->name,
						'label' => $taxonomy->label,
					];
				}, get_taxonomies( [ 'public' => true ], OBJECT ) ),
			],
		] as $setting ) {
			$id = 'hsm_' . $setting['id'];
			add_settings_field( $id, $setting['title'], function() use ( $id, $setting ) {
				$value = get_option( $id );
				switch ( $setting['type'] ) {
					case 'number':
					case 'text':
						printf(
							'<input type="%1$s" value="%2$s" name="%3$s" placeholder="%4$s" />',
							esc_attr( $setting['type'] ),
							esc_attr( $value ),
							esc_attr( $id ),
							esc_attr( $setting['placeholder'] ?? '' )
						);
						if ( ! empty( $setting['label'] ) ) {
							printf( '<p class="description">%s</p>', esc_html( $setting['label'] ) );
						}
						break;
					case 'bool':
						printf(
							'<label><input type="checkbox" name="%s" value="1" %s/>%s</label>',
							esc_attr( $id ),
							checked( $value, true, false ),
							esc_html( $setting['label'] )
						);
						break;
					case 'checkbox':
						$value = (array) $value;
						foreach ( $setting['options'] as $option ) {
							printf(
								'<label style="display: inline-block; margin-right: 1em;"><input type="checkbox" name="%1$s[]" value="%2$s" %3$s/> %4$s</label>',
								esc_attr( $id ),
								esc_attr( $option['value'] ),
								checked( in_array( $option['value'], $value, true ), true, false ),
								esc_html( $option['label'] )
							);
						}
						break;
					case 'radio':
						foreach ( $setting['options'] as $option ) {
							printf(
								'<label style="display: block; margin-bottom: 0.5em;"><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label>',
								esc_attr( $id ),
								esc_attr( $option['value'] ),
								checked( $option['value'], $value, false ),
								esc_html( $option['label'] )
							);
						}
						break;
				}
			}, 'hsm', 'hsm_setting_default' );

			register_setting( 'hsm', $id );
		}
	}

	/**
	 * Get property
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'post_types':
			case 'news_post_types':
			case 'taxonomies':
				return array_values( array_filter( (array) get_option( 'hsm_' . $name, [] ) ) );
			case 'disable_core':
				return (bool) get_option( 'hsm_' . $name );
			case 'posts_per_page':
				return min( 5000, (int) get_option( 'hsm_' . $name, 1000 ) ) ?: 1000;
			case 'attachment_sitemap':
				return get_option( 'hsm_' . $name );
			default:
				return null;
		}
	}
}
