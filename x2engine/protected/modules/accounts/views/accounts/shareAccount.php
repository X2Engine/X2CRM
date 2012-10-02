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
$authParams['assignedTo']=$model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('accounts','All Accounts'), 'url'=>array('index')),
	array('label'=>Yii::t('accounts','Create Account'), 'url'=>array('create')),
	array('label'=>Yii::t('accounts','View'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('accounts','Edit Account'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Share Account')),
	array('label'=>Yii::t('accounts','Add a User'), 'url'=>array('addUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Remove a User'), 'url'=>array('removeUser', 'id'=>$model->id)),
	array('label'=>Yii::t('accounts','Delete Account'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

// editor CSS file	
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/tinyeditor/style.css');

// editor javascript files
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/tinyeditor/tinyeditor.js');
?>
<h2><?php echo Yii::t('accounts','Share Account');?>: <b><?php echo $model->name; ?></b></h2>
<?php
if(!empty($status)) {
	$index = array_search('200',$status);
	if($index !== false) {
		unset($status[$index]);
		$email = '';
		$subject = '';
	}
	echo '<div class="form">';
	foreach($status as &$status_msg) echo $status_msg." \n";
	echo '</div>';
}
// echo var_dump($errors);
?>
<div class="form">
<form method="POST" name="share-contact-form">
	<b><span<?php if(in_array('email',$errors)) echo ' class="error"'; ?>><?php echo Yii::t('contacts','E-Mail');?></span></b><br /><input type="text" name="email" size="50"<?php if(in_array('email',$errors)) echo ' class="error"'; ?> value="<?php if(!empty($email)) echo $email; ?>"><br />
	<b><span<?php if(in_array('body',$errors)) echo ' class="error"'; ?>><?php echo Yii::t('app','Message Body');?></span></b><br /><textarea name="body" id="input" style="height:200px;width:558px;"<?php if(in_array('body',$errors)) echo ' class="error"'; ?>><?php echo $body; ?></textarea><br />
	<input type="submit" class="x2-button" value="<?php echo Yii::t('app','Share');?>" />
</form>
</div>
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'accounts-form',
	'enableAjaxValidation'=>false,
	'action'=>array('saveChanges','id'=>$model->id),
));
?>
<h2><?php echo Yii::t('accounts','Account:'); ?> <b><?php echo $model->name; ?></b></h2>
<?php
$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'accounts','form'=>$form)); 
$this->endWidget(); ?>
<script>
editor=new TINY.editor.edit('editor',{
    id:'input', // (required) ID of the textarea
    width:550, // (optional) width of the editor
    height:300, // (optional) heightof the editor
    cssclass:'te', // (optional) CSS class of the editor
    controlclass:'tecontrol', // (optional) CSS class of the buttons
    rowclass:'teheader', // (optional) CSS class of the button rows
    dividerclass:'tedivider', // (optional) CSS class of the button diviers
    controls:['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|', 'orderedlist', 'unorderedlist', '|' ,'outdent' ,'indent', '|', 'leftalign', 'centeralign', 'rightalign', 'blockjustify', 'n', '|', 'unformat', '|', 'undo', 'redo', 'font', 'size', 'style','n', '|', 'image', 'hr', 'link', 'unlink', '|', 'cut', 'copy', 'paste'], // (required) options you want available, a '|' represents a divider and an 'n' represents a new row
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

</script>