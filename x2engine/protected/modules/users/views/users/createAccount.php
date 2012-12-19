<?php

$groups=array();
foreach(Groups::model()->findAll() as $group){
	$groups[$group->id]=$group->name;
}
$roles=array();
foreach(Roles::model()->findAll() as $role){
	$roles[$role->id]=$role->name;
}
?>
<h1><?php echo Yii::t('users','Create User'); ?></h1>
<?php echo $this->renderPartial('_form', array('model'=>$user, 'roles'=>$roles, 'groups'=>$groups,'selectedGroups'=>array(),'selectedRoles'=>array(),'flag'=>true)); ?>