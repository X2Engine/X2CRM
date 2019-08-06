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




Yii::import('application.modules.users.models.*');
Yii::import('application.components.util.*');
Yii::import('application.models.*');
Yii::import('application.models.embedded.*');

/**
 * Test for Credentials model class.
 * @package application.tests.unit.models
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
        $this->assertEquals(4,$default->id);
        $this->assertEquals('Sales Rep\'s Backup Email Account',$default->name);
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
