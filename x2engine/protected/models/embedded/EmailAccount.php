<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::import('application.models.embedded.JSONEmbeddedModel');

/**
 * Authentication data and tools for interfacing with a SMTP server, i.e. to
 * send email.
 * 
 * @package X2CRM.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailAccount extends JSONEmbeddedModel {

    public $senderName = '';
    public $email = '';
    public $port = 25;
    public $security = '';
    public $server = '';
    public $user = '';
    public $password = '';

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app', 'Sender Name'),
            'email' => Yii::t('app', 'Email address'),
            'server' => Yii::t('app', 'Server'),
            'port' => Yii::t('app', 'Port'),
            'security' => Yii::t('app', 'Security type'),
            'user' => Yii::t('app', 'User name (if different from email address)'),
            'password' => Yii::t('app', 'Password'),
        );
    }

    public function detailView(){
        echo "\"{$this->senderName}\" &lt;{$this->email}&gt; &nbsp;&bull;&nbsp; {$this->server}:{$this->port}".($this->security != '' ?'&nbsp;&bull;&nbsp;'.Yii::t('app','secured with')." {$this->security}" : '');
    }

    public function modelLabel() {
        return Yii::t('app','Email Account');
    }

    /**
     * Generate the form for the embedded model
     */
    public function renderInputs() {
        foreach($this->attributeNames() as $attr){
            echo CHtml::activeLabel($this, $attr,array('for'=>$this->resolveName($attr)));
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
                case 'port':
                    echo CHtml::activeNumberField($this, $attr, $this->htmlOptions($attr));
                    break;
                case 'security':
                    echo CHtml::activeDropDownList($this, $attr,array(''=>'None','tls'=>'TLS','ssl'=>'SSL'), $this->htmlOptions($attr));
                    break;
                case 'user':
                    echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                    break;
                case 'password':
                    echo CHtml::activePasswordField($this, $attr, $this->htmlOptions($attr));
                    break;
            }
        }
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
            array('senderName,server,port,security,user,email,password','safe'),
        );
    }

}

?>
