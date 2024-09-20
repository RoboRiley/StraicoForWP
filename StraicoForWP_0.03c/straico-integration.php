<?php
/*
Plugin Name: Straico Integration
Description: Integrates Straico API to generate content for posts or pages.
Version: 1.0
Author: Your Name
*/

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'STRAICO_INTEGRATION_VERSION', '1.0' );
define( 'STRAICO_INTEGRATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files
require_once( STRAICO_INTEGRATION_PLUGIN_DIR . 'includes/admin-page.php' );
require_once( STRAICO_INTEGRATION_PLUGIN_DIR . 'includes/api-functions.php' );
require_once( STRAICO_INTEGRATION_PLUGIN_DIR . 'includes/settings-page.php' );
require_once( STRAICO_INTEGRATION_PLUGIN_DIR . 'includes/Parsedown.php' );

?>