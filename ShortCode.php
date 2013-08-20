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
		} else {
		?>
		<style type="text/css">
			<?php echo '#' . esc_attr($show_id); ?> {
				height: 580px;
				position: relative;
				width: 100%;
				overflow-x: hidden;
				overflow-y: scroll;
			}
		</style>
		<?php } ?>
		<div id="<?php esc_attr_e($show_id); ?>">
			<p>Twitter list</p>
			<ol></ol>
			<form action="" name="<?php esc_attr_e($form_id); ?>">
				<?php wp_nonce_field( self::ACTION ); ?>
				<input type="hidden" name="limit" id="limit" value="<?php esc_attr_e($limit); ?>">
				<input type="hidden" name="offset" id="offset" value="0">
				<input type="button"  name="<?php esc_attr_e($submit_id); ?>" id="<?php esc_attr_e($submit_id); ?>"value="<?php _e("更に読み込む", SnsTrend::NAME_DOMAIN);?>" style="width: 100%;line-height: 3em;">
			</form>
		</div>
		<script type="text/javascript">
			ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			jQuery(function($){

				var is_archive = <?php echo is_archive() ? "true" : "false"; ?>;

				var $submit_button = $(<?php echo "'#".esc_attr($submit_id)."'"; ?>);
				$submit_button.loading = false;
				var $show_area = $(<?php echo "'#".esc_attr($show_id)." ol'"; ?>);

			// 画面スクロールによる自動ローディング
				$(window).scroll(function(){
					// 対象までの高さを取得
					var distanceTop = $submit_button.offset().top - $(window).height();
					// 対象まで達しているかどうかを判別
					if ( $(window).scrollTop() > distanceTop && $submit_button.loading==false && is_archive==false) {
//						$submit_button.trigger('click');
					}
				});

				$submit_button.click(function(){
					var $limit = $(this.form).find("input[name='limit']");
					var $offset = $(this.form).find("input[name='offset']");
					var $_wpnonce = $(this.form).find("input[name='_wpnonce']");
					var $_wp_http_referer = $(this.form).find("input[name='_wp_http_referer']");

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
							for(var i=0; i < $data.length; i++) {
								$show_area.append($($data[i]).hide()).find("li").slideDown("slow");
							}
							// offsetをlimit分増やす
							$offset.val(Number(json.offset) + Number(json.limit));// TODO 実liの数を数えた方がいいかも
							// ボタン処理
							$submit_button.css({
								'pointerEvents': 'auto',
								'color': '#000'
							});
							// アーカイブページでは読み込みしない
							if (is_archive)
								$submit_button.remove();
							// loading終了処理
							$submit_button.loading = false;
						},
						error: function(){
							alert('error');
						}
					});
					// click後のJavaScript処理
					// ボタン処理
					$submit_button.loading = true;
						$(this).css({
						'pointerEvents': 'none',
						'color': '#ccc'
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

		$Blog->renderBlogList($blogs);


		// TODO 関連ブログを定期的に取得してTrendsテーブルに保存


		// TODO Ajaxで関連ブログ一覧を取得して表示（件数指定）






	}





}
