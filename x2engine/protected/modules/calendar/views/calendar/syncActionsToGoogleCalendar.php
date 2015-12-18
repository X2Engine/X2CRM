<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
?>

<?php

$actionsTitle = Modules::displayName(true, "Actions");

$menuOptions = array(
    'index', 'myPermissions', 'sync',
);
$this->insertMenu($menuOptions);

?>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'calendar-form',
    'enableAjaxValidation' => false,
        ));
?>

<?php
Yii::app()->clientScript->registerScript('syncSaveChanges', "
$(function() {
	$('#calendar-form').find('select').change(function() {
		$('#save-button').addClass('highlight'); // highlight button when user changes a field in the form
	});
});
", CClientScript::POS_HEAD);
?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
    <div class="formSection">
        <div class="formSectionHeader">
            <span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
        </div>
    </div>
</div>
<div class="errorSummary">
    <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message){
        echo '<div class="flash-'.$key.'">'.$message."</div>\n";
    }
    ?>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
    <table frame="border">
        <td>
            <?php if($googleIntegration){ 
                if(empty($errors) && !$auth->getErrors() && $auth->getAccessToken()){ 
                    echo $form->labelEx($model, 'googleCalendarName');
                    echo $form->dropDownList($model, 'syncGoogleCalendarId', $googleCalendarList); 
                    ?>
                    <br />
                    <?php 
                    if(isset($syncGoogleCalendarName) && $syncGoogleCalendarName){ 
                        echo Yii::t(
                            'calendar',
                            'Your {actions} are being synced to the Google Calendar '.
                            '"{calendarName}".',
                            array(
                                '{calendarName}' => $syncGoogleCalendarName,
                                '{actions}' => lcfirst($actionsTitle),
                            )); 
                    ?> <br />
                    <?php } 
                    echo CHtml::link(
                        Yii::t('calendar', "Don't Sync My {actions} To Google Calendar", array(
                            '{actions}' => $actionsTitle,
                        )),
                        $this->createUrl('').'?unlinkGoogleCalendar'); 
                }else{ 
                    echo CHtml::link(
                        Yii::t('calendar', "Sync My {actions} To Google Calendar", array(
                            '{actions}' => $actionsTitle,
                        )),
                        $auth->getAuthorizationUrl(null)); 
                } 
            }else{
                echo Yii::t('calendar', 'Google Integration is not configured on this server.');
            }
            ?>
        </td>
    </table>
</div>

<?php
if(empty($errors) && $googleIntegration && !$auth->getErrors() && $auth->getAccessToken()){
    echo '	<div class="row buttons">'."\n";
    echo '		'.CHtml::submitButton(
        Yii::t('app', 'Enable Sync'), 
        array('class' => 'x2-button', 'id' => 'save-button', 'tabindex' => 24))."\n";
    echo "	</div>\n";
}
$this->endWidget();
?>
