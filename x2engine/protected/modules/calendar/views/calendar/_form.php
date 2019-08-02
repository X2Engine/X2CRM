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




Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() . '/js/multiselect/css/ui.multiselect.css', 'screen, projection');
Yii::app()->clientScript->registerCss('userPermissionCss', "
.user-permission {
    width: 460px;
    height: 200px;
}
#switcher {
    margin-top: 20px;
}
", 'screen, projection');
Yii::app()->clientScript->registerScript('userCalendarPermission', "
$(function() {
    $('.user-permission').multiselect();
});
", CClientScript::POS_HEAD);
$users = array_map (array('CHtml', 'encode'), $users);
?>
<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'calendar-form',
        'enableAjaxValidation' => false,
    ));
    ?>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'name');
        echo $form->textField($model, 'name');
        echo $form->error($model, 'name');
        ?>
    </div>
    <br>
    <?php
    $viewPermission = $model->getUserIdsWithViewPermission();
    $editPermission = $model->getUserIdsWithEditPermission();
    ?>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'viewPermission')
        ?>
        <?php
        echo CHtml::listBox('view-permission', $viewPermission, $users, array(
            'class' => 'user-permission',
            'multiple' => 'multiple',
            'onChange' => 'giveSaveButtonFocus();',
        ));
        ?>
        <br>
    </div>
    <div class="row">
        <?php
        echo $form->labelEx($model, 'editPermission');
        ?>
        <?php
        echo CHtml::listBox('edit-permission', $editPermission, $users, array(
            'class' => 'user-permission',
            'multiple' => 'multiple',
            'onChange' => 'giveSaveButtonFocus();',
        ));
        ?>
    </div>
    <?php
    //dropdown to pick either google or Outlook. Can't do both.
    if( $googleIntegration && $outlookIntegration ){
        if($client->getAccessToken() && $clientOutlook->getAccessToken()){
            echo "<br>";
            echo $form->labelEx($model, 'syncType');
            $syncTypes = array('google' => 'Google Calendar', 'outlook' => 'Outlook Calendar');
            echo $form->dropDownList($model, 'syncType', $syncTypes, array('empty'=>Yii::t('calendar','Select an integration')));
        }
    }elseif($googleIntegration){
        if($client->getAccessToken()){
        echo "<br>";
            echo $form->labelEx($model, 'syncType');
            $syncTypes = array('google' => 'Google Calendar');
            echo $form->dropDownList($model, 'syncType', $syncTypes, array('empty'=>Yii::t('calendar','Select an integration'))); 
        }    
    }elseif($outlookIntegration){
        if($clientOutlook->getAccessToken()){
            echo "<br>";
            echo $form->labelEx($model, 'syncType');
            $syncTypes = array('outlook' => 'Outlook Calendar');
            echo $form->dropDownList($model, 'syncType', $syncTypes, array('empty'=>Yii::t('calendar','Select an integration')));
        } 
    }     
    ?>
    <?php
    //google Version
    // REMEMBER THEY CAN ONLY PICK EITHER OR
    if ($googleIntegration || $hubCalendaring) {
        ?>
        <div class="form">
            <br>
            <h3><?php echo Yii::t('calendar', 'Google Calendar Sync');?></h3>
        </div>

        <div class="form">
            <table>
                <td>

                    <?php if ($client->getAccessToken()) { ?>
                        <?php
                        //$model->syncType = 'google';
                        $model->remoteSync = 1;
                        echo $form->hiddenField($model, 'remoteSync');
                        //echo $form->hiddenField($model, 'syncType');
                        ?>
                        <?php
                        echo $form->labelEx($model, 'remoteCalendarId');
                        ?>
                        <?php
                        echo $form->dropDownList($model, 'remoteCalendarId', $googleCalendarList, array('empty'=>Yii::t('calendar','Select a calendar')));
                        ?>
                    <?php } else { ?>
                        <?php
                        echo CHtml::link(Yii::t('calendar', "Link to Google Calendar"), $client->getAuthorizationUrl('calendar'),array('class'=>'x2-button'));
                        ?>
                    <?php } ?>
                </td>
            </table>
            <br>
        </div>
    <?php } ?>
    
    <?php
    //outlook Version
    if ($outlookIntegration) {
        ?>
        <div class="form">
            <br>
            <h3><?php echo Yii::t('calendar', 'Outlook Calendar Sync');?></h3>
        </div>

        <div class="form">
            <table>
                <td>

                    <?php if ($clientOutlook->getAccessToken()) { ?>
                        <?php
                        //$model->syncType = 'outlook';
                        $model->remoteSync = 1;
                        echo $form->hiddenField($model, 'remoteSync');
                        //echo $form->hiddenField($model, 'syncType');
                        ?>
                        <?php
                        echo $form->labelEx($model, 'remoteCalOutlook');
                        ?>
                        <?php
                        echo $form->dropDownList($model, 'remoteCalOutlook', $outlookCalendarList, array('empty'=>Yii::t('calendar','Select a calendar')));
                        ?>
                    <?php } else { ?>
                        <?php
                        echo CHtml::link(Yii::t('calendar', "Link to Outlook Calendar"), $clientOutlook->getAuthorizationUrl(),array('class'=>'x2-button'));                           
                        ?>
                    <?php } ?>
                </td>
            </table>
            <br>
        </div>
    <?php } ?>
    
    <?php
    echo '	<div class="row buttons">' . "\n";
    echo '		' . CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button', 'tabindex' => 24)) . "\n";
    echo "	</div>\n";
    $this->endWidget();
    ?>
</div>
