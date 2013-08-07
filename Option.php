<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/23
 * Time: 22:26
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;


/**
 * Class SnsTrendOption
 * @package SnsTrend
 */
class Option {

	public $option_group = 'sns_trend_options_group';


	public function __construct() {
		add_action('admin_init', array($this, 'setting_options_page'));
		add_action('admin_menu', array($this, 'add_options_page'));
	}


	/**
	 * 設定ページのセッティング
	 */
	public function setting_options_page() {
		//#TODO 一般設定セクションをここに作成


		// hidden 'option_page' 'action' '_wpnonce' '_wp_http_referer' settings_fields($option_group) で出力
		register_setting( $this->option_group, 'sns_trend_general' );
		// 一般設定
		add_settings_section('sns_trend_general', __('general'), array($this, 'twitter_section_text'), 'sns_trend_general');
		add_settings_field('color', __('Color'), array($this, 'setting_input'), 'sns_trend_general', 'sns_trend_general',
			array(
				'label_for' => 'color',
				'type' => 'text',
				'option' => 'sns_trend_general',
			)
		);


		// hidden 'option_page' 'action' '_wpnonce' '_wp_http_referer' settings_fields($option_group) で出力
		register_setting( $this->option_group, 'sns_trend_twitter', array($this, 'plugin_options_validate') );
		// セクションを設定 do_settings_sections('twitter') で出力
		add_settings_section('sns_trend_twitter', 'Twitter OAuth settings', array($this, 'twitter_section_text'), 'sns_trend_twitter');
		// フィールドを設定 第4引数で指定した
		add_settings_field('consumer_key', 'Consumer key', array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'text',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'consumer_key'
				),
				'label_for' => 'consumer_key',
			)
		);
		add_settings_field('consumer_secret', 'Consumer secret', array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'text',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'consumer_secret'
				),
				'label_for' => 'consumer_secret',
			)
		);
		add_settings_field('access_token', 'Access token', array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'text',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'access_token'
				),
				'label_for' => 'access_token',
			)
		);
		add_settings_field('access_token_secret', 'Access token secret', array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'text',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'access_token_secret'
				),
				'label_for' => 'access_token_secret',
			)
		);
		add_settings_field('bearer_access_token', null, array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'hidden',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'bearer_access_token'
				),
//				'label_for' => 'bearer_access_token',
			)
		);
		add_settings_field('bearer_access_token_expired', null, array($this, 'setting_input'), 'sns_trend_twitter', 'sns_trend_twitter',
			array(
				'type' => 'hidden',
				'option' => array(
					'key'   => 'sns_trend_twitter',
					'value' => 'bearer_access_token_expired'
				),
//				'label_for' => 'bearer_access_token_expired',
			)
		);
	}
	/**
	 * callback validate sanitize
	 * @param $input
	 * @return mixed
	 */
	function plugin_options_validate($input) {
		$newinput = $input;
		$newinput['text_string'] = trim($input['text_string']);
		if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
			$newinput['text_string'] = '';
		}
		return $newinput;
	}
	/**
	 * callback add_settings_section()
	 * セクションにechoする
	 */
	public function twitter_section_text() {
		_e('<p>Main description of this section here.</p>');
	}
	/**
	 * callback add_settings_field()
	 */
	public function setting_input($args) {
		$type   = $args['type'];
		$option = $args['option'];
		if (is_array($option)) {
			$options = get_option($option['key']);
			$id    = $option['value'];
			$name  = $option['key'] . "[" . $option['value'] . "]";
			$value = ( isset($options[$option['value']]) ) ? $options[$option['value']] : "";
		} else {
			$options = get_option($option);
			$id    = $option;
			$name  = $option;
			$value = $options;
		}
		//$label_for = $args['label_for'];
		//var_dump($options);

		switch ($type) {
			case 'text' :
				$input = sprintf('<input type="%s" id="%s" name="%s" size="60" value="%s">', $type, $id, $name, esc_attr($value));
				break;
			case 'hidden' :
			default :
				$input = sprintf('<input type="%s" id="%s" name="%s" value="%s">', $type, $id, $name, esc_attr($value));
				break;
		}
		echo $input;
	}

	/**
	 * 設定ページ
	 */
	public function add_options_page() {
		//#TODO 設定用ページ
		add_options_page(__('SNS Trend'), __('SNS Trend'), 'administrator', 'sns-trend-options', array($this, 'render_options_page'));
	}
	/**
	 * Render options page.
	 */
	public function render_options_page() {
		global $title;
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>
				<?php _e($title); ?>
			</h2>
			<form method="post"action="options.php">
				<?php settings_fields('sns_trend_options_group'); ?>
				<?php do_settings_sections('sns_trend_general'); ?>
				<?php do_settings_sections('sns_trend_twitter'); ?>
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>">
			</form>
		</div>
	<?php
	}

}



//#TODO sample
// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//

function eg_settings_api_init() {
	// Add the section to reading settings so we can add our
	// fields to it
	add_settings_section('eg_setting_section',
		'Example settings section in reading',
		'\SnsTrend\eg_setting_section_callback_function',
		'reading');

	// Add the field with the names and function to use for our new
	// settings, put it in our new section
	add_settings_field('eg_setting_name',
		'Example setting Name',
		'\SnsTrend\eg_setting_callback_function',
		'reading',
		'eg_setting_section');

	// Register our setting so that $_POST handling is done for us and
	// our callback function just has to echo the <input>
	register_setting('reading','eg_setting_name');
}// eg_settings_api_init()

add_action('admin_init', '\SnsTrend\eg_settings_api_init');

// ------------------------------------------------------------------
// Settings section callback function
// ------------------------------------------------------------------
//
// This function is needed if we added a new section. This function
// will be run at the start of our section
//

function eg_setting_section_callback_function() {
	echo '<p>Intro text for our settings section</p>';
}

// ------------------------------------------------------------------
// Callback function for our example setting
// ------------------------------------------------------------------
//
// creates a checkbox true/false option. Other types are surely possible
//

function eg_setting_callback_function() {
	echo '<input name="eg_setting_name" id="gv_thumbnails_insert_into_excerpt" type="checkbox" value="1" class="code" ' . checked( 1, get_option('eg_setting_name'), false ) . ' /> Explanation text';
}