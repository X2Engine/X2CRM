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

$authParams['assignedTo'] = $model->assignedTo;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
	array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
	array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
	array('label'=>Yii::t('contacts','View'),'url'=>array('view', 'id'=>$model->id)),
    array('label'=>Yii::t('contacts','Edit Contact'),'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('contacts','Share Contact'),'url'=>array('shareContact','id'=>$model->id)),
	array('label'=>Yii::t('contacts','View Relationships')),
	array('label'=>Yii::t('contacts','Delete Contact'),'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
),$authParams);

?>

<?php 
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'opportunities-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('opportunities','Relationships for Contact: '.$model->name).'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'name'=>'secondType',
                        'header'=>Yii::t("contacts",'Type'),
			'value'=>'($data->firstType=="Contacts" && $data->firstId=="'.$model->id.'")?$data->secondType:$data->firstType',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
                array(
			'name'=>'name',
                        'header'=>Yii::t("contacts",'Record'),
			'value'=>'($data->firstType=="Contacts" && $data->firstId=="'.$model->id.'")?
                            (!is_null(X2Model::model($data->secondType)->findByPk($data->secondId))?CHtml::link(X2Model::model($data->secondType)->findByPk($data->secondId)->name,array("/".strtolower($data->secondType)."/".strtolower($data->secondType)."/view/id/".$data->secondId)):"Record not found."):
                            (!is_null(X2Model::model($data->firstType)->findByPk($data->firstId))?CHtml::link(X2Model::model($data->firstType)->findByPk($data->firstId)->name,array("/".strtolower($data->firstType)."/".strtolower($data->firstType)."/view/id/".$data->firstId)):"Record not found.")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
	),
));