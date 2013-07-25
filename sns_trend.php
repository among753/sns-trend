<?php
/*
Plugin Name: SNS Trend
Plugin URI: https://github.com/among753/sns-trend
Description: SNS Trend Ranking
Author: among753
Version: 0.1.0
Author URI: https://github.com/among753
*/

namespace SnsTrend;

define( 'SNS_TREND_ABSPATH', dirname( __FILE__ ) );

//Load required files
require_once SNS_TREND_ABSPATH . "/sns_trend.class.php";
global $sns_trend;
$sns_trend = new SnsTrend();

//require_once SNS_TREND_ABSPATH . "widgets".DIRECTORY_SEPARATOR."popular.php";

require_once SNS_TREND_ABSPATH . "/functions.php";


?>