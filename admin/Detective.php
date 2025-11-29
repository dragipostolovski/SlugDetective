<?php

namespace codinginzen;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    Search_Informant
 * @subpackage Search_Informant/admin
 * @author     Dragi Postolovski <dpostolovskimk@gmail.com>
 */
class Detective {

	/**
	 * The plugin's unique identifier.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The plugin's unique identifier.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $original_search_term = '';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->run();
	}

	/**
     * Run the admin area.
     *
	 * @return void
	 */
    public function run() {
	    add_filter( 'acf/fields/relationship/query', [ $this, 'modify_fields_relationship_query' ], 10, 3);
	    add_filter( 'pre_get_posts', [ $this, 'modify_pre_get_posts' ], 10, 1 );
		add_filter( 'get_search_query', [ $this, 'display_clean_search_query' ] );
    }

	/**
	 * Search in ACF field Relationship.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function modify_pre_get_posts( $query ) {
		if ( is_admin() && strpos( $query->query_vars['s'], 'slug:' ) !== false ) {

			// Save the original search term for display
        	$this->original_search_term = $query->query_vars['s'];

			$posts = array_column( $this->get_posts_by_post_name( $query->query_vars['s'] ), 'ID' );

			$query->set( 'post_type', $query->query_vars['post_type'] );
			$query->set( 'post__in', $posts );
			
			// REMOVE this line — it clears the search term
        	$query->set( 's', '' );
		}

		return $query;
	}

	/**
	 * Modify the relationship custom field.
	 *
	 * @param $args
	 * @param $field
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function modify_fields_relationship_query( $args, $field, $post_id ) {
	    $s = $args['s'];

		if( strpos( $s, 'slug:' ) !== false  ) {
			$posts = array_column( $this->get_posts_by_post_name( $s ), 'ID' );

			$args['post__in'] = $posts;
			$args['s'] = '';
		}

	    return $args;
	}

	/**
	 * Get posts by their post_name, allowing exact or partial matching.
	 *
	 * Usage:
	 *   slug:my-slug       → exact match (post_name = 'my-slug')
	 *   slug:my-slug:like  → partial match (post_name LIKE '%my-slug%')
	 *
	 * @param string $s The search string that starts with "slug:" and may end with ":like".
	 * @return array Always returns an array of results (possibly empty).
	 */
	public function get_posts_by_post_name(string $s): array {
		global $wpdb;

		// Remove the leading "slug:" prefix.
		$slug = str_replace('slug:', '', $s);

		// Check if :like mode is used.
		$use_like = false;
		if (strpos($slug, ':like') !== false) {
			$use_like = true;
			$slug = str_replace(':like', '', $slug);
		}

		if ($use_like) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_name LIKE %s",
					'%' . $wpdb->esc_like($slug) . '%'
				)
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s",
					trim($slug)
				)
			);

			var_dump($results);
		}

		return is_array($results) ? $results : [];
	}

	/**
	 * Return a descriptive search query for the admin search subtitle.
	 *
	 * @param string $search Original search string.
	 * @return string Clean, descriptive string.
	 */
	public function display_clean_search_query( $search ) {
		if ( !empty( $this->original_search_term ) ) {
			$slug = str_replace( 'slug:', '', $this->original_search_term );

			if ( strpos( $slug, ':like' ) !== false ) {
				$slug = str_replace( ':like', '', $slug );
				return sprintf( '%s (partial match)', trim( $slug ) );
			}

			return sprintf( '%s (exact match)', trim( $slug ) );
		}

		// Normal search
		return $search;
	}
}