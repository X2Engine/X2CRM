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
		minColWidth:30,
		onResize:$.noop,
		onDrag:$.noop,
		ignoreLastCol:false
	},
	originalElement:null,
	tables:$(),
	t1:{
		table:$(),
		firstRow:$(),
		masterCells:$(),
		grips:$(),
		gripContainer:$(),
	},
	t2:{
		table:$(),
		firstRow:$(),
		masterCells:$(),
		grips:$(),
		gripContainer:$(),
	},

	colWidths:[],

	mouseStartX:0,
	currentGrip:0,
	colStartW:0,

	// hoverCell:$(),
	// hoverTimeout:null,

	/**
	 * Sets up table resizing
	 */
	_create:function() {
		var self = this;
		if(this.element.is('table'))
			this.tables = $(this.element);
		else
			this.tables = $(this.element).find('table.items').addClass("x2grid-resizable"); //.data('x2resizableGrid',true);

		this.t1.table = this.tables.eq(0);
		this.t2.table = this.tables.eq(1);
		this.t1.firstRow = this.t1.table.find("tr:first");
		this.t2.firstRow = this.t2.table.find("tr:first");
		this.t1.masterCells = this.t1.firstRow.children();
		this.t2.masterCells = this.t2.firstRow.children();

		this.scanColWidths();
		this.resetColWidths();

		this.t1.gripContainer = $(document.createElement("div")).addClass("x2grid-grips").insertBefore(this.t1.table);
		this.t2.gripContainer = $(document.createElement("div")).addClass("x2grid-grips").insertBefore(this.t2.table);
		this.createGrips();

		this.originalElement = this.element;

		this.element = this.t1.gripContainer.add(this.t2.gripContainer);	// only apply mouse handling to the grips

		this._mouseInit();
	},
	_destroy:function() {
		this._mouseDestroy();
		this.element = this.originalElement;
		this.t1.gripContainer.remove();
		this.t2.gripContainer.remove();
		this.tables.removeClass("x2grid-resizable");
		this.element.removeData('x2-gridResizing');
		// this.table.off("mouseenter,mouseleave");
	},
	/**
	 * Start dragging. Determine which grip has been...gripped.
	 */
	_mouseStart:function(e) {
		// this.table.addClass("x2grid-resizing");
		this.tables.css('cursor','col-resize');
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
			if(typeof this.options.onDrag === 'function')
				this.options.onDrag(e);
				// e.currentTarget = t[0]; cb(e); }
			this.updateGrips();
			this.updateColWidth(this.currentGrip);
		}
	},
	_mouseStop:function(e) {
		this.currentGrip = 0;
		// this.tables.removeClass("x2grid-resizing");
		this.tables.css('cursor','');
		if(typeof this.options.onResize === 'function')
			this.options.onResize(e);
	},
	/*
	 * Scans current real column widths
	 */
	scanColWidths:function() {
		this.colWidths = [];	// clear previous stuff
		var colCount = this.t1.masterCells.length;
		if(this.options.ignoreLastCol)
			colCount--;
		for(var i=0;i<colCount;i++) {
			var cell = this.t1.masterCells.eq(i);
			var w = Math.max(this.options.minColWidth,cell.width());
			// if(i === 0)	// except the first one,
				// w++;	// every column has a 1px border
			this.colWidths.push(w);
		}
	},
	updateColWidth:function(index) {
		this.t1.masterCells.get(index).style.width = this.colWidths[index]+"px";
		if(index < this.t2.masterCells.length)
			this.t2.masterCells.get(index).style.width = (this.colWidths[index]-9)+"px";
	},
	resetColWidths:function(row) {
		var parent = this.t1.firstRow.parent();
		this.t1.firstRow.detach();
		for(var i=0;i<this.t1.masterCells.length;i++) {
			if(this.colWidths[i] !== undefined)
				this.t1.masterCells.get(i).style.width = this.colWidths[i]+"px";
		}
		this.t1.firstRow.prependTo(parent);

		if(this.t2.masterCells.length > 1) {
			parent = this.t2.firstRow.parent();
			this.t2.firstRow.detach();
			for(var i=0;i<this.t2.masterCells.length;i++) {
				if(this.colWidths[i] !== undefined)
					this.t2.masterCells.get(i).style.width = (this.colWidths[i]-9)+"px";
			}
			this.t2.firstRow.prependTo(parent);
		}
	},
	createGrips:function() {
		// make sure there are the right number of grips (only create/delete as needed)

		var colCount = this.colWidths.length;
		var gripCount = 0;
		var t1Height = this.t1.table.height();
		var t2Height = this.t2.table.height();
		this.t1.grips = $();	// not sure why but these need to be cleared after an AJAX rerfresh
		this.t2.grips = $();

		while((gripCount = this.t1.grips.length) < colCount) {
			this.t1.grips = this.t1.grips.add($(document.createElement("div")).height(t1Height).appendTo(this.t1.gripContainer));
			this.t2.grips = this.t2.grips.add($(document.createElement("div")).height(t2Height).appendTo(this.t2.gripContainer));
		}
		this.updateGrips();
	},
	updateGrips:function() {
		var self = this;
		var x = -1;
		for(var i=0;i<this.currentGrip;i++)
			x += self.colWidths[i]+2;

		for(var i=this.currentGrip;i<this.t1.grips.length;i++) {
			x += self.colWidths[i]+2;
			this.t1.grips.get(i).style.left = x+"px";
			if(this.t2.grips.length)
				this.t2.grips.get(i).style.left = x+"px";
		}
	}

});



$.widget("x2.colDragging", /* $.ui.mouse, */ {
	options:{
		start:$.noop,
		complete:$.noop
	},

	startMouseX:0,

	tables:$(),

	t1:{},
	t2:{},
	colgroup:$(),
	colWidths:[],
	spacers:[],		// an array of either undefined or {elem:[spacer cell],width:[spacer width]}
	timeout:null,
	dragged:{
		col:$(),
		cell1:$(),
		// cell2:$(),
		width:0,
		index:0
	},

	helperTemplate:null,
	helper:$(),
	helperStartPos:{},

	hoverIndex:-1,
	tableOffsetX:0,

	dragging:false,

	_create:function() {
		var self = this;
		this.tableOffsetX = $(this.element).offset().left;
		var tables = this.element.find("table.items");

		this.t1.table = tables.eq(0);
		this.t1.firstRow = this.t1.table.find("tr:first");
		this.t1.masterCells = this.t1.firstRow.children();

		this.t2.table = tables.eq(1);
		this.t2.firstRow = this.t2.table.find("tr:first");
		this.t2.masterCells = this.t2.firstRow.children();

		this.helperTemplate = $('<div class="grid-view"><table class="x2grid-helper x2grid-resizable items"><thead><tr></tr></thead></table></div>');

		this.t1.masterCells.each(function(i,elem) {
			$(elem).disableSelection().bind('selectstart',function(e){e.preventDefault();return false;});
		});

		this.t1.firstRow.bind('mousedown.colDragging',function(startEvent) {
			startEvent.preventDefault();

			if($(startEvent.target).closest('th').is(':last-child'))
				return false;
			self.startMouseX = startEvent.pageX;
			$(document).bind('mousemove.colDragging',function(dragEvent) {	// listen for mousemove anywhere in the window
				dragEvent.preventDefault();
				if(!self.dragging && Math.abs(dragEvent.pageX - self.startMouseX) > 10) {	// start actually dragging if they move the mouse at least 10px
					self.dragging = true;
					self._mouseStart(startEvent);	// fire _mouseStart() only once
					self._mouseDrag(dragEvent);
				} else if(self.dragging) {
					self._mouseDrag(dragEvent);		// fire _mouseDrag() a bunch
				}
			}).bind('mouseup.colDragging',function(stopEvent) {
				$(this).unbind('mousemove.colDragging');	// stop dragging on mouseup anywhere
				if(self.dragging) {
					self.dragging = false;
					self._mouseStop(stopEvent);
			}});
			return false;
		});
	},
	_destroy:function() {
		this.t1.firstRow.unbind('mousedown.colDragging');
		$(document).unbind('mousemove.colDragging mouseup.colDragging');
	},
	/**
	 * Start dragging.
	 */
	_mouseStart:function(e) {
		this.options.start(e);

		var self = this;
		this.dragging = true;
		this.startMouseX = e.pageX;

		this.t1.masterCells = this.t1.firstRow.children();	// rescan, these cells may have been reordered

		this.colWidths = [];
		this.t1.masterCells.each(function(i,elem) {
			var w = $(elem).outerWidth();
			// if(i > 0)
				// w++;
			self.colWidths.push(w);
		});

		this.spacers = [];
		this.dragged.cell1 = $(e.target).closest('td,th');
		this.dragged.index = this.dragged.cell1.index();
		// this.dragged.cell2 = this.t2.firstRow.eq(this.dragged.index);
		this.dragged.width = this.colWidths[this.dragged.index];

		this.hoverIndex = this.dragged.index;

		this.helperStartPos = this.dragged.cell1.offset();


		this.helper = this.helperTemplate.clone().width(this.dragged.width);
		this.helper.find('tr').append(this.dragged.cell1.clone());
		this.helper.offset(this.helperStartPos).appendTo('body');

		this.dragged.t1Col = this.t1.table.find('tr').children(':nth-child('+(this.dragged.index+1)+')');
		this.dragged.t2Col = this.t2.table.find('tr').children(':nth-child('+(this.dragged.index+1)+')');

		this._addSpacers();
		this.dragged.t1Col.remove();
		// this.dragged.t2Col.remove();

		this.spacers[this.dragged.index].width = this.dragged.width;
		this.spacers[this.dragged.index].cell1.style.width = this.dragged.width+'px';
		// this.spacers[this.dragged.index].cell2.style.width = this.dragged.width+'px';
		this.spacers[this.dragged.index].hidden = false;


		if(this.timeout === null)
			this.timeout = setInterval(function(){ self._animate(); },20);
	},
	/**
	 * Called on mousemove event.
	 */
	_mouseDrag:function(e) {
		this.helper.offset({top:this.helperStartPos.top,left:this.helperStartPos.left + e.pageX - this.startMouseX});

		// hoverIndex must be >= 0 and <= [# of cols - 2] (the last col is empty, and the dragged col has been removed)
		this.hoverIndex = Math.max(0,Math.min(this.t1.masterCells.length-2,this._getTargetIndex(e.pageX)));
		// if(this.hoverIndex === (this.hoverIndex = this._getTargetIndex(e.pageX))) {
			// this.dragged.col.insertBefore(this.colgroup.children().not(this.dragged.col).eq(this.hoverIndex));
			// this.dragged.cell.insertBefore(this.t1.masterCells.not(this.dragged.cell).eq(this.hoverIndex)); //.css('width',this.dragged.width+'px');
		// }
	},
	_mouseStop:function(e) {
		// return;
		this.dragging = false;
		clearInterval(this.timeout);
		this.timeout = null;

		this.t1.table.find('td.spacer,th.spacer').remove();
		// this.t2.table.find('td.spacer,th.spacer').remove();

		this.helper.remove();

		// this.dragged.col.css('width',this.dragged.width+'px');
		// this.dragged.cell; // /* .css('width',this.dragged.width+'px') */.removeClass('x2grid-hidden-col');
		// if(this.dragged.index !== this.hoverIndex) {
		// var targetCol = this.colgroup.children().eq(this.hoverIndex);
		// var targetCell = this.t1.masterCells.eq(this.hoverIndex);
		// if(targetCell === null) {
			// targetCol = this.colgroup.children().last();
			// targetCell = this.t1.masterCells.last();
		// }
		// this.dragged.col.insertBefore(targetCol);

		// this.dragged.cell.insertBefore(targetCell);

		var cols = this.t1.masterCells.length;

		var t1cells = this.t1.table.find('tr').children();	// array of all cells
		var t2cells = this.t2.table.find('tr').children();

		for(var i=0;i<this.dragged.t1Col.length;i++)
			$(this.dragged.t1Col[i]).insertBefore(t1cells.eq(i*(cols-1) + this.hoverIndex));	// one column has been removed, so we have to use (col - 1) for the table width

		if(this.hoverIndex >= this.dragged.index)	// meanwhile in table 2, the original dragged column hasn't been removed so we have to add 1
			this.hoverIndex++;						// to get the real index (if the new position is to the right of the starting position)
		for(var i=0;i<this.dragged.t2Col.length;i++)
			$(this.dragged.t2Col[i]).insertBefore(t2cells.eq(i*cols + this.hoverIndex));

		if(this.timeout !== null)
			clearInterval(this.timeout);

		this.hoverIndex = -1;

		this.options.complete(e);
	},
	/**
	 * Determine which column's starting position the mouse is over
	 */
	_getTargetIndex:function(x) {
		var offset = this.tableOffsetX;
		var i;
		for(i=0;i<this.colWidths.length;i++) {
			offset += this.colWidths[i];	// add one for the border
			if(x < offset)
				return i;
		}
		return i;
	},
	/**
	 *
	 */
	_addSpacers:function() {
		var headerRows = this.t1.table.find("tr").not(this.t1.firstRow);
		// var bodyRows = this.t2.table.find("tr").not(this.t2.firstRow);

		var headerSpacers = $(document.createElement('th')).addClass('spacer').css('width','0px').insertBefore(this.t1.masterCells);
		// var bodySpacers = $(document.createElement('td')).addClass('spacer').css('width','0px').insertBefore(this.t2.masterCells);

		$(document.createElement('td')).addClass('spacer').insertBefore(headerRows.children());
		// $(document.createElement('td')).addClass('spacer').insertBefore(bodyRows.children());

		for(var i=0;i<this.colWidths.length;i++) {
			if(i === this.hoverIndex)
				continue;
			// if(i === this.hoverIndex + 1) {	// don't add a spacer where the dragged column is originating; that would result in 2 sequential spacers (oh my!)
				// this.spacers.push(false);
			// } else {
				this.spacers.push({
					cell1:headerSpacers.get(i),
					// cell2:bodySpacers.get(i),
					width:0,
					hidden:true
				});
			// }
		}
	},
	/**
	 * Animates the sliding headers by widening the target position's spacer by 25%
	 * (or at least 1px) and evenly removing the width from other spacers.
	 * Runs every 20ms until the user stops dragging.
	 */
	_animate:function() {
		var currentSpacer = this.spacers[this.hoverIndex];
		// if(currentSpacer === false)
			// currentSpacer = this.spacers[this.hoverIndex-1]
			// return;

		var remainingWidth = this.dragged.width - currentSpacer.width;
		if(remainingWidth > 0) {
			currentSpacer.hidden = false;
			dx = Math.ceil(remainingWidth / 4.0);	// half the remaining difference, minimum 1px
			currentSpacer.width += dx;

			while(dx-- > 0) {	// loop through the other spacers removing 1px at a time until we get to dx pixels
				for(var i=0;i<this.spacers.length;i++) {
					if(i !== this.hoverIndex && this.spacers[i] !== false && this.spacers[i].width > 0) {
						this.spacers[i].width--;
						break;
					}
				}
			}
			for(var i=0;i<this.spacers.length;i++) {	// apply all the changed widths
				var spacer = this.spacers[i];
				if(spacer !== false && !spacer.hidden) {
					spacer.cell1.style.width = spacer.width+'px';	// otherwise set the new width
					// spacer.cell2.style.width = spacer.width+'px';
					// spacer.col.css('width',spacer.width+'px');

					if(spacer.width <= 0)
						spacer.hidden = true;
				}
			}
		}
	}
});

$.widget("x2.gvSettings", {

	prevGvSettings: '',
	saveGridviewSettingsTimeout: null,
	tables:$(),
    _lastCheckedCheckboxId: undefined, // used for multiselect
    _shiftPressed: false, // used for multiselect
    _SHIFTWHICH: 16, // used for multiselect

	options: {
		viewName:'gridView',
		columnSelectorId:'column-selector',
		columnSelectorHtml:'',
		ajaxUpdate:false,
		saveSettings:true,
		saveTimeout:1000
	},

	// setGridviewModel:function(model) {
		// viewName = model;
	// }

	_create: function() {

		var self = this;
		var o = self.options;

		if(o.ajaxUpdate) {
			this.element.find('.search-button').click(function() {
				$('.search-form').toggle();
				return false;
			});
		} else {
			this.element.after(o.columnSelectorHtml);
			$('#'+o.columnSelectorId).find('input').bind('change',function() { 
                self._saveColumnSelection(this,self); 
            });
			// this.element.closest('div.grid-view').find('.column-selector-link').bind('click',function() { self._toggleColumnSelector(this); });
		}
			// $('#'+o.columnSelectorId).find('input').bind('change',function() { self._saveColumnSelection(this); });
			this.element.find('.column-selector-link').bind('mousedown',function() { 
                self._toggleColumnSelector(this,self); 
            });
		// }

		this.tables = this.element.find('table.items');

		this._setupGridviewResizing(self);
		this._setupGridviewDragging(self);
		this._setupGridviewChecking(self);
		this._compareGridviewSettings(self);

		this.element.find('.yiiPager').on('click','a',function() {
			$('html,body').animate({scrollTop:0},500,'swing');
		});

        this.element.find('.auto-resize-button').on('click',function() {
			self._autoSizeColumns(self);
		});

		// var headerHeight = this.tables.eq(0).height();
		// this.tables.eq(0).parent().css('margin-right',this.getScrollbarWidth()+'px');
		// this.tables.eq(1).parent().css({
			// 'margin-top':'-'+headerHeight+'px',
			// 'padding-top':headerHeight+'px'
		// });
		this.tables.eq(1).parent().scroll(function() {
			self.tables.eq(0).parent().scrollLeft(self.tables.eq(1).parent().scrollLeft());
		});
	},

    /*
    Set up grid behavior which enables multiselect using shift + check
    */
	_setupGridviewChecking:function(self) {

        // check/uncheck all boxes between first and last
        function checkUncheckAllBetween (check, firstCheckboxId, lastCheckboxId) {
            x2.DEBUG && console.log ('checkUncheckAllBetween: ' + check + ',' + firstCheckboxId + 
                ',' + lastCheckboxId);

            self.element.find ('[type="checkbox"]').each (function () {
                var currCheckboxId = parseInt ($(this).attr ('id').match (/[0-9]+$/));
                if (currCheckboxId >= firstCheckboxId && currCheckboxId <= lastCheckboxId) {
                    if (check) {
                        $(this).attr ('checked', 'checked');
                    } else {
                        $(this).removeAttr ('checked');
                    }
                }
            });
        }

        // checkbox behavior
        this.element.find ('[type="checkbox"]').on ('change', function () {
            var checkboxId = parseInt ($(this).attr ('id').match (/[0-9]+$/));
            if (checkboxId === null) return; // invalid checkbox
            //x2.DEBUG && console.log ('_setupGridviewChecking: checkboxId = ' + checkboxId);
            var checked = $(this).is (':checked');
            //x2.DEBUG && console.log ('_setupGridviewChecking: checked = ' + checked);
            x2.DEBUG && console.log ('_setupGridviewChecking: checkbox changed: _shiftPressed = ' +
                self._shiftPressed);
            if (self._shiftPressed && 
                ((checked && checkboxId !== self._lastCheckedCheckboxId) || 
                (!checked && checkboxId !== self._lastUncheckedCheckboxId))) {

                var lastTouched;
                if (self._lastUncheckedCheckboxId) {
                    lastTouched = self._lastUncheckedCheckboxId;
                } else {
                    lastTouched = self._lastCheckedCheckboxId;
                }

                var firstCheckboxId, lastCheckboxId;
                if (checkboxId < lastTouched) {
                    firstCheckboxId = checkboxId;
                    lastCheckboxId = lastTouched; 
                } else { // checkboxId > lastTouched
                    lastCheckboxId = checkboxId;
                    firstCheckboxId = lastTouched; 
                }
                checkUncheckAllBetween (checked, firstCheckboxId, lastCheckboxId);
            }

            if (checked) {
                self._lastCheckedCheckboxId = checkboxId; // save last checked
                self._lastUncheckedCheckboxId = undefined;
            } else { // !$(this).is (':checked') 
                self._lastUncheckedCheckboxId = checkboxId; // save last unchecked
                self._lastCheckedCheckboxId = undefined;
            }
        });
        //x2.DEBUG && console.log (this.element);

        // set and unset shift property
        $(document).on ('keydown', function (evt) {
            if (evt.which === self._SHIFTWHICH) self._shiftPressed = true;
            //x2.DEBUG && console.log ('shift up ' + evt.which);
        });
        $(document).on ('keyup', function (evt) {
            if (evt.which === self._SHIFTWHICH) self._shiftPressed = false;
            //x2.DEBUG && console.log ('shift up ' + evt.which);
        });

    },
	_setupGridviewResizing:function(self) {
		if(this.element.data('x2-gridResizing') !== undefined) {
			this.element.gridResizing("destroy");
		}
		this.element.gridResizing({
			onResize:function(){ self._compareGridviewSettings(self); },
			onDrag:function(){ clearTimeout(self.saveGridviewSettingsTimeout); },
			ignoreLastCol:true
		});
	},

	_setupGridviewDragging:function(self) {
		this.element.colDragging({
			start:function(){
				clearTimeout(self.saveGridviewSettingsTimeout);
			},
			complete:function(){
				self._setupGridviewResizing(self);
				self._compareGridviewSettings(self);
			}
		});
	},

	_compareGridviewSettings:function(self) {
		var o = self.options;
		var headerCells = this.tables.eq(0).find('tr:first th');

		var cols = this.tables.eq(0).find('tr').first().children();
		var gvSettings = '{';
		var tableData = [];
		for(var i=0;i<headerCells.length-1;i++) {
			var width = cols.eq(i).width();
			if(width != 0)
				tableData.push('\"'+headerCells.eq(i).attr('id').substr(2)+'\":'+width);
		}
		gvSettings += tableData.join(',') + '}';
		if(this.prevGvSettings != '' && this.prevGvSettings != gvSettings) {
			var encodedGvSettings = encodeURI(gvSettings);
			var links = $('div.grid-view table th a, div.grid-view div.pager a');

			links.each(function(i,elem) {
				var link = $(elem);
				var url = link.attr('href');
				var startPos = $.inArray ('&viewName=', url);
				if(startPos > -1)
					url = url.substr(0,startPos);

				link.attr('href',url+'&viewName='+o.viewName+'&gvSettings='+encodedGvSettings);
			});

			clearTimeout(this.saveGridviewSettingsTimeout);
			this.saveGridviewSettingsTimeout = setTimeout(function() {
				if(o.saveSettings) {
					$.ajax({
						url: yii.scriptUrl+'/site/saveGridviewSettings',
						type: 'GET',
						data: 'viewName='+o.viewName+'&gvSettings='+encodedGvSettings
					});
				}
			},o.saveTimeout);


		}
        // console.debug(gvSettings);
		this.prevGvSettings = gvSettings;
	},

	_toggleColumnSelector: function(object, self) {
		var options = self.options;

        // check if fixed header is hidden
        if ($('#x2-gridview-top-bar-outer').length && 
            !$('#x2-gridview-top-bar-outer').is (':visible')) return;

        var fadeOut;
        if($('#'+options.columnSelectorId).is(':visible')) {
            fadeOut = true;
        } else {
            fadeOut = false;
        }

        if (fadeOut) {
            $('.column-selector-link').removeClass ('clicked');
		    $('#'+options.columnSelectorId).fadeOut(300,'swing',afterFadeOut);

        } else {
            // get the position of the link
            var xPos = $('.column-selector-link').position().left;
            var yPos = self.tables.eq(0).parent().position().top;

            //show the menu directly over the placeholder
            //$('#'+o.columnSelectorId).css( { 'left': xPos + 'px', 'top':yPos + 'px' } );
            $('#'+options.columnSelectorId).attr ('style', 'left: ' + xPos + 'px;');
			$(".column-selector-link").addClass('clicked');
		    $('#'+options.columnSelectorId).fadeIn(300,'swing',afterFadeIn);
        }


        function afterFadeOut () {
            x2.DEBUG && console.log ('_toggleColumnSelector: fade toggle');
            $(document).unbind('click.columnSelector');
        }

        function afterFadeIn () {

            // enable close on click outside
            $(document).bind('click.columnSelector',function(e) {
                // e.stopPropagation();
                // console.debug($(e.target).parent().parent());
                var clicked = $(e.target).add($(e.target).parents());
                if(!($(e.target).parents().is('#'+options.columnSelectorId) || 
                   clicked.hasClass('column-selector-link'))) {
                    self._toggleColumnSelector(null,self);
                }
            });
        }

	},

	_saveColumnSelection: function(object,self) {
		// $(document).unbind('click.columnSelector');
		var data = $(object).closest('form').serialize()+'&viewName='+self.options.viewName;
		if(data !== null && data != '') {
			$.fn.yiiGridView.update(this.element.attr('id'), {
				data: data
			});
		}
	},

	getScrollbarWidth:function() {
		var outer = $(document.createElement('div')).addClass('scrollbar-width-test');
		var inner = $(document.createElement('div')).appendTo(outer);
		outer.appendTo('body');
		var w1 = inner[0].offsetWidth;
		// outer.css('overflow', 'scroll');
		var w2 = outer[0].offsetWidth;
		// if(w1 == w2)
			// w2 = outer[0].clientWidth;
		// outer.remove();
		// console.debug(w1);
		// console.debug(w2);
		return w2 - w1;
	},

    _autoSizeColumns:function(self){
        $('.grid-view td').css('width',(100/($('.grid-view th').length)+"%"));
        $('.grid-view th').css('width',(100/($('.grid-view th').length)+"%"));
		this._compareGridviewSettings(self);
        this._setupGridviewResizing(self);
		this._setupGridviewDragging(self);
    }
});
})(jQuery);
