/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

	/**
	 * X2Engine
	 * 
	 */
(function() {
	var options = {
		
	};
	// var methods = {
		// set: function(msg) {
			
		// },
		// start: function(msg) {
			
		// }
	// };

	$.fn.x2link = function(options) {
	
		var opts = $.extend({
			source:""
		},options);

		return this.each(function() {
			$(this).autocomplete({
				source:function( request, response ) {
					$.ajax({
						url:opts.source,
						dataType:"json",
						data:{q:request.term},
						success:function(data) {
							// response($.map(data.geonames,function(item) {
								// return {
									// label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
									// value: item.name
								// }
							// }));
						}
					});
				},
				minLength:2,
				select:function(event,ui) {
					// log(ui.item ?
						// "Selected: " + ui.item.label :
						// "Nothing selected, input was " + this.value);
				},
				open:function() {
					$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
				},
				close:function() {
					$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
				}
			});
		});
	}
	
	// $.fn.titleMarquee = function(method) {
		// if (methods[method])
			// return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		// else
			// $.error( 'Method ' +  method + ' does not exist on titleMarquee' );
	// };
})(jQuery);