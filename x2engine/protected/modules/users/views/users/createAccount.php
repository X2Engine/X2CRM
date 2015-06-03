<?php
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/users.css');
$groups=array();
foreach(Groups::model()->findAll() as $group){
	$groups[$group->id]=$group->name;
}
$roles=array();
foreach(Roles::model()->findAll() as $role){
	$roles[$role->id]=$role->name;
}
?>
<!--<div class="page-title icon users"><h2>
    <?php echo Yii::t('users','Create {user}', array(
        '{user}' => Modules::displayName(false),
    )); ?>
</h2></div> -->
<div id="container">
<div id="login-box-outer">
<div id="login-box">
<?php echo $this->renderPartial(
    '_form', array(
        'update' => false,
        'model'=>$user,
        'roles'=>$roles,
        'groups'=>$groups,
        'selectedGroups'=>array(),
        'selectedRoles'=>array(),
        'flag'=>true,
        'create'=>true,
        'status'=>false
    ));
?>
</div>
</div>
</div>
