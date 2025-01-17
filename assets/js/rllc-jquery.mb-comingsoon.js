﻿(function ($) {
	var MbComingsoon;
	MbComingsoon = function (date, element, localization, speed, callBack, gmt, showText) {
		this.$el 		= $(element);
		this.gmt 		= gmt;
		this.showText 	= showText;
		this.end 		= date;
		this.active 	= false;
		this.interval 	= 1000;
		this.speed 		= speed;
		if (jQuery.isFunction(callBack))
			this.callBack = callBack;
		else
			this.callBack = null;
		this.localization = {days: "days", hours: "hours", minutes: "minutes", seconds: "seconds"};
		$.extend(this.localization, this.localization, localization);

	}

	MbComingsoon.prototype = {
		getCounterNumbers: function () {
			var result = {
					days   : {
						tens    : 0,
						units   : 0,
						hundreds: 0
					},
					hours  : {
						tens : 0,
						units: 0
					},
					minutes: {
						tens : 0,
						units: 0
					},
					seconds: {
						tens : 0,
						units: 0
					}
				}, millday 	= 1000 * 60 * 60 * 24,
				millhour 	= 1000 * 60 * 60,
				millminutes = 1000 * 60,
				millseconds = 1000,
				rest = 0
				;

			var now 	 = new Date();
			var time_gmt = now.getTimezoneOffset() / 60 + this.gmt;
			var diff 	 = this.end.getTime() - now.getTime() - (time_gmt * 60 * 60000);
			if (diff <= 0)
				return result;
			// Max number of days is 99 (i will expand in future versions)
			var days = Math.min(Math.floor(diff / millday), 999);
			rest 	 = diff % millday;
			result.days.hundreds= Math.floor(days / 100);
			var dayrest 	  	= days % 100;
			result.days.tens 	= Math.floor(dayrest / 10);
			result.days.units 	= dayrest % 10;

			var hours 			= Math.floor(rest / millhour);
			rest 				= rest % millhour;
			result.hours.tens 	= Math.floor(hours / 10);
			result.hours.units 	= hours % 10;

			var minutes 		 = Math.floor(rest / millminutes);
			rest 				 = rest % millminutes;
			result.minutes.tens  = Math.floor(minutes / 10);
			result.minutes.units = minutes % 10;

			var seconds 		 = Math.floor(rest / 1000);
			result.seconds.tens  = Math.floor(seconds / 10);
			result.seconds.units = seconds % 10;
			return result;
		},
		updatePart       : function (part) {
			var cn = this.getCounterNumbers();
			var $part = $('.' + part, this.$el);
			if (part == 'days') {
				this.setDayHundreds(cn.days.hundreds > 0);
				if ($part.find('.number.hundreds.show').html() != cn[part].hundreds) {
					var $n1 = $('.n1.hundreds', $part);
					var $n2 = $('.n2.hundreds', $part);
					this.scrollNumber($n1, $n2, cn[part].hundreds);
				}
			}
			if ($part.find('.number.tens.show').html() != cn[part].tens) {
				var $n1 = $('.n1.tens', $part);
				var $n2 = $('.n2.tens', $part);
				this.scrollNumber($n1, $n2, cn[part].tens);

			}
			if ($part.find('.number.units.show').html() != cn[part].units) {
				var $n1 = $('.n1.units', $part);
				var $n2 = $('.n2.units', $part);
				this.scrollNumber($n1, $n2, cn[part].units);
			}
		},
		timeOut : function () {
			var now = new Date()
			var time_gmt = now.getTimezoneOffset() / 60 + this.gmt;
			var diff = this.end.getTime() - now.getTime() - (time_gmt * 60 * 60000);
			if (diff <= 0)
				return true;
			return false;
		},
		setDayHundreds : function (action) {
			if (action)
				$('.counter.days', this.$el).addClass('with-hundreds');
			else
				$('.counter.days', this.$el).removeClass('with-hundreds');
		},
		updateCounter    : function () {
			this.updatePart('days');
			this.updatePart('hours');
			this.updatePart('minutes');
			this.updatePart('seconds');
			if (this.timeOut()) {
				this.active = false;
				if (this.callBack)
					this.callBack(this);
			}
		},
		localize         : function (localization) {
			if ($.isPlainObject(localization))
				$.extend(this.localization, this.localization, localization);
			$('.days', this.$el).siblings('.counter-caption').text(this.localization.days);
			$('.hours', this.$el).siblings('.counter-caption').text(this.localization.hours);
			$('.minutes', this.$el).siblings('.counter-caption').text(this.localization.minutes);
			$('.seconds', this.$el).siblings('.counter-caption').text(this.localization.seconds);
		},
		start            : function (interval) {
			if (interval)
				this.interval = interval;
			var i = this.interval;
			this.active = true;
			var me = this;
			setTimeout(function () {
				me.updateCounter();
				if (me.active)
					me.start();
			}, i);
		},
		stop             : function () {
			this.active = false;
		},
		scrollNumber     : function ($n1, $n2, value) {
			if ($n1.hasClass('show')) {
				$n2.removeClass('hidden-down')
					.css('top', '-100%')
					.text(value)
					.stop()
					.animate({ top: 0 }, this.speed, function () {
						$n2.addClass('show');
					});
				$n1.stop().animate({ top: "100%" }, this.speed, function () {
					$n1.removeClass('show')
						.addClass('hidden-down');
				});
			} else {
				$n1.removeClass('hidden-down')
					.css('top', '-100%')
					.text(value)
					.stop()
					.animate({ top: 0 }, this.speed, function () {
						$n1.addClass('show');
					});
				$n2.stop().animate({ top: "100%" }, this.speed, function () {
					$n2.removeClass('show')
						.addClass('hidden-down');
				});
			}
		}
	}

	jQuery.fn.mbComingsoon = function (opt) {
		var defaults = {
			interval    : 1000,
			callBack    : null,
			localization: { days: "days", hours: "hours", minutes: "minutes", seconds: "seconds" },
			speed       : 500,
			gmt         : 0,
			showText    : 1
		}
		var options = {};
		var content = '   <div class="rllc-counter-group" id="myCounter">' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter days">' +
			'               <div class="number show n1 hundreds">0</div>' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 hundreds">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'           <div class="counter-caption">days</div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter hours">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'           <div class="counter-caption">hours</div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter minutes">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'           <div class="counter-caption">minutes</div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter seconds">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'           <div class="counter-caption">seconds</div>' +
			'       </div>' +
			'   </div>';
		var content_notext = '   <div class="rllc-counter-group" id="myCounter">' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter days">' +
			'               <div class="number show n1 hundreds">0</div>' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 hundreds">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter hours">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter minutes">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'       </div>' +
			'       <div class="rllc-counter-block">' +
			'           <div class="counter seconds">' +
			'               <div class="number show n1 tens">0</div>' +
			'               <div class="number show n1 units">0</div>' +
			'               <div class="number hidden-up n2 tens">0</div>' +
			'               <div class="number hidden-up n2 units">0</div>' +
			'           </div>' +
			'       </div>' +
			'   </div>';
		return this.each(function () {
			var $this = $(this);
			var data = $this.data('mbComingsoon');
			if (!data) {
				if (opt instanceof Date)
					options.expiryDate = opt;
				else if ($.isPlainObject(opt))
					$.extend(options, defaults, opt);
				else if (typeof opt == "string")
					options.expiryDate = new Date(opt);
				if (!options.expiryDate)
					throw new Error('Expiry date is required!');

				data = new MbComingsoon(options.expiryDate, $this, options.localization, options.speed, options.callBack, options.gmt, options.showText);
				if (options.showText) {
					$this.html(content);
				} else {
					$this.html(content_notext);
				}
				data.localize();
				data.start();
			} else if (opt == 'start')
				data.start();
			else if (opt == 'stop')
				data.stop();
			else if ($.isPlainObject(opt)) {
				if (opt.expiryDate instanceof Date)
					data.end = opt.expiryDate;
				if ($.isNumeric(opt.interval))
					data.interval = opt.interval;
				if ($.isNumeric(opt.gmt))
					data.gmt = opt.gmt;
				if ($.isNumeric(opt.showText))
					data.showText = opt.showText;
				if ($.isFunction(opt.callBack))
					data.callBack = opt.callBack;
				if ($.isPlainObject(opt.localization))
					this.localize(opt.localization);
			}
		})
	}
})(jQuery)