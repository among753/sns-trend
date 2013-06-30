<h2>Edit Trend List</h2>

<?php echo $this->form->create($model->name); ?>
<?php echo $this->form->input('name'); ?>
<?php echo $this->form->input('description'); ?>
<?php echo $this->form->has_many_dropdown('TrendKeyword', $trend_keywords, array('style' => 'width: 200px;', 'empty' => true)); ?>
<?php echo $this->form->input('created'); ?>
<?php echo $this->form->input('modified'); ?>
<?php echo $this->form->end('Update'); ?>

<hr>
<p>ボタンを押すとtwitterAPI search にリクエスト</p>
<p>取得したデータをTrendDataに格納</p>

<?php
//echo "<hr><p>おぶじぇくと</p>"; var_dump($object);
//echo "<hr><p>もでる</p>"; var_dump($model);
?>

<?php


$html_truncater = new HtmlTruncater();



if (isset($_POST['wpnonce']) && wp_verify_nonce($_POST['wpnonce'], 'my-nonce') ) {
	var_dump($_POST);
	var_dump($_GET);
	
}

$html = '<form action="" method="post">';
//$html .= MvcFormTagsHelper::text_input('title');
// $html .= MvcFormTagsHelper::text_input('tagline', array('value' => 'A default value'));
// $html .= MvcFormTagsHelper::checkbox_input('is_public', array('id' => 'public_checkbox', 'label' => 'Public'));

$html .= MvcFormTagsHelper::hidden_input('wpnonce', array('value' => wp_create_nonce('my-nonce')));

$html .= '<input type="submit" value="最新データ取得">';
$html .= '</form>';
echo $html;

var_dump($trend_Datas);

?>

