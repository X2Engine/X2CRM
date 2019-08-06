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




$this->pageTitle=Yii::t('contacts','Saved Maps');
$this->insertMenu(array(
    'all', 'lists', 'create', 'import', 'export', 'map', 'savedMaps',
));
?>
<?php

?>
<div class='flush-grid-view'>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'maps-grid',
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template'=> '<div class="page-title icon contacts"><h2>'.Yii::t('contacts','Saved Maps').'</h2><div class="title-bar">'
        .'{summary}</div></div>{items}{pager}',
    'dataProvider'=>$dataProvider,
    // 'enableSorting'=>false,
    // 'model'=>$model,
    // 'columns'=>$columns,
    // 'columnSelectorId'=>'contacts-column-selector',
    'columns'=>array(
        array(
            'name'=>'name',
            'type'=>'raw',
            'value'=>'CHtml::link($data->name,Yii::app()->controller->createUrl("googleMaps",array("loadMap"=>$data->id)))',
        ),
        array(
            'name'=>'owner',
            'type'=>'raw',
            'value'=>'User::getUserLinks($data->owner)'
        ),
        array(
            'name'=>'contactId',
            'type'=>'raw',
            'value'=>'!is_null(Contacts::model()->findByPk($data->contactId))?CHtml::link(Contacts::model()->findByPk($data->contactId)->name,array("/contacts/contacts/view","id"=>$data->contactId)):"None"',
        ),
        'zoom',
        array(
            'header'=>Yii::t('contacts','Center Coordinates'),
            'type'=>'raw',
            'value'=>'"(".$data->centerLat.", ".$data->centerLng.")"'
        ),
        array(
            'header'=>Yii::t('contacts','Location Type'),
            'type'=>'raw',
            'value'=>'!is_null($data->locationType) ? $data->locationType : Yii::t("contacts", "All")'
        ),
        array(
            'header'=>Yii::t('contacts','Delete Map'),
            'type'=>'raw',
            'value'=>'CHtml::link("Delete","#",array("submit"=>"deleteMap?id=".$data->id,"confirm"=>"Are you sure you want to delete this map?","csrf"=>true))',
        ),

    ),
));
?>
</div>

