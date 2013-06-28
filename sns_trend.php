<?php
/*
Plugin Name: Sns Trend
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Raning
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/


// move core file in this directry
require_once dirname(__FILE__).'/wp_mvc.php';


register_activation_hook(__FILE__, 'sns_trend_activate');
register_deactivation_hook(__FILE__, 'sns_trend_deactivate');

function sns_trend_activate() {
	require_once dirname(__FILE__).'/sns_trend_loader.php';
	$loader = new SnsTrendLoader();
	$loader->activate();
}

function sns_trend_deactivate() {
	require_once dirname(__FILE__).'/sns_trend_loader.php';
	$loader = new SnsTrendLoader();
	$loader->deactivate();
}

?>