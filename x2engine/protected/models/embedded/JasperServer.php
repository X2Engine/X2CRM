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
 * Authentication data for using external reports from a Jasper Server.
 *
 * @package application.models.embedded
 */
class JasperServer extends JSONEmbeddedModel implements AdminOwnedCredentials {
    
    public static function getAdminProperty () {
        return 'jasperCredentialsId'; 
    }

    public function getMetaData () {
        return array (
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'Jasper Server',
        );
    }

    public $server = '';
    public $username = '';
    public $password = '';

    public function rules(){
        return array(
            array('server,username,password', 'safe'),
        );
    }

    public function getProtectedFields () {
        return array ('password');
    }

    public function renderForm () {
		$this->renderInputs();
		echo '<br />';
		echo '<br />';
    }

    public function attributeLabels(){
        return array(
            'server' => Yii::t('app','Jasper Server URL'),
            'username' => Yii::t('app','Username'),
            'password' => Yii::t('app','Password'),
        );
    }

    public function getPageTitle () {
        return $this->modelLabel ();
    }

    public function modelLabel() {
        return Yii::t('app','Jasper Server Integration');
    }

    public function htmlOptions ($name, $options=array ()) {
        return X2Html::mergeHtmlOptions (
            parent::htmlOptions ($name, $options), array ('class' => 'twitter-credential-input'));
    }

    public function renderInputs(){
        echo $this->getInstructions ();
		echo CHtml::tag ('h3', array (), $this->exoModel->getAttributeLabel ($this->exoAttr));
		echo '<hr />';
        echo CHtml::activeLabel($this, 'server');
        $this->renderInput ('server');
        echo CHtml::activeLabel($this, 'username');
        $this->renderInput ('username');
        echo CHtml::activeLabel($this, 'password');
        $this->renderInput ('password');
        echo CHtml::errorSummary($this);
        echo '<br>';
        echo '<br>';
    }

    private function getInstructions () {
        return 
            '
            <h3>'.Yii::t('app', 'Configuring Jasper Server Integration').'</h3>
            <hr>
            <ol>
                <li>'.Yii::t('app', 'Provide the full URL to your Jasper Server, for example, {link}.', array (
                    '{link}' => 
                        '<a href="http://example.com:8080/jasperserver">http://example.com:8080/jasperserver</a>'
                )).'
                </li>
                <li>'.Yii::t ('app', 'Enter the username and password to authenticate with the server. It is recommended to create a reduced permissions user in Jasper to connect read-only.').'
                </li>
                <li>'.Yii::t('app', 'Save your X2CRM Jasper Server Integration settings.').'</li>
            </ol>
            ';
    }

}

?>
