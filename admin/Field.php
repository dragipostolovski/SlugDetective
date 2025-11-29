<?php

namespace codinginzen;

/**
 * Adds a "Slug" column to post and page list tables in the admin.
 * The column appears directly after the Title column and is sortable.
 */
class Field {

	/**
	 * @var string
	 */
	private $plugin_name;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * Set the plugin name and version.
	 *
	 * @param string $plugin_name
	 * @param string $version
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public function run() {

		// Add the Slug column after Title.
		add_filter( 'manage_posts_columns', [ $this, 'add_slug_column' ] );
		add_filter( 'manage_pages_columns', [ $this, 'add_slug_column' ] );

		// Render the Slug column values.
		add_action( 'manage_posts_custom_column', [ $this, 'render_slug_column' ], 10, 2 );
		add_action( 'manage_pages_custom_column', [ $this, 'render_slug_column' ], 10, 2 );

		// Make the Slug column sortable.
		add_filter( 'manage_edit-post_sortable_columns', [ $this, 'make_slug_sortable' ] );
		add_filter( 'manage_edit-page_sortable_columns', [ $this, 'make_slug_sortable' ] );

		// Adjust query when sorting by slug.
		add_action( 'pre_get_posts', [ $this, 'sort_by_slug' ] );
	}

	/**
	 * Insert the Slug column directly after the Title column.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_slug_column( $columns ) {
		$updated = [];

		foreach ( $columns as $key => $label ) {
			$updated[ $key ] = $label;

			if ( $key === 'title' ) {
				$updated['post_slug'] = 'Slug';
			}
		}

		return $updated;
	}

	/**
	 * Display the slug value in the Slug column.
	 *
	 * @param string $column
	 * @param int    $post_id
	 * @return void
	 */
	public function render_slug_column( $column, $post_id ) {
		if ( $column === 'post_slug' ) {
			$slug = get_post_field( 'post_name', $post_id );
			echo esc_html( $slug );
		}
	}

	/**
	 * Mark the Slug column as sortable.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function make_slug_sortable( $columns ) {
		$columns['post_slug'] = 'post_slug';
		return $columns;
	}

	/**
	 * Modify the sorting behavior so sorting by slug is handled correctly.
	 *
	 * @param \WP_Query $query
	 * @return void
	 */
	public function sort_by_slug( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->get( 'orderby' ) === 'post_slug' ) {
			$query->set( 'orderby', 'name' );
		}
	}
}