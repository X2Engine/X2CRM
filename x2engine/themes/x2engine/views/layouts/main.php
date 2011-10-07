<?php
/*********************************************************************************
 * X2Engine is a customer relationship management program developed by
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
 
$isGuest = Yii::app()->user->isGuest;
$isAdmin = !$isGuest && Yii::app()->user->getName()=='admin';
$isUser = !($isGuest || $isAdmin);

// jQuery and jQuery UI libraries
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerCoreScript('jquery.ui');

// custom scripts
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/mainmenu.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/backgroundImage.js');

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css','screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css','print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css','screen');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/details.css','screen');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css','screen');

Yii::app()->clientScript->registerScript('savePageOpacity',"
function saveOpacity(e,ui) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('site/pageOpacity')) . "',
		type: 'GET',
		data:  'opacity='+ui.value
	});
}
",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('checkImages',"
$(document).ready(function() {
	$('#main-menu-icon, #footer-logo, #footer-logo img').css({'display':'inline','visibility':'visible','z-index':'2147483647'});
});
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
	'themes/x2engine/images/x2footer.png'=>'42a673ec030ac4030f4624fa93318812',
	'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) != $value)
		$checkResult = true;
}
$theme2Css = '';
if($checkResult)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('site/warning')).') !important;} #bg{display:none !important;}';
	// get user record
	$userModel = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());
        if(!isset($userModel))
            $userModel=ProfileChild::model()->findByPk(1);
	
	if (!empty($userModel->pageOpacity)) {
		$defaultOpacity = $userModel->pageOpacity / 100;
		Yii::app()->clientScript->registerScript('loadPageOpacity',"
			$(document).ready(function() {
				$('#page').fadeTo(0,".$defaultOpacity.");
			});
		",CClientScript::POS_HEAD);
	}
	
	// check for background image, use it if one is set
	if(empty($userModel->backgroundImg))
		$backgroundImg = CHtml::image('','',array('id'=>'bg','style'=>'display:none;'));
	else
		$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/'.$userModel->backgroundImg,'',array('id'=>'bg'));

	// check for background image
	if(!empty($userModel->backgroundColor)) {
		$themeCss .= 'body {background-color:#'.$userModel->backgroundColor.";}\n";
		
		if(!empty($backgroundImg)) {
			$shadowRgb = 'rgb(0,0,0,0.5)';	// use a black shadow if there is an image
		} else {
			$shadowColor = hex2rgb($userModel->backgroundColor);	// if there is no BG image, calculate a darker tone for the shadow
			
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
	if(!empty($userModel->menuBgColor)){
		$themeCss .= '#main-menu-bar {background:#'.$userModel->menuBgColor.";}\n";
        }
	if(!empty($userModel->menuTextColor)){
		$themeCss .= '#main-menu-bar ul a, #main-menu-bar ul span {color:#'.$userModel->menuTextColor.";}\n";
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
	if($isAdmin)
		$menuItems['users'] = array('label'=>Yii::t('app','Users'), 'url'=>array('/users/admin'), 'active'=>($module=='users')? true : null);
}

$maxMenuItems = 6;
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
	array('label' => Yii::t('app','Group Chat'), 'url' => array('/site/groupChat')),
	array('label' => Yii::t('app','Social'),'url' => array('/profile/index')),
	array('label' => Yii::t('app','Admin'), 'url' => array('/admin/index'),'active'=>($module=='admin'&&Yii::app()->controller->action->id!='viewPage')?true:null, 'visible'=>$isAdmin),
	array('label' => Yii::t('app','Logout'),'url' => array('/site/logout'), 'visible'=>$isAdmin),
	// array('label' => CHtml::button(Yii::app()->user->getName(),array('id'=>'user-menu-toggle','onclick'=>'','class'=>'x2-button')), 'visible'=>$isUser,
	array('label' => Yii::t('app','Profile').' ('.Yii::app()->user->getName().')', 'visible'=>$isUser,
		'items' => array(
			array('label' => Yii::t('app','Profile'),'url' => array('/profile/view','id' => Yii::app()->user->getId()), 'visible'=>$isUser),
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
	if(Yii::app()->session['loginTime']<time()-$admin->timeout){
            if(!Yii::app()->user->isGuest){
                Yii::app()->user->logout();
                $this->redirect(Yii::app()->controller->createUrl('site/login'));
            }
            
     
	}else{
		Yii::app()->session['loginTime']=time();
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
		<?php echo CHtml::link('',array('site/page','view'=>'about'),array('id'=>'main-menu-icon')); ?>
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
		<?php
		$imghtml = CHtml::image($themeURL.'/images/x2footer.png','');
		echo CHtml::link($imghtml,array('site/page','view'=>'about')); // Yii::app()->request->baseURL.'/index.php');
		
		?></div>
		Copyright &copy; <?php echo date('Y').' '.CHtml::link('X2Engine Inc.','http://www.x2engine.com');?>
		<?php echo Yii::t('app','Rights reservered.'); ?>
		<?php
		echo Yii::t('app','The Program is provided AS IS, without warranty.<br />Licensed under {GPLv3}. This program is free software; you can redistribute it and/or modify it<br />under the terms of the {GPLv3long} as published by the Free Software Foundation<br />including the additional permission set forth in the source code header.',
		array(
			'{GPLv3}'=>CHtml::link('GPLv3',Yii::app()->getBaseUrl().'/GPL-3.0 License.txt'),
			'{GPLv3long}'=>CHtml::link(Yii::t('app','GNU General Public License version 3'),Yii::app()->getBaseUrl().'/GPL-3.0 License.txt')
		));?><br />
		<?php echo Yii::t('app','Generated in {time} seconds',array('{time}'=>round(CLogger::getExecutionTime(),3)));
		?><br />
		<?php $imghtml = CHtml::image($themeURL.'/images/x2touch.png','');
		echo CHtml::link($imghtml,Yii::app()->getBaseUrl().'/index.php/x2touch'); ?>
	</div>
</div>
</body>
</html>