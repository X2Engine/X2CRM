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




include("protected/modules/bugReports/bugReportsConfig.php");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>Modules::itemDisplayName()))),
	array('label'=>Yii::t('module','Update {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
    ModelFileUploader::menuLink()

));
?>
<div class="page-title">
    <h2>
    <?php 
         echo Yii::t('module','View {X}',array('{X}'=>Modules::itemDisplayName())); ?>: <?php 
         echo $model->name; 
    ?>
    </h2>
    <?php
    echo X2Html::emailFormButton();
    echo X2Html::inlineEditButtons();
    ?>
</div>
<div id="main-column" class="half-width">
<?php 
$this->widget('DetailView', array(
	'model' => $model,
	'modelName' =>'BugReports'
));
// $this->renderPartial('application.components.views.@DETAILVIEW',array('model'=>$model, 'modelName'=>'BugReports')); 

$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			'to'=>implode (', ', $model->getRelatedContactsEmails ()),
			'modelName'=>'BugReports',
			'modelId'=>$model->id,
		),
		'insertableAttributes' => 
            array(
                Yii::t('accounts','Bug Report Attributes')=>$model->getEmailInsertableAttrs ($model)
            ),
		'startHidden'=>true,
	)
);

$this->widget ('ModelFileUploader', array(
    'associationType' => 'bugReports',
    'associationId' => $model->id,
));
?>


<?php
$this->widget('X2WidgetList', array('block'=>'center', 'model'=>$model, 'modelType'=>'BugReports'));

?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'bugReports',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'calendar' => false
	)
);
$this->widget('History',array('associationType'=>'BugReports','associationId'=>$model->id));
?>
</div>
