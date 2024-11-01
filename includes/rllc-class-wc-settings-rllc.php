<?php
if ( !defined( 'ABSPATH' ) )
    exit; 

if ( !class_exists( 'RLLC_WC_Settings_RLLC_RCODE' ) ) :
    class RLLC_WC_Settings_RLLC_RCODE extends WC_Settings_Page {
        public function __construct() {
            $this->id = 'rllc';
            $this->label = esc_html__( 'Store Sale CountDown', 'rllc_store_sale_countdown' );

            add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 200 );
            add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
            add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
            add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
            /* Single */
            add_action( 'woocommerce_admin_field_countdown_single_position', array( $this, 'countdown_single_position' ) );
            add_action( 'woocommerce_admin_field_countdown_single_element', array( $this, 'countdown_single_element' ) );
            /* Categories */
            add_action( 'woocommerce_admin_field_countdown_categories_element', array( $this, 'countdown_categories_element' ) );
            add_action( 'woocommerce_admin_field_countdown_categories_position', array( $this, 'countdown_categories_position' ) );
        }

        public function get_sections() {
            $sections = array(
                'rllc_general'          => esc_html__( 'General', 'rllc_store_sale_countdown' ),
                'rllc_product'          => esc_html__( 'Products List', 'rllc_store_sale_countdown' ),
                'rllc_product_single'   => esc_html__( 'Single Product', 'rllc_store_sale_countdown' ),
            );
            return $sections;
        }

        public function output() {
            global $current_section;
            $settings = $this->get_settings( $current_section );
            WC_Admin_Settings::output_fields( $settings );
        }

        public function save() {
            global $current_section;
            $settings = $this->get_settings( $current_section );
            WC_Admin_Settings::save_fields( $settings );
        }

        public function get_settings_rllc_product_single() {
            return
                    array(
                        array(
                            'title' => esc_html__( 'Sale Countdown on Product Detail Page', 'rllc_store_sale_countdown' ),
                            'type' => 'title',
                            'desc' => '',
                            'id' => 'wooscd_detail_product'
                        ),
                        array(
                            'title' => esc_html__( 'Enable', 'rllc_store_sale_countdown' ),
                            'id' => 'rllc_ob_single_enable',
                            'default' => '1',
                            'type' => 'radio',
                            'desc_tip' => esc_html__( 'Enable countdown for single page.', 'rllc_store_sale_countdown' ),
                            'options' => array(
                                '1' => esc_html__( 'Yes', 'rllc_store_sale_countdown' ),
                                '0' => esc_html__( 'No', 'rllc_store_sale_countdown' )
                            ),
                        ),
                        array(
                            'type' => 'countdown_single_position'
                        ),
                        array(
                            'title' => esc_html__( 'Show date text', 'rllc_store_sale_countdown' ),
                            'id' => 'rllc_ob_single_datetext_show',
                            'default' => '1',
                            'type' => 'radio',
                            'desc_tip' => esc_html__( 'Show Days, Hours, Mins, Sec.', 'rllc_store_sale_countdown' ),
                            'options' => array(
                                '1' => esc_html__( 'Yes', 'rllc_store_sale_countdown' ),
                                '0' => esc_html__( 'No', 'rllc_store_sale_countdown' )
                            ),
                        ),
                        array(
                            'type' => 'countdown_single_element'
                        ),
                        array(
                            'type' => 'sectionend',
                            'id' => 'wooscd_detail_product'
                        )
            );
        }

        public function get_settings_rllc_product() {
            return
                    array(
                        array(
                            'title' => esc_html__( 'Sale Countdown on Products List', 'rllc_store_sale_countdown' ),
                            'type' => 'title',
                            'desc' => '',
                            'id' => 'wooscd_rllc_product'
                        ),
                        array(
                            'title' => esc_html__( 'Enable', 'rllc_store_sale_countdown' ),
                            'id' => 'rllc_ob_categories_enable',
                            'default' => '1',
                            'type' => 'radio',
                            'desc_tip' => esc_html__( 'Enable countdown categories page.', 'rllc_store_sale_countdown' ),
                            'options' => array(
                                '1' => esc_html__( 'Yes', 'rllc_store_sale_countdown' ),
                                '0' => esc_html__( 'No', 'rllc_store_sale_countdown' )
                            ),
                        ),
                        array(
                            'type' => 'countdown_categories_position'
                        ),
                        array(
                            'title' => esc_html__( 'Show date text', 'rllc_store_sale_countdown' ),
                            'id' => 'rllc_ob_categories_datetext_show',
                            'default' => '1',
                            'type' => 'radio',
                            'desc_tip' => esc_html__( 'Show Days, Hours, Mins, Sec.', 'rllc_store_sale_countdown' ),
                            'options' => array(
                                '1' => esc_html__( 'Yes', 'rllc_store_sale_countdown' ),
                                '0' => esc_html__( 'No', 'rllc_store_sale_countdown' )
                            ),
                        ),
                        array(
                            'type' => 'countdown_categories_element'
                        ),
                        array(
                            'type' => 'sectionend',
                            'id' => 'wooscd_rllc_product'
                        )
            );
        }

        public function get_settings( $current_section = '' ) {
            if ( is_callable( array( $this, 'get_settings_' . $current_section ) ) ) {
                return call_user_func( array( $this, 'get_settings_' . $current_section ) );
            }

            $rllc_product_categories_select  = array();
            $rllc_product_tag_select        = array();
            $rllc_product_categories        = get_terms( 'product_cat' );
            $rllc_product_tag               = get_terms( 'product_tag' );
            foreach ( $rllc_product_categories as $key => $value ) {
                $rllc_product_categories_select [ $value->term_id ] = $value->name;
            }
            foreach ( $rllc_product_tag as $key => $value ) {
                $rllc_product_tag_select [ $value->term_id ] = $value->name;
            }

            return array(
                array( 'title' => esc_html__( 'Shop Sale and CountDown Settings', 'rllc_store_sale_countdown' ),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'product_sale_options' ),
                array(
                    'title' => esc_html__( 'Enable Coming Schedule', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_coming_schedule',
                    'type' => 'checkbox',
                    'desc' => esc_html__( 'Enable.', 'rllc_store_sale_countdown' ),
                    'default' => 'yes'
                ),
                array(
                    'title' => esc_html__( 'Use color', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_use_color',
                    'default' => '1',
                    'type' => 'radio',
                    'desc_tip' => esc_html__( 'Please select color for countdown.', 'rllc_store_sale_countdown' ),
                    'options' => array(
                        '0' => esc_html__( 'WooCommerce Frontend Styles', 'rllc_store_sale_countdown' ),
                        '1' => esc_html__( 'Custom below', 'rllc_store_sale_countdown' )
                    ),
                ),
                array(
                        'title'   => esc_html__( 'Discount type', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'radio',
                        'id'      => 'rllc_type',
                        'default' => '',
                        'options' => array( '%', 'Fixed For ALL' ),
                    ),
                    array(
                        'title'   => esc_html__( 'Sale type', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'select',
                        'id'      => 'rllc_sale_type',
                        'default' => '',
                        'options' => array( 'Apply For All Day', 'Day Wise' ),
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount',
                        'default' => '',
                        'placeholder'=>'Number Only'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Monday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_1',
                        'default' => '',
                        'placeholder'=>'Number Only : Monday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Tuesday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_2',
                        'default' => '',
                        'placeholder'=>'Number Only : Tuesday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Wednesday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_3',
                        'default' => '',
                        'placeholder'=>'Number Only : Wednesday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Thursday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_4',
                        'default' => '',
                        'placeholder'=>'Number Only : Thursday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Friday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_5',
                        'default' => '',
                        'placeholder'=>'Number Only : Friday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Saturday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_6',
                        'default' => '',
                        'placeholder'=>'Number Only : Saturday'
                    ),
                    array(
                        'title'   => esc_html__( 'Discount amount for Sunday', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'    => 'number',
                        'id'      => 'rllc_discount_amount_7',
                        'default' => '',
                        'placeholder'=>'Number Only : Sunday'
                    ),
                    array(
                        'title'       => esc_html__( 'Sale starts', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'id'          => 'rllc_start',
                        'type'        => 'text',
                        'class'       => 'rllc_datetimepicker',
                        'placeholder' => esc_html__( 'From&hellip; YYYY-MM-DD HH:MM', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'default'     => '',
                    ),
                    array(
                        'title'       => esc_html__( 'Sale ends', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'id'          => 'rllc_end',
                        'type'        => 'text',
                        'class'       => 'rllc_datetimepicker',
                        'placeholder' => esc_html__( 'To&hellip; YYYY-MM-DD HH:MM', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'default'     => '',
                    ),
                    array(
                        'title'    => esc_html__( 'Exclude Sale products', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Exclude products that are already on sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'checkbox',
                        'id'       => 'rllc_exclude_sale',
                        'default'  => 'no',
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Use regular price', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Use regular price for discount price for product that are already on sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'checkbox',
                        'id'       => 'rllc_use_regular_price',
                        'default'  => 'no',
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Exclude Product type', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product type to exclude from sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'multiselect',
                        'id'       => 'rllc_exclude_type',
                        'default'  => '',
                        'class'    => 'chosen_select_nostd',
                        'options'  => wc_get_product_types(),
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Exclude Category', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product categories to exclude from sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'multiselect',
                        'id'       => 'rllc_exclude_cat',
                        'default'  => '',
                        'class'    => 'chosen_select_nostd',
                        'options'  => $rllc_product_categories_select,
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Include Category', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product categories  to include in sale. Only product within this categories will be on sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'multiselect',
                        'id'       => 'rllc_include_cat',
                        'default'  => '',
                        'class'    => 'chosen_select_nostd',
                        'options'  => $rllc_product_categories_select,
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Exclude tag', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product tag to exclude from sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'multiselect',
                        'id'       => 'rllc_exclude_tag',
                        'default'  => '',
                        'class'    => 'chosen_select_nostd',
                        'options'  => $rllc_product_tag_select,
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Include tag', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product tags  to include in sale. Only product within this tags will be on sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'multiselect',
                        'id'       => 'rllc_include_tag',
                        'default'  => '',
                        'class'    => 'chosen_select_nostd',
                        'options'  => $rllc_product_tag_select,
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Exclude products', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select product to exclude from sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'rllc_ajaxproduct',
                        'id'       => 'rllc_exclude_product',
                        'default'  => '',
                        'desc_tip' => true,
                    ),
                    array(
                        'title'    => esc_html__( 'Include products', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Select products to include in sale. Only selsected products will be on sale.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'rllc_ajaxproduct',
                        'id'       => 'rllc_include_product',
                        'default'  => '',
                        'desc_tip' => true,

                    ),
                    array(
                        'title'    => esc_html__( 'Exclude SKU', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Enter SKU to exclude from sale. Use comma for delimiter.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'text',
                        'id'       => 'rllc_exclude_sku',
                        'default'  => '',
                        'desc_tip' => true,
                        'css'      => 'width:100%;',
                    ),
                    array(
                        'title'    => esc_html__( 'Include SKU', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'desc'     => esc_html__( 'Enter SKU to include in sale. Only entered SKU will be on sale. Use comma for delimiter.', 'RLLC_RCODEHUB-WooCommerce-Store-Sale' ),
                        'type'     => 'text',
                        'id'       => 'rllc_include_sku',
                        'default'  => '',
                        'desc_tip' => true,
                        'css'      => 'width:100%;',

                    ),
                array(
                    'title' => esc_html__( 'Time color', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_time_color',
                    'default' => '#000000',
                    'type' => 'text',
                    'class' => 'colorpick',
                    'desc' => esc_html__( 'Set color for time on Countdown.', 'rllc_store_sale_countdown' ),
                ),
                array(
                    'title' => esc_html__( 'Background color', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_background_color',
                    'default' => '#A9A9A9',
                    'type' => 'text',
                    'class' => 'colorpick',
                    'desc' => esc_html__( 'Set background color for time on Countdown.', 'rllc_store_sale_countdown' ),
                ),
                array(
                    'title' => esc_html__( 'Bar Sale color', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_bar_color',
                    'default' => '#ff0000',
                    'type' => 'text',
                    'class' => 'colorpick',
                    'desc' => esc_html__( 'Set bar\'s color what number sales on Countdown bar sale.', 'rllc_store_sale_countdown' ),
                ),
                array(
                    'title' => esc_html__( 'Bar Sale Background color', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_bg_bar_color',
                    'default' => '#006699',
                    'type' => 'text',
                    'class' => 'colorpick',
                    'desc' => esc_html__( 'Set bar\'s background color what number sales on Countdown bar sale.', 'rllc_store_sale_countdown' ),
                ),
                array(
                    'title' => esc_html__( 'Product sale\'s title', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_title_sale',
                    'default' => 'Sale',
                    'type' => 'text',
                    'desc' => esc_html__( 'Title of product what is saling.', 'rllc_store_sale_countdown' )
                ),
                array(
                    'title' => esc_html__( 'Product coming\'s title', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_title_coming',
                    'default' => 'Comming',
                    'type' => 'text',
                    'desc' => esc_html__( 'Title of product what is coming sale.', 'rllc_store_sale_countdown' )
                ),
                array(
                    'title' => esc_html__( 'Bar sale for product variations', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_bar_sale_variations',
                    'default' => '0',
                    'type' => 'radio',
                    'desc_tip' => esc_html__( 'Show product quantity sold with format', 'rllc_store_sale_countdown' ),
                    'options' => array(
                        '0' => esc_html__( 'Private', 'rllc_store_sale_countdown' ),
                        '1' => esc_html__( 'Total variations', 'rllc_store_sale_countdown' ),
                    ),
                ),
                array(
                    'title' => esc_html__( 'Hide product', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_hide_product',
                    'default' => '0',
                    'type' => 'radio',
                    'desc_tip' => esc_html__( 'Product will be hide when time schedule expired.', 'rllc_store_sale_countdown' ),
                    'options' => array(
                        '0' => esc_html__( 'No', 'rllc_store_sale_countdown' ),
                        'draft' => esc_html__( 'Set as Draft', 'rllc_store_sale_countdown' ),
                        'trash' => esc_html__( 'Move to Trash', 'rllc_store_sale_countdown' )
                    ),
                ),
                array(
                    'title' => esc_html__( 'Remove Sale Price', 'rllc_store_sale_countdown' ),
                    'id' => 'rllc_ob_remove_sale_price',
                    'default' => '1',
                    'type' => 'radio',
                    'desc_tip' => esc_html__( 'Sale price will be remove when time schedule expired.', 'rllc_store_sale_countdown' ),
                    'options' => array(
                        '0' => esc_html__( 'No', 'rllc_store_sale_countdown' ),
                        '1' => esc_html__( 'Yes', 'rllc_store_sale_countdown' )
                    ),
                ),
                array(
                    'type' => 'sectionend',
                    'id' => 'product_sale_options'
                )
            );
        }

        public function countdown_single_position() {
            $selected = get_option( 'rllc_ob_detail_position', 0 );
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php _e( 'CountDown position', 'rllc_store_sale_countdown' ) ?></th>
                <td class="forminp">
                    <ul>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="0" <?php checked( $selected, 0 ); ?>/>
                                <?php _e( 'Above tabs area', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="1" <?php checked( $selected, 1 ); ?>/>
                                <?php _e( 'Below tabs area', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="2" <?php checked( $selected, 2 ); ?>/>
                                <?php _e( 'Above short description', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="3" <?php checked( $selected, 3 ); ?>/>
                                <?php _e( 'Below short description', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="4" <?php checked( $selected, 4 ); ?>/>
                                <?php _e( 'Above Add to cart', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="5" <?php checked( $selected, 5 ); ?>/>
                                <?php _e( 'Below Add to cart', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="6" <?php checked( $selected, 6 ); ?>/>
                                <?php _e( 'Above title', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="7" <?php checked( $selected, 7 ); ?>/>
                                <?php _e( 'Below title', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="8" <?php checked( $selected, 8 ); ?>/>
                                <?php _e( 'Above price', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_detail_position" value="9" <?php checked( $selected, 9 ); ?>/>
                                <?php _e( 'Below price', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                    </ul>
                </td>
            </tr>
            <?php
        }

        public function countdown_categories_position() {
            $selected = get_option( 'rllc_ob_categories_position', 0 );
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php _e( 'CountDown position', 'rllc_store_sale_countdown' ) ?></th>
                <td class="forminp">
                    <ul>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_categories_position" value="0" <?php checked( $selected, 0 ); ?>/>
                                <?php _e( 'Above price', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_categories_position" value="1" <?php checked( $selected, 1 ); ?>/>
                                <?php _e( 'Above title', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_categories_position" value="2" <?php checked( $selected, 2 ); ?>/>
                                <?php _e( 'Above Add to Cart', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_categories_position" value="3" <?php checked( $selected, 3 ); ?>/>
                                <?php _e( 'Below Add to Cart', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="rllc_ob_categories_position" value="4" <?php checked( $selected, 4 ); ?>/>
                                <?php _e( 'Above thumbnail', 'rllc_woosales_countdown_position' ); ?>
                            </label>
                        </li>
                    </ul>
                </td>
            </tr>
            <?php
        }

        public function countdown_categories_element() {
            ob_start();
            rllc_woosales_elements_display_sortable( 'rllc_ob_woosales_categories' );
            echo ob_get_clean();
        }

        public function countdown_single_element() {
            ob_start();
            rllc_woosales_elements_display_sortable( 'rllc_ob_woosales_single' );
            echo ob_get_clean();
        }
    }

    endif;

return new RLLC_WC_Settings_RLLC_RCODE();
