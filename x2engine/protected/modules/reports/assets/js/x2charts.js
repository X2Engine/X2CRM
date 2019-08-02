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







// keep track of the number of charts
// so each one gets a different id
// eg. "x2chart_1" "x2chart_2" ...
var x2chartnum = 1;

var x2report_data;
var x2report_column_data;
var row_names;

// keep track of which charts are showing, then pass them as GET values when saving charts
var ChartsGetData = new Array();
var ChartsGetDataType = new Array();

$(function() {
	$('a.x2-pie-chart-button').qtip({content: "Generate a Pie Chart"});
	$('a.x2-bar-graph-button').qtip({content: "Generate a Bar Graph"});
	// $('#send-email-button').click(function() {
		// teditor.post();
	// });
	// setupEmailEditor();
});

//**
// rowPieChart
// 
// generate a new pie chart
// 
// title - Title to display for this chart
// row - row number (from report_data)
function rowPieChart(row) {

	ChartsGetData.push(row);
	ChartsGetData.push('rowpie');

	var x2chart_id = 'x2chart_' + x2chartnum;
	x2chartnum++;
	
	var remove = $('<a>', {
		'href': '#',
		'html': '[x]',
	});
	
	var x2chart_wrapper = $('<div>', {
		'id': x2chart_id,
		'style': 'width: auto; height: 350px; opacity: 0.0; display: none;',
	});
	
	var x2chart_outer_wrapper = $('<div>');
	$('#x2-report-charts').prepend(x2chart_outer_wrapper);
	x2chart_outer_wrapper.append(remove);
	x2chart_outer_wrapper.append(x2chart_wrapper);
	remove.click(function(e) {
		$(this).parent().animate({opacity: 0.0}, function() {
			$(this).slideUp('fast', function() {
				$(this).remove();
				var i = $.inArray (row, ChartsGetData);
				ChartsGetData[i] = -1;
			});
		});
		return false;
	});
	
		x2chart_wrapper.slideDown('fast', function() {
			
		jQuery.jqplot (x2chart_id, [report_data[row]], {
			seriesDefaults: {
				// Make this a pie chart.
				renderer: jQuery.jqplot.PieRenderer, 
				rendererOptions: {
					// Put data labels on the pie slices.
					// By default, labels show the percentage of the slice.
					'fill':true,
					'showDataLabels':true,
					'sliceMargin':4,
					'lineWidth':5,
					'textColor': '#000000'
				}
			},
            seriesColors: ['#1D4C8C', '#45B41D', '#CEC415', '#CA8613', '#BC0D2C', '#5A1992', '#156A86', '#69B10A', '#C6B019', '#C87010', '#AB074F', '#3D1783'],
			'grid': {
			    background: '#FFFFFF',
			    borderColor: '#000000',
			    borderWidth: 1.0,
			},
			legend: { show:true, location: 'ne', placement: 'insideGrid', textColor: 'black'},
			title: {
				text: row_names[row],
				textColor: '#000000'
				
			},
		});
		
		x2chart_wrapper.animate({opacity: 1.0});
		});
}

//**
// columnPieChart
// 
// generate a new pie chart
// 
// title - Title to display for this chart
// column - column number (from report_data)
function columnPieChart(row) {
	
	ChartsGetData.push(row);
	ChartsGetData.push('columnpie');
	
	var x2chart_id = 'x2chart_' + x2chartnum;
	x2chartnum++;
	
	var remove = $('<a>', {
		'href': '#',
		'html': '[x]',
	});
	
	var x2chart_wrapper = $('<div>', {
		'id': x2chart_id,
		'style': 'width: auto; height: 350px; opacity: 0.0; display: none;',
	});
	
	var column_data = new Array();
	for(var i=0; i<report_data.length-1; i++) {
		column_data[i] = [row_names[i], report_column_data[i][row][1]];
	}
	
	var x2chart_outer_wrapper = $('<div>');
	$('#x2-report-charts').prepend(x2chart_outer_wrapper);
	x2chart_outer_wrapper.append(remove);
	x2chart_outer_wrapper.append(x2chart_wrapper);
	remove.click(function(e) {
		$(this).parent().animate({opacity: 0.0}, function() {
			$(this).slideUp('fast', function() {
				$(this).remove();
				var i = $.inArray (row, ChartsGetData);
				ChartsGetData[i] = -1;
			});
		});
		return false;
	});
	
		x2chart_wrapper.slideDown('fast', function() {
			
			myplot = jQuery.jqplot (x2chart_id, [column_data], {
				seriesDefaults: {
					// Make this a pie chart.
					renderer: jQuery.jqplot.PieRenderer, 
					rendererOptions: {
						// Put data labels on the pie slices.
						// By default, labels show the percentage of the slice.
						'fill':true,
						'showDataLabels':true,
						'sliceMargin':4,
						'lineWidth':5
					}
				},
            	seriesColors: ['#1D4C8C', '#45B41D', '#CEC415', '#CA8613', '#BC0D2C', '#5A1992', '#156A86', '#69B10A', '#C6B019', '#C87010', '#AB074F', '#3D1783'],
				'grid': {
				    background: '#FFFFFF',
				    borderColor: '#000000',
				    borderWidth: 1.0,
				},
				legend: { show:true, location: 'ne', placement: 'insideGrid'  },
				title: {
					text: column_names[row],
					textColor: '#000000'
				
				},
			});
			/*
			x2theme = {
				seriesStyles: {
            		seriesColors: ['#417DCD', '#6AEA3B', '#FFF440', '#FFB740', '#EF3C5C', '#7434AC', '#2C83A0', '#9DE93A', '#FFE63F', '#FFA33F', '#D8357D', '#6137AE'],
            	}
            };
            			
			myplot.themeEngine.newTheme('x2', x2theme);
    		myplot.activateTheme('x2');
    		*/
			
			x2chart_wrapper.animate({opacity: 1.0});
		});
		
}

//**
// rowBarGraph
// 
// generate a new bar graph
// 
// title - Title to display for this graph
// row - row number (from report_data)
function rowBarGraph(row) {

	ChartsGetData.push(row);
	ChartsGetData.push('rowbar');

	var x2chart_id = 'x2chart_' + x2chartnum;
	x2chartnum++;
	
	var remove = $('<a>', {
		'href': '#',
		'html': '[x]',
	});
	
	var x2chart_wrapper = $('<div>', {
		'id': x2chart_id,
		'style': 'width: auto; height: 350px; opacity: 0.0; display: none;',
	});
	
	var row_data = new Array();
	var rowNames = new Array();
	for(var i=0;i<report_data[row].length; i++) {
		rowNames[i] = report_data[row][i][0];
		row_data[i] = report_data[row][i][1];
	}
		
	var x2chart_outer_wrapper = $('<div>');
	$('#x2-report-charts').prepend(x2chart_outer_wrapper);
	x2chart_outer_wrapper.append(remove);
	x2chart_outer_wrapper.append(x2chart_wrapper);
	remove.click(function(e) {
		$(this).parent().animate({opacity: 0.0}, function() {
			$(this).slideUp('fast', function() {
				$(this).remove();
				var i = $.inArray (row, ChartsGetData);
				ChartsGetData[i] = -1;
			});
		});
		return false;
	});
	
		x2chart_wrapper.slideDown('fast', function() {
			$.jqplot(x2chart_id, [row_data], {
            // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            animate: !$.jqplot.use_excanvas,
    'axesDefaults':{
        'tickRenderer':$.jqplot.CanvasAxisTickRenderer,
        'tickOptions':{'angle':-45}},
        seriesColors:['#1D4C8C'],
        'seriesDefaults':{
            'renderer':$.jqplot.BarRenderer,
            'rendererOptions':{
                'barMargin':30,
                varyBarColor: true
            },
            'pointLabels':{
                'show':true,
                'location':'s',
                'hideZeros':true
            }
       },
			'grid': {
				gridLineWidth: 0.1,
				background: '#FFFFFF',
				gridLineColor: '#000000',
				borderColor: '#000000',
				borderWidth: 1.0,
			},
            'axes': {
                'xaxis': {
                    'renderer': $.jqplot.CategoryAxisRenderer,
                    'tickRenderer': $.jqplot.CanvasAxisTickRenderer,
                    'tickOptions': {
                    	textColor: '#000000',
                    },
                    'ticks': rowNames
               }
            },
            highlighter: { show: false },
			title: {
				text: row_names[row],
				textColor: '#000000'
			},
        });
		
		x2chart_wrapper.animate({opacity: 1.0});
		});
}


//**
// columnBarGraph
// 
// generate a new bar graph
// 
// title - Title to display for this graph
// column - column number (from report_data)
function columnBarGraph(column) {

	ChartsGetData.push(column);
	ChartsGetData.push('columnbar');

	var x2chart_id = 'x2chart_' + x2chartnum;
	x2chartnum++;
	
	var remove = $('<a>', {
		'href': '#',
		'html': '[x]',
	});
	
	var x2chart_wrapper = $('<div>', {
		'id': x2chart_id,
		'style': 'width: auto; height: 350px; opacity: 0.0; display: none;',
	});
	
	var columnNames = new Array();
	var column_data = new Array();
	for(var i=0; i<report_data.length-1; i++) {
		columnNames[i] = row_names[i];
		column_data[i] = report_column_data[i][column][1];
	}
	
	var x2chart_outer_wrapper = $('<div>');
	$('#x2-report-charts').prepend(x2chart_outer_wrapper);
	x2chart_outer_wrapper.append(remove);
	x2chart_outer_wrapper.append(x2chart_wrapper);
	remove.click(function(e) {
		$(this).parent().animate({opacity: 0.0}, function() {
			$(this).slideUp('fast', function() {
				$(this).remove();
				var i = $.inArray (column, ChartsGetData);
				ChartsGetData[i] = -1;
			});
		});
		return false;
	});
	
		x2chart_wrapper.slideDown('fast', function() {
			$.jqplot(x2chart_id, [column_data], {
            // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            animate: !$.jqplot.use_excanvas,
		'axesDefaults':{
			'tickRenderer':$.jqplot.CanvasAxisTickRenderer,
			'tickOptions':{'angle':-45}
		},
        seriesColors:['#1D4C8C'],
        'seriesDefaults':{
            'renderer':$.jqplot.BarRenderer,
            'rendererOptions':{
                'barMargin':30,
                varyBarColor: true
            },
            'pointLabels':{
                'show':true,
                'location':'s',
                'hideZeros':true
            }
       },
			'grid': {
				gridLineWidth: 0.1,
				background: '#FFFFFF',
				gridLineColor: '#000000',
				borderColor: '#000000',
				borderWidth: 1.0,
			},
            'axes': {
                'xaxis': {
                    'renderer': $.jqplot.CategoryAxisRenderer,
                    'tickRenderer': $.jqplot.CanvasAxisTickRenderer,
                    'tickOptions': {
                    	textColor: '#000000',
                    },
                    'ticks': columnNames
               }
            },
            highlighter: { show: false },
			title: {
				text: column_names[column],
				textColor: '#000000'
				
			},
        });
		
		x2chart_wrapper.animate({opacity: 1.0});
		});
}

function beforeSaveReport() {
	var GetString = "";
	for(var i=0; i<ChartsGetData.length; i+=2) {
		if(ChartsGetData[i] != -1) {
			GetString += '&chartValue[]=' + ChartsGetData[i];
			GetString += '&chartType[]=' + ChartsGetData[i+1];
		}
	}
	
	$('#save-report-button').attr('href', $('#save-report-button').attr('href') + GetString);
	
	return true;
}

function printReport() {
	var form = $('<form>', {
		'action': $('body').data('printReportUrl'),
		'method': 'POST',
	});
	$('#lead-activity-grid').find('a').each(function() {
		$(this).remove();
	});
	$('#lead-activity-grid').find('div.keys').remove();
	
	var table = $('<input>', {
		'type': 'hidden',
		'name': 'ReportTable',
		'value': $('#lead-activity-grid').html(),
	});
	$('body').append(form);
	$('#x2-report-charts').children().each(function() {
		var image = $(this).children('div').jqplotToImageElem();
		var imageData = $('<input>', {
			'type': 'hidden',
			'name': 'ChartImage[]',
			'value': image.src,
		});
		form.append(imageData);
	});
	form.append(table)
	form.submit();
}

function emailReport() {
	toggleEmailForm();
}

// gets called after the inline email editor has been setup
// we need to ensure the editor is setup so we can copy the report table into the editors iframe
function inlineEmailEditorCallback() {

	var emailBody = $('#inline-email-form').find('iframe').contents().find('body');
	emailBody.html($('#lead-activity-grid').html());
	var table = emailBody.find('table');
//	teditor.e.body.innerHTML = $('#lead-activity-grid').html();
	emailBody.find('div.keys').remove();
	
	table.find('a').each(function() {
		$(this).remove();
	});
	
	table.addClass('print-report');
	
	table.css('border-collapse', 'collapse');
	table.css('width', '100%');
	
	table.find('th').each(function() {
		$(this).css('border', '1px solid black');
		$(this).css('background-color', '#eee');
	});
	
	table.find('td').each(function() {
		$(this).css('border', '1px solid black');
	});

	$('#x2-report-charts').children().each(function() {
		var image = $(this).children('div').jqplotToImageElem();
		var title = $(this).find('div.jqplot-title').html();
		$.post($('body').data('saveTempImageUrl'), {ImageBase64: image.src, ImageName: title}, function(response) {
			response = $.parseJSON(response);
			if(response['status'] == 'success') {
            	// success uploading temp file
            	// save it's name in the form so it gets attached when the user clicks send
            	var file = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[id][]',
            		'class': 'AttachmentFiles',
            		'value': response['id'], // name of temp file
            	});
            	
            	var temp = $('<input>', {
            		'type': 'hidden',
            		'name': 'AttachmentFiles[types][]',
            		'value': 'temp',
            	});
            	
            	var wrapper = $('<div>');
            	
            	var attachment = $('#inline-email-form').find('.next-attachment');
            	var newFileChooser = attachment.clone();
            	
            	attachment.removeClass('next-attachment');
            	attachment.append(file);
            	attachment.append(temp);
            	
            	var remove = $("<a>", {
            		'href': "#",
            		'html': "[x]",
            	});
            	
            	attachment.children('.filename').html(response['name']);
            	attachment.children('.remove').append(remove);
            	
            	remove.click(function() {removeAttachmentFile(remove.parent().parent(), $('body').data('removeTempFileUrl')); return false;});
            	
            	attachment.children('.upload-wrapper').remove();
            	attachment.after(newFileChooser);
            	initX2FileInput();
			}
		});
	});
		
	return false;
}




