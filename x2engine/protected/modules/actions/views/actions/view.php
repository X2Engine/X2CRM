<?php
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




Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/auxlib.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/X2Tags/TagContainer.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/X2Tags/TagCreationContainer.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/X2Tags/InlineTagsContainer.js');

$authParams['X2Model'] = $model;
$menuOptions = array(
    'todays', 'my', 'everyones', 'create', 'view', 'edit', 'share', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

?>
<div class="page-title icon actions">
	<h2><?php
    if($model->associationName=='none')
        echo Yii::t('actions','{action}', array('{action}' => Modules::displayName()));
    else if ($model->isMultiassociated())
        echo '<span class="no-bold">',Yii::t('actions','{action}', array('{action}' => Modules::displayName())),':</span> '.$model->renderMultiassociations (false);
    else
        echo '<span class="no-bold">',Yii::t('actions','{action}', array('{action}' => Modules::displayName())),':</span> '.CHtml::encode($model->associationName); ?>
	</h2>
</div>
<?php
$this->renderPartial('_detailView',array('model'=>$model));

if (empty($model->type) || $model->type=='Web Lead') {
	if ($model->complete=='Yes')
		echo CHtml::link(Yii::t('actions','Uncomplete'),array('/actions/actions/uncomplete','id'=>$model->id),array('class'=>'x2-button'));
	else {
?>
<?php
if(isset($associationModel) && $model->associationType=='contacts') {
    $this->actionMenu[] = array('label'=>Yii::t('app','Send Email'),'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;'));
	$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>'"'.$associationModel->name.'" <'.$associationModel->email.'>, ',
			// 'subject'=>'hi',
			// 'redirect'=>'contacts/'.$model->id,
			'modelName'=>'Contacts',
			'modelId'=>$associationModel->id,
		),
		'associationType'=>'Contacts',
		'startHidden'=>true,
	)
);
}

$this->widget('X2WidgetList', array ('model' => $model));

$completeUrl = $this->createUrl ('complete', array (
	'id' => $model->id
)); 
?>

<div class="form" id="action-form">
	<form id="complete-action" name="complete-action" method='POST' action="<?php echo $completeUrl ?>">
		<b><?php echo Yii::t('actions','Completion Notes'); ?></b>
		<textarea name="note" rows="4" ></textarea>
	<div class="row buttons">
		<button type="submit" name="submit" class="x2-button" value="complete"><?php echo Yii::t('actions','Complete'); ?></button>
		<button type="submit" name="submit" class="x2-button" value="completeNew"><?php echo Yii::t('actions','Complete + New Action'); ?></button>

	</div>
    <?php echo X2Html::csrfToken (); ?>   
	</form>
</div>
<?php
	}
}

if($model->associationId!=0 && !is_null($associationModel)) {
	if($model->associationType=='contacts') {
		echo '<div class="page-title rounded-top"><h2>'.Yii::t('actions','Contact Info').'</h2></div>';
		$this->renderPartial('application.modules.contacts.views.contacts._detailViewMini',array('model'=>$associationModel,'actionModel'=>$model));
	}

	$actionHistory=new CActiveDataProvider('Actions', array(
		'criteria'=>array(
			'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
			'condition'=>'associationId='.$model->associationId.' AND associationType=\''.$model->associationType.'\''
	)));

	$this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$actionHistory,
		'itemView'=>'_view',
		'htmlOptions'=>array('class'=>'action list-view'),
		'template'=> '<h3>'.Yii::t('app','History').'</h3>{summary}{sorter}{items}{pager}',
	));
}


?>
<!--<a class="x2-button" href="#" onClick="x2.forms.toggleForm('#action-form',400);return false;"><span><?php echo Yii::t('app','Create Action'); ?></span></a>-->
<?php /*
	$this->widget('InlineActionForm',
			array(
				'associationType'=>'contact',
				'associationId'=>$model->associationId,
				'assignedTo'=>Yii::app()->user->getName(),
				'users'=>$users,
				'startHidden'=>true
			)
	);
	*/
?>
<script>
$('#complete-button').click(function(){
    $("form#complete-action").submit();
});
</script>
