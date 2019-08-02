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






/**
 * Create/update a personal inbox
 * @param X2Model $model The email inbox
 */

 ?>
<div class='form'>
<?php
$retDict = Credentials::getCredentialOptions (
    $model, 'credentialId', 'email', Yii::app()->user->id, array(), true, true);
$credentials = $retDict['credentials'];
$htmlOptions = $retDict['htmlOptions'];
$credentialsNotAdded = false;
if (!count ($credentials)) {
    $credentialsNotAdded = true;
    $credentials[-1] = CHtml::encode (Yii::t('emailInboxes', 'Select one'));
}


$form = $this->beginWidget ('X2ActiveForm', array (
    'id' => 'my-inbox-form',
    'htmlOptions' => array (
        'class' => 'form2',
    )
));
    echo $form->errorSummary($model);
    echo $form->label ($model, 'name');
    echo $form->textField ($model, 'name');
    echo $form->label ($model, 'credentialId');
    echo CHtml::activeDropDownList (
        $model, 'credentialId', $credentials, $htmlOptions);
    echo '<br>';
    echo $form->label ($model, 'password', array (
        'style' => 'display: inline-block;'
    ));
    echo X2Html::hint2 (
        Yii::t('app', 'Re-enter your password for the selected email credentials'), array (

        'style' => 'display: inline-block;'
    ));
    echo "<br>";
    echo X2Html::x2ActivePasswordField ($model, 'password', array (
    ), true);
    if ($credentialsNotAdded) {
        echo "
            <div> 
            ".CHtml::encode (
                Yii::t('emailInboxes', 'You have not added your email credentials.')).
                "&nbsp;<a href='".Yii::app()->createUrl ('/profile/manageCredentials')."'>".
                    CHtml::encode (Yii::t('emailInboxes', 'Add email credentials')).
                "</a>
            </div>";
    }
    echo '<div class="bs-row">';
        echo $form->checkBox ($model, 'settings[logOutboundByDefault]', array (
            'class' => 'left-input',
        ));
        echo $form->label ($model, 'settings[logOutboundByDefault]', array (
            'class' => 'right-label',
        ));
        echo X2Html::hint2 (EmailInboxes::getAutoLogEmailsDescription ());
    echo '</div>';
    echo '<div class="bs-row">';
        echo $form->checkBox ($model, 'settings[logInboundByDefault]', array (
            'class' => 'left-input',
        ));
        echo $form->label ($model, 'settings[logInboundByDefault]', array (
            'class' => 'right-label',
        ));
        echo X2Html::hint2 (EmailInboxes::getAutoLogEmailsDescription ('in'));
    echo '</div>';
    echo '<div class="bs-row">';
        echo $form->checkBox ($model, 'settings[disableQuota]', array (
            'class' => 'left-input',
        ));
        echo $form->label ($model, 'settings[disableQuota]', array (
            'class' => 'right-label',
        ));
    echo '</div>';


    echo $form->label ($model, 'settings[copyToSent]', array (
        'style' => 'display: inline-block;'
    ));
    echo X2Html::hint2 (
        Yii::t('app', 'Specify the folder to save sent messages to. This may be handled '.
                'automatically by some Email Service Providers, such as GMail'), array (
        'style' => 'display: inline-block;'
    ));
    echo '<br/>';
    echo CHtml::activeDropDownList ($model,'settings[copyToSent]', $model->copyToSentOptions);
    echo '<br/>';
    echo '<br/>';
    echo '<div class="row buttons">'.
        CHtml::submitButton(
            $model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),
            array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)).
        '</div>';
$this->endWidget ();
?>
</div>
