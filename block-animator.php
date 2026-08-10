<?php
/**
 * Plugin Name: Block Animator
 * Plugin URI: https://michal-pawelczyk.net/wp-plugins/block-animator
 * Description: Adds scroll-triggered CSS animations to any block in the block editor.
 * Version: 0.1.0
 * Author: Michał Pawelczyk
 * Author URI: https://michal-pawelczyk.net
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: block-animator
 * Domain Path: /languages
 *
 * @package Block Animator
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'BLOCK_ANIMATOR_PLUGIN_FILE', __FILE__ );
define( 'BLOCK_ANIMATOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BLOCK_ANIMATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/interface-registrable.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-animations.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-timing-functions.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-settings.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-block-attributes.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-editor-assets.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-frontend-assets.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-block-renderer.php';
require_once BLOCK_ANIMATOR_PLUGIN_PATH . 'inc/class-bootstrap.php';

( new BlockAnimator\Bootstrap(
	array(
		new BlockAnimator\Settings(),
		new BlockAnimator\Block_Attributes(),
		new BlockAnimator\Editor_Assets(),
		new BlockAnimator\Frontend_Assets(),
		new BlockAnimator\Block_Renderer(),
	)
) )->boot();
