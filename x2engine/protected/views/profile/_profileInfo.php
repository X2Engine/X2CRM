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




$model->fullName = $model->user->getFullName();
$isMyProfile = ($model->id === Yii::app()->params->profile->id);

$attributeLabels = $model->attributeLabels();
if ($isMyProfile) {
    $miscLayoutSettings = $model->miscLayoutSettings;
    $profileInfoMinimized = $miscLayoutSettings['profileInfoIsMinimized'];
    $fullProfileInfo = true;
    
    // $fullProfileInfo = $miscLayoutSettings['fullProfileInfo'];
}
?>
<div id='profile-info-container' class='x2-layout-island'>

<div class="responsive-page-title page-title icon profile">
    <h2>
        <span class="no-bold"><?php echo Yii::t('profile','Profile:'); ?></span>
        <?php echo CHtml::encode($model->fullName); ?>
    </h2>
<?php
    if ($isMyProfile) {
        // echo ResponsiveHtml::gripButton ();
        ?>
        <div class='responsive-menu-items'>
        <?php
        echo CHtml::link(
            "<span class='fa fa-caret-down'></span>", '#',
            array(
                'class' => 'icon right',
                'id' => 'profile-info-minimize-button',
                'title' => Yii::t('app', 'Minimize Profile Info'),
                'style' => ($profileInfoMinimized ? 'display: none;' : ''), 
                'onclick' => '
                    auxlib.saveMiscLayoutSetting ("profileInfoIsMinimized", 1); 
                    $("#profile-info-minimize-button").hide ();
                    $("#profile-info-maximize-button").show ();
                    $("#profile-info-contents-container").slideUp ();
                    return false;'
            )
        );
        echo CHtml::link(
            "<span class='fa fa-caret-left'></span>", '#',
            array(
                'class' => 'icon right',
                'id' => 'profile-info-maximize-button',
                'title' => Yii::t('app', 'Maximize Profile Info'),
                'style' => (!$profileInfoMinimized ? 'display: none;' : ''), 
                'onclick' => '
                    auxlib.saveMiscLayoutSetting ("profileInfoIsMinimized", 0); 
                    $("#profile-info-maximize-button").hide ();
                    $("#profile-info-minimize-button").show ();
                    $("#profile-info-contents-container").slideDown ();
                    return false;'
            )
        );
        // echo X2Html::settingsButton (Yii::t('app', 'Profile Settings'), 
            // array (
                // 'id' => 'profile-settings-button',
                // 'class' => 'right x2-popup-dropdown-button',
            // ));
        ?> 
        <ul id='profile-info-settings-menu' class='x2-popup-dropdown-menu' style='display: none;'>
            <li id='add-profile-widget-button-list-item'>
                <span id='add-profile-widget-button'><?php 
                    echo Yii::t('app', 'Show Profile Widget'); ?></span>
            </li>
            <li>
                <span id='create-profile-widget-button'><?php 
                    echo Yii::t('app', 'Create Profile Widget'); ?></span>
            </li>
        </ul>
        <?php
        echo CHtml::link(
            '<span class="fa fa-edit fa-lg"></span>', 
            $this->createUrl('update', array('id' => $model->id)),
            array(
                'class' => 'edit right',
                'title' => Yii::t('app', 'Edit Profile'),
            )
        );
        ?>
        </div>
        <?php
        // echo $model->getHiddenProfileWidgetMenu ();
    }
?>
</div>

<!-- <div id='create-profile-widget-dialog' class='form' style='display: none;'>
    <label for='' class='left-label'><?php echo Yii::t('app', 'Widget Type: '); ?></label>
    <?php
    $widgetSubtypeOptions = SortableWidget::getWidgetSubtypeOptions ('profile');
    asort ($widgetSubtypeOptions);
    // $widgetSubtypeOptions['ChartWidget'] = "Chart Widget";
    echo CHtml::dropDownList (
        'widgetType', '', $widgetSubtypeOptions);
    ?>
</div>
 -->
<div id='profile-info-contents-container'
 <?php echo ($isMyProfile && $profileInfoMinimized ? 'style="display: none;"' : ''); ?>>
<table id='profile-info' class="details">
    <tr>
        <td class="label" width="20%"><?php echo $attributeLabels['fullName']; ?></td>
        <td><b><?php echo CHtml::encode($model->fullName); ?></b></td>
        <td class='profile-picture-row' rowspan="9" style="text-align:center;">
            <span class="file-wrapper full-profile-info">
            <?php Profile::renderEditableAvatar($model->id);?>
            </span>
        </td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['tagLine']; ?></td>
        <td><?php echo CHtml::encode($model->tagLine); ?></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['username']; ?></td>
        <td><b><?php echo CHtml::encode($model->user->alias); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['officePhone']; ?></td>
        <td><b><?php echo CHtml::encode($model->officePhone); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['cellPhone']; ?></td>
        <td><b><?php echo CHtml::encode($model->cellPhone); ?></b></td>
    </tr>
    <tr>
        <td class="label"><?php echo $attributeLabels['emailAddress']; ?></td>
        <td><b><?php echo CHtml::mailto(CHtml::encode($model->emailAddress)); ?></b></td>
    </tr>
    <tr class='full-profile-details-row' <?php 
     echo (!$isMyProfile || $fullProfileInfo ? '' : 'style="display:none;"'); ?>>
        <td class="label"><?php echo $attributeLabels['googleId']; ?></td>
        <td><b><?php echo CHtml::mailto(CHtml::encode($model->googleId)); ?></b></td>
    </tr>
    <tr class='full-profile-details-row' <?php 
     echo (!$isMyProfile || $fullProfileInfo ? '' : 'style="display:none;"'); ?>>
        <td class="label"><?php echo Yii::t('profile','Signature'); ?></td>
        <td><div style="height:50px;width:0px;float:left;"></div><?php echo $model->getSignature(true); ?></td>
    </tr>
    <!--tr>
        <td id='toggle-full-profile' colspan='2'
         <!--?php echo (!$isMyProfile ? 'style="display:none;"' : ''); ?->
         title='<!--?php echo Yii::t('app', 'Toggle Full Profile Details'); ?->'>| | |</td>
    </tr-->
</table>
</div>
</div>
