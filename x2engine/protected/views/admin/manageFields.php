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




Yii::app()->clientScript->registerCss('manageFieldsCSS',"

#fields-grid {
    border: 1px solid rgb(200, 200, 200);
    margin: 0 12px;
    border-radius: 4px;
}

#fields-grid-page-title {
    margin-bottom: -5px;
    border-bottom: none !important;
    border-radius: 4px 4px 0 0;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
}

#fields-form {
    padding-left: 0;
    padding-right: 0;
}

#remove-field-button {
    margin-left: 9px;
}

#createUpdateField-loading {
    display: none;
    position: absolute;
    opacity: 0.5;
    z-index: 100;
    background: white url(".Yii::app()->theme->baseUrl."/images/loading.gif) no-repeat center scroll;
}


.field-option.field-modified {
    background-color: #B3CFF5;
}

.field-option.field-custom {
    background-color: #C4F59D; 
}
");

Yii::app()->clientScript->registerMain();
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/fieldEditor.js', CClientScript::POS_HEAD);
$loadUrl = Yii::app()->createUrl('/admin/createUpdateField');
Yii::app()->clientScript->registerScript('fieldEditor-config', 'x2.fieldEditor.loadUrl = '.json_encode($loadUrl).';', CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Manage Fields'); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', 'This page has a list of all fields that have been modified, and allows you to add or remove your own fields, as well as customizing the pre-set fields.'); ?>
</div>
<div class="form" id="fields-form">
    <?php
    $this->widget('X2GridViewGeneric', array(
        'id' => 'fields-grid',
        'title'=>Yii::t('accounts','Modified Fields'),
        'buttons'=>array('clearFilters','columnSelector','autoResize'),
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template' => '<div class="page-title">{title}{summary}</div>{items}{pager}',
        'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
        'filter' => $searchModel,
        'dataProvider' => $dataProvider,
        'gvSettingsName' => 'manageFields',
        'defaultGvSettings' => array (
            'modelName' => 100,
            'fieldName' => 100,
            'attributeLabel' => 100,
            'required' => 60,
            'type' => 80,
            'uniqueConstraint' => 50,
            'defaultValue' => 90,
        ),
        'columns' => array(
            array (
                'name' => 'modelName',
                'type' => 'raw',
            ),
            array (
                'name' => 'fieldName',
                'type' => 'raw',
            ),
            array (
                'name' => 'attributeLabel',
                'type' => 'raw',
            ),
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
            array(
                'name' => 'type',
                'value' => 'Yii::t("app",$data->type)',
                'type' => 'raw'
            ),
            array(
                'name' => 'uniqueConstraint',
                'type' => 'raw',
                'value' => '$data->uniqueConstraint==1?Yii::t("app","Yes"):Yii::t("app","No")'
            ),
            array (
                'name' => 'defaultValue',
                'type' => 'raw',
            )
        ),
    ));

    ?>

    <br>
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();$('#removeField').hide();x2.fieldEditor.load('create')" class="x2-button" id="remove-field-button"><?php echo Yii::t('admin','Add Field');?></a>
    <a href="javascript:void(0);" onclick="$('#removeField').show();$('#createUpdateField').hide();" class="x2-button"><?php echo Yii::t('admin','Remove Field');?></a>
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();$('#removeField').hide();x2.fieldEditor.load('update')" class="x2-button"><?php echo Yii::t('admin','Customize Field');?></a>
    <br>
    <br>
    <div id="createUpdateField-loading"></div>
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
