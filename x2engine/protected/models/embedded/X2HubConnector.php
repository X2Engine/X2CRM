<?php

/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

Yii::import('application.models.embedded.*');

/**
 * Authentication data for connecting to X2Hub services
 *
 * @package application.models.embedded
 */
class X2HubConnector extends JSONEmbeddedModel implements AdminOwnedCredentials {
    
    public static function getAdminProperty () {
        return 'hubCredentialsId'; 
    }

    public function getMetaData () {
        return array (
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'X2Hub Integration',
        );
    }

    public $unique_id = '';

    public function rules(){
        return array(
            array('unique_id', 'safe'),
        );
    }

    public function getProtectedFields () {
        return array ();
    }

    public function renderForm () {
		$this->renderInputs();
		echo '<br />';
		echo '<br />';
    }

    public function attributeLabels(){
        return array(
            'unique_id' => Yii::t('app','Product Key'),
        );
    }

    public function getPageTitle () {
        return $this->modelLabel ();
    }

    public function modelLabel() {
        return Yii::t('app','X2Hub Integration');
    }

    public function htmlOptions ($name, $options=array ()) {
        return parent::htmlOptions ($name, $options);
    }

    public function renderInputs(){
        echo $this->getInstructions ();
		echo CHtml::tag ('h3', array (), $this->exoModel->getAttributeLabel ($this->exoAttr));
		echo '<hr />';
        echo CHtml::activeLabel($this, 'unique_id');
        $this->renderInput ('unique_id');
        echo CHtml::errorSummary($this);
        echo '<br>';
        echo '<br>';
    }

    private function getInstructions () {
        return 
            '
            <h3>'.Yii::t('app', 'Configuring X2Hub Integration').'</h3>
            <hr>
            <p>'.
                Yii::t('app',
                    'Enter your X2Hub product key below to enable external connectivity '.
                    'through the X2Hub Services. Once you have configured your settings, '.
                    'other connectors will be automatically configured and utilized through '.
                    'X2Hub, including Google Integration, Twitter Integration, etc.'
                ).
            '</p>
            ';
    }

}

?>
