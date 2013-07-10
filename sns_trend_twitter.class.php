<?php
/**
 * Created by JetBrains PhpStorm.
 * User: KS
 * Date: 2013/07/10
 * Time: 23:59
 * To change this template use File | Settings | File Templates.
 */

class SnsTrendTwitter {
	public $aaa="aaa";

	public function __construct() {

	}

	public function init() {
		// 管理メニューに追加するフック
		add_action('admin_menu', array(&$this, 'mt_add_pages'));
	}

	public function mt_add_pages() {
		// カスタムのトップレベルメニューにサブメニューを追加:
		add_submenu_page("edit.php?post_type=trend", __('ぺーじたいとる'), __('Data list'), 'administrator', 'sns_trend_data_list', array($this, 'render_trend_data_list'));

	}

	public function render_trend_data_list() {
		//#TODO データの一覧を出力
		?>
		<h2>でーたーーーーーーいちらんーーーー</h2>
		あああああ




		<?php
	}

}