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

Yii::import('application.modules.users.models.*');
Yii::import('application.components.util.*');
Yii::import('application.models.*');
Yii::import('application.models.embedded.*');

/**
 * Test for Credentials model class.
 * @package X2CRM.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CredentialsTest extends X2DbTestCase {

    public static function referenceFixtures(){
        return array(
            'users' => 'User',
        );
    }


    public $fixtures = array(
        'credentials' => 'Credentials',
        'defaultCreds' => ':x2_credentials_default',
    );

    public function testInstantiateNew() {
        $cred = new Credentials();
    }

    public function testGetters() {
        $cred = $this->credentials('testUser');
        $this->assertTrue($cred->auth instanceof EmailAccount, 'Was JSONEmbeddedModelFieldsBehavior.afterFind() really run? Because the model field isn\'t an embedded model.');
        $this->assertEquals('Sales Rep',$cred->auth->senderName, "senderName not preserved");
        $this->assertEquals('sales@rep.com',$cred->auth->email, "email not preserved");
        $this->assertEquals(6669,$cred->auth->port, "port not preserved");
        $this->assertEquals('tls',$cred->auth->security, "security not preserved");
        $this->assertEquals('sales@rep.com',$cred->auth->user, "user not preserved"); // Same user as email address
        $this->assertEquals('smtp.rep.com',$cred->auth->server, "server not preserved");
        $this->assertEquals('12345luggage',$cred->auth->password, "password not preserved");
        $default = Credentials::model()->findDefault($this->users('testUser')->id,'email');
        $this->assertEquals(2,$default->id);
        $this->assertEquals('Sales Rep\'s 1st GMail Account',$default->name);
        // As a bonus: test the magic getters. No assertions really necessary or
        // practical to have here because we'd have to declare things in two
        // separate places. We just call to see if an exception is thrown.
        $cred->authModels;
        $cred->authModelLabels;
        $cred->serviceLabels;
        $cred->defaultSubstitutes;
    }

    public function testMakeDefault() {
        $cred = $this->credentials('testUser');
        $cred->makeDefault($this->users('testUser')->id,'email');
        $defaults = $cred->getDefaultCredentials(true);
        $default = Credentials::model()->findDefault($this->users('testUser')->id,'email');
        $this->assertEquals($cred->id,$default->id,'Failed asserting proper function of set-as-default method.');
    }

    /**
     * Tests transparent transition between encrypted and unencrypted
     */
    public function testUnsafe() {
        $cred = $this->credentials('testUser');
        $expectedAttributes = $cred->auth->attributes;
        EncryptedFieldsBehavior::setupUnsafe();
        $cred->save();
        $unencryptedJSON = Yii::app()->db->createCommand()
                ->select('auth')
                ->from('x2_credentials')
                ->where('id=:id',array(':id'=>$cred->id))
                ->queryScalar();
        $attributes = CJSON::decode($unencryptedJSON);
        $this->assertEquals($expectedAttributes,$attributes);

    }
}

?>
