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




Yii::import('application.models.embedded.*');

/**
 * Authentication data for connecting to X2Hub services
 *
 * @package application.models.embedded
 */
class X2HubConnector extends JSONEmbeddedModel implements AdminOwnedCredentials {

    public static function getAdminProperty() {
        return 'hubCredentialsId';
    }

    public function getMetaData() {
        return array(
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'X2 Hub Services',
        );
    }

    public $unique_id = false;
    public $hubEnabled = false;
    public $enableGoogleCalendar = true;
    public $enableGoogleMaps = true;
    public $enableTwoFactor = true;
    public $enableGoogleSpeech = false;
    public $enableLinkedIn = true;
    public $enableDropbox = true;

    public function rules() {
        return array(
            array('unique_id,hubEnabled,enableGoogleCalendar,enableGoogleMaps,enableTwoFactor,enableLinkedIn, enableDropbox', 'safe'),
        );
    }

    public function getProtectedFields() {
        return array();
    }

    public function renderForm() {
        $this->renderInputs();
        echo '<br />';
        echo '<br />';
    }

    public function attributeLabels() {
        return array(
            'unique_id' => Yii::t('app', 'X2 Hub Product Key'),
            'hubEnabled' => Yii::t('app', 'Hub Enabled'),
            'enableGoogleCalendar' => Yii::t('app', 'Enable Google Calendar Sync'),
            'enableGoogleMaps' => Yii::t('app', 'Enable Google Maps'),
            'enableGoogleSpeech' => Yii::t('app', 'Enable Google Speech'),
            'enableTwoFactor' => Yii::t('app', 'Enable 2 Factor Authentication'),
            'enableLinkedIn' => Yii::t('app', 'Enable LinkedIn Integration'),
            'enableDropbox' => Yii::t('app', 'Enable Dropbox Integration'),
        );
    }

    public function getPageTitle() {
        return $this->modelLabel();
    }

    public function modelLabel() {
        return Yii::t('app', 'X2 Hub Services');
    }

    public function htmlOptions($name, $options = array()) {
        return parent::htmlOptions($name, $options);
    }

    public function renderInput($field) {
        switch ($field) {
            case 'hubEnabled':
            case 'enableGoogleCalendar':
            case 'enableLinkedIn':
            case 'enableDropbox':
            case 'enableGoogleMaps':
            case 'enableTwoFactor':
                echo CHtml::hiddenField('Credentials[auth][' . $field . ']', 0);
                echo CHtml::checkBox('Credentials[auth][' . $field . ']', $this->$field);
                break;
            case 'unique_id':
                echo CHtml::textField('Credentials[auth][' . $field . ']', $this->$field);
                break;
            case 'enableGoogleSpeech': // unreleased
                //echo CHtml::hiddenField('Credentials[auth]['.$field.']', 0);
                echo CHtml::checkBox('Credentials[auth][' . $field . ']', $this->$field, array('disabled' => 'disabled', 'style' => 'opacity:0.5', 'title' => Yii::t('app', 'Coming Soon!')));
                break;
            default:
                parent::renderInput($field);
                break;
        }
    }

    public function renderInputs() {
        Yii::app()->clientScript->registerCss('hubConfigStyling', '
            #hubServiceConfig input[type="checkbox"] {
                margin-right: 8px;
            }
            #hubServiceConfig label {
                display: inline;
            }
            #hubServiceConfig label:after {
                content: "";
                display: block;
            }
        ');

        echo $this->getInstructions();

        echo CHtml::tag('h3', array(), $this->exoModel->getAttributeLabel($this->exoAttr));
        echo '<hr />';
        echo CHtml::activeLabel($this, 'unique_id');
        $this->renderInput('unique_id');
        echo CHtml::activeLabel($this, 'hubEnabled');
        $this->renderInput('hubEnabled');

        echo CHtml::tag('h4', array(), Yii::t('', 'Services'));
        echo '<div id="hubServiceConfig">';
        $this->renderInput('enableTwoFactor');
        echo CHtml::activeLabel($this, 'enableTwoFactor');
        $this->renderInput('enableGoogleCalendar');
        echo CHtml::activeLabel($this, 'enableGoogleCalendar');
        $this->renderInput('enableGoogleMaps');
        echo CHtml::activeLabel($this, 'enableGoogleMaps');
        $this->renderInput('enableLinkedIn');
        echo CHtml::activeLabel($this, 'enableLinkedIn');
        $this->renderInput('enableDropbox');
        echo CHtml::activeLabel($this, 'enableDropbox');
        $this->renderInput('enableGoogleSpeech');
        echo CHtml::activeLabel($this, 'enableGoogleSpeech');
        echo '</div>';

        echo CHtml::errorSummary($this);
        echo '<br>';
        echo '<br>';
    }

    private function getInstructions() {
        $enabled = false;
        if (Yii::app()->settings->hubCredentialsId && $this->hubEnabled) {
            $enabled = Yii::app()->controller
                    ->attachBehavior('HubConnectionBehavior', new HubConnectionBehavior)
                    ->pingHub();
        }
        return
                '<h3>' . Yii::t('app', 'Configuring X2 Hub Services') . '</h3>
            <hr>
            <div>' . Yii::t('app', 'Status') . ': ' .
                ($enabled ?
                        '<span style="color:green">' . X2Html::fa('check') . Yii::t('app', 'Enabled') . '</span>' :
                        '<span style="color:red">' . X2Html::fa('times') . Yii::t('app', 'Disabled')) . '</span>' .
                '</div><br />
            <p>' .
                Yii::t('app', 'Enter your X2 Hub product key below to enable external connectivity ' .
                        'through the X2 Hub Services. Once you have configured your settings, ' .
                        'other connectors will be automatically configured and utilized through ' .
                        'X2 Hub, including Google Maps integration and two factor auth support.'
                ) .
                '</p>';
    }

}

?>
