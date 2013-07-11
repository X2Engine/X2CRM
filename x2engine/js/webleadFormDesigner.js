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


function sanitizeInput(value) {
	return encodeURIComponent(value.trim().replace(/[^a-zA-Z0-9#,]/g, ''));
}

function generateQuery(params) {
	var query = '';
	var first = true;

	for (var i=0; i<params.length; i++) {
		if (params[i].search(/^[^=]+=[^=]+$/) != -1) {
			if (first) {
				query += '?'; first = false;
			} else {
				query += '&';
			}

			query += params[i];
		}
	}

	return query;
}

function updateParams() {
    if ($(this).data ('ignoreChange')) {
        return;
    }
	var params = [];
	if (listId != null) {
		params.push('lid='+listId);
	}

	$.each(fields, function(i, field) {
		var value = sanitizeInput($('#'+field).val());
		if (value.length > 0) { params.push(field+'='+value); }
	});

	var query = generateQuery(params);
	var newembed = embedcode.replace(/(src=\"[^\"]*)/, "$1" + query);

	$('#embedcode').val(newembed);
	$('#iframe_example').html(newembed);
}

function clearFields() {
	$('#name').val('');
	$.each(fields, function(i, field) {
		$('#'+field).val('');
	});
}

function updateFields(form) {
	$('#name').val(form.name);
	$.each(form.params, function(key, value) {
		if ($.inArray(key, fields) != -1) {
			$('#'+key).val(value);
		}
		if ($.inArray(key, colorfields) != -1) {
			$('#'+key).spectrum ("set", $('#'+key).val ());
		}
	});
}

function saved(data, status, xhr) {
	var newForm = $.parseJSON(data);
	if (typeof newForm.errors !== "undefined") { return; }
	newForm.params = $.parseJSON(newForm.params);
	var index = -1;
	$.each(savedforms, function(i, el) {
		if (newForm.id == el.id) {
			index = i;
		}
	});
	if (index != -1) {
		savedforms.splice(index, 1, newForm);
	} else {
		savedforms.push(newForm);
		$('#saved-forms').append('<option value="'+newForm.id+'">'+newForm.name+'</option>');
	}
	$('#saved-forms').val(newForm.id);
	alert(x2.formSavedMsg);
}

$(function() {
	$('#embedcode').focus(function() {
		$(this).select();
	});
	$('#embedcode').mouseup(function(e) {
		e.preventDefault();
	});
	$('#embedcode').focus();

	$.each(colorfields, function(i, field) {
		var selector = '#' + field;
        setupSpectrum ($(selector));
        $(selector).on ('change', updateParams);
	});

	$.each(fields, function(i, field) {
		$('#'+field).on('change', updateParams);
	});

	$('#save').click(function(e) {
		if ($.trim($('#name').val()).length == 0) {
			$('#name').addClass('error');
			$('[for="name"]').addClass('error');
			$('#save').after('<div class="errorMessage">'+x2.nameRequiredMsg+'</div>');
			e.preventDefault(); //has no effect
		}
	});

	$('#saved-forms').on('change', function() {
		var id = $(this).val();
		clearFields();
		if (id != 0) {
			var match = $.grep(savedforms, function(el, i) {
				return id == el.id;
			});
			updateFields(match[0]);
		}
		updateParams();
		$('#embedcode').focus();
    	$.each(colorfields, function(i, field) {
            if ($('#'+field).val () === '') {
                addCheckerImage ($('#'+field));
            } else {
                removeCheckerImage ($('#'+field));
            }
        });
	});

	if (listId != null) { updateParams(); }
});
