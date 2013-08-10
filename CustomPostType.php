<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/08
 * Time: 0:56
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;

use SnsTrend\Model\Trends;


/**
 * Class CustomPostType
 * @package SnsTrend
 */
class CustomPostType {

	const POST_TYPE = 'trend';

	public $meta_box;

	/**
	 * @var Trends
	 */
	protected $trends;

	public function __construct() {

		$this->trends = new Trends();

		$this->add_actions();


		// カスタムポストタイプにメタボックス追加
		$params = array(
			array(
				'meta_key'   => '_trend_keywords',
				'input_type' => 'text',
				'input_value' => '',
				'description' => __("検索ワードをカンマで区切って入力してください。"),
				'validate'   => array(
					'length'  => 100,
//					'require' => true
				),
//				'ajax'          => false, // 保存にajaxを使うか
			),
			array(
				'meta_key'   => 'radio_test',
				'input_type' => 'radio',
				'input_value' => array('ra-menn',"afdsfasd","あああああ"),
				'description' => __("検索ワードを選んでください。"),
				'validate'   => array(
					'length'  => 100,
					'require' => true
				),
				'ajax'          => false, // 保存にajaxを使うか
			),
			array(
				'meta_key'   => 'checkbox_test',
				'input_type' => 'checkbox',
				'input_value' => array('wattu',"bbbb","あああああいいい"),
				'description' => __("検索ワードを選んでください。（複数可）"),
				'validate'   => array(
					'length'  => 100,
					'require' => true
				),
				'ajax'          => false, // 保存にajaxを使うか
			),
		);
		$this->meta_box = new MetaBox(array(
			'id'            => 'meta_keywords',
			'title'         => _x('キーワード', 'word hosoku'),
			'params'         => $params,
//			'callback'      => 'trends_meta_html',
			'screen'        => self::POST_TYPE,
			'context'       => 'advanced',
			'priority'      => 'default',
			'callback_args' => null
		));

	}

	public function add_actions() {
		// カスタム投稿タイプ追加
		add_action('init', array(&$this, 'register_post_type'), 0);
		// タクソノミー追加
		add_action('init', array(&$this, 'register_taxonomy'), 0);

		// 管理画面一覧のタイトルに項目追加
		add_filter( "manage_edit-". self::POST_TYPE ."_columns", array($this, 'manage_edit_columns') );
		// trend_data の内容表示
		add_action( 'manage_posts_custom_column', array($this, 'manage_posts_custom_column'),null, 2);

		//add filter to insure the text Trend, or trend, is displayed when user updates a trend
		add_filter('post_updated_messages', array(&$this, 'filter_post_updated_message'));
		//display contextual help for Trends
		add_action( 'contextual_help', array(&$this, 'action_contextual_help'), 10, 3 );

	}

	/**
	 * カスタム投稿タイプ登録
	 */
	public function register_post_type() {
		// #TODO label
		$labels = array(
			'name' => _x('Trends', 'post type general name'),
			'singular_name' => _x('Trend', 'post type singular name'),
			'add_new' => _x('Add New', 'trend'),
			'add_new_item' => __('Add New Trend'),
			'edit_item' => __('Edit Trend'),
			'new_item' => __('New Trend'),
			'view_item' => __('View Trend'),
			'search_items' => __('Search Trends'),
			'not_found' =>  __('No trends found'),
			'not_found_in_trash' => __('No trends found in Trash'),
			'parent_item_colon' => ''
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array('with_front' => false ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','author','thumbnail','excerpt','custom-fields','comments'),
			'register_meta_box_cb' => array(&$this, 'register_meta_box_cb')
		);
		register_post_type(self::POST_TYPE, $args);
	}

	/**
	 * タクソノミー登録
	 */
	public function register_taxonomy() {
		// http://codex.wordpress.org/Function_Reference/register_taxonomy
		$labels = array(
			'name'              => _x( 'Genres', 'taxonomy general name' ),
			'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Genres' ),
			'all_items'         => __( 'All Genres' ),
			'parent_item'       => __( 'Parent Genre' ),
			'parent_item_colon' => __( 'Parent Genre:' ),
			'edit_item'         => __( 'Edit Genre' ),
			'update_item'       => __( 'Update Genre' ),
			'add_new_item'      => __( 'Add New Genre' ),
			'new_item_name'     => __( 'New Genre Name' ),
			'menu_name'         => __( 'Genre' ),
		);
		$args = array(
			'labels'            => $labels,
			'public'            => true,// set: 'show_ui', 'show_in_nav_menus', 'show_tagcloud'
			'show_admin_column' => true,// taxonomy columns on associated post-types. (Available since 3.5)
			'hierarchical'      => true,
//			'update_count_callback' => '',// teamが増減した時に呼ばれる関数。post_type共通でtaxonomyを使う場合とか？
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'cate_'.self::POST_TYPE ),
		);
		register_taxonomy('cate_'.self::POST_TYPE, self::POST_TYPE, $args);
	}

	/**
	 * register_meta_box_cb
	 */
	public function register_meta_box_cb() {
	}


	/**
	 * 項目：trend_data追加
	 *
	 * @param array $columns
	 * @return array
	 */
	function manage_edit_columns($columns) {
		// titleの次にtrend_dataを入れる
		$columns_new = array();
		foreach ($columns as $key => $value) {
			$columns_new[$key] = $value;
			if ($key == 'title')
				$columns_new['trend_data'] = "Trend Data";
		}
		return $columns_new;
	}

	/**
	 * 項目：trend_dataの内容を表示
	 *
	 * @param string $column_name
	 * @param int $post_id
	 */
	function manage_posts_custom_column( $column_name, $post_id ) {
		if( $column_name == 'trend_data' ) {
			$page_path = sprintf(
				'edit.php?post_type=%1$s&page=%2$s&action=save&post=%3$s&trend_type=%4$s&wp_nonce=%5$s',
				self::POST_TYPE,
				Data::PAGE,
				$post_id,
				'twitter',
				'1374812304'//TODO wp_nonceとか使う？一回だけ保存する
			);
			$admin_url = admin_url( $page_path );
			printf('<a href="%s">twitterデータ取得</a>', $admin_url);
		}
	}


	/**
	 * filter: カスタム投稿のメッセージを変更
	 *
	 * @param $messages
	 * @return mixed
	 */
	public function filter_post_updated_message( $messages ) {
		global $post, $post_ID;

		$messages['trend'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Trend updated. <a href="%s">View trend</a>'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Trend updated.'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Trend restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Trend published. <a href="%s">View trend</a>'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Trend saved.'),
			8 => sprintf( __('Trend submitted. <a target="_blank" href="%s">Preview trend</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Trend scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview trend</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Trend draft updated. <a target="_blank" href="%s">Preview trend</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * filter:ヘルプを編集
	 *
	 * @param $contextual_help
	 * @param $screen_id
	 * @param $screen
	 * @return string
	 */
	public function action_contextual_help($contextual_help, $screen_id, $screen) {
		//$contextual_help .= var_dump($screen); // use this to help determine $screen->id
		if ('trend' == $screen->id ) {
			$contextual_help =
				'<p>' . __('Things to remember when adding or editing a trend:') . '</p>' .
				'<ul>' .
				'<li>' . __('Specify the correct genre such as Mystery, or Historic.') . '</li>' .
				'<li>' . __('Specify the correct writer of the trend.  Remember that the Author module refers to you, the author of this trend review.') . '</li>' .
				'</ul>' .
				'<p>' . __('If you want to schedule the trend review to be published in the future:') . '</p>' .
				'<ul>' .
				'<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
				'<li>' . __('Change the date to the date to actual publish this article, then click on Ok.') . '</li>' .
				'</ul>' .
				'<p><strong>' . __('For more information:') . '</strong></p>' .
				'<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>') . '</p>' .
				'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>' ;
		} elseif ( 'edit-trend' == $screen->id ) {
			$contextual_help =
				'<p>' . __('This is the help screen displaying the table of trends blah blah blah.') . '</p>' ;
		}
		return $contextual_help;
	}

}
