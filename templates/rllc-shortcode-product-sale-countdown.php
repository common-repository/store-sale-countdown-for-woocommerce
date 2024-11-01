<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

$product = wc_get_product( $id );
if ( $product->post->post_status !== 'publish' || $product->post->post_type !== 'product' ) {
	return;
}

$turn_off_countdown = get_post_meta( $product->id, '_turn_off_countdown', true );
if ( $turn_off_countdown ) { return; }

$expired_time = $time_from = $time_end = $_woosale_from_time = $_woosale_to_time = '';
if ( $product->product_type === 'variable' ) {
	$_product_variables = $product->get_available_variations();
	foreach ( $_product_variables as $variable ) {
		if ( $variable_id && $variable_id == $variable['variation_id'] ) {
			$product = wc_get_product( $variable_id ); break;
		} else {
			$product = wc_get_product( $variable['variation_id'] ); break;
		}
	}
}
if ( $product ) {
	$time_from          = $product->sale_price_dates_from;
	$time_end           = $product->sale_price_dates_to;
	$_woosale_from_time = $product->woosale_from_time;
	$_woosale_to_time   = $product->woosale_to_time;
}

$current_time = strtotime( current_time( 'mysql' ) );
if ( $time_from ){
	if ( $_woosale_from_time ) {
		$time_from = rllc_woosales_add_specified_time( $time_from, $_woosale_from_time );
	}
	if ( $current_time < $time_from ) {
		$expired_time = $time_from;
	}
}
if ( $time_end ){
	if ( $_woosale_to_time ) {
		$time_end = rllc_woosales_add_specified_time( $time_end, $_woosale_to_time );
	}
	if ( $current_time < $time_end && $current_time > $time_from ) {
		$expired_time = $time_end;
	}
}

if ( ! $expired_time ) { return; }
$date = new DateTime( date( 'Y-m-d H:i:s', $expired_time ), new DateTimeZone( rllc_woosales_get_timezone_string() ) );
?>

<div class="shortcode_product">
	<div class="myCounter woosales-counter widget_product_detail" data-time="<?php echo esc_attr( $date->format( DATE_ATOM ) ) ?>" data-speed="500"></div>
</div>