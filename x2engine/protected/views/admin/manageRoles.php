
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'roles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('accounts','Role List').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'name',
	),
)); ?>
<br />
<a href="#" onclick="$('#addRole').toggle();$('#deleteRole').hide();$('#editRole').hide();" class="x2-button">Add Role</a>
<a href="#" onclick="$('#deleteRole').toggle();$('#addRole').hide();$('#editRole').hide();" class="x2-button">Delete Role</a>
<a href="#" onclick="$('#editRole').toggle();$('#addRole').hide();$('#deleteRole').hide();" class="x2-button">Edit Role</a>
<br />
<br />
<div id="addRole" style="display:none;">
<?php $this->renderPartial('roleEditor',array(
    'model'=>$model,
)); ?>
</div>

<div id="deleteRole" style="display:none;">
<?php $this->renderPartial('deleteRole',array(
    'roles'=>$roles,
)); ?>
</div>

<div id="editRole" style="display:none;">
<?php $this->renderPartial('editRole',array(
    'model'=>$model,
)); ?>
</div>