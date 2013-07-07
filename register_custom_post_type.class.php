<?php
/**
 * Created by JetBrains PhpStorm.
 * User: KS
 * Date: 2013/07/08
 * Time: 0:56
 * To change this template use File | Settings | File Templates.
 */

class RegisterCustomPostType {

	public $post_type = 'book';

	public $meta_name = 'trends_keyword';

	public function __construct() {
		// カスタム投稿タイプ追加
		add_action('init', array(&$this, 'register_post_type'));

		// カスタムフィールド入力ボックス追加
		add_action('admin_init', array(&$this, 'add_meta_box'));

		//add filter to insure the text Book, or book, is displayed when user updates a book
		add_filter('post_updated_messages', array(&$this, 'filter_post_updated_message'));

		//display contextual help for Books
		add_action( 'contextual_help', array(&$this, 'action_contextual_help'), 10, 3 );

	}

	public function register_post_type()
	{
		$labels = array(
			'name' => _x('Books', 'post type general name'),
			'singular_name' => _x('Book', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Book'),
			'edit_item' => __('Edit Book'),
			'new_item' => __('New Book'),
			'view_item' => __('View Book'),
			'search_items' => __('Search Books'),
			'not_found' =>  __('No books found'),
			'not_found_in_trash' => __('No books found in Trash'),
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
			'supports' => array('title','editor','author','thumbnail','excerpt','custom-fields','comments')
		);
		register_post_type($this->post_type, $args);

	}

	public function add_meta_box() {
		add_meta_box($this->meta_name, 'キーワード', array(&$this, 'trends_meta_html'), $this->post_type);
	}

	public function trends_meta_html() {
		global $post;
		$trends_keywords = get_post_custom_values($this->meta_name, $post->ID);
		var_dump($trends_keywords);

		//入力フィールドの表示
		?>
		<input type="hidden" name="events-nonce" id="events-nonce" value="<?php echo wp_create_nonce( 'events-nonce' ) ?>" >
		<style type="text/css">
			#event-meta table th {
				text-align: left;
				font-weight: normal;
				padding-right: 10px;
			}
		</style>
		<div id="trends-meta">
			<table>
			<?php if($trends_keywords) : ?>
			<?php foreach($trends_keywords as $value) : ?>
				<tr>
					<th>キーワード</th>
					<td><input name="trends_keyword[]" class="trends-meta" id="trends_keyword" value="<?php echo $value; ?>"></td>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
				<tr>
					<th>キーワード</th>
					<td><input name="trends_keyword[]" class="trends-meta" id="trends_keyword" value="新規入力欄"></td>
				</tr>
			</table>
		</div>
	<?php
	}


	/**
	 * filter: カスタム投稿のメッセージを変更
	 *
	 * @param $messages
	 * @return mixed
	 */
	public function filter_post_updated_message( $messages ) {
		global $post, $post_ID;

		$messages['book'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Book updated. <a href="%s">View book</a>'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Book updated.'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Book restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Book published. <a href="%s">View book</a>'), esc_url( get_permalink($post_ID) ) ),
			7 => __('Book saved.'),
			8 => sprintf( __('Book submitted. <a target="_blank" href="%s">Preview book</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Book scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview book</a>'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Book draft updated. <a target="_blank" href="%s">Preview book</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
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
		if ('book' == $screen->id ) {
			$contextual_help =
				'<p>' . __('Things to remember when adding or editing a book:') . '</p>' .
				'<ul>' .
				'<li>' . __('Specify the correct genre such as Mystery, or Historic.') . '</li>' .
				'<li>' . __('Specify the correct writer of the book.  Remember that the Author module refers to you, the author of this book review.') . '</li>' .
				'</ul>' .
				'<p>' . __('If you want to schedule the book review to be published in the future:') . '</p>' .
				'<ul>' .
				'<li>' . __('Under the Publish module, click on the Edit link next to Publish.') . '</li>' .
				'<li>' . __('Change the date to the date to actual publish this article, then click on Ok.') . '</li>' .
				'</ul>' .
				'<p><strong>' . __('For more information:') . '</strong></p>' .
				'<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>') . '</p>' .
				'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>' ;
		} elseif ( 'edit-book' == $screen->id ) {
			$contextual_help =
				'<p>' . __('This is the help screen displaying the table of books blah blah blah.') . '</p>' ;
		}
		return $contextual_help;
	}

}