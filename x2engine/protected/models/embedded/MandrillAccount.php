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
 * Authentication data for using a Mandrill account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package application.models.embedded
 */
class MandrillAccount extends EmailAccount {

    public $email = '';
    public $password = '';
    public $port = 587;
    public $security = 'tls';
    public $senderName = '';
    public $server = 'smtp.mandrillapp.com';
    public $user = '';

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app','Sender Name'),
            'user' => Yii::t('app', 'Mandrill Username'),
            'email' => Yii::t('app','Sender Address'),
            'password' => Yii::t('app','API Key'),
        );
    }

    public function modelLabel() {
        return Yii::t('app','Mandrill Email Account');
    }

    public function renderInput ($attr) {
        switch($attr){
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
        echo CHtml::activeLabel($this, 'user');
        $this->renderInput ('user');
        echo CHtml::activeLabel($this, 'password');
        $this->renderInput ('password');
        echo '<br/>';
        echo CHtml::errorSummary($this);
    }

    public function rules(){
        return array(
            array('email', 'email'),
            array('senderName,email,password', 'required'),
            array('senderName,email,password', 'safe'),
        );
    }

}

?>
