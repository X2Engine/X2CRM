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




Yii::app()->clientScript->registerCss ('createModule', "
#newModule .cell {
    margin-right: 42px;
}
");

?>

<div class="page-title"><h2><?php echo Yii::t('module', 'Create New Module'); ?></h2></div>
<div class="form">
    <div style="width:600px">
        <?php 
        echo Yii::t('admin', 'This form will allow you to create a custom module with similar '.
            'functionality to the other existing modules. '); 
        echo Yii::t('admin', 'Please fill out the fields below to create a new module. After '.
            'fields are created, you will need to enter the Form Editor to create a form layout'); 
        ?>
        <br> <br>
        <?php 
        echo Yii::t('admin','Extra fields should be added from the "Manage Fields" page.');
        ?>
        <br><br>
    </div>
</div>
<div class="form">
    <?php if(!empty($errors)){ ?>
        <div class="errorSummary">
            <p><?php echo Yii::t('yii', 'Please fix the following input errors:'); ?></p>
            <ul><?php foreach($errors as $error){ ?>
                <li><?php echo $error; ?></li><?php } ?>
            </ul>
        </div><br>
    <?php } ?>
    <form id="newModule" method="POST" action="createModule">
        <div class="row">
            <div class="cell" style="width:200px;">
                <label for="title">
                    <?php echo Yii::t('module', 'Module Title'); ?><span class="required">*</span>
                </label><?php echo Yii::t('module', 'The name for your new module'); ?>
                <br>
                <input type="text" size="30" onFocus="x2.forms.toggleText(this);" onBlur="x2.forms.toggleText(this);" 
                 style="color:#aaa;" name="title" id="title" />
            </div>
            <div class="cell">
                <label for="recordName">
                    <?php echo Yii::t('module', 'Item Name'); ?>
                </label>
                <?php echo Yii::t('module', '(Optional) What to call individual records, e.g. "Create new X"'); ?>
                <br>
                <input type="text" size="30" onFocus="x2.forms.toggleText(this);" onBlur="x2.forms.toggleText(this);" 
                 style="color:#aaa;" name="recordName" id="recordName" />
            </div>
        </div>
        <div class="row">
            <div class="cell">
                <label for="moduleName">
                    <?php echo Yii::t('module', 'DB Table Name'); ?>
                </label>
                <?php 
                echo Yii::t('module', 'Optional (alphanumeric only, must start with a letter)'); 
                ?>
                <br>
                <input type="text" size="30" onFocus="x2.forms.toggleText(this);" onBlur="x2.forms.toggleText(this);" 
                 style="color:#aaa;" name="moduleName" id="moduleName" />
                <br>
            </div>
        </div>

        <div class="row">
            <div class="cell">
                <label for="searchable">
                    <?php echo Yii::t('admin','Is this module searchable?');?>
                </label>
                <select name="searchable" type="dropdown">
                    <option value="1">
                        <?php echo Yii::t('app','Yes');?>
                    </option>
                    <option value="0">
                        <?php echo Yii::t('app','No');?>
                    </option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="cell">
                <label for="editable">
                    <?php echo Yii::t('admin','Can this module have forms/fields edited?');?>
                </label>
                <select name="editable" type="dropdown">
                    <option value="1">
                        <?php echo Yii::t('app','Yes');?>
                    </option>
                    <option value="0">
                        <?php echo Yii::t('app','No');?>
                    </option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="cell">
                <label for="adminOnly">
                    <?php echo Yii::t('admin','Is this module only visible to admin?');?>
                </label>
                <select name="adminOnly" type="dropdown">
                    <option value="1">
                        <?php echo Yii::t('app','Yes');?>
                    </option>
                    <option value="0" selected="selected">
                        <?php echo Yii::t('app','No');?>
                    </option>
                </select>
            </div>
        </div>

        <br>
        <br>
        <input type="Submit" name="Submit" value="<?php echo Yii::t('app', 'Submit'); ?>" 
         class="x2-button" />
         <?php echo X2Html::csrfToken(); ?>
</div>
