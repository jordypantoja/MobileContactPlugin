<?php
/**
 * Mobile Contact Section.
 *
**/
class Mobile_Contact_Bar_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $plugin_screen_hook_suffix = null;

	/**
	 * version of this plugin.
	 *
	 * @since    1.5
	 *
	 * @var      string
	 */
	private $plugin_version = null;

	/**
	 * Name of this plugin.
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $plugin_name = null;

	/**
	 * Unique identifier for this plugin.
	 *
	 * It is the same as in class Mobile_Contact_Section
	 * Has to be set here to be used in non-object context, e.g. callback functions
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $plugin_slug = null;

	/**
	 * Unique identifier in the WP options table
	 *
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $settings_db_slug = null;

	/**
	 * Slug of the menu page on which to display the form sections
	 *
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $settings_section_slug = 'mcs_options_page';

	/**
	 * Group name of options
	 *
	 *
	 * @since    1.0
	 *
	 * @var      string
	 */
	private $settings_fields_slug = 'mcs_options_group';
	
	/**
	 * Structure of the form sections with headline, description and options
	 *
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	private $form_structure = null;

	/**
	 * Stored settings in an array
	 *
	 *
	 * @since    1.0
	 *
	 * @var      array
	 */
	private $stored_settings = array();

	/**
	 * Social networks
	 *
	 *
	 * @since    1.5
	 *
	 * @var      array
	 */
	private $social_networks = array();
	
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0
	 */
	private function __construct() {

		// Call variables from public plugin class.
		$plugin = Mobile_Contact_Section::get_instance();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_version = $plugin->get_plugin_version();
		$this->settings_db_slug = $plugin->get_settings_db_slug();
		$this->social_networks = $plugin->get_social_networks();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_head',			 array( $this, 'print_admin_css' ) );

		// Add the menu item to the options page in Settings.
		add_action( 'admin_menu', array( $this, 'add_menu_item_to_options_page' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'admin_init', array( $this, 'register_options' ) );

		// get current or default settings
		$this->stored_settings = $plugin->get_stored_settings();

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( ), $this->plugin_version );
		}

		/* collect css for the color picker */
		#wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'wp-color-picker' );
 	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		/* collect js for the color picker */
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), $this->plugin_version );
		}
		#wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Print dynamic CSS in the HTML Head section
	 *
	 * @since     1.4
	 *
	 */
	public function print_admin_css() {
	
		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}
		
		// print CSS only on this plugin's page
		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			$root_url = plugin_dir_url( dirname( __FILE__ ) );
			//$pngs = array( 'imdb', 'yelp', 'soundcloud', 'snap' ); // PNG image file namens
			// echo '<style type="text/css">';
			// print "\n";
			// $background_size = '40px 40px';
			// $background_position = '2.77em';
			// foreach ( array( 'phone' ) as $name ) {
			// 	printf(
			// 		".form-table th label[for='%s'] { display: block; height: 85px; background: url('%spublic/assets/images/phone.png') no-repeat scroll 0 %s transparent; background-size: %s; }",
			// 		$name,
			// 		$root_url,
			// 		$name,
			// 		$background_position,
			// 		$background_size
			// 	);
			// 	print "\n";
			// }
			
			
			// echo '</style>';
			// print "\n";
		}
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0
	 */
	public function add_menu_item_to_options_page() {

		$text = 'Settings';
		// Add a settings page for this plugin to the Settings menu.
		$this->plugin_screen_hook_suffix = add_options_page(
			sprintf( '%s %s', $this->plugin_name, __( $text ) ),
			$this->plugin_name,
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_options_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0
	 */
	public function display_options_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0
	 */
	public function add_action_links( $links ) {

		$text = 'Settings';
		return array_merge(
			array(
				'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->plugin_slug ) ) . '">' . esc_html__( $text ) . '</a>'
			),
			$links
		);

	}

	/**
	* Define and register the options
	* Run on admin_init()
	*
	* @since   1.0
	*/
	public function register_options () {

		$title = null;
		$html = null;
		
		$font_sizes = array();
		foreach( range( 4, 48 ) as $value ) {
			$font_sizes[ $value ] = sprintf( '%dpx', $value );
		}
		
		$icon_sizes = array();
		foreach( range( 10, 48, 2 ) as $value ) {
			$icon_sizes[ $value ] = sprintf( '%dpx', $value );
		}
		
		$readjustments = array();
		foreach( range( 0, 75 ) as $value ) {
			$readjustments[ $value ] = sprintf( '%dpx', $value );
		}
		
		$padding_sizes = array();
		foreach( range( 0, 32 ) as $value ) {
			$padding_sizes[ $value ] = sprintf( '%dpx', $value );
		}
		

		// translate recurring strings once
		$label_example	= esc_html__( 'Example', 'mobile-contact-sec' );
		$label_enter	= esc_html__( 'Enter a valid URL. If the URL is invalid it will not be used.', 'mobile-contact-sec' );
		$label_url		= esc_html__( 'Your URL on %s', 'mobile-contact-sec' );
		// define the form sections, order by appereance, with headlines, and options
		$this->form_structure = array(
			'1st_section' => array(
				'headline' => esc_html__( 'Mobile Contact Section', 'mobile-contact-sec' ),
				'description' => esc_html__( 'Set the Mobile Contact Section Data ', 'mobile-contact-sec' ),
				'options' => array(
					'headline' => array(
						'type'    => 'textfield',
						'title'   => esc_html__( 'Contact Us Page Text', 'mobile-contact-sec' ),
						'desc'    => esc_html__( 'Enter the Contact Us Page Text.', 'mobile-contact-sec' ),
					),
					'headline_url' => array(
						'type'    => 'url',
						'title'   => esc_html__( 'URL of the Contact Us Page', 'mobile-contact-sec' ),
						'desc'    => esc_html__( 'Enter a web address and the Contact Us Page becomes a link. The address must start with http:// or https://', 'mobile-contact-sec' ) ,
					),
					'phone' => array(
						'type'    => 'textfield',
						'title'   => esc_html__( 'Phone Number', 'mobile-contact-sec' ),
						'desc'    => esc_html__( 'Enter your Contact phone number.', 'mobile-contact-sec' ),
					),
					'phone_text' => array(
						'type'    => 'textfield',
						'title'   => esc_html__( 'Phone Text', 'mobile-contact-sec' ),
						'desc'    => esc_html__( 'Enter a text for the Contact text', 'mobile-contact-sec' ),
					),
					'bg_color' => array(
						'type'    => 'colorpicker',
						'title'   => esc_html__( 'Background Color', 'mobile-contact-sec' ),
						'desc'    => esc_html__( 'Select the Background color', 'mobile-contact-sec' ),
					),
					
				),
			),
			
		);
		// build form with sections and options
		foreach ( $this->form_structure as $section_key => $section_values ) {
		
			// assign callback functions to form sections (options groups)
			add_settings_section(
				// 'id' attribute of tags
				$section_key, 
				// title of the section.
				$this->form_structure[ $section_key ][ 'headline' ],
				// callback function that fills the section with the desired content
				array( $this, 'print_section_' . $section_key ),
				// menu page on which to display this section
				$this->settings_section_slug
			); // end add_settings_section()
			
			// set labels and callback function names per option name
			foreach ( $section_values[ 'options' ] as $option_name => $option_values ) {
				// set default description
				$desc = '';
				if ( isset( $option_values[ 'desc' ] ) and '' != $option_values[ 'desc' ] ) {
					if ( 'checkbox' == $option_values[ 'type' ] ) {
						$desc =  $option_values[ 'desc' ];
					} else {
						$desc =  sprintf( '<p class="description">%s</p>', $option_values[ 'desc' ] );
					}
				}
				// build the form elements values
				switch ( $option_values[ 'type' ] ) {
					case 'url':
						$title = sprintf( '<label for="%s">%s</label>', $option_name, $option_values[ 'title' ] );
						$value = isset( $this->stored_settings[ $option_name ] ) ? esc_url( $this->stored_settings[ $option_name ] ) : '';
						$html = sprintf( '<input type="text" id="%s" name="%s[%s]" value="%s">', $option_name, $this->settings_db_slug, $option_name, $value );
						$html .= $desc;
						break;
					
					case 'colorpicker':
						$title = sprintf( '<label for="%s">%s</label>', $option_name, $option_values[ 'title' ] );
						$value = isset( $this->stored_settings[ $option_name ] ) ? esc_attr( $this->stored_settings[ $option_name ] ) : '#cccccc';
						$html = sprintf( '<input type="text" id="%s" class="wp-color-picker" name="%s[%s]" value="%s">', $option_name, $this->settings_db_slug, $option_name, $value );
						$html .= $desc;
						break;
					// else text field
					default:
						$title = sprintf( '<label for="%s">%s</label>', $option_name, $option_values[ 'title' ] );
						$value = isset( $this->stored_settings[ $option_name ] ) ? esc_attr( $this->stored_settings[ $option_name ] ) : '';
						$html = sprintf( '<input type="text" id="%s" name="%s[%s]" value="%s">', $option_name, $this->settings_db_slug, $option_name, $value );
						$html .= $desc;
				} // end switch()

				// register the option
				add_settings_field(
					// form field name for use in the 'id' attribute of tags
					$option_name,
					// title of the form field
					$title,
					// callback function to print the form field
					array( $this, 'print_option' ),
					// menu page on which to display this field for do_settings_section()
					$this->settings_section_slug,
					// section where the form field appears
					$section_key,
					// arguments passed to the callback function 
					array(
						'html' => $html,
					)
				); // end add_settings_field()

			} // end foreach( section_values )

		} // end foreach( section )

		// finally register all options. They will be stored in the database in the wp_options table under the options name $this->settings_db_slug.
		register_setting( 
			// group name in settings_fields()
			$this->settings_fields_slug,
			// name of the option to sanitize and save in the db
			$this->settings_db_slug,
			// callback function that sanitizes the option's value.
			array( $this, 'sanitize_options' )
		); // end register_setting()
		
	} // end register_options()

	/**
	* Check and return correct values for the settings
	*
	* @since   1.0
	*
	* @param   array    $input    Options and their values after submitting the form
	* 
	* @return  array              Options and their sanatized values
	*/
	public function sanitize_options ( $input ) {
		foreach ( $this->form_structure as $section_name => $section_values ) {
			foreach ( $section_values[ 'options' ] as $option_name => $option_values ) {
				switch ( $option_values[ 'type' ] ) {
					// if checkbox is set assign '1', else '0'
					case 'checkbox':
						$input[ $option_name ] = isset( $input[ $option_name ] ) ? $input[ $option_name ] : 0 ;
						break;
					// if checkbox of a group of checkboxes is set assign '1', else '0'
					case 'checkboxes':
						foreach ( array_keys( $option_values[ 'values' ] ) as $option_name ) {
							$input[ $option_name ] = isset( $input[ $option_name ] ) ? $input[ $option_name ] : 0 ;
						}
						break;
					// clean email value
					case 'email':
						$email = sanitize_email( $input[ $option_name ] );
						$input[ $option_name ] = is_email( $email ) ? $email : '';
						break;
					// clean url values
					case 'url':
						$input[ $option_name ] = esc_url_raw( $input[ $option_name ] );
						break;
					// clean float values between 0 and 1
					case 'zero2one':
						// consider possible local orthography: change comma to point
						$input[ $option_name ] = str_replace( ',', '.', $input[ $option_name ] );
						// cast string to float number in the range from 0 to 1
						if ( $input[ $option_name ] < 0 ) {
							$input[ $option_name ] = 0.0;
						} elseif ( 1 < $input[ $option_name ] ) {
							$input[ $option_name ] = 1.0;
						} else {
							// note: strings are converted to 0.0
							$input[ $option_name ] = floatval( $input[ $option_name ] );
						}
						break;
					// clean all other form elements values
					default:
						$input[ $option_name ] = sanitize_text_field( $input[ $option_name ] );
				} // end switch()
			} // foreach( options )
		} // foreach( sections )
		return $input;
	} // end sanitize_options()

	/**
	* Print the option
	*
	* @since   1.0
	*
	*/
	public function print_option ( $args ) {
		echo $args[ 'html' ];
	}

	/**
	* Print the explanation for section 1
	*
	* @since   1.0
	*/
	public function print_section_1st_section () {
		printf( "<p>%s</p>\n", $this->form_structure[ '1st_section' ][ 'description' ] );
	}

}
