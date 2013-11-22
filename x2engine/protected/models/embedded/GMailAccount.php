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

Yii::import('application.models.embedded.*');

/**
 * Authentication data for using a Google account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package X2CRM.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class GMailAccount extends EmailAccount {

    public $senderName = '';
    public $email = '';
    public $password = '';
    public $port = 587;
    public $security = 'tls';
    public $server = 'smtp.gmail.com';
    public $user = '';

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app','Sender Name'),
            'email' => Yii::t('app','Google ID'),
            'password' => Yii::t('app','Password'),
        );
    }

    public function modelLabel() {
        return Yii::t('app','Google Email Account');
    }

    public function renderInputs(){
        foreach($this->attributeNames() as $attr){
            echo CHtml::activeLabel($this, $attr);
            switch($attr){
                case 'senderName':
                    echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                    break;
                case 'email':
                    echo '<p class="fieldhelp-thin-small">'.Yii::t('app', '(example@gmail.com)').
                        '</p>';
                    echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                    break;
                case 'password':
                    echo CHtml::activePasswordField($this, $attr, $this->htmlOptions($attr));
                    break;
            }
        }
        echo CHtml::errorSummary($this);

    }

    public function rules(){
        return array(
            array('email','email'),
            array('senderName,email,password', 'required'),
            array('senderName,email,password', 'safe'),
        );
    }

}

?>
