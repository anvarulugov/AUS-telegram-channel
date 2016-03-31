<?php 
/**
 * AUS Basic Options
 *
 * plugin Options page generator class
 * Uses Wordpress default form html markup
 *
 * @link http://codex.wordpress.org/Creating_Options_Pages
 *
 * @package WordPress
 * @subpackage AUS Bsic Options
 * @since AUS Bsic 0.1.1
 * @author Anvar Ulugov
 * @license GPL2
 */

class AUS_tb_options {

	private $options;
	private $developer_mode;
	private $plugin_name;
	private $plugin_slug;
	private $configs;

	function __construct( $configs = array() ) {

		if ( ! empty( $configs ) ) {
			$this->configs = $configs;
			$this->options 	= get_option( $this->configs['options'] );
			$this->plugin_slug = $this->configs['plugin_slug'];
			$this->plugin_name = $this->configs['plugin_name'];
		}

		$this->developer_mode = true;
		add_action( 'admin_menu', array( $this, 'create_menu_page' ) );
		add_action( 'admin_init', array( $this, 'initialize_plugin_options' ) );
	}

	/**
	 * Register Menu items
	 */

	public function create_menu_page() {
		add_options_page( 
			__( 'Telegram Submission', 'aus-basic' ), 
			__( 'AUS Telegram Bot' , 'aus-basic' ), 
			'manage_options', 
			$this->plugin_slug . '_plugin_options', 
			array( $this, 'menu_page_display' )
		);
	}

	/**
	 * Main menu page display
	 */

	public function menu_page_display() {
	?>
		<div class="wrap">
		<?php if ( $this->developer_mode ) : ?>
			<?php print_r( $this->options ); ?>
		<?php endif; ?>
		<h2><?php echo sprintf( __( '%s Options', 'aus-basic' ), $this->plugin_name ); ?></h2>
		<form action="options.php" method="post">
		<?php settings_fields( $this->plugin_slug . '_plugin_options_group' ); ?>
		<?php do_settings_sections( $this->plugin_slug . '_plugin_options' ); ?>
		<?php submit_button(); ?>
		</form>
		</div>
	<?php 
	}

	/**
	 * Initialize plugin options
	 */

	public function initialize_plugin_options() {

		add_settings_section(
			$this->plugin_slug . '_plugin_settings_section',
			'',
			'',
			$this->plugin_slug . '_plugin_options'
		);

		add_settings_field(
			'bot_token',
			'<label for="bot_token">' . __( 'Bot Token', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'bot_token',
				'type' => 'text',
				'description' => __( 'Insert bot token', 'aus-basic' ),
			)
		);

		add_settings_field(
			'channelname',
			'<label for="channelname">' . __( '@channelusername', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'channelname',
				'type' => 'text',
				'description' => __( 'Insert @channelusername', 'aus-basic' ),
			)
		);

		add_settings_field(
			'start_date',
			'<label for="start_date">' . __( 'Start date', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'start_date',
				'type' => 'text',
				'description' => __( 'Insert from which date posts must be send. Format: 2015-01-02', 'aus-basic' ),
			)
		);

		add_settings_field(
			'categories',
			'<label for="categories">' . __( 'Categories', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'categories',
				'type' => 'categories',
				'atts' => array( 'multiple' => true ),
				'description' => __( 'Select categories. If none is selected all categories will be used', 'aus-basic' ),
			)
		);

		add_settings_field(
			'recurrence',
			'<label for="recurrence">' . __( 'Recurrence', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'recurrence',
				'type' => 'select',
				'options' => array(
					'hourly'	 =>'Once Hourly',
					'twicedaily' => 'Twice Daily', 
					'daily' 	 => 'Once Daily', 
				),
				'description' => __( 'Select recurrence duration', 'aus-basic' ),
			)
		);

		add_settings_field(
			'text_limit',
			'<label for="text_limit">' . __( 'Text limit', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'text_limit',
				'type' => 'number',
				'description' => __( 'Here you change the default (100 words) text limit.', 'aus-basic' ),
			)
		);

		add_settings_field(
			'before_text',
			'<label for="before_text">' . __( 'Before text', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'before_text',
				'type' => 'textarea',
				'description' => __( 'Custom text before message.', 'aus-basic' ),
				'editor' => array( 'visual' => false ),
			)
		);

		add_settings_field(
			'after_text',
			'<label for="after_text">' . __( 'After text', 'aus-basic' ) . '</label>',
			array( $this, 'input'),
			$this->plugin_slug . '_plugin_options',
			$this->plugin_slug . '_plugin_settings_section',
			array(
				'id' => 'after_text',
				'type' => 'textarea',
				'description' => __( 'Custom text after message.', 'aus-basic' ),
				'editor' => array( 'visual' => false ),
			)
		);

		register_setting(
			$this->plugin_slug . '_plugin_options_group',
			$this->plugin_slug . '_plugin_options',
			array( $this, 'senitize')
		);

	}

	public function senitize( $input ) {
		$output = array();

		foreach ($input as $key => $value) {
			if ( isset( $input[ $key ] ) ) {
				if ( is_array( $input[ $key ] ) ) {
					foreach ( $input[ $key ] as $sub_key => $sub_value ) {
						$output[ $key ][ $sub_key ] = strip_tags( stripslashes( $sub_value ) );
					}
				} else {
					$output[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
				}
			}
		}

		return apply_filters( array( $this, 'senitize' ), $output, $input);

	}

	public function _esc_attr( $option ) {
		if( isset( $this->options[ $option ] ) )
			return $this->options[ $option ];
		else
			return false;
	}

	/**
	 * Initialize plugin options callbacks
	 */
	public function plugin_general_options_ballback() {

		$html = '<h4>' . __( 'General Options', 'aus-basic' ) . '</h4>';
		//echo $html;

	}

	public function input( $args, $name_type = 'option', $post_id = false ) {

		$defaults = array(
			'id' => '',
			'type' => '',
			'title' => '',
			'description' => '',
			'options' => array(),
			'editor' => array(
				'visual' => true,
				'teeny'=>true,
				'textarea_rows'=>4,
			),
			'atts' => array(),
		);

		$configs = array_replace_recursive( $defaults, $args );
		extract( $configs, EXTR_OVERWRITE );

		if ( ( $type == 'select' || $type == 'cats' || $type == 'categories' ) && ! empty( $atts ) && array_key_exists( 'multiple', $atts ) ) {
			$multiple = true;
		} else {
			$multiple = false;
		}

		if ( $name_type == 'option' ) {
			$field_name = $this->plugin_slug . '_plugin_options' . '[' . $id . ']';
			$value = $this->_esc_attr( $id, $type );
		} elseif ( $name_type == 'metabox' && $post_id ) {
			$field_name = $id;
			$value = get_post_meta( $post_id, $id, true );
		}
		

		$editor['textarea_name'] = $field_name;

		$attributes = '';
		if( isset( $atts ) and ! empty( $atts ) ) {
			foreach ($atts as $attribute => $attr_value) {
				$attributes .= $attribute . '="' . $attr_value . '"';
			}
		}

		switch ( $type ) {

			case 'radio':
				$input = '<fieldset>';
				foreach ($options as $key => $option) {
					$input .= '<label title="' . $option . '">';
					$input .= '<input type="radio" name="' . $field_name . '" value="' . $key . '" ' . ( $value == $key ? 'checked="checked"' : '' ) . ' />';
					$input .= '<span>' . $option . '</span>';
					$input .= '</label><br />';
				}
				$input .= '</fieldset>';
				break;
			case 'radioimage':
				$input = '<fieldset>';
				$input .= '<ul class="radioimage">';
				foreach ( $options as $key => $option ) {
					$input .= "<li>";
					$input .= '<label title="' . $option . '">';
					$input .= '<input style="display:none" type="radio" name="' . $field_name . '" value="' . $key . '" ' . ( $value == $key ? 'checked="checked"' : '' ) . ' />';
					$input .= '<img' . ( $value == $key ? ' class="checked"' : '' ) . '  src="' . get_aus_uri() . '/media/img/' . $option . '" />';
					//$input .= '<span>' . $option . '</span>';
					$input .= '</label>';
					$input .= "</li>";
				}
				$input .= '</ul>';
				$input .= '</fieldset>';
				break;
			case 'textarea':
				if ( $editor['visual'] === true ) {
					ob_start();
					wp_editor($value, $id, $editor);
					$input = ob_get_contents();
					ob_end_clean();
				} else {
					$input = '<textarea name="' . $field_name . '" id="' .$id . '"' . $attributes . '>' . $value . '</textarea>';
				}
				break;
			case 'select':
				$input  = '<select name="' . $field_name . ( $multiple ? '[]' : '' ) . '" id="' .$id . '" ' . $attributes . '>';
				$input .= '<option value="0">&ndash; ' . __( 'Select', 'aus-basic' ) . ' &ndash;</option>';
				foreach ( $options as $key => $option ) {
					if ( $multiple ) {
						$selected = ( in_array( $key, $value ) ? 'selected="selected"' : '' );
					} else {
						$selected = ( $value == $key ? 'selected="selected"' : '' );
					}
					$input .= '<option ' . $selected . ' value="'. $key .'">' . $option . '</option>';
				}
				$input .= '</select>';
				break;

			case 'categories':
			case 'cats':
				$input = '<select name="' . $field_name . ( $multiple ? '[]' : '' ) . '" id="' .$id . '" ' . $attributes . '>';
				$input .= '<option value="0">&ndash; ' . __( 'Select', 'aus-basic' ) . ' &ndash;</option>';
				foreach ( get_categories( array( 'hide_empty' => false ) ) as $cat ) {
					if ( $multiple ) {
						$selected = ( in_array( $cat->cat_ID, (array)$value ) ? 'selected="selected"' : '' );
					} else {
						$selected = ( $value == $cat->cat_ID ? 'selected="selected"' : '' );
					}
					$input .= '<option ' . $selected . ' value="'. $cat->cat_ID .'">' . $cat->cat_name . '</option>';
				}
				$input .= '</select>';
				break;

			case 'thumbnails':
				$input = '<select name="' . $field_name . '" id="' .$id . '" ' . $attributes . '>';
				$input .= '<option value="0">&ndash; ' . __( 'Select', 'aus-basic' ) . ' &ndash;</option>';
				foreach ( $this->get_image_sizes() as $thumbnail => $size ) {
					$input .= '<option ' . ( $value == $thumbnail ? 'selected="selected"' : '' ) . ' value="'. $thumbnail . '">' . $thumbnail . ' - ' . $size['width'] . 'x' . $size['height'] . 'px</option>';
				}
				$input .= '</select>';
				break;

			case 'image':
				$input = '<input id="' .$id . '" type="text" size="36" name="' . $field_name . '" placeholder="http://..." value="' . $value . '" />';
				$input .= '<input class="button image-upload" data-field="#' . $id . '" type="button" value="' . __( 'Upload Image', 'aus-basic' ) . '" />';
				break;

			case 'checkbox':
				$input = '<fieldset class="checkbox-label">';
				$input .= '<label title="' . $id . '">';
				$input .= '<input name="' . $field_name . '" id="' .$id . '" type="' .$type . '" value="1"' . $attributes  . ( $value ? 'checked="checked"' : '' ) . ' />';
				$input .= $title;
				$input .= '</label>';
				$input .= '<span class="checkbox' . ( $value ? ' checked' : '' ) . '"></span>';
				$input .= '</fieldset>';
				break;

			default:
			case 'email':
			case 'text':
				$input = '<input name="' . $field_name . '" id="' .$id . '" type="' .$type . '" value="' . $value . '"' . $attributes . ' />';
				break;

		}

		$html  = '';
		$html .= $input;
		if( ! empty( $description ) )
			$html .= '<p class="description">' . $description . '</p>';
		echo $html;
	}

	public function get_image_sizes( $size = '' ) {

		global $_wp_additional_image_sizes;

		$sizes = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach( $get_intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

				$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				$sizes[ $_size ] = array( 
					'width' => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
				);

			}

		}

		// Get only 1 size if found
		if ( $size ) {

			if( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}

		}

		return $sizes;
	}

}