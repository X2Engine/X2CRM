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


$(function() {

	/* var recordTitle = $(".record-title").first();
	var x2layout = $(".x2-layout").first();

	if(recordTitle.length && ($.browser != 'msie' || $.browser.version > 6)) {

		var mainColumn = recordTitle.parent();

		var recordTitleTop = recordTitle.offset().top;
		var recordTitleHeight = recordTitle.height()+15;

		// var pageContainer = $('#page-body'); //.find('.container:first');
		var scrolled = false;

		// sidebarMenu.parent().height(sidebarMenu.height()+20);

		$(window).scroll(function(e) {
				// console.debug($(this).scrollTop());
			if($(this).scrollTop() + 31 >= recordTitleTop) {
				if(!scrolled) {
					mainColumn.css('margin-top',recordTitleHeight+'px');
					recordTitle.addClass('fixed');
					recordTitle.width(x2layout.width()-48);
					scrolled = true;
				}
			} else if(scrolled) {
				recordTitle.removeClass('fixed');
				recordTitle.css('width','');
				mainColumn.css('margin-top','');
				scrolled = false;
			}
			// scrolled = true;
		});

		$(window).resize(function(e) {
			if(scrolled)
				recordTitle.width(x2layout.width()-48);
		});
	} */




	// $('div.x2-layout .formSection:not(.showSection) .tableWrapper').hide();

	// $('div.x2-layout .formItem').disableSelection();

	$('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
		toggleFormSection($(this).closest('.formSection'));
		saveFormSections();
	});

	$('a#showAll, a#hideAll').click(function() {
		$('a#showAll, a#hideAll').toggleClass('hide');
		if($('#showAll').hasClass('hide')) {
			$('div.x2-layout .formSection:not(.showSection)').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		} else {
			$('div.x2-layout .formSection.showSection').each(function() {
				if($(this).find('a.formSectionHide').length > 0)
					toggleFormSection(this);
			});
		}
	});

	$('.inlineLabel').find('input:text, textarea').focus(function() { 
			formFieldFocus(this); 
		}).blur(function() { 
			formFieldBlur(this); 
		});

	// set up x2 helper tooltips
	$('.x2-hint').qtip({
		events: {
			show: function (event, api) {
				var tooltip = api.elements.tooltip;
				var windowWidth = $(window).width ();
				var elemWidth = $(api.elements.target).width ();
				var elemLeft = $(api.elements.target).offset ().left;
				var tooltipWidth = $(api.elements.tooltip).width ();

				if (elemLeft + elemWidth + tooltipWidth > windowWidth) {

					// flip tooltip if it would go off screen
					api.set ({
						'position.my': 'top right',
				    	'position.at': 'bottom right'
					});
				} else {
					api.set ({
						'position.my': 'top left',
				    	'position.at': 'bottom right'
					});
				}
			}
		}

	});
	$('.x2-info').qtip(); // no format qtip (.x2-hint turns text blue)

	/*
	$(window).resize(function() {
		$('#sidebar-right').height($(window).height() - 79);
		$('#sidebar-left-container').height($(window).height() - 79);
		$('#content').height($(window).height() - 79);
	});
	*/
});

function toggleFormSection(section) {
	if($(section).hasClass('showSection'))
		$(section).find('.tableWrapper').slideToggle(400,function(){
			$(this).parent('.formSection').toggleClass('showSection');
			saveFormSections();
		});
	else {
		$(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
		saveFormSections();
	}
}
function saveFormSections() {
	var formSectionStatus = [];
	$('div.x2-layout .formSection').each(function(i,section) {
		formSectionStatus[i] = $(section).hasClass('showSection')? '1' : '0';
	});
	var formSettings = '['+formSectionStatus.join(',')+']';
	$.ajax({
		url: yii.scriptUrl+'/site/saveFormSettings',
		type: 'GET',
		data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
	});
}

function toggleText(field) {
	if(field.defaultValue==field.value) {
		field.value = ''
		field.style.color = 'black'
	} else if(field.value=='') {
		field.value = field.defaultValue
		field.style.color = '#aaa'
	}
}
function formFieldFocus(elem) {
	var field = $(elem);
	if(field.val() == field.attr('title')) {
		field.val('');
		field.css('color','#000');
	}
}
function formFieldBlur(elem) {
	var field = $(elem);
	if(field.val() == '') {
		field.val(field.attr('title'));
		field.css('color','#aaa');
	}
}

// $(function() {
	// placeholderTest = document.createElement('input');
	// if(!('placeholder' in placeholderTest)) {

		// var active = document.activeElement;

		// $.delegate(':text','focus',function() {
			// if($(this).attr('placeholder') != '' && $(this).val() == $(this).attr('placeholder'))
				// $(this).val('').removeClass('placeholder');
		// });

		// $.delegate(':text','blur',function() {
			// if($(this).attr('placeholder') != '' && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder')))
				// $(this).val($(this).attr('placeholder')).addClass('placeholder');
		// });
		// $(':text').blur();
		// $(active).focus();
		// $('form').submit(function () {
			// $(this).find('.placeholder').each(function() { $(this).val(''); });
		// });
	// }
// });

function submitForm(formName) {
	document.forms[formName].submit();
}
function toggleForm(formName,duration) {
	if($(formName).is(':hidden')) {
		$('html,body').animate({
			scrollTop: ($('#action-form').offset().top-200)
		}, 300);
	}
	$(formName).toggle('blind',{},duration);

}
function hide(field) {
	$(field).hide(); //field.style.display="none";
	// button=document.getElementById('save-changes');
	$('#save-changes').addClass('highlight'); //button.style.background='yellow';
}
function show(field) {
	$(field).show();
	// field.style.display="block";
}


function renderContactLookup(item) {
	var label = "<a style=\"line-height: 1;\">" + item.label + "<span style=\"font-size: 0.6em;\">";

	if(item.email) {		// add email if defined
		label += "<br>";
		label += item.email;
	}

	if(item.city || item.state || item.country || item.email) {
		label += "<br>";

		if(item.city)
			label += item.city;
		if(item.state) {
			if(item.city)
				label += ", ";
			label += item.state;
		}
		if(item.country) {
			if(item.city || item.state)
				label += ", ";
			label += item.country;
		}
	}
    if(item.assignedTo){
        label += "<br>" + item.assignedTo;
    }
	label += "</span>";
	label += "</a>";

	return label;
}

function fileUpload(form, fileField, action_url, remove_url) {
	// Create the iframe...
	var iframe = document.createElement("iframe");
	iframe.setAttribute("id", "upload_iframe");
	iframe.setAttribute("name", "upload_iframe");
	iframe.setAttribute("width", "0");
	iframe.setAttribute("height", "0");
	iframe.setAttribute("border", "0");
	iframe.setAttribute("style", "width: 0; height: 0; border: none;");

	// Add to document...
	form.parentNode.appendChild(iframe);
	window.frames['upload_iframe'].name = "upload_iframe";

	iframeId = document.getElementById("upload_iframe");

	// Add event...
	var eventHandler = function () {

			if(iframeId.detachEvent) iframeId.detachEvent("onload", eventHandler);
			else iframeId.removeEventListener("load", eventHandler, false);

			// Message from server...
			if(iframeId.contentDocument) {
				var content = iframeId.contentDocument.body.innerHTML;
			} else if(iframeId.contentWindow) {
				var content = iframeId.contentWindow.document.body.innerHTML;
			} else if(iframeId.document) {
				var content = iframeId.document.body.innerHTML;
			}

			var response = $.parseJSON(content)

			if(response['status'] == 'success') {
				// success uploading temp file
				// save it's name in the form so it gets attached when the user clicks send
				var file = $('<input>', {
					'type': 'hidden',
					'name': 'AttachmentFiles[id][]',
					'class': 'AttachmentFiles',
					'value': response['id'] // name of temp file
				});

				var temp = $('<input>', {
					'type': 'hidden',
					'name': 'AttachmentFiles[temp][]',
					'value': true
				});

				var parent = fileField.parent().parent().parent();

				parent.parent().find('.error').html(''); // clear error messages
				var newFileChooser = parent.clone(); // save copy of file upload span before we start making changes

				parent.removeClass('next-attachment');
				parent.append(file);
				parent.append(temp);

				var remove = $("<a>", {
					'href': "#",
					'html': "[x]"
				});

				parent.find('.filename').html(response['name']);
				parent.find('.remove').append(remove);

				remove.click(function() {removeAttachmentFile(remove.parent().parent(), remove_url); return false;});

				fileField.parent().parent().remove();

				parent.after(newFileChooser);
				initX2FileInput();

			} else {
				fileField.parent().parent().parent().find('.error').html(response['message']);
				fileField.val("");
			}

			// Del the iframe...
			setTimeout('iframeId.parentNode.removeChild(iframeId)', 250);
		}

	if(iframeId.addEventListener)
		iframeId.addEventListener("load", eventHandler, true);
	if(iframeId.attachEvent)
		iframeId.attachEvent("onload", eventHandler);

	// Set properties of form...
	form.setAttribute("target", "upload_iframe");
	form.setAttribute("action", action_url);
	form.setAttribute("method", "post");
	form.setAttribute("enctype", "multipart/form-data");
	form.setAttribute("encoding", "multipart/form-data");

	// Submit the form...
	form.submit();
}

// remove an attachment that is stored on the server as a temp file
function removeAttachmentFile(attachment, remove_url) {
	var id = attachment.find(".AttachmentFiles");
	$.post(remove_url, {'id': id.val()});

	attachment.remove();
}

// set up x2 file input
// call this function everytime an x2 file input is created
function initX2FileInput() {
	// bind hover and click effects
	$('input.x2-file-input[type=file]').hover(function() {
		var button = $('input.x2-file-input[type=file]').next();
		if(button.hasClass('active') == false) {
			$('input.x2-file-input[type=file]').next().addClass('hover');
		}
	}, function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
	});

	$('input.x2-file-input[type=file]').mousedown(function() {
		$('input.x2-file-input[type=file]').next().removeClass('hover');
		$('input.x2-file-input[type=file]').next().addClass('active');
	});

	$('body').mouseup(function() {
		$('input.x2-file-input[type=file]').next().removeClass('active');
	});

	// position the saving icon for uploading files
	// width
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('width'), 10)/2;
	var halfIconWidth = parseInt($('#choose-file-saving-icon').css('width'), 10)/2;
	var iconLeft = chooseFileButtonCenter - halfIconWidth;
	$('#choose-file-saving-icon').css('left', iconLeft + 'px');

	// height
	var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('height'), 10)/2;
	var halfIconHeight = parseInt($('#choose-file-saving-icon').height(), 10)/2;
	var iconTop = chooseFileButtonCenter - halfIconHeight;
	$('#choose-file-saving-icon').css('top', iconTop + 'px');

}


