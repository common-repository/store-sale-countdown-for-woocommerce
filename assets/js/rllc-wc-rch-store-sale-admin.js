(function( $ ) {
	'use strict';
	jQuery(document).ready(function($){	
		jQuery('.rllc_datetimepicker').rllc_datetimepicker({
			defaultDate: "",
			dateFormat: "yy-mm-dd",
			numberOfMonths: 1,
			showButtonPanel: true,
			showOn: "button",
			buttonImage: rcodhub_wccsss.calendar_image,
			buttonImageOnly: true
		});	
		//Sale type - deafult
		var rllc_sale_type  = jQuery('#rllc_sale_type').val();
		if(rllc_sale_type==1){ //day wise
			jQuery( "tr:eq( 4 )" ).hide();
			jQuery( "tr:eq( 5 )" ).show();
			jQuery( "tr:eq( 6 )" ).show();
			jQuery( "tr:eq( 7 )" ).show();
			jQuery( "tr:eq( 8 )" ).show();
			jQuery( "tr:eq( 9 )" ).show();
			jQuery( "tr:eq( 10 )" ).show();
			jQuery( "tr:eq( 11 )" ).show();

		}else{
			jQuery( "tr:eq( 4 )" ).show();
			jQuery( "tr:eq( 5 )" ).hide();
			jQuery( "tr:eq( 6 )" ).hide();
			jQuery( "tr:eq( 7 )" ).hide();
			jQuery( "tr:eq( 8 )" ).hide();
			jQuery( "tr:eq( 9 )" ).hide();
			jQuery( "tr:eq( 10 )" ).hide();
			jQuery( "tr:eq( 11 )" ).hide();

		}
		//onchange			
		jQuery('#rllc_sale_type').on('change', function(){
			var rllc_sale_type  = jQuery('#rllc_sale_type').val();
			if(rllc_sale_type==1){   //day wise
				jQuery( "tr:eq( 4 )" ).hide();
				jQuery( "tr:eq( 5 )" ).show();
				jQuery( "tr:eq( 6 )" ).show();
				jQuery( "tr:eq( 7 )" ).show();
				jQuery( "tr:eq( 8 )" ).show();
				jQuery( "tr:eq( 9 )" ).show();
				jQuery( "tr:eq( 10 )" ).show();
				jQuery( "tr:eq( 11 )" ).show();
			}else{
				jQuery( "tr:eq( 4 )" ).show();
				jQuery( "tr:eq( 5 )" ).hide();
				jQuery( "tr:eq( 6 )" ).hide();
				jQuery( "tr:eq( 7 )" ).hide();
				jQuery( "tr:eq( 8 )" ).hide();
				jQuery( "tr:eq( 9 )" ).hide();
				jQuery( "tr:eq( 10 )" ).hide();
				jQuery( "tr:eq( 11 )" ).hide();
			}
		});
	});	
})( jQuery );