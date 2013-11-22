<?php
/*********************************************************************************
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
 ********************************************************************************/
$themeUrl = Yii::app()->theme->getBaseUrl();
$auth = Yii::app()->authManager;
//printR ((int) isset (Yii::app()->controller));
$moduleName = '';
if (!is_object (Yii::app()->controller->module) && isset ($moduleName)) {
    //printR ($this->module, true);
    $moduleName = $moduleName;
} else {
    $moduleName = Yii::app()->controller->module->name;
}
$actionAccess = ucfirst($moduleName).'Update';
$authItem = $auth->getAuthItem($actionAccess);
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
		$("#Relationships_Contacts_autocomplete").data( "uiAutocomplete" )._renderItem = function( ul, item ) {
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

$hideRelationships = true;
if($startHidden == false) {
	$relationshipsCount = count($relationshipsDataProvider->data);
	if($relationshipsCount > 1) {
		$hideRelationships = false;
	}
}
?>

<div id="relationships-form" style="text-align: center;<?php //echo ($hideRelationships? ' display: none;' : ''); ?>">

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
		'value' => '$data->myModelName',
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
if(Yii::app()->user->checkAccess(ucfirst($moduleName).'Update', array('assignedTo' => $model->assignedTo)))
	$columns[] = array(
		'name' => 'deletion',
		'header' => Yii::t("contacts", 'Delete'),
		'value' => "CHtml::link(CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/delete.png'),'javascript:void(0);',array('class'=>'x2-hint','title'=>'Deleting this relationship will not delete the linked record.', 'submit'=>'".Yii::app()->controller->createUrl('/site/deleteRelationship')."?firstId='.\$data->id.'&firstType='.get_class(\$data).'&secondId=".$model->id."&secondType=".get_class($model)."&redirect=/".Yii::app()->controller->getId()."/".$model->id."','confirm'=>'Are you sure you want to delete this relationship?'))",
		'type' => 'raw',
		'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
		'headerHtmlOptions' => array('style' => 'width:50px'),
	);


$this->widget('zii.widgets.grid.CGridView', array(
	'id' => "relationships-grid",
	'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template' => '<div class="title-bar">'
	.'{summary}</div>{items}{pager}',
	'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
	'dataProvider' => $relationshipsDataProvider,
	'columns' => $columns,
));

?>

<?php if(!isset($authItem) || Yii::app()->user->checkAccess($actionAccess,array('assignedTo'=>$model->assignedTo))) { ?>
	<div class="form" style="width:29%;display: inline-block; margin-top: 20px;">
		<div style="width: 170px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Contacts_id">
			<?php
				$staticLinkModel = X2Model::model('Contacts');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'Relationships_Actions',
					'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
                    'value'=>Yii::t('app','Start typing to suggest...'),
					'options' => array(
						'minLength' => '1',
						'select' => 'js:function( event, ui ) {
					    			$(this).val(ui.item.value);
					    			$("#Relationships_Contacts_id").val(ui.item.id);
					    			return false;
					    		}',
					),
					'htmlOptions'=>array(
						'id'=>'Relationships_Contacts_autocomplete',
						'class'=>'relationships-add-autocomplete',
                        'onfocus' => 'toggleText(this);',
                        'onblur' => 'toggleText(this);',
                        'style'=>'color:#aaa',
					),
				));
                echo CHtml::link(CHtml::image($themeUrl.'/images/Plus_sign.png'),'#',array('class'=>'right','style'=>'margin-top:6px;','onclick'=>'return false;','id'=>'create-contact'));
			?>
		</div>
		<?php echo CHtml::ajaxButton(
			Yii::t('app', 'Link Contact'),
			Yii::app()->controller->createUrl('/site/addRelationship'),
			array(
				'type'=>'POST',
				'data'=>"js:{ModelName: '$modelName', ModelId: '{$model->id}', RelationshipModelName: 'Contacts', RelationshipModelId: parseInt(\$('#Relationships_Contacts_id').val())}",
				'beforeSend'=>'function(xhr) {
					if($("#Relationships_Contacts_id").val() == "") {
						return false;
					} else if( isNaN(parseInt($("#Relationships_Contacts_id").val())) ) {
						return false;
					} else if($("#Relationships_Contacts_autocomplete").val() == "") {
						return false;
					}
				}',
				'success'=>'function(response) {
					if(response == "duplicate") {
						alert("Relationship already exists.");
					} else if(response == "success") {
						$.fn.yiiGridView.update("relationships-grid");
						$("#Relationships_Contacts_autocomplete").val("");
						$("#Relationships_Contacts_id").val("");
					}
				}'
			),
			array(
				'class'=>'x2-button',
				'id'=>'Relationships_Contacts_addbutton',
				'style'=>'margin: 0 auto;'
			)
		);

                ?>
	</div>
<?php } ?>

<?php if(!isset($authItem) || Yii::app()->user->checkAccess($actionAccess,array('assignedTo'=>$model->assignedTo))) { ?>
	<div class="form" style="width:29%;display: inline-block; margin-top: 20px;">
		<div style="width: 170px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Accounts_id">
			<?php
				$staticLinkModel = X2Model::model('Accounts');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'Relationships_Actions',
					'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
                    'value'=>Yii::t('app','Start typing to suggest...'),
					'options' => array(
						'minLength' => '1',
						'select' => 'js:function( event, ui ) {
					    			$(this).val(ui.item.value);
					    			$("#Relationships_Accounts_id").val(ui.item.id);
					    			return false;
					    		}',
					),
					'htmlOptions'=>array(
						'id'=>'Relationships_Accounts_autocomplete',
						'class'=>'relationships-add-autocomplete',
                        'onfocus' => 'toggleText(this);',
                        'onblur' => 'toggleText(this);',
                        'style'=>'color:#aaa',
					),
				));
                echo CHtml::link(CHtml::image($themeUrl.'/images/Plus_sign.png'),'#',array('class'=>'right','style'=>'margin-top:6px;','onclick'=>'return false;','id'=>'create-account'));
			?>
		</div>
		<?php echo CHtml::ajaxButton(
			Yii::t('app', 'Link Account'),
			Yii::app()->controller->createUrl('/site/addRelationship'),
			array(
				'type'=>'POST',
				'data'=>"js:{ModelName: '$modelName', ModelId: '{$model->id}', RelationshipModelName: 'Accounts', RelationshipModelId: parseInt(\$('#Relationships_Accounts_id').val())}",
				'beforeSend'=>'function(xhr) {
					if($("#Relationships_Accounts_id").val() == "") {
						return false;
					} else if( isNaN(parseInt($("#Relationships_Accounts_id").val())) ) {
						return false;
					} else if($("#Relationships_Accounts_autocomplete").val() == "") {
						return false;
					}
				}',
				'success'=>'function(response) {
					if(response == "duplicate") {
						alert("Relationship already exists.");
					} else if(response == "success") {
						$.fn.yiiGridView.update("relationships-grid");
						$("#Relationships_Accounts_autocomplete").val("");
						$("#Relationships_Accounts_id").val("");
					}
				}'
			),
			array(
				'class'=>'x2-button',
				'id'=>'Relationships_Accounts_addbutton',
				'style'=>'margin: 0 auto;'
			)
		); ?>
	</div>
<?php } ?>


<?php if(!isset($authItem) || Yii::app()->user->checkAccess($actionAccess,array('assignedTo'=>$model->assignedTo))) { ?>

<div class="form" style="width:29%;display: inline-block; margin-top: 20px;">
<div style="width: 170px; margin: 0 auto;">
<input type="hidden" id="Relationships_Opportunity_id">
<?php
	$staticLinkModel = X2Model::model('Opportunity');
	$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
		'name'=>'Relationships_Actions',
		'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
        'value'=>Yii::t('app','Start typing to suggest...'),
		'options' => array(
			'minLength' => '1',
			'select' => 'js:function( event, ui ) {
		    			$(this).val(ui.item.value);
		    			$("#Relationships_Opportunity_id").val(ui.item.id);
		    			return false;
		    		}',
		),
		'htmlOptions'=>array(
			'id'=>'Relationships_Opportunity_autocomplete',
			'class'=>'relationships-add-autocomplete',
            'onfocus' => 'toggleText(this);',
            'onblur' => 'toggleText(this);',
            'style'=>'color:#aaa',
		),
	));
    echo CHtml::link(CHtml::image($themeUrl.'/images/Plus_sign.png'),'#',array('class'=>'right','style'=>'margin-top:6px;','onclick'=>'return false;','id'=>'create-opportunity'));
?>
</div>
<?php
echo CHtml::ajaxButton(
	Yii::t('app', 'Link Opportunity'),
	Yii::app()->controller->createUrl('/site/addRelationship'),
	array(
		'type'=>'POST',
		'data'=>"js:{ModelName: '$modelName', ModelId: '{$model->id}', RelationshipModelName: 'Opportunity', RelationshipModelId: parseInt(\$('#Relationships_Opportunity_id').val())}",
		'beforeSend'=>'function(xhr) {
			if($("#Relationships_Opportunity_id").val() == "") {
				return false;
			} else if( isNaN(parseInt($("#Relationships_Opportunity_id").val())) ) {
				return false;
			} else if($("#Relationships_Opportunity_autocomplete").val() == "") {
				return false;
			}
		}',
		'success'=>'function(response) {
			if(response == "duplicate") {
				alert("Relationship already exists.");
			} else if(response == "success") {
				$.fn.yiiGridView.update("relationships-grid");
				$("#Relationships_Opportunity_autocomplete").val("");
				$("#Relationships_Opportunity_id").val("");
			}
		}'
	),
	array(
		'class'=>'x2-button',
		'id'=>'Relationships_Opportunity_addbutton',
		'style'=>'margin: 0 auto;'
	)
);
?>
</div>
<?php } ?>

	</div>

