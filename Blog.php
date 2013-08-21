<?php
/**
 * Created by PhpStorm.
 * User: sasaki
 * Date: 13/08/20
 * Time: 11:20
 */

namespace SnsTrend;


use SnsTrend\Model\Posts;
use SnsTrend\Model\Trends;


/**
 * Class Blog
 * @package SnsTrend
 *
 */
class Blog {


	protected $blogs;

	public function __construct() {

		$this->trends = new Trends();
		$this->posts = new Posts();

	}

	/**
	 * Google Blog Searchにリクエストを送信
	 * RSSを取得する
	 *
	 * @return mixed
	 */
	public function search( $q ) {
		// &tbs=qdr:h //1h以内
		//      sbd:1 //日付順
		$params = array(
			'tbm' => 'blg',
			'output' => 'rss',
			'hl'  => 'ja',
			'ie'  => 'UTF-8',//input encoding
			'oe'  => 'UTF-8',//output encoding
			'q'   => $q,
			'tbs' => 'qdr:m,sbd:1'
		);
		$url = 'https://www.google.co.jp/search?' . http_build_query($params);
//		$url = 'http://news.livedoor.com/topics/rss.xml';
//		var_dump($url);

		if ( defined("WP_PROXY_HOST") ) {
			$proxy = array(
				"http" => array(
					"proxy" => "tcp://" . WP_PROXY_HOST .":".WP_PROXY_PORT,
					'request_fulluri' => true
				)
			);
			// RSS情報を文字列で取得
			$info_xml_str = file_get_contents($url, false, stream_context_create($proxy));
//			var_dump($info_xml_str);
			// XMLオブジェクトに変換
			return $this->blogs = simplexml_load_string($info_xml_str);
		} else {
			return $this->blogs = simplexml_load_file($url);
		}
	}

	public function renderBlogList( $blogs=null ) {
		if ( empty($blogs) )
			$blogs = $this->blogs;

		if ( empty($blogs->channel->item) )
			return false;

		echo "\t\t<dl>".PHP_EOL;
		foreach ($blogs->channel->item as $item) {
			?>
			<dt><a href="<?php echo esc_url($item->link); ?>"><?php echo strip_tags($item->title); ?></a></dt>
			<dd><?php echo strip_tags($item->description, "<b>"); ?></dd>
			<?php
//			var_dump($item->title, $item->link, $item->description );
		}
		echo "\t\t</dl>".PHP_EOL;

	}

}