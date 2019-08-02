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







function setUpDialogs (saveChanges, translations) {
	$('.editor-dialog').dialog ({
		title: translations['editDialogTitle'],
		dialogClass: 'gallery-widget-dialog',
		autoOpen: false,
		height: "auto",
		width: "auto",
		resizable: false,
		draggable: false,
		show: 'fade',
		modal: true,
		hide: 'fade',
		position: {
			my: 'center',
			at: 'center',
			of: window
		},
		open: function () {
			var $dialog = $('.editor-dialog');
			var $img = $dialog.find ('img');
			var imageNum = $dialog.find ('img').length;
			$dialog.dialog ('option', 'width', 600)
			$dialog.dialog ('option', 'height', 500)
			$img.on ('load', function () {
				var width = $(this).width ();
				var height = $(this).height ();
				if (width > 600 || height > 250) {
					if (height > 250 && width > 600 ) {
						if (height > width) {	
							$(this).width ((250 / height) * width);
							$(this).height (250);
						} else {
							$(this).height ((600 / width) * height);
							$(this).width (600);
						}
					} else if (height > 250) {
						$(this).width ((250 / height) * width);
						$(this).height (250);
					} else if (width > 600) {
						$(this).height ((600 / width) * height);
						$(this).width (600);
					}
				}
				$dialog.dialog ('option', 'position', 'center');
			});

			$('.ui-widget-overlay').one ('click', function () {
				$dialog.dialog ('close');
			})
		},
		buttons: [
			{ 
				text: translations['editDialogSaveButtonLabel'],
				click: saveChanges,
				'class': 'editor-dialog-save-button'
			},
			{ 
				text: translations['editDialogCloseButtonLabel'],
				click: function () {
					$('.editor-dialog').dialog ('close');
				},
				'class': 'editor-dialog-close-button'
			}
		]
	});
	$('.preview-dialog').dialog ({
		title: translations['viewDialogTitle'],
		dialogClass: 'gallery-widget-dialog',
		autoOpen: false,
		height: "auto",
		width: 'auto',
		resizable: false,
		draggable: false,
		show: 'fade',
		modal: true,
		hide: 'fade',
		position: {
			my: 'center',
			at: 'center',
			of: window
		},
		open: function () {
			var $dialog = $('.preview-dialog');
			var $img = $dialog.find ('img');
			$dialog.dialog ('option', 'width', 600)
			$dialog.dialog ('option', 'height', 500)
			$img.on ('load', function () {
				var width = $(this).width ();
				var height = $(this).height ();
				if (width > 600 || height > 500) {
					if (height > 400 && width > 600 ) {
						if (height > width) {	
							$(this).width ((400 / height) * width);
							$(this).height (400);
						} else {
							$(this).height ((600 / width) * height);
							$(this).width (600);
						}
					} else if (height > 400) {
						$(this).width ((400 / height) * width);
						$(this).height (400);
					} else if (width > 600) {
						$(this).height ((600 / width) * height);
						$(this).width (600);
					}
				}
				$dialog.dialog ('option', 'position', 'center');
			});
			$('.ui-widget-overlay').one ('click', function () {
				$dialog.dialog ('close');
			})
		},
		buttons: [
			{ 
				text: translations['viewDialogCloseButtonLabel'],
				click: function () {
					$('.preview-dialog').dialog ('close');
				},
				'class': 'preview-dialog-close-button'
			},
		]
	});

	// re-center dialog
	$(window).on ('resize', function () {
		if ($('.preview-dialog').is (':visible')) {
			$('.preview-dialog').dialog ('option', 'position', 'center');
		} else if ($('.editor-dialog').is (':visible')) {
			$('.editor-dialog').dialog ('option', 'position', 'center');
		}
	});

}



