<?php
/**
 * Created by JetBrains PhpStorm.
 * User: KS
 * Date: 2013/07/08
 * Time: 0:56
 * To change this template use File | Settings | File Templates.
 */

class CustomPostTypeTrend {

	public $post_type = 'trend';

	public $meta_keyword = 'trends_keyword';
	public $meta_keywords = 'trends_keywords';

	public function __construct() {

	}

	public function init() {
		// カスタム投稿タイプ追加
		add_action('init', array(&$this, 'register_post_type'), 0);
		// タクソノミー追加
		add_action('init', array(&$this, 'register_taxonomy'), 1);

		// 'publish_'.$this->post_type カスタム投稿タイプが更新、公開された時
		// edit_post, save_post, wp_insert_post
		// 保存時に実行する処理
		add_action('wp_insert_post', array(&$this, 'save_post_type'));
//		add_filter('wp_insert_post_data', array(&$this, 'save_post_type'), '99', 2);

		// 管理画面各ページの <head> 要素に JavaScript を追加するために実行する。
		// #TODO カスタム投稿のみフックできないか
		// hooks: admin_print_scripts, admin_enqueue_scripts, admin_print_scripts-*(ex:widgets.php)
		// sackライブラリでadmin-ajax.phpにAJAXでPOSTするJavaScript関数を<head>にセット
		add_action('admin_enqueue_scripts', array(&$this, 'myplugin_js_admin_header'));
		// wp_ajax_*アクションを使うことで、リクエスト受信時にプラグインのどのPHP関数を呼び出すかをWordPressに通知することができます。
		// wp_ajax_*(admin-ajax.phpがPOSTで受け取ったaction名)
		add_action('wp_ajax_myplugin_elev_lookup', array(&$this, 'myplugin_ajax_elev_lookup'));


		// admin-ajax.phpへリクエストを送信し返ってきた情報をもとにページ情報を出力
		// wp-admin/load-script.phpでjQuery本体読み込んでるのでそれより後
		add_action( 'admin_head-post.php', array(&$this, 'sh_show_json'), 20 );
		add_action( 'admin_head-post-new.php',  array(&$this, 'sh_show_json'), 20 );
		// json出力
		add_action( 'wp_ajax_sh_get_json', array(&$this, 'sh_get_json') );
		//add_action( 'wp_ajax_nopriv_sh_get_json', array(&$this, 'sh_get_json') );// use front



		//add filter to insure the text Trend, or brend, is displayed when user updates a brend
		add_filter('post_updated_messages', array(&$this, 'filter_post_updated_message'));

		//display contextual help for Trends
		add_action( 'contextual_help', array(&$this, 'action_contextual_help'), 10, 3 );



		require_once SNS_TREND_ABSPATH . "/sns_trend_twitter.class.php";
		$twitter = new SnsTrendTwitter();
		$twitter->init();

	}



	/**
	 * カスタム投稿タイプ登録
	 */
	public function register_post_type() {
		// #TODO label
		$labels = array(
			'name' => _x('Trends', 'post type general name'),
			'singular_name' => _x('Trend', 'post type singular name'),
			'add_new' => _x('Add New', 'brend'),
			'add_new_item' => __('Add New Trend'),
			'edit_item' => __('Edit Trend'),
			'new_item' => __('New Trend'),
			'view_item' => __('View Trend'),
			'search_items' => __('Search Trends'),
			'not_found' =>  __('No brends found'),
			'not_found_in_trash' => __('No brends found in Trash'),
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
		add_meta_box($this->meta_keywords, 'キーワード', array(&$this, 'trends_meta_html'), $this->post_type);
		add_meta_box($this->meta_keyword, 'キーワード(AJAX)', array(&$this, 'trends_meta_html_ajax'), $this->post_type);
	}

	public function trends_meta_html() {
		global $wpdb, $post;
		$trends_keywords = get_post_meta($post->ID, 'trends_keywords', true);
		//var_dump($trends_keywords);

		//入力フィールドの表示
		?>
		<div id="trends_keywords">
			<p><?php _e("キーワードを , 区切りで入力してください。"); ?></p>
			<input type="text" name="trends_keywords" id="trends_keywords" value="<?php echo esc_attr(get_post_meta($post->ID, 'trends_keywords', true)); ?>" style="width: 96%;" />
		</div>
	<?php
	}

	/**
	 * 投稿保存の後、update_post_meta
	 *
	 * @param $post_id
	 */
	public function save_post_type($post_id){
		//#TODO バリデートをwp_postを保存する前にやらなあかんかも。。
		// バリデートは保留　JavaScriptでやる？

		if (get_post_type() <> $this->post_type) return;
		update_post_meta($post_id, $this->meta_keywords, $_POST['trends_keywords']);
	}

	/**
	 * wp_postmetaをajaxで更新できるように（時間があれば実装）
	 */
	public function trends_meta_html_ajax() {
		global $wpdb, $post;
		//meta_idを取得するならこっち
		$trends_keyword_arr = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d", $this->meta_keyword, $post->ID ) );
		//var_dump($trends_keyword_arr);

		//入力フィールドの表示
		wp_nonce_field('trends_keyword-nonce','trends_keyword-nonce');
		?>
		<style type="text/css">
			#trends_keyword-meta table th {
				text-align: left;
				font-weight: normal;
				padding-right: 10px;
			}
		</style>
		<div id="trends_keyword-meta">
			<p>jQuery,ajax()使用</p>
			<table>
				<?php if($trends_keyword_arr) : ?>
					<?php foreach($trends_keyword_arr as $obj) : ?>
						<tr id="trends_keyword[<?php echo esc_attr($obj->meta_id); ?>]">
							<th>キーワード</th>
							<td>
								<input type="text" name="trends_keyword[<?php echo esc_attr($obj->meta_id); ?>][value]" class="trends-meta" id="trends_keyword[<?php echo esc_attr($obj->meta_id); ?>][value]" value="<?php echo esc_attr($obj->meta_value); ?>">
								<input type="button" naem="trends_keyword_edit" value="<?php _e('edit'); ?>" />
								<input type="button" value="<?php _e('delete'); ?>" onclick="hoge(this.form.hogehoge);" /></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<tr>
					<th>キーワード</th>
					<td>
						<input type="text" name="trends_keyword[new]" class="trends-meta" id="trends_keyword" value="新規入力欄">
						<input type="button" name="trends_keyword_button" value="<?php _e('insert'); ?>" class="button" />
						<div id="json-data"></div>
					</td>
				</tr>
			</table>
		</div>
		<hr>
		<div>
			<p>sackモジュール使用</p>
			緯度: <input type="text" name="latitude_field" />
			経度: <input type="text" name="longitude_field" />
			<input type="button" value="Look Up Elevation"
			       onclick="myplugin_ajax_elevation(this.form.latitude_field,this.form.longitude_field,this.form.elevation_field);" /><br>
			高度: <input type="text" name="elevation_field" id="elevation_field" />
		</div>
	<?php
	}



	/**
	 * admin-ajax.phpにPOSTを送る JavaScriptのfunctionを設置する。
	 * admin-ajaxはPOSTを受けて wp_ajax_{$_POST['action']} のhookに設定されてるPHPのfunctionを実行する。
	 * PHPのechoした結果は JavaScriptのfunction 内 $.ajax({…, success: function(data){}, …}) の data に帰ってくる。
	 */
	public function sh_show_json() {
		global $post_type, $post;
		if ($post_type !== $this->post_type) return;// 特定のpost_typeのみ

		// jQueryを管理画面で読み込む
		// wp_print_scripts( array('jquery') );// 依存関係無しに読み込むのでできれば使わない。
		// wp_enqueue_script('jquery');// 読み込み順とか調整して読み込む。管理画面ではどうも効かない。jquery以外はいける。管理画面では load-scripts.php でjquery本体他を読み込んでる。
		// ∴ jQuery読み込み後に設置するのでwp_print_scripts()で強制的にjQueryを読み込んで設置するか
		// admin_head の一番最後 or admin_print_footer_scripts にhookして設置する。
		?>
		<script type="text/javascript">
			//ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';// globalで定義済み
			jQuery(function($){
				$("input[name='trends_keyword_button']").click(function(){
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							"action": "sh_get_json",// 'wp_ajax_sh_get_json'というhookにひっかけてある関数を実行するよう通知
							"post_id" : "<?php echo esc_js($post->ID); ?>",
							"meta_key" : "<?php echo esc_js($this->meta_keyword); ?>",
							"meta_value" : $(this.form).find("input[name='trends_keyword\\[new\\]']").val(),
							"trends_keyword-nonce" : $(this.form).find("input[name='trends_keyword-nonce']").val(),
							"_wp_http_referer" : $(this.form).find("input[name='_wp_http_referer']").val()
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

	public function sh_get_json() {
		//#TODO ここで$_POSTを受けてpost_metaを登録する
		if (! wp_verify_nonce($_POST['trends_keyword-nonce'], 'trends_keyword-nonce') ) die('Security check out');

		//wp_specialchars_decode();
		//html_entity_decode();


		add_post_meta($_POST['post_id'], $_POST['meta_key'], $_POST['meta_value']);

		$array = array( 'test' => esc_js(print_r($_POST, true)));
		$json = json_encode( $array );
		nocache_headers();
		header( "Content-Type: application/json; charset=" . get_bloginfo( 'charset' ) );
		//
		//echo $json;
		die($json);
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