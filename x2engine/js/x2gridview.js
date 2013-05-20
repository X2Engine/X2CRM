/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

(function($) {


$.widget("x2.gridResizing", $.ui.mouse, {
	options:{
		minColWidth:30
	},
	
	table:$(),
	hCells:$(),
	cols:$(),
	grips:$(),
	gripContainer:$(),
	colWidths:[],
	
	mouseStartX:0,
	currentGrip:0,
	colStartW:0,
	
	/**
	 * Sets up table resizing
	 */
	_create:function() {
		// return;
		var self = this;
		this.table = $(this.element);
		this.hCells = this.table.find("tr:first").children();
		
		this.scanColWidths();
		
		// find colgroup and cols, create if missing
		var colgroup = this.table.find("colgroup").empty();
		if(colgroup.length === 0)
			colgroup = $(document.createElement('colgroup')).prependTo(this.table);
		
		for(var i=0;i<this.hCells.length;i++)	// create one <col> for each header cell
			$(document.createElement('col')).appendTo(colgroup);
		this.cols = colgroup.children("col");
		this.updateColWidths();
		
		this.table.addClass("x2grid-resizable");
		this.gripContainer = $(document.createElement("div")).addClass("x2grid-grips").insertBefore(this.table);
		
		this.table.find("td,th").removeAttr('width').css("width",null).each(function(i,elem) {
			$(elem).html("<div>"+$(elem).html()+"</div>");
		})
		
		this.createGrips();
		
		this.element = this.gripContainer;	// initialize mouse handling on the grips
		this._mouseInit();
		
	},
	_destroy:function() {
		this._mouseDestroy();
		this.gripContainer.remove();
		this.table.removeClass("x2grid-resizable");
	},
	/**
	 * Filter what to drag
	 */
	_mouseCapture:function(e) {
		// return $(e.target).is(".x2grid-grip");
		return true;
	},
	/**
	 * Start dragging. Determine which grip has been...gripped.
	 */
	_mouseStart:function(e) {
		this.table.addClass("x2grid-resizing");
		this.mouseStartX = e.pageX;
		this.currentGrip = $(e.target).index();
		this.colStartW = this.colWidths[this.currentGrip];	//this.currentGrip.position().left;
	},
	/**
	 * Called on mousemove event. Resizes column to left of current grip, minimum width of 30px
	 */
	_mouseDrag:function(e) {
		var w = Math.max(30,this.colStartW + e.pageX - this.mouseStartX);
		if(this.colWidths[this.currentGrip] !== w) {
			this.colWidths[this.currentGrip] = w;
			this.updateColWidths();
			this.updateGrips();
		}
	},
	_mouseStop:function(e) {
		this.table.removeClass("x2grid-resizing");
		this.grips.height(this.table.height());
	},
	/* 
	 * Scans current real column widths
	 */
	scanColWidths:function() {
		for(var i=0;i<this.hCells.length;i++) {
			var w = Math.max(this.options.minColWidth,this.hCells.eq(i).width() + 10);
			if(i > 0)	// except the first one, 
				w++;	// every column has a 1px border
			this.colWidths.push(w);
		}
	},
	updateColWidths:function() {
		for(var i in this.colWidths) {
			this.cols.eq(i).width(this.colWidths[i]);
		}
	},
	createGrips:function() {
		// make sure there are the right number of grips (only create/delete as needed)
		var colCount = this.colWidths.length;
		var gripCount;
		while((gripCount = this.grips.length) != colCount) {
			if(gripCount > colCount)
				this.grips.last().remove();
			else if(gripCount < colCount)
				this.grips = this.grips.add($(document.createElement("div")).addClass("x2grid-grip").height(this.table.height()).appendTo(this.gripContainer));
		}
		this.updateGrips();
	},
	updateGrips:function() {
		var self = this;
		var x = 1;
		this.grips.each(function(i,elem) {
			x += self.colWidths[i];
			$(elem).css("left",x+"px");
		});
		
	}
	
});

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
		saveTimeout:1000
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
		// this.element.dragtable('destroy');	// reset if this was already activated
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
					url: yii.scriptUrl+'/site/saveGridviewSettings',
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