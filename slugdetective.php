<?php

/**
 * @SlugDetective
 *
 * Plugin Name:       SlugDetective
 * Description:       Search posts and pages by slug in the admin area.
 * Version:           1.0.1
 * Author:            Dragi Postolovski
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
require plugin_dir_path( __FILE__ ) . 'includes/Primary.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */

$plugin = new codinginzen\Primary();
$plugin->run();