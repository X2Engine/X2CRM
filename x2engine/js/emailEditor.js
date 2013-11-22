/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/



/**
 * Creates an instance of CKEditor, replacing the speficied element. Checks if the editor
 * has already been instantiated and destroys it if so. Merges any config options provided
 * with default configuration. Sets up jquery drag and drop of images from the Media
 * widget into the editor.
 *
 * @param String editorId The ID of the textarea to be replaced
 * @param Object editorConfig Optional config object containing options for the editor
 */
function createCKEditor(editorId,editorConfig,callback) {

	if (x2.isAndroid) {
		if (editorConfig) {
			if ('height' in editorConfig) {
				$('#' + editorId).css ('height', editorConfig['height']);
			} else {
				$('#' + editorId).css ('height', '95%');
			}
			if ('width' in editorConfig) {
				$('#' + editorId).css ('width', editorConfig['width']);
			} else {
				$('#' + editorId).css ('width', '99%');
			}
		}
		return null;
	}

    var editor = $('#'+editorId).ckeditor(
        function() {
            $('#cke_'+editorId).droppable({
                accept: '.media',
                activeClass: 'ui-state-active',
                hoverClass: 'ui-state-hover',
                drop: function(event, ui) {

                    var media = $(ui.draggable.context);
                    if(media.hasClass('drive-link')){
                        var mediaUrl = media.attr('href');
                        var text=media.html();
                        var link = new CKEDITOR.dom.element('a');
                        var div = new CKEDITOR.dom.element('div');
                        var icon = new CKEDITOR.dom.element('div');
                        icon.setStyles({
                            'height':'16px',
                            'width':'16px',
                            'float':'left',
                            'background':media.prev().css('background')
                        });
                        div.setStyles({
                            'width':'400px',
                            'border':'1px solid rgb(221, 221, 221)',
                            'background-color':'rgb(245, 245, 245)',
                            'padding':'5px',
                            'margin-bottom':'5px',
                            'height':'18px',
                            'max-height':'18px',
                            'font-weight':'bold',
                            'font':'Arial',
                            'font-size':'13px'
                        });
                        link.setStyles({
                            'text-decoration':'none',
                            'color':'rgb(17, 85, 204)',
                            'cursor':'pointer',
                            'white-space':'nowrap',
                            'overflow':'hidden',
                            'text-overflow':'ellipsis',
                            'max-width':'370px',
                            'margin-left':'5px'
                        });
                        link.setAttribute('href',mediaUrl);
                        link.setHtml(text);
                        var editorId='email-message';
                        //var editor=$('#'+editorId).ckeditorGet();
                        var range = editor.createRange();
                        range.moveToPosition( range.root, CKEDITOR.POSITION_BEFORE_END );
                        editor.getSelection().selectRanges( [ range ] );
                        div.append(icon);
                        div.append(link);
                        editor.insertElement(div);
                    }else if(media.hasClass('image-file')) {

                        var mediaUrl = media.attr('data-url');

                        var img = new CKEDITOR.dom.element('img');
                        img.setAttribute('src',mediaUrl);

                        editor.insertElement(img);
                    }
                }
            });
            if(callback)
                callback();
        },
        $.extend({
            toolbar:'MyEmailToolbar',
            height:300,
            // filebrowserBrowseUrl: '/browser/browse/type/all',
            // filebrowserUploadUrl: '/browser/upload/type/all',
            // filebrowserImageBrowseUrl: '/browser/browse/type/image',
            filebrowserImageUploadUrl: yii.scriptUrl+'/media/ajaxUpload',
            filebrowserWindowWidth: 800,
            filebrowserWindowHeight: 500
        },editorConfig)
        ).ckeditorGet();
    return editor;
}


/**
 *	Set up attachments in the email form so that the attachments div is droppable for
 *  files dragged over from the media widget. This is called when the page loads (if the
 *  page has an inline email form) and whenever the email form is replaced, like after an
 *  ajax call from pressing the preview button.
 */
function setupEmailAttachments(droppableId) {
    $('#'+droppableId).droppable({
        accept:'.media',
        activeClass:'x2-state-active',
        hoverClass:'x2-state-hover',
        drop:function(evt,ui) {

            var media = ui.draggable.context;
            if($(media).hasClass('drive-link')){
                var mediaUrl = $(media).attr('href');
                var text=$(media).html();
                var link = new CKEDITOR.dom.element('a');
                var div = new CKEDITOR.dom.element('div');
                var icon = new CKEDITOR.dom.element('div');
                icon.setStyles({
                    'height':'16px',
                    'width':'16px',
                    'float':'left',
                    'background':$(media).prev().css('background')
                });
                div.setStyles({
                    'width':'400px',
                    'border':'1px solid rgb(221, 221, 221)',
                    'background-color':'rgb(245, 245, 245)',
                    'padding':'5px',
                    'margin-bottom':'5px',
                    'height':'18px',
                    'max-height':'18px',
                    'font-weight':'bold',
                    'font':'Arial',
                    'font-size':'13px'
                });
                link.setStyles({
                    'text-decoration':'none',
                    'color':'rgb(17, 85, 204)',
                    'cursor':'pointer',
                    'white-space':'nowrap',
                    'overflow':'hidden',
                    'text-overflow':'ellipsis',
                    'max-width':'370px',
                    'margin-left':'5px'
                });
                link.setAttribute('href',mediaUrl);
                link.setHtml(text);
                var editorId='email-message';
                var editor=$('#'+editorId).ckeditorGet();
                var range = editor.createRange();
                range.moveToPosition( range.root, CKEDITOR.POSITION_BEFORE_END );
                editor.getSelection().selectRanges( [ range ] );
                div.append(icon);
                div.append(link);
                editor.insertElement(div);
            }else{
                var mediaId = media.href.split('/').pop();
                var mediaName = media.innerHTML;

                var file = $('<input>', {
                    'type': 'hidden',
                    'name': 'AttachmentFiles[id][]',
                    'class': 'AttachmentFiles',
                    'value': mediaId // name of temp file
                });

                var temp = $('<input>', {
                    'type': 'hidden',
                    'name': 'AttachmentFiles[temp][]',
                    'value': false // indicates that this is not a temp file
                });

                var remove = $("<a>", {
                    'href': "#",
                    'html': "[x]"
                });

                var attachment = $('.next-attachment');
                var newFileChooser = attachment.clone();
                attachment.children('.error').html(''); // clear attachment errors (if any)

                attachment.removeClass('next-attachment');

                attachment.append(temp);
                attachment.append(file);
                attachment.find('.filename').html(mediaName);
                attachment.find('.remove').append(remove);
                attachment.find('.upload-wrapper').remove();

                remove.click(function() {
                    attachment.fadeOut(200,function(){
                        $(this).remove();
                    });
                    return false;
                });

                attachment.after(newFileChooser);
                initX2FileInput();
            }
        }
    }).on('click','.remove a',function() {	// remove attachments when user clicks on the X
        $(this).parent().parent().remove();
        return false;
    });
}
