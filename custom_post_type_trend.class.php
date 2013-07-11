<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 2013/07/08
 * Time: 0:56
 * To change this template use File | Settings | File Templates.
 */

class CustomPostTypeTrend {

	public $post_type = 'trend';

	public function __construct($post_type) {
		$this->post_type = $post_type;
		$this->add_actions();
	}

	public function add_actions() {
		// カスタム投稿タイプ追加
		add_action('init', array(&$this, 'register_post_type'), 0);
		// タクソノミー追加
		add_action('init', array(&$this, 'register_taxonomy'), 0);

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
		register_post_type($this->post_type, $args);
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
			'rewrite'           => array( 'slug' => 'cate_'.$this->post_type ),
		);
		register_taxonomy('cate_'.$this->post_type, $this->post_type, $args);
	}

	/**
	 * register_meta_box_cb
	 */
	public function register_meta_box_cb() {
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