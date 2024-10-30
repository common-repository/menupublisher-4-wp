<?php
	/*
	Plugin Name: MenuPublisher 4 WP
	Plugin URI: https://www.menupublisher.de/index.php?page=wp_plugin
	Description: This plugin renders lunch or menus from a restaurant on MenuPublisher.de directly on your website
	Version: 1.3
	Author: Medienmacher GmbH
	Author URI: https://medienmacher.de/
	Text Domain: menupublisher-4-wp
	 */

	defined('ABSPATH') or die('No script kiddies please!');

	class menupublisher_4_wp {
		static $mmlt_templates_included  = '';
		static $lang_code                = null;
		static $constants                = array(
			'ADMIN_MENU_SLUG'       => 'menupublisher_4_wp_options',
			'AUTHOR'                => 'Medienmacher GmbH',
			'AUTHOR_URL'            => 'http://medienmacher.de',
			'DEFAULT_LANG_CODE'     => 'de',
			'LAYOUT_1_COLUMN'       => 'mmlt1Column',
			'LAYOUT_2_COLUMNS'      => 'mmlt2Columns',
			'MMLT_MAIN_OPTION_NAME' => 'menupublisher-4-wp-settings',
			'PLUGIN_NAME'           => 'MenuPublisher 4 WP',
			'PLUGIN_SLUG'           => 'menupublisher_4_wp',
			'SHORT_CODE_NAME'       => 'menupublisher-4-wp',

			// WordPress translation docs advise:
			// DO NOT USE this constant inside the __() functions
			// this constant is only for lookup
			'TEXT_DOMAIN'           => 'menupublisher-4-wp',
			'TMCE_PLUGIN_NAME'      => 'menupublisher_4_wp_plugin',
			'VERSION'               => '1.0',
		);

		/* locales on MenuPublisher.de use 2-characters, while wordpress uses
		 * a combination <language key>_<country key>
		 *
		 * the following list contains all languages supported by MenuPublisher.de as values
		 * that are mapped to wordpress languages
		 *
		 * there is no use extending this list with new language values as long as
		 * MenuPublisher.de does not support them.
		 */
		static $locales_mappings = array(
			'de'    => 'de',
			'de_DE' => 'de',
			'en'    => 'en',
			'en_GB' => 'en',
			'en_US' => 'en',
			'es'    => 'es',
			'es_ES' => 'es',
			'fr'    => 'fr',
			'fr_FR' => 'fr',
			'it'    => 'it',
			'it_IT' => 'it',
			'ru'    => 'ru',
			'ru_RU' => 'ru',
		);

		public static function init() {
			if ( self::$lang_code == null ) {
				$locale = get_locale();
				if ( isset( self::$locales_mappings[ $locale ] ) ) {
					self::$lang_code = self::$locales_mappings[ $locale ];
				} else {
					self::$lang_code = self::$constants['DEFAULT_LANG_CODE'];
				}
			}

			self::$constants['TMCE_PLUGIN_BTN_TEXT'] = __( 'Insert InlineCode', 'menupublisher-4-wp' );

			self::register_script_style();

			// trailing slash after languages is here IMPORTANT
			load_plugin_textdomain( 'menupublisher-4-wp', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			add_action(
				'wp_enqueue_scripts',
				array( self::$constants['PLUGIN_SLUG'], 'enqueue_fe_scripts' )
			);
			add_shortcode(
				self::$constants['SHORT_CODE_NAME'],
				array( self::$constants['PLUGIN_SLUG'], 'inline_short_code' )
			);
			add_action(
				'admin_enqueue_scripts',
				array( self::$constants['PLUGIN_SLUG'], 'admin_enqueue_scripts' )
			);
			add_action(
				'admin_menu',
				array( self::$constants['PLUGIN_SLUG'], 'admin_menu' )
			);

			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
				return;
			}

			$settings = get_option( self::$constants['MMLT_MAIN_OPTION_NAME'] );
			if (
				! is_array($settings) ||
				! is_array($settings['aSnippets'])
			) {
				return;
			}

			// Add only in Rich Editor mode
	    if ( get_user_option('rich_editing') == 'true' ) {
	      add_filter( 'mce_external_plugins', array( self::$constants['PLUGIN_SLUG'], 'register_tmce_plugin' ) );
	      //you can use the filters mce_buttons_2, mce_buttons_3 and mce_buttons_4
	      //to add your button to other toolbars of your tinymce
	      add_filter('mce_buttons', array( self::$constants['PLUGIN_SLUG'], 'register_buttons'));
	    }
		}//public static function init()

		public static function register_tmce_plugin( $plugin_array ) {
			$settings = get_option( self::$constants['MMLT_MAIN_OPTION_NAME'] );
			?>
				<script type="text/javascript">
					var mmlt_data = {
						constants: {
							<?php $comma    = ''; ?>
							<?php foreach ( self::$constants as $constant_name => $constant_value) { ?>
								<?php echo $comma; $comma = ','; ?>
								<?php echo $constant_name; ?>: "<?php echo $constant_value; ?>"
							<?php } ?>
						},
						aSnippets: [
							<?php $comma    = ''; ?>
							<?php foreach ( $settings['aSnippets'] as $loop_snippet_setup ) { ?>
								<?php echo $comma; $comma = ','; ?> {
									snippet_name: "<?php echo $loop_snippet_setup['snippet_name']; ?>"
								}
							<?php } ?>
						]
				};
				</script>
			<?php
			$plugin_array[ self::$constants['TMCE_PLUGIN_NAME'] ] = plugins_url( 'assets/js/tmce_plugin.js?ver=1', __FILE__ );
			return $plugin_array;
		}//public static function register_tmce_plugin()

		public static function register_buttons( $buttons ) {
			array_push( $buttons, 'separator',  self::$constants['TMCE_PLUGIN_NAME'] );
			return $buttons;
		}//public static function register_buttons()

		private static function register_script_style() {
			wp_register_style(
				'mmlt-style',
				plugins_url( 'assets/css/style.css', __FILE__),
				array(),
				'4'
			);
			wp_register_script(
				'mmlt-admin-script',
				plugins_url( 'assets/js/admin-script.js', __FILE__ ),
				array( 'jquery' ),
				'2'
			);
			wp_register_script(
				'mmlt-script',
				plugins_url( 'assets/js/script.js', __FILE__ ),
				array( 'jquery' ),
				'4'
			);
		}//private static function register_script_style()

		public static function admin_enqueue_scripts() {
			wp_enqueue_style( 'mmlt-style' );
			wp_enqueue_script( 'mmlt-admin-script' );
		}//public static function admin_enqueue_scripts()

		public static function enqueue_fe_scripts() {
			wp_enqueue_style( 'mmlt-style' );
			wp_enqueue_script( 'mmlt-script' );
		}//public static function enqueue_fe_scripts()

		public static function inline_short_code( $atts = array(), $content = null ) {
			array_change_key_case( $atts );
			if ( !self::$mmlt_templates_included ) {
				require( dirname( __FILE__ ) . '/assets/templates/mmlt-templates.php' );
				self::$mmlt_templates_included = 1;
			}
			require( dirname( __FILE__ ) . '/assets/templates/menupublisher-4-wp-startcode.php' );

			$content .= menupublisher_4_wp_startcode::render( self::$constants , self::$lang_code, $atts['snippet_name'] );

			return $content;
		}//public static function inline_short_code()

		public static function admin_menu() {
			require( dirname( __FILE__ ) . '/assets/templates/menupublisher-4-wp-settings.php' );
			menupublisher_4_wp_settings::init( self::$constants );
		}//public static function admin_menu()

	}//class menupublisher_4_wp

	add_action( 'plugins_loaded', array( 'menupublisher_4_wp', 'init' ) );