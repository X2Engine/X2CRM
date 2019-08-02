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






Yii::import('application.modules.actions.models.*');

class AnonContactTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.FingerprintTest'),
        'anonContacts' => array ('AnonContact', '.FingerprintTest'),
        'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
    );

    public function testBeforeSave () {
        $lastModifiedId = Yii::app()->db->createCommand()
            ->select('id')
            ->from('x2_anon_contact')
            ->order('lastUpdated ASC')
            ->queryScalar();

        $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
        $this->assertNotEquals (null, $anonContact);
        $action = new Actions;
        $action->setAttributes (array (
            'id' => 1000000000,
            'associationType' => 'anoncontact',
            'associationId' => $lastModifiedId,
            'createDate' => time (),
            'lastUpdated' => time (),
            'completeDate' => time (),
        ), false);
        $this->assertSaves ($action);

        // set max to 0 to check if anon contact gets deleted on before save event
        Yii::app()->settings->maxAnonContacts = 0;
        $anonContact = new AnonContact;
        $anonContact->setAttributes (array (
            'trackingKey' => 'test',
            'createDate' => time (),
            'fingerprintId' => 20000,
        ), false);
        $this->assertSaves ($anonContact);

        $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
        $action = X2Model::model('Actions')->findByPk($action->id);
        // should have deleted this anon contact before the new anon contact was saved
        $this->assertEquals (null, $anonContact);
        // should also have deleted action associated with deleted anon contact
        $this->assertEquals (null, $action);
    }

    public function testAfterDelete () {
        $anonContact = $this->anonContacts ('anonContact1');
        $action = new Actions;
        $action->setAttributes (array (
            'subject' => 'test',
            'associationName' => $anonContact->id,
            'associationId' => $anonContact->id,
            'associationType' => 'anoncontact',
        ), false);
        $this->assertSaves ($action);

        $this->assertTrue ($anonContact->delete ());
        // ensure that associated action gets deleted with the anon contact
        $this->assertEquals (null,  Actions::model ()->findByPk ($action->id));
    }


}

?>
