<?php
/**
 * Plugin Name: Before After Image Comparison - Block
 * Description: Compare and filter between two images
 * Version: 1.1.7
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: image-compare
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

// Constant
define( 'ICB_PLUGIN_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.1.7' );
define( 'ICB_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'ICB_DIR_PATH', plugin_dir_path( __FILE__ ) );

require_once ICB_DIR_PATH . 'inc/block.php';