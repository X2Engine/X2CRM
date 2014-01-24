<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/
Yii::app()->clientScript->registerMain();
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/fieldEditor.js', CClientScript::POS_HEAD);
$loadUrl = Yii::app()->createUrl('/admin/createUpdateField');
Yii::app()->clientScript->registerScript('fieldEditor-config', 'x2.fieldEditor.loadUrl = '.json_encode($loadUrl).';', CClientScript::POS_READY);
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
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();$('#removeField').hide();x2.fieldEditor.load('create')" class="x2-button"><?php echo Yii::t('admin','Add Field');?></a>
    <a href="javascript:void(0);" onclick="$('#removeField').show();$('#createUpdateField').hide();" class="x2-button"><?php echo Yii::t('admin','Remove Field');?></a>
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();$('#removeField').hide();x2.fieldEditor.load('update')" class="x2-button"><?php echo Yii::t('admin','Customize Field');?></a>
    <br>
    <br>
    <div id="createUpdateField" style="display:none">
    </div>

    <div id="removeField" style="display:none;">
        <?php
        $this->renderPartial('removeFields', array(
            'fields' => $fields,
        ));
        ?>
    </div>
</div>