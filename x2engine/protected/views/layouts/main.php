<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
$auth = Yii::app()->authManager;
$isAdmin = !$isGuest && (Yii::app()->params->isAdmin);
$isUser = !($isGuest || $isAdmin);
if($isAdmin && file_exists($updateManifest = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..',UpdaterBehavior::UPDATE_DIR,'manifest.json')))) {
    $manifest = @json_decode(file_get_contents($updateManifest),1);
    if(isset($manifest['scenario']) && !(Yii::app()->controller->id == 'admin' && Yii::app()->controller->action->id == 'updater')) {
        Yii::app()->user->setFlash('admin.update',Yii::t('admin', 'There is an unfinished {scenario} in progress.',array('{scenario}'=>$manifest['scenario']=='update' ? Yii::t('admin','update'):Yii::t('admin','upgrade')))
                .'&nbsp;&bull;&nbsp;'.CHtml::link(Yii::t('admin','Resume'),array("/admin/updater",'scenario'=>$manifest['scenario']))
                .'&nbsp;&bull;&nbsp;'.CHtml::link(Yii::t('admin','Cancel'),array("/admin/updater",'scenario'=>'delete','redirect'=>1)));
    }
} else if($isAdmin && Yii::app()->session['alertUpdate']){
    Yii::app()->user->setFlash('admin.update',Yii::t('admin', 'A new version is available.')
            .'&nbsp;&bull;&nbsp;'.CHtml::link(Yii::t('admin','Update X2CRM'),array('/admin/updater'))
            .'&nbsp;&bull;&nbsp;'.CHtml::link(Yii::t('admin','Updater Settings'),array('/admin/updaterSettings')));
    Yii::app()->session['alertUpdate'] = false;
}
if(is_int(Yii::app()->locked)) {
    $lockMsg = '<strong>'.Yii::t('admin','The application is currently locked.').'</strong>';
    if(file_exists(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'components','LockAppAction.php')))) {
        $lockMsg .= ' '.CHtml::link(Yii::t('admin','Unlock X2CRM'),array('/admin/lockApp','toggle'=>0));
    } else {
        $lockMsg .= Yii::t('admin', 'You can manually unlock the application by deleting the file {file}', array('{file}' => '<em>"x2crm.lock"</em> in protected/config'));
    }
    Yii::app()->user->setFlash('admin.isLocked',$lockMsg);
}


$cs = Yii::app()->clientScript;
$baseUrl = $cs->baseUrl;
$scriptUrl = $cs->scriptUrl;
$themeUrl = $cs->themeUrl;
$admin = $cs->admin;
$profile = $cs->profile;
$fullscreen = $cs->fullscreen;
$cs->registerMain();

$backgroundImg = '';
$defaultOpacity = 1;

$preferences = null;
if ($profile != null) {
    $preferences = $profile->theme;
}


$logoMissing = false;
$checkFiles = array(
    // 'themes/x2engine/images/x2footer.png'=>'1393e4af54ababdcf76fac7f075b555b',
    // 'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
    'images/powered_by_x2engine.png' => 'b7374cbbd29cd63191f7e0b1dcd83c48',
);
foreach($checkFiles as $key => $value){
    if(!file_exists($key) || hash_file('md5', $key) !== $value)
        $logoMissing = true;
}
$theme2Css = '';
if($logoMissing)
    $theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('/site/warning')).') !important;} #bg{display:none !important;}';

// check for background image, use it if one is set
// if(!$preferences['backgroundImg'])
// $backgroundImg = CHtml::image('','',array('id'=>'bg','style'=>'display:none;'));
// else
// $backgroundImg = CHtml::image($baseUrl.'/uploads/'.$preferences['backgroundImg'],'',array('id'=>'bg'));


$themeCss = '';
if ($preferences != null && $preferences['menuTextColor'])
    $themeCss .= 'ul.main-menu > li > a, ul.main-menu > li > span {color:#'.$preferences['menuTextColor'].";}\n";
// if ($preferences != null && $preferences['pageHeaderBgColor'])
    // $themeCss .= 'div.page-title {background-color:#'.$preferences['pageHeaderBgColor'].";}\n";
if ($preferences != null && $preferences['pageHeaderTextColor'])
    $themeCss .= 'div.page-title, div.page-title h2 {color:#'.$preferences['pageHeaderTextColor'].";}\n";
// calculate a slight gradient for menu bar color
if ($preferences != null && $preferences['menuBgColor']) {
	//$rgb = X2Color::hex2rgb($preferences['menuBgColor']);
	//$darkerBgColor = '#'.X2Color::rgb2hex(floor($rgb[0]*0.85),floor($rgb[1]*0.85),floor($rgb[2]*0.85));
	//$themeCss .= '#header {';
	//$themeCss .= X2Color::gradientCss('#'.$preferences['menuBgColor'],$darkerBgColor)."}\n";
	$themeCss .= '#header {
        background: #' . $preferences['menuBgColor'] . ' !important;
    }';
	// $themeCss .= '.main-menu > li:hover, .main-menu > li.active {background:rgba('.floor($rgb[0]*0.4).','.floor($rgb[1]*0.4).','.floor($rgb[2]*0.4).',0.5);}';
}
// calculate a slight gradient for menu bar color
if ($preferences != null && $preferences['pageHeaderBgColor']) {
	$rgb = X2Color::hex2rgb($preferences['pageHeaderBgColor']);
	$darkerBgColor = '#'.X2Color::rgb2hex(floor($rgb[0]*0.85),floor($rgb[1]*0.85),floor($rgb[2]*0.85));
	$themeCss .= 'div.page-title {';
	$themeCss .= X2Color::gradientCss('#'.$preferences['pageHeaderBgColor'],$darkerBgColor).'}';
	// $themeCss .= '.main-menu > li:hover, .main-menu > li.active {background:rgba('.floor($rgb[0]*0.4).','.floor($rgb[1]*0.4).','.floor($rgb[2]*0.4).',0.5);}';
}


if ($preferences != null && $preferences['activityFeedWidgetBgColor']){
	$themeCss .= '#feed-box {
		background-color: #'.$preferences['activityFeedWidgetBgColor'].';
	 }';
}
if ($preferences != null && $preferences['gridViewRowColorEven']){
	$themeCss .= 'div.grid-view table.items tr.even {
		background: #'.$preferences['gridViewRowColorEven'].' !important;
	 }';
}
if ($preferences != null && $preferences['gridViewRowColorOdd']){
	$themeCss .= 'div.x2-gridview tr.odd {
		background: #'.$preferences['gridViewRowColorOdd'].' !important;
	 }';
}

/* Retrieve flash messages and calculate the appropriate styles for flash messages if applicable */
$allFlashes = Yii::app()->user->getFlashes();
$adminFlashes = array();
$index = 0;
foreach($allFlashes as $key => $message){
    if(strpos($key, 'admin') === 0){
        $adminFlashes[$index] = $message;
        $index++;
    }
}

if($n_flash = count($adminFlashes)) {
    $flashTotalHeight = 17; // See layout.css for details
    $themeCss .= '
div#header {
    position:fixed;
    top: '.($flashTotalHeight*$n_flash).'px;
    left: 0;
}
div#page {
    margin-top:'.(32 + $flashTotalHeight*$n_flash).'px !important;
}
div#x2-gridview-top-bar-outer {
    position:fixed;
    top: '.(32 +$flashTotalHeight*$n_flash).'px;
    left: 0;
}
';
    foreach($adminFlashes as $index => $message) {
        $themeCss .= "
div.flash-message-$index {
        top: ".(string)($index*$flashTotalHeight)."px;
}
";
    }
}

// Outputs white or black depending on input color
// @param $colorString a string representing a hex number
// @param $testType standardText or linkText
function convertTextColor($colorString, $textType){
    // Split the string to red, green and blue components
    // Convert hex strings into ints
    $red = intval(substr($colorString, 0, 2), 16);
    $green = intval(substr($colorString, 2, 2), 16);
    $blue = intval(substr($colorString, 4, 2), 16);
    if($textType == 'standardText'){
        return (((($red * 299) + ($green * 587) + ($blue * 114)) / 1000) >= 128) ? 'black' : 'white';
    }else if($textType == 'linkText'){
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
            return '#fff000';  // Yellow links
        }
        else
            return '#0645AD'; // Blue link color
    }
    else if($textType == 'visitedLinkText'){
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
            return '#ede100';  // Yellow links
        }
        else
            return '#0B0080'; // Blue link color
    }
    else if($textType == 'activeLinkText'){
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
            return '#fff000';  // Yellow links
        }
        else
            return '#0645AD'; // Blue link color
    }
    else if($textType == 'hoverLinkText'){
        if(((($red < 100) || ($green < 100)) && $blue > 80) || (($red < 80) && ($green < 80) && ($blue < 80))){
            return '#fff761';  // Yellow links
        }
        else
            return '#3366BB'; // Blue link color
    }
}

// Check if any element of a triple is significantly less than the other two
// based on a defined value
function isSignificantlyLess($x, $y, $z, $howMuch){
    if(($x > ($z + $howMuch)) && ($y > ($z + $howMuch)))
        return true;
    if(($x > ($y + $howMuch)) && ($z > ($y + $howMuch)))
        return true;
    if(($y > ($x + $howMuch)) && ($z > ($x + $howMuch)))
        return true;
    return false;
}

$cs->registerCss('applyTheme', $themeCss, 'screen', CClientScript::POS_HEAD);
$cs->registerCss('applyTheme2', $theme2Css, 'screen', CClientScript::POS_HEAD);

// $admin=Admin::model()->findByPk(1);

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

$module = Yii::app()->controller->id;
$menuItems = array();
if($isGuest){
    $menuItems = array(
        array('label' => Yii::t('app', 'Login'), 'url' => array('/site/login')),
    );
}
// $admin=Admin::model()->findByPk(1);

$modules = Modules::model()->findAll(array('condition' => 'visible="1"', 'order' => 'menuPosition ASC'));
$standardMenuItems = array();
foreach($modules as $moduleItem){
    if(($isAdmin || $moduleItem->adminOnly == 0) && $moduleItem->name != 'users'){
        if($moduleItem->name != 'document')
            $standardMenuItems[$moduleItem->name] = $moduleItem->title;
        else
            $standardMenuItems[$moduleItem->title] = $moduleItem->title;
    }
}

$defaultAction = 'index';
//$isAdmin? 'admin' : 'index';

foreach($standardMenuItems as $key => $value){
    $file = Yii::app()->file->set('protected/controllers/'.ucfirst($key).'Controller.php');
    $action = ucfirst($key).ucfirst($defaultAction);
    $authItem = $auth->getAuthItem($action);
    $permission = Yii::app()->user->checkAccess($action) || is_null($authItem);
    if($file->exists){
        if($permission)
            $menuItems[$key] = array('label' => Yii::t('app', $value), 'url' => array("/$key/$defaultAction"), 'active' => (strtolower($module) == strtolower($key)) ? true : null);
    }elseif(is_dir('protected/modules/'.$key)){
        if(!is_null($this->getModule()))
            $module = $this->getModule()->id;
        if($permission)
            $menuItems[$key] = array('label' => Yii::t('app', $value), 'url' => array("/$key/$defaultAction"), 'active' => (strtolower($module) == strtolower($key) && (!isset($_GET['static']) || $_GET['static'] != 'true')) ? true : null);
    } else{
        $page = Docs::model()->findByAttributes(array('name' => ucfirst(mb_ereg_replace('&#58;', ':', $value))));
        if(isset($page) && Yii::app()->user->checkAccess('DocsView')){
            $id = $page->id;
            $menuItems[$key] = array('label' => ucfirst($value), 'url' => array('/docs/'.$id.'?static=true'), 'active' => Yii::app()->request->requestUri == $scriptUrl.'/docs/'.$id.'?static=true' ? true : null);
        }
    }
}



$maxMenuItems = 4;
//check if menu has too many items to fit nicely
$menuItemCount = count($menuItems);
if($menuItemCount > $maxMenuItems){
    $moreMenuItems = array();
    //move the last few menu items into the "More" dropdown
    for($i = 0; $i < $menuItemCount - ($maxMenuItems - 1); $i++){
        array_unshift($moreMenuItems, array_pop($menuItems));
    }
    //add "More" to main menu
    $menuItems[] = array('label' => Yii::t('app', 'More'), 'items' => $moreMenuItems, 'itemOptions' => array('id' => 'more-menu', 'class' => 'dropdown'));
}

// find out the dimensions of the user-uploaded logo so the menu can do its layout calculations
$logoOptions = array();
if(is_file(Yii::app()->params->logo)){
    $logoSize = @getimagesize(Yii::app()->params->logo);
    if($logoSize)
        $logoSize = array(min($logoSize[0], 200), min($logoSize[1], 30));
    else
        $logoSize = array(92, 30);

    $logoOptions['width'] = $logoSize[0];
    $logoOptions['height'] = $logoSize[1];
}
array_unshift($menuItems, array(
    'label' => CHtml::image(Yii::app()->request->baseUrl.'/'.Yii::app()->params->logo, Yii::app()->name, $logoOptions),
    'url' => array('/profile', 'id' => Yii::app()->user->id),
    'active' => false,
    'itemOptions' => array('id' => 'search-bar-title', 'class' => 'special','title'=>Yii::t('app','Go to Activity Feed'))
));


/* Construction of the user menu */
$notifCount = X2Model::model('Notification')->countByAttributes(array('user' => Yii::app()->user->getName()), 'createDate < '.time());

$searchbarHtml = CHtml::beginForm(array('/search/search'), 'get')
        .'<button class="x2-button black" type="submit"><span></span></button>'
        .CHtml::textField('term', Yii::t('app', 'Search for contact, action, deal...'), array(
            'id' => 'search-bar-box',
            'onfocus' => 'toggleText(this);',
            'onblur' => 'toggleText(this);',
            'autocomplete' => 'off'
        )).'</form>';

if(!empty($profile->avatar) && file_exists($profile->avatar))
    $avatar = Yii::app()->request->baseUrl.'/'.$profile->avatar;
else
    $avatar = Yii::app()->request->baseUrl.'/uploads/default.png';

$widgetsImageUrl = $themeUrl.'/images/admin_settings.png';
if(!Yii::app()->user->isGuest){
    $widgetMenu = $profile->getWidgetMenu();
}else{
    $widgetMenu = "";
}
$userMenu = array(
    array('label' => Yii::t('app', 'Admin'), 'url' => array('/admin/index'), 'active' => ($module == 'admin') ? true : null, 'visible' => $isAdmin),
    array('label' => Yii::t('app', 'Profile'), 'url' => array('/profile/view', 'id' => Yii::app()->user->getId())),
    array('label' => Yii::t('app', 'Users'), 'url' => array('/users/users/admin'), 'visible' => $isAdmin),
    array('label' => Yii::t('app', 'Users'), 'url' => array('/profile/profiles'), 'visible' => !$isAdmin),
    array('label' => $searchbarHtml, 'itemOptions' => array('id' => 'search-bar', 'class' => 'special')),
    array('label' => CHtml::link(
        '<span>'.$notifCount.'</span>', '#', array('id' => 'main-menu-notif', 'style' => 'z-index:999;')),
        'itemOptions' => array('class' => 'special')),
    array('label' => CHtml::link(
        '<span>&nbsp;</span>', '#', array('class' => 'x2-button', 'id' => 'fullscreen-button')),
        'itemOptions' => array('class' => 'search-bar special')),
    array('label' => CHtml::link('<div class="widget-icon"></div>', '#', array(
            'id' => 'widget-button',
            'class' => 'x2-button',
            'title' => 'hidden widgets'
        )).$widgetMenu,
        'itemOptions' => array('class' => 'search-bar special'
    )),
    array('label' => CHtml::image($avatar, '', array('height' => 25, 'width' => 25)).Yii::app()->user->getName(),
        'itemOptions' => array('id' => 'profile-dropdown', 'class' => 'dropdown'),
        'items' => array(
            array('label' => Yii::t('app', 'Profile'), 'url' => array('/profile/view', 'id' => Yii::app()->user->getId())),
            array('label' => Yii::t('app', 'Notifications'), 'url' => array('/site/viewNotifications')),
            array('label' => Yii::t('app', 'Preferences'), 'url' => array('/profile/settings')),
			array('label' => Yii::t('profile', 'Manage Apps'), 'url' => array('/profile/manageCredentials')),
            array('label' => Yii::t('help', 'Icon Reference'), 'url' => array('/site/page', 'view' => 'iconreference')),
            array('label' => Yii::t('help', 'Help'), 'url' => 'http://www.x2engine.com/reference_guide','linkOptions'=>array('target'=>'_blank')),
            array('label' => Yii::t('app', 'Report A Bug'), 'url' => array('/site/bugReport')),
            array('label' => Yii::t('app', '---'), 'itemOptions' => array('class' => 'divider')),
            array('label' => Yii::app()->params->sessionStatus ? Yii::t('app', 'Go Invisible') : Yii::t('app', 'Go Visible'), 'url' => '#',
                'linkOptions' => array(
                    'submit' => array('/site/toggleVisibility', 'visible' => !Yii::app()->params->sessionStatus, 'redirect' => Yii::app()->request->requestUri),
                    'confirm' => 'Are you sure you want to toggle your session status?',
            )),
            array('label' => Yii::t('app', 'Logout'), 'url' => array('/site/logout'))
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
// HTML 5 Reset:
// <!--[if lt IE 7 ]> <html class="ie ie6 ie-lt10 ie-lt9 ie-lt8 ie-lt7 no-js" lang="en"> <![endif]-->
// <!--[if IE 7 ]> <html class="ie ie7 ie-lt10 ie-lt9 ie-lt8 no-js" lang="en"> <![endif]-->
// <!--[if IE 8 ]> <html class="ie ie8 ie-lt10 ie-lt9 no-js" lang="en"> <![endif]-->
// <!--[if IE 9 ]> <html class="ie ie9 ie-lt10 no-js" lang="en"> <![endif]-->
// <!--[if gt IE 9]><!--><html class="no-js" lang="en"><!--<![endif]-->


// causes the moronic "compatibility mode" in IE8 for some dang reason
/* <!--[if lt IE 9]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>" class="lt-ie9">
<![endif]-->
<!--[if gt IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<![endif]-->
<!--[if !IE]> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<!-- <![endif]--> */


?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">

<head>
<meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge">
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
	if ($preferences != null && $preferences['backgroundColor'])
		echo 'background-color:#'.$preferences['backgroundColor'].';';

	if ($preferences != null && $preferences['backgroundImg']) {

		if(file_exists('uploads/'.$preferences['backgroundImg'])) {
			echo 'background-image:url('.$baseUrl.'/uploads/'.$preferences['backgroundImg'].');';
		} else {
			echo 'background-image:url('.$baseUrl.'/uploads/media/'.Yii::app()->user->getName().'/'.$preferences['backgroundImg'].');';
        }

		switch($bgTiling = $preferences['backgroundTiling']) {
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
?>" class="<?php if($noBorders) echo 'no-borders'; if($fullscreen) echo ' no-widgets'; ?>">

<div id="page-container">
<div id="page">
<?php //echo $backgroundImg; ?>
    <?php
    if(count($adminFlashes) > 0){
        foreach($adminFlashes as $index => $message){
                echo CHtml::tag('div',array('class'=>"admin-flash-message flash-message-$index"),$message);
        }
    } ?>
	<div id="header" <?php echo !$preferences['menuBgColor']? 'class="defaultBg"' : ''; ?>>
		<div id="header-inner">
			<div id="main-menu-bar">
				<?php
				//render main menu items
				$this->widget('zii.widgets.CMenu', array(
					'id' => 'main-menu',
					'encodeLabel' => false,
					'htmlOptions' => array('class' => 'main-menu'),
					'items' => $menuItems
				));
				//render user menu items if logged in
				if(!$isGuest){
					$this->widget('zii.widgets.CMenu', array(
						'id' => 'user-menu',
						'items' => $userMenu,
						'htmlOptions' => array('class' => 'main-menu'),
						'encodeLabel' => false
					));
				}
				?>
				<div id="notif-box">
					<div id="no-notifications"<?php if($notifCount > 0) echo ' style="display:none;"'; ?>>
					<?php echo Yii::t('app', 'You don\'t have any notifications.'); ?>
					</div><div id="notifications"></div>
                    <div id="notif-view-all"<?php if($notifCount < 11) echo ' style="display:none;"'; ?>>
					    <?php echo CHtml::link(
                            Yii::t('app', 'View all'), array('/site/viewNotifications')); ?>
					</div>
                    <div class='right' id="notif-clear-all"
                     <?php if ($notifCount === '0') echo ' style="display:none;"'; ?>>
					    <?php echo CHtml::link(Yii::t('app', 'Clear all'), '#'); ?>
					</div>
				</div>
				<div id="notif-box-shadow-correct"> <!-- IE fix, used to force repaint -->
				</div>
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

		if($preferences != null &&
           ($preferences['loginSound'] || $preferences['notificationSound']) &&
           isset($_SESSION['playLoginSound']) && $_SESSION['playLoginSound']){

			$_SESSION['playLoginSound'] = false;
			$where = 'fileName = "'.$preferences['loginSound'].'"';
			$uploadedBy = Yii::app()->db->createCommand()->select('uploadedBy')->from('x2_media')->where($where)->queryRow();
			if(!empty($uploadedBy['uploadedBy'])){
				$loginSound = Yii::app()->baseUrl.'/uploads/media/'.$uploadedBy['uploadedBy'].'/'.$preferences['loginSound'];
			}else{
				$loginSound = Yii::app()->baseUrl.'/uploads/'.$preferences['loginSound'];
			}
			echo "";
			Yii::app()->clientScript->registerScript('playLoginSound', '
		$("#loginSound").attr("src","'.$loginSound.'");

        var sound = $("#loginSound")[0];
        if (Modernizr.audio) sound.play();

');
		}
		?>
<a id="page-fader" class="x2-button"><span></span></a>
<div id="dialog" title="Completion Notes? (Optional)" style="display:none;" class="text-area-wrapper">
	<textarea id="completion-notes" style="height:110px;"></textarea>
</div>
</body>
<audio id="notificationSound"> </audio>
<audio id='loginSound'> </audio>
</html>
