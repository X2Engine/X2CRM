<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed'),'url'=>array('/profile/index')),
	array('label'=>Yii::t('users','Manage Users'), 'url'=>array('admin')),
	array('label'=>Yii::t('users','Create User'), 'url'=>array('create')),
	array('label'=>Yii::t('users','Invite Users')),
));
?>
<div class="page-title icon users"><h2><?php echo Yii::t('users','Invite Users to X2CRM'); ?></h2></div>

<form method="POST">
<div class="form">
<h2><?php echo Yii::t('users','Instructions'); ?></h2>
<?php echo Yii::t('users','Please enter a list of e-mails separated by commas.'); ?>
	<div class="row"><textarea name="emails" style="width:600px;height:150px;"></textarea></div>
	<div class="row"><input type="submit" value="<?php echo Yii::t('app','Submit');?>" class="x2-button"></div>
</div>
</form>
