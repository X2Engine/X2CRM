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






Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/packager.css');
Tours::loadTips('admin.packager');
?>

<div class="page-title"><h2><?php 
    echo Yii::t('admin', 'X2Packager');
?></h2></div>

<div id='packager-form' class="form">
    <?php 
    echo Yii::t('admin', 
       'X2Packager allows you to export or import a complete set '.
       'of customizations to the system, including a custom theme, '.
       'modules, fields and dropdowns, {processes}, document and email '.
       'templates, roles and permissions, and optionally {contact} data', array(
           '{contact}' => strtolower(Modules::displayName (false, 'Contacts')),
           '{processes}' => strtolower(Modules::displayName (true, 'Workflow')),
       ));

    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'packages-grid',
        'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
            '/css/gridview',
        'template'=> '<div class="page-title"><h2>'.
            Yii::t('admin','AppliedPackages').'</h2><div class="title-bar">'
            .'{summary}</div></div>{items}{pager}',
            'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
        'dataProvider'=>new CArrayDataProvider ($appliedPackages, array('keyField' => 'name')),
        'columns'=>array(
            array(
                'name'=>'name',
                'value'=>'$data["name"]',
                'header'=>Yii::t('admin', 'Name')
            ),
            array(
                'name'=>'modules',
                'value'=>'isset($data["modules"])? implode(", ", $data["modules"]) : null',
                'header'=>Yii::t('admin', 'Modules')
            ),
            array(
                'name'=>'roles',
                'value'=>'isset($data["roles"])? implode(", ", $data["roles"]) : null',
                'header'=>Yii::t('admin', 'Roles')
            ),
            array(
                'name'=>'media',
                'value'=>'isset($data["media"])? implode(", ", $data["media"]) : null',
                'header'=>Yii::t('admin', 'Media')
            ),
            array(
                'name'=>'count',
                'value'=>'is_numeric($data["count"])? $data["count"] : 0',
                'header'=>Yii::t('admin', 'Number of Records')
            ),
            array(
                'value'=>'CHtml::link (Yii::t("admin", "Revert"),
                    array("revertPackage", "name" => $data["name"]),
                    array("class" => "x2-button revert-btn"))',
                'type' => 'raw',
            ),
        ),
        'emptyText' => Yii::t('admin', 'There are currently no packages applied'),
    ));

    echo '<h3>'.Yii::t('admin', 'Import X2Package').'</h3>';
    echo CHtml::form('previewPackageImport','post',array('enctype'=>'multipart/form-data','id'=>'file-form'));
    echo CHtml::fileField('data', '', array('id'=>'import-data'));
    echo CHtml::submitButton(Yii::t('admin','Import'), array(
        'class' => 'x2-button',
        'id' => 'import-button'
    ));
    echo CHtml::endForm(); ?>
    <div id="file-form-status" style="color:red"></div>

    <?php X2Html::getFlashes(); ?>

    <br /><br />
    <hr>
    <h3><?php echo Yii::t('admin', 'Export X2Package'); ?></h3>
        <?php echo Yii::t('admin', 'Select the components of the system you would like to package'); ?>
    <br /><br />

<?php
    echo '<h4>'.Yii::t('admin', 'Package Name').'</h4>';
    echo '<div class="row">';
    echo CHtml::textField ('packageName', '', array(
        'placeholder' => Yii::t('admin', 'Please enter a package name'),
    ));
    echo '</div>';
    echo '<div class="row">';
    echo CHtml::textArea ('packageDescription', '', array(
        'placeholder' => Yii::t('admin', 'Please enter a description for your package'),
    ));
    echo '</div>';

    echo '<div class="row">';
    $this->renderPackageComponentSelection (Yii::t('admin', 'Modules'), 'module',
        function($elem) { return $elem->title; }, $modules, Yii::t('admin', 'modules'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Custom Fields'), 'field',
        function($elem) { return $elem->modelName.'.'.$elem->fieldName; }, $fields, Yii::t('admin', 'fields'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Form Layouts'), 'formLayout',
        function($elem) { return X2Model::getModelTitle($elem->model).' '.$elem->version; },
        $forms, Yii::t('admin', 'form layout'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Flows'), 'flow',
        function($elem) { return $elem->name; }, $flows, Yii::t('admin', 'flows'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Media'), 'media',
        function($elem) {
            return $elem->fileName . ($elem->name ? ' ('.$elem->name.')' : ''); },
        $media, null, array (
            'class' => 'media-items-box',
        ));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Themes'), 'themes',
        function($elem) { return $elem->fileName; }, $themes);

    $this->renderPackageComponentSelection (Yii::t('admin', '{processes}', array(
        '{processes}' => Modules::displayName(true, 'Workflow')
    )), 'process', function($elem) { return $elem->name; }, $processes, Yii::t('admin', 'processes'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Roles'), 'roles',
        function($elem) { return $elem->name; }, $roles, Yii::t('admin', 'roles'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Templates'), 'template',
        function($elem) { return $elem->name.' ('.ucfirst($elem->type).')'; }, $templates,
        Yii::t('admin', 'templates'));

    echo '<h4>'.CHtml::encode (Yii::t('admin', 'Data')).'</h4>';
    echo '<div class="row">';
    echo '<div class="cell">'.CHtml::checkbox ('includeContacts').'</div>';
    echo '<div class="cell" style="padding-top:4px">'.CHtml::label (Yii::t('admin',
        'Include {contact} data?', array(
            '{contact}' => strtolower(Modules::displayName (false, 'Contacts')),
        )
    ), 'includeContacts').'</div>';
    echo '</div><br />';

    echo '<div id="export-loading"></div><br />';
    echo '<br /><div id="status-box"></div><br />';
    echo CHtml::button(Yii::t('admin', 'Export'), array(
        'class' => 'x2-button',
        'id' => 'export-button'
    ));
    echo CHtml::button(Yii::t('admin', 'Download'), array(
        'class' => 'x2-button',
        'id' => 'download-link',
    ));

    Yii::app()->clientScript->registerScript ('package-export','
    ;(function () {
        var collectExportables = function(type) {
            var exportables = [];
            $(".exportable-" + type + ":checked").each(function() {
                var elemId = $(this).attr("id").split("-");
                if (elemId.length > 1)
                    exportables.push(elemId[1]);
            });
            return exportables;
        };

        var getExportComponents = function() {
            var exportComponents = {
                "selectedModules": collectExportables("module"),
                "selectedFields": collectExportables("field"),
                "selectedFormLayout": collectExportables("formLayout"),
                "selectedX2Flow": collectExportables("flow"),
                "selectedWorkflow": collectExportables("process"),
                "selectedDocs": collectExportables("template"),
                "selectedRoles": collectExportables("roles"),
                "selectedMedia": collectExportables("media"),
                "selectedThemes": collectExportables("themes"),
                "includeContacts": $("#includeContacts").is(":checked") ? "true" : "false",
                "packageName": $("#packageName").val(),
                "packageDescription": $("#packageDescription").val()
            }
            if (exportComponents["selectedModules"].length == 0 &&
                    exportComponents["selectedFields"].length == 0 &&
                    exportComponents["selectedFormLayout"].length == 0 &&
                    exportComponents["selectedX2Flow"].length == 0 &&
                    exportComponents["selectedWorkflow"].length == 0 &&
                    exportComponents["selectedDocs"].length == 0 &&
                    exportComponents["selectedMedia"].length == 0 &&
                    exportComponents["selectedRoles"].length == 0 &&
                    exportComponents["selectedThemes"].length == 0 &&
                    exportComponents["includeContacts"] == "false") {
                alert("'.CHtml::encode (Yii::t('admin', 'Nothing selected to package!')).'");
                return false;
            }
            return exportComponents;
        };

        var selectAll = function(type) {
            $(".exportable-" + type).each (function() {
                $(this).attr("checked", "checked");
            });
        };
        var deselectAll = function(type) {
            $(".exportable-" + type).each (function() {
                $(this).removeAttr("checked");
            });
        };

        $("#export-button").click(function() {
            var exportComponents = getExportComponents();
            if (!exportComponents)
                return;

            $("#status-box").html ("'.CHtml::encode (Yii::t('admin', 'Beginning export')).'");
            $("#status-box").css ("color", "green");
            $("#export-button").hide();
            auxlib.containerLoading($("#export-loading"));

            $.ajax({
                url: "'.$this->createUrl ('exportPackage').'",
                type: "post",
                data: exportComponents,
                success: function(data) {
                    auxlib.containerLoadingStop($("#export-loading"));
                    data = JSON.parse(data);
                    if (data[0] == "success") {
                        $("#status-box").append ("<br />'.Yii::t('admin', 'Export complete').'");
                        $("#download-link").slideDown();
                    } else {
                        $("#status-box").html ("'.Yii::t('admin', 'Export failed: ').'");
                        $("#status-box").append (data["message"]);
                        $("#status-box").css ("color", "red");
                        $("#export-button").show();
                    }
                }
            });
        });

        $(".selectall").click(function(e) {
            var namespace = $(this).attr ("id").split("-")[1];
            if ($(this).attr("checked") === "checked")
                selectAll (namespace);
            else
                deselectAll (namespace);
        });
        $("#download-link").click(function(e) {
            e.preventDefault();  //stop the browser from following
            window.location.href = "downloadData?file=X2Package-" +
                $("#packageName").val() + ".zip";
        });

        $("#import-button").click(function(e) {
            e.preventDefault();
            var filename = $("#import-data").val();
            var filenameComponents = filename.split(".");
            if (filename.length === 0 || filenameComponents.length < 2) {
                $("#file-form-status").html ("'. 
                    CHtml::encode (Yii::t('admin', 'No file specified')).'");
            } else if (filenameComponents[1] !== "zip") {
                $("#file-form-status").html ("'. 
                    CHtml::encode (Yii::t('admin', 'Not a zip archive')).'");
            } else {
                $("#file-form-status").html ("");
                $("#file-form").submit();
            }
        });

        $(function() {
            $("#download-link").hide();
        });
    }) ();
    ', CClientScript::POS_END);
?>
</div>
