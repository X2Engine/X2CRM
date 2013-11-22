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

<div class="page-title"><h2><?php echo Yii::t('module', 'Create New Module'); ?></h2></div>
<div class="form">
    <div style="width:600px">
        <?php echo Yii::t('admin', 'This form will allow you to create a custom module with similar functionality to the other existing modules. '); ?>
        <?php echo Yii::t('admin', 'Please fill out the fields below to create a new module. After fields are created, you will need to enter the Form Editor to create a form layout'); ?><br><br>
        <?php echo Yii::t('admin','Extra fields should be added from the "Manage Fields" page.');?><br><br>
    </div>
</div>
<div class="form">
    <?php if(!empty($errors)){ ?>
        <div class="errorSummary"><p><?php echo Yii::t('yii', 'Please fix the following input errors:'); ?></p>
            <ul><?php foreach($errors as $error){ ?>
                    <li><?php echo $error; ?></li><?php } ?>
            </ul>
        </div><br>
    <?php } ?>
    <form id="newModule" method="POST" action="createModule">
        <div class="row">
            <div class="cell" style="width:200px;"><label for="title"><?php echo Yii::t('module', 'Module Title'); ?> <span class="required">*</span></label><?php echo Yii::t('module', 'The name for your new module'); ?><br><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="title" id="title" /></div>
            <div class="cell"><label for="recordName"><?php echo Yii::t('module', 'Item Name'); ?></label><?php echo Yii::t('module', '(Optional) What to call individual records, e.g. "Create new X"'); ?><br><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="recordName" id="recordName" /></div>
        </div>
        <div class="row">
            <div class="cell"><label for="moduleName"><?php echo Yii::t('module', 'DB Table Name'); ?></label><?php echo Yii::t('module', 'Optional (alphanumeric only, must start with a letter)'); ?><br><input type="text" size="30" onFocus="toggleText(this);" onBlur="toggleText(this);" style="color:#aaa;" name="moduleName" id="moduleName" /><br></div>
        </div>

        <div class="row">
            <div class="cell"><label for="searchable"><?php echo Yii::t('admin','Is this module searchable?');?></label><select name="searchable" type="dropdown"><option value="1"><?php echo Yii::t('app','Yes');?></option><option value="0"><?php echo Yii::t('app','No');?></option></select></div>
        </div>

        <div class="row">
            <div class="cell"><label for="editable"><?php echo Yii::t('admin','Can this module have forms/fields edited?');?></label><select name="editable" type="dropdown"><option value="1"><?php echo Yii::t('app','Yes');?></option><option value="0"><?php echo Yii::t('app','No');?></option></select></div>
        </div>

        <div class="row">
            <div class="cell"><label for="adminOnly"><?php echo Yii::t('admin','Is this module only visible to admin?');?></label><select name="adminOnly" type="dropdown"><option value="1"><?php echo Yii::t('app','Yes');?></option><option value="0" selected="selected"><?php echo Yii::t('app','No');?></option></select></div>
        </div>

        <br><br><input type="Submit" name="Submit" value="<?php echo Yii::t('app', 'Submit'); ?>" class="x2-button" />
</div>