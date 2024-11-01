<?php
if ( !class_exists( 'RLLC_RCODE_Admin' ) ) {
    class RLLC_RCODE_Admin {
        public function __construct() {
            /* setting */
            add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'rllc_add_settings_page' ) );
            add_action( 'woocommerce_update_options_rllc', array( $this, 'rllc_save_setting' ) );
            /* product and order */
            // add_action( 'woocommerce_order_items_table', array( $this, 'rllc_woo_update_sale' ) );
            /* simple product */
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'rllc_woo_add_custom_general_fields' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'rllc_woo_add_custom_general_fields_save' ) );

            /* variable product */
            add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'rllc_woo_add_custom_product_variable' ), 10, 3 );
            add_action( 'woocommerce_save_product_variation', array( $this, 'rllc_woo_add_custom_product_variable_save' ), 10, 2 );
			add_action( 'woocommerce_bulk_edit_variations', array( $this, 'rllc_woocommerce_bulk_edit_variations_callback'), 10, 4);

            /* sync product variable */
            add_action( 'woocommerce_save_product_variation', array( $this, 'rllc_woo_add_custom_product_variable_sync' ), 20, 2 );
            add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'rllc_bulk_variable_options' ) );
            add_action( 'woocommerce_order_status_changed', array( $this, 'rllc_woo_sale_update_quantity_sale' ) );

            /* Add setting in Product edit */
            add_filter( 'product_type_options', array( $this, 'rllc_product_type_options' ) );
            add_filter( 'plugin_action_links_' . basename( RLLC_RCODE_DIR ) . '/' . basename( __FILE__ ), array( $this, 'rllc_settings_link' ) );
            add_action( 'manage_product_posts_columns', array( $this, 'rllc_product_columns' ) );
            add_action( 'manage_product_posts_custom_column', array( $this, 'rllc_product_column_content' ), 10, 2 );

            add_action( 'plugin_action_links', array( $this, 'rllc_add_plugin_link' ), 10, 2 );
        }

        static function rllc_add_settings_page( $settings ) {
            $settings[] = include_once( "rllc-class-wc-settings-rllc.php" );
            return $settings;
        }

        function rllc_add_plugin_link( $plugin_actions, $plugin_file ) {
            $new_actions = array();
            if ( $plugin_file=='rllc-store-sale-countdown/rllc-store-sale-countdown.php' ) {
                $new_actions['cl_settings'] = sprintf( __( '<a href="%s">Settings</a>', 'rllc_store_sale_countdown' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=rllc&section=rllc_general' ) ) );
            }
            return array_merge( $new_actions, $plugin_actions );
        }

        public function rllc_woo_update_sale( $order ) {
            global $rllc_store_sale_countdown;
            $order_id   = $order->id;
            $order      = new WC_Order( $order_id );
            $items      = $order->get_items();
            foreach ( $items as $item ) {
                if ( $item['variation_id'] == 0 ) {
                    $time_from  = get_post_meta( $item['product_id'], '_sale_price_dates_from', true );
                    $time_end   = get_post_meta( $item['product_id'], '_sale_price_dates_to', true );
                    $_woosale_from_time = get_post_meta( $item['product_id'], '_woosale_from_time', true );

                    if ( !$time_from || !$time_end ) {
                        continue;
                    }
                    if ( $_woosale_from_time != '' ) {
                        $time_from = rllc_woosales_add_specified_time( $time_from, $_woosale_from_time );
                    }
                    $current_time = strtotime( current_time( 'Y-m-d G:i:s' ) );
                    if ( $time_from > $current_time ) {
                        continue;
                    }

                    $woocommerce_quantity_sale      = get_post_meta( $item['product_id'], '_quantity_sale', true );
                    $woocommerce_rllc_quantity_discount  = get_post_meta( $item['product_id'], '_rllc_quantity_discount', true );
                    $woocommerce_quantity_sale += $item['qty'];
                    if ( $order->post_status == 'wc-completed' ) {
                        update_post_meta( $item['product_id'], '_quantity_sale', esc_attr( $woocommerce_quantity_sale ) );
                    }
                    if ( $woocommerce_rllc_quantity_discount <= $woocommerce_quantity_sale && $woocommerce_rllc_quantity_discount ) {

                        $woocommerce_regular_price = get_post_meta( $item['product_id'], '_regular_price', true );
                        delete_post_meta( $item['product_id'], '_sale_price' );
                        delete_post_meta( $item['product_id'], '_sale_price_dates_from' );
                        delete_post_meta( $item['product_id'], '_sale_price_dates_to' );
                        delete_post_meta( $item['product_id'], '_woosale_from_time', '' );
                        delete_post_meta( $item['product_id'], '_woosale_to_time', '' );
                        update_post_meta( $item['product_id'], '_price', esc_attr( $woocommerce_regular_price ) );
                    }
                } else {
                    $time_from  = get_post_meta( $item['variation_id'], '_sale_price_dates_from', true );
                    $time_end   = get_post_meta( $item['variation_id'], '_sale_price_dates_to', true );
                    $_woosale_from_time = get_post_meta( $item['variation_id'], '_woosale_from_time', true );

                    if ( !$time_from || !$time_end ) {
                        continue;
                    }
                    if ( $_woosale_from_time != '' ) {
                        $time_from = rllc_woosales_add_specified_time( $time_from, $_woosale_from_time );
                    }
                    $current_time = strtotime( current_time( 'Y-m-d G:i:s' ) );
                    if ( $time_from > $current_time ) {
                        continue;
                    }

                    $woocommerce_quantity_sale      = rllc_woosale_get_product_quantity_sale($item['variation_id']);
                    $woocommerce_rllc_quantity_discount  = rllc_woosale_get_product_rllc_quantity_discount($item['variation_id']);
                    $woocommerce_quantity_sale += $item['qty'];
                    if ( $order->post_status == 'wc-completed' ) {
                        update_post_meta( $item['variation_id'], '_quantity_sale', esc_attr( $woocommerce_quantity_sale ) );
                    }
                    if ( $woocommerce_rllc_quantity_discount <= $woocommerce_quantity_sale && $woocommerce_rllc_quantity_discount ) {
                        $woocommerce_regular_price = get_post_meta( $item['variation_id'], '_regular_price', true );
                        delete_post_meta( $item['variation_id'], '_sale_price' );
                        delete_post_meta( $item['variation_id'], '_sale_price_dates_from' );
                        delete_post_meta( $item['variation_id'], '_sale_price_dates_to' );
                        delete_post_meta( $item['variation_id'], '_woosale_from_time', '' );
                        delete_post_meta( $item['variation_id'], '_woosale_to_time', '' );
                        update_post_meta( $item['variation_id'], '_price', esc_attr( $woocommerce_regular_price ) );
                    }
                }
            }
        }

        public function rllc_woo_add_custom_general_fields_save( $post_id ) {
            // Text Field
            if ( isset( $_POST['_quantity_sale'] ) ) {
                $woocommerce_quantity_sale = sanitize_text_field($_POST['_quantity_sale']);
                if ( !empty( $woocommerce_quantity_sale ) ) {
                    update_post_meta( $post_id, '_quantity_sale', is_string( $woocommerce_quantity_sale ) ? esc_attr( $woocommerce_quantity_sale ) : $woocommerce_quantity_sale  );
                } else {
                    update_post_meta( $post_id, '_quantity_sale', 0 );
                }
            }
            if ( isset( $_POST['_rllc_quantity_discount'] ) ) {
                $woocommerce_rllc_quantity_discount = sanitize_text_field($_POST['_rllc_quantity_discount']);
                if ( !$woocommerce_rllc_quantity_discount ) {
                    $woocommerce_rllc_quantity_discount = sanitize_text_field($_POST['_stock']);
                }
                if ( !empty( $woocommerce_rllc_quantity_discount ) ) {
                    update_post_meta( $post_id, '_rllc_quantity_discount', is_string( $woocommerce_quantity_sale ) ? esc_attr( $woocommerce_rllc_quantity_discount ) : $woocommerce_quantity_sale  );
                } else {
                    update_post_meta( $post_id, '_rllc_quantity_discount', 0 );
                }
            }
            if ( isset( $_POST['_rllc_turn_off_countdown'] ) ) {
                $_rllc_turn_off_countdown = sanitize_text_field($_POST['_rllc_turn_off_countdown']);
                if ( !empty( $_rllc_turn_off_countdown ) ) {
                    update_post_meta( $post_id, '_rllc_turn_off_countdown', 'yes' );
                } else {
                    update_post_meta( $post_id, '_rllc_turn_off_countdown', '' );
                }
            } else {
                update_post_meta( $post_id, '_rllc_turn_off_countdown', '' );
            }
            if ( isset( $_POST['_rllc_hide_only_countdown'] ) ) {
                $rllc_hide_only_countdown = sanitize_text_field($_POST['_rllc_hide_only_countdown']);
                if ( !empty( $rllc_hide_only_countdown ) ) {
                    update_post_meta( $post_id, '_rllc_hide_only_countdown', 'yes' );
                } else {
                    update_post_meta( $post_id, '_rllc_hide_only_countdown', '' );
                }
            } else {
                update_post_meta( $post_id, '_rllc_hide_only_countdown', '' );
            }
            if ( isset( $_POST['_rllc_hide_only_salebar'] ) ) {
                $rllc_hide_only_salebar = sanitize_text_field($_POST['_rllc_hide_only_salebar']);
                if ( !empty( $rllc_hide_only_salebar ) ) {
                    update_post_meta( $post_id, '_rllc_hide_only_salebar', 'yes' );
                } else {
                    update_post_meta( $post_id, '_rllc_hide_only_salebar', '' );
                }
            } else {
                update_post_meta( $post_id, '_rllc_hide_only_salebar', '' );
            }
            /* Save specified time */
            if ( isset( $_POST['_woosale_from_time'] ) ) {
                $_woosale_from_time = sanitize_text_field($_POST['_woosale_from_time']);
                if ( !empty( $_woosale_from_time ) ) {
                    update_post_meta( $post_id, '_woosale_from_time', is_string( $_woosale_from_time ) ? esc_attr( $_woosale_from_time ) : $_woosale_from_time  );
                } else {
                    update_post_meta( $post_id, '_woosale_from_time', '' );
                }
            }
            if ( isset( $_POST['_woosale_to_time'] ) ) {
                $_woosale_to_time = sanitize_text_field($_POST['_woosale_to_time']);
                if ( !empty( $_woosale_to_time ) ) {
                    update_post_meta( $post_id, '_woosale_to_time', is_string( $_woosale_to_time ) ? esc_attr( $_woosale_to_time ) : $_woosale_to_time  );
                } else {
                    update_post_meta( $post_id, '_woosale_to_time', '' );
                }
            }
        }

        public function rllc_woo_add_custom_general_fields() {
            global $post;
            $_product = wc_get_product( $post->ID );
            if ( in_array( rllc_woosale_get_product_type($_product), array( 'variable' ) ) ) {
                return;
            }
            $_woosale_from_time = rllc_woosale_get_from_time( $_product );
            $_woosale_to_time   = rllc_woosale_get_to_time( $_product );
            // Display Custom Field Value
            echo '<div class="options_group thim-countdown-options" style="display: none;">';
            woocommerce_wp_text_input(
                    array(
                        'id' => '_woosale_from_time',
                        'label' => esc_html__( 'Sale start time', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Enter the Specified start time of your sales with format <strong>H:i:s</strong>.', 'rllc_store_sale_countdown' ),
                        'value' => $_woosale_from_time ? $_woosale_from_time : ''
                    )
            );
            woocommerce_wp_text_input(
                    array(
                        'id' => '_woosale_to_time',
                        'label' => esc_html__( 'Sale end time', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Enter the Specified end time of your sales with format <strong>H:i:s</strong>.', 'rllc_store_sale_countdown' ),
                        'value' => $_woosale_to_time ? $_woosale_to_time : ''
                    )
            );

            woocommerce_wp_text_input(
                    array(
                        'id' => '_rllc_quantity_discount',
                        'label' => esc_html__( 'Total product discount', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Enter the TOTAL Sale product.', 'rllc_store_sale_countdown' ),
                        'default' => '0'
                    )
            );
            woocommerce_wp_text_input(
                    array(
                        'id' => '_quantity_sale',
                        'label' => esc_html__( 'Total sold quantity', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Quantity sale of this product is sold.', 'rllc_store_sale_countdown' )
                    )
            );
            echo '</div>';
        }

        public function rllc_woo_add_custom_product_variable( $loop, $data, $variation ) {
			if ( $variation ) {
				if ( trim( rllc_woosale_get_product_type($variation->ID) ) != 'variation' ) {
					return;
				}
			}

            $_rllc_quantity_discount = rllc_woosale_get_product_rllc_quantity_discount( $variation->ID );
            $_quantity_sale     = rllc_woosale_get_product_quantity_sale($variation->ID);
            $_woosale_from_time = rllc_woosale_get_from_time($variation->ID);
            $_woosale_to_time   = rllc_woosale_get_to_time($variation->ID);

            // Display Custom Field Value
            echo '<tr class="options_group"><td>';
            woocommerce_wp_text_input(
                    array(
                        'id' => '_woosale_from_time[' . $loop . ']',
                        'label' => esc_html__( 'Sale start time', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Enter the Specified start time of your sales with format <strong>H:i:s</strong>.', 'rllc_store_sale_countdown' ),
                        'value' => $_woosale_from_time ? $_woosale_from_time : ''
                    )
            );
            woocommerce_wp_text_input(
                    array(
                        'id' => '_woosale_to_time[' . $loop . ']',
                        'label' => esc_html__( 'Sale end time', 'rllc_store_sale_countdown' ),
                        'placeholder' => '',
                        'desc_tip' => 'true',
                        'description' => esc_html__( 'Enter the Specified end time of your sales with format <strong>H:i:s</strong>.', 'rllc_store_sale_countdown' ),
                        'value' => $_woosale_to_time ? $_woosale_to_time : ''
                    )
            );
            // Text Field
            @woocommerce_wp_text_input(
                            array(
                                'id' => '_rllc_quantity_discount[' . $loop . ']',
                                'label' => esc_html__( 'Total product discount', 'rllc_store_sale_countdown' ),
                                'placeholder' => '',
                                'desc_tip' => 'true',
                                'description' => esc_html__( 'Enter the TOTAL Sale product.', 'rllc_store_sale_countdown' ),
                                'value' => $_rllc_quantity_discount ? $_rllc_quantity_discount : 0
                            )
            );
            @woocommerce_wp_text_input(
                            array(
                                'id' => '_quantity_sale[' . $loop . ']',
                                'label' => esc_html__( 'Total sold quantity', 'rllc_store_sale_countdown' ),
                                'placeholder' => '',
                                'desc_tip' => 'true',
                                'description' => esc_html__( 'Quantity sale of this product is sold.', 'rllc_store_sale_countdown' ),
                                'value' => $_quantity_sale ? $_quantity_sale : 0
                            )
            );
            echo '</td></tr>';
        }

        public function rllc_woo_add_custom_product_variable_save( $variation_id, $i ) {
            if ( isset( $_POST['_quantity_sale'] ) ) {
                $woocommerce_quantity_sale = sanitize_text_field($_POST['_quantity_sale']);
            } else {
                $woocommerce_quantity_sale = array();
            }
            if ( isset( $_POST['_rllc_quantity_discount'] ) ) {
                $woocommerce_rllc_quantity_discount = sanitize_text_field($_POST['_rllc_quantity_discount']);
            } else {
                $woocommerce_rllc_quantity_discount = array();
            }
            if ( isset( $_POST['_woosale_from_time'] ) ) {
                $_woosale_from_time = sanitize_text_field($_POST['_woosale_from_time']);
            } else {
                $_woosale_from_time = array();
            }
            if ( isset( $_POST['_woosale_to_time'] ) ) {
                $_woosale_to_time = sanitize_text_field($_POST['_woosale_to_time']);
            } else {
                $_woosale_to_time = array();
            }
			
			// update data
			if ( !empty( $woocommerce_quantity_sale[$i] ) ) {
				update_post_meta( $variation_id, '_quantity_sale', esc_attr( $woocommerce_quantity_sale[$i] ) );
			} else {
				update_post_meta( $variation_id, '_quantity_sale', 0 );
			}

			if ( !$woocommerce_rllc_quantity_discount ) {
				$woocommerce_rllc_quantity_discount = sanitize_text_field($_POST['_stock']);
			}
			if ( !empty( $woocommerce_rllc_quantity_discount[$i] ) ) {
				update_post_meta( $variation_id, '_rllc_quantity_discount', esc_attr( $woocommerce_rllc_quantity_discount[$i] ) );
			} else {
				update_post_meta( $variation_id, '_rllc_quantity_discount', 0 );
			}
			/* Save specified time */
			if ( !empty( $_woosale_from_time[$i] ) ) {
				update_post_meta( $variation_id, '_woosale_from_time', esc_attr( $_woosale_from_time[$i] ) );
			} else {
				update_post_meta( $variation_id, '_woosale_from_time', '' );
			}
			if ( !empty( $_woosale_to_time[$i] ) ) {
				update_post_meta( $variation_id, '_woosale_to_time', esc_attr( $_woosale_to_time[$i] ) );
			} else {
				update_post_meta( $variation_id, '_woosale_to_time', '' );
			}
            /* add schedule */
            rllc_store_sale_countdown_add_schedule( $variation_id );
        }

        public function rllc_woo_add_custom_product_variable_sync( $variation_id, $i ) {
            $sync_variation_date_key = isset( $_POST['woosales_sync_variation'] ) ? absint( $_POST['woosales_sync_variation'] ) : '';
            if ( $sync_variation_date_key === '' ) {
                return;
            }
            if ( isset( $_POST['variable_post_id'] ) ) {
                // Text Field
                $variable_post_ids = sanitize_text_field($_POST['variable_post_id']);
            } else {
                $variable_post_ids = array();
            }
            $variation_syn_id = isset( $variable_post_ids[$sync_variation_date_key] ) ? absint( $variable_post_ids[$sync_variation_date_key] ) : '';
            if ( $variation_syn_id === '' || $variation_syn_id == $variation_id ) {
                return;
            }

            $date_from = get_post_meta( $variation_syn_id, '_sale_price_dates_from', true );
            $date_end  = get_post_meta( $variation_syn_id, '_sale_price_dates_to', true );
            $time_from = get_post_meta( $variation_syn_id, '_woosale_from_time', true );
            $time_end  = get_post_meta( $variation_syn_id, '_woosale_to_time', true );

            update_post_meta( $variation_id, '_sale_price_dates_from', $date_from );
            update_post_meta( $variation_id, '_sale_price_dates_to', $date_end );
            update_post_meta( $variation_id, '_woosale_from_time', $time_from );
            update_post_meta( $variation_id, '_woosale_to_time', $time_end );

            /* add schedule */
            rllc_store_sale_countdown_add_schedule( $variation_id );
        }

        public function rllc_woo_sale_update_quantity_sale( $order_id ) {
            $order      = new WC_Order( $order_id );
            $order_stt  = $order->get_status();
            if ( $order_stt == 'completed' ) {
                return $this->rllc_woo_update_sale( $order );
            }
        }

        public function rllc_product_columns( $cols ) {
            $cols['schedule'] = esc_html__( 'Schedule', 'rllc_store_sale_countdown' );
            return $cols;
        }

        public function rllc_product_column_content( $col, $id ) {
            if ( $col == 'schedule' ) {
                if ( rllc_woosales_has_countdown( $id ) ) {
                    $dates = $this->rllc_get_product_schedule_dates( $id );
                    if ( $dates === false ) {
                        _e( '-', 'rllc_store_sale_countdown' );
                    } else {
                        if ( $dates['from'] && $dates['to'] ) {
                            $tip = sprintf( esc_html__( 'From %s to %s', 'rllc_store_sale_countdown' ), rllc_woosale_format_date_time( $dates['from'] ), rllc_woosale_format_date_time( $dates['to'] ) );
                        } elseif ( $dates['from'] ) {
                            $tip = sprintf( esc_html__( 'From %s', 'rllc_store_sale_countdown' ), rllc_woosale_format_date_time( $dates['from'] ) );
                        } elseif ( $dates['to'] ) {
                            $tip = sprintf( esc_html__( 'To %s', 'rllc_store_sale_countdown' ), rllc_woosale_format_date_time( $dates['to'] ) );
                        }
                        echo esc_html__('<div class="thim-row-countdown-schedule">');
                        echo esc_html__('<span class="dashicons dashicons-clock tips" data-tip="'.esc_attr( $tip ) . '"></span>');
                        echo esc_html__('</div>');
                    }
                }
            }
        }

        public function rllc_get_product_schedule_dates( $product_id = 0, $field = '' ) {
            if ( !$product_id ) {
                $product_id = get_the_ID();
            }
            if ( get_post_type( $product_id ) != 'product' ) {
                return false;
            }

            $product    = wc_get_product( $product_id );
            $date_from 	= rllc_woosale_get_date_on_sale_from( $product );
            $date_to 	= rllc_woosale_get_date_on_sale_to( $product );
            $time_from  = '';
            $time_to    = '';
            if ( !$date_from && !$date_to ) {
                return false;
            }
            if ( $date_from ) {
                $time_from = rllc_woosale_get_from_time($product);
                if ( $time_from != '' ) {
                    $date_from = rllc_woosales_add_specified_time( $date_from, $time_from );
                }
            }
            if ( $date_to ) {
                $time_to = rllc_woosale_get_to_time($product);
                if ( $time_to != '' ) {
                    $date_to = rllc_woosales_add_specified_time( $date_to, $time_to );
                }
            }

            $dates = array( 'from' => $date_from, 'to' => $date_to, 'time_from' => $time_from, 'time_to' => $time_to );
            if ( $field && array_key_exists( $field, $dates ) ) {
                return $dates[$field];
            }
            return $dates;
        }

        public function rllc_bulk_variable_options() {
            ?>
            <optgroup label="<?php esc_attr_e( 'Store Sale CountDown', 'rllc_store_sale_countdown' ); ?>">
                <option value="rllc_variable_sale_price_dates_from" data-desc="<?php echo esc_attr(esc_html__( 'Start date : YYYY-MM-DD', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'Start date', 'rllc_store_sale_countdown' ); ?></option>
                <option value="rllc_variable_sale_price_dates_to" data-desc="<?php echo esc_attr(esc_html__( 'End date : YYYY-MM-DD', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'End date', 'rllc_store_sale_countdown' ); ?></option>
                <option value="_rllc_quantity_discount" data-desc="<?php echo esc_attr(esc_html__( 'Total product discount', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'Total product discount', 'rllc_store_sale_countdown' ); ?></option>
                <option value="_quantity_sale" data-desc="<?php echo esc_attr(esc_html__( 'Sold', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'Sold', 'rllc_store_sale_countdown' ); ?></option>
				<option value="_woosale_from_time" data-desc="<?php echo esc_attr(esc_html__( 'Start time', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'Start time', 'rllc_store_sale_countdown' ); ?></option>
                <option value="_woosale_to_time" data-desc="<?php echo esc_attr(esc_html__( 'End time', 'rllc_store_sale_countdown' )); ?>"><?php _e( 'End time', 'rllc_store_sale_countdown' ); ?></option>
            </optgroup>
            <?php
        }
		
		public function rllc_woocommerce_bulk_edit_variations_callback($bulk_action, $data, $product_id, $variations){
			if( !$variations || empty($variations) || !isset( $data['value']) ) {
				return;
			}

			if( 'rllc_variable_sale_price_dates_from' == $bulk_action ) {
				$value = esc_attr($data['value']);
				$date_from     = (string) $value ? wc_clean( $value ) : '';
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_sale_price_dates_from', $date_from ? strtotime( $date_from ) : '' );
				}
			}

			if( 'rllc_variable_sale_price_dates_to' == $bulk_action ) {
				$value = esc_attr($data['value']);
				$date_to = (string) $value ? wc_clean( $value ) : '';
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_sale_price_dates_to', $date_to ? strtotime( $date_to ) : '' );
				}
				return;
			}
			
			if( '_rllc_quantity_discount' == $bulk_action ) {
				$value = intval($data['value']);
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_rllc_quantity_discount', esc_attr( $value ) );
				}
				return;
			}
			if( '_quantity_sale' == $bulk_action ) {
				$value = intval($data['value']);
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_quantity_sale', esc_attr( $value ) );
				}
				return;
			}
			if( '_woosale_from_time' == $bulk_action ) {
				$value = $data['value'];
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_woosale_from_time', esc_attr( $value ) );
				}
				return;
			}
			if( '_woosale_to_time' == $bulk_action ) {
				$value = $data['value'];
				foreach( $variations as $variation_id ) {
					update_post_meta( $variation_id, '_woosale_to_time', esc_attr( $value ) );
				}
				return;
			}
		}

        public function rllc_product_type_options( $options ) {
            $product_type_options = array(
                'rllc_turn_off_countdown' => array(
                    'id' => '_rllc_turn_off_countdown',
                    'wrapper_class' => 'show_if_simple show_if_variable',
                    'label' => esc_html__( 'Hide all countdown and sale bar', 'rllc_store_sale_countdown' ),
                    'description' => esc_html__( 'Hide countdown on this product.', 'rllc_store_sale_countdown' ),
                    'default' => 'no'
                ),
                'rllc_hide_only_countdown' => array(
                    'id' => '_rllc_hide_only_countdown',
                    'wrapper_class' => 'show_if_simple show_if_variable',
                    'label' => esc_html__( 'Hide only countdown', 'rllc_store_sale_countdown' ),
                    'description' => esc_html__( 'Hide only countdown on product sale. ', 'rllc_store_sale_countdown' ),
                    'default' => 'no'
                ),
                'rllc_hide_only_salebar' => array(
                    'id' => '_rllc_hide_only_salebar',
                    'wrapper_class' => 'show_if_simple show_if_variable',
                    'label' => esc_html__( 'Hide only Sale Bar', 'rllc_store_sale_countdown' ),
                    'description' => esc_html__( 'Hide only Sale Bar on product sale.', 'rllc_store_sale_countdown' ),
                    'default' => 'no'
                )
            );

            $options = array_merge( $options, $product_type_options );
            return $options;
        }

        public function rllc_save_setting() {
            if ( isset( $_POST['rllc_ob_detail_position'] ) ) {
                update_option( 'rllc_ob_detail_position', sanitize_text_field($_POST['rllc_ob_detail_position']) );
            }
            if ( isset( $_POST['rllc_ob_single_element_text'] ) ) {
                update_option( 'rllc_ob_single_element_text', sanitize_text_field($_POST['rllc_ob_single_element_text']) );
            }
            if ( isset( $_POST['rllc_ob_woosales_single'] ) ) {
                update_option( 'rllc_ob_woosales_single', sanitize_text_field($_POST['rllc_ob_woosales_single']) );
            }

            if ( isset( $_POST['rllc_ob_categories_position'] ) ) {
                update_option( 'rllc_ob_categories_position', sanitize_text_field($_POST['rllc_ob_categories_position']) );
            }
            if ( isset( $_POST['rllc_ob_categories_element_text'] ) ) {
                update_option( 'rllc_ob_categories_element_text', sanitize_text_field($_POST['rllc_ob_categories_element_text']) );
            }
            if ( isset( $_POST['rllc_ob_woosales_categories'] ) ) {
                update_option( 'rllc_ob_woosales_categories', sanitize_text_field($_POST['rllc_ob_woosales_categories']) );
            }                       
        }
    }
}
return new RLLC_RCODE_Admin();