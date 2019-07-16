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




Yii::import ('application.components.sortableWidget.components.InlineRelationshipsGridView');

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineRelationshipsWidget.css'
);

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
    $("#relationships-grid .contact-name").each(function (i) {
        var contactId = $(this).attr("href").match(/\\d+$/);

        if(contactId !== null && contactId.length) {
            $(this).qtip({
                content: {
                    text: "'.addslashes(Yii::t('app','loading...')).'",
                    ajax: {
                        url: yii.baseUrl+"/index.php/contacts/qtip",
                        data: { id: contactId[0] },
                        method: "get"
                    }
                },
                style: {
                }
            });
        }
    });

    if($("#Relationships_Contacts_autocomplete").length == 1 &&
        $("Relationships_Contacts_autocomplete").data ("uiAutocomplete")) {
        $("#Relationships_Contacts_autocomplete").data( "uiAutocomplete" )._renderItem = 
            function( ul, item ) {

            var label = "<a style=\"line-height: 1;\">" + item.label;
            label += "<span style=\"font-size: 0.7em; font-weight: bold;\">";
            if(item.city || item.state || item.country) {
                label += "<br>";

                if(item.city) {
                    label += item.city;
                }

                if(item.state) {
                    if(item.city) {
                        label += ", ";
                    }
                    label += item.state;
                }

                if(item.country) {
                    if(item.city || item.state) {
                        label += ", ";
                    }
                    label += item.country;
                }
            }
            if(item.assignedTo){
                label += "<br>" + item.assignedTo;
            }
            label += "</span>";
            label += "</a>";

            return $( "<li>" )
                .data( "item.autocomplete", item )
                .append( label )
                .appendTo( ul );
        };
    }
}

$(function() {
    refreshQtip();
});


$(".editLabelLink").parent()
    .hover(function() { $(this).find(".editLabelLink").show(); })
    .mouseleave(function() { $(".editLabelLink").hide(); });
$(".editLabelLink").click(function() {
    var promptMsg = "'.Yii::t('app', 'How would you like to label this relationship?').'";
    var errorMsg = "'.Yii::t('app', 'Failed. Please ensure you have permission in the related modules.').'";
    var id = $(this).data("id");
    var model = $(this).data("model");
    var oldLabel = $(this).data("label");
    var newLabel = prompt(promptMsg, oldLabel);
    if (newLabel != null && newLabel !== oldLabel) {
        if (newLabel === "") {
            if (!confirm("'.Yii::t('app', 'Remove this label?').'"))
                return;
        }
        $.ajax({
            type: "POST",
            url: "'.Yii::app()->createUrl ('/relationships/relabelRelationship', array(
                'secondId' => $_GET['id'],
                'secondType' => get_class($model),
            )).'",
            data: {
                firstId: id,
                firstType: model,
                label: newLabel
            },
            success: function () {
                $.fn.yiiGridView.update("relationships-grid");
            },
            error: function () {
                alert(errorMsg);
            }
        });
    }
});
');

$relationshipsDataProvider = $this->getDataProvider ();

?>

<div id="relationships-form" 
<?php  ?>
 style="<?php echo ($displayMode === 'grid' ?  '' : 'display: none;'); ?>"
<?php  ?>
 class="<?php echo ($this->getWidgetProperty ('mode') === 'simple' ? 
    'simple-mode' : 'full-mode'); ?>">

<?php

$columns = array(
    array(
        'name' => 'expandButton.',
        'header' => '',
        'value' => "in_array (
                get_class (\$data->relatedModel), 
                QuickCRUDBehavior::getModelsWhichSupportQuickView ()) ?

                '<span class=\'detail-view-toggle\' title=\'".
                    CHtml::encode (Yii::t('app', 'View inline record details'))."\'
                    data-id=\''.\$data->relatedModel->id.'\'
                    data-class=\''.get_class (\$data->relatedModel).'\'
                    data-name=\''.CHtml::encode (\$data->relatedModel->name).'\'>
                    <span class=\'fa fa-caret-right\'></span>
                    <span class=\'fa fa-caret-down\' style=\'display: none;\'></span>
                </span>' : ''",
        'type' => 'raw',
    ),
    array(
        'name' => 'name',
        'header' => Yii::t("contacts", 'Name'),
        'value' => '$data->renderAttribute ("name")',
        'type' => 'raw',
    ),
    array(
        'name' => 'relatedModelName',
        'header' => Yii::t("contacts", 'Type'),
        'value' => '$data->renderAttribute ("relatedModelName")',
        'filter' => array ('' => CHtml::encode (Yii::t('app', '-Select one-'))) + 
            $linkableModelsOptions, 
        'type' => 'raw',
    ),
    array(
        'name' => 'assignedTo',
        'header' => Yii::t("contacts", 'Assigned To'),
        'value' => '$data->renderAttribute("assignedTo")',
        'type' => 'raw',
    ),
    array(
        'name' => 'label',
        'header' => Yii::t("contacts", 'Label'),
        'value' => '$data->renderAttribute("label")." ".$data->renderInlineRelabelControls()',
        'type' => 'raw',
    ),
    array(
        'name' => 'createDate',
        'header' => Yii::t('contacts', 'Create Date'),
        'value' => '$data->renderAttribute("createDate")',
        'filterType' => 'date',
        'type' => 'raw'
    ),
);

$columns[] = array(
    // trailing dot is a kludge to prevent CDataColumn from rendering filter cell
    'name' => 'deletion.', 
    'header' => Yii::t("contacts", 'Delete'),
    'htmlOptions' => array (
        'class' =>'delete-button-cell'
    ),
    'value' => 
        "
        CHtml::ajaxLink(
            '<span class=\'fa fa-times x2-delete-icon\'></span>',
            '".Yii::app()->controller->createUrl('/site/deleteRelationship').
                "?firstId='.\$data->relatedModel->id.
                '&firstType='.get_class(\$data->relatedModel).
                '&secondId=".$model->id."&secondType=".get_class($model).
                "&redirect=/".Yii::app()->controller->getId()."/".$model->id."',
            array (
                'success' => 'function () {
                    $.fn.yiiGridView.update(\'relationships-grid\');
                }',
            ),
            array(
                'class'=>'x2-hint',
                'title'=>'Deleting this relationship will not delete the linked record.',
                'confirm'=>'Are you sure you want to delete this relationship?'))",
    'type' => 'raw',
);


$this->widget('InlineRelationshipsGridView', array(
    'id' => "relationships-grid",
    'possibleResultsPerPage' => array(5, 10, 20, 30, 40, 50, 75, 100),
    'enableGridResizing' => false,
    'showHeader' => CPropertyValue::ensureBoolean ($this->getWidgetProperty('showHeader')),
    'hideFullHeader' => CPropertyValue::ensureBoolean (
        $this->getWidgetProperty('hideFullHeader')),
    'resultsPerPage' => $this->getWidgetProperty ('resultsPerPage'),
    'sortableWidget' => $this,
    'defaultGvSettings' => array (
        'expandButton.' => '12',
        'name' => '22%',
        'relatedModelName' => '18%',
        'assignedTo' => '18%',
        'label' => '18%',
        'createDate' => '15%',
        'deletion.' => 70,
    ),
    'filter' => $this->getFilterModel (),
    'htmlOptions' => array (
        'class' => 
            ($relationshipsDataProvider->itemCount < $relationshipsDataProvider->totalItemCount ?
            'grid-view has-pager' : 'grid-view'),
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'inlineRelationshipsGrid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'template' => '<div class="title-bar">{summary}</div>{items}{pager}',
    'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
    'dataProvider' => $relationshipsDataProvider,
    'columns' => $columns,
    'enableColDragging' => false,
    'rememberColumnSort' => false,
));
?>
</div>

<!---->

<div id='inline-relationships-graph-container'>
<?php
if ($displayMode === 'graph') {
    Yii::app()->controller->renderPartial ('application.views.relationships.graphInline', array (
        'model' => $this->model,
        'inline' => true,
        'height' => $height,
    ));
}
?>
</div>

<!---->

<?php
if($hasUpdatePermissions) {

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');
?>

<div class='clearfix'></div>
<form id='new-relationship-form' class="form" style='display: none;'>
    <input type="hidden" id='ModelId' name="ModelId" value="<?php echo $model->id; ?>">
    <input type="hidden" id='ModelName' name="ModelName" value="<?php echo $modelName; ?>">


    <div class='row'>
        <?php 
        echo CHtml::label(Yii::t('app','Link Type:'), 'RelationshipModelName');
        echo CHtml::dropDownList (
            'RelationshipModelName', 'Contacts', $linkableModelsOptions, 
            array (
                'id' => 'relationship-type',
                'class' => 'x2-select field',
            ));
        echo CHtml::link(
            '', '#',
            array(
                'onclick'=>'return false;',
                'id'=>'quick-create-record',
                'class' => 'fa fa-plus fa-lg pseudo-link',
                'style' => 'visibility: hidden; height:16px;',
            ));
        ?>
    </div>

    <div class='row'>
        <?php
        echo CHtml::label( Yii::t('app','Name:'), 'RelationshipName');
        echo "<div id='inline-relationships-autocomplete-container'>";
        X2Model::renderModelAutocomplete ('Contacts');
        echo CHtml::hiddenField ('RelationshipModelId');
        echo("</div>");
        echo CHtml::textField ('myName',$model->name, array('disabled'=>'disabled'));
        ?>
        <!-- <input type="hidden" id='RelationshipModelId' name="RelationshipModelId"> -->
    </div>

    <div class='row'>
        <?php
        echo X2Html::label (Yii::t('app', 'Label:'), 'RelationshipLabelButton');
        echo X2Html::textField ('secondLabel');


        echo X2Html::textField ('firstLabel', '' ,array(
            'title' => Yii::t('app','Create a different label for ').$model->name));
        echo X2Html::hiddenField ('mutual','true');
        echo X2Html::link (
            '', '', 
            array(
                'id'=>'RelationshipLabelButton',
                'class' => 'pseudo-link fa fa-long-arrow-right',
                'title' => Yii::t('app','Create a different label for ').$model->name
            ));
        ?>
    </div>
    
    <?php 
        echo X2Html::csrfToken();
        echo CHtml::button (
            Yii::t('app', 'Create Relationship'), 
            array('id' => 'add-relationship-button', 'class'=>'x2-button'));
    ?>
    
</form>

<?php 
} 
?>

