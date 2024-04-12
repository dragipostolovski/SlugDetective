<?php

namespace projectsengine;

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
class SlugDetectiveAdmin {

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
//	    add_action( 'load-edit.php', array( $this, 'load_edit_php_action' ), 10, 2 );
	    add_filter( 'acf/fields/relationship/query', array( $this, 'modify_fields_relationship_query' ), 10, 3);
	    add_filter( 'pre_get_posts', array( $this, 'modify_pre_get_posts' ), 10, 1 );
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
			$posts = array_column( $this->get_posts_by_post_name( $query->query_vars['s'] ), 'ID' );

			$query->set( 'post_type', $query->query_vars['post_type'] );
			$query->set( 'post__in', $posts );
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
	 * Modify the query.
	 *
	 * @return void
	 */
	function load_edit_php_action() {
		add_action( 'posts_where', array( $this, 'search_posts_by_slug' ), 10, 2 );
	}

	/**
	 * Query the posts based on the new filter.
	 *
	 * @param $where
	 * @param $q
	 *
	 * @return mixed
	 */
	public function search_posts_by_slug( $where, $q ) {
		global $pagenow;
		global $wpdb;

		if( 'edit.php' == $pagenow && isset( $_GET['s'] ) && '' !== $_GET['s'] && strpos( $_GET['s'], 'slug:' ) !== false ) {
			$s = str_replace('slug:', '', $q->query_vars['s'] );
			$type = $q->query_vars['post_type'];
			$where = " AND $wpdb->posts.post_name LIKE '%" . $s . "%' AND $wpdb->posts.post_type = '".$type."'";
		}

		return $where;
	}

	/**
	 * Get the posts by post name.
	 *
	 * @return array|object|stdClass[]
	 */
	public function get_posts_by_post_name( $s ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name LIKE '%" . str_replace( 'slug:', '', $s ) . "%' " )
		);
	}
}
