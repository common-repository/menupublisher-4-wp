<?php
	defined('ABSPATH') or die('No script kiddies please!');

	class menupublisher_4_wp_settings {
		const          DEVEL                   = 0;
		private static $constants              = null;

		public static function init($constants) {
			self::$constants = $constants;
			add_options_page(
				sprintf(
					__( '%s options', 'menupublisher-4-wp' ),
					self::$constants['PLUGIN_NAME']
				),
				self::$constants['PLUGIN_NAME'],
				'manage_options',
				self::$constants['ADMIN_MENU_SLUG'],
				array( 'menupublisher_4_wp_settings', 'get_options' )
			);
		}//public static function init()

		public static function get_options() {
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have permissions' ) );
			}

			// $settings has the following form:
			// $settings['sRestaurantInlineHash']
			// $settings['bPowered']
			// $settings['aSnippets'][ $snippet_id ]['snippet_name'] // unique name among aSnippets
			// $settings['aSnippets'][ $snippet_id ]['card_types']   // comma list of card_types (currently only 0, for lunch)
			// $settings['aSnippets'][ $snippet_id ]['whole_week']
			// $settings['aSnippets'][ $snippet_id ]['collections']
			// $settings['aSnippets'][ $snippet_id ]['snippet_layout']
			$settings = get_option( self::$constants['MMLT_MAIN_OPTION_NAME'] );
			if (!is_array($settings)) {
				$settings = array(
					'sRestaurantInlineHash' => '',
					'bPowered'              => 1,
					'aSnippets'             => array(),
				);
			}
			$errors         = array();
			$settings_saved = 0;
			$snippet_id     = 0;
			if ( isset( $_GET['snippet_id'] ) || isset( $_GET['create_snippet'] ) ) {
				$snippet_id   = (int) $_GET['snippet_id'];
			}

			if ( isset( $_POST['mmlt-submit'] ) ) {
				switch ( $_POST['mmlt-submit'] ) {
					case 'sRestaurantInlineHash':
						$errors = self::validate_restaurant_inline_hash( $settings );
						break;

					case 'aSnippet':
						$validated_data = self::validate_snippet( $settings );
						$snippet_id     = $validated_data['snippet_id'];
						$errors         = $validated_data['errors'];
						break;

					default:
						// unknown mmlt-submit ignore the POST
						$errors['unknown-mmlt-submit'] = 1;
						break;

				}//switch ( $_POST['mmlt-submit'] )

				if ( count( $errors ) < 1 ) {
					// in that case data was valid, and we can save it
					$settings_saved = 1;
					update_option( self::$constants['MMLT_MAIN_OPTION_NAME'], $settings );
				}
			}//if ( isset( $_POST['mmlt-submit'] ) )

			if ( $snippet_id != 0 || ( isset( $_GET['create_snippet'] ) && (int) $_GET['create_snippet'] ) ) {
				self::render_snippet_settings( $settings, $errors, $settings_saved, $snippet_id );
			} else {
				self::render_snippets_list( $settings, $errors, $settings_saved );
			}
		}//public static function get_options()

		private static function validate_restaurant_inline_hash( &$settings ) {
			$errors = array();
			if ( ! isset( $_POST['sRestaurantInlineHash'] ) || ( $_POST['sRestaurantInlineHash'] == '' ) ) {
				$errors['sRestaurantInlineHash'] = __( 'You must enter a RestaurantCode', 'menupublisher-4-wp' );
			} else {
				// check if that sRestaurantInlineHash is valid
				$restaurnt_inline_hash_check_url = 'http://www.lunchtime.de';
				$restaurnt_inline_hash_check_url =
					$restaurnt_inline_hash_check_url .
					'/index.php' .
					'?page=common_inline' .
					'&spage=new_menus' .
					'&sRestaurantInlineHash=' . rawurlencode( $_POST['sRestaurantInlineHash'] ) .
					'&bCheckHash=1'
				;
				$check_response = file_get_contents($restaurnt_inline_hash_check_url);
				if ( ( $check_response === '' ) || ( $check_response === null ) ) {
					$errors['sRestaurantInlineHash'] = __( 'RestaurantCode cannot be validated currently, since the Server seems to be down. Try again later.', 'menupublisher-4-wp' );
				} else if ( ! $check_response ) {
					$errors['sRestaurantInlineHash'] = __( 'RestaurantCode is invalid, please get the correct RestaurantCode from your MenuPublisher backend.', 'menupublisher-4-wp' );
				}
			}//else if ( $_POST['sRestaurantInlineHash'] != '' )

			$settings['sRestaurantInlineHash'] = $_POST['sRestaurantInlineHash'];
			if ($_POST['bPowered']) {
				$settings['bPowered'] = 1;
			} else {
				$settings['bPowered'] = 0;
			}

			return $errors;
		}//private static function validate_restaurant_inline_hash()

		private static function validate_snippet( &$settings ) {
			$errors                 = array();
			$snippet_id             = (int)$_POST['snippet_id'];
			$snippet_name           =      $_POST['snippet_name'];
			$snippet_name_duplicate = 0;
			if ( $snippet_id == 0 ) {
				$snippet_id++;
				while ( isset( $settings['aSnippets'][ $snippet_id ] ) ) {
					$snippet_id++;
				}
				$settings['aSnippets'][ $snippet_id ] = array(
					'snippet_name'     => '',
					'snippet_layout'   => self::$constants['LAYOUT_1_COLUMN'],
					'card_types'       => '',
					'whole_week'       => 0,
					'collections'      => 0,
				);
			}//if ( $snippet_id == 0 )

			if ( $snippet_name == '' ) {
				$errors['snippet_name'] = __( 'You must enter a unique snippet name.', 'menupublisher-4-wp' );
			} else if ( preg_match( '/[^0-9a-zA-Z_\-]/', $snippet_name ) ) {
				$errors['snippet_name'] = __( 'You may only use numbers, upper and lower case letters (a-zA-Z), underscore "_" and minus "-" for snippet name.', 'menupublisher-4-wp' );
			} else {
				// check for $snippet_name uniqueness
				foreach ( $settings['aSnippets'] as $loop_snippet_id => $loop_snippet_setup ) {
					if ( $loop_snippet_id == $snippet_id ) {
						continue;
					}
					if ( $loop_snippet_setup['snippet_name'] == $snippet_name ) {
						$snippet_name_duplicate = 1;
						break;
					}
				}//foreach ( $settings['aSnippets'] )
				if ( $snippet_name_duplicate ) {
					$errors['snippet_name'] = __( 'This snippet name is already used, choose another one', 'menupublisher-4-wp' );
				}
			}//else if ( $snippet_name != '' )

			$settings['aSnippets'][ $snippet_id ]['snippet_name'] = $snippet_name;

			$snippet_layout = $_POST['snippet_layout'];
			if ( $snippet_layout != self::$constants['LAYOUT_2_COLUMNS'] ) {
				$snippet_layout = self::$constants['LAYOUT_1_COLUMN'];
			}
			$settings['aSnippets'][ $snippet_id ]['snippet_layout'] = $snippet_layout;

			if ( isset( $_POST['lunch'] ) && ( $_POST['lunch'] == 'on' ) ) {
				$settings['aSnippets'][ $snippet_id ]['card_types'] = '0';
			} else {
				$settings['aSnippets'][ $snippet_id ]['card_types'] = '';
			}

			if ( isset( $_POST['whole_week'] ) && ( $_POST['whole_week'] == 'on' ) ) {
				$settings['aSnippets'][ $snippet_id ]['whole_week'] = 1;
			} else {
				$settings['aSnippets'][ $snippet_id ]['whole_week'] = 0;
			}

			if ( isset( $_POST['collections'] ) && ( $_POST['collections'] == 'on' ) ) {
				$settings['aSnippets'][ $snippet_id ]['collections'] = 1;
			} else {
				$settings['aSnippets'][ $snippet_id ]['collections'] = 0;
			}

			return array(
				'errors'     => $errors,
				'snippet_id' => $snippet_id
			);
		}//private static function validate_snippet()

		private static function render_snippet_settings( $settings, $errors, $settings_saved, $snippet_id ) {
			$snippet = array();
			if ( isset( $settings['aSnippets'][ $snippet_id ] ) ) {
				$snippet = $settings['aSnippets'][ $snippet_id ];
			}

			?>
				<form
					action=""
					method="post"
					class="wrap"
				>
					<input
						type="hidden"
						name="mmlt-submit"
						value="aSnippet"
					/>
					<input
						type="hidden"
						name="snippet_id"
						value="<?php echo (int) $snippet_id; ?>"
					/>
					<div class="menupublisher-4-wp-settings">
						<a
							href="<?php menu_page_url( self::$constants['ADMIN_MENU_SLUG'] ); ?>"
							class="button button-primary"
						>
							&lt; <?php echo _e( 'back', 'menupublisher-4-wp' ); ?>
						</a>
						<h1>
							<?php _e( 'Snippet setup',  'menupublisher-4-wp' ); ?>
						</h1>
						<?php if ( $settings_saved ): ?>
							<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
								<p>
									<strong>
										<?php _e( 'Settings saved.', 'menupublisher-4-wp' ); ?>
									</strong>
								</p>
								<button class="notice-dismiss" type="button">
									<span class="screen-reader-text">
										<?php _e( 'Dismiss this notice.', 'menupublisher-4-wp' ); ?>
									</span>
								</button>
							</div>
						<?php endif; ?>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'Snippet name:',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td>
										<div class="mmlt-text-container">
											<input
												type="text"
												class="regular-text"
												name="snippet_name"
												value="<?php echo esc_attr( $snippet['snippet_name'] ); ?>"
											/>
										</div>
										<?php if ( $errors['snippet_name'] != '' ):?>
											<p class="mmlt-error">
												<?php echo $errors['snippet_name']; ?>
											</p>
										<?php	else:?>
											<p class="description">
												<?php _e( 'Must be unique among all snippets.',  'menupublisher-4-wp' ); ?>
											</p>
										<?php	endif;?>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'Layout:',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td>
										<label>
											<input
												type="radio"
												name="snippet_layout"
												value="<?php echo self::$constants['LAYOUT_1_COLUMN']; ?>"
												<?php
													if ( $snippet['snippet_layout'] != self::$constants['LAYOUT_2_COLUMNS'] ):
												?>
													checked="checked"
												<?php endif; ?>
											/>&nbsp;<?php _e( 'one column',  'menupublisher-4-wp' ); ?>
										</label>
										<br />
										<label>
											<input
												type="radio"
												name="snippet_layout"
												value="<?php echo self::$constants['LAYOUT_2_COLUMNS']; ?>"
												<?php
													if ( $snippet['snippet_layout'] == self::$constants['LAYOUT_2_COLUMNS'] ):
												?>
													checked="checked"
												<?php endif; ?>
											/>&nbsp;<?php _e( 'two columns',  'menupublisher-4-wp' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'Lunch card:',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td>
										<div class="mmlt-text-container">
											<label>
												<input
													type="checkbox"
													name="lunch"
													<?php
														$cards = explode( ',', $snippet['card_types'] );
													if ( in_array( '0', $cards ) ):
													?>
														checked="checked"
													<?php endif; ?>
												/>&nbsp;<?php echo _e( 'Show lunch dishes',  'menupublisher-4-wp' ); ?>
											</label>
											<br />
											<label>
												<input
													type="checkbox"
													name="whole_week"
													<?php
														if ( (int) $snippet['whole_week'] ):
													?>
														checked="checked"
													<?php endif; ?>
												/>&nbsp;<?php echo _e( 'show whole week',  'menupublisher-4-wp' ); ?>
											</label>
										</div>
										<p class="description">
											<?php echo _e( 'if you unselect whole week, then only dishes from today till sunday will be displayed',  'menupublisher-4-wp' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'Menu (à la carte):',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td>
										<div class="mmlt-text-container">
											<label>
												<input
													type="checkbox"
													name="collections"
													<?php if ( (int) $snippet['collections'] ):?>
														checked="checked"
													<?php endif; ?>
												/>&nbsp;<?php echo _e( 'Show à la carte dishes',  'menupublisher-4-wp' ); ?>
											</label>
										</div>
									</td>
								</tr>
							</tbody>
						</table>

						<p class="submit">
							<input
								type="submit"
								value="<?php esc_attr_e( 'save', 'menupublisher-4-wp' ) ?>"
								class="button button-primary"
								id="submit"
								name="submit"
							/>
						</p>
					</div>
				</form>
			<?php
		}//private static function render_snippet_settings()

		private static function render_snippets_list( $settings, $errors, $settings_saved ) {
			?>
				<form
					action=""
					method="post"
					class="wrap"
				>
					<input
						type="hidden"
						name="mmlt-submit"
						value="sRestaurantInlineHash"
					/>
					<div class="menupublisher-4-wp-settings">
						<h1><?php
							printf(
								__( '%s setup',  'menupublisher-4-wp' ),
								self::$constants['PLUGIN_NAME']
							);
						?></h1>
						<?php if ( $settings_saved ): ?>
							<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
								<p>
									<strong>
										<?php _e( 'Settings saved.', 'menupublisher-4-wp' ); ?>
									</strong>
								</p>
								<button class="notice-dismiss" type="button">
									<span class="screen-reader-text">
										<?php _e( 'Dismiss this notice.', 'menupublisher-4-wp' ); ?>
									</span>
								</button>
							</div>
						<?php endif; ?>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'RestaurantCode:',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td colspan="2">
										<div class="mmlt-text-container">
											<input
												type="text"
												class="regular-text"
												name="sRestaurantInlineHash"
												value="<?php echo esc_attr( $settings['sRestaurantInlineHash'] ); ?>"
											/>
										</div>
										<div class="mmlt-ipoint">
											<div class="mmlt-payload">
												<?php _e( 'You can find this in your MenuPublisher backend, navigate there to DISTRIBUTION => Wordpress Plugin and search for <b>RestaurantCode</b>',  'menupublisher-4-wp' ); ?>
											</div>
										</div>
										<?php if ( $errors['sRestaurantInlineHash'] != '' ):?>
										<div class="mmlt-errpoint">
											<div class="mmlt-payload">
												<?php echo $errors['sRestaurantInlineHash']; ?>
											</div>
										</div>
										<?php	endif;?>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label>
											<?php _e( 'Powered info:',  'menupublisher-4-wp' ); ?>
										</label>
									</th>
									<td colspan="2">
										<div class="mmlt-text-container">
											<label>
												<input
													type="checkbox"
													name="bPowered"
													<?php if ( (int) $settings['bPowered'] ):?>
														checked="checked"
													<?php endif; ?>
												/>&nbsp;<?php echo _e( 'Show powered info', 'menupublisher-4-wp' ); ?>
											</label>
										</div>
									</td>
								</tr>
								<?php foreach ( $settings['aSnippets'] as $loop_snippet_id => $loop_snippet_setup ): ?>
									<tr>
										<th scope="row">
											<?php echo $loop_snippet_setup['snippet_name']; ?>
										</th>
										<td>
											<?php
												$selected_properties = '';
												if ($loop_snippet_setup['snippet_layout'] == self::$constants['LAYOUT_2_COLUMNS']) {
													$selected_properties = __( '2-columns layout', 'menupublisher-4-wp' );
												} else {
													$selected_properties = __( '1-column layout', 'menupublisher-4-wp' );
												}
												foreach ( explode( ', ', $loop_snippet_setup['card_types']) as $card_type ) {
													if ($card_type == 0) {
														$selected_properties .= ', ' . __( 'shows lunch dishes', 'menupublisher-4-wp' );
														if ($loop_snippet_setup['whole_week']) {
															$selected_properties .=  ' ' . __( 'of whole week ', 'menupublisher-4-wp' );
														}
													}
												}
												if ($loop_snippet_setup['collections']) {
													$selected_properties .= ', ' . __( 'shows à la carte dishes', 'menupublisher-4-wp' );
												}
												echo $selected_properties;
											?>
										</td>
										<td>
											<a
												href="<?php menu_page_url( self::$constants['ADMIN_MENU_SLUG'] ); ?>&snippet_id=<?php echo $loop_snippet_id; ?>"
												class="button button-primary"
											>
												<?php _e( 'edit', 'menupublisher-4-wp' ); ?>
											</a>
										</td>
									</tr>
								<?php endforeach;?>
							</tbody>
						</table>

						<p class="submit">
							<?php if ( ( $settings['sRestaurantInlineHash'] != '' ) && ( $errors['sRestaurantInlineHash'] == '' ) ):?>
								<a
									href="<?php menu_page_url( self::$constants['ADMIN_MENU_SLUG'] ); ?>&create_snippet=1"
									class="button button-primary"
									style="float: right;"
								>
									<?php _e( '+add code snippet',  'menupublisher-4-wp' ); ?>
								</a>
							<?php	endif; ?>
							<input
								type="submit"
								value="<?php esc_attr_e( 'save', 'menupublisher-4-wp' ) ?>"
								class="button button-primary"
								id="submit"
								name="submit"
							/>
						</p>
					</div>
				</form>
			<?php
		}//private static function render_snippets_list()

	}//class menupublisher-4-wp-settings


/* eof */