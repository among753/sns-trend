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




		add_shortcode('sns-trend-list', array($this, 'snsTrendList'));
	}

	public function snsTrendList($atts) {
		global $post;
		$post_id   = $post->ID;
		$trend_type = 'twitter';
		$count = 10;
		extract(shortcode_atts(
			array(
				'post_id'    => $post_id,
				'trend_type' => $trend_type,
				'count'      => $count
			),
			$atts
		));

		// TODO post_type post_id で絞り込み検索
		// TODO Ajaxで読み込みを実装
		$this->showTrendList(20);

	}

	/**
	 * Ajaxでadmin-ajax.phpへPOST
	 * admin-ajax.phpはdata->action = "hook_name" を見て
	 * "wp_ajax_hook_name" のアクションフックに登録してあるfunctionを実行する
	 * 実行されたfunctionの出力データを success:function(data){} で受け取り処理を行う
	 */
	public function showTrendList( $limit=30 ) {
		global $post, $wp_query;

		if ( is_archive() ) {
			echo "trendのカウントでも表示しとく？";
//			var_dump($post, $wp_query);
			return false;
		}


		?>
		<div id="show-trend-list">
			<p>ひょうじするとこーーーーー</p>
		</div>
		<form action="" name="load-trend-list">
			<?php wp_nonce_field( self::ACTION ); ?>
			<input type="hidden" name="limit" id="limit" value="<?php esc_attr_e($limit); ?>">
			<input type="hidden" name="offset" id="offset" value="0">
			<input type="submit"  name="load-trend-list-submit" id="load-trend-list-submit"value="<?php _e("更に読み込む", SnsTrend::NAME_DOMAIN);?>">
		</form>
		<script type="text/javascript">
			ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			jQuery(function($){
				// #TODO clickをやめて画面下にスクロールしたら読み込みに変更
				$("#load-trend-list-submit").click(function(){
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							"action" : "<?php echo self::ACTION; ?>",// action hook
							"post_id" : "<?php echo esc_js($post->ID); ?>",
							"limit" : $(this.form).find("input[name='limit']").val(),
							"offset" : $(this.form).find("input[name='offset']").val(),
							"_wpnonce" : $(this.form).find("input[name='_wpnonce']").val(),
							"_wp_http_referer" : $(this.form).find("input[name='_wp_http_referer']").val()
						},
						success: function(json){
							// php処理成功後
//							var json_str = JSON.stringify(json);//Jsonデータを文字列に変換
							$('#show-trend-list').append(json.data);
							// offsetをlimit分増やす
							$("input[name='offset']").val(Number(json.offset) + Number(json.limit));

							$("input[name='load-trend-list-submit']").css({
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


/**
 * unserializeしたいとき文字コードが違う場合などに
 *
 * @param $string
 * @return mixed
 */
function _unserialize($string) {
	$ret = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $string);
	return unserialize($ret);
}
