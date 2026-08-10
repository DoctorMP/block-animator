<?php
/**
 * Settings page.
 *
 * Registers the "Block Animator" options page under Settings, where an
 * administrator sets the site-wide defaults used whenever a block is
 * left on "Default" — separately for scroll-triggered animations and
 * for repeated (attention seeker) animations.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * @since 0.1.0
 */
class Settings implements Registrable {

	/**
	 * Option name under which all settings are stored as a single array.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const OPTION_NAME = 'block_animator_settings';

	/**
	 * Settings page / option group slug.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const PAGE_SLUG = 'block-animator';

	/**
	 * {@inheritDoc}
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the settings page under Settings → Block Animator.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Block Animator', 'block-animator' ),
			__( 'Block Animator', 'block-animator' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the setting and its two sections/field sets.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'block_animator_scroll_defaults',
			__( 'Scroll-Triggered Animation Defaults', 'block-animator' ),
			array( $this, 'render_scroll_section_description' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'default_animation',
			__( 'Default animation', 'block-animator' ),
			array( $this, 'render_animation_field' ),
			self::PAGE_SLUG,
			'block_animator_scroll_defaults'
		);

		add_settings_field(
			'default_duration',
			__( 'Default duration (ms)', 'block-animator' ),
			array( $this, 'render_duration_field' ),
			self::PAGE_SLUG,
			'block_animator_scroll_defaults'
		);

		add_settings_field(
			'default_delay',
			__( 'Default delay (ms)', 'block-animator' ),
			array( $this, 'render_delay_field' ),
			self::PAGE_SLUG,
			'block_animator_scroll_defaults'
		);

		add_settings_field(
			'default_timing_function',
			__( 'Default timing function', 'block-animator' ),
			array( $this, 'render_timing_field' ),
			self::PAGE_SLUG,
			'block_animator_scroll_defaults'
		);

		add_settings_section(
			'block_animator_repeat_defaults',
			__( 'Repeated Animation Defaults', 'block-animator' ),
			array( $this, 'render_repeat_section_description' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'default_repeat_animation',
			__( 'Default animation', 'block-animator' ),
			array( $this, 'render_repeat_animation_field' ),
			self::PAGE_SLUG,
			'block_animator_repeat_defaults'
		);

		add_settings_field(
			'default_repeat_duration',
			__( 'Default duration (ms)', 'block-animator' ),
			array( $this, 'render_repeat_duration_field' ),
			self::PAGE_SLUG,
			'block_animator_repeat_defaults'
		);

		add_settings_field(
			'default_repeat_interval',
			__( 'Default interval (ms)', 'block-animator' ),
			array( $this, 'render_repeat_interval_field' ),
			self::PAGE_SLUG,
			'block_animator_repeat_defaults'
		);
	}

	/**
	 * Render the scroll-defaults section description.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_scroll_section_description(): void {
		esc_html_e(
			'Used whenever a scroll-triggered block\'s own animation, duration, delay, or timing function is left on "Default".',
			'block-animator'
		);
	}

	/**
	 * Render the repeat-defaults section description.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_repeat_section_description(): void {
		esc_html_e(
			'Used whenever a repeated-animation block\'s own animation, duration, or interval is left on "Default".',
			'block-animator'
		);
	}

	/**
	 * Render the settings page shell.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Block Animator', 'block-animator' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the default scroll-animation select field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_animation_field(): void {
		$current = self::get_options()['default_animation'];
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_animation]">
			<?php foreach ( Animations::choices_for_site_default( Animations::CATEGORY_SCROLL ) as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the default-duration number field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_duration_field(): void {
		$current = self::get_options()['default_duration'];
		?>
		<input
			type="number"
			min="0"
			step="1"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_duration]"
			value="<?php echo esc_attr( (string) $current ); ?>"
		/>
		<?php
	}

	/**
	 * Render the default-delay number field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_delay_field(): void {
		$current = self::get_options()['default_delay'];
		?>
		<input
			type="number"
			min="0"
			step="1"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_delay]"
			value="<?php echo esc_attr( (string) $current ); ?>"
		/>
		<?php
	}

	/**
	 * Render the default-timing-function select field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_timing_field(): void {
		$current = self::get_options()['default_timing_function'];
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_timing_function]">
			<?php foreach ( Timing_Functions::choices_for_site_default() as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the default repeat-animation select field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_repeat_animation_field(): void {
		$current = self::get_options()['default_repeat_animation'];
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_repeat_animation]">
			<?php foreach ( Animations::choices_for_site_default( Animations::CATEGORY_ATTENTION ) as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render the default repeat-duration number field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_repeat_duration_field(): void {
		$current = self::get_options()['default_repeat_duration'];
		?>
		<input
			type="number"
			min="0"
			step="1"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_repeat_duration]"
			value="<?php echo esc_attr( (string) $current ); ?>"
		/>
		<?php
	}

	/**
	 * Render the default repeat-interval number field.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function render_repeat_interval_field(): void {
		$current = self::get_options()['default_repeat_interval'];
		?>
		<input
			type="number"
			min="0"
			step="1"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_repeat_interval]"
			value="<?php echo esc_attr( (string) $current ); ?>"
		/>
		<?php
	}

	/**
	 * Sanitize submitted settings before they're saved.
	 *
	 * @since  0.1.0
	 * @param  mixed $input Raw submitted value.
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ): array {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();

		$animation = isset( $input['default_animation'] ) ? sanitize_key( (string) $input['default_animation'] ) : $defaults['default_animation'];
		if ( Animations::DEFAULT_SENTINEL === $animation || ! Animations::is_valid( $animation, Animations::CATEGORY_SCROLL ) ) {
			$animation = $defaults['default_animation'];
		}

		$timing = isset( $input['default_timing_function'] ) ? sanitize_key( (string) $input['default_timing_function'] ) : $defaults['default_timing_function'];
		if ( Timing_Functions::DEFAULT_SENTINEL === $timing || ! Timing_Functions::is_valid( $timing ) ) {
			$timing = $defaults['default_timing_function'];
		}

		$repeat_animation = isset( $input['default_repeat_animation'] ) ? sanitize_key( (string) $input['default_repeat_animation'] ) : $defaults['default_repeat_animation'];
		if ( Animations::DEFAULT_SENTINEL === $repeat_animation || ! Animations::is_valid( $repeat_animation, Animations::CATEGORY_ATTENTION ) ) {
			$repeat_animation = $defaults['default_repeat_animation'];
		}

		return array(
			'default_animation'        => $animation,
			'default_duration'         => isset( $input['default_duration'] ) ? absint( $input['default_duration'] ) : $defaults['default_duration'],
			'default_delay'            => isset( $input['default_delay'] ) ? absint( $input['default_delay'] ) : $defaults['default_delay'],
			'default_timing_function'  => $timing,
			'default_repeat_animation' => $repeat_animation,
			'default_repeat_duration'  => isset( $input['default_repeat_duration'] ) ? absint( $input['default_repeat_duration'] ) : $defaults['default_repeat_duration'],
			'default_repeat_interval'  => isset( $input['default_repeat_interval'] ) ? absint( $input['default_repeat_interval'] ) : $defaults['default_repeat_interval'],
		);
	}

	/**
	 * Hardcoded fallback values used until an administrator saves the
	 * settings page for the first time.
	 *
	 * @since  0.1.0
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'default_animation'        => Animations::NONE,
			'default_duration'         => 600,
			'default_delay'            => 0,
			'default_timing_function'  => 'ease',
			'default_repeat_animation' => Animations::NONE,
			'default_repeat_duration'  => 1000,
			'default_repeat_interval'  => 2000,
		);
	}

	/**
	 * Saved settings merged with fallback defaults.
	 *
	 * @since  0.1.0
	 * @return array<string, mixed>
	 */
	public static function get_options(): array {
		static $options = null;

		if ( null === $options ) {
			$saved   = get_option( self::OPTION_NAME, array() );
			$options = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		}

		return $options;
	}

	/**
	 * Resolved site-wide default scroll-animation slug.
	 *
	 * @since  0.1.0
	 * @return string
	 */
	public static function get_default_animation(): string {
		$value = self::get_options()['default_animation'];

		return ( Animations::is_valid( $value, Animations::CATEGORY_SCROLL ) && Animations::DEFAULT_SENTINEL !== $value ) ? $value : Animations::NONE;
	}

	/**
	 * Resolved site-wide default duration, in milliseconds.
	 *
	 * @since  0.1.0
	 * @return int
	 */
	public static function get_default_duration(): int {
		return max( 0, (int) self::get_options()['default_duration'] );
	}

	/**
	 * Resolved site-wide default delay, in milliseconds.
	 *
	 * @since  0.1.0
	 * @return int
	 */
	public static function get_default_delay(): int {
		return max( 0, (int) self::get_options()['default_delay'] );
	}

	/**
	 * Resolved site-wide default timing function.
	 *
	 * @since  0.1.0
	 * @return string
	 */
	public static function get_default_timing_function(): string {
		$value = self::get_options()['default_timing_function'];

		return ( Timing_Functions::is_valid( $value ) && Timing_Functions::DEFAULT_SENTINEL !== $value ) ? $value : 'ease';
	}

	/**
	 * Resolved site-wide default repeat-animation slug.
	 *
	 * @since  0.1.0
	 * @return string
	 */
	public static function get_default_repeat_animation(): string {
		$value = self::get_options()['default_repeat_animation'];

		return ( Animations::is_valid( $value, Animations::CATEGORY_ATTENTION ) && Animations::DEFAULT_SENTINEL !== $value ) ? $value : Animations::NONE;
	}

	/**
	 * Resolved site-wide default repeat duration, in milliseconds.
	 *
	 * @since  0.1.0
	 * @return int
	 */
	public static function get_default_repeat_duration(): int {
		return max( 0, (int) self::get_options()['default_repeat_duration'] );
	}

	/**
	 * Resolved site-wide default repeat interval, in milliseconds.
	 *
	 * @since  0.1.0
	 * @return int
	 */
	public static function get_default_repeat_interval(): int {
		return max( 0, (int) self::get_options()['default_repeat_interval'] );
	}
}
