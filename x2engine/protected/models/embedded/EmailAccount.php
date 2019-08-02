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




Yii::import('application.models.embedded.JSONEmbeddedModel');

/**
 * Authentication data and tools for interfacing with a SMTP server, i.e. to
 * send email.
 * 
 * @package application.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailAccount extends JSONEmbeddedModel {

    public $email = '';
    public $smtpNoValidate = false;
    public $imapNoValidate = false;
    public $imapPort = 143;
    public $imapSecurity = '';
    public $imapServer = '';
    public $password = '';
    public $port = 25;
    public $security = '';
    public $senderName = '';
    public $server = '';
    public $user = '';
    public $enableVerification = true;
    public $disableInbox = false;

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app', 'Sender Name'),
            'email' => Yii::t('app', 'Email address'),
            'server' => Yii::t('app', 'Server'),
            'port' => Yii::t('app', 'Port'),
            'security' => Yii::t('app', 'Security type'),
            'user' => Yii::t('app', 'User name (if different from email address)'),
            'password' => Yii::t('app', 'Password'),
            'smtpNoValidate' => Yii::t('app','Disable SSL Validation'),
            'imapPort' => Yii::t('app','IMAP Port'),
            'imapServer' => Yii::t('app','IMAP Server'),
            'imapSecurity' => Yii::t('app','IMAP Security'),
            'imapNoValidate' => Yii::t('app','Disable SSL Validation'),
            'disableInbox' => Yii::t('app','Disable Email Inbox'),
        );
    }

    public function detailView(){
        echo "\"{$this->senderName}\" &lt;{$this->email}&gt; &nbsp;&bull;&nbsp; {$this->server}:{$this->port}".($this->security != '' ?'&nbsp;&bull;&nbsp;'.Yii::t('app','secured with')." {$this->security}" : '');
    }

    public function modelLabel() {
        return Yii::t('app','Email Account');
    }

    public function renderInput ($attr) {
        switch($attr){
            case 'senderName':
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'email':
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'server':
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'imapServer':
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'port':
                echo CHtml::activeNumberField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'imapPort':
                echo CHtml::activeNumberField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'security':
                echo CHtml::activeDropDownList($this, $attr,array(''=>'None','tls'=>'TLS','ssl'=>'SSL'), $this->htmlOptions($attr));
                break;
            case 'imapSecurity':
                echo CHtml::activeDropDownList($this, $attr,array(''=>'None','tls'=>'TLS','ssl'=>'SSL'), $this->htmlOptions($attr));
                break;
            case 'imapNoValidate':
                echo CHtml::activeCheckBox($this, $attr, $this->htmlOptions($attr));
                break;
            case 'smtpNoValidate':
                echo CHtml::activeCheckBox($this, $attr, $this->htmlOptions($attr));
                break;
            case 'disableInbox':
                echo CHtml::activeCheckBox($this, $attr, $this->htmlOptions($attr));
                break;
            case 'user':
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'password':
                echo X2Html::x2ActivePasswordField ($this, $attr, $this->htmlOptions ($attr), true);
                break;
        }
    }

    /**
     * Renders a label and input for disabling SMTP SSL Validation
     * This can be overridden in child classes to hide the controls
     * and require SSL validation, e.g., for provider-specific accounts
     */
    public function renderSmtpSslValidation() {
        echo CHtml::activeLabel($this, 'smtpNoValidate');
        $this->renderInput ('smtpNoValidate');
    }

    /**
     * Generate the form for the embedded model
     */
    public function renderInputs() {
        $this->password = null;
        echo CHtml::activeLabel ($this, 'senderName');
        $this->renderInput ('senderName');
        echo CHtml::activeLabel ($this, 'email');
        $this->renderInput ('email');
        echo CHtml::activeLabel ($this, 'server');
        $this->renderInput ('server');
        echo CHtml::activeLabel ($this, 'port');
        $this->renderInput ('port');
        echo CHtml::activeLabel ($this, 'security');
        $this->renderInput ('security');
        echo CHtml::activeLabel ($this, 'user');
        $this->renderInput ('user');
        echo CHtml::activeLabel ($this, 'password');
        $this->renderInput ('password');
        $this->renderSmtpSslValidation();
        echo '<br/>';
        echo '<br/>';
		echo CHtml::tag ('h3', array (), Yii::t('app', 'IMAP Configuration'));
        echo '<hr/>';
        echo CHtml::activeLabel($this, 'imapPort');
        $this->renderInput ('imapPort');
        echo CHtml::activeLabel($this, 'imapSecurity');
        $this->renderInput ('imapSecurity');
        echo CHtml::activeLabel($this, 'imapNoValidate');
        $this->renderInput ('imapNoValidate');
        echo CHtml::activeLabel($this, 'imapServer');
        $this->renderInput ('imapServer');
        echo CHtml::activeLabel($this, 'disableInbox');
        $this->renderInput ('disableInbox');
        echo CHtml::errorSummary($this);
    }

    /**
     * Substitutes email address as username if username is empty
     * @param type $attribute
     * @param type $params
     */
    public function emailUser($attribute,$params=array()) {
        if(empty($this->$attribute) && !empty($this->email))
            $this->$attribute = $this->email;
    }

    public function rules() {
        return array(
            array('port','numerical','integerOnly'=>1,'min'=>1),
            array('email','email'),
            array('user','emailUser'),
            array('server,user,email','length','min'=>1,'max'=>500,'allowEmpty'=>0),
            array('password','required'),
            array('senderName,server,port,security,user,email,password,imapPort,imapServer,imapSecurity,smtpNoValidate,imapNoValidate,disableInbox','safe'),
        );
    }

}

?>
