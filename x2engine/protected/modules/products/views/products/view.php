<?php
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

include("protected/modules/products/productConfig.php");
$themeUrl = Yii::app()->theme->getBaseUrl();
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('create')),
	array('label'=>Yii::t('module','View',array('{X}'=>$moduleConfig['recordName']))),
	array('label'=>Yii::t('module','Update',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete',array('{X}'=>$moduleConfig['recordName'])), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
));
?>
<div id="main-column" class="half-width">
<div class="page-title icon products">
<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('products','Product:'); ?></span> <?php echo $model->name; ?></h2>
	<a class="x2-button icon edit right" href="update/<?php echo $model->id;?>"><span></span></a>
</div>
<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Product')); ?>

<div class="form">
	<b><?php echo Yii::t('app', 'Tags'); ?></b>
<?php $this->widget('InlineTags', array('model'=>$model)); ?>
</div>

<?php $this->widget('Attachments',array('associationType'=>'products','associationId'=>$model->id)); ?>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'products',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'products','associationId'=>$model->id));
?>