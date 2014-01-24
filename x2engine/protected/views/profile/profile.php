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

/*
Public/private profile page. If the requested profile belongs to the current user, profile widgets
get displayed in addition to the activity feed/profile information sections. 
*/

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/profile.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(Yii::app()->getTheme()->getBaseUrl().'/css/profileCombined.css');
//Yii::app()->clientScript->registerCssFile(Yii::app()->getTheme()->getBaseUrl().'/css/profile.css');

AuxLib::registerPassVarsToClientScriptScript (
    'x2.profile', array ('isMyProfile' => ($isMyProfile ? 'true' : 'false')), 'profileScript');

?>
<div id='profile-info-container-outer' class='x2-layout-island'>
<?php
$this->renderPartial('_profileInfo', array('model'=>$model, 'isMyProfile'=>$isMyProfile)); 
?>
</div>
<?php

if ($isMyProfile) {
    $layout = $model->profileWidgetLayout;
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/sortableWidgets/SortableWidget.js', CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/sortableWidgets/SortableWidgetManager.js', 
        CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/sortableWidgets/ProfileWidgetManager.js', 
        CClientScript::POS_END);
    Yii::app()->clientScript->registerScript ('profilePageWidgetInitScript', "
        x2.profileWidgetManager = new ProfileWidgetManager ({
            setSortOrderUrl: '".Yii::app()->controller->createUrl ('/profile/setWidgetOrder')."',
            showWidgetContentsUrl: '".Yii::app()->controller->createUrl (
                '/profile/view', array ('id' => 1))."',
            connectedContainerSelector: '.connected-sortable-profile-container'
        });
    ", CClientScript::POS_READY);
     
    /**
     * @param int $containerNumber The container for which widgets should get instantiated 
     * @param array $layout profile widget layout
     * @param object $controller profile controller
     * @param object $model profile model
     */
    function displayWidgets ($containerNumber, $layout, $controller, $model) {
        // display profile widgets in order
        foreach ($layout as $widgetClass => $settings) {
            if ($settings['containerNumber'] == $containerNumber) {
                $controller->widget('application.components.sortableWidget.'.$widgetClass, array (
                    'profile' => $model,
                    'widgetType' => 'profile',
                ));
            }
        }
    }
?>

<div id='profile-widgets-container'>
<div id='profile-widgets-container-inner' class='connected-sortable-profile-container'>

<?php
displayWidgets (1, $layout, $this, $model);
?>
</div>
</div>

<div id='profile-widgets-container-2' class='connected-sortable-profile-container'>
<?php
displayWidgets (2, $layout, $this, $model);
?>
</div>

<?php
}
?>
<div id='activity-feed-container-outer' class='x2-layout-island'>
<?php
$this->renderPartial('_activityFeed', array(
    'dataProvider' => $dataProvider,
    'profileId' => $model->id,
    'users' => $users,
    'lastEventId' => $lastEventId,
    'firstEventId' => $firstEventId,
    'lastTimestamp' => $lastTimestamp,
    'stickyDataProvider' => $stickyDataProvider,
    'usersDataProvider' => $usersDataProvider,
    'isMyProfile' => $isMyProfile
));
?>
</div>
