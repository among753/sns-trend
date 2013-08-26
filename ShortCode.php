<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2013/08/03
 * Time: 19:04
 */

namespace SnsTrend;
use SnsTrend\Model\Posts;
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

	/**
	 * @var Posts
	 */
	protected $posts;


	//TODO Ajax classを作って移動する？
	const ACTION = "sns_trend_list";

	public function __construct() {

		$this->trends = new Trends();
		$this->posts  = new Posts();

		// TODO JavaScriptをフックで登録 if front </body>の前

		// AJAXの受信側を登録
		add_action('wp_ajax_nopriv_' . self::ACTION, array(&$this, 'getTrendList'));
		add_action('wp_ajax_' . self::ACTION,        array(&$this, 'getTrendList'));




		add_shortcode('sns-trend-blog', array($this, 'setSnsTrendBlog'));



		add_shortcode('sns-trend-list', array($this, 'setSnsTrendList'));
	}

	/**
	 * Ajaxでadmin-ajax.phpへPOST
	 * admin-ajax.phpはdata->action = "hook_name" を見て
	 * "wp_ajax_hook_name" のアクションフックに登録してあるfunctionを実行する
	 * 実行されたfunctionの出力データを success:function(data){} で受け取り処理を行う
	 * TODO FIX DBにAjaxでデータを取得する動作で2sぐらいかかるのでDBへのアクセスを減らす
	 * TODO 10件づつDBに取りに行くのをやめて100件取得して10件表示
	 * TODO 残り20件になったら+100件取りに行く
	 */
	public function setSnsTrendList( $atts ) {
		global $post, $wp_query;

		// ショートコードの引数
		$post_id   = $post->ID;
		$trend_type = Twitter::TYPE;
		$limit = 100;
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
		ob_start();
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
				<ol style="margin-left: 40px"></ol>
				<form action="" name="<?php esc_attr_e($form_id); ?>">
					<?php wp_nonce_field( self::ACTION ); ?>
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
				var $_wpnonce = $show_form.find("input[name='_wpnonce']");
				var $_wp_http_referer = $show_form.find("input[name='_wp_http_referer']");
				var $submit_button = $(<?php echo "'#".esc_attr($submit_id)."'"; ?>);
				$submit_button.loading = false;

				var Twitter = {
					limit : 100,
					offset : 0,
					search: function(args) {
						var defer = $.Deferred();
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								"action"           : "<?php echo self::ACTION; ?>",// action hook
								"post_id"          : "<?php echo esc_js($post_id); ?>",
								"limit"            : this.limit,
								"offset"           : this.offset,
								"_wpnonce"         : $_wpnonce.val(),
								"_wp_http_referer" : $_wp_http_referer.val()
							},
							success: defer.resolve,
							error: defer.reject
						});
						return defer.promise();
					},
					start: function($button) {
						// ボタン処理
						$button.loading = true;
						$button.show_flg = false;
						$button.css({
							'pointerEvents': 'none',
							'color': '#ccc'
						});
					},
					setTweets: function(json) {
//						console.log(json);
//							var json_str = JSON.stringify(json);//Jsonデータを文字列に変換
						var $data = $(json.data);

						// TODO データを全部取得し終わった時の処理を追加

						// li追加
						$show_area.append($data.hide());
						// offsetを設定
						this.offset = $show_area.find("li").length;
					},
					show: function($buffer, $limit) {
						if ($submit_button.show_flg)
							return false;

						$buffer.slideDown("slow", function(){
							Twitter.end($submit_button);
						});

						return $submit_button.show_flg = true;
					},
					end: function($button) {
						// ボタン処理
						$button.css({
							'pointerEvents': 'auto',
							'color': '#000'
						});
						// アーカイブページでは読み込みしない
						if (is_archive)
							$button.remove();

						// loading終了処理
						$button.loading = false;

					}

				};


				$submit_button.click(function(){
					// click後のJavaScript処理
					Twitter.start($submit_button);

					var $buffer = $show_area.find("li:hidden");

					if ($buffer.length <= 10) {
						Twitter.search(5).then(function(data) {
							Twitter.setTweets(data);
							Twitter.show($show_area.find("li:hidden:lt(10)"), 10);
						});
					}

					if ($buffer.length > 0) {
						Twitter.show($show_area.find("li:hidden:lt(10)"), 10);
					}

					return false;

				}).trigger('click');



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
					if ( distanceTop < (Number(distanceShowArea) + Number(height) ) && $submit_button.loading==false && is_archive==false) {
						$submit_button.trigger('click');
					}
				});



			});

		</script>
	<?php
		return ob_get_clean();
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



	public function setSnsTrendBlog( $atts ) {
		global $post;

		// ショートコードの引数
		// TODO global $postのみの表示にする？
		$limit = 10;
		extract(shortcode_atts(
			array(
				'limit'      => $limit
			),
			$atts
		));

		$keywords = $this->posts->meta['trend_keywords'];
		$q = Posts::consolidatedQuery($post->post_title, $post->$keywords);

		// カスタムフィールド単体ならそのまま$post->keyで取得できる。
		// 複数の場合一個しか取れないのでget_post_meta($post->ID, $key)を使う。
		// http://elearn.jp/wpman/column/c20121004_01.html
//		$meta = get_post_meta($post->ID,'radio_test');
//		var_dump($meta);

		// TODO とりあえずRSSを取得してそのまま表示するClass Blogを作成

		// TODO 時系列で扱うのが難しいので関連記事として表示するだけにするか・・
		$Blog = new Blog();

		$blogs = $Blog->search( $q );

//		var_dump($blogs);
		ob_start();
		$Blog->renderBlogList($blogs);

		// TODO 関連ブログを定期的に取得してTrendsテーブルに保存

		// TODO Ajaxで関連ブログ一覧を取得して表示（件数指定）

		return ob_get_clean();
	}





}
