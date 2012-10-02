/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

(function($) {
$.widget("x2.gvSettings", {

	prevGvSettings: '',
	saveGridviewSettingsTimeout: null,
	// self: null,
	// o: null,

	options: {
		viewName:'gridView',
		columnSelectorId:'column-selector',
		columnSelectorHtml:'',
		ajaxUpdate:false,
		saveTimeout:1000,
	},

	// setGridviewModel:function(model) {
		// viewName = model;
	// }

	_create: function() {
	
		var self = this;
		o = self.options;

		if(o.ajaxUpdate) {
			$(self.element).parent().find('.search-button').click(function() {
				$('.search-form').toggle();
				return false;
			});
		
		} else {
			this.element.closest('.grid-view').after(o.columnSelectorHtml);
			$('#'+o.columnSelectorId).find('input').bind('change',function() { self._saveColumnSelection(this,self); });
			// this.element.closest('div.grid-view').find('.column-selector-link').bind('click',function() { self._toggleColumnSelector(this); });
		}
			// $('#'+o.columnSelectorId).find('input').bind('change',function() { self._saveColumnSelection(this); });
			this.element.closest('div.grid-view').find('.column-selector-link').bind('click',function() { self._toggleColumnSelector(this,self); });
		// }
		this._setupGridviewResizing(self);
		this._setupGridviewDragging(self);
		this._compareGridviewSettings(self);
		
		
	},

	_setupGridviewResizing: function(self) {

		this.element.colResizable({disable:true});	// remove old colResizable class, if it exists
		this.element.colResizable({
			liveDrag:true,
			//gripInnerHtml:'<div class=\"grip\"></div>', 
			draggingClass:'dragging',
			onResize:function() { self._compareGridviewSettings(self); },
			onDrag:function() { clearTimeout(this.saveGridviewSettingsTimeout); }
		});
	},

	_setupGridviewDragging: function(self) {
		this.element.dragtable('destroy');	// reset if this was already activated
		this.element.dragtable({
			// delay:500,
			distance:10,
			complete:function(e,ui){
				self._setupGridviewResizing(self);
				self._compareGridviewSettings(self);
			},
			start:function(e,ui) {
				clearTimeout(this.saveGridviewSettingsTimeout);
			}
			// displayHelper: function(e,ui) {
				// console.log('display helpers ',ui);
			// }
		});
	},

	_compareGridviewSettings: function(self) {
		var o = self.options;

		var columns = this.element.find('tr:first th');
		var cols = this.element.find('col');
		var gvSettings = '{';
		var tableData = [];
		columns.each(function(i){
		
			var width = $(cols[i]).attr('width');
			tableData.push('\"'+$(this).attr('id').substr(2)+'\":'+width);
		});
		gvSettings += tableData.join(',') + '}';
		if(this.prevGvSettings != '' && this.prevGvSettings != gvSettings) {
			var encodedGvSettings = encodeURI(gvSettings);
			var links = $('div.grid-view table th a, div.grid-view div.pager a');
			
			links.each(function(i,element) {
				var link = $(element);
				var url = link.attr('href');
				var startPos = url.indexOf('&viewName=');
				if(startPos > -1)
					url = url.substr(0,startPos);

				link.attr('href',url+'&viewName='+self.options.viewName+'&gvSettings='+encodedGvSettings);
			});
		
			clearTimeout(this.saveGridviewSettingsTimeout);
			this.saveGridviewSettingsTimeout = setTimeout(function() {
				$.ajax({
					url: yii.baseUrl+'/index.php/site/saveGridviewSettings',
					type: 'GET',
					data: 'viewName='+self.options.viewName+'&gvSettings='+encodedGvSettings
				});

			},o.saveTimeout);
			
			
		}
		// console.debug(gvSettings);
		this.prevGvSettings = gvSettings;
	},

	_toggleColumnSelector: function(object, self) {
		var o = self.options;
		// console.debug('ugh');
		if(object) {
		//get the position of the link
			var xPos = $(object).position().left - 6;
			var yPos = self.element.position().top + 4;
			
			//show the menu directly over the placeholder
			$('#'+o.columnSelectorId).css( { 'left': xPos + 'px', 'top':yPos + 'px' } );
		}
		
		$('#'+o.columnSelectorId).fadeToggle(300,'swing',function() {
			if($('#'+o.columnSelectorId).is(':visible')) {
				$(document).bind('click.columnSelector',function(e) {
					// e.stopPropagation();
					// console.debug($(e.target).parent().parent());
					var clicked = $(e.target).add($(e.target).parents());
					if(!($(e.target).parents().is('#'+o.columnSelectorId) || clicked.hasClass('column-selector-link'))) {
						self._toggleColumnSelector(null,self);
					}
				});
			} else
				$(document).unbind('click.columnSelector');
		});
	},

	_saveColumnSelection: function(object,self) {
		var o = self.options;
		// $(document).unbind('click.columnSelector');
		var data = $(object).closest('form').serialize()+'&viewName='+self.options.viewName;
		if(data !== null && data != '') {
			$.fn.yiiGridView.update(this.element.closest('div.grid-view').attr('id'), {
				data: data
			});
		}
	}
});
})(jQuery);