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
 * Authentication data for using a Google account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package application.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class GMailAccount extends EmailAccount {

    public $email = '';
    public $imapNoValidate = false;
    public $imapPort = 993;
    public $imapSecurity = 'ssl';
    public $imapServer = 'imap.gmail.com';
    public $password = '';
    public $port = 587;
    public $security = 'tls';
    public $senderName = '';
    public $server = 'smtp.gmail.com';
    public $user = '';
    public $disableInbox = false;

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app','Sender Name'),
            'email' => Yii::t('app','Google ID'),
            'password' => Yii::t('app','Password'),
            'imapPort' => Yii::t('app','IMAP Port'),
            'imapServer' => Yii::t('app','IMAP Server'),
            'imapSecurity' => Yii::t('app','IMAP Security'),
            'imapNoValidate' => Yii::t('app','Disable SSL Validation'),
            'disableInbox' => Yii::t('app','Disable Email Inbox'),
        );
    }

    public function modelLabel() {
        return Yii::t('app','Google Email Account');
    }

    public function renderInput ($attr) {
        switch($attr){
            case 'email':
                echo '<p class="fieldhelp-thin-small">'.Yii::t('app', '(example@gmail.com)').
                    '</p>';
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'password':
                echo X2Html::x2ActivePasswordField ($this, $attr, $this->htmlOptions ($attr), true);
                break;
            default:
                parent::renderInput ($attr);
        }
    }

    public function renderInputs(){
        $this->password = null;
        echo CHtml::activeLabel($this, 'senderName');
        $this->renderInput ('senderName');
        echo CHtml::activeLabel($this, 'email');
        $this->renderInput ('email');
        echo CHtml::activeLabel($this, 'password');
        $this->renderInput ('password');
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

    public function rules(){
        return array(
            array('email','email'),
            array('senderName,email,password', 'required'),
            array('senderName,email,password,imapPort,imapSecurity,imapNoValidate,imapServer,disableInbox', 'safe'),
        );
    }

}

?>
