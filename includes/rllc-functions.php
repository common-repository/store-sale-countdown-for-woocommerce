<?php
if (! defined('ABSPATH')) {
    exit();
}

function rllc_woosale_get_product_type( $product ) {
	$product_id = is_object($product)?$product->get_id():$product;
	if(!is_object($product)){
		$product = rllc_woosale_get_product($product_id);
	}
	return $product->get_type();
}

function rllc_woosale_get_product($product_id){
	return wc_get_product($product_id);
}

function rllc_woosale_get_date_on_sale_from( $product ) {
   return $rllc_sale_from = strtotime(get_option( 'rllc_start', true ));
}

function rllc_woosale_get_date_on_sale_to( $product ) {
    return $rllc_sale_from = strtotime(get_option( 'rllc_end', true ));
}

function rllc_woosale_get_from_time( $product ){
    return $rllc_sale_from = strtotime(get_option( 'rllc_start', true ));
}

function rllc_woosale_get_to_time( $product ){
    return $rllc_sale_from = strtotime(get_option( 'rllc_end', true ));
}

function rllc_woosale_get_product_id( $product ){
	return $product->get_id();
}

function rllc_woosale_get_turn_off_countdown( $product ) {
	$product_id = is_object($product)?$product->get_id():$product;
	return get_post_meta( $product_id, '_turn_off_countdown', true );
}

function rllc_woosale_get_product_stock( $product ) {
	if( rllc_woosale_is_woo3() ) {
		return $product->get_stock_status();
	} else {
		return $product->stock;
	}
}

function rllc_woosale_get_product_manage_stock( $product ){
	if( rllc_woosale_is_woo3() ) {
		return $product->get_manage_stock();
	} else {
		return $product->manage_stock;
	}
}

function rllc_woosale_get_product_quantity_discount( $product ) {
	$product_id = is_object($product) ? $product->get_id() : $product;
	return get_post_meta( $product_id, '_quantity_discount', true );
}

function rllc_woosale_get_product_quantity_sale($product) {
	$product_id = is_object($product)?$product->get_id():$product;
	return get_post_meta( $product_id, '_quantity_sale', true );
}

function rllc_woosale_get_rllc_wcml_duplicate_of_variation($product){
	return get_post_meta( $product->get_id(), '_rllc_wcml_duplicate_of_variation', true );
}

function rllc_woosale_get_product_rllc_hide_only_salebar($product){
	return get_post_meta( $product->get_id(), '_rllc_hide_only_salebar', true );
}

function rllc_woosale_get_product_rllc_hide_only_countdown($product){
	return get_post_meta( $product->get_id(), '_rllc_hide_only_countdown', true );
}

if ( !function_exists('rllc_woosale_is_woo3') ) {
	function rllc_woosale_is_woo3(){
		global $woocommerce;
		if ( !$woocommerce || version_compare( $woocommerce->version, '3.0.0', '>=' ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( !function_exists( 'rllc_woosale_format_date_time' ) ) {
	function rllc_woosale_format_date_time ($time = ''){
		if (! $time) {
			$time = current_time('timestamp');
		}
		if (! is_numeric($time)) {
			$time = strtotime($time);
		}
		$format = get_option('date_format', 'Y-m-d') . ' ' .get_option('time_format', 'H:i:s');
		return apply_filters('rllc_woosale_format_date_time', date_i18n($format, $time), $format, $time);
	}
}

if ( !function_exists( 'rllc_woosale_single_is_enabled' ) ) {
    function rllc_woosale_single_is_enabled() {
        $check_showon = get_option( 'rllc_ob_single_enable', 1 );
        return $check_showon == 1;
    }
}

if ( !function_exists( 'rllc_woosale_categories_is_enabled' ) ) {
    function rllc_woosale_categories_is_enabled() {
        $check_showon = get_option( 'rllc_ob_categories_enable', 1 );
        return $check_showon == 1;
    }
}

add_action( 'the_post', 'rllc_woosales_setup_product', 999 );
if ( !function_exists( 'rllc_woosales_setup_product' ) ) {
	function rllc_woosales_setup_product() {
		global $product, $post;
		$current_time = strtotime( current_time( 'mysql', true ) );
		$is_woo3      = rllc_woosale_is_woo3();
		if ( $post->post_type !== 'product' || ! $product ) {
			return;
		}
		if ( $product->get_type() === 'variable' ) {
			$variations = $product->get_available_variations();
			$product->woosale_start  = array();
			$product->woosale_end    = array();
			foreach ( $variations as $variable ) {
				$id         = $variable['variation_id'];
				$variable   = rllc_woosale_get_product( $id ); 
				$date_start = rllc_woosale_get_date_on_sale_from( $variable ); 
				$time_start = rllc_woosale_get_from_time( $variable ); 
				$date_end   = rllc_woosale_get_date_on_sale_to( $variable ); 
				$time_end   = rllc_woosale_get_to_time( $variable );
				$args       = array(
						'date_start'  => $date_start,
						'time_start'  => $time_start,
						'date_end'    => $date_end,
						'time_end'    => $time_end
				);
				$woosale_start = $woosale_end = '';
				if ( $date_start ) {
					$woosale_start = $date_start;
					if ( $time_start ) {
						$woosale_start = rllc_woosales_add_specified_time( $woosale_start, $time_start );
					}
				}
				$woosale_start = apply_filters( 'rllc_woosales_setup_product_woosale_start', $woosale_start, $product, $args );
				$product->woosale_start[$id] = $woosale_start;
				if ( $date_end ) {
					$woosale_end = $date_end;
					if ( $time_end ) {
						$woosale_end = rllc_woosales_add_specified_time( $woosale_end, $time_end );
					}
				}
				$woosale_end = apply_filters( 'rllc_woosales_setup_product_woosale_end', $woosale_end, $product, $args );
				$product->woosale_end[$id] = $woosale_end;
			}
		} else {
			$date_start = rllc_woosale_get_date_on_sale_from( $product );
			$time_start = rllc_woosale_get_from_time( $product );
			$date_end   = rllc_woosale_get_date_on_sale_to( $product );
			$time_end   = rllc_woosale_get_to_time( $product );
			$product->woosale_start = $product->woosale_end = null;
			$args = array(
					'current_time' => current_time('Y-m-d H:i:s', true),
					'date_start'   => $date_start,
					'time_start'   => $time_start,
					'date_end'     => $date_end,
					'time_end'     => $time_end
			);
			$woosale_start = $woosale_end = '';
			if ( $date_start ) {
				$woosale_start = $date_start;
				if ( $time_start ) {
					$woosale_start = rllc_woosales_add_specified_time( $woosale_start, $time_start );
				}
			}
			$woosale_start = apply_filters( 'rllc_woosales_setup_product_woosale_start', $woosale_start, $product, $args );
			$product->woosale_start = $woosale_start;
			if ( $date_end ) {
				$woosale_end = $date_end;
				if ( $time_end ) {
					$woosale_end = rllc_woosales_add_specified_time( $woosale_end, $time_end ); 
				}
			}
			$woosale_end = apply_filters( 'rllc_woosales_setup_product_woosale_end', $woosale_end, $product, $args );
			$product->woosale_end = $woosale_end;
		}
	}
}

if ( !function_exists( 'rllc_woosales_has_countdown' ) ) {
    function rllc_woosales_has_countdown( $id = null ) {
        if ( !$id ) {
            $id = get_the_ID();
        }
        if ( !$id ) {
            return false;
        }
        $product = wc_get_product( $id );
        $_turn_off_countdown = rllc_woosale_get_turn_off_countdown( $product );
        if ( $_turn_off_countdown ) {
            return;
        }
		
        $results = array();
        if ( $product->get_type() === 'variable' ) {
            $_product_variables = $product->get_available_variations();
            $current_time = current_time( 'timestamp', true );
            foreach ( $_product_variables as $variable ) {
                $variable           = rllc_woosale_get_product($variable['variation_id']);
                $date_from          = rllc_woosale_get_date_on_sale_from( $variable );
                $date_end           = rllc_woosale_get_date_on_sale_to( $variable );
                $_woosale_from_time = rllc_woosale_get_from_time( $variable );
                $_woosale_to_time   = rllc_woosale_get_to_time( $variable );
                
                if ( $date_from && $_woosale_from_time != '' ) {
                    $date_from = rllc_woosales_add_specified_time( $date_from, $_woosale_from_time );
                }
                if ( $date_end && $_woosale_to_time != '' ) {
                    $date_end = rllc_woosales_add_specified_time( $date_end, $_woosale_to_time );
                }
                if ( $current_time >= $date_end ) {
                    continue;
                }
                $results = array( 'from' => $date_from, 'to' => $date_end );
                break;
            }
        } else {
            $date_from 	= rllc_woosale_get_date_on_sale_from( $product );
            $date_to 	= rllc_woosale_get_date_on_sale_to( $product );
            if ( $date_from ) {
                $time_from = rllc_woosale_get_from_time( $product );
                if ( $time_from != '' ) {
                    $date_from = rllc_woosales_add_specified_time( $date_from, $time_from );
                }
            }
            if ( $date_to ) {
                $time_to = rllc_woosale_get_to_time( $product );
                if ( $time_to != '' ) {
                    $date_to = rllc_woosales_add_specified_time( $date_to, $time_to );
                }
            }
            $results = array( 'from' => $date_from, 'to' => $date_to );
        }
        return $results;
    }
}

if ( !function_exists( 'rllc_woosales_add_specified_time' ) ) {
	function rllc_woosales_add_specified_time( $date = '', $time = '' ) {
		if ( ! $date ) {
			$date = current_time( 'timestamp', true );
		}
		if ( is_string( $date ) ) {
			$date = strtotime( $date );
		}
		$timestamp = $date;
		if ( $time ) {
			$time_timestamp = strtotime( $time, 0 );
			$timestamp += $time_timestamp;
		}
		return $timestamp;
	}
}

if ( !function_exists( 'rllc_woosales_setup_product_countdown' ) ) {
    function rllc_woosales_setup_product_countdown( $single = true ) {
        global $product;
        $product_id 		= rllc_woosale_get_product_id( $product );
        $turn_off_countdown = rllc_woosale_get_turn_off_countdown( $product );
        $products 			= array();
        if ( !$product_id || $turn_off_countdown ) {
            return $products;
        }
        $hide_datetext = $single ? get_option( 'rllc_ob_single_datetext_show', 1 ) : get_option( 'rllc_ob_categories_datetext_show', 1 );
        $hide_datetext  = $hide_datetext == 0;
        $current_time   = current_time( 'timestamp', true );
        if ( rllc_woosale_get_product_type($product) === 'variable' ) {
            $total_discount = $total_sold = 0;
            foreach ( $product->get_available_variations() as $variable ) {
                $product_variation = rllc_woosale_get_product( $variable['variation_id'] ); 
                if ( $wpml_id = rllc_woosale_get_rllc_wcml_duplicate_of_variation($product_variation)){
                    $product_variation = wc_get_product( $wpml_id );
                }
                $time_from = isset( $product->woosale_start[$variable['variation_id']] ) ? $product->woosale_start[$variable['variation_id']] : null;
                $time_end = isset( $product->woosale_end[$variable['variation_id']] ) ? $product->woosale_end[$variable['variation_id']] : null;
                $discount       = rllc_woosale_get_product_quantity_discount($product_variation);
                $sale           = rllc_woosale_get_product_quantity_sale($product_variation);
                $stock          = rllc_woosale_get_product_stock($product_variation);
                $_manage_stock  = rllc_woosale_get_product_manage_stock($product_variation);
                if ( $_manage_stock ) {
                    if ( trim( $_manage_stock ) == 'yes' ) {
                        if ( $stock < 1 ) {
                            $discount = 0;
                        }
                    }
                }
                if ( !$sale ) {
                    $sale = 0;
                }
                if ( (!$time_from && !$time_end ) || ( $time_from && !$time_end && $time_from < $current_time ) || ( $time_end && !$time_from && $time_end < $current_time ) ) {
                    continue;
                }
                $per_sale = 0;
                if ( $discount > 0 && $sale <= $discount ) {
                    $per_sale = absint( $sale / $discount * 100 );
                }
                $total_discount += absint( $discount );
                $total_sold += absint( $sale );
                $products[] = array(
                    'hide_datetext' => $hide_datetext ? 0 : 1,
                    'current_time'  => $current_time,
                    'woosale_start' => $time_from,
                    'woosale_end'   => $time_end,
                    'hide_coming'   => ( $current_time < $time_from ) && get_option( 'rllc_ob_coming_schedule', 'yes' ) !== 'yes',
                    'sale'          => $sale,
                    'discount'      => $discount,
                    'per_sale'      => $per_sale,
                    'is_variation'  => true,
                    'variation_id'  => $variable['variation_id']
                );
            }

            if ( get_option( 'rllc_ob_bar_sale_variations', 0 ) ) {
                $per_sale = 0;
                if ( $total_sold < $total_discount ) {
                    $per_sale = absint( $total_sold / $total_discount * 100 );
                }
                foreach ( $products as $k => $product_k ) {
                    $products[$k]['discount']   = $total_discount;
                    $products[$k]['sale']       = $total_sold;
                    $products[$k]['per_sale']   = $per_sale;
                }
            }
            if ( !$single ) {
                return $products;
            }
        } else {
            $time_from 	= $product->woosale_start;
            $time_end 	= $product->woosale_end;
            $discount 	= rllc_woosale_get_product_quantity_discount($product);
            $sale 		= rllc_woosale_get_product_quantity_sale($product);
            $stock 		= rllc_woosale_get_product_stock($product);
            $_manage_stock = rllc_woosale_get_product_manage_stock($product);
            if ( $_manage_stock ) {
                if ( trim( $_manage_stock ) == 'yes' ) {
                    if ( $stock < 1 ) {
                        $discount = 0;
                    }
                }
            }

            if ( !$sale ) {
                $sale = 0;
            }

            if ( (!$time_from && !$time_end ) || ( $time_from && !$time_end && $time_from < $current_time ) || ( $time_end && !$time_from && $time_end < $current_time ) ) {
                return $products;
            }

            $per_sale = 0;
            if ( $discount > 0 && $sale <= $discount ) {
                $per_sale = absint( $sale / $discount * 100 );
            }

            $products[] = array(
                'hide_datetext' => $hide_datetext ? 0 : 1,
                'current_time'  => $current_time,
                'woosale_start' => $time_from,
                'woosale_end'   => $time_end,
                'hide_coming'   => ( $current_time < $time_from ) && get_option( 'rllc_ob_coming_schedule', 'yes' ) !== 'yes',
                'sale'          => $sale,
                'discount'      => $discount,
                'per_sale'      => $per_sale,
                'is_variation'  => false,
                'variation_id'  => false
            );
        }
        return apply_filters( 'rllc_woosales_setup_product_countdown', $products );
    }
}

if ( !function_exists( 'rllc_woosales_elements_display' ) ) {
    function rllc_woosales_elements_display( $sort = array() ) {
        $elements = $default = apply_filters( 'rllc_woosales_elements_display', array(
            'title' => array(
                'sort' => '',
                'name' => esc_html__( 'Title', 'rllc_store_sale_countdown' ),
                'desc' => esc_html__( 'Status of countdown. Eg: Coming or Sale.', 'rllc_store_sale_countdown' ),
                'id' => 'title',
                'status' => '',
                'callback' => 'woosales_display_title'
            ),
            'schedule' => array(
                'sort' => '',
                'name' => esc_html__( 'Schedule', 'rllc_store_sale_countdown' ),
                'desc' => esc_html__( 'Schedule time start to time end.', 'rllc_store_sale_countdown' ),
                'id' => 'schedule',
                'status' => '',
                'callback' => 'woosales_display_schedule'
            ),
            'sale-bar' => array(
                'sort' => '',
                'name' => esc_html__( 'Sale Bar', 'rllc_store_sale_countdown' ),
                'desc' => esc_html__( 'Quantity sold and quantity discount.', 'rllc_store_sale_countdown' ),
                'id' => 'salebar',
                'status' => '',
                'callback' => 'woosales_display_sale_bar'
            ),
            'countdown' => array(
                'sort' => '',
                'name' => esc_html__( 'Countdown', 'rllc_store_sale_countdown' ),
                'desc' => esc_html__( 'Countdown.', 'rllc_store_sale_countdown' ),
                'id' => 'countdown',
                'status' => '',
                'callback' => 'woosales_display_countdown'
            ),
                ) );

        if ( $sort ) {
            $elements = array();
            foreach ( $sort as $s ) {
                if ( array_key_exists( $s, $default ) ) {
                    $elements[$s] = $default[$s];
                }
            }
        }
        return $elements;
    }
}

if ( !function_exists( 'rllc_woosales_elements_display_sortable' ) ) {
    function rllc_woosales_elements_display_sortable( $option_name = '' ) {
        $options = get_option( $option_name, array( 'sort' => array(), 'enabled' => array(
                'title' => 'on', 'schedule' => 'on', 'sale-bar' => 'on', 'countdown' => 'on'
            ) ) );
        $enabled = isset( $options['enabled'] ) ? $options['enabled'] : array();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'CountDown Element Display Order', 'rllc_store_sale_countdown' ) ?></th>
            <td class="forminp">
                <table class="wc_gateways woosales_table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                            $columns = apply_filters( 'rllc_store_sale_countdown_element_order_columns', array(
                                'sort' => '',
                                'name' => esc_html__( 'Element', 'woocommerce' ),
                                'desc' => esc_html__( 'Description', 'woocommerce' ),
                                'status' => esc_html__( 'Enabled', 'woocommerce' )
                                    ) );

                            foreach ( $columns as $key => $column ) {
                                echo '<th class="' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( rllc_woosales_elements_display( $options['sort'] ) as $id => $section ) {
                            echo '<tr>';
                            foreach ( $section as $key => $column ) {
                                switch ( $key ) {
                                    case 'sort' :
                                        echo '<td width="1%" class="sort">
											<input type="hidden" name="' . esc_attr( $option_name ) . '[sort][]" value="' . esc_attr( $id ) . '" />
										</td>';
                                        break;
                                    case 'name' :
                                        echo '<td class="name">' . esc_html( $column ) . '</td>';
                                        break;
                                    case 'desc' :
                                        echo '<td class="desc"><p class="desc">' . esc_html( $column ) . '</p></td>';
                                        break;
                                    case 'status' :
                                        echo '<td class="status">
										<label>
											<input type="checkbox" name="' . esc_attr( $option_name ) . '[enabled][' . esc_attr( $id ) . ']" class="checkbox" ' . ( array_key_exists( $id, $enabled ) ? ' checked' : '' ) . ' />
											<div class="rllc_woosales_switch ' . ( array_key_exists( $id, $enabled ) ? ' on' : '' ) . '"></div>
										</label>
										</td>';
                                        break;
                                    default :
                                        do_action( 'woocommerce_payment_gateways_setting_column_' . $key, $section );
                                        break;
                                }
                            }
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }
}

if ( !function_exists( 'rllc_store_sale_countdown_add_schedule' ) ) {
    function rllc_store_sale_countdown_add_schedule( $product_id = null ) {
        if ( !$product_id ) {
            return;
        }
        $product	= wc_get_product( $product_id );
        $start_time = rllc_woosales_add_specified_time( rllc_woosale_get_date_on_sale_from($product), rllc_woosale_get_from_time($product) );
        $end_time	= rllc_woosales_add_specified_time( rllc_woosale_get_date_on_sale_to($product), rllc_woosale_get_to_time($product) );
        wp_clear_scheduled_hook( 'rllc_store_sale_countdown_schedule_update_quantity_product', array( $product_id, $start_time, $end_time ) );
        wp_schedule_single_event( $end_time, 'rllc_store_sale_countdown_schedule_update_quantity_product', array( $product_id, $start_time, $end_time ) );
        do_action( 'rllc_store_sale_countdown_add_schedule', $product_id );
    }
}

add_action( 'rllc_store_sale_countdown_schedule_update_quantity_product', 'rllc_woosalecountdown_update_schedule_product', 10, 3 );
if ( !function_exists( 'rllc_woosalecountdown_update_schedule_product' ) ) {
    function rllc_woosalecountdown_update_schedule_product( $product_id = null, $start_time = '', $end_time = '' ) {
        $status = get_option( 'rllc_ob_hide_product', 0 );
        if ( !$product_id || !$end_time ) {
            return;
        }
        $product		= wc_get_product( $product_id );
        $start_time		= rllc_woosales_add_specified_time( rllc_woosale_get_date_on_sale_from($product), rllc_woosale_get_from_time($product) );
        $end_time		= rllc_woosales_add_specified_time( rllc_woosale_get_date_on_sale_to($product), rllc_woosale_get_to_time($product) );
        $current_time	= current_time( 'timestamp' );
        if ( $status ) {
            wp_update_post( array( 'ID' => $product_id, 'post_status' => $status ) );
        }
    }
}

add_action( 'rllc_store_sale_countdown_before_widget_sale_product', 'rllc_woosales_widget_hook_remove_element', 10, 2 );
if ( !function_exists( 'rllc_woosales_widget_hook_remove_element' ) ) {
    function rllc_woosales_widget_hook_remove_element( $args, $instance ) {
        $GLOBALS['woosales_widget_instance'] = $instance;
        if ( isset( $instance['show_link'] ) && !$instance['show_link'] ) {
            remove_action( 'woocommerce_before_shop_loop_item', 'rllc_woocommerce_template_loop_product_link_open', 10 );
            remove_action( 'woocommerce_after_shop_loop_item', 'rllc_woocommerce_template_loop_product_link_close', 5 );
        }
        if ( isset( $instance['show_title'] ) && !$instance['show_title'] ) {
            remove_action( 'woocommerce_shop_loop_item_title', 'rllc_woocommerce_template_loop_product_title', 10 );
        }
        if ( isset( $instance['show_rating'] ) && !$instance['show_rating'] ) {
            remove_action( 'woocommerce_after_shop_loop_item_title', 'rllc_woocommerce_template_loop_rating', 5 );
        }
        if ( isset( $instance['show_price'] ) && !$instance['show_price'] ) {
            remove_action( 'woocommerce_after_shop_loop_item_title', 'rllc_woocommerce_template_loop_price', 10 );
        }
        if ( isset( $instance['show_image'] ) && !$instance['show_image'] ) {
            remove_action( 'woocommerce_before_shop_loop_item_title', 'rllc_woocommerce_template_loop_product_thumbnail', 10 );
        }
        if ( isset( $instance['show_image'], $instance['product_image'] ) && $instance['show_image'] ) {
            remove_action( 'woocommerce_before_shop_loop_item_title', 'rllc_woocommerce_template_loop_product_thumbnail', 10 );
            do_action( 'rllc_store_sale_countdown_widget_image_size', $instance );
        }
        if ( isset( $instance['show_button'] ) && !$instance['show_button'] ) {
            remove_action( 'woocommerce_after_shop_loop_item', 'rllc_woocommerce_template_loop_add_to_cart', 10 );
        }
    }
}

add_action( 'rllc_store_sale_countdown_after_widget_sale_product', 'rllc_woosales_widget_hook_add_element', 10, 2 );
if ( !function_exists( 'rllc_woosales_widget_hook_add_element' ) ) {
    function rllc_woosales_widget_hook_add_element( $args, $instance ) {
        global $rllc_woosales_widget_instance;
        $rllc_woosales_widget_instance = null;
        if ( isset( $instance['show_link'] ) && !$instance['show_link'] ) {
            add_action( 'woocommerce_before_shop_loop_item', 'rllc_woocommerce_template_loop_product_link_open', 10 );
            add_action( 'woocommerce_after_shop_loop_item', 'rllc_woocommerce_template_loop_product_link_close', 5 );
        }
        if ( isset( $instance['show_title'] ) && !$instance['show_title'] ) {
            add_action( 'woocommerce_shop_loop_item_title', 'rllc_woocommerce_template_loop_product_title', 10 );
        }
        if ( isset( $instance['show_rating'] ) && !$instance['show_rating'] ) {
            add_action( 'woocommerce_after_shop_loop_item_title', 'rllc_woocommerce_template_loop_rating', 5 );
        }
        if ( isset( $instance['show_price'] ) && !$instance['show_price'] ) {
            add_action( 'woocommerce_after_shop_loop_item_title', 'rllc_woocommerce_template_loop_price', 10 );
        }
        if ( isset( $instance['product_image'] ) ) {
            remove_action( 'rllc_store_sale_countdown_widget_image_size', 'rllc_woosales_widget_image_size', 10 );
        }
        if ( isset( $instance['show_image'] ) && !$instance['show_image'] ) {
            add_action( 'woocommerce_before_shop_loop_item_title', 'rllc_woocommerce_template_loop_product_thumbnail', 10 );
        }
        if ( isset( $instance['show_button'] ) && !$instance['show_button'] ) {
            add_action( 'woocommerce_after_shop_loop_item', 'rllc_woocommerce_template_loop_add_to_cart', 10 );
        }
    }
}

add_action( 'rllc_store_sale_countdown_widget_image_size', 'rllc_woosales_widget_image_size', 10 );
if ( !function_exists( 'rllc_woosales_widget_image_size' ) ) {
    function rllc_woosales_widget_image_size( $instance ) {
        if ( isset( $instance['product_image'] ) ) {
            add_action( 'woocommerce_before_shop_loop_item_title', 'rllc_woosale_template_loop_product_thumbnail', 10 );
        }
    }
}

function rllc_woosale_template_loop_product_thumbnail() {
	global $rllc_woosales_widget_instance;
	if ( isset( $rllc_woosales_widget_instance['product_image'] ) ) {
		echo woocommerce_get_product_thumbnail( $rllc_woosales_widget_instance['product_image'] );
	}
}

if ( ! function_exists( 'rllc_woosales_get_timezone_string' ) ) {
	function rllc_woosales_get_timezone_string() {
		$tzstring     = get_option( 'timezone_string' );
		$gmt_offset   = get_option( 'gmt_offset' );
		if ( ! $tzstring ) {
			$timezones = timezone_identifiers_list();
			foreach ( $timezones as $key => $zone ) {
				$origin_dtz = new DateTimeZone( $zone );
				$origin_dt  = new DateTime( 'now', $origin_dtz );
				$offset = $origin_dtz->getOffset( $origin_dt ) / 3600;
				if ( $offset == $gmt_offset ) {
					$tzstring = $zone;
				}
			}
		}
		return $tzstring;
	}
}


if( !function_exists( 'rllc_woosales_woocommerce_product_is_on_sale' ) ) {
	function rllc_woosales_woocommerce_product_is_on_sale( $on_sale, $product = null ) {
		$product_type = $product->get_type();
		if( $product_type == 'variable' ) {
			$_product_variables = $product->get_available_variations();
			$current_time = current_time( 'timestamp', true );
			foreach ( $_product_variables as $variable ) {
				$variable           = rllc_woosale_get_product($variable['variation_id']);
				$date_from          = rllc_woosale_get_date_on_sale_from( $variable );
				$date_end           = rllc_woosale_get_date_on_sale_to( $variable );
				$_woosale_from_time = rllc_woosale_get_from_time( $variable );
				$_woosale_to_time   = rllc_woosale_get_to_time( $variable );
				if ( $date_from && $_woosale_from_time != '' ) {
					$date_from = rllc_woosales_add_specified_time( $date_from, $_woosale_from_time );
				}
				if ( $date_end && $_woosale_to_time != '' ) {
					$date_end = rllc_woosales_add_specified_time( $date_end, $_woosale_to_time );
				}
				if ( $current_time >= $date_end ) {
					continue;
				}
				$on_sale = true;
				break;
			}
			return $on_sale;
		}

		$_woosale_from_time		= rllc_woosale_get_from_time( $product );
		$_woosale_to_time		= rllc_woosale_get_to_time( $product );
		$_sale_price_dates_from = rllc_woosale_get_date_on_sale_from( $product );
		$_sale_price_dates_to	= rllc_woosale_get_date_on_sale_to( $product );
		$sale_price		        = $product->get_sale_price();
		$regular_price	        = $product->get_regular_price();
		if( !rllc_woosale_is_woo3() ) {
			$on_sale = $regular_price > $sale_price;
		}
		if( !$_sale_price_dates_from && !$_sale_price_dates_to ) {
			return $on_sale;
		}

		$time_from 	= rllc_woosales_add_specified_time( $_sale_price_dates_from, $_woosale_from_time );
		$time_to 	= rllc_woosales_add_specified_time( $_sale_price_dates_to, $_woosale_to_time );
		$now        = current_time( 'timestamp', true );
		if( $now < $time_from || $now > $time_to ) {
			$on_sale = false;
		}  elseif( $now >= $time_from && $now < $time_to ) {
			$on_sale = true;
		}
		return $on_sale;
	}
}


function old_rllc_filter_woocommerce_product_get_price_old( $price, $product, $x = null, $y = NULL ) {
	$product_type = rllc_woosale_get_product_type( $product );
	$is_on_sale   = rllc_woosales_woocommerce_product_is_on_sale( false, $product );
	if ( ! $is_on_sale ) {
		return $price;
	}
	if ( 'variable' === $product_type ) {
		return $price;
	} elseif ( 'variation' === $product_type ) {
		if ( $is_on_sale ) {
			$price = $product->get_sale_price();
		} else {
			$price = $product->get_regular_price();
		}
		return $price;
	}
	if ( $is_on_sale ) {
		$price = $product->get_sale_price();
	} else {
		$price = $product->get_regular_price();
	}
	return $price;
}

function rllc_filter_woocommerce_product_get_price( $rllc_price, $product, $parent_product = null, $y = NULL ) {
    $rllc_exclude_sale      = get_option( 'rllc_exclude_sale', 'no' );
    $product_regular_price  = get_post_meta( $product->get_id(), '_regular_price', true );
    $product_sale_price     = get_post_meta( $product->get_id(), '_sale_price', true );
    $product_price          = get_post_meta( $product->get_id(), '_price', true );
    $rllc_is_product_on_sale= $product_price == $product_sale_price;
    if ( $rllc_exclude_sale == 'yes' && $rllc_is_product_on_sale ) {
        return $rllc_price;
    }
    if ( $rllc_price !== $product_price && ( current_filter() == 'woocommerce_product_get_price' || current_filter() == 'woocommerce_product_variation_get_price' ) ) {
        return $rllc_price;
    }
    if ( $rllc_price !== $product_sale_price && ( current_filter() == 'woocommerce_product_get_sale_price' || current_filter() == 'woocommerce_product_variation_get_sale_price' ) ) {
        return $rllc_price;
    }
    $orginal_product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
    
    //Need to day wise
    $rllc_sale_type = get_option( 'rllc_sale_type' );
    if($rllc_sale_type==1){ //day wise
        $rllc_totayDay  = date("l");
        if($rllc_totayDay=='Monday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_1');
        }else if($rllc_totayDay=='Tuesday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_2');
        }else if($rllc_totayDay=='Wednesday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_3');
        }else if($rllc_totayDay=='Thursday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_4');
        }else if($rllc_totayDay=='Friday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_5');
        }else if($rllc_totayDay=='Saturday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_6');
        }else if($rllc_totayDay=='Sunday'){
            $rllc_discount    = get_option( 'rllc_discount_amount_7');
        }else{
            $rllc_discount    = get_option( 'rllc_discount_amount', '' );
        }
    }else{ //for all days
        $rllc_discount    = get_option( 'rllc_discount_amount', '' );
    }

    $rllc_type = get_option( 'rllc_type', '0' );
    if ( $parent_product ) {
        $product_id = method_exists( $parent_product, 'get_id' ) ? $parent_product->get_id() : $parent_product->id;
    } elseif ( $product->is_type( 'variation' ) && $parent_product == null ) {
        $product_id = method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : '';
    } else {
        $product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
    }

    $rllc_product_ids_on_sale = rllc_set_onsale_page_transient();
    if ( false === $rllc_product_ids_on_sale && is_array( $rllc_product_ids_on_sale ) ) {
        return $rllc_price;
    }
    if ( ! in_array( $product_id, $rllc_product_ids_on_sale ) ) {
        return $rllc_price;
    }
    if ( empty( $rllc_price ) ) {
        $rllc_price = get_post_meta( $orginal_product_id, '_price', true );
    }


    if ( $rllc_is_product_on_sale && get_option( 'rllc_use_regular_price', 'no' ) == 'yes' ) {
        $rllc_price = $product_regular_price;
    }
    if ( $rllc_type == '0' ) {              //Discount type -> %
        if (is_numeric($rllc_price) && is_numeric($rllc_discount)) {
            $newprice = $rllc_price - ( $rllc_price * ( $rllc_discount / 100 ) );
        }else{
            $newprice = $rllc_price;
        }
    } elseif ( $rllc_type == '1' ) {    //Discount type -> Fixed
        global $woocommerce_wpml;
        $newprice = $rllc_price - rllc_wpml_covert_price( $rllc_discount );
    } else {
        do_action( 'Wc_rch_store_sale_calculate_price_' . $rllc_type, $rllc_price, $product );
    }


    if ( $newprice > 0 && $newprice < $rllc_price ) {
        return $newprice;
    } else {
        return $rllc_price;
    }
}

function rllc_wpml_covert_price( $rllc_price ) {
    global $woocommerce_wpml;
    if ( function_exists( 'icl_object_id' ) && isset( $woocommerce_wpml ) && isset( $woocommerce_wpml->multi_currency->prices ) ) {
        $rllc_price = $woocommerce_wpml->multi_currency->prices->convert_price_amount( $rllc_price );
    }
    return $rllc_price;
}
// woocommerce_variation_prices_price
function rllc_filter_callback_woocommerce_variation_prices_price( $price, $variation, $product ) {
	$is_sale = rllc_woosales_woocommerce_product_is_on_sale(false, $variation);
	if( $is_sale ) {
		$price = $variation->get_sale_price();
	}
	return $price;
}

function rllc_set_onsale_page_transient() {
    $rllc_tax_query          = array();
    $rllc_metaquery          = array();
    $rllc_excludetype        = get_option( 'rllc_exclude_type', '' );
    $rllc_excludecat         = get_option( 'rllc_exclude_cat', '' );
    $rllc_includecat         = get_option( 'rllc_include_cat', '' );
    $rllc_excludetags        = get_option( 'rllc_exclude_tag', '' );
    $rllc_includetags        = get_option( 'rllc_include_tag', '' );
    $rllc_excludeproduct_tmp = get_option( 'rllc_exclude_product', '' );
    $rllc_includeproduct_tmp = get_option( 'rllc_include_product', '' );
    $rllc_includesku_tmp     = get_option( 'rllc_include_sku', '' );
    $rllc_excludesku_tmp     = get_option( 'rllc_exclude_sku', '' );

    if ( ! empty( $rllc_excludetype ) ) {
        $rllc_tax_query[] = array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => $rllc_excludetype, 
            'operator' => 'NOT IN',
        );
    }

    if ( ! empty( $rllc_excludecat ) ) {
        $rllc_tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $rllc_excludecat, 
            'operator' => 'NOT IN',
        );
    }

    if ( ! empty( $rllc_includecat ) ) {
        $rllc_tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $rllc_includecat, 
            'operator' => 'IN',
        );
    }

    if ( ! empty( $rllc_excludetags ) ) {
        $rllc_tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field'    => 'id',
            'terms'    => $rllc_excludetags, 
            'operator' => 'NOT IN',
        );
    }
    if ( ! empty( $rllc_includetags ) ) {
        $rllc_tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field'    => 'id',
            'terms'    => $rllc_includetags, 
            'operator' => 'IN',
        );
    }
    if ( ! empty( $rllc_includesku_tmp ) ) {
        $rllc_metaquery[] =
            array(
                'key'     => '_sku',
                'value'   => explode( ',', $rllc_includesku_tmp ),
                'compare' => 'IN',
            );
    }
    if ( ! empty( $rllc_excludesku_tmp ) ) {
        $rllc_metaquery[] = array(
            'key'     => '_sku',
            'value'   => explode( ',', $rllc_excludesku_tmp ),
            'compare' => 'NOT IN',
        );
    }

    if ( ! empty( $rllc_excludeproduct_tmp ) ) {
        $query_args['post__not_in'] = $rllc_excludeproduct_tmp;
    }
    if ( ! empty( $rllc_includeproduct_tmp ) ) {
        $query_args['post__in'] = $rllc_includeproduct_tmp;
    }
    if ( ! empty( $rllc_tax_query ) ) {
        $query_args['tax_query'] = $rllc_tax_query;
    }
    if ( ! empty( $rllc_metaquery ) ) {
        $query_args['meta_query'] = $rllc_metaquery;
    }
    $query_args['post_type']      = 'product';
    $query_args['posts_per_page'] = '-1';
    $query = new WP_Query( $query_args );
    $rllc_product_ids_on_sale = wp_parse_id_list( array_merge( wp_list_pluck( $query->posts, 'ID' ), array_diff( wp_list_pluck( $query->posts, 'post_parent' ), array( 0 ) ) ) );
    set_transient( 'rllc_wc_onsale_page_products_onsale', $rllc_product_ids_on_sale, DAY_IN_SECONDS * 30 );
    return $rllc_product_ids_on_sale;
}
