(function($) {
	$(window).load(function() {
		$('input[name="variation_id"]').change(function() {
			var pv_id = parseInt($(this).val());
			$('.rllc_ob_product_avariable_detail').slideUp();
			if (pv_id) {
				$('.rllc_ob_product_detail_' + pv_id).slideDown();
			}
		});
	});

	$.fn.rllc_store_sale_countdown = function() {
		var countdowns = this;
		for (var i = 0; i < countdowns.length; i++) {
			var _countdown 	= $(countdowns[i]);
			var speed 		= _countdown.attr('data-speed');
			var remain 		= parseInt(_countdown.attr('data-timestamp-remain'))*1000;
			var gmt 		= _countdown.attr( 'data-gmt' );
			var time 		= _countdown.attr('data-time');
			var showtext 	= _countdown.attr('data-showtext');
			var current_time = new Date();
			var expiryTime  = current_time.getTime()+remain;
			var expiryDate 	= new Date(expiryTime);
			var gmt 		= -expiryDate.getTimezoneOffset() / 60;
			var options 	= {
				expiryDate : expiryDate,
				speed : speed ? speed : 500,
				gmt : gmt,
				showText : parseInt(showtext),
				localization : rllc_store_sale_countdown_i18n.localization
			};
			_countdown.mbComingsoon(options);
		}
	}
	$(document).ready(function() {
		$('.woosales-counter').rllc_store_sale_countdown();
	});
})(jQuery);