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




$folderForm = $this->beginWidget('X2ActiveForm', array(
    'formModel' => $model,
    'id' => 'folder-form-inner',
));
X2Flashes::renderFlashes('error');
X2Flashes::renderFlashes('success');
echo $folderForm->labelEx($model, 'name');
echo $folderForm->textField($model, 'name');
echo $folderForm->hiddenField($model, 'parentFolder');
echo $folderForm->labelEx($model, 'visibility');
echo $folderForm->dropDownList(
    $model, 
    'visibility', 
    X2PermissionsBehavior::getVisibilityOptions (), array (
        'data-default' => 1
    ));
echo X2Html::ajaxSubmitButton('', '', array(
    'dataType' => 'json',
    'success'=>'function(data){
        var folderForm$ = $("#folder-form")
        if (data.success) {
            folderForm$.dialog("close");
            x2.forms.clearForm ($("#folder-form form"), true);
            x2.flashes.displayFlashes({
                success:['.
                    CJSON::encode (Yii::t('docs','Folder created.')).']});

            $.fn.yiiGridView.update("folder-contents", {complete:function(){ 
                x2.folderManager.setUpDragAndDrop(); }});
        } else {
            folderForm$.find ("form").replaceWith (data.form);
        }
    }',
), array(
    'class' => 'x2-button',
    'live' => false,
    'style' => 'display: none;',
));
$this->endWidget();

?>
