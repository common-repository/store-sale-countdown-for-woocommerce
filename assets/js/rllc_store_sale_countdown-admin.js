( function ( $ ) {
    "use strict";
    var WooSalesCountdown = window.WooSalesCountdown = {
        init: function () {
            var _doc = $( document );
            _doc.on( 'woocommerce_variations_loaded', this.add_button );
            _doc.on( 'click', '.sync-variation', this.change );
        },
        add_button: function ( e ) {
            e.preventDefault();
            var variations = $( '#woocommerce-product-data .woocommerce_variation h3' );
            for ( var i = 0; i < variations.length; i++ ) {
                var _vari = $( variations[i] ), 
                        _sync_button = '<button type="button" class="button handlediv wc-reload tips sync-variation" data-tip="' + rllc_store_sale_countdown_i18n.sync_variation + '" data-variable="' + i + '"></button>';
                if ( _vari.find( '.sync-variation' ).length >= 1 ) {
                    continue;
                }
                _vari.append( _sync_button );
            }
            $( '.sync-variation' ).tipTip( {
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            } );
        },
        change: function ( e ) {
            e.preventDefault();
            var _self = $( this ),
                    _variable_id = _self.attr( 'data-variable' ),
                    _wrap = _self.parents( '#variable_product_options_inner' ),
                    _input = _wrap.find( 'input[name="woosales_sync_variation"]' ),
                    _variable = _self.parents( '.woocommerce_variation:first' );
            _self.toggleClass( 'active' );
            if ( _self.hasClass( 'active' ) ) {
                $( '.sync-variation' ).removeClass( 'active' );
                _self.addClass( 'active' );
                if ( _input.length == 0 ) {
                    _variable.append( '<input type="hidden" name="woosales_sync_variation" value="' + _variable_id + '" />' );
                } else {
                    _input.val( _variable_id );
                }
                $( '.woocommerce_variation' ).addClass( 'variation-needs-update' );
                $( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
            } else {
                $( '.sync-variation' ).removeClass( 'active' );
                _input.remove();
            }
        },
    };

    $( document ).ready( function () {
		function woosale_do_variation_action(event){
			var field_text   = $( '#field_to_edit option:selected' ).attr('data-desc');
			var value        = prompt( field_text );
			var data         = {};
			data.value       = value;
			return data;
		}
		$( 'select.variation_actions' ).on( 'variable_sale_price_dates_from_ajax_data', woosale_do_variation_action );
		$( 'select.variation_actions' ).on( 'variable_sale_price_dates_to_ajax_data', woosale_do_variation_action );
		$( 'select.variation_actions' ).on( '_quantity_discount_ajax_data', woosale_do_variation_action );
		$( 'select.variation_actions' ).on( '_quantity_sale_ajax_data', woosale_do_variation_action );
		$( 'select.variation_actions' ).on( '_woosale_from_time_ajax_data', woosale_do_variation_action );
		$( 'select.variation_actions' ).on( '_woosale_to_time_ajax_data', woosale_do_variation_action );

        var $wrp = $( '#woocommerce-product-data' );
        $wrp.on( 'click', '.sale_schedule', function () {
            $wrp.find( '.thim-countdown-options' )
                    .show().find( 'input' ).each( function () {
                var $inp = $( this ),
                        val = $inp.data( 'val' );
                $inp.val( val != undefined ? val : '' );
            } );
            return false;
        } );

        $wrp.on( 'click', '.cancel_sale_schedule', function () {
            $wrp.find( '.thim-countdown-options' )
                    .hide().find( 'input' ).each( function () {
                var $inp = $( this ),
                        val = $inp.val();
                $inp.data( 'val', val );
            } );
            return false;
        } );

        $wrp.find( '.thim-countdown-options' ).toggle( $wrp.find( '.sale_price_dates_fields' ).is( ':visible' ) );
        $( document ).on( 'click', '.rllc_woosales_switch', function ( e ) {
            e.preventDefault();
            var _self = $( this ),
                    _checkbox = _self.parents( 'label:first' ).find( '.checkbox' );
            _self.toggleClass( 'on' );
            if ( _self.hasClass( 'on' ) ) {
                _checkbox.attr( 'checked', true );
            } else {
                _checkbox.attr( 'checked', false );
            }
        } );
        WooSalesCountdown.init();
    } );
} )( jQuery );
