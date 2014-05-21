<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

Yii::app()->clientScript->registerCss('inlineRelationshipsCss',"

#relationship-model-name-container {
    text-align: left;
    overflow: hidden;
}
#relationship-model-name-container label {
    display: inline !important;
}

#quick-create-record {
    position: relative;
    top: 7px;
    right: 9px;
}

#relationships-grid .summary {
    margin-bottom: 6px;
}

#new-relationship-form .record-name-autocomplete {
    width: 200px !important;
    float: left;
}

#relationships-grid .x2grid-header-container {
    border-radius: 4px 4px 0 0;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
    border: 1px solid rgb(197, 197, 197);
    border-bottom: none;
}

#relationships-grid .x2grid-body-container {
    border-radius: 0 0 4px 4px;
    -moz-border-radius: 0 0 4px 4px;
    -webkit-border-radius: 0 0 4px 4px;
    -o-border-radius: 0 0 4px 4px;
    border: 1px solid rgb(197, 197, 197);
    border-top: none;
}

#relationships-grid {
    margin-bottom: 9px;
    margin-top: 11px;
}

label[for=\"RelationshipModelName\"],
#relationship-type {
    float: left;
}

label[for=\"RelationshipModelName\"] {
    margin-top: 12px;
    margin-right: 6px;
}

#quick-create-record {
    margin-left: 11px;
    margin-top: 2px;
}

#add-relationship-button {
    clear: both;
}

");

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
');

$relationshipsDataProvider = new CArrayDataProvider($model->relatedX2Models,array(
	'id' => 'relationships-gridview',
	'sort' => array('attributes'=>array('name','myModelName','createDate','assignedTo')),
	'pagination' => array('pageSize'=>10)
));

?>

<div id="relationships-form" style="text-align: center;">

<?php
$columns = array(
	array(
		'name' => 'name',
		'header' => Yii::t("contacts", 'Name'),
		'value' => '$data->link',
		'type' => 'raw',
	),
	array(
		'name' => 'myModelName',
		'header' => Yii::t("contacts", 'Type'),
        'value' => 'X2Model::getModelTitle ($data->myModelName)',
		'type' => 'raw',
	),
	array(
		'name' => 'assignedTo',
		'header' => Yii::t("contacts", 'Assigned To'),
		'value' => '$data->renderAttribute("assignedTo")',
		'type' => 'raw',
	),
	array(
		'name' => 'createDate',
		'header' => Yii::t('contacts', 'Create Date'),
		'value' => '$data->renderAttribute("createDate")',
		'type' => 'raw'
	),
);

$columns[] = array(
    'name' => 'deletion',
    'header' => Yii::t("contacts", 'Delete'),
    'value' => 
        "CHtml::link(
            CHtml::image(
                Yii::app()->theme->baseUrl.'/css/gridview/delete.png'),
            'javascript:void(0);',
            array(
                'class'=>'x2-hint',
                'title'=>'Deleting this relationship will not delete the linked record.',
                'submit'=>'".Yii::app()->controller->createUrl('/site/deleteRelationship').
                    "?firstId='.\$data->id.'&firstType='.get_class(\$data).
                    '&secondId=".$model->id."&secondType=".get_class($model).
                    "&redirect=/".Yii::app()->controller->getId()."/".$model->id."',
                'confirm'=>'Are you sure you want to delete this relationship?'))",
    'type' => 'raw',
);

$this->widget('X2GridViewGeneric', array(
	'id' => "relationships-grid",
    'defaultGvSettings' => array (
        'name' => 180,
        'myModelName' => 180,
        'assignedTo' => 180,
        'createDate' => 180,
        'deletion' => 60
    ),
    'gvSettingsName' => 'inlineRelationshipsGrid',
	'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
	'template' => '<div class="title-bar">{summary}</div>{items}{pager}',
	'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
	'dataProvider' => $relationshipsDataProvider,
	'columns' => $columns,
));

?>

<?php 

if($hasUpdatePermissions) {

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');

Yii::app()->clientScript->registerScript('inlineRelationshipsScript',"

x2.InlineRelationships = (function () {

function InlineRelationships (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        defaultsByRelatedModelType: {}, // {<model type>: <dictionary of default attr values>}
        createUrls: {}, // {<model type>: <string>}
        modelType: null,
        modelId: null,
        dialogTitles: {}, // {<model type>: <string>}
        tooltips: {}, // {<model type>: <string>}

        // used to determine which models the quick create button is displayed for
        modelsWhichSupportQuickCreate: [], 
        createRelationshipUrl: '',
        DEBUG: false && x2.DEBUG, 

        // used to request to autocomplete widgets when related model type is changed
        ajaxGetModelAutocompleteUrl: '' 
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._relationshipManager;

    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Set up quick create button for given model class
 * @param string modelType 
 */
InlineRelationships.prototype.initQuickCreateButton = function (modelType) {

    if (this._relationshipManager && 
        this._relationshipManager instanceof x2.RelationshipsManager) {

        this._relationshipManager.destructor ();
    }

    if ($.inArray (modelType, this.modelsWhichSupportQuickCreate) !== -1) {
        $('#quick-create-record').show ();
    } else {
        $('#quick-create-record').hide ();
        return;
    }

    this._relationshipManager = new x2.RelationshipsManager ({
        element: $('#quick-create-record'),
        modelType: this.modelType,
        modelId: this.modelId,
        relatedModelType: modelType,
        createRecordUrl: this.createUrls[modelType],
        attributeDefaults: this.defaultsByRelatedModelType[modelType] || {},
        dialogTitle: this.dialogTitles[modelType],
        tooltip: this.tooltips[modelType]
    });

};

/**
 * Requests a new autocomplete widget for the specified model class, replacing the current one
 * @param string modelType
 */
InlineRelationships.prototype._changeAutoComplete = function (modelType) {
    $('#inline-relationships-autocomplete-container').hide ();
    $('#inline-relationships-autocomplete-container').before ($('<div>', {
        'class': 'x2-loading-icon',
        'style': 'height: 27px; background-size: 27px;'
    }));
    $.ajax ({
        type: 'GET',
        url: this.ajaxGetModelAutocompleteUrl,
        data: {
            modelType: modelType
        },
        success: function (data) {
            // remove span element used by jQuery widget
            $('#inline-relationships-autocomplete-container input').
                first ().next ('span').remove ();
            // replace old autocomplete with the new one
            $('#inline-relationships-autocomplete-container input').first ().replaceWith (data); 
            // remove the loading gif
            $('#inline-relationships-autocomplete-container').prev ().remove ();
            $('#inline-relationships-autocomplete-container').show ();
        }
    });
};

/**
 * submits relationship create form via AJAX, performs validation 
 */
InlineRelationships.prototype._submitCreateRelationshipForm = function () {
    var that = this; 
    if ($('#RelationshipModelId').val() === '') {
        that.DEBUG && console.log ('model id is not set');
        return false;
    } else if (isNaN(parseInt($('#RelationshipModelId').val()))) {
        that.DEBUG && console.log ('model id is NaN');
        return false;
    } else if($('.record-name-autocomplete').val() === '') {
        that.DEBUG && console.log ('second name autocomplete is not set');
        return false;
    }

    $.ajax ({
        url: this.createRelationshipUrl,
        type: 'POST', 
        data: $('#new-relationship-form').serializeArray (),
        success: function (data) {
			if(data === 'duplicate') {
				alert('Relationship already exists.');
			} else if(data === 'success') {
				$.fn.yiiGridView.update('relationships-grid');
                var count = parseInt ($('#relationship-count').html ().match (/\((\d+)\)/)[1], 10);
                $('#relationship-count').html ('(' + (count + 1) + ')');

				$('.record-name-autocomplete').val('');
				$('#RelationshipModelId').val('');
			}
        }
    });
};

/**
 * Sets up create form submission button behavior 
 */
InlineRelationships.prototype._setUpCreateFormSubmission = function () {
    var that = this;
    $('#add-relationship-button').on ('click', function () {
        that._submitCreateRelationshipForm ();
        return false;
    });
};

/*
Private instance methods
*/

InlineRelationships.prototype._init = function () {
    var that = this;
    
    this._setUpCreateFormSubmission ();

    $('#relationship-type').change (function () {
        that.initQuickCreateButton ($(this).val ()); 
        that._changeAutoComplete ($(this).val ());
    });
};

return InlineRelationships;

}) ();

x2.inlineRelationships = new x2.InlineRelationships ({
    defaultsByRelatedModelType: ".CJSON::encode ($defaultsByRelatedModelType).",
    createUrls: ".CJSON::encode ($createUrls).",
    modelType: '".$modelName."',
    modelId: ".$model->id.",
    dialogTitles: ".CJSON::encode ($dialogTitles).",
    tooltips: ".CJSON::encode ($tooltips).",
    modelsWhichSupportQuickCreate: $.map (".CJSON::encode ($modelsWhichSupportQuickCreate).",
        function (val) { return val; }),
    ajaxGetModelAutocompleteUrl: '".
        Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete')."',
    createRelationshipUrl: '".
        Yii::app()->controller->createUrl ('/site/addRelationship')."'
});

x2.inlineRelationships.initQuickCreateButton ('Contacts');

", CClientScript::POS_READY);

?>

<form id='new-relationship-form' class="form">
    <input type="hidden" id='ModelId' name="ModelId" value="<?php echo $model->id; ?>">
    <input type="hidden" id='ModelName' name="ModelName" value="<?php echo $modelName; ?>">

    <div id='inline-relationships-autocomplete-container'>
    <?php
    X2Model::renderModelAutocomplete ('Contacts');
    ?>
    <input type="hidden" id='RelationshipModelId' name="RelationshipModelId">
    </div>
    <div class='row' id='relationship-model-name-container'>
        <label for='RelationshipModelName'>
            <?php echo Yii::t('app', 'Link Type:'); ?>
        </label>
        <?php
        echo CHtml::dropDownList (
            'RelationshipModelName', 'Contacts', $linkableModelsOptions, 
            array (
                'id' => 'relationship-type',
                'class' => 'x2-select',
            ));
        echo CHtml::link(
            CHtml::image(Yii::app()->theme->getBaseUrl ().'/images/Plus_sign.png'),'#',
            array(
                'onclick'=>'return false;',
                'id'=>'quick-create-record',
                'style' => 'display: none;'
            ));
        ?>
    </div>
    
    <button id='add-relationship-button' class='x2-button'>
        <?php echo Yii::t('app', 'Create Relationship'); ?>
    </button>
</form>

<?php } ?>

</div>
