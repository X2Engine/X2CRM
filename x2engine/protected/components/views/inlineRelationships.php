<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
	$(".contact-name").each(function (i) {
		var contactId = $(this).attr("href").match(/\\d+$/);

		if(typeof contactId != null && contactId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.baseUrl+"/index.php/contacts/qtip",
						data: { id: contactId[0] },
						method: "get",
					}
				},
				style: {
				}
			});
		}
	});
	
	if($("#Relationships_Contacts_autocomplete").length == 1) {
		$("#Relationships_Contacts_autocomplete").data( "autocomplete" )._renderItem = function( ul, item ) {
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
			
			label += "<br>" + item.assignedTo;
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

$modelName = ucwords($modelName);
$relationshipsDataProvider = new CActiveDataProvider('Relationships', array(
    'criteria' => array(
    	'condition' => "(firstType=\"$modelName\" AND firstId=\"{$model->id}\") OR (secondType=\"$modelName\" AND secondId=\"{$model->id}\")",
    )
));

$hideRelationships = true;
if($startHidden == false) {
	$relationshipsCount = count($relationshipsDataProvider->data);
	if($relationshipsCount > 1) {
		$hideRelationships = false;
	} else if($relationshipsCount == 1) {
		$relationshipsData = $relationshipsDataProvider->data;
		$relationship = $relationshipsData[0];
		$hideRelationships = false;
		if($modelName == 'Contacts' && $relationship && ($relationship->firstType == 'Accounts' || $relationship->secondType == 'Accounts') ) {
			$hideRelationships = true;
		}
	}
}
?>

<div id="relationships-form" style="text-align: center;<?php echo ($hideRelationships? ' display: none;' : ''); ?>">
	<div class="form">

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>"opportunities-grid",
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('opportunities','Relationships').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'afterAjaxUpdate'=>'js: function(id, data) { refreshQtip(); }',
	'dataProvider'=>$relationshipsDataProvider,
	'columns'=>array(
        array(
			'name'=>'name',
			'header'=>Yii::t("contacts",'Name'),
			'value'=>'($data->firstType=="'.$modelName.'" && $data->firstId=="'.$model->id.'")?
                            (!is_null(CActiveRecord::model($data->secondType)->findByPk($data->secondId))?CHtml::link(CActiveRecord::model($data->secondType)->findByPk($data->secondId)->name,array("/".(strtolower($data->secondType)=="opportunity"? "opportunities" : strtolower($data->secondType))."/".$data->secondId."/"), array("class"=>($data->secondType=="Contacts"? "contact-name":null))):"Record not found."):
                            (!is_null(CActiveRecord::model($data->firstType)->findByPk($data->firstId))?CHtml::link(CActiveRecord::model($data->firstType)->findByPk($data->firstId)->name,array("/".(strtolower($data->firstType)=="opportunity"? "opportunities" : strtolower($data->firstType))."/".$data->firstId."/"), array("class"=>($data->firstType=="Contacts"? "contact-name":null))):"Record not found.")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
		array(
			'name'=>'secondType',
			'header'=>Yii::t("contacts",'Type'),
			'value'=>"(\$data->firstType==\"$modelName\" && \$data->firstId==\"{$model->id}\")?\$data->secondType:\$data->firstType",
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
        array(
			'name'=>'name',
			'header'=>Yii::t("contacts",'Assigned To'),
			'value'=>'($data->firstType=="Contacts" && $data->firstId=="'.$model->id.'")?
                            (!is_null(CActiveRecord::model($data->secondType)->findByPk($data->secondId))?UserChild::getUserLinks(CActiveRecord::model($data->secondType)->findByPk($data->secondId)->assignedTo):"Record not found."):
                            (!is_null(CActiveRecord::model($data->firstType)->findByPk($data->firstId))?UserChild::getUserLinks(CActiveRecord::model($data->firstType)->findByPk($data->firstId)->assignedTo):"Record not found.")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
	),
));

?>

<?php if($modelName != 'Contacts') { ?>
	<div class="form" style="display: inline-block; margin-top: 20px;">
		<div style="width: 200px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Contacts_id">
			<?php
				$staticLinkModel = CActiveRecord::model('Contacts');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'Relationships_Actions',
					'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
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
					),
				));
			?>
		</div>
		<?php echo CHtml::ajaxButton(
			Yii::t('app', 'Associate Contact'),
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
						$.fn.yiiGridView.update("opportunities-grid");
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
		); ?>
	</div>
<?php } ?>

<?php if($modelName != 'Accounts') { ?>
	<div class="form" style="display: inline-block; margin-top: 20px;">
		<div style="width: 200px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Accounts_id">
			<?php
				$staticLinkModel = CActiveRecord::model('Accounts');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'Relationships_Actions',
					'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
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
					),
				));
			?>
		</div>
		<?php echo CHtml::ajaxButton(
			Yii::t('app', 'Associate Account'),
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
						$.fn.yiiGridView.update("opportunities-grid");
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


<?php if($modelName != 'Opportunity') { ?>

<div class="form" style="display: inline-block; margin-top: 20px;">
<div style="width: 200px; margin: 0 auto;">
<input type="hidden" id="Relationships_Opportunity_id">
<?php
	$staticLinkModel = CActiveRecord::model('Opportunity');
	$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
		'name'=>'Relationships_Actions',
		'source' => Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
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
		),
	));
?>
</div>
<?php
echo CHtml::ajaxButton(
	Yii::t('app', 'Associate Opportunity'),
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
				$.fn.yiiGridView.update("opportunities-grid");
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
</div>

