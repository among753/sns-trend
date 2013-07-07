<?php
/*
Plugin Name: SNS Trend Develop
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Ranking
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/


//Load required files
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."sns_trend.class.php";
//require_once dirname(__FILE__).DIRECTORY_SEPARATOR."widgets".DIRECTORY_SEPARATOR."popular.php";
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."functions.php";

//Make Instance
global $sns_trend;
$sns_trend = new SnsTrend();
//Register Activation Hook.
register_activation_hook(__FILE__, array($sns_trend, "activate"));
register_deactivation_hook(__FILE__, array($sns_trend, "deactivate"));

?>