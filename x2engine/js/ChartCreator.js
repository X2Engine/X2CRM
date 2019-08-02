/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






x2.ChartCreator = (function() {

	/**
	 * Jquery extension to select a column from a cell
	 * @param  {selector} parent parent container selector
	 */
	$.fn.column = function(parent) {
		if (typeof parent === 'undefined')
			parent = $(this).closest('table');

		var col = $(this).parent().children().index($(this))+1;
		var rows = $(parent).find('table tr');
		var nth = ':nth-child('+col+')';
		return rows.find('td'+nth+', th'+nth);
	}

	function ChartCreator(argsDict){
		var defaultArgs = {
			reportSelector: '#generated-report',
			totalsSelector: '.x2-sibling-grid',
			containerSelector: '#chart-creator',
			report: {},
			translations: {},
			autoOpen: false
		};
		auxlib.applyArgs(this,  defaultArgs, argsDict);

		this.container = $(this.containerSelector);
		this.$report = $( this.reportSelector ).add( this.totalsSelector );

		this.setUpChartTypeSelection();
		this.setUpAxisSelection();
		this.setUpDialog();
		this.setUpEventHandlers();
		this.setUpAutoOpen();
	}

	ChartCreator.prototype.open = function() {
		this.container.closest('.ui-dialog').css({position: 'fixed'}).end().dialog('open');
	}

	ChartCreator.prototype.$column = function(col) {
		return $(col).column(this.$report).not('.drill-down-grid *');
	}

	ChartCreator.prototype.setUpChartTypeSelection = function (element){
		var that = this;
		this.container.find('.chart-selector .choice').click(function() {
		    that.container.find('.choice, form').removeClass('active');
		    
		    var id = $(this).attr('value');
		    
		    that.container.find('form#'+id).addClass('active');
		    $(this).addClass('active');
		}).first().trigger('click');
	}

	ChartCreator.prototype.highlightReport = function(bool) {
        bool = typeof bool === 'undefined' ? true : bool; 
		if (bool)
			$('#report-container').addClass('active');
		else 
			$('#report-container').removeClass('active');
	}

	/**
	 * Initializes axis selector elements' events
	 */
	ChartCreator.prototype.setUpAxisSelection = function() {
		var that = this;
		$('.axis-selector').
			click (function(){
				var axis = $(this).attr('axis');
				that.currentField = $(this).siblings('.axis-selector-hidden');
				that.enterSelection(axis);
			}).
			siblings('.clear-field').click (function(){
			    var textField = $(this).siblings('.axis-selector');
			    textField.
			    	removeClass('confirmed').
			    	removeClass('in-selection')
			    	.val('');

			    $(this).siblings('.axis-selector-hidden').val('');

			    $(this).hide();
			});

	}


	/**
	* Function to set up handlers for different types of selections 
	* such as (column, row, cell, both)
	*/
	ChartCreator.prototype.setUpEventHandlers = function() { 
		var that = this;

		/***************************************
		* Parent class for the axis handlers
		***************************************/
		var Handler = function(axis) {
			this.axis = axis;
			this.selector = axis.selector;

			this.mouseOver = function() {
				axis.groupSelector(this).addClass('hover-selection');
			}

			this.mouseOut = function() {
				axis.groupSelector(this).removeClass('hover-selection');
			};

			this.insertSelection = function() {
				that.getData();

				var value = axis.value(this);
				var label = axis.label(this);

				that.currentField.val(value);
				that.currentField.siblings('.axis-selector').
					val(label).
					addClass('confirmed');
				that.currentField.siblings('.clear-field').show();

				that.confirmCells();
				that.exitSelection();
			};	

			return this;
		}


		/***************************************
		* Child classes for the handlers
		***************************************/
		var column = {
			selector: function () {
				return that.$report.find('td, th');
			},
			groupSelector: function(element) {
				return that.$column(element);
			},
			value: function(element) {
				var index = that.rowIndex(element);
				var header = that.data.headers[index];
				return header;
			},
			label: function(element) {
				var label = that.$column(element).
					first().
					html();
				return label;
			}
		};

		var row = {
			selector: function() {
				return that.$report.find('tr');
			},
			groupSelector: function(element) {
				return $(element);
			},
			value: function(element) {
				if ($(element).closest(that.totalsSelector).length != 0) {
					return '__total';
				}
				var index = that.rowIndex(element);
				var row = that.data.currPageRawData[index];
				return row[0][1];
			},
			label: function(element) {
				var label = $(element).children('td').first().text();
				return label;
			}
		};

		var cell = {
			selector: function() {
				return that.$report.find('td');
			},
			groupSelector: function(element) {
				return $(element);
			},
			value: function(element) {
				var columnField = column.value(element);
				var rowField = row.value ($(element).closest('tr'));
				return JSON.stringify ({
					row: rowField, 
					column: columnField
				});
			},
			label: function(element) {
				var label = $(element).html();

				return label;
			}
		};

		this.handlers = {
			row:    new Handler(row),
			column: new Handler(column),
			cell:   new Handler(cell),
		};

	}

	ChartCreator.prototype.setUpDialog = function() {
		this.container.dialog({
			width: 340,
			height: 500,
			autoOpen: false
		});
	}

	ChartCreator.prototype.closeDialog = function() {
		this.container.dialog('close');
	}

	ChartCreator.prototype.setUpAutoOpen = function() {
		if (!this.autoOpen) return;

		$('#content-container-inner').hide();
		$('.reports-page-title #minimize-button .fa').removeClass('fa-caret-down');
		$('.reports-page-title #minimize-button .fa').addClass('fa-caret-left');
		$('.reports-page-title').addClass('minimized');

		setTimeout(function(){
			$('#report-settings').find('button[type="submit"]').trigger('click');
		});

		this.open();
	}

	ChartCreator.prototype.getData = function() {
		var dataKey;
		if (this.report.type == 'grid' ) {
			dataKey = 'x2-gridReportGridSettings';
		} else if (this.report.type == 'summation' ) {
			dataKey = 'x2-summationReportGvSettings';
		} else if (this.report.type == 'rowsAndColumns' ) {
			dataKey = 'x2-rowsAndColumnsReportGridSettings';
		}

		this.data = $('#generated-report').data(dataKey).options;
	}

	ChartCreator.prototype.isTotalsRow = function (element){
		if ( $(this.totalsSelector).find(element).length !== 0 ) {
			return true;
		}

		return false;
	}

	ChartCreator.prototype.rowIndex = function (row){
		if (this.isTotalsRow (row) && $(row).is ('tr')) {
			return 'total';
		}

		return $(row).
			parent ().
			children ().
			index ($(row));
	}


	ChartCreator.prototype.enterSelection = function(type) {
		/**
		 * If the current field is not set correctly, exit
		 */
		if( typeof this.currentField === 'undefined' || !this.currentField ) {
			return;
		}

		/**
		 * Only one field can be in selection at a time
		 */
		this.exitSelection();

		/**
		 * Query the report, add the totals row
		 */		
		this.$report = $('#generated-report')
			.add( this.totalsSelector );

		 /**
		 * Add in selection to the current visible field
		 */
		this.currentField.siblings('.axis-selector').
			addClass('in-selection').
			removeClass('confirmed').
			attr('placeholder', this.translations['inSelection' + type]).
			val('');

		// this.container.closest('.ui-dialog').css('opacity', 0.3)
		// .css('pointer-events', 'none');

		var handler = this.handlers[type];

		/**
		* Attach the handlers to the selection
		* see setUpHandlers
		*/

		handler.selector()
			.hover( 
				handler.mouseOver, 
				handler.mouseOut)
			.click( 
				handler.insertSelection);

		this.currentHandlers = handler;

		// Supress Links in the report table
		this.$report.find('a').click(function(e){
			e.preventDefault();
		});

		this.highlightReport();
	}

	ChartCreator.prototype.hoverCells = function(){
		return this.$report.find('.hover-selection');
	}

	ChartCreator.prototype.confirmedCells = function(field){
		if (typeof field === 'undefined') {
			return this.$report.find('.confirmed-selection');
		} else {
			return this.$report.find('.confirmed-selection[field="'+field+'"]');
		}
	}

	ChartCreator.prototype.confirmCells = function() {
		var cells = this.hoverCells();
		// cells.addClass('confirmed-selection');
		cells.removeClass('hover-selection');

		cells.attr('field', this.currentField.attr('id'));

	}

	ChartCreator.prototype.clearField = function() {
		var textField = this.currentField.siblings('.axis-selector');

		textField.
			val('').
			removeClass('confirmed').
			removeClass('in-selection').
			siblings('.clear-field').hide().
			siblings('input').val('');
	}

	ChartCreator.prototype.exitSelection = function(index) {
		
		/**
		 * Remove the in selection class from other axis selectors
		 * Remove the tips
		 */
		this.container.find('.axis-selector').
			removeClass('in-selection').
			attr('placeholder', this.translations.exitSelection);
		// this.currentField.siblings('.tip').hide();


		/**
		 * Detach all handlers that were
		 * attached to the cells to not interfere with other handlers
		 */
        var trThTd$ = this.$report.find('tr, th, td');
        trThTd$.
            unbind('click').
            unbind('mouseenter').
            unbind('mouseleave');

        this.$report.find('a').unbind('click');

		this.highlightReport(false);
	}

	return ChartCreator;
})();

