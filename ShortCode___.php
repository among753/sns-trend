<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2013/08/03
 * Time: 19:04
 */

namespace SnsTrend;
use SnsTrend\Model\Trends;

/**
 * Class ShortCode
 * @package SnsTrend
 */
class ShortCode {

	/**
	 * @var Trends
	 */
	protected $trends;


	//TODO Ajax classを作って移動する？
	const ACTION = "sns_trend_list";

	public function __construct() {

		$this->trends = new Trends();

		// TODO JavaScriptをフックで登録 if front </body>の前

		// AJAXの受信側を登録
		add_action('wp_ajax_nopriv_' . self::ACTION, array(&$this, 'getTrendList'));
		add_action('wp_ajax_' . self::ACTION,        array(&$this, 'getTrendList'));




		add_shortcode('sns-trend-list', array($this, 'setSnsTrendList'));
	}

	/**
	 * Ajaxでadmin-ajax.phpへPOST
	 * admin-ajax.phpはdata->action = "hook_name" を見て
	 * "wp_ajax_hook_name" のアクションフックに登録してあるfunctionを実行する
	 * 実行されたfunctionの出力データを success:function(data){} で受け取り処理を行う
	 */
	public function setSnsTrendList( $atts ) {
		global $post, $wp_query;

		// ショートコードの引数
		$post_id   = $post->ID;
		$trend_type = Twitter::TYPE;
		$limit = 10;
		extract(shortcode_atts(
			array(
				'post_id'    => $post_id,
				'trend_type' => $trend_type,
				'limit'      => $limit
			),
			$atts
		));

		//idの値設定
		$show_id   = "show-trend-list-" . $post_id;
		$form_id   = "load-trend-list-" . $post_id;
		$submit_id = "load-trend-list-submit-" . $post_id;

		//アーカイブページでの表示処理
		if ( is_archive() ) {
			$limit = 3; // 表示件数を減らす
		}
		?>
		<style type="text/css">
			<?php echo '#' . esc_attr($show_id); ?> > .scroll {
				height: 580px;
				position: relative;
				width: 100%;
				overflow-x: hidden;
				overflow-y: scroll;
			}
		</style>
		<div id="<?php esc_attr_e($show_id); ?>">
			<p>Twitter list</p>
			<div class="<?php echo ! is_archive() ? 'scroll' : ''; ?>">
				<ol></ol>
				<form action="" name="<?php esc_attr_e($form_id); ?>">
					<?php wp_nonce_field( self::ACTION ); ?>
					<input type="hidden" name="limit" id="limit" value="<?php esc_attr_e($limit); ?>">
					<input type="hidden" name="offset" id="offset" value="0">
					<input type="button"  name="<?php esc_attr_e($submit_id); ?>" id="<?php esc_attr_e($submit_id); ?>"value="<?php _e("更に読み込む", SnsTrend::NAME_DOMAIN);?>" style="width: 100%;line-height: 3em;">
				</form>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(function($){
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

				var is_archive = <?php echo is_archive() ? "true" : "false"; ?>;

				var $show_box = $(<?php echo "'#".esc_attr($show_id)."'"; ?>);
				var $show_area = $show_box.find("ol:first");
				var $show_form = $show_box.find("form:first");
				var $limit = $show_form.find("input[name='limit']");
				var $offset = $show_form.find("input[name='offset']");
				var $_wpnonce = $show_form.find("input[name='_wpnonce']");
				var $_wp_http_referer = $show_form.find("input[name='_wp_http_referer']");
				var $submit_button = $(<?php echo "'#".esc_attr($submit_id)."'"; ?>);
				$submit_button.loading = false;



				if ((!window.firebug || !firebug.firebuglite) &&
					(!window.console || !console.firebuglite)) {
					window.console = {};
					window.console['log'] = function () {}
				}

				Twitter = {
					search: function(data) {

						var defer = $.Deferred();
						$.ajax({
							type:    'POST',
							url:     ajaxurl,
							data:    data,
							success: defer.resolve,
							error:   defer.reject
						});
						return defer.promise();
					},
					show: function(ele, prop, data) {
						console.log(data);
						ele.empty().hide();
						if (!data.error) {
							if (data.results.length) {
								$.each(data.results, function(i, tweet) {
									Twitter.msg(ele, prop, tweet.text);
								});
							} else {
								Twitter.msg(ele, prop, 'no results');
							}
						} else {
							Twitter.msg(ele, prop, data.error);
						}
						ele.fadeIn();
					},
					msg: function(ele, prop, msg) {
						ele.append(prop.before + msg + prop.after);
					}
				};


			// 画面スクロールによる自動ローディング
//				$(window).scroll(function(){
				$show_box.find(".scroll").scroll(function(){
					// 対象までの高さを取得
//					var distanceTop = $submit_button.offset().top - $(window).height();
					var distanceTop = $submit_button.offset().top;
					var distanceShowArea = $show_box.find(".scroll").offset().top;
					var height = $show_box.find(".scroll").height();
					$show_box.find("p:first").text(distanceTop + " | " + (Number(distanceShowArea) + Number(height) ));
					// 対象まで達しているかどうかを判別
//					if ( $(window).scrollTop() > distanceTop && $submit_button.loading==false && is_archive==false) {
					if (distanceTop < (Number(distanceShowArea) + Number(height)) &&
						$submit_button.loading==false &&
						is_archive==false) {
//						$submit_button.trigger('click');
					}
				});

				$submit_button.click(function(){

					var form_data = {
						"action"           : "<?php echo self::ACTION; ?>",// action hook
						"post_id"          : "<?php echo esc_js($post_id); ?>",
						"limit"            : $limit.val(),
						"offset"           : $offset.val(),
						"_wpnonce"         : $_wpnonce.val(),
						"_wp_http_referer" : $_wp_http_referer.val()
					};

					Twitter.search(form_data).then(function(data) {
						Twitter.show($('#tweets'), {
							before: '<li>',
							after: '</li>'
						}, data);
					});


					var show_flg = false;

					var self = this;

					if ( self.show_count === undefined )
						self.show_count = 0;
					self.show_count++;

					// 先読みした分表示
					$show_area.find("li:hidden").each(function(){
						$(this).slideDown("slow");
						show_flg = true;
					});

					// click後のJavaScript処理
					// ボタン処理
					$submit_button.loading = true;
					$(this).css({
						'pointerEvents': 'none',
						'color': '#ccc'
					});

					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							"action"           : "<?php echo self::ACTION; ?>",// action hook
							"post_id"          : "<?php echo esc_js($post_id); ?>",
							"limit"            : $limit.val(),
							"offset"           : $offset.val(),
							"_wpnonce"         : $_wpnonce.val(),
							"_wp_http_referer" : $_wp_http_referer.val()
						},
						success: function(json){
							// php処理成功後
//							var json_str = JSON.stringify(json);//Jsonデータを文字列に変換
							var $data = $(json.data);
							// li追加
							$show_area.append($data.hide());
							// 先読み分を表示しなかった場合表示(2回目は表示しないでストック)
							if ( show_flg==false && self.show_count!==2 ) {
								$data.slideDown("slow");
							}

							// offsetをlimit分増やす
							$offset.val(Number(json.offset) + Number(json.limit));// TODO 実liの数を数えた方がいいかも
							// ボタン処理
							$submit_button.css({
								'pointerEvents': 'auto',
								'color': '#000'
							});
							// アーカイブページでは読み込みしない
							if (is_archive) {
								$submit_button.remove();
							}

							//初期表示時ストックのためキック
							if ( self.show_count === 1 ) {
								$submit_button.trigger('click');
							}

							// loading終了処理
							$submit_button.loading = false;

							// TODO ロード終了後スクロールを1pxでもしないとアクションしないので処理を追加する
							var distanceShowArea = $show_box.find(".scroll").offset().top;
//							$show_box.find(".scroll").animate({scrollTop:distanceShowArea - 1}, 1500);
//							$('html,body').animate({ scrollTop: 0 }, 'slow');
						},
						error: function(){
							alert('error');
						}
					});

					return false;

				}).trigger('click');

			});

		</script>
	<?php


	}

	/**
	 * $_POSTを受けてリストデータをtrendsから取得json dataで返す
	 */
	public function getTrendList() {
		if (! wp_verify_nonce($_POST['_wpnonce'], self::ACTION) ) die('Security check out');

		// TODO trands data 読み込み

		// TODO $_POSTのサニタイズ

		$args = array(
			"wheres" => array(
				'trend_type' => 'twitter',
				'post_id'    => $_POST['post_id']
			),
			"limit" => $_POST['limit'],
			"offset" => $_POST['offset']
		);
		$tweets = $this->trends->get($args);

		// TODO $data に出力を格納
		$data = "";
		foreach ($tweets as $tweet) {
			$trend_data = json_decode($tweet->trend_data);
			$data .= Twitter::render_twitter_list($trend_data, True);

		}
		$args['data'] = $data;

		nocache_headers();
		header( "Content-Type: application/json; charset=" . get_bloginfo( 'charset' ) );
		die( json_encode($args) );
	}

}
