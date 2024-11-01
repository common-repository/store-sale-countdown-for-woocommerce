<?php
if ( !defined( 'ABSPATH' ) ) {
    exit();
}
class RLLC_RCODE_Shortcodes {
    public static function init() {
        $shortcodes = array(
            'rllc_product_sale',
            'rllc_product_sale_countdown'
        );
        foreach ( $shortcodes as $shortcode ) {
            add_shortcode( $shortcode, array( __CLASS__, $shortcode ) );
        }
    }

    public static function rllc_product_sale( $atts, $content = null ) {
        global $rllc_store_sale_countdown;
        $atts = shortcode_atts( array('columns'=>'4','orderby'=>'title','order'=>'asc','id'=>'','skus'=>''), $atts );
        if ( $atts['id'] ) {
            $atts['ids'] = $atts['id'];
            return WC_Shortcodes::products( $atts );
        }
    }

    public static function rllc_product_sale_countdown( $atts, $content = null ) {
        global $rllc_store_sale_countdown;
        $atts = shortcode_atts( array(
            'id'            => '',
            'variable_id'   => ''
                ), $atts );
        return $rllc_store_sale_countdown->rllc_get_template_content( 'rllc-shortcode-product-sale-countdown.php', $atts );
    }
}
