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
 
$isGuest = Yii::app()->user->isGuest;
$isAdmin = !$isGuest && Yii::app()->user->getName()=='admin';
$isUser = !($isGuest || $isAdmin);
if(Yii::app()->session['alertUpdate']){
?><script>
	alert(<?php echo addslashes(Yii::t('admin','A new version is available.  To update X2Engine or to turn off these notifications, please go to the Admin tab.')); ?>);
</script>

<?php
Yii::app()->session['alertUpdate']=false;
}

// jQuery and jQuery UI libraries
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerCoreScript('jquery.ui');

// custom scripts
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/layout.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/LGPL/jquery.formatCurrency-1.4.0.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/LGPL/jquery.formatCurrency.all.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/tinyeditor.js');
// Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/backgroundImage.js');

Yii::app()->clientScript->registerScript('setYiiBaseUrl',"var yiiBaseUrl='".Yii::app()->getBaseUrl()."';", CClientScript::POS_HEAD);

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/dragtable.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css','print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/layout2.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/details.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/x2forms.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/tinyeditor.css','screen, projection');
Yii::app()->clientScript->registerScript('fullscreenToggle',"

window.fullscreen = " . (Yii::app()->session['fullscreen']? 'true':'false') . ";
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('checkImages',"
$(document).ready(function() {
	$('#main-menu-icon, #footer-logo, #footer-logo img').css({'display':'inline','visibility':'visible','z-index':'2147483647'});
           
});
",CClientScript::POS_END);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/notifications.js');
if($this->getModule()!='mobile'){
$notifUrl = $this->createUrl('/site/checkNotifications');
Yii::app()->clientScript->registerScript('updateNotificationJs', "
        notifUrl='".$this->createUrl('/site/checkNotifications')."'
	$(document).ready(updateNotifications());	//update on page load
",CClientScript::POS_HEAD); 
}



$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

$checkResult = false;
$checkFiles = array(
	'themes/x2engine/images/x2footer.png'=>'1393e4af54ababdcf76fac7f075b555b',
	'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) != $value)
		$checkResult = true;
}
$theme2Css = '';
if($checkResult)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('site/warning')).') !important;} #bg{display:none !important;}';


// if(!empty(Yii::app()->params->profile->backgroundColor))
	// $themeCss .= 'body {background-color:#'.Yii::app()->params->profile->backgroundColor.";}\n";

// check for background image, use it if one is set
if(empty(Yii::app()->params->profile->backgroundImg))
	$backgroundImg = CHtml::image('','',array('id'=>'bg','style'=>'display:none;'));
else
	$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/'.Yii::app()->params->profile->backgroundImg,'',array('id'=>'bg'));

if(!empty(Yii::app()->params->profile->menuBgColor))
	$themeCss .= '#main-menu-bar {background:#'.Yii::app()->params->profile->menuBgColor.";}\n";

if(!empty(Yii::app()->params->profile->menuTextColor))
	$themeCss .= '#main-menu-bar ul a, #main-menu-bar ul span {color:#'.Yii::app()->params->profile->menuTextColor.";}\n";


Yii::app()->clientScript->registerCss('applyTheme',$themeCss,'screen',CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCss('applyTheme2',$theme2Css,'screen',CClientScript::POS_HEAD);

// $admin=Admin::model()->findByPk(1);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$module = Yii::app()->controller->id;

if($isGuest) {
	$menuItems = array(
		array('label'=>Yii::t('app','Login'), 'url'=>array('/site/login'))
	);
} else {
	// $admin=Admin::model()->findByPk(1);

	$modules = Modules::model()->findAll(array('condition'=>'visible="1"','order'=>'menuPosition ASC'));
	$standardMenuItems = array();	
	foreach($modules as $moduleItem){
		if($isAdmin || $moduleItem->adminOnly==0)
			$standardMenuItems[$moduleItem->name]=$moduleItem->title;
	}
	

	$menuItems = array();
	
	$defaultAction = $isAdmin? 'admin' : 'index';
	
	foreach($standardMenuItems as $key=>$value) {
		$file=Yii::app()->file->set('protected/controllers/'.ucfirst($key).'Controller.php');
		if($file->exists)
			$menuItems[$key] = array('label'=>Yii::t('app', $value),'url'=>array("/$key/$defaultAction"), 'active'=>(strtolower($module)==strtolower($key))? true : null);
		elseif(is_dir('protected/modules/'.$key)) {
			if(!is_null($this->getModule()))
				$module=$this->getModule()->id;
			$menuItems[$key] = array('label'=>Yii::t('app', $value),'url'=>array("/$key/$defaultAction"), 'active'=>(strtolower($module)==strtolower($key))? true : null);
		} else {
			$page=DocChild::model()->findByAttributes(array('title'=>ucfirst(mb_ereg_replace('&#58;',':',$value))));
			if(isset($page)){
				$id=$page->id;
				$menuItems[$key] = array('label' =>ucfirst($value),		'url' => array('/admin/viewPage/'.$id),		'active'=>Yii::app()->request->requestUri==Yii::app()->request->baseUrl.'/index.php/admin/viewPage/'.$id?true:null);
			}
		}
	}
	
}

$maxMenuItems = 4;
//check if menu has too many items to fit nicely
$menuItemCount = count($menuItems);
if ($menuItemCount > $maxMenuItems) {
	$moreMenuItems = array();
	//move the last few menu items into the "More" dropdown
	for ($i = 0; $i<$menuItemCount-($maxMenuItems-1); $i++) {
		array_unshift($moreMenuItems, array_pop($menuItems));
	}
	//add "More" to main menu
	$menuItems[] = array('label'=>Yii::t('app','More'),'items'=>$moreMenuItems,'itemOptions'=>array('id'=>'more-menu'));
}

$userMenu = array(
	array('label' => Yii::t('app','Chat'), 'url' => array('/site/groupChat')),
	array('label' => Yii::t('app','Social'),'url' => array('/profile/index')),
	array('label' => Yii::t('app','Admin'), 'url' => array('/admin/index'),'active'=>($module=='admin'&&Yii::app()->controller->action->id!='viewPage')?true:null, 'visible'=>$isAdmin),
	array('label' => Yii::t('app','Logout'),'url' => array('/site/logout'), 'visible'=>$isAdmin),
	// array('label' => CHtml::button(Yii::app()->user->getName(),array('id'=>'user-menu-toggle','onclick'=>'','class'=>'x2-button')), 'visible'=>$isUser,
	array('label' => Yii::t('app','Profile').' ('.Yii::app()->user->getName().')', 'visible'=>$isUser,
		'items' => array(
			array('label' => Yii::t('app','Profile'),'url' => array('/profile/view','id' => Yii::app()->user->getId()), 'visible'=>$isUser),
                        array('label' => Yii::t('app','Notifications'),'url' => array('/site/viewNotifications'), 'visible'=>$isUser),
			array('label' => Yii::t('app','Settings'),'url' => array('/profile/settings'), 'visible'=>$isUser),
                        array('label' => Yii::t('app','Help'),'url' => 'http://www.x2engine.com/screen-shots-2', 'visible'=>$isUser, 'linkOptions'=>array('target'=>'_blank')),
			array('label' => Yii::t('app','Logout'),'url' => array('/site/logout'))
		)
	),
);
	
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8">
<link rel="icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon">
<link rel="shortcut-icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon">
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ie.css" media="screen, projection">
<![endif]-->
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>

<div id="header">
	<div id="main-menu-bar">
		<div class="width-constraint">
			<?php
				$notifications = CActiveRecord::model('NotificationChild')->countByAttributes(array('user'=>Yii::app()->user->getName(),'viewed'=>0));
				echo CHtml::link($notifications,array('/site/viewNotifications'),array('id'=>'main-menu-notif','style'=>'z-index:999;display:none;'));
				echo CHtml::link('',array('/site/page','view'=>'about'),array('id'=>'main-menu-icon')) ?>
			<?php
			//render main menu items
			$this->widget('zii.widgets.CMenu', array(
				'id'=>'main-menu',
				'htmlOptions'=>array('class'=>'main-menu'),
				'items'=>$menuItems
			));
			//render user menu items if logged in
			if (!$isGuest) {
				// echo CHtml::button(Yii::app()->user->getName(),array('id'=>'user-menu-toggle','onclick'=>'','class'=>'x2-button float'));
				$this->widget('zii.widgets.CMenu', array(
					'id' => 'user-menu',
					'items' => $userMenu,
					'encodeLabel' => false
				));
			}
			
		?>
		</div>
	</div>
	<?php
	if (!$isGuest) {	//only render searchbar if logged in
		if(!empty($this->menu)) {
	?>
	<div id="submenu-bar">
		<div class="width-constraint">
			<?php $this->widget('zii.widgets.CMenu',array('items'=>$this->menu)); ?>
		</div>
	</div>
	<?php } ?>
	<div id="search-bar">
		<div class="width-constraint">
			<form name="search" action="<?php echo $this->createUrl('/search/search');?>" method="get">
				<span id="search-bar-title"><?php echo '<a href="'.Yii::app()->request->baseUrl.'/index.php/site/whatsNew"><img height="30" width="200" src='.Yii::app()->request->baseUrl.'/'.Yii::app()->params->logo.'></a>'; ?></span>
				<input type="text" class="text" id="search-bar-box" name="term" value="<?php echo Yii::t('app','Search for contact, action, deal...'); ?>" onFocus="toggleText(this);" onBlur="toggleText(this);">
				<a class="x2-button" href="#" onClick="submitForm('search');"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</form>
			<?php
			echo ' '.CHtml::link('<span>&nbsp;</span>','#',array('class'=>'x2-button','id'=>'fullscreen-button'))." \n";
			echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Contact').'</span>',array('/contacts/create'),array('class'=>'x2-button'))." \n";
			echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Action').'</span>',array('/actions/default/create','param'=>Yii::app()->user->getName().';none:0'),array('class'=>'x2-button'))." \n";
			// echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Contact + Action').'</span>',array('actions/quickCreate'),array('class'=>'x2-button'))." \n";
			?>
		</div>
	</div>
	<?php } ?>
</div>
<div class="width-constraint" id="page-body">
<?php echo $content; ?>
</div>
<div id="footer">
	<div id="footer-logos">
		<a href="<?php echo Yii::app()->getBaseUrl().'/index.php/x2touch'; ?>">
			<?php echo CHtml::image($themeURL.'/images/x2touch.png','',array('id'=>'x2touch-logo')); ?></a>
		<a href="<?php echo CHtml::normalizeUrl(array('site/page','view'=>'about')); ?>">
			<?php echo CHtml::image($themeURL.'/images/x2footer.png','', array('id'=>'x2crm-logo')); ?></a>
	</div>
	Copyright &copy; <?php echo date('Y').' '.CHtml::link('X2Engine Inc.','http://www.x2engine.com');?>
	<?php echo Yii::t('app','Rights reserved.'); ?>
	<?php
	echo Yii::t('app','The Program is provided AS IS, without warranty.<br>Licensed under {BSD}.',
	array(
		'{BSD}'=>CHtml::link('BSD License',Yii::app()->getBaseUrl().'/LICENSE.txt'),
		'{GPLv3long}'=>CHtml::link(Yii::t('app','GNU General Public License version 3'),Yii::app()->getBaseUrl().'/GPL-3.0 License.txt')
	));?><br>
	<?php echo Yii::t('app','Generated in {time} seconds',array('{time}'=>number_format(Yii::getLogger()->getExecutionTime(),3)));
	?><br>
	<?php
	Yii::app()->clientScript->registerScript('logos',"
	$(window).load(function(){
		if((!$('#main-menu-icon').length) || (!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
			$('a').removeAttr('href');
			alert('Please put the logo back');
			window.location='http://www.x2engine.com';
		}
		var touchlogosrc = $('#x2touch-logo').attr('src');
		var logosrc=$('#x2crm-logo').attr('src');
		if(logosrc!='$themeURL/images/x2footer.png'|| touchlogosrc!='$themeURL/images/x2touch.png'){
			$('a').removeAttr('href');
			alert('Please put the logo back');
			window.location='http://www.x2engine.com';
	}
	});
	");
	 ?><br>
</div>

</body>
</html>
