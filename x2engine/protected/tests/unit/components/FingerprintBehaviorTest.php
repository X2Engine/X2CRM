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






Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.models.*');

/**
 * @package application.tests.unit.modules.contacts.models
 */
class FingerprintBehaviorTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.FingerprintTest'),
        'anonContacts' => array ('AnonContact', '.FingerprintTest'),
        'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
    );

    public function testAfterDelete () {
        // test Contacts afterDelete
        $contact = $this->contacts ('contact1');
        $fingerprint = $contact->fingerprint;
        $this->assertTrue ($contact->fingerprint instanceof Fingerprint);
        $this->assertTrue ($contact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

        // test AnonContact afterDelete
        $anonContact = $this->anonContacts ('anonContact2');
        $fingerprint = $anonContact->fingerprint;
        $this->assertTrue ($anonContact->fingerprint instanceof Fingerprint);
        $this->assertTrue ($anonContact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

        // ensure that fingerprint isn't deleted if it's shared by an anonymous contact
        $anonContact = $this->anonContacts ('anonContact3');
        $contact = $this->contacts ('contact2');
        $fingerprint = $anonContact->fingerprint;
        $this->assertTrue ($fingerprint instanceof Fingerprint);
        $contact->fingerprintId = $fingerprint->id;
        $this->assertTrue ($contact->save ());
        $this->assertTrue ($anonContact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertTrue ($fingerprint instanceof Fingerprint);

        // ensure that fingerprint isn't deleted if it's shared by a contact
        $contact = $this->contacts ('contact2');
        $anonContact = $this->anonContacts ('anonContact1');
        $fingerprint = $contact->fingerprint;
        $this->assertTrue ($fingerprint instanceof Fingerprint);
        $anonContact->fingerprintId = $fingerprint->id;
        $this->assertTrue ($anonContact->save ());
        $this->assertTrue ($contact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertTrue ($fingerprint instanceof Fingerprint);
    }

    public function testAfterDeleteByPk () {
        $anonContact = $this->anonContacts ('anonContact2');
        $fingerprint = $anonContact->fingerprint;
        X2Model::model('AnonContact')->findByPk ($anonContact->id)->delete ();
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

    }

}

?>
