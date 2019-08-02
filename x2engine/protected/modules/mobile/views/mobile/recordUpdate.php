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




Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->module->assetsUrl.'/js/RecordCreateController.js');

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.RecordCreateController ();
", CClientScript::POS_END);

$authParams['X2Model'] = $model;

if ($model instanceof X2Model &&
    $this->hasMobileAction ('mobileDelete') && $this->hasMobileAction ('mobileUpdate') &&
    Yii::app()->user->checkAccess(ucfirst ($this->module->name).'Delete', $authParams)) {
?>




<?php
}
?>

<div class='refresh-content' data-refresh-selector='.header-content-right'>
    <?php
    $importButtonClass = '';
    if($model instanceof Contacts) { 
    ?>
        <div class='header-content-right' style="margin-right: 80px;">
            <div class='export-button'>
            <?php
            echo CHtml::encode (Yii::t('mobile', 'Export'));
            ?>
            </div>
        </div>
    <?php
    } else if($model instanceof Product) { 
        $importButtonClass = 'product-import-button';
    ?>
        <div class='header-content-right' style="margin-right: 80px;">
            <div class='<?php echo $importButtonClass; ?>'>
            <?php
                echo CHtml::encode (Yii::t('mobile', 'Import'));
            ?>
            </div>
        </div>
    <?php
    }
    ?>
    <div class='header-content-right'>
        <div class='submit-button disabled'>
        <?php
        echo CHtml::encode (Yii::t('mobile', 'Update'));
        ?>
        </div>
    </div>
</div>
<?php


$this->renderPartial ('application.modules.mobile.views.mobile._recordForm', array (
    'model' => $model
));

?>
<ul data-role='listview' data-inset='true'>
    <li>
        <a class='delete-button requires-confirmation' style="color:red;"
            href='<?php echo $this->createAbsoluteUrl ('mobileDelete', array (
                'id' => $model->id,
            )); ?>'><?php 
            echo CHtml::encode (Yii::t('mobile', 'Delete Record')); ?>
        </a>
        <div class='confirmation-text' style='display: none;'>
            <?php
            echo CHtml::encode (
                Yii::t('app', 'Are you sure you want to delete this {type}?', array (
                    '{type}' => lcfirst ($model->getDisplayName (false)),
                )));
            ?>
        </div>
    </li>
</ul>