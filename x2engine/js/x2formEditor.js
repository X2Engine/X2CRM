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




(function () {

x2 = typeof x2 === 'undefined' ? {} : x2; 
x2.formEditor = {};

function percentWidth (percent) {

	if (typeof percent === 'undefined') {
		var width = x2.formEditor.getWidthOfHiddenCell ($(this));
		percent = width / $(this).closest ('.formSection').width() * 100 * 100;
		percent = Math.round(percent) / 100;

		return percent;
	}

	$(this).width (percent + '%');

	return this;
}

var formEditorVersion = '5.2';

window.selectedFormItems = $([]);
window.layoutChanged = false;


function toggleFormSection(section) {
	if($(section).hasClass('showSection'))
		$(section).find('.tableWrapper').slideToggle(400,function(){
			$(this).parent('.formSection').toggleClass('showSection');
		});
	else
		$(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
}

$(function() {
	///////////////// Form Section Controls /////////////////
	$('#preview').tabs();

	// setup form editor controls
	$('#addRow').click(function() {
		addFormSection();
	});
	$('#addCollapsibleRow').click(function() {
		addFormSection('collapsible');
	});

	$('#setTabOrder').click(function() {
		$(this).toggleClass('clicked');
		$('#formEditor').toggleClass('tabOrderMode');
		$('#formEditor .formTabOrder').each(function(i,item) {
			$(item).html((i+1));

		});
	});

	// form subsection delete
	$(document).delegate('.formSectionDelete','click',function() {
		deleteFormSection($(this))
	});

	// enter preview mode
	$('#borderToggleButton').click(function() {

		if ($('#formEditor').is(':hidden')) {
			$('#formEditor').show();
			$('#preview').hide();
			$('#borderToggleButton').removeClass('clicked');
			return;
		}

		$('#borderToggleButton').addClass('clicked');

		$.ajax({
			url: yii.scriptUrl + '/site/layoutPreview',
			type: 'POST',
			data: {
				modelName: $('#modelList').val(),
				layout: generateFormJson()
			},
			success: function(data){
				$('#preview > div').html('');
				$(data).first().appendTo('#preview-form');
				$(data).last().appendTo('#preview-view');
				$('#preview').show();
				$('#formEditor').hide();
				$('#save-button').click (function (event) {
					event.preventDefault ();
				});
			}
		});
		// deselectAll();

		// $('#formEditor').toggleClass('editMode');
		// if($('#borderToggleButton').hasClass('clicked'))
		// 	$('.formSortable').sortable('option','disabled',true);
		// else
		// 	$('.formSortable').sortable('option','disabled',false);
	});

	$('#formEditorForm').submit(function() {

			// console.debug(generateFormJson());
		if($('#modelList').val() != '' && $('#modelList').val() != '')
			$('#layoutHiddenField').val(generateFormJson());
		else
			return false;
	});

	// form subsection toggle
	$('div.x2-layout').delegate('.formSectionShow, .formSectionHide','click',function() {
		toggleFormSection($(this).closest('.formSection'));
		x2.forms.saveFormSections();
	});

	// form section add column
	$(document).delegate('.formSectionAddCol','click',function() {
		addColumn($(this).closest('.formSection'));
	});
	// form section delete column
	$(document).delegate('.formSectionDelCol','click',function() {
		deleteColumn($(this).closest('.formSection'));
	});
	// form section delete column
	$(document).delegate('.formSectionSetName','click',function() {
		setSectionName($(this).closest('.formSection'));
	});



	///////////////// Form Item Selection/Deselection /////////////////


	// formItem selection
	$(document).delegate('#formEditor .formItem','click',function(e) {
		if(!$('#borderToggleButton').hasClass('clicked')) {
			if(!e.shiftKey) {
				deselectAll();
			}
			if(window.selectedFormItems.is(this)) {	// if the item is already selected
				$(this).removeClass('selected');
				window.selectedFormItems = window.selectedFormItems.not(this);
				if(window.selectedFormItems.length == 0)
					$('.formItemOptions').stop().fadeOut(400);
			} else {
				$(this).addClass('selected');
				window.selectedFormItems = window.selectedFormItems.add(this);
				updateFormItemOptions();

				$('.formItemOptions').stop().fadeIn(400);
			}
		}
	});
	// deselect formItems when user clicks on white space
	$(document).click(function(e) {
		var elements = $(e.target).add($(e.target).parents());

		if(!e.shiftKey && !elements.is('#formEditor .formItem, #formEditorControls')) {
			deselectAll();
		}
	});

	///////////////// Form Item Manipulation /////////////////

	// listen for delete keys ... if a formItem is selected, delete that junk
	$(document).keydown(function(e) {
		if(e.which==46 && $('input:focus, textarea:focus').length == 0) {	// if we're in a text box, nevermind
			window.selectedFormItems.removeClass('selected').appendTo('#editorFieldList');
			resetFormItem(window.selectedFormItems);
			sortFieldList();
			deselectAll();
		}
	});


	// listen for changes in labelType option
	$('#labelType').change(function(e) {
		setLabelType(window.selectedFormItems,$(this).val());
	});

	// listen for changes in readOnly option
	$('#readOnly').change(function(e) {
		setReadOnly(window.selectedFormItems,$(this).val());
	});



	///////////////// jQuery Sortables Setup /////////////////

	// main form sortable has rows that don't connect to any other sortables
	$('#formEditor').sortable({
		tolerance:'intersect',
		items:'.formSection',
		placeholder:'formSectionPlaceholder',
		handle:'.formSectionHeader',
		opacity:0.5,
		axis:'y',
		distance:10,
		change:function() { window.layoutChanged = true; }
	});
	// $('#formEditor .formItem').disableSelection();


	// list of available fields on the side
	$('#editorFieldList').sortable({
		connectWith: '.formSortable',
		tolerance: 'pointer',
		placeholder:'formItemPlaceholder',
		remove: function(event, ui) {		// make items resizable when removed from main list
				$(ui.item).closest('.formItem').data({'labelType':'left','readOnly':0}).find('.formInputBox').closest('.formInput').addClass('leftLabel');
				window.layoutChanged = true;
			},
		receive: function(event, ui) {
				resetFormItem($(ui.item));	// clear formItem's settings
				sortFieldList();			// sort field list
				window.layoutChanged = true;
			},
		update: function(event,ui) { sortFieldList(); }
	});

	// setup field text toggling for any formItem that might get changed to inlineLabel
	$('div.x2-layout').delegate('div.x2-layout .inlineLabel input:text, div.x2-layout .inlineLabel textarea','focus',function() { x2.forms.formFieldFocus(this); });
	$('div.x2-layout').delegate('div.x2-layout .inlineLabel input:text, div.x2-layout .inlineLabel textarea','blur',function() { x2.forms.formFieldBlur(this); });


});

// creates a new formSelection in the formEditor sortable
function addFormSection(type,columns,title,formSection) {
    formSection = typeof formSection === 'undefined' ? {} : formSection; 

	if(typeof type == 'undefined')
		type = 'default';

	if(typeof columns != 'number')
		columns = 1;

	if(typeof title == 'undefined')
		title = '';

    var showSection = (type !== 'collapsible' ||
        typeof formSection.collapsedByDefault === 'undefined' || !formSection.collapsedByDefault) ?
        'showSection ' : '';

	// create formSection div and formSectionHeader, with editing links
	var html = '<div class="formSectionHeader">';
	html += '<a href="javascript:void(0)" class="formSectionDelCol">&ndash;Col</a>';
	html += '<a href="javascript:void(0)" class="formSectionAddCol">+Col</a>';
	html += '<a href="javascript:void(0)" class="formSectionSetName">Rename</a>';
	html += '<a href="javascript:void(0)" class="formSectionDelete">' + 
		'<i class="fa fa-times"></i></a>';
	// add toggle link if this is collapsible
	if(type == 'collapsible')
		html += '<a href="javascript:void(0)" class="formSectionShow">[+]</a><a href="javascript:void(0)" class="formSectionHide">[&ndash;]</a>';
	html += '<span class="sectionTitle">'+title+'</span>';
	html += '</div><div class="tableWrapper" style="' + 
        (showSection ? '' : 'display: none;') + '"><table><tr class="formSectionRow">';
	// add however many columns
	for(a=0; a<columns; a++) {
		html += '<td><div class=\"formSortable\"></div></td>';
	}
	html += '</tr></table></div></div>';
	// $('#formEditor').find('.formSortable').css('border','1px solid red');

	$(document.createElement('div'))
		.addClass('formSection ' + showSection +((type == 'collapsible')? ' collapsible':''))
		.appendTo('#formEditor').html(html)
		.find('.formSortable')
		.sortable({
			connectWith:'.formSortable',
			items:'.formItem',
			tolerance:'pointer',
			placeholder:'formItemPlaceholder'
		});

	var $formContent = $('#formEditor').find('.formSection').last().find('table');
	setupColResizing($formContent);

	window.layoutChanged = true;
}

// deletes a form section, finding all formItems and returning them to the field list
function deleteFormSection($formSection) {
	var formItems = $formSection.closest('.formSection').find('.formItem');
	resetFormItem(formItems);
	window.selectedFormItems = window.selectedFormItems.not(formItems);
	formItems.removeClass('selected').appendTo('#editorFieldList');
	sortFieldList();
	$formSection.closest('.formSection').fadeOut(300,function() { $(this).remove(); });

	window.layoutChanged = true;
}

function setSectionName($formSection) {
	var newName = prompt('Please enter a name for this section.');
	if(newName != null)
		$formSection.find('.sectionTitle').html(newName);
}


// adds a new column to the current form section
function addColumn($formSection) {
	var $formContent = $formSection.find('table');

	// loop through every row of the table, transferring formItems left
	$formContent.find('tr.formSectionRow').each(function(i,row) {

		var columns = $(row).find('td');

		$('<td><div class=\"formSortable\"></div></td>').appendTo($(row)).find('.formSortable').sortable({
				connectWith:'.formSortable',
				tolerance:'pointer',
				items:'.formItem',
				placeholder:'formItemPlaceholder'
			});

		if(i==0) { // first row only: calculate new widths

			// calculate old average column width, then calculate average with the new column
			var targetWidth = $(row).width();

			var averageWidth = targetWidth / columns.length;
			$(row).find('td:last').width(averageWidth);

			var widthFactor = targetWidth / $(row).width();

			// scale column to have the same total width
			var sum = 0, scaledSum = 0, newWidth = 0;
			$(row).find('td').each(function(i,cell) {
				sum += $(cell).width();
				newWidth = Math.round((sum*widthFactor)-scaledSum);
				$(cell).width(newWidth-1);
				scaledSum += newWidth;
			});
		}
	});

	setupColResizing($formContent);

	window.layoutChanged = true;
}

// deletes the last column, moving its contents to the previous column's formSortable div
function deleteColumn($formSection) {
	var $formContent = $formSection.find('table');
	// loop through every row of the table, transferring formItems left
	$formContent.find('tr.formSectionRow').each(function(i,row) {


		var columns = $(row).find('td');
		if(columns.length < 2)
			return;

		if(i==0) { // first row only: calculate new widths

			// calculate old average column width, then calculate average with the new column
			var targetWidth = $(row).width();
			var widthFactor = targetWidth / (targetWidth - columns.last().width());
			// scale column to have the same total width
			var sum = 0, scaledSum = 0, newWidth = 0;
			$(row).find('td:not(:last)').each(function(i,cell) {
				sum += $(cell).width();
				newWidth = Math.round((sum*widthFactor)-scaledSum);
				$(cell).width(newWidth)+1;
				scaledSum += newWidth;
			});
		}

		var lastCell = $(columns[columns.length-1]);
		var secondToLast = $(columns[columns.length-2]);

		lastCell.find('.formItem').appendTo(secondToLast.find('.formSortable')); // transfer form items to the left
		lastCell.remove();
	});
	setupColResizing($formContent);

	window.layoutChanged = true;
}

// removes and recreates resize handles for the formSection table columns
function setupColResizing($table) {
	// var count = $table.find ('td').length;
	// $table.find ('td').each (function(){
	// 	$(this).percentWidth (100.0/count);
	// });

	// if($table.data('x2-gridResizing') !== undefined) {
		// $table.gridResizing("destroy");
	// }
	// $table.gridResizing({
		// onResize:function(){ window.layoutChanged = true; },
	// });
	$table.colResizable({disable:true})	// remove old colResizable class, if it exists
		.colResizable({
			liveDrag:true,
			draggingClass:'colResizableDragging',
			onResize:function() { 
				window.layoutChanged = true; 
			}
		});
}

// formats all selected formItems to the chosen label type
function setLabelType(formItems,type) {

	if(typeof type != 'undefined' && type != '')
		formItems.data('labelType',type);	// store type via jQuery's Data system

	switch(type) {
		case 'left':
			formItems.each(function(i,item) {
				$(item).removeClass('inlineLabel noLabel topLabel').addClass('leftLabel');
				$(item).find('input,textarea').attr('value','').val('').css('color','#000');	// reset default value and clear field
			});
			break;
		case'inline':
			formItems.each(function(i,item) {
				$(item).removeClass('topLabel leftLabel').addClass('inlineLabel');
				var attributeLabel = $(item).find('label').html();
				$(item).find('input,textarea').attr('value',attributeLabel).val(attributeLabel).css('color','#aaa');	// reset default value and clear field
			});
			break;
		case 'none':
			formItems.each(function(i,item) {
				$(item).removeClass('inlineLabel topLabel leftLabel').addClass('noLabel');
				$(item).find('input,textarea').attr('value','').val('').css('color','#000');	// reset default value and clear field
			});
			break;
		case 'top':
		default:
			formItems.each(function(i,item) {
				$(item).removeClass('inlineLabel noLabel leftLabel').addClass('topLabel');
				$(item).find('input,textarea').attr('value','').val('').css('color','#000');	// reset default value and clear field
			});
	}

	window.layoutChanged = true;
}

// sets all selected formItems to be read-only (disabled)
function setReadOnly(formItems,readOnly) {
	if(readOnly === '' || readOnly === 'undefined' || readOnly === undefined)
		readOnly = '0';
	// console.debug(readOnly);

	formItems.data('readOnly',readOnly);	// store readOnly via jQuery's Data system

	if(readOnly == '1')
		formItems.find('input,textarea').attr('disabled','disabled');
	else
		formItems.find('input,textarea').removeAttr('disabled');

	window.layoutChanged = true;
}

// scans all selected elements to see if they share any values, and updates the formItemOptions controls
function updateFormItemOptions() {

	var labelType = '';
	var readOnly = '';
	window.selectedFormItems.each(function(i,item) {
		var thisLabelType = $(item).data('labelType');
		var thisReadOnly = $(item).data('readOnly');

		if(labelType === '')
			labelType = thisLabelType;
		else if(labelType !== thisLabelType)
			labelType = 'mixed';

		if(readOnly === '')
			readOnly = thisReadOnly;
		else if(readOnly !== thisReadOnly)
			readOnly = 'mixed';
	});
	$('#labelType').val(labelType);
	$('#readOnly').val(readOnly);
}

function deselectAll() {
	window.selectedFormItems.removeClass('selected');	// deselect all elements
	window.selectedFormItems = $([]);
	$('.formItemOptions').stop().fadeOut(400);
}


// removes all user settings from an array of formItems (label setup, size, read-only, etc)
function resetFormItem($items) {
	window.selectedFormItems = window.selectedFormItems.not($items);
	$items.removeClass('selected');	// disable resizing, and de-select this item
	$items.removeClass('noLabel topLabel').addClass('leftLabel');
	$items.find('input, textarea').attr('value','').removeAttr('disabled').val('').css('color','#000');
	$items.data('labelType','left');
	$items.data('readOnly',0);
}

// puts field list in alphabetical order
function sortFieldList() {
	var mylist = $('#editorFieldList');
	var listitems = mylist.children('.formItem').get();
	listitems.sort(function(a, b) {
		var labelA = $(a).find('label').html().toLowerCase();
		var labelB = $(b).find('label').html().toLowerCase();
		return labelA.localeCompare(labelB);
	});
	$.each(listitems, function(idx, itm) {
		mylist.append(itm);
	});
}


// loop through form structure and generate a JSON layout string
function generateFormJson() {

	var formJson = {
		version: formEditorVersion,
		sections: []
	};

	// loop through sections, add rows to rows[]
	$('#formEditor .formSection').each(function(i,section) {
		var sectionJson = {
			rows: []
		};

		if($(section).hasClass('collapsible')) {
			sectionJson.collapsible = true;
            sectionJson.collapsedByDefault = !$(section).find ('.tableWrapper').
                closest ('.formSection').hasClass ('showSection');
		} else {
			sectionJson.collapsible = false;
        }

		var title = $(section).find('.sectionTitle').html();
		sectionJson.title = title;


		// loop through rows, add columns to cols[]
		$(section).find('tr').each(function(j,row) {
			var rowJson = {
				cols: []
			};

			// loop through columns, add formItems to [items], also add widths
			$(row).find('td').each(function(k,col) {
				var columnJson = {
					items: []
				};

				columnJson.width = percentWidth.call ($(this)) + '%';

				// loop through formItems and get all individual properties (height, width, options)
				$(col).find('.formItem').each(function(l,item) {
					var itemJson = {}

					itemJson.name = $(item).attr('id');
					itemJson.labelType = $(item).data('labelType');
					itemJson.readOnly = $(item).data('readOnly');
					itemJson.tabindex = $(item).find('input,textarea,checkbox,select').first().attr('tabindex');

					columnJson.items.push(itemJson);
				});

				rowJson.cols.push(columnJson);
			});
	
			sectionJson.rows.push(rowJson);
		});

		formJson.sections.push(sectionJson);
	});
	
	return JSON.stringify(formJson);
}
x2.formEditor.generateFormJson = generateFormJson;

x2.formEditor.tempRenderCell = function (td$, fn) {
    var visible = td$.closest ('.tableWrapper').is(':visible');
    if (!visible) {
        var style = td$.closest ('.tableWrapper').attr ('style');
        td$.closest ('.tableWrapper').css ({
            visibility: 'hidden',
            display: 'block',
        })
    }
    
    var results = fn ();

    if (!visible) {
        td$.closest ('.tableWrapper').attr ('style', style);
    }
    return results;
};

x2.formEditor.setWidthOfHiddenCell = function (td$, width) {
    this.tempRenderCell (td$, function () {
        td$.width (width); 
    });
};

x2.formEditor.getWidthOfHiddenCell = function (td$) {
    return this.tempRenderCell (td$, function () {
        return td$.width (); 
    });
};

// parse a JSON layout string and call appropriate functions to recreate the layout
function loadFormJson(formJson) {

	var form = $.parseJSON(formJson);

	for(var i=0; i<form.sections.length; i++) {

		var formSection = form.sections[i];

		var type = formSection.collapsible? 'collapsible' : '';
		if(formSection.rows.length > 0) {
			addFormSection(type,formSection.rows[0].cols.length,formSection.title, formSection);

			$formSection = $('#formEditor .formSection:last');

			for(j=0; j<formSection.rows[0].cols.length; j++) {

				var $col = $formSection.find('td:nth-child('+(j+1)+')');

                x2.formEditor.setWidthOfHiddenCell ($col, formSection.rows[0].cols[j].width);

				for(k=0; k<formSection.rows[0].cols[j].items.length; k++) {
					// console.log(formSection.rows[0].cols[j].items[k]);
					var properties = formSection.rows[0].cols[j].items[k];
					var formItem = $('#editorFieldList').find('#'+properties.name);
					formItem.appendTo($col.find('.formSortable'))
						.data({'labelType':properties.labelType,'readOnly':properties.readOnly}).find('.formInputBox').find('input,textarea,checkbox,select').attr('tabindex',properties.tabindex);

					setReadOnly(formItem,properties.readOnly);
					setLabelType(formItem,properties.labelType);
				}
			}
			setupColResizing($formSection.find('table'));
		}
	}


	window.layoutChanged = false;	// this is set to true by various functions above, needs to be reset
}

x2.formEditor.loadFormJson = loadFormJson;

}) ();
