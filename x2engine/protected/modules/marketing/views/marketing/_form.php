<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/tinyeditor/style.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/tinyeditor/tinyeditor.js');
Yii::app()->clientScript->registerScript('editorSetup',"
editor=new TINY.editor.edit('editor',{
	id:'Campaign_content', // (required) ID of the textarea
	width:580, // (optional) width of the editor
	height:250, // (optional) heightof the editor
	cssclass:'te', // (optional) CSS class of the editor
	controlclass:'tecontrol', // (optional) CSS class of the buttons
	rowclass:'teheader', // (optional) CSS class of the button rows
	dividerclass:'tedivider', // (optional) CSS class of the button diviers
	controls:['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|', 'orderedlist', 'unorderedlist', '|' ,'outdent' ,'indent', '|', 'leftalign', 'centeralign', 'rightalign', 'blockjustify', '|', 'unformat', '|', 'undo', 'redo','n',  'font', 'size', 'style','|', 'image', 'hr', 'link', 'unlink', '|', 'cut', 'copy', 'paste'], // (required) options you want available, a '|' represents a divider and an 'n' represents a new row
	footer:true, // (optional) show the footer
	fonts:['Verdana','Arial','Georgia','Trebuchet MS'],  // (optional) array of fonts to display
	xhtml:false, // (optional) generate XHTML vs HTML
	cssfile:'style.css', // (optional) attach an external CSS file to the editor
	css:'', // (optional) attach CSS to the editor
	bodyid:'editor', // (optional) attach an ID to the editor body
	footerclass:'tefooter', // (optional) CSS class of the footer
	toggle:{text:'source',activetext:'wysiwyg',cssclass:'toggle'}, // (optional) toggle to markup view options
	resize:{cssclass:'resize'} // (optional) display options for the editor resize
});
",CClientScript::POS_END);

Yii::app()->clientScript->registerScript('attachmentSetup',"
$(function() {
	$('#campaign-attachments').droppable({
		accept: '.media',
		activeClass: 'x2-state-active',
		hoverClass: 'x2-state-hover',
		drop: function(event, ui) {

			var media = ui.draggable.context;
			
			var mediaId = media.href.split('/').pop();
			var mediaName = media.innerHTML;

            var file = $('<input>', {
                'type': 'hidden',
                'name': 'AttachmentFiles[id][]',
                'class': 'AttachmentFiles',
                'value': mediaId, // name of temp file
            });		
            
           	var remove = $('<a>', {
           	    'href': '#',
           	    'html': '[x]',
           	});
            
			var attachment = $('.next-attachment');
			var newFileChooser = attachment.clone();
			
			attachment.removeClass('next-attachment');
			
			attachment.append(file);
			attachment.find('.filename').html(mediaName);
			attachment.find('.remove').append(remove);
			
            remove.click(function() {attachment.remove(); return false;});
			
			attachment.after(newFileChooser);
//			initX2FileInput();
		}
	});
	
	$('#campaign-attachments-wrapper').qtip({content: 'Drag files from the Media Widget here.'});
	$('#campaign-attachments span.remove a').click(function() {\$(this).parent().parent().remove(); return false;});
});
",CClientScript::POS_END);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'campaign-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('onsubmit'=>'editor.post();')
));

$this->renderPartial('application.components.views._form', array('model'=>$model,'users'=>User::getNames(),'form'=>$form, 'modelName'=>'Campaign'));
?>

<h2>Attachments</h2>

<div id="campaign-attachments-wrapper" class="x2-layout form-view">
<div class="formSection showSection">
	<div class="formSectionHeader">
		<span class="sectionTitle">Attachments</span>
	</div>
	<div id="campaign-attachments" class="tableWrapper" style="min-height: 100px; padding: 5px;">
		<?php $attachments = $model->attachments; ?>
		<?php if($attachments) { ?>
			<?php foreach($attachments as $attachment) { ?>
				<?php $media = $attachment->mediaFile; ?>
				<?php if($media && $media->fileName) { ?>
					<div style="font-weight: bold;">
						<span class="filename"><?php echo $media->fileName; ?></span>
						<input type="hidden" value="<?php echo $media->id; ?>" name="AttachmentFiles[id][]" class="AttachmentFiles">
						<span class="remove"><a href="#">[x]</a></span>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } ?>
		<div class="next-attachment" style="font-weight: bold;">
			<span class="filename"></span>
			<span class="remove"></span>
		</div>
	</div>
</div>
</div>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

<?php $this->endWidget(); ?>
