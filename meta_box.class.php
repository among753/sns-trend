<?php
/**
 * Created by JetBrains PhpStorm.
 * User: among753
 * Date: 13/07/11
 * Time: 10:16
 * To change this template use File | Settings | File Templates.
 */

namespace SnsTrend;


/**
 * Class SnsTrendMetaBox
 *
 */
class MetaBox {
	public $id            = '';// HTML 'id' attribute of the edit screen section
	public $title         = 'meta box';// Title of the edit screen section, visible to user
	public $callback      = '';// Function that prints out the HTML for the edit screen section.
	public $screen        = null;// ('post', 'page', 'link', or custom_post_type)
	public $context       = 'advanced';// ('normal', 'advanced', or 'side')
	public $priority      = 'default';// ('high', 'core', 'default' or 'low')
	public $callback_args = null;// function $callback($post, $callback_args)

	public $params = '';
	public $params_default = array(
								'meta_key'    => null, // 登録するmeta_key
								'input_type'  => null, // form input type ('text', 'check', 'radio', 'textarea' 'select')
								'input_value' => '',
								'description' => '',
								'validate'    => array(
										//'length'  => 100,
										//'require' => true
								),
								'ajax'        => false, // 保存にajaxを使うか
							);

	public function __construct( $args ) {
		// to propaty
		foreach ($args as $key => $value) {
			if (isset($this->{$key}) || $key === 'screen' || $key === 'callback_args') {
				$this->{$key} = $value;
			}
		}

		// foreachで回せるように
		if (empty($this->params[0]['meta_key']))
			$this->params = array($this->params);

		// callback を設定
		if (empty($this->callback)) {
			if (empty($this->params[0])) {
				$this->callback = array($this, "_null_callback_html");
			} else {
				$this->callback = array($this, "_default_callback_html");
			}
			$this->callback_args = &$this->params;
		}

		foreach ($this->params as &$param) {
			// $paramのdefaultへの初期化をする
			$param = array_merge($this->params_default, $param);

			// 保存（validate）の登録
			if ($param['ajax']) {
				// 管理画面各ページの <head> 要素に JavaScript を追加するために実行する。
				// #TODO カスタム投稿のみフックできないか
				// hooks: admin_print_scripts, admin_enqueue_scripts, admin_print_scripts-*(ex:widgets.php)
				// sackライブラリでadmin-ajax.phpにAJAXでPOSTするJavaScript関数を<head>にセット
				add_action('admin_enqueue_scripts', array($this, 'myplugin_js_admin_header'));
				// wp_ajax_*アクションを使うことで、リクエスト受信時にプラグインのどのPHP関数を呼び出すかをWordPressに通知することができます。
				// wp_ajax_*(admin-ajax.phpがPOSTで受け取ったaction名)
				add_action('wp_ajax_myplugin_elev_lookup', array($this, 'myplugin_ajax_elev_lookup'));

				// admin-ajax.phpへリクエストを送信し返ってきた情報をもとにページ情報を出力
				// wp-admin/load-script.phpでjQuery本体読み込んでるのでそれより後
				add_action( 'admin_head-post.php', array($this, 'sh_show_json'), 20 );
				add_action( 'admin_head-post-new.php',  array($this, 'sh_show_json'), 20 );
				// json出力
				add_action( 'wp_ajax_sh_get_json', array($this, 'sh_get_json') );
				//add_action( 'wp_ajax_nopriv_sh_get_json', array(&$this, 'sh_get_json') );// use front

			} else {
				// 'publish_'.$this->post_type カスタム投稿タイプが更新、公開された時
				// edit_post, save_post, wp_insert_post
				// 保存時に実行する処理
				add_action('wp_insert_post', array($this, 'save_post_type'));

				// JavaScriptバリデート
				add_action( 'admin_head-post.php', array($this, 'save_validation'), 20 );
				add_action( 'admin_head-post-new.php',  array($this, 'save_validation'), 20 );

			}

		}
		unset($param);

		// meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	public function add_meta_box() {
		add_meta_box($this->id, $this->title, $this->callback, $this->screen, $this->context, $this->priority, $this->callback_args);
	}


	/**
	 * 設定値が無い場合 class の説明を表示
	 */
	public function _null_callback_html($post, $metabox) {
		// $paramsが無い or 足りない時はclassの説明を表示
		?>
		<h4><?php _e("class SnsTrendMetaBoxの説明"); ?></h4>
		<p>
			class SnsTrendMetaBoxとは...
		</p>
		<?php
	}

	/**
	 * callbackが指定なしの場合使用するdefault callback function
	 * add_meta_boxのcallbackで呼ばれる
	 */
	public function _default_callback_html($post, $metabox) {
		//#TODO
		//var_dump($post);
		//var_dump($metabox);

		$params = $metabox['args'];
		foreach ($params as $param) {
			//$paramsをpurseしてinputボックスを出力
			$meta_values = get_post_meta($post->ID, $param['meta_key']);
			if (is_string($param['input_value'])) $param['input_value'] = array($param['input_value']);

			?>
			<div id="<?php esc_attr_e($param['meta_key'] . "_field"); ?>">
				<p><?php esc_html_e($param['description']); ?></p>
				<div id="<?php esc_attr_e($param['meta_key'].'_errmsg'); ?>" style="display: none;"></div>

				<?php
				switch ( $param['input_type'] ) {
					case 'text':
						//#TODO metaが複数の時の処理
						$meta_value = array_pop($meta_values);
						if (!$meta_value) $meta_value = $param['input_value'][0];
						?>

						<label for="<?php esc_attr_e($param['meta_key']); ?>"><?php esc_html_e($param['meta_key']); ?> ：</label>
						<input type="text" name="<?php esc_attr_e($param['meta_key']); ?>" id="<?php esc_attr_e($param['meta_key']); ?>" value="<?php esc_attr_e($meta_value); ?>" style="width: 100%;" />

						<?php
						break;
					case 'checkbox':


						foreach ($param['input_value'] as $key => $value) {
							?>
							<input type="checkbox" name="<?php esc_attr_e($param['meta_key']. "[]"); ?>" value="<?php esc_attr_e($value); ?>" id="<?php esc_attr_e($param['meta_key'].'_'.$key);?>" <?php if ( in_array($value, $meta_values) ) echo 'checked="checked"'; ?> />
							<label for="<?php esc_attr_e($param['meta_key'].'_'.$key);?>"><?php esc_html_e($value); ?></label>


							<?php
						}
						break;
					case 'radio':
						$meta_value = array_pop($meta_values);
						if (!$meta_value) $meta_value = $param['input_value'][0];
						foreach ($param['input_value'] as $key => $value) {
							?>
							<input type="radio" name="<?php esc_attr_e($param['meta_key']); ?>" value="<?php esc_attr_e($value); ?>" id="<?php esc_attr_e($param['meta_key'].'_'.$key);?>" <?php if ($meta_value == $value) echo 'checked="checked"'; ?> />
							<label for="<?php esc_attr_e($param['meta_key'].'_'.$key);?>"><?php esc_html_e($value); ?></label>
							<?php
						}
						break;
					default :
						break;
				}
				?>

				<?php if ($param['ajax']) : ?>
					<div id="json-data"></div>
					<input type="button" name="<?php esc_attr_e('ajax-save-'.$param['meta_key']); ?>" value="<?php _e('save'); ?>" class="button" />
				<?php endif; ?>



			</div>
			<?php

		}
	}


	/**
	 * 投稿保存の後、update_post_meta
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function save_post_type($post_id){
		if (get_post_type() <> $this->screen) return;// 特定のpost_typeのみ
		foreach ($this->params as $param) {
			if (!$param['ajax']) {
				//
				switch ($param['input_type']) {
					case 'checkbox':
						delete_post_meta( $post_id, esc_attr($param['meta_key']));
						if (isset($_POST[$param['meta_key']])) {
							foreach ($_POST[$param['meta_key']] as $value) {
								add_post_meta($post_id, esc_attr($param['meta_key']), esc_attr($value) );
							}
						}
						break;
					case 'text':
					default:
						if (isset($_POST[$param['meta_key']])) {
							update_post_meta($post_id, esc_attr($param['meta_key']), esc_attr($_POST[$param['meta_key']]));
						}
						break;
				}
			}

		}

	}

	/**
	 *
	 */
	public function save_validation() {
		global $post_type, $post;
		if (get_post_type() <> $this->screen) return;// 特定のpost_typeのみ

		$js_str = '';
		foreach ($this->params as $param) {
			if (empty($param['ajax']) && $param['validate']) {

				$validate = $param['validate'];
				switch ($param['input_type']) {
					case 'checkbox':
						break;
					case 'text':
						if ($validate['length']) {
							$js_str .= sprintf(
								"
								if ( $(\"input[name='%s']\").val().length > %d ) {
									errors += \"<p>ながすぎます</p>\";
								}
								",
								esc_js($param['meta_key']),
								$validate['length']
							);
						}
						if ($validate['require']) {
							$js_str .= sprintf(
								"
								if ($(\"input[name='%s']\").val() == '') {
									errors += \"<p>からです。</p>\";
								}
								",
								esc_js($param['meta_key'])
							);
						}
						break;
					default:
						break;
				}

			}
		}

		// jQueryを管理画面で読み込む
		?>
		<script type="text/javascript">
			jQuery(function($){
				$("form").submit(function() {
					var errors = '';

					<?php echo $js_str; ?>


					if (errors) {
						$("#trends_keywords_errmsg").addClass("error").html(errors).show();
						$("input[name='save']").removeClass("button-primary-disabled");
						$("span.spinner").css("display", "none");
						return false;
					} else {
						return true;
					}

				});
			});
		</script>
	<?php
	}

	/**
	 * add_meta_boxのcallbackで呼ばれる
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
		if ($post_type !== $this->screen) return;// 特定のpost_typeのみ

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



}