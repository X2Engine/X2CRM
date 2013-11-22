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
<div class="page-title"><h2><?php echo Yii::t('admin', 'Tag Manager'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php echo Yii::t('admin', "This is a list of all tags currently used within the app."); ?><br />
        <?php echo Yii::t('admin', "To delete a tag, click the delete link in the grid below.  This will remove any relationship between that tag and records, but textual references to the tag will be preserved.") ?><br /><br />
        <?php echo Yii::t('admin', 'To delete all tags, use the "Delete All" button at the bottom of the grid.'); ?>
    </div>
</div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'tags-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div class="page-title"><h2>'.Yii::t('admin', 'Tags').'</h2><div class="title-bar">'
    .'{summary}</div></div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        array(
            'header' => Yii::t('admin','Tag'),
            'name' => 'tag',
            'type' => 'raw',
            'value' => "CHtml::link(\$data->tag,array('/search/search','term'=>'#'.ltrim(\$data->tag,'#')), array('class'=>'x2-link x2-tag'))"
        ),
        array(
            'header' => Yii::t('admin','# of Records'),
            'type' => 'raw',
            'value' => "X2Model::model('Tags')->countByAttributes(array('tag'=>\$data->tag))"
        ),
        array(
            'header' => Yii::t('admin','Delete Tag'),
            'type' => 'raw',
            'value' => "CHtml::link(Yii::t('admin','Delete Tag'),'#',array('class'=>'x2-button', 'submit'=>'deleteTag?tag='.\substr(\$data->tag,1),'confirm'=>Yii::t('admin','Are you sure you want to delete this tag?')))"
        ),
    ),
));
?><br>
<?php echo CHtml::link(Yii::t('admin', 'Delete All'), '#', array('class' => 'x2-button', 'submit' => 'deleteTag?tag=all', 'confirm' => Yii::t('admin','Are you sure you want to delete all tags?'))); ?>
