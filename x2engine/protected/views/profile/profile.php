<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/*
Public/private profile page. If the requested profile belongs to the current user, profile widgets
get displayed in addition to the activity feed/profile information sections. 
*/

if (!$isMyProfile && Yii::app()->user->id == $model->id) {
    $this->insertActionMenu();
}

$this->noBackdrop = true;

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->baseUrl.'/js/profile.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFiles ('profileCombinedCss', array (
    'profile.css', 'activityFeed.css', '../../../js/multiselect/css/ui.multiselect.css'
));
Yii::app()->clientScript->registerResponsiveCssFile (Yii::app()->theme->baseUrl.'/css/responsiveActivityFeed.css');

AuxLib::registerPassVarsToClientScriptScript (
    'x2.profile', array ('isMyProfile' => ($isMyProfile ? 'true' : 'false')), 'profileScript');


$fullProfile = $isMyProfile ? 'full-profile' : '';
$width = '';
if ($isMyProfile) {
    $this->leftWidgets = array (
        'ProfileInfo' => array(
            'model' => $model
        )
    );

    $dashboard = $this->widget('ProfileDashboardManager', array(
        'model' => $model
        ));

    list($width) = $dashboard->getColumnWidths();

    Tours::loadTips ('profile.index');
}

?>
<div id='profile-content-container' class='<?php echo $fullProfile ?>'>

    <div id='profile-info-container-outer'>
        <?php 
        if (!$isMyProfile) { 
            $this->renderPartial('_profileInfo', array(
                'model' => $model, 
            )); 
        }
        echo X2Html::getFlashes(); 
        ?>
    </div>

        <?php 
        if ($isMyProfile) $dashboard->renderContainer(1); 
        if ($isMyProfile) $dashboard->renderContainer(2); 
        ?>

        <div id='activity-feed-container-outer' style="width: <?php echo $width ?>">
            <?php $this->renderPartial('_activityFeed', $activityFeedParams); ?>
        </div>  

</div>
