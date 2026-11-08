<?php
/**
 * Plugin Name: Animora: Animations for Blocks
 * Plugin URI: https://michal-pawelczyk.net/projects/animora-animacje-dla-blokow/
 * Description: Adds scroll-triggered CSS animations to any block in the block editor.
 * Version: 0.1.0
 * Author: Michał Pawelczyk
 * Author URI: https://michal-pawelczyk.net
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: animora
 * Domain Path: /languages
 *
 * @package Animora
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'ANIMORA_PLUGIN_FILE', __FILE__ );
define( 'ANIMORA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANIMORA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ANIMORA_PLUGIN_PATH . 'inc/interface-registrable.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-animations.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-timing-functions.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-settings.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-block-attributes.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-editor-assets.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-frontend-assets.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-block-renderer.php';
require_once ANIMORA_PLUGIN_PATH . 'inc/class-bootstrap.php';

( new Animora\Bootstrap(
	array(
		new Animora\Settings(),
		new Animora\Block_Attributes(),
		new Animora\Editor_Assets(),
		new Animora\Frontend_Assets(),
		new Animora\Block_Renderer(),
	)
) )->boot();
