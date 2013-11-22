<?php
/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */
?>

<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'changelog-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div class="page-title"><h2>'.Yii::t('admin','Changelog').'</h2><div class="title-bar">'
    .CHtml::link(Yii::t('app', 'Clear Filters'), array('viewChangelog', 'clearFilters' => 1))
    .'{summary}</div></div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider' => $model->search(),
    'filter' => $model,
    'afterAjaxUpdate' => 'refreshQtipHistory',
    'columns' => array(
        array(
            'header' => Yii::t('admin', 'History'),
            'value' => '$data->type=="Contacts"?CHtml::link(Yii::t("app","View"),array("/contacts/contacts/revisions","id"=>$data->itemId,"timestamp"=>$data->timestamp),array("class"=>"x2-hint","title"=>Yii::t("admin","Click to view the record at this point in its history."))):""',
            'type' => 'raw',
        ),
        array(
            'name' => 'recordName',
            'header' => Yii::t('admin', 'Record'),
            'value' => 'CHtml::link($data->recordName,Yii::app()->controller->createUrl(strtolower($data->type)."/".$data->itemId))',
            'type' => 'raw',
        ),
        'changed',
        'fieldName',
        'oldValue',
        'newValue',
        'changedBy',
        array(
            'name' => 'timestamp',
            'header' => Yii::t('admin', 'Timestamp'),
            'value' => 'Formatter::formatLongDateTime($data->timestamp)',
            'type' => 'raw',
            'htmlOptions' => array('width' => '20%'),
        ),
    ),
));
echo "<br>";
echo CHtml::link(Yii::t('admin','Clear Changelog'), '#', array('class' => 'x2-button', 'submit' => 'clearChangelog', 'confirm' => 'Are you sure you want to clear the changelog?'));
?>
<script>
    function refreshQtipHistory(){
        $('.x2-hint').qtip();
    }
</script>
