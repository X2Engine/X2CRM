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



?>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sessions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('admin','User View Log').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
            'name'=>'user',
            'header'=>Yii::t('admin','User'),
            'type'=>'raw',
            'value'=>'User::getUserLinks($data->user)',
        ),
         array(
            'name'=>'link',
            'header'=>Yii::t('admin','Link'),
            'type'=>'raw',
            'value'=>'X2Model::getModelLink($data->recordId,X2Model::getModelName($data->recordType))',
        ),
        array(
            'name'=>'timestamp',
            'header'=>Yii::t('admin','Timestamp'),
            'type'=>'raw',
            'value'=>'Formatter::formatCompleteDate($data->timestamp)',
        ),
        array(
            'name'=>'recordType',
            'header'=>Yii::t('admin','Record Type'),
            'type'=>'raw',
            'value'=>'CHtml::encode($data["recordType"])'
        ),
        array(
            'name'=>'recordId',
            'header'=>Yii::t('admin','Record ID'),
            'type'=>'raw',
        ),
	),
));
echo "<br>";
echo CHtml::link(Yii::t('admin','Clear View History'),'clearViewHistory',array('class'=>'x2-button','confirm'=>'Are you sure you want to delete the user view history?'));