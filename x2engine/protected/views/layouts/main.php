<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$isGuest = Yii::app()->user->isGuest;
$auth=Yii::app()->authManager;
$isAdmin = !$isGuest && (Yii::app()->user->checkAccess('AdminIndex'));
$isUser = !($isGuest || $isAdmin);
if(Yii::app()->session['alertUpdate']){
?><script>
	alert('<?php echo addslashes(Yii::t('admin','A new version is available.  To update X2Engine or to turn off these notifications, please go to the Admin tab.')); ?>');
</script>
<?php
Yii::app()->session['alertUpdate']=false;
}

$baseUrl = Yii::app()->getBaseUrl();
$scriptUrl = Yii::app()->request->scriptUrl;
$themeUrl = Yii::app()->theme->getBaseUrl();

$cs = Yii::app()->clientScript;
$jsVersion = '?'.Yii::app()->params->buildDate;

// jQuery and jQuery UI libraries
$cs ->registerCoreScript('jquery')
	->registerCoreScript('jquery.ui');

// custom scripts
$cs ->registerScriptFile($baseUrl.'/js/json2.js')
	->registerScriptFile($baseUrl.'/js/layout.js')
	->registerScriptFile($baseUrl.'/js/publisher.js')
	->registerScriptFile($baseUrl.'/js/media.js')
	->registerScriptFile($baseUrl.'/js/x2forms.js')
    ->registerScriptFile($baseUrl.'/js/tags.js')
	->registerScriptFile($baseUrl.'/js/LGPL/jquery.formatCurrency-1.4.0.js'.$jsVersion)
	->registerScriptFile($baseUrl.'/js/LGPL/jquery.formatCurrency.all.js'.$jsVersion)
	->registerScriptFile($baseUrl.'/js/modernizr.custom.66175.js')
	->registerScriptFile($baseUrl.'/js/relationships.js')
	->registerScriptFile($baseUrl.'/js/widgets.js')
	->registerScriptFile($baseUrl.'/js/qtip/jquery.qtip.min.js'.$jsVersion)
    ->registerScriptFile($baseUrl.'/js/actionFrames.js'.$jsVersion);


if(Yii::app()->session['translate'])
	$cs->registerScriptFile($baseUrl.'/js/translator.js');

$cs ->registerScriptFile($baseUrl.'/js/backgroundFade.js');
$cs->registerScript('datepickerLanguage',"
    $.datepicker.setDefaults( $.datepicker.regional[ '' ] );
");
// $cs ->registerScriptFile($baseUrl.'/js/backgroundImage.js');

// MoneyMask extension:
$mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
$aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
Yii::app()->getClientScript()->registerScriptFile("$aMmPath/jquery.maskMoney.js");
Yii::app()->clientScript->registerCoreScript('jquery');

// blueprint CSS framework
$cs ->registerCssFile($themeUrl.'/css/screen.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/jquery-ui.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/dragtable.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/print.css'.$jsVersion,'print')
	->registerCssFile($themeUrl.'/css/main.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/layout.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/details.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/x2forms.css'.$jsVersion,'screen, projection')
	->registerCssFile($themeUrl.'/css/form.css'.$jsVersion,'screen, projection')
	->registerCssFile($baseUrl.'/js/qtip/jquery.qtip.min.css'.$jsVersion,'screen, projection');
// $cs->registerCssFile($cs->getCoreScriptUrl().'/jui/css/base/jquery-ui.css'.$jsVersion);

$cs->registerScript('fullscreenToggle','
window.enableFullWidth = ' . (!Yii::app()->user->isGuest?(Yii::app()->params->profile->enableFullWidth? 'true':'false'):'true') . ';
window.fullscreen = ' . (Yii::app()->session['fullscreen']? 'true':'false') . ';
',CClientScript::POS_HEAD);

$cs ->registerScriptFile($baseUrl.'/js/jstorage.min.js'.$jsVersion)
	->registerScriptFile($baseUrl.'/js/notifications.js'.$jsVersion);

if(Yii::app()->params->profile->language=='he' || Yii::app()->params->profile->language=='fa')
	$cs->registerCss('rtl-language','body{text-align:right;}');

$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

$logoMissing = false;
$checkFiles = array(
	// 'themes/x2engine/images/x2footer.png'=>'1393e4af54ababdcf76fac7f075b555b',
	// 'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
	'images/powered_by_x2engine.png'=>'b7374cbbd29cd63191f7e0b1dcd83c48',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) !== $value)
		$logoMissing = true;
}
$theme2Css = '';
if($logoMissing)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('site/warning')).') !important;} #bg{display:none !important;}';

// check for background image, use it if one is set
// if(empty(Yii::app()->params->profile->backgroundImg))
	// $backgroundImg = CHtml::image('','',array('id'=>'bg','style'=>'display:none;'));
// else
	// $backgroundImg = CHtml::image($baseUrl.'/uploads/'.Yii::app()->params->profile->backgroundImg,'',array('id'=>'bg'));


$themeCss = '';
if(!empty(Yii::app()->params->profile->menuTextColor))
	$themeCss .= 'ul.main-menu > li > a, ul.main-menu > li > span {color:#'.Yii::app()->params->profile->menuTextColor.";}\n";
if(!empty(Yii::app()->params->profile->pageHeaderBgColor))
	$themeCss .= 'div.page-title {background-color:#'.Yii::app()->params->profile->pageHeaderBgColor.";}\n";
if(!empty(Yii::app()->params->profile->pageHeaderTextColor))
	$themeCss .= 'div.page-title, div.page-title h2 {color:#'.Yii::app()->params->profile->pageHeaderTextColor.";}\n";

if(!empty(Yii::app()->params->profile->activityFeedWidgetBgColor)) {
	$themeCss .= '#chat-box {
                        background:#'.Yii::app()->params->profile->activityFeedWidgetBgColor.';
                        color:'.convertTextColor(Yii::app()->params->profile->activityFeedWidgetBgColor.'', 'standardText').';
                     }
                     #chat-box a:link     { color: ' . convertTextColor(Yii::app()->params->profile->activityFeedWidgetBgColor.'', 'linkText') . '; }
                     #chat-box a:visited  { color: ' . convertTextColor(Yii::app()->params->profile->activityFeedWidgetBgColor.'', 'visitedLinkText') . '; }
                     #chat-box a:active   { color: ' . convertTextColor(Yii::app()->params->profile->activityFeedWidgetBgColor.'', 'activeLinkText') . '; }
                     #chat-box a:hover    { color: ' . convertTextColor(Yii::app()->params->profile->activityFeedWidgetBgColor.'', 'hoverLinkText') . '; }
                     ';
}

// Outputs white or black depending on input color
// @param $colorString a string representing a hex number
// @param $testType standardText or linkText
function convertTextColor($colorString, $textType){
    // Split the string to red, green and blue components
    // Convert hex strings into ints
    $red   = intval(substr($colorString, 0, 2), 16);
    $green = intval(substr($colorString, 2, 2), 16);
    $blue  = intval(substr($colorString, 4, 2), 16);
    if($textType == 'standardText') {
        return (((($red*299)+($green*587)+($blue*114))/1000) >= 128) ? 'black' : 'white';
    }
    else if ($textType == 'linkText') {
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
            return '#fff000';  // Yellow links
        }
        else return '#0645AD'; // Blue link color
    }
    else if ($textType == 'visitedLinkText') {
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
            return '#ede100';  // Yellow links
        }
        else return '#0B0080'; // Blue link color
    }
    else if ($textType == 'activeLinkText') {
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
            return '#fff000';  // Yellow links
        }
        else return '#0645AD'; // Blue link color
    }
    else if ($textType == 'hoverLinkText') {
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))) {
            return '#fff761';  // Yellow links
        }
        else return '#3366BB'; // Blue link color
    }
}

// Check if any element of a triple is significantly less than the other two
// based on a defined value
function isSignificantlyLess($x, $y, $z, $howMuch){
    if(($x > ($z + $howMuch)) && ($y > ($z + $howMuch))) return true;
    if(($x > ($y + $howMuch)) && ($z > ($y + $howMuch))) return true;
    if(($y > ($x + $howMuch)) && ($z > ($x + $howMuch))) return true;
    return false;
}
$cs->registerCss('applyTheme',$themeCss,'screen',CClientScript::POS_HEAD);
$cs->registerCss('applyTheme2',$theme2Css,'screen',CClientScript::POS_HEAD);

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
	foreach($modules as $moduleItem) {
		if(($isAdmin || $moduleItem->adminOnly==0) && $moduleItem->name != 'users') {
			if($moduleItem->name!='document')
				$standardMenuItems[$moduleItem->name]=$moduleItem->title;
			else
				$standardMenuItems[$moduleItem->title]=$moduleItem->title;
		}
	}
	$menuItems = array();

	$defaultAction = 'index';
    //$isAdmin? 'admin' : 'index';

	foreach($standardMenuItems as $key=>$value) {
		$file=Yii::app()->file->set('protected/controllers/'.ucfirst($key).'Controller.php');
        $action=ucfirst($key).ucfirst($defaultAction);
        $authItem=$auth->getAuthItem($action);
        $permission=Yii::app()->user->checkAccess($action) || is_null($authItem);
		if($file->exists){
            if($permission)
                $menuItems[$key] = array('label'=>Yii::t('app', $value),'url'=>array("/$key/$defaultAction"), 'active'=>(strtolower($module)==strtolower($key))? true : null);
        }elseif(is_dir('protected/modules/'.$key)) {
			if(!is_null($this->getModule()))
				$module=$this->getModule()->id;
            if($permission)
                $menuItems[$key] = array('label'=>Yii::t('app', $value),'url'=>array("/$key/$defaultAction"), 'active'=>(strtolower($module)==strtolower($key) && (!isset($_GET['static']) || $_GET['static']!='true'))? true : null);
		} else {
			$page = Docs::model()->findByAttributes(array('name'=>ucfirst(mb_ereg_replace('&#58;',':',$value))));
			if(isset($page)){
				$id=$page->id;
				$menuItems[$key] = array('label' =>ucfirst($value),'url' => array('/docs/'.$id.'?static=true'),'active'=>Yii::app()->request->requestUri==$scriptUrl.'/docs/'.$id.'?static=true'?true:null);
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
	$menuItems[] = array('label'=>Yii::t('app','More'),'items'=>$moreMenuItems,'itemOptions'=>array('id'=>'more-menu','class'=>'dropdown'));
}

// find out the dimensions of the user-uploaded logo so the menu can do its layout calculations
$logoOptions = array();
if(is_file(Yii::app()->params->logo)) {
	$logoSize = @getimagesize(Yii::app()->params->logo);
	if($logoSize)
		$logoSize = array(min($logoSize[0],200),min($logoSize[1],30));
	else
		$logoSize = array(92,30);

	$logoOptions['width'] = $logoSize[0];
	$logoOptions['height'] = $logoSize[1];
}
array_unshift($menuItems,array(
	'label'=>CHtml::image(Yii::app()->request->baseUrl.'/'.Yii::app()->params->logo,Yii::app()->name,$logoOptions),
	'url'=>array('/site/whatsNew'),
	'active'=>false,
	'itemOptions'=>array('id'=>'search-bar-title','class'=>'special')
));


$notifCount = X2Model::model('Notification')->countByAttributes(array('user'=>Yii::app()->user->getName()),'createDate < '.time());

$searchbarHtml = CHtml::beginForm(array('/search/search'),'get')
	.'<button class="x2-button black" type="submit"><span></span></button>'
	.CHtml::textField('term',Yii::t('app','Search for contact, action, deal...'),array(
		'id'=>'search-bar-box',
		'onfocus'=>'toggleText(this);',
		'onblur'=>'toggleText(this);',
		'autocomplete'=>'off'
	)).'</form>';

if(!empty(Yii::app()->params->profile->avatar) && file_exists(Yii::app()->params->profile->avatar))
	$avatar = Yii::app()->request->baseUrl.'/'.Yii::app()->params->profile->avatar;
else
	$avatar = Yii::app()->request->baseUrl.'/uploads/default.png';

$widgetsImageUrl = $themeUrl . '/images/admin_settings.png';
if(!Yii::app()->user->isGuest){
    $widgetMenu=Yii::app()->params->profile->getWidgetMenu();
}else{
    $widgetMenu="";
}
$userMenu = array(
	array('label' => Yii::t('app','Admin'), 'url' => array('/admin/index'),'active'=>($module=='admin')?true:null, 'visible'=>$isAdmin),
	array('label' => Yii::t('app','Activity'),'url' => array('/site/whatsNew')),

	array('label' => Yii::t('app','Users'),'url' => array('/users/admin'),'visible'=>$isAdmin),
	array('label' => Yii::t('app','Users'),'url' => array('/profile/profiles'),'visible'=>!$isAdmin),

	array('label' => $searchbarHtml,'itemOptions'=>array('id'=>'search-bar','class'=>'special')),
	array('label'=>CHtml::link('<span>'.$notifCount.'</span>','#',array('id'=>'main-menu-notif','style'=>'z-index:999;')),'itemOptions'=>array('class'=>'special')),
	array('label'=>CHtml::link('<span>&nbsp;</span>','#',array('class'=>'x2-button','id'=>'fullscreen-button')),'itemOptions'=>array('class'=>'search-bar special')),
	array('label'=>CHtml::link('<div class="widget-icon"></div>','#',array(
			'id'=>'widget-button',
			'class'=>'x2-button',
			'title'=>'hidden widgets'
		)).$widgetMenu,
		'itemOptions'=>array('class'=>'search-bar special'
	)),
	array('label'=>CHtml::image($avatar,'',array('height'=>25,'width'=>25)).Yii::app()->user->getName(),
		'itemOptions'=>array('id'=>'profile-dropdown','class'=>'dropdown'),
		'items' => array(
			array('label' => Yii::t('app','Profile'),'url' => array('/profile/view','id' => Yii::app()->user->getId())),
			array('label' => Yii::t('app','Notifications'),'url' => array('/site/viewNotifications')),
			array('label' => Yii::t('app','Preferences'),'url' => array('/profile/settings')),
			array('label' => Yii::t('app','Help'),'url' => 'http://www.x2engine.com/screen-shots-2', 'linkOptions'=>array('target'=>'_blank')),
			array('label' => Yii::t('help','Icon Reference'), 'url' => array ('/site/page/', 'view'=>'iconreference')),
            array('label' => Yii::t('app','Report A Bug'),'url' => array('/site/bugReport')),
            array('label' => Yii::t('app','---'),'itemOptions'=>array('class'=>'divider')),
			array('label' => Yii::app()->params->sessionStatus? Yii::t('app','Go Invisible') : Yii::t('app','Go Visible'),'url'=>'#',
				'linkOptions'=>array(
					'submit'=>array('/site/toggleVisibility','visible'=>!Yii::app()->params->sessionStatus,'redirect'=>Yii::app()->request->requestUri),
					'confirm'=>'Are you sure you want to toggle your session status?',
				)),
			array('label' => Yii::t('app','Logout'),'url' => array('/site/logout'))
		)
	),
	// array(
		// 'label'=>'',
		// 'itemOptions'=>array(
			// 'class'=>'special leadrouting-indicator'.(Yii::app()->params->sessionStatus == 1? ' visible' : ''),
			// 'title'=>Yii::app()->params->sessionStatus? Yii::t('app','Visible to lead routing') : Yii::t('app','Invisible to lead routing'))
		// )
	// ),
);

?><!DOCTYPE html>
<!--[if lt IE 9]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>" class="lt-ie9">
<![endif]-->
<!--[if gt IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<![endif]-->
<!--[if !IE]> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<!-- <![endif]-->

<head>
<meta charset="UTF-8">
<link rel="icon" href="<?php echo $baseUrl; ?>/images/favicon.ico" type="image/x-icon">
<link rel="shortcut-icon" href="<?php echo $baseUrl; ?>/images/favicon.ico" type="image/x-icon">
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/ie.css" media="screen, projection">
<![endif]-->
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<?php
if(method_exists($this,'renderGaCode'))
    $this->renderGaCode('internal');
?>

</head>
<body style="<?php
	$noBorders = false;
	if(!empty(Yii::app()->params->profile->backgroundColor))
		echo 'background-color:#'.Yii::app()->params->profile->backgroundColor.';';

	if(!empty(Yii::app()->params->profile->backgroundImg)) {

		if(file_exists('uploads/'.Yii::app()->params->profile->backgroundImg))
			echo 'background-image:url('.$baseUrl.'/uploads/'.Yii::app()->params->profile->backgroundImg.');';
		else
			echo 'background-image:url('.$baseUrl.'/uploads/media/'.Yii::app()->user->getName().'/'.Yii::app()->params->profile->backgroundImg.');';

		switch($bgTiling = Yii::app()->params->profile->backgroundTiling) {
			case 'repeat-x':
			case 'repeat-y':
			case 'repeat':
				echo 'background-repeat:'.$bgTiling.';';
				break;
			case 'center':
				echo 'background-repeat:no-repeat;background-position:center center;';
				break;
			case 'stretch':
			default:
				echo 'background-attachment:fixed;background-size:cover;';
				$noBorders = true;
		}
	}
?>"<?php if($noBorders) echo ' class="no-borders"'; ?>>

<div id="page-container">
<div id="page">
<?php //echo $backgroundImg; ?>
<div id="header" <?php
	if(empty(Yii::app()->params->profile->menuBgColor))
		echo 'class="defaultBg"';
	else
		echo 'style="background-color:#'.Yii::app()->params->profile->menuBgColor.';"';
?>>
	<div id="header-inner">
		<div id="main-menu-bar">
				<?php
				//render main menu items
				$this->widget('zii.widgets.CMenu', array(
					'id'=>'main-menu',
					'encodeLabel'=>false,
					'htmlOptions'=>array('class'=>'main-menu'),
					'items'=>$menuItems
				));
				//render user menu items if logged in
				if (!$isGuest) {
					$this->widget('zii.widgets.CMenu', array(
						'id' => 'user-menu',
						'items' => $userMenu,
						'htmlOptions'=>array('class'=>'main-menu'),
						'encodeLabel' => false
					));
				}
			?>
			<div id="notif-box"><div id="no-notifications"<?php if($notifCount > 0) echo ' style="display:none;"'; ?>>
				<?php echo Yii::t('app','You don\'t have any notifications.'); ?>
				</div><div id="notifications"></div><div id="notif-view-all"<?php if($notifCount < 11) echo ' style="display:none;"'; ?>>
				<?php echo CHtml::link(Yii::t('app','View all'),array('/site/viewNotifications')); ?>
				</div></div>
		</div>
	</div>
</div>
<?php echo $content; ?>
<!--
<div style="font-family:monospace, 'Courier New';">
<?php
	// if(!empty($GLOBALS['modelDebug']))
		// foreach($GLOBALS['modelDebug'] as $modelName=>$calls) {
			// echo "<u>$modelName</u><br>";
			// foreach($calls as $x) {

				// foreach($x as $y) {
					// if(isset($y['file'],$y['line'],$y['function'])) {
						// $file = preg_replace('/^.+x2engine/','',$y['file']).' <b>('.$y['line'].')</b>';

						// $file .= '<span style="color:#aaa">';
						// $file .= substr('---------------------------------------------------------------------------------------------------',0,110-strlen($file));
						// echo $file.'</span> <em>'.$y['function'].'()</em><br>';
					// }
				// }
				// echo '<br>';
			// }
		// }

	// if(!empty($GLOBALS['modelCount'])) {

		// $total = 0;
		// foreach($GLOBALS['modelCount'] as $modelname=>$ids) {
			// $total += count($ids);
			// $values = array_count_values($ids);

			// foreach($values as $id=>$count) {
				// if($id<0) $id='null';
				// echo "$modelname-$id ... $count<br>";

			// }
		// }
		// echo "<br>total: $total";
	// }

	// echo $GLOBALS['accessCount'];
	// if(isset( $GLOBALS['access'] ))
		// var_dump( $GLOBALS['access'] );
	// var_dump( Yii::app()->db->getStats());

?>
</div>
-->
</div>
</div>
<?php
$this->renderPartial('//layouts/footer');
if(Yii::app()->session['translate'])
	echo '<div class="yiiTranslationList"><b>Other translated messages</b><br></div>';

if(isset($_SESSION['playLoginSound']) && $_SESSION['playLoginSound']){
        $_SESSION['playLoginSound']=false;
        $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());
        $where = 'fileName = "' . $profile->loginSound . '"';
        $uploadedBy = Yii::app()->db->createCommand()->select('uploadedBy')->from('x2_media')->where($where)->queryRow();
        if(!empty($uploadedBy['uploadedBy'])){
            $loginSound = Yii::app()->baseUrl . '/uploads/media/' . $uploadedBy['uploadedBy'] . '/' . $profile->loginSound;
        }else{
            $loginSound = Yii::app()->baseUrl . '/uploads/'. $profile->loginSound;
        }
        echo "";
        Yii::app()->clientScript->registerScript('playLoginSound','
                $("#loginSound").attr("src","'.$loginSound.'");
                var sound = $("#loginSound")[0];
                sound.play();
        ');
}
?>
<a id="page-fader" class="x2-button"><span></span></a>
<div id="dialog" title="Completion Notes? (Optional)" style="display:none;" class="text-area-wrapper">
    <textarea id="completion-notes" style="height:110px;width:99%;"></textarea>
</div>
</body>
<audio id="notificationSound"></audio>
<audio id='loginSound'></audio>
</html>
