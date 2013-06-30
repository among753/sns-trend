<h2><?php echo MvcInflector::titleize($this->action); ?></h2>

<p>ここにTrendListのselectbox</p>
<p>ボタンを押すとtwitterAPI search にリクエスト</p>
<p>取得したデータをTrendDataに格納</p>

<?php 
//echo "おぶじぇくと"; var_dump($objects);
//echo "もでる"; var_dump($model);
echo "とれんどりすと"; var_dump($trend_lists);
?>

<?php 

var_dump($_POST);

$html = '<form action="" method="post">';
$html .= MvcFormTagsHelper::text_input('title');
$html .= MvcFormTagsHelper::text_input('tagline', array('value' => 'A default value'));
$html .= MvcFormTagsHelper::checkbox_input('is_public', array('id' => 'public_checkbox', 'label' => 'Public'));
$html .= '<input type="submit" value="aaaaaaaa">';
$html .= '</form>';
echo $html;

?>


<?php //foreach($speakers as $object): ?>
	<?php //$this->render_view('speakers/_item', array('locals' => array('object' => $object))); ?>
<?php //endforeach; ?>