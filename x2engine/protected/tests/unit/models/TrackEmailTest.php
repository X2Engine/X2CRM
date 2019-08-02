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
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * Tests on 3 different model classes to verify that a selection of them work.
 */
class TrackEmailTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'quote' => 'Quote',
            'contacts' => 'Contacts',
            'accounts' => 'Accounts',
        );
    }

    public $fixtures = array(
        'actions' => 'Actions',
        'actionText' => 'ActionText',
        'trackEmail' => 'TrackEmail',
        'events' => 'Events',
    );
    
    public function testContactEmailOpen(){
        $contact = $this->contacts('testAnyone');
        $this->assertNullEmailOpenAction($contact);
        $contactInitialActionCount = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_actions')
                ->where('associationType = :type AND associationId = :id',
                        array(
                    ':type' => $contact->module,
                    ':id' => $contact->id,
                ))
                ->queryScalar();

        $action = new Actions();
        $action->completedBy = 'admin';
        $action->createDate = time();
        $action->dueDate = time();
        $action->subject = 'Test Subject';
        $action->completeDate = time();
        $action->complete = 'Yes';
        $action->actionDescription = 'Test Body';

        // These attributes are context-sensitive and subject to change:
        $action->associationId = $contact->id;
        $action->associationType = $contact->module;
        $action->type = 'email';
        $action->visibility = 1;
        $action->assignedTo = 'admin';
        $action->save();

        $track = new TrackEmail();
        $track->actionId = $action->id;
        $track->uniqueId = md5(uniqid(rand(), true));
        $this->assertSaves($track);

        $this->assertEquals($contactInitialActionCount + 1,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $contact->module,
                            ':id' => $contact->id,
                        ))
                        ->queryScalar());
        
        $track->recordEmailOpen();
        
        $this->assertEquals($contactInitialActionCount + 2,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $contact->module,
                            ':id' => $contact->id,
                        ))
                        ->queryScalar());
        
        $this->assertEmailOpenAction($contact);
    }
    
    public function testAccountEmailOpen(){
        $account = $this->accounts('testQuote');
        $this->assertNullEmailOpenAction($account);
        $accountInitialActionCount = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_actions')
                ->where('associationType = :type AND associationId = :id',
                        array(
                    ':type' => $account->module,
                    ':id' => $account->id,
                ))
                ->queryScalar();

        $action = new Actions();
        $action->completedBy = 'admin';
        $action->createDate = time();
        $action->dueDate = time();
        $action->subject = 'Test Subject';
        $action->completeDate = time();
        $action->complete = 'Yes';
        $action->actionDescription = 'Test Body';

        // These attributes are context-sensitive and subject to change:
        $action->associationId = $account->id;
        $action->associationType = $account->module;
        $action->type = 'email';
        $action->visibility = 1;
        $action->assignedTo = 'admin';
        $action->save();

        $track = new TrackEmail();
        $track->actionId = $action->id;
        $track->uniqueId = md5(uniqid(rand(), true));
        $this->assertSaves($track);

        $this->assertEquals($accountInitialActionCount + 1,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $account->module,
                            ':id' => $account->id,
                        ))
                        ->queryScalar());
        
        $track->recordEmailOpen();
        
        $this->assertEquals($accountInitialActionCount + 2,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $account->module,
                            ':id' => $account->id,
                        ))
                        ->queryScalar());
        
        $this->assertEmailOpenAction($account);
    }
    
    public function testQuoteEmailOpen(){
        $quote = $this->quote('docsTest');
        $this->assertNullEmailOpenAction($quote);
        $quoteInitialActionCount = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_actions')
                ->where('associationType = :type AND associationId = :id',
                        array(
                    ':type' => $quote->module,
                    ':id' => $quote->id,
                ))
                ->queryScalar();

        $action = new Actions();
        $action->completedBy = 'admin';
        $action->createDate = time();
        $action->dueDate = time();
        $action->subject = 'Test Subject';
        $action->completeDate = time();
        $action->complete = 'Yes';
        $action->actionDescription = 'Test Body';

        // These attributes are context-sensitive and subject to change:
        $action->associationId = $quote->id;
        $action->associationType = $quote->module;
        $action->type = 'email';
        $action->visibility = 1;
        $action->assignedTo = 'admin';
        $action->save();

        $track = new TrackEmail();
        $track->actionId = $action->id;
        $track->uniqueId = md5(uniqid(rand(), true));
        $this->assertSaves($track);

        $this->assertEquals($quoteInitialActionCount + 1,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $quote->module,
                            ':id' => $quote->id,
                        ))
                        ->queryScalar());
        
        $track->recordEmailOpen();
        
        $this->assertEquals($quoteInitialActionCount + 2,
                Yii::app()->db->createCommand()
                        ->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = :type AND associationId = :id',
                                array(
                            ':type' => $quote->module,
                            ':id' => $quote->id,
                        ))
                        ->queryScalar());
        
        $this->assertEmailOpenAction($quote);
    }
    
    private function assertNullEmailOpenAction($model) {
        $this->assertNull(Actions::model()->findByAttributes(array(
                    'associationType' => $model->module,
                    'associationId' => $model->id,
                    'type' => 'emailOpened',
        )));
    }
    
    private function assertEmailOpenAction($model) {
        $action = Actions::model()->findByAttributes(array(
            'associationType' => $model->module,
            'associationId' => $model->id,
            'type' => 'emailOpened',
        ));
        
        $this->assertNotNull($action);
        
        //Make sure the module text is correct in the open text
        $openText = Modules::displayName(false, $model->module).' has opened the email sent on';
        $this->assertNotFalse(strpos($action->actionDescription, $openText));
    }

}
