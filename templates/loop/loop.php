<?php
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

if ( $product['hide_coming'] ) {
    return;
}

global $rllc_store_sale_countdown;
$class = '';
if ( $product['is_variation'] && $product['variation_id'] ) {
    $class = ' rllc_ob_product_avariable_detail rllc_ob_product_detail_' . $product['variation_id'];
}
?>
<div class="rllc_ob_wrapper rllc_ob_product_detail<?php echo esc_attr( $class ) ?>">
<?php
do_action( 'rllc_store_sale_countdown_before_render_loop_product' );
if ( isset( $options['sort'], $options['enabled'] ) ) {
    foreach ( $options['sort'] as $element ) {
        $enabled = array_key_exists( $element, $options['enabled'] ) && $options['enabled'][$element] == 'on';
        do_action( 'rllc_store_sale_countdown_before_product_element', $element, $product, $enabled );
        $rllc_store_sale_countdown->rllc_get_template( 'loop/' . $element . '.php', array( 'item' => $product, 'enabled' => $enabled ) );
        do_action( 'rllc_store_sale_countdown_after_product_element', $element, $product, $enabled );
    }
}
do_action( 'rllc_store_sale_countdown_after_render_loop_product' );
?>
</div>