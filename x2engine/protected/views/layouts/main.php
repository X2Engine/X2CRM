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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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