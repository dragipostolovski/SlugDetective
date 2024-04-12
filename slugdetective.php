<?php

/**
 * @search-informant
 *
 * Plugin Name:       SlugDetective
 * Plugin URI:        https://projectsengine.com/plugin/slug-detective
 * Description:       Search posts and pages by slug in the admin area.
 * Version:           1.0.0
 * Author:            Projects Engine
 * Author URI:        https://projectsengine.com/user/dragipostolovski
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       slugdetective
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The core plugin class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/SlugDetective.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */

$plugin = new projectsengine\SlugDetective();
$plugin->run();