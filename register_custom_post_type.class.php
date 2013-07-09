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
		add_action('init', array(&$this, 'register_post_type'), 0);
		// タクソノミー追加
		add_action('init', array(&$this, 'register_taxonomy'), 1);

		// 'publish_'.$this->post_type カスタム投稿タイプが更新、公開された時
		// 保存時に実行する処理
		add_action('save_post', array(&$this, 'save_post_type'));

		// 管理画面各ページの <head> 要素に JavaScript を追加するために実行する。
		// #TODO カスタム投稿のみフックできないか
		// sackライブラリでadmin-ajax.phpにAJAXでPOSTするJavaScript関数を<head>にセット
		add_action('admin_print_scripts', array(&$this, 'myplugin_js_admin_header'));
		// wp_ajax_*アクションを使うことで、リクエスト受信時にプラグインのどのPHP関数を呼び出すかをWordPressに通知することができます。
		// wp_ajax_*(admin-ajax.phpがPOSTで受け取ったaction名)
		add_action('wp_ajax_myplugin_elev_lookup', array(&$this, 'myplugin_ajax_elev_lookup'));


		// admin-ajax.phpへリクエストを送信し返ってきた情報をもとにページ情報を出力
		add_action( 'admin_print_scripts', 'sh_show_json' );
		function sh_show_json() {
			// jQuery
			wp_print_scripts( array('jquery') );
			?>
			<script type="text/javascript">
				//ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';// glocalで定義済み
				jQuery(function($){
					$("input[name='trends_keyword_button']").click(function(){
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								"action": "sh_get_json",
								"fuga": "ふが",
								"hoge": "ほげ"
							},
							success: function(data){
								// php処理成功後
								var json_str = JSON.stringify(data);
								$('#json-data').append(json_str);
								$("input[name='trends_keyword_button']").css({
									'pointerEvents': 'auto',
									'color': '#000'
								});
							},
							error: function(){
								alert('error');
							}
						});
						// click後のJavaScript処理
						$(this).css({
							'pointerEvents': 'none',
							'color': '#ccc'
						});
						return false;
					});
				});
			</script>
		<?php
		}

		// json出力
		add_action( 'wp_ajax_sh_get_json', 'sh_get_json' );
		//add_action( 'wp_ajax_nopriv_sh_get_json', 'sh_get_json' );// use front
		function sh_get_json() {

			//#TODO ここで$_POSTを受けてpost_metaを登録する


			$array = array( 'foo' => print_r($_POST, true), 'hoge' => $_POST['hoge'] );
			$json = json_encode( $array );
			nocache_headers();
			header( "Content-Type: application/json; charset=" . get_bloginfo( 'charset' ) );
			echo $json;
			die();
		}

		//add filter to insure the text Book, or book, is displayed when user updates a book
		add_filter('post_updated_messages', array(&$this, 'filter_post_updated_message'));

		//display contextual help for Books
		add_action( 'contextual_help', array(&$this, 'action_contextual_help'), 10, 3 );

	}

	/**
	 * カスタム投稿タイプ登録
	 */
	public function register_post_type() {
		// #TODO label
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
		register_post_type($this->post_type, array(
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
		));
	}

	/**
	 * タクソノミー登録
	 */
	public function register_taxonomy() {
		// #TODO Internationalization
		$labels = array(
			'name' => 'イベントカテゴリー',
			'singular_name' => 'イベントカテゴリー',
			'search_items' =>  'イベントを検索',
			'popular_items' => 'よく使われているイベント',
			'all_items' => 'すべてのイベント',
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => 'イベントカテゴリーの編集',
			'update_item' => '更新',
			'add_new_item' => '新規イベントカテゴリー',
			'new_item_name' => '新しいイベントカテゴリー'
		);
		register_taxonomy('cate_'.$this->post_type, $this->post_type, array(
			'label' => 'イベントカテゴリー',
			'labels' => $labels,
			'hierarchical' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'cate_'.$this->post_type ),
		));
	}

	/**
	 * register_meta_box_cb
	 */
	public function register_meta_box_cb() {
		add_meta_box($this->meta_name, 'キーワード', array(&$this, 'trends_meta_html'), $this->post_type);
	}

	public function trends_meta_html() {

		global $wpdb;
		global $post;
		$trends_keywords = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d", $this->meta_name, $post->ID ) );//meta_idを取得するならこっち

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
					<?php foreach($trends_keywords as $obj) : ?>
						<tr>
							<th>キーワード</th>
							<td><input name="trends_keyword[<?php echo $obj->meta_id ?>]" class="trends-meta" id="trends_keyword" value="<?php echo $obj->meta_value; ?>"><input type="button" value="<?php _e('edit'); ?>" onclick="hoge(this.form.hogehoge);" /><input type="button" value="<?php _e('delete'); ?>" onclick="hoge(this.form.hogehoge);" /></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<tr>
					<th>キーワード</th>
					<td>
						<input name="trends_keyword_input" class="trends-meta" id="trends_keyword" value="新規入力欄"><input name="trends_keyword_button" type="button" value="<?php _e('insert'); ?>" />
						<div id="json-data"></div>
					</td>
				</tr>
			</table>
		</div>

		緯度: <input type="text" name="latitude_field" />
		経度: <input type="text" name="longitude_field" />
		<input type="button" value="Look Up Elevation"
		       onclick="myplugin_ajax_elevation(this.form.latitude_field,this.form.longitude_field,this.form.elevation_field);" />
		高度: <input type="text" name="elevation_field" id="elevation_field" />

	<?php
	}


	/**
	 * 投稿保存時
	 *
	 * @param $post_id
	 */
	public function save_post_type($post_id){
		$test = $post_id;
	}

	/**
	 * sackライブラリ設定
	 */
	public function myplugin_js_admin_header() {
		// JavascriptのSACKライブラリをAjaxに使用
		wp_print_scripts( array( 'sack' ));

		// カスタムJavascript関数の定義
		?>
		<script type="text/javascript">
			//<![CDATA[
			function myplugin_ajax_elevation( lat_field, long_field, elev_field )
			{
				var mysack = new sack( "<?php echo admin_url( 'admin-ajax.php' ); ?>" );

				mysack.execute = 1;
				mysack.method = 'POST';
				mysack.setVar( "action", "myplugin_elev_lookup" );
				mysack.setVar( "latitude", lat_field.value );
				mysack.setVar( "longitude", long_field.value );
				mysack.setVar( "elev_field_id", elev_field.id );
				mysack.encVar( "cookie", document.cookie, false );
				mysack.onError = function() { alert('Ajax error in looking up elevation' )};
				mysack.runAJAX();

				return true;
			}
			//]]>
		</script>
	<?php
	}

	/**
	 * ajaxの返信を受け取る
	 */
	public function myplugin_ajax_elev_lookup() {
		// 送信された情報を格納
		$lat = $_POST['latitude'];
		$long = $_POST['longitude'];
		$field_id = $_POST['elev_field_id'];

		// SnoopyによるURLリクエストを生成
		// http://wpdocs.sourceforge.jp/AJAX_in_Plugins

		// 戻り値としてJavascriptを生成
		die( "document.getElementById('$field_id').value = '$lat$long'" );
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