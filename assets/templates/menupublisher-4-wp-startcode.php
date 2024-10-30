<?php
	defined('ABSPATH') or die('No script kiddies please!');

	if (defined('MENUPUBLISHER_4_WP_STARTCODE_DEFINED')) {
		printf(
			__( '<div style="color: red;">You may only use %s once on a page</div>', 'menupublisher-4-wp' ),
			self::$constants['SHORT_CODE_NAME']
		);
		return;
	}
	define('MENUPUBLISHER_4_WP_STARTCODE_DEFINED', 1);

	class menupublisher_4_wp_startcode {
		private static $devel     = 0;
		private static $constants = null;

		public static function render( $constants, $lang_code, $snippet_name ) {
			self::$constants = $constants;
			$settings        = get_option( self::$constants['MMLT_MAIN_OPTION_NAME'] );
			if ( ! is_array( $settings ) ) {
				ob_start();
				?>
					<div class="mmlt-error">
						<?php
							printf(
								__( 'Please setup %s <a href="%s">here</a> before using this shortcode.', 'menupublisher-4-wp' ),
								self::$constants['PLUGIN_NAME'],
								menu_page_url( self::$constants['ADMIN_MENU_SLUG'] )
							);
						?>
					</div>
				<?php
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}//if ( ! is_array( $settings ) )

			$snippet = null;
			foreach ( $settings['aSnippets'] as $loop_snippet ) {
				if ( $loop_snippet['snippet_name'] == $snippet_name ) {
					$snippet = $loop_snippet;
					break;
				}
			}

			if ( ! $snippet ) {
				ob_start();
				?>
					<div class="mmlt-error">
						<?php
							printf(
								__( 'Please setup %s <a href="%s">here</a> before using this shortcode.', 'menupublisher-4-wp' ),
								self::$constants['PLUGIN_NAME'],
								menu_page_url( self::$constants['ADMIN_MENU_SLUG'] )
							);
						?>
					</div>
				<?php
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}

			if ( $lang_code != self::$constants['DEFAULT_LANG_CODE'] ) {
				$snippet['all_translations'] = 1;
			}

			ob_start();
			?>
			<!--
				### mmltMark Payload ###
			-->
			<div class="mmltPayload mmltParent <?php echo $snippet['snippet_layout']; ?>">
				<!--
					### mmltMark Allergenbutton ###
				-->
				<div>
					<div class="mmlt-fc">
						<div class="mmltAdditAllergButton translatable mmltLabel mmltFixed_additAndAllerg" onclick="ommltProcessor.fToggleAdditAllergFiltersContainer(this);">
						</div>
					</div>
					<div class="mmltAdditAllergFiltersContainer" style="display: none;">
						<div class="translatable mmltLabel mmltFixed_additAllergText">
						</div>
						<div class="mmlt-fc">
							<div onclick="ommltProcessor.fSwitchAdditAllerg(this);" class="filtersSelector mmltAllergens translatable mmltLabel mmltFixed_allergens">
							</div>
							<div onclick="ommltProcessor.fSwitchAdditAllerg(this);" class="filtersSelector mmltAdditives translatable mmltLabel mmltFixed_additives">
							</div>
						</div>
						<div class="mmltAllergensFilters menuFilters mmlt-fc">
						</div>
						<div class="mmltAdditivesFilters menuFilters mmlt-fc">
						</div>
					</div>
				</div>

				<div class="mmltLunchContainer">
					<div class="mmltLunchTitle translatable mmltLabel">
						<span class="mmltFixed_lunch">
						</span>
						<div class="mmltLunchSubTitle">
							<span class="mmltFixed_from">
							</span>
							<span class="mmltTimespan">
								01 - 01
							</span>
						</div>
					</div>
				</div>
			</div>

			<!--
				### mmltMark InlineCode ###
			-->
			<script type="text/javascript">
				var ommltProcessor = null;

				function fmmltJsPostProcessing() {
					ommltProcessor = new cmmltProcessor(
						{ //oTranslations:
							oFixedStrings: {
								additAllergText: '<?php _e( 'Here you have the possibility to exclude dishes that contain specific allergens or additives. If you wish to hide a menu with a certain allergen, then simply uncheck the corresponding checkbox. The same applies to additives.', 'menupublisher-4-wp' ); ?>',
								additives:       '<?php _e( 'Additives',                  'menupublisher-4-wp' ); ?>',
								additAndAllerg:  '<?php _e( 'Additives and allergens',    'menupublisher-4-wp' ); ?>',
								allergens:       '<?php _e( 'Allergens',                  'menupublisher-4-wp' ); ?>',
								downloadPdf:     '<?php _e( '&gg; download card as PDF ', 'menupublisher-4-wp' ); ?>',
								from:            '<?php _e( 'from',                       'menupublisher-4-wp' ); ?>',
								lunch:           '<?php _e( 'Lunch',                      'menupublisher-4-wp' ); ?>',
								menus:           '<?php _e( 'Menu',                       'menupublisher-4-wp' ); ?>',
								noData:          '<?php _e( 'Currently the restaurant has no menu setup, try again later.', 'menupublisher-4-wp' ); ?>',
								or:              '<?php _e( 'or',                         'menupublisher-4-wp' ); ?>'
							},
							aWeekDayNames: [
								'<?php _e( 'Monday',    'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Tuesday',   'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Wednesday', 'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Thursday',  'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Friday',    'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Saturday',  'menupublisher-4-wp' ); ?>',
								'<?php _e( 'Sunday',    'menupublisher-4-wp' ); ?>'
							],
							aMonthNames: [
								'<?php _e( 'January',   'menupublisher-4-wp' ); ?>',
								'<?php _e( 'February',  'menupublisher-4-wp' ); ?>',
								'<?php _e( 'March',     'menupublisher-4-wp' ); ?>',
								'<?php _e( 'April',     'menupublisher-4-wp' ); ?>',
								'<?php _e( 'May',       'menupublisher-4-wp' ); ?>',
								'<?php _e( 'June',      'menupublisher-4-wp' ); ?>',
								'<?php _e( 'July',      'menupublisher-4-wp' ); ?>',
								'<?php _e( 'August',    'menupublisher-4-wp' ); ?>',
								'<?php _e( 'September', 'menupublisher-4-wp' ); ?>',
								'<?php _e( 'October',   'menupublisher-4-wp' ); ?>',
								'<?php _e( 'November',  'menupublisher-4-wp' ); ?>',
								'<?php _e( 'December',  'menupublisher-4-wp' ); ?>'
							]
						},//oTranslations
						'<?php echo $lang_code; ?>',
						<?php
							if ($settings['bPowered']) {
								echo 1;
							} else {
								echo 0;
							}
						?>
					);//ommltProcessor = new cmmltProcessor()
					ommltProcessor.fProcess();
				}//function fmmltJsPostProcessing()
			</script>
			<?php
			$inline_url = self::get_inline_url( $snippet, $settings['sRestaurantInlineHash'] );
			wp_enqueue_script(
				'mmlt-inline-script',
				$inline_url,
				array( 'jquery', 'mmlt-script' ),
				'1',
				true
			);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}//public static function render()

		private static function get_inline_url( $snippet, $sRestaurantInlineHash ) {
			$inline_url     = '//www.lunchtime.de/index.php?page=common_inline&spage=new_menus';
			if (self::$devel) {
				$sRestaurantInlineHash = '7544bfa194';
				$inline_url            = '//lunchtime-localdevel.wapitz.de/index.php?page=common_inline&spage=new_menus';
				echo '<div style="color: red;">development URL was used </div>';
			}
			$inline_url .=
				'&var_prefix=mmlt' .
				'&sRestaurantInlineHash=' . $sRestaurantInlineHash .
				'&js_post_processing=fmmltJsPostProcessing'
			;
			if ( $snippet['card_types'] != '' ) {
				$inline_url .= '&card_types=' . $snippet['card_types'];
				if ( $snippet['whole_week'] != '' ) {
					$inline_url .= '&whole_week=1';
				}
			}
			if ( $snippet['collections'] != '' ) {
				$inline_url .= '&collections=1';
			}
			if ( $snippet['all_translations'] != '' ) {
				$inline_url .= '&all_translations=1';
			}
			if ( $snippet['menu_images'] != '' ) {
				$inline_url .= '&menu_images=1';
			}
			return $inline_url;
		}//private static function get_inline_url()

	}//menupublisher_4_wp_startcode

/* eof */