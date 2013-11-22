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
<div class="page-title"><h2><?php echo Yii::t('admin', 'Modified Fields'); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', 'This page has a list of all fields that have been modified, and allows you to add or remove your own fields, as well as customizing the pre-set fields.'); ?>
</div>
<div class="form">
    <?php
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'fields-grid',
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template' => '<div class="page-title"><h2>'.Yii::t('admin', 'Fields').'</h2></div><div class="title-bar">'
        .'{summary}</div>{items}{pager}',
        'dataProvider' => $dataProvider,
        'columns' => array(
            'modelName',
            'fieldName',
            'attributeLabel',
            // array(
            // 'name'=>'visible',
            // 'header'=>'Visibility',
            // 'value'=>'$data->visible==1?"Shown":"Hidden"',
            // 'type'=>'raw',
            // ),
            array(
                'name' => 'required',
                'value' => '$data->required==1?Yii::t("app","Yes"):Yii::t("app","No")',
                'type' => 'raw',
            ),
        /*
          'tickerSymbol',
          'employees',
          'associatedContacts',
          'notes',
         */
        ),
    ));
    ?>
    <br>
    <a href="#" onclick="$('#addField').toggle();$('#removeField').hide();$('#customizeField').hide();" class="x2-button"><?php echo Yii::t('admin','Add Field');?></a>
    <a href="#" onclick="$('#removeField').toggle();$('#addField').hide();$('#customizeField').hide();" class="x2-button"><?php echo Yii::t('admin','Remove Field');?></a>
    <a href="#" onclick="$('#customizeField').toggle();$('#addField').hide();$('#removeField').hide();" class="x2-button"><?php echo Yii::t('admin','Customize Field');?></a>
    <br>
    <br>
    <div id="addField" style="display:none;">
        <?php
        $this->renderPartial('addField', array(
            'model' => $model,
        ));
        ?>
    </div>

    <div id="removeField" style="display:none;">
        <?php
        $this->renderPartial('removeFields', array(
            'fields' => $fields,
        ));
        ?>
    </div>

    <div id="customizeField" style="display:none;">
        <?php
        $this->renderPartial('customizeFields', array(
            'model' => $model,
        ));
        ?>
    </div>
</div>