<?php
if ( !defined( 'ABSPATH' ) || !class_exists( 'WC_Widget' ) ) {
    exit;
}
class RLLC_RCODE_Widget_Sale_Products extends WC_Widget {
    public function __construct() {
        $this->widget_cssclass    = 'woocommerce widget_products rllc_widget_sale_products';
        $this->widget_description = esc_html__( 'Display a list of your sale products countdown on your site.', 'rllc_store_sale_countdown' );
        $this->widget_id    = 'rllc_sale_products';
        $this->widget_name  = esc_html__( 'WooCommerce Sale Products Countdown', 'rllc_store_sale_countdown' );
        $this->settings     = array(
            'title' => array(
                'type' => 'text',
                'std' => esc_html__( 'Products', 'rllc_store_sale_countdown' ),
                'label' => esc_html__( 'Title', 'rllc_store_sale_countdown' )
            ),
            'products' => array(
                'type' => 'products',
                'std' => array(),
                'label' => esc_html__( 'Products On Sale', 'rllc_store_sale_countdown' )
            ),
            'show_title' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Show title products', 'rllc_store_sale_countdown' )
            ),
            'show_rating' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Show rating products', 'rllc_store_sale_countdown' )
            ),
            'show_price' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Show price products', 'rllc_store_sale_countdown' )
            ),
            'show_image' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Show image products', 'rllc_store_sale_countdown' )
            ),
            'show_link' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Linkable products', 'rllc_store_sale_countdown' )
            ),
            'show_button' => array(
                'type' => 'checkbox',
                'std' => 1,
                'label' => esc_html__( 'Show Add To Cart Button', 'rllc_store_sale_countdown' )
            ),
            'product_image' => array(
                'type' => 'select',
                'std' => 'shop_catalog',
                'options' => array(
                    'shop_catalog' => esc_html__( 'Shop Catalog', 'rllc_store_sale_countdown' ),
                    'shop_single' => esc_html__( 'Single Product Image', 'rllc_store_sale_countdown' ),
                    'shop_thumbnail' => esc_html__( 'Shop Thumnail', 'rllc_store_sale_countdown' )
                ),
                'label' => esc_html__( 'Product Image', 'rllc_store_sale_countdown' )
            )
        );

        parent::__construct();
        /* products field */
        add_action( 'woocommerce_widget_field_products', array( $this, 'products_field' ), 10, 4 );
        add_filter( 'woocommerce_widget_settings_sanitize_option', array( $this, 'update_widget' ), 10, 4 );
    }

    public function products_field( $key, $value, $setting, $instance ) {
        $class = isset( $setting['class'] ) ? $setting['class'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
            <select class="widefat <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>[]" multiple>
                <?php foreach ( wc_get_product_ids_on_sale() as $product_id ) : ?>
                    <?php if ( get_post_type( $product_id ) !== 'product' ) continue; ?>
                    <option value="<?php echo esc_attr( $product_id ); ?>" <?php echo in_array( $product_id, $value ) ? ' selected' : ''; ?>><?php echo esc_html( get_the_title( $product_id ) ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function update_widget( $value, $new_instance, $key, $setting ) {
        if ( $key !== 'products' ) {
            return $value;
        }
        return $new_instance[$key];
    }

    public function rllc_get_products( $args = array(), $instance = array() ) {
        $query_args = array(
            'post_type'     => 'product',
            'post_status'   => 'publish'
        );
        if ( !isset( $instance['products'] ) ) {
            foreach ( wc_get_product_ids_on_sale() as $product_id ) {
                if ( get_post_type( $product_id ) !== 'product' ) {
                    continue;
                }
                $instance['products'][] = $product_id;
            }
        }
        $query_args['post__in'] = $instance['products'];
        return new WP_Query( apply_filters( 'rllc_store_sale_countdown_products_widget_query_args', $query_args ) );
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) {
            return;
        }

        ob_start();
        do_action( 'rllc_store_sale_countdown_before_widget_sale_product', $args, $instance );
        if ( ( $products = $this->rllc_get_products( $args, $instance ) ) && $products->have_posts() ) {
            $this->widget_start( $args, $instance );
            ?>

            <div class="woocommerce rllc_ob_widget">
                <?php
                woocommerce_product_loop_start();
                while ( $products->have_posts() ) {
                    $products->the_post();
                    wc_rllc_get_template_part( 'content', 'product' );
                }
                woocommerce_product_loop_end();
                ?>
            </div>
            <?php
            $this->widget_end( $args );
        }
        wp_reset_postdata();
        do_action( 'rllc_store_sale_countdown_after_widget_sale_product', $args, $instance );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}