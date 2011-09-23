<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

<div class="container" id="page">

	<div id="header">
		<div id="logo"><?php $imghtml=CHtml::image(Yii::app()->request->baseURL.'/images/x2engine.jpg');
                                        echo CHtml::link($imghtml, Yii::app()->request->baseURL.'/index.php'); ?></div>
	</div><!-- header -->

	<div id="mainmenu">
		<?php $this->widget('zii.widgets.CMenu',array(
			'items'=>array(
				array('label'=>'Actions', 'url'=>array('/actions/index'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
				array('label'=>'Contacts', 'url'=>array('/contacts/index'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
				array('label'=>'Sales', 'url'=>array('/sales/index'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
				array('label'=>'Projects', 'url'=>array('/projects/index/'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
                                array('label'=>'Marketing', 'url'=>array('/marketing/index/'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
                                array('label'=>'Cases', 'url'=>array('/cases/index/'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
                                array('label'=>'Accounts', 'url'=>array('/accounts/index/'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
                            
                                array('label'=>'Actions', 'url'=>array('/actions/admin'), 'visible'=>Yii::app()->user->getName()=='admin'),
				array('label'=>'Contacts', 'url'=>array('/contacts/admin'), 'visible'=>Yii::app()->user->getName()=='admin'),
				array('label'=>'Sales', 'url'=>array('/sales/admin'), 'visible'=>Yii::app()->user->getName()=='admin'),
				array('label'=>'Projects', 'url'=>array('/projects/admin/'), 'visible'=>Yii::app()->user->getName()=='admin'),
                                array('label'=>'Marketing', 'url'=>array('/marketing/admin/'), 'visible'=>Yii::app()->user->getName()=='admin'),
                                array('label'=>'Cases', 'url'=>array('/cases/admin/'), 'visible'=>Yii::app()->user->getName()=='admin'),
                                array('label'=>'Accounts', 'url'=>array('/accounts/admin/'), 'visible'=>Yii::app()->user->getName()=='admin'),
                            
                                array('label'=>'Profile', 'url'=>array('/profile/index/'), 'visible'=>(!Yii::app()->user->isGuest && Yii::app()->user->getName()!='admin')),
				array('label'=>'Users', 'url'=>array('/users/admin'), 'visible'=>Yii::app()->user->getName()=='admin'),
				array('label'=>'Login', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
				array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest),
                                array('label'=>'Admin','url'=>array('/admin/index/'),'visible'=>Yii::app()->user->getName()=='admin')
			),
		)); ?>
	</div><!-- mainmenu -->
	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<?php endif?>

	<?php echo $content; ?>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> by <?php echo CHtml::link('X2Engine Inc.','http://x2engine.com');?><br/>
		All Rights Reserved.<br/>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>