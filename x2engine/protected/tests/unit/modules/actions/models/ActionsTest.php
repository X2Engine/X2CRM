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

Yii::import('application.modules.actions.models.*');

/**
 * Test for the Actions class
 * @package X2CRM.tests.unit.modules.actions.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionsTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array('actions'=>'Actions');
    }

    /**
     * Test special validation that avoids empty association when the type is
     * something meant to be associated, i.e. a logged call, note, etc.
     */
    public function testValidate() {
        $action = new Actions();
        $action->type = 'call';
        $action->actionDescription = 'Contacted. Will call back later';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
        // Do the same thing but with "None" association type. Validation should fail.
        $action = new Actions();
        $action->type = 'call';
        $action->associationType = 'None';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
    }
}

?>
