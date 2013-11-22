<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
