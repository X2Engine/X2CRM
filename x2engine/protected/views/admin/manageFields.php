<h2>Modified Fields</h2>
This page has a list of all fields that have been modified, and allows you to add or remove your own fields, as well as customizing the pre-set fields.
<br /><br />
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fields-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('accounts','Fields').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'modelName',
		'fieldName',
		'attributeLabel',
		array(
			'name'=>'visible',
                        'header'=>'Visibility',
			'value'=>'$data->visible==1?"Shown":"Hidden"',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'40%'),
		),
		/*
		'tickerSymbol',
		'employees',
		'associatedContacts',
		'notes',
		*/
	),
)); ?>
<br />
<a href="#" onclick="$('#addField').toggle();$('#removeField').hide();$('#customizeField').hide();" class="x2-button">Add Field</a>
<a href="#" onclick="$('#removeField').toggle();$('#addField').hide();$('#customizeField').hide();" class="x2-button">Remove Field</a>
<a href="#" onclick="$('#customizeField').toggle();$('#addField').hide();$('#removeField').hide();" class="x2-button">Customize Field</a>
<br />
<br />
<div id="addField" style="display:none;">
<?php $this->renderPartial('addField',array(
    'model'=>$model,
)); ?>
</div>

<div id="removeField" style="display:none;">
<?php $this->renderPartial('removeFields',array(
    'fields'=>$fields,
)); ?>
</div>

<div id="customizeField" style="display:none;">
<?php $this->renderPartial('customizeFields',array(
    'model'=>$model,
)); ?>
</div>