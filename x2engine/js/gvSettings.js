/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

function gvSettingsAfterUpdate(id,data,callback) {
	

	if(typeof callback == 'function')
		callback();
}


(function($) {
$.widget("x2.gvSettings", {

	prevGvSettings: '',
	saveGridviewSettingsTimeout: null,
	self: null,

	options: {
		viewName:'gridView',
		columnSelector:'column-selector',
		ajaxUpdate:false
	},

	// setGridviewModel:function(model) {
		// viewName = model;
	// }

	_create: function() {
	
		self = this;
		
		// if(!this.options.ajaxUpdate) {
			$('#'+this.options.columnSelector).find('input').bind('change',function() { self._saveColumnSelection(this); });
			this.element.closest('div.grid-view').find('.column-selector-link').bind('click',function() { self._toggleColumnSelector(this); });
		// }
		this._setupGridviewResizing();
		this._setupGridviewDragging();
		this._compareGridviewSettings();
		
		
	},

	_setupGridviewResizing: function() {
		this.element.colResizable({disable:true});	// remove old colResizable class, if it exists
		this.element.colResizable({
			liveDrag:true,
			//gripInnerHtml:'<div class=\"grip\"></div>', 
			draggingClass:'dragging',
			onResize:function() { self._compareGridviewSettings(); },
			onDrag:function() { clearTimeout(this.saveGridviewSettingsTimeout); }
		});
	},

	_setupGridviewDragging: function() {
		this.element.dragtable('destroy');	// reset if this was already activated
		this.element.dragtable({
			// delay:500,
			distance:10,
			complete:function(e,ui){
				self._setupGridviewResizing();
				self._compareGridviewSettings();
			},
			start:function(e,ui) {
				clearTimeout(this.saveGridviewSettingsTimeout);
			}
			// displayHelper: function(e,ui) {
				// console.log('display helpers ',ui);
			// }
		});
	},

	_compareGridviewSettings: function() {

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
					url: yiiBaseUrl+'/site/saveGridviewSettings',
					type: 'GET',
					data: 'viewName='+self.options.viewName+'&gvSettings='+encodedGvSettings
				});

			},2000);
			
			
		}
		this.prevGvSettings = gvSettings;
	},

	_toggleColumnSelector: function(object) {
		// console.debug('ugh');
		if(object) {
		//get the position of the link
			var xPos = $(object).position().left - 6;
			var yPos = self.element.position().top + 4;
			
			//show the menu directly over the placeholder
			$('.column-selector').css( { 'left': xPos + 'px', 'top':yPos + 'px' } );
		}
		
		$('.column-selector').fadeToggle(300,'swing',function() {
			if($('.column-selector').is(':visible')) {
				$(document).bind('click.columnSelector',function(e) {
					e.stopPropagation();
					var clicked = $(e.target).add($(e.target).parents());
					if(!(clicked.hasClass('column-selector') || clicked.hasClass('column-selector-link')))
						self._toggleColumnSelector();
				});
			} else
				$(document).unbind('click.columnSelector');
		});
	},

	_saveColumnSelection: function(object) {
		var data = $(object).closest('form').serialize()+'&viewName='+self.options.viewName;
		if(data !== null && data != '') {
			$.fn.yiiGridView.update(this.element.closest('div.grid-view').attr('id'), {
				data: data
			});
		}
	}
});
})(jQuery);