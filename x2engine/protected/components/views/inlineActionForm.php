<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerScript('highlightSaveAction',"
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
	}
	);
}
);");

$inlineForm = (isset($inlineForm)); // true if this is in the InlineActionForm
$quickCreate = $inlineForm? false : ($this->getAction()->getId() == 'quickCreate');	// true if we're inside the quickCreate view
if(isset($_GET['inline']))
    $inlineForm=$_GET['inline'];
$action = $inlineForm? array('/actions/actions/create','inline'=>1) : null;

?>
<script>
    $(function() {
            var tabs=$( "#tabs" ).tabs();
            $("#actions-newCreate-form").submit(function(){
               $("#save-button1").val(tabs.tabs('option', 'selected'));
           })
    });

</script>
<div id="tabs">
<ul>
	<li><a href="#tabs-1"><?php echo Yii::t('actions','Log A Call'); ?></a></li>
	<li><a href="#tabs-2"><?php echo Yii::t('actions','New Action'); ?></a></li>
	<li><a href="#tabs-3"><?php echo Yii::t('actions','New Comment'); ?></a></li>
</ul>

<div class="form" id="action-form">
<?php
$form = $this->beginWidget('CActiveForm', array(
	'action'=>$action,
	'id'=>'actions-newCreate-form',
	'enableAjaxValidation'=>false,
));
//echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";

echo $form->errorSummary($actionModel);
?>
<div class="row">
<b><?php echo $form->labelEx($actionModel,'actionDescription'); ?></b>
<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>($inlineForm?3:6), 'cols'=>40,'style'=>'width:500px;')); ?>
<div id="tabs-1">

</div>

<div id="tabs-2">


<div class="row">
	<?php
	if ($inlineForm) {
		echo $form->hiddenField($actionModel,'associationType');
	} else {
	?>
	<div class="row">
		<div class="cell">
		<?php echo $form->label($actionModel,'associationType'); ?>
		<?php echo $form->dropDownList($actionModel,'associationType',
			array(
				'none'=>Yii::t('actions','None'),
				'contacts'=>Yii::t('actions','Contact'),
				'opportunities'=>Yii::t('actions','Opportunity'),
				'accounts'=>Yii::t('actions','Account'),
			),
			array(
				'ajax' => array(
					'type'=>'POST', //request type
					'url'=>CController::createUrl('/actions/actions/parseType'), //url to call.
					//Style: CController::createUrl('currentController/methodToCall')
					'update'=>'#', //selector to update
					'success'=>'function(data){
							window.location="?param='.Yii::app()->user->getName().';"+data+":0";
						}'
					)
				)
			);
			echo $form->error($actionModel,'associationType'); ?>
		</div>
		<div class="cell" id="auto_complete">
			<?php
			echo $form->label($actionModel,'associationName');
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'auto_select',
				'value'=>$actionModel->associationName,
				'source' => $this->createUrl('/actions/actions/getTerms',array('type'=>$actionModel->associationType)),
				'options'=>array(
					'minLength'=>'2',
					'select'=>'js:function( event, ui ) {
						$("#'.CHtml::activeId($actionModel,'associationId').'").val(ui.item.id);
						$(this).val(ui.item.value);
						return false;
					}',
				),
			));
			//echo $form->error($actionModel,'associationName');
			?>
		</div>
	</div>
	<?php } ?>

	<div class="cell">
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>
		<?php echo $form->label($actionModel,'dueDate');
		if ($actionModel->isNewRecord)
			$actionModel->dueDate = date('M d, Y',time()).' 23:59';	//default to tomorow for new actions
		else
			$actionModel->dueDate = date('M d, Y H:i',$actionModel->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$actionModel, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'M dd, yy',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'priority'); ?>
		<?php echo $form->dropDownList($actionModel,'priority',array(
			'1'=>Yii::t('actions','Low'),
			'2'=>Yii::t('actions','Medium'),
			'3'=>Yii::t('actions','High')));
		//echo $form->error($actionModel,'priority'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'assignedTo'); ?>
		<?php echo $form->dropDownList($actionModel,'assignedTo',$users,array('id'=>'actionsAssignedToDropdown')); ?>
		<?php //echo $form->error($actionModel,'assignedTo'); ?>
		<?php /* x2temp */
			echo "<br />";
			if($this instanceof ActionsController){
				$url=$this->createUrl('/groups/groups/getGroups');
			}else{
				$url=$this->controller->createUrl('/groups/groups/getGroups');
			}
			echo "<label>Group?</label>";
			echo CHtml::checkBox('group','',array(
				'id'=>'groupCheckbox',
				'ajax'=>array(
					'type'=>'POST', //request type
						'url'=>$url, //url to call.
						//Style: CController::createUrl('currentController/methodToCall')
						'update'=>'#actionsAssignedToDropdown', //selector to update
						'complete'=>'function(){
							if($("#groupCheckbox").attr("checked")!="checked"){
								$("#groupCheckbox").attr("checked","checked");
								$("#Actions_visibility option[value=\'2\']").remove();
							}else{
								$("#groupCheckbox").removeAttr("checked");
								$("#Actions_visibility").append(
									$("<option></option>").val("2").html("User\'s Groups")
								);
							}
						}'
				)
			));
		/* end x2temp */ ?>
	</div>

	<div class="cell">
		<?php echo $form->label($actionModel,'visibility'); ?>
		<?php
			$visibility=array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private'));
			/* x2temp */
			$visibility[2]='User\'s Groups';
			/* end x2temp */
			?>
		<?php echo $form->dropDownList($actionModel,'visibility',$visibility); ?>
		<?php //echo $form->error($actionModel,'visibility'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'reminder'); ?>
		<?php //echo $form->checkBox($actionModel,'reminder',array('value'=>'Yes','uncheckedValue'=>'No')); ?>
		<?php echo $form->dropDownList($actionModel,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes'))); ?>
	</div>
</div>
</div>
<div id="tabs-3">

</div>
</div>
</div>
<div class="row buttons">
	<?php echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),
			array('type'=>'submit','class'=>'x2-button','id'=>'save-button1','name'=>'submit')); ?>
</div>
<?php $this->endWidget(); ?>
</div>
