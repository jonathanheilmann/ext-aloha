
/*
* EL7R_NOTIFY_JS v0.1
* Author: Ali AlNoaimi
* License: GPL v3
* www.the-ghost.com
*/

(function($) {
	$.fn.el7r_notify = function(options) {

		var text = options['text'];
		if(options['place_h']) { var place_h = options['place_h']; } else { var place_h = 'left'; }
		if(options['place_v']) { var place_v = options['place_v']; } else { var place_v = 'top'; }
		if(options['icon']) { var icon = options['icon']; } else { var icon = 'icons/pixel.gif'; }
		if(options['skin']) { var skin = options['skin']; } else { var skin = 'default'; }
		if(options['delay']) { var delay = options['delay']; } else { var delay = '2000'; }
		if(options['effect']) { var effect = options['effect']; } else { var effect = 'slide'; }
		if(options['ex']) { var ex = options['ex']; } else { var ex = false; }

		if(!$("#el7r_notify_"+place_v+"_"+place_h).length) {
			$("body").prepend("<div id='el7r_notify_"+place_v+"_"+place_h+"'></div>");
		}

		var foo = new Date;
		var unixtime_ms = foo.getTime();
		var unixtime = parseInt(unixtime_ms / 1000);

		var thisid=unixtime+'_'+Math.floor(Math.random()*5);

		var icon_html = '<span class="icon"><img src="'+icon+'" /></span>';
		icon_html = '';

		var ex_code = '';
		if(ex == 'true') {
			ex_code = '<span class="ex" onclick="$(this).parent().hide();">x</span>';
		}
		$("#el7r_notify_"+place_v+"_"+place_h).prepend('<div class="el7r_notify '+skin+'" id="alert_'+thisid+'" style="display:none">'+icon_html+' '+ex_code+' '+text+'</div>');

		if(effect == 'fade') {
			$("div#alert_"+thisid).fadeIn().delay(delay).fadeOut();
		} else if(effect == 'slide') {
			$("div#alert_"+thisid).slideDown("fast").delay(delay).slideUp("fast");
		} else if(effect == 'animate') {
			$("div#alert_"+thisid).show("fast").delay(delay).hide("fast");
		}

		$(".el7r_notify").hover(function() {
			$(this).css("opacity","0.5");
		},function() {
			$(this).css("opacity","1");
		});

	};

})( window.Aloha.jQuery || window.jQuery);