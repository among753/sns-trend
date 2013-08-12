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


		if ( is_archive() ) {
			// 表示件数を減らす
			$limit = 3;
		}
		$show_id   = "show-trend-list-" . $post_id;
		$form_id   = "load-trend-list" . $post_id;
		$submit_id = "load-trend-list-submit-" . $post_id;
		?>
		<div id="<?php esc_attr_e($show_id); ?>">
			<p>Twitter list</p>
		</div>
		<form action="" name="<?php esc_attr_e($form_id); ?>">
			<?php wp_nonce_field( self::ACTION ); ?>
			<input type="hidden" name="limit" id="limit" value="<?php esc_attr_e($limit); ?>">
			<input type="hidden" name="offset" id="offset" value="0">
			<input type="submit"  name="<?php esc_attr_e($submit_id); ?>" id="<?php esc_attr_e($submit_id); ?>"value="<?php _e("更に読み込む", SnsTrend::NAME_DOMAIN);?>">
		</form>
		<script type="text/javascript">
			ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			jQuery(function($){
				// #TODO clickをやめて画面下にスクロールしたら読み込みに変更
				$(<?php echo "'#".esc_attr($submit_id)."'"; ?>).click(function(){
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							"action"           : "<?php echo self::ACTION; ?>",// action hook
							"post_id"          : "<?php echo esc_js($post_id); ?>",
							"limit"            : $(this.form).find("input[name='limit']").val(),
							"offset"           : $(this.form).find("input[name='offset']").val(),
							"_wpnonce"         : $(this.form).find("input[name='_wpnonce']").val(),
							"_wp_http_referer" : $(this.form).find("input[name='_wp_http_referer']").val()
						},
						success: function(json){
							// php処理成功後
//							var json_str = JSON.stringify(json);//Jsonデータを文字列に変換
							$(<?php echo "'#".esc_attr($show_id)."'"; ?>).append(json.data);
							// offsetをlimit分増やす
							$("input[name='offset']").val(Number(json.offset) + Number(json.limit));// TODO 実liの数を数えた方がいいかも

							// ボタン処理
							$submit_button = $(<?php echo "'#".esc_attr($submit_id)."'"; ?>);
							$submit_button.css({
								<?php if ( is_archive() ) echo "'display': 'none',";/*アーカイブページのときボタンけす*/ ?>
								'pointerEvents': 'auto',
								'color': '#000'
							});
						},
						error: function(){
							alert('error');
						}
					});
					// click後のJavaScript処理
					// ボタン処理
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
