<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>"relationships-grid",
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'afterAjaxUpdate'=>'js: function(id, data) { refreshQtip(); }',
	'dataProvider'=>$relationshipsDataProvider,
	'columns'=>array(
        array(
			'name'=>'name',
			'header'=>Yii::t("contacts",'Name'),
			'value'=>'$data->link',
			'type'=>'raw',
		),
		array(
			'name'=>'myModelName',
			'header'=>Yii::t("contacts",'Type'),
			'value'=>'$data->myModelName',
			'type'=>'raw',
		),
        array(
			'name'=>'assignedTo',
			'header'=>Yii::t("contacts",'Assigned To'),
			'value'=>'$data->renderAttribute("assignedTo")',
			'type'=>'raw',
		),
		array(
			'name'=>'createDate',
			'header'=>Yii::t('contacts','Date Created'),
			'value'=>'$data->renderAttribute("createDate")',
			'type' => 'raw'
		),
        array(
			'name'=>'deletion',
			'header'=>Yii::t("contacts",'Delete'),
			'value'=>"CHtml::link(CHtml::image(Yii::app()->theme->baseUrl.'/css/gridview/delete.png'),'#',array('class'=>'x2-hint','title'=>'Deleting this relationship will not delete the linked record.', 'submit'=>'".Yii::app()->controller->createUrl('/site/deleteRelationship')."?firstId='.\$data->id.'&firstType='.\$data->myModelName.'&secondId=".$model->id."&secondType=".get_class($model)."&redirect=/".Yii::app()->controller->getId()."/".$model->id."','confirm'=>'Are you sure you want to delete this relationship?'))",
            'type'=>'raw',
			'htmlOptions'=>array('style'=>'width:25px;text-align:center;'),
			'headerHtmlOptions'=>array('style'=>'width:50px'),
		),
	),
));

?>

<?php if($modelName != 'Contacts') { ?>
	<div class="form" style="display: inline-block; margin-top: 20px;">
		<div style="width: 200px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Contacts_id">
			<?php
				$staticLinkModel = X2Model::model('Contacts');
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
		); ?>
	</div>
<?php } ?>

<?php if($modelName != 'Accounts') { ?>
	<div class="form" style="display: inline-block; margin-top: 20px;">
		<div style="width: 200px; margin: 0 auto;">
			<input type="hidden" id="Relationships_Accounts_id">
			<?php
				$staticLinkModel = X2Model::model('Accounts');
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


<?php if($modelName != 'Opportunity') { ?>

<div class="form" style="display: inline-block; margin-top: 20px;">
<div style="width: 200px; margin: 0 auto;">
<input type="hidden" id="Relationships_Opportunity_id">
<?php
	$staticLinkModel = X2Model::model('Opportunity');
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

