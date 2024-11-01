<?php
if ( !defined( 'ABSPATH' ) ) {
	exit();
}

global $product;

if ( !$enabled || rllc_woosale_get_product_rllc_hide_only_countdown($product) === 'yes' ) {
	return;
}

$time = false;
if ( $item['woosale_start'] && $item['current_time'] < $item['woosale_start'] ) {
	$time = $item['woosale_start'];
} else if ( $item['woosale_end'] && $item['woosale_end'] > $item['current_time'] ) {
	$time = $item['woosale_end'];
}

if ( !$time ) {
	return;
}

$timestamp_remain = $item['woosale_end'] - $item['current_time'];
$time_remain      = gmdate("Y-m-d H:i:s", $timestamp_remain);
?>
<div class="myCounter woosales-counter widget_product_detail" data-time-remain="<?php echo esc_attr($time_remain);?>"  data-timestamp-remain="<?php echo esc_attr( $timestamp_remain); ?>" data-time="" data-speed="500" data-showtext="<?php echo esc_attr( $item['hide_datetext'] ) ?>"></div>