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
	alert('A new version is available.  To update X2Engine or to turn off these notifications, please go to the Admin tab.');
</script>

<?php
Yii::app()->session['alertUpdate']=false;
}

// jQuery and jQuery UI libraries
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerCoreScript('jquery.ui');

// custom scripts
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/mainmenu.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/backgroundImage.js');
if(isset(Yii::app()->params->profile) && Yii::app()->params->profile->enableBgFade == 1)
	Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/backgroundFade.js');

Yii::app()->clientScript->registerScript('setYiiBaseUrl',"var yiiBaseUrl='".Yii::app()->getBaseUrl()."';", CClientScript::POS_HEAD);

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/dragtable.css','screen, projection');
// Yii::app()->clientScript->registerCssFile($themeURL.'/css/jquery.contextMenu.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css','print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/details.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css','screen, projection');
Yii::app()->clientScript->registerScript('fullscreenToggle',"

var fullscreen = " . (Yii::app()->session['fullscreen']? 'true':'false') . ";

$(function() {
	$('#fullscreen-button').bind('click',function() { fullscreenToggle(); });
	
	if($('#content-box').hasClass('span-19'))
		return;

	if(fullscreen) {
		$('#sidebar-left-box').removeClass().addClass('span-0');
		$('#content-box').removeClass().addClass('span-24');
		$('#sidebar-right-box').removeClass().addClass('span-0');
	}
});

function fullscreenToggle() {
	if($('#content-box').hasClass('span-19'))
		return;

	$.ajax({
		url: yiiBaseUrl+'/site/fullscreen',
		type: 'GET',
		data: 'fs='+(fullscreen?'0':'1'),
		// success: function(response) {
			// if(response=='Success')
				// $('#history-'+actionId).fadeOut(200,function() { $('#history-'+actionId).remove(); });
			// }
	});
		
	if (fullscreen) {
		$('#sidebar-left-box').removeClass().addClass('span-4');
		$('#content-box').removeClass().addClass('span-15');
		$('#sidebar-right-box').removeClass().addClass('span-5 last');
	} else {
		$('#sidebar-left-box').removeClass().addClass('span-0');
		$('#content-box').removeClass().addClass('span-24');
		$('#sidebar-right-box').removeClass().addClass('span-0');
	}
	fullscreen = !fullscreen;
	
	$(window).trigger('resize');
}
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('savePageOpacity',"
function saveOpacity(e,ui) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('site/pageOpacity')) . "',
		type: 'GET',
		data: 'opacity='+ui.value
	});
}
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('checkImages',"
$(document).ready(function() {
	$('#main-menu-icon, #footer-logo, #footer-logo img').css({'display':'inline','visibility':'visible','z-index':'2147483647'});
});
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/notifications.js');
$notifUrl = $this->createUrl('site/checkNotifications');
Yii::app()->clientScript->registerScript('updateNotificationJs', "
        notifUrl='".$this->createUrl('site/checkNotifications')."'
	$(document).ready(updateNotifications());	//update on page load
",CClientScript::POS_HEAD); 



$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

// convert HEX color to RGB values
function hex2rgb($color) {
	if ($color[0] == '#')
		$color = substr($color, 1);

	if (strlen($color) == 6)
		list($r, $g, $b) = array($color[0].$color[1],
								 $color[2].$color[3],
								 $color[4].$color[5]);
	else if (strlen($color) == 3)
		list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	else
		return false;

	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array($r, $g, $b);
}

$checkResult = false;
$checkFiles = array(
	'themes/x2engine/images/x2footer.png'=>'78f7836c6c79e3c7a03667ba4320e637',
	'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) != $value)
		$checkResult = true;
}
$theme2Css = '';
if($checkResult)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('site/warning')).') !important;} #bg{display:none !important;}';
	
	if (!empty(Yii::app()->params->profile->pageOpacity)) {
		$defaultOpacity = Yii::app()->params->profile->pageOpacity / 100;
		Yii::app()->clientScript->registerScript('loadPageOpacity',"
			$(document).ready(function() {
				$('#page').fadeTo(0,".$defaultOpacity.");
			});
		",CClientScript::POS_HEAD);
	}
	
	// check for background image, use it if one is set
	if(empty(Yii::app()->params->profile->backgroundImg))
		$backgroundImg = CHtml::image('','',array('id'=>'bg','style'=>'display:none;'));
	else
		$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/'.Yii::app()->params->profile->backgroundImg,'',array('id'=>'bg'));

	// check for background image
	if(!empty(Yii::app()->params->profile->backgroundColor)) {
		$themeCss .= 'body {background-color:#'.Yii::app()->params->profile->backgroundColor.";}\n";
		
		if(!empty($backgroundImg)) {
			$shadowRgb = 'rgb(0,0,0,0.5)';	// use a black shadow if there is an image
		} else {
			$shadowColor = hex2rgb(Yii::app()->params->profile->backgroundColor);	// if there is no BG image, calculate a darker tone for the shadow
			
			foreach($shadowColor as &$value) {
				$value = floor(0.5*$value);
			}
			$shadowRgb = 'rgb('.implode(',',$shadowColor).')';
		}
		$themeCss .= "#page {
-moz-box-shadow: 0 0 30px $shadowRgb;
-webkit-box-shadow: 0 0 30px $shadowRgb;
box-shadow: 0 0 30px $shadowRgb;
}\n";
	}
	if(!empty(Yii::app()->params->profile->menuBgColor)){
		$themeCss .= '#main-menu-bar {background:#'.Yii::app()->params->profile->menuBgColor.";}\n";
        }
	if(!empty(Yii::app()->params->profile->menuTextColor)){
		$themeCss .= '#main-menu-bar ul a, #main-menu-bar ul span {color:#'.Yii::app()->params->profile->menuTextColor.";}\n";
        }
	
	Yii::app()->clientScript->registerCss('applyTheme',$themeCss,'screen',CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCss('applyTheme2',$theme2Css,'screen',CClientScript::POS_HEAD);

$admin=Admin::model()->findByPk(1);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$module = Yii::app()->controller->id;

if($isGuest) {
	$menuItems = array(
		array('label'=>Yii::t('app','Login'), 'url'=>array('/site/login'))
	);
} else {
	// $admin=Admin::model()->findByPk(1);
	
	$nicknames = explode(":",$admin->menuNicknames);
	$menuOrder = explode(":",$admin->menuOrder);
	$menuVis = explode(":",$admin->menuVisibility);
	
	$standardMenuItems = array();		// hash array with correct order, containing realName => nickName
	
	for($i=0;$i<count($menuOrder);$i++) {
		if($menuVis[$i] == 1)
			$standardMenuItems[$menuOrder[$i]] = mb_ereg_replace('&#58;',':',$nicknames[$i]);	// only include visible items
	}

	$menuItems = array();
	
	$defaultAction = $isAdmin? 'admin' : 'index';
	
	foreach($standardMenuItems as $key=>$value) {
		$file=Yii::app()->file->set('protected/controllers/'.ucfirst($key).'Controller.php');
		if($file->exists)
			$menuItems[$key] = array('label'=>Yii::t('app', $value),'url'=>array("/$key/$defaultAction"), 'active'=>(strtolower($module)==strtolower($key))? true : null);
		else {
			$page=DocChild::model()->findByAttributes(array('title'=>ucfirst(mb_ereg_replace('&#58;',':',$key))));
			if(isset($page)){
				$id=$page->id;
				$menuItems[$key] = array('label' =>ucfirst($value),		'url' => array('/admin/viewPage/'.$id),		'active'=>Yii::app()->request->requestUri==Yii::app()->request->baseUrl.'/index.php/admin/viewPage/'.$id?true:null);
			}
		}
	}
	if($isAdmin) {
		$menuItems['users'] = array('label'=>Yii::t('app','Users'), 'url'=>array('/users/admin'), 'active'=>($module=='users')? true : null);
	}
}

$maxMenuItems = 5;
//check if menu has too many items to fit nicely
$menuItemCount = count($menuItems);
if ($menuItemCount > $maxMenuItems) {
	$moreMenuItems = array();
	//move the last few menu items into the "More" dropdown
	for ($i = 0; $i<$menuItemCount-($maxMenuItems-1); $i++) {
		array_unshift($moreMenuItems, array_pop($menuItems));
	}
	//add "More" to main menu
	$menuItems[] = array('label'=>Yii::t('app','More'),'items'=>$moreMenuItems);
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
			array('label' => Yii::t('app','Logout'),'url' => array('/site/logout'))
		)
	),
);
	
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<?php 
        $session=Sessions::model()->findByAttributes(array('user'=>Yii::app()->user->getName()));
        if(isset($session)){
            if(time()-$session->lastUpdated>$admin->timeout){
                $session->delete();
                $this->redirect(Yii::app()->controller->createUrl('site/logout'));
            }else{
                $session->lastUpdated=time();
                $session->save();
            }
        }elseif(!Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->controller->createUrl('site/logout'));
        }
?>
<link rel="icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon0" />
<link rel="shortcut-icon" href="<?php echo Yii::app()->getBaseUrl(); ?>/images/favicon.ico" type="image/x-icon" />
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ie.css" media="screen, projection" />
<![endif]-->
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body id="body-tag">
<?php echo $backgroundImg; ?>
<!--<div class="ie-shadow" style="display:none;"></div>-->
<div class="container" id="page">
	<div id="main-menu-bar">
                <?php
                    $notifications=CActiveRecord::model('NotificationChild')->findAllByAttributes(array('user'=>Yii::app()->user->getName(),'viewed'=>0))
                ?>
                <?php echo CHtml::link(count($notifications),array('site/viewNotifications'),array('id'=>'main-menu-notif','style'=>'z-index:999;display:none;'));
                      echo CHtml::link('',array('site/page','view'=>'about'),array('id'=>'main-menu-icon')) ?>
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
	<?php if (!$isGuest) {	//only render searchbar if logged in
	?>
	<div id="search-bar">
		<form name="search" action="<?php echo $this->createUrl('search/search');?>" method="get">
			<span id="search-bar-title"><?php echo '<a href="'.Yii::app()->request->baseUrl.'/index.php/site/whatsNew"><img height="30" width="200" src='.Yii::app()->request->baseUrl.'/'.Yii::app()->params->logo.'></a>'; ?></span>
			<input type="text" class="text" id="search-bar-box" name="term" value="<?php echo Yii::t('app','Search for contact, action, deal...'); ?>" onFocus="toggleText(this);" onBlur="toggleText(this);" />
			<a class="x2-button" href="#" onClick="submitForm('search');"><span><?php echo Yii::t('app','Go'); ?></span></a>
		</form>
		<div id="transparency-slider-box" style="position:absolute;height:90px;padding:20px 10px;background:white;display:none;">
		<?php
		$this->widget('zii.widgets.jui.CJuiSlider', array(
			'value'=>$defaultOpacity,
			// additional javascript options for the slider plugin
			'options'=>array(
				'orientation'=>'vertical',
				'min'=>0.2,
				'max'=>1.0,
				'step'=>0.05,
				'slide'=>"js:function(event,ui) {
					$('#page').fadeTo(0,ui.value);
					resetSliderTimeout();
				}",
				'change'=>"js:function(event,ui) {saveOpacity(event,ui);}"
			),
			'htmlOptions'=>array(
				'id'=>'transparency-slider',
				//'style'=>'position:absolute;height:90px;display:none;'
			),
		));?></div><?php
		echo ' '.CHtml::link('<span>&nbsp;</span>','#',array('class'=>'x2-button','id'=>'fullscreen-button'))." \n";
		echo ' '.CHtml::link('<span>&nbsp;</span>','#',array('class'=>'x2-button','id'=>'transparency-button'))." \n";
		echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Contact').'</span>',array('contacts/create'),array('class'=>'x2-button'))." \n";
		echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Action').'</span>',array('actions/create','param'=>Yii::app()->user->getName().';none:0'),array('class'=>'x2-button'))." \n";
		echo ' '.CHtml::link('<span class="add-button">'.Yii::t('app','Contact + Action').'</span>',array('actions/quickCreate'),array('class'=>'x2-button'))." \n";
		?>
	</div>
	<?php
	}
	echo $content;
	?>
	<div id="footer">
		<hr><div id="footer-logos">
		<?php $imghtml = CHtml::image($themeURL.'/images/x2touch.png','');
		echo CHtml::link($imghtml,Yii::app()->getBaseUrl().'/index.php/x2touch'); ?></div>
		Copyright &copy; <?php echo date('Y').' '.CHtml::link('X2Engine Inc.','http://www.x2engine.com');?>
		<?php echo Yii::t('app','Rights reservered.'); ?>
		<?php
		echo Yii::t('app','The Program is provided AS IS, without warranty.<br />Licensed under {BSD}.',
		array(
			'{BSD}'=>CHtml::link('BSD License',Yii::app()->getBaseUrl().'/LICENSE.txt'),
			'{GPLv3long}'=>CHtml::link(Yii::t('app','GNU General Public License version 3'),Yii::app()->getBaseUrl().'/GPL-3.0 License.txt')
		));?><br />
		<?php echo Yii::t('app','Generated in {time} seconds',array('{time}'=>round(CLogger::getExecutionTime(),3)));
		?><br /><?php
		$imghtml = CHtml::image($themeURL.'/images/x2footer.png','');
		echo CHtml::link($imghtml,array('site/page','view'=>'about')); // Yii::app()->request->baseURL.'/index.php');
		
		?>
	</div>
</div>
</body>
</html>