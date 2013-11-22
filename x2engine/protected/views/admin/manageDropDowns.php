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
<div class="page-title"><h2><?php echo Yii::t('admin', 'Dropdown List'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php echo Yii::t('admin', 'Manage all dropdowns.  These can be linked to fields via Field Management.  Any default dropdowns can also be edited here to change the available options throughout the application.  Deleting default dropdowns may cause issues with pre-existing forms.') ?>
    </div>
</div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'fields-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<h2>'.Yii::t('admin', 'Dropdowns').'</h2><div class="title-bar">'
    .'{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        'name',
        'options',
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
<a href="#" onclick="$('#createDropdown').toggle();$('#deleteDropdown').hide();$('#editDropdown').hide();" class="x2-button"><?php echo Yii::t('admin', 'Create Dropdown'); ?></a>
<a href="#" onclick="$('#deleteDropdown').toggle();$('#createDropdown').hide();$('#editDropdown').hide();" class="x2-button"><?php echo Yii::t('admin', 'Delete Dropdown'); ?></a>
<a href="#" onclick="$('#editDropdown').toggle();$('#createDropdown').hide();$('#deleteDropdown').hide();" class="x2-button"><?php echo Yii::t('admin', 'Edit Dropdown'); ?></a>
<br>
<br>
<div id="createDropdown" style="display:none;">
    <?php
    $this->renderPartial('dropDownEditor', array(
        'model' => $model,
    ));
    ?>
</div>
<div id="deleteDropdown" style="display:none;">
<?php
$this->renderPartial('deleteDropdowns', array(
    'dropdowns' => $dropdowns,
));
?>
</div>
<div id="editDropdown" style="display:none;">
<?php
$this->renderPartial('editDropdown', array(
    'model' => $model,
));
?>
</div>