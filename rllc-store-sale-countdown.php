<?php
/*
Plugin Name: Store Sale Countdown For WooCommerce
Description: Create sale on your shop site and show countdown for sales products from WooCommerce plugin.
Version: 1.0.0
Author: importerwc            
WC requires at least: 3.2.0
WC tested up to: 4.2
Tested up to: 5.4
License: GPLv2 or later
*/
  
@session_start();
if ( !defined( 'ABSPATH' ) ) {
    exit();
}
define( 'RLLC_RCODE_URI', plugin_dir_url( __FILE__ ) );
define( 'RLLC_RCODE_DIR', plugin_dir_path( __FILE__ ) );
define( 'RLLC_RCODE_ASSET_URI', RLLC_RCODE_URI . 'assets/' );

class RLLC_RCODE {
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'rllc_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'rllc_uninstall' ) );
        add_action( 'plugins_loaded', array( $this, 'rllc_init_woostoresalecountdown' ) );
    }

    public function rllc_before_template_part( $template_name, $rllc_template_path, $located, $args ) {
        $template = str_replace( array( '\\', '/' ), '-', $template_name );
        $template = sanitize_title( str_replace( '.php', '', $template ) );
        do_action( 'rllc_position_before-' . $template );
        if ( array_key_exists( 'rllc-countdown-position', $_REQUEST ) ) {
            echo "<code class=\"rllc-thim-position-code\">[before-{$template}]</code>";
        }
    }

    public function rllc_after_template_part( $template_name, $rllc_template_path, $located, $args ) {
        $template = str_replace( array( '\\', '/' ), '-', $template_name );
        $template = sanitize_title( str_replace( '.php', '', $template ) );
        do_action( 'rllc_position_after-' . $template );
        if ( array_key_exists( 'rllc-countdown-position', $_REQUEST ) ) {
            echo "<code class=\"rllc-thim-position-code\">[after-{$template}]</code>";
        }
    }

	public function rllc_add_hooks() {
		global $woocommerce;
		if( !$woocommerce ){
			return;
		}

		if ( version_compare( $woocommerce->version, '3.0.0', '>=' ) ) {
			add_filter( 'woocommerce_product_is_on_sale', 'rllc_woosales_woocommerce_product_is_on_sale', 1, 2  );
			add_filter( 'woocommerce_product_get_price', 'rllc_filter_woocommerce_product_get_price', 1, 3 );
			add_filter( 'woocommerce_product_variation_get_price', 'rllc_filter_woocommerce_product_get_price', 10, 4 ); 

		} else {
		    add_filter( 'woocommerce_get_price', 'rllc_filter_woocommerce_get_price', 10, 3 );
			add_filter( 'woocommerce_variation_get_price', 'rllc_filter_woocommerce_get_price', 10, 4 );
		}

		add_action( 'woocommerce_rllc_before_template_part', array( $this, 'rllc_before_template_part' ), 10, 4 );
		add_action( 'woocommerce_rllc_after_template_part', array( $this, 'rllc_after_template_part' ), 10, 4 );
        $pattern = '/[\[]?([a-zA-Z\-\_]+)[\]]?/';
        
        /* loop product */
        if ( rllc_woosale_categories_is_enabled() ) {
            $category_position = get_option( 'rllc_ob_categories_position', 0 );
            switch ( $category_position ) {
                case '0': // coundown position - above price
                    add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'rllc_loop_countdown' ), 9.9 );
                    break;
                case '1': // coundown position - above title
                    add_action( 'woocommerce_shop_loop_item_title', array( $this, 'rllc_loop_countdown' ), 9.9 );
                    break;
                case '2': // coundown position - above add-to-cart
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'rllc_loop_countdown' ), 9.9 );
                    break;
                case '3': // coundown position - below add-to-cart
                    add_action( 'woocommerce_after_shop_loop_item', array( $this, 'rllc_loop_countdown' ), 10.1 );
                    break;
                case '4': // coundown position - above thumbnail
                    add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'rllc_loop_countdown' ), 9.9 );
                    break;
                default:
                    $rllc_positions = get_option( 'rllc_ob_categories_element_text' );
                    $rllc_positions = explode( ',', $rllc_positions );
                    $rllc_positions = array_map( 'trim', $rllc_positions );
                    if ( sizeof( $rllc_positions ) )
                        foreach ( $rllc_positions as $position ) {
                            preg_match( $pattern, $position, $match );
                            if ( isset( $match[1] ) && $match[1] ) {
                                $position = $match[1];
                                add_action( 'rllc_position_' . $position, array( $this, 'rllc_loop_countdown' ) );
                            }
                        }
                    break;
            }
        }

        /* single product */
        if ( rllc_woosale_single_is_enabled() ) {
            $single_position = get_option( 'rllc_ob_detail_position', 0 );
            switch ( $single_position ) {
                case '0': // above tabs
                    add_action( 'woocommerce_after_single_product_summary', array( $this, 'rllc_single_countdown' ), 9.9 );
                    break;
                case '1': // below tabs
                    add_action( 'woocommerce_after_single_product_summary', array( $this, 'rllc_single_countdown' ), 10.1 );
                    break;
                case '2': // above short description
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 19.9 );
                    break;
                case '3': // below short description
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 20.1 );
                    break;
                case '4': // above add to cart
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 29.9 );
                    break;
                case '5': // below add to cart
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 30.1 );
                    break;
                case '6': // above title
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 4.9 );
                    break;
                case '7': // below title
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 5.1 );
                    break;
                case '8': // above price
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 9.9 );
                    break;
                case '9': // below price
                    add_action( 'woocommerce_single_product_summary', array( $this, 'rllc_single_countdown' ), 10.1 );
                    break;
                default: /* custom */
                    $rllc_positions = get_option( 'rllc_ob_single_element_text' );
                    $rllc_positions = explode( ',', $rllc_positions );
                    $rllc_positions = array_map( 'trim', $rllc_positions );
                    if ( sizeof( $rllc_positions ) )
                        foreach ( $rllc_positions as $position ) {
                            preg_match( $pattern, $position, $match );
                            if ( isset( $match[1] ) && $match[1] ) {
                                $position = $match[1];
                                add_action( 'rllc_position_' . $position, array( $this, 'rllc_single_countdown' ) );
                            }
                        }
                    break;
            }
        }
    }

    /* product loop */
    public function rllc_loop_countdown() {
        if ( !rllc_woosale_categories_is_enabled() || !rllc_woosales_has_countdown() || is_admin() ) {
            return;
        }
        global $product;
        $classes = array( 'rllc_ob_categories', 'rllc_store_sale_countdown', 'rllc_store_sale_countdown-category' );
        $options = get_option( 'rllc_ob_woosales_categories', array(
            'sort' => array( 'title', 'schedule', 'sale-bar', 'countdown' ),
            'enabled' => array( 'title' => 'on', 'schedule' => 'on', 'sale-bar' => 'on', 'countdown' => 'on' ) ) );

        $products = rllc_woosales_setup_product_countdown( false );
        $this->rllc_get_template( 'rllc_item.php', array(
            'products' => $products,
            'classes'  => $classes,
            'options'  => $options,
            // 'is_single'	=> true
        ) );
        /* Clear Cache */
        $this->rllc_clear_cache();
    }

    /* product single */
    public function rllc_single_countdown() {
		
        if ( !rllc_woosale_single_is_enabled() || !rllc_woosales_has_countdown() || is_admin() ) {
            return;
        }
        $classes = array( 'rllc_store_sale_countdown', 'rllc_store_sale_countdown-single' );
        $options = get_option( 'rllc_ob_woosales_single', array(
            'sort' => array( 'title', 'schedule', 'sale-bar', 'countdown' ),
            'enabled' => array( 'title' => 'on', 'schedule' => 'on', 'sale-bar' => 'on', 'countdown' => 'on' )
                ) );

        $products = rllc_woosales_setup_product_countdown();
        $this->rllc_get_template( 'rllc_item.php', array(
            'products' => $products,
            'classes'  => $classes,
            'options'  => $options,
            // 'is_single'	=> false
        ) );
        /* Clear Cache */
        $this->rllc_clear_cache();
    }

    /**
     * This is an extremely useful function if you need to execute any actions when your plugin is activated.
     */
    public function rllc_install() {
        global $wp_version;
        if ( version_compare( $wp_version, "2.9", "<" ) ) {
            deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
            wp_die( "This plugin requires WordPress version 2.9 or higher." );
        }
    }

    /**
     * This function is called when deactive.
     */
    public function rllc_uninstall() {
        //do something
    }

    /**
     * Function set up include javascript, css.
     */
    public function rllc_obScriptInit() {
        wp_enqueue_script( 'rllc-script-name', RLLC_RCODE_ASSET_URI . 'js/rllc-jquery.mb-comingsoon.min.js', array(), false, true);
        wp_enqueue_style( 'rllc-style-name', RLLC_RCODE_ASSET_URI . 'css/rllc_store_sale_countdown.css' );

        /* register main js */
        wp_register_script( 'rllc_store_sale_countdown', RLLC_RCODE_ASSET_URI . 'js/rllc_store_sale_countdown.js', array(), false, true );
        wp_localize_script( 'rllc_store_sale_countdown', 'rllc_store_sale_countdown_i18n', apply_filters( 'rllc_store_sale_countdown_i18n', array(
            'localization' => array(
                'days'      => esc_html__( 'days', 'rllc_store_sale_countdown' ),
                'hours'     => esc_html__( 'hours', 'rllc_store_sale_countdown' ),
                'minutes'   => esc_html__( 'minutes', 'rllc_store_sale_countdown' ),
                'seconds'   => esc_html__( 'seconds', 'rllc_store_sale_countdown' )
            )
        ) ) );
        wp_enqueue_script( 'rllc_store_sale_countdown' );
    }

    public function rllc_admin_assets() {
        global $pagenow;
        if ( 'widgets.php' == $pagenow ) {
            wp_enqueue_style( 'woocommerce_admin_styles' );
            wp_enqueue_script( 'select2' );
        }

        if ( !empty( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'rllc' && (empty($_REQUEST['section']) || !empty($_REQUEST['section']) && (sanitize_text_field($_REQUEST['section']) == 'rllc_general' ))) {
            wp_enqueue_script( 'rllc-admin-settings', RLLC_RCODE_ASSET_URI . 'js/rllc-admin-settings.js', array( 'wc-admin-variation-meta-boxes', 'wc-admin-meta-boxes', 'serializejson', 'media-models' ) );

            $random = (rand(10,100));
            //custom
            wp_register_script(
                'rllc_store_sale_countdown',
                plugin_dir_url( __FILE__ ) . 'assets/js/rllc-wc-rch-store-sale-admin.js?t='.$random,
                array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'timepicker-addon' ),
                '1.0.0',
                false
            );
            wp_localize_script( 'rllc_store_sale_countdown', 'rcodhub_wccsss', array( 'calendar_image' => WC()->plugin_url() . '/assets/images/calendar.png' ) );
            wp_enqueue_script( 'rllc_store_sale_countdown' );
            wp_enqueue_script(
                'timepicker-addon',
                plugin_dir_url( __FILE__ ) . '/assets/js/rllc-jquery-ui-timepicker-addon.js',
                array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
                '1.0.0',
                true
            );
            wp_enqueue_style( 'jquery-ui-datepicker' );
        }

        wp_enqueue_style( 'rllc-admin', RLLC_RCODE_ASSET_URI . 'css/rllc-admin.css' );
        wp_register_script( 'rllc_store_sale_countdown-admin', RLLC_RCODE_ASSET_URI . 'js/rllc_store_sale_countdown-admin.js', array( 'jquery' ), false, true );
        wp_localize_script( 'rllc_store_sale_countdown-admin', 'rllc_store_sale_countdown_i18n', apply_filters( 'rllc_store_sale_countdown_i18n', array(
            'sync_variation' => esc_html__( 'Sync Countdown Variable', 'rllc_store_sale_countdown' ),
            'confirm' => esc_html__( 'Set this countdown for all variable', 'rllc_store_sale_countdown' )
        ) ) );
        wp_enqueue_script( 'rllc_store_sale_countdown-admin' );
    }

    /**
     * Register widget
     */
    public function rllc_register_widgets() {
        require_once RLLC_RCODE_DIR . '/includes/rllc-class-rllc-widget-sale-products.php';
        register_widget( 'RLLC_RCODE_Widget_Sale_Products' );
    }

    /**
     * Load and custom CSS from setting
     */
    public function rllc_store_sale_countdown_style_load() {
        @$colors = array_map( 'esc_attr', (array) get_option( 'woocommerce_frontend_css_colors' ) );

        // Defaults
        if ( empty( $colors['primary'] ) ) {
            $colors['primary'] = '#ad74a2';
        }
        if ( empty( $colors['secondary'] ) ) {
            $colors['secondary'] = '#f7f6f7';
        }

        @$rllc_ob_use_color = get_option( 'rllc_ob_use_color' );
        if ( $rllc_ob_use_color ) {
            @$background_color      = get_option( 'rllc_ob_background_color' );
            @$time_color            = get_option( 'rllc_ob_time_color' );
            @$rllc_ob_bar_color     = get_option( 'rllc_ob_bar_color' );
            @$rllc_ob_bg_bar_color  = get_option( 'rllc_ob_bg_bar_color' );
        } else {
            @$background_color      = $colors['secondary'];
            @$time_color            = $colors['primary'];
            @$rllc_ob_bar_color     = $time_color;
            @$rllc_ob_bg_bar_color  = $background_color;
        }
        echo "<style type='text/css'>
			.rllc-counter-block .counter .number{background-color:$background_color;color:$time_color;}
			.rllc_ob_discount{background-color:$rllc_ob_bg_bar_color;}
			.rllc_ob_sale{background-color:$rllc_ob_bar_color}
		</style>";
    }

	/**
	 * Init when plugin load
	 */
	public function rllc_init_woostoresalecountdown() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'rllc_admin_notices', array(
					$this,
					'rllc_admin_notices' 
			) );
			return;
		}
		
		require_once ('includes/rllc-class-rllc-admin.php');
		require_once ('includes/rllc-functions.php');
		
		if ( ! is_admin() ) {
			require_once 'includes/rllc-class-rllc-shortcodes.php';
			/* intt shortcode */
			RLLC_RCODE_Shortcodes::init();
		}

		/**
		 * add action of plugin
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'rllc_admin_assets' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'rllc_obScriptInit' ) );
		add_action( 'widgets_init', array( $this, 'rllc_register_widgets' ) );
		add_action( 'wp_print_scripts', array( $this, 'rllc_store_sale_countdown_style_load' ) );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'rllc_check_quantity_product_in_sale' ) );
		add_action( 'wp_head', array( $this, 'rllc_add_hooks' ) );
	}

    public function rllc_admin_notices() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html__( '<strong>WooCommerce</strong> plugin is not rllc_installed or activated.', 'rllc_store_sale_countdown' ); ?></p>
        </div>
        <?php
    }

    /*
    * Function Setting link in plugin manager
    */
    public function rllc_settings_link( $links ) {
        $rllc_settings_link = '<a href="admin.php?page=wc-settings&tab=rllc_store_sale_countdown" title="' . esc_attr__( 'Settings', 'rllc_store_sale_countdown' ) . '">' . esc_html__( 'Settings', 'rllc_store_sale_countdown' ) . '</a>';
        array_unshift( $links, $rllc_settings_link );
        return $links;
    }

    /**
     * Function check quantity in sale
     */
    public function rllc_check_quantity_product_in_sale( $data ) {
        if ( isset( $data['product_id'] ) ) {
			$product_id  = $data['product_id'];
			$product     = wc_get_product($product_id);
			if(!$product->is_on_sale()){
				return $data;
			}
            $_quantity_discount = get_post_meta( $data['product_id'], '_quantity_discount', true );
            $_quantity_sale     = get_post_meta( $data['product_id'], '_quantity_sale', true );
            $_quantity_sale     = $_quantity_sale ? $_quantity_sale : 0;
            if ( $_quantity_discount ) {
                $total = absint( $_quantity_discount ) - absint( $_quantity_sale );
                if ( $total > 0 && $total < absint( $data['quantity'] ) ) {
                    $data['quantity'] = $total;
                }
            }
        }
        return $data;
    }

    /**
     * Clear cache. Support Supper Cache
     */
    protected function rllc_clear_cache() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        /* Clear Cache with Supper Cache */
        if ( function_exists( 'wp_cache_clean_cache' ) ) {
            global $file_prefix;
            wp_cache_clean_cache( $file_prefix );
        }
    }

    /* template path */
    public function rllc_template_path() {
        return apply_filters( 'rllc_store_sale_countdown_rllc_template_path', 'rllc_store_sale_countdown' );
    }

    /**
     * get template part
     *
     * @param   string $slug
     * @param   string $name
     *
     * @return  string
     */
    public function rllc_get_template_part( $slug, $name = '' ) {
        $template = '';

        // Look in yourtheme/slug-name.php and yourtheme/courses-manage/slug-name.php
        if ( $name ) {
            $template = locate_template( array( "{$slug}-{$name}.php", $this->rllc_template_path() . "/{$slug}-{$name}.php" ) );
        }

        // Get default slug-name.php
        if ( !$template && $name && file_exists( RLLC_RCODE_DIR . "/templates/{$slug}-{$name}.php" ) ) {
            $template = RLLC_RCODE_DIR . "/templates/{$slug}-{$name}.php";
        }

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/courses-manage/slug.php
        if ( !$template ) {
            $template = locate_template( array( "{$slug}.php", $this->rllc_template_path() . "{$slug}.php" ) );
        }

        // Allow 3rd party plugin filter template file from their plugin
        if ( $template ) {
            $template = apply_filters( 'rllc_store_sale_countdown_rllc_get_template_part', $template, $slug, $name );
        }
        if ( $template && file_exists( $template ) ) {
            load_template( $template, false );
        }

        return $template;
    }

    /**
     * Get other templates passing attributes and including the file.
     *
     * @param string $template_name
     * @param array  $args          (default: array())
     * @param string $rllc_template_path (default: '')
     * @param string $default_path  (default: '')
     *
     * @return void
     */
    public function rllc_get_template( $template_name, $args = array(), $rllc_template_path = '', $default_path = '' ) {
        if ( $args && is_array( $args ) ) {
            extract( $args );
        }

        $located = $this->locate_template( $template_name, $rllc_template_path, $default_path );
        if ( !file_exists( $located ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
            return;
        }
        // Allow 3rd party plugin filter template file from their plugin
        $located = apply_filters( 'rllc_store_sale_countdown_rllc_get_template', $located, $template_name, $args, $rllc_template_path, $default_path );

        do_action( 'rllc_store_sale_countdown_rllc_before_template_part', $template_name, $rllc_template_path, $located, $args );

        include( $located );

        do_action( 'rllc_store_sale_countdown_rllc_after_template_part', $template_name, $rllc_template_path, $located, $args );
    }

    /**
     * Locate a template and return the path for inclusion.
     */
    public function locate_template( $template_name, $rllc_template_path = '', $default_path = '' ) {

        if ( !$rllc_template_path ) {
            $rllc_template_path = $this->rllc_template_path();
        }

        if ( !$default_path ) {
            $default_path = RLLC_RCODE_DIR . '/templates/';
        }

        $template = null;
        // Look within passed path within the theme - this is priority
        $template = locate_template(
                array(
                    trailingslashit( $rllc_template_path ) . $template_name,
                    $template_name
                )
        );
        // Get default template
        if ( !$template ) {
            $template = $default_path . $template_name;
        }

        // Return what we found
        return apply_filters( 'rllc_store_sale_countdown_locate_template', $template, $template_name, $rllc_template_path );
    }

    public function rllc_get_template_content( $template_name, $args = array(), $rllc_template_path = '', $default_path = '' ) {
        ob_start();
        $this->rllc_get_template( $template_name, $args, $rllc_template_path, $default_path );
        return ob_get_clean();
    }
}

$GLOBALS['rllc_store_sale_countdown'] = new RLLC_RCODE();