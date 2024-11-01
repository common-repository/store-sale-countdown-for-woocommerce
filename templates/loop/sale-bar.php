<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

global $product;
if ( ! $enabled || rllc_woosale_get_product_rllc_hide_only_salebar($product) === 'yes' ) {
	return;
}
?>

<span><?php printf( '%s/%s' . esc_html__( ' sold', 'rllc_store_sale_countdown' ), $item['sale'], $item['discount'] ) ?></span>
<div class="rllc_ob_discount">
	<div class="rllc_ob_sale" style="width:<?php echo esc_attr( $item['per_sale'] ) ?>%"></div>
</div>
