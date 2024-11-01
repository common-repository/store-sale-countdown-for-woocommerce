<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! $enabled ) { return; }

?>

<h5 class="schedule_text"><?php printf( '%s', rllc_woosale_format_date_time( $item['woosale_start'] ) . esc_html__( ' to ', 'rllc_store_sale_countdown' ) . rllc_woosale_format_date_time( $item['woosale_end'] ) ) ?></h5>