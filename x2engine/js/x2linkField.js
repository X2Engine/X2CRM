/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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