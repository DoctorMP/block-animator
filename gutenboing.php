<?php
/**
 * Plugin Name: GutenBoing: Animations for Gutenberg Blocks
 * Plugin URI: https://michal-pawelczyk.net/wp-plugins/gutenboing
 * Description: Adds scroll-triggered CSS animations to any block in the block editor.
 * Version: 0.1.0
 * Author: Michał Pawelczyk
 * Author URI: https://michal-pawelczyk.net
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gutenboing
 * Domain Path: /languages
 *
 * @package GutenBoing
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'GUTENBOING_PLUGIN_FILE', __FILE__ );
define( 'GUTENBOING_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GUTENBOING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GUTENBOING_PLUGIN_PATH . 'inc/interface-registrable.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-animations.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-timing-functions.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-settings.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-block-attributes.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-editor-assets.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-frontend-assets.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-block-renderer.php';
require_once GUTENBOING_PLUGIN_PATH . 'inc/class-bootstrap.php';

( new GutenBoing\Bootstrap(
	array(
		new GutenBoing\Settings(),
		new GutenBoing\Block_Attributes(),
		new GutenBoing\Editor_Assets(),
		new GutenBoing\Frontend_Assets(),
		new GutenBoing\Block_Renderer(),
	)
) )->boot();
