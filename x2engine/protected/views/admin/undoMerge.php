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






Yii::app()->clientScript->registerCss('undoMergeCss',"

#merge-grid {
    position: relative;
}

#merge-grid  .summary {
    position: absolute;
    top: -28px;
    right: 0;
}

");

$columns = array(
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Record Link')),
        'name' => 'modelLink',
        'type' => 'raw',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Merged Record')),
        'name' => 'mergeModel',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Record Type')),
        'name' => 'modelType',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Involved Records')),
        'name' => 'recordCount',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Timestamp')),
        'name' => 'mergeDate',
        'type' => 'raw',
        'value' => 'Formatter::formatDateTime($data["mergeDate"])',
        'headerHtmlOptions' => array('style'=>'width:175px'),
    ),
    array(
        'type' => 'raw',
        'header' => CHtml::encode(Yii::t('admin', 'Undo Merge')),
        'name' => 'invalidUndo',
        'value' => 'CHtml::ajaxButton("' . Yii::t('admin', "Undo") . '","undoMerge",array('
        . '"type"=>"POST","data"=>array("mergeModelId"=>$data["mergeModelId"],"modelType"=>$data["modelType"]),'
        . '"success"=>"window.location = window.location"'
        . '),'
        . 'array("class"=>"x2-button","style"=>$data["invalidUndo"]?"color:grey":"","disabled"=>$data["invalidUndo"]?"disabled":""))'
    )
);

echo "<div class='page-title'><h2>" . Yii::t('admin', 'Undo Record Merge') . "</h2></div>";
echo "<div class='form'>";
echo "<div style='width:600px;'><br>";
echo Yii::t('admin', "This page allows for reverting record merges which users have performed in the app.")
 . "<br><br>";
echo Yii::t('admin', "Reverted merges will restore all original data to the original records, and delete the record that was created by the merge. Any new data on the merged record will be lost.")
 . "<br><br>";
echo Yii::t('admin', "Merged records which have been merged again into new records cannot be reverted until all record merges further down the chain are undone.")
 . "<br><br>";
echo Yii::t('admin','Fields with unique constraints will not have data restored upon reverting a merge.');
echo "</div><br>";
echo "</div>";

echo "<div class='page-title'><h2>".Yii::t('admin','Record Merge Log')."</h2></div>";
$this->widget('application.components.X2GridView.X2GridViewGeneric', array(
    'id' => 'merge-grid',
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'filter' => $filtersForm,
));
