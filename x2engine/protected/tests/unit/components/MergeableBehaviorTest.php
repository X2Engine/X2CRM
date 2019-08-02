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
Yii::import('application.modules.accounts.models.*');

class MergeableBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'contact' => 'Contacts',
        'account' => 'Accounts',
        'events' => array('Events','.DummyData'),
        'relationships' => 'Relationships',
    );

    
    public function testSetMergedField() {
        $model1 = new Contacts;
        $model1->firstName = 'Test';
        $model2 = new Contacts;
        $model2->firstName = 'Another';
        $model3 = new Contacts;

        $newModel = new Contacts;
        $this->assertEquals(null, $newModel->firstName);
        $field = Fields::model()->findByAttributes(array(
            'modelName' => 'Contacts',
            'fieldName' => 'firstName',
        ));
        $newModel->setMergedField($field, array(
            $model1,
            $model2,
            $model3,
        ));
        $this->assertEquals($model2->firstName, $newModel->firstName);
        $model1->lastUpdated = time();
        $newModel->setMergedField($field, array(
            $model1,
            $model2,
            $model3,
        ));
        $this->assertEquals($model1->firstName, $newModel->firstName);

        $model1->backgroundInfo = 'This is some text';
        $model3->backgroundInfo = 'This is some more text';
        $field2 = Fields::model()->findByAttributes(array(
            'modelName' => 'Contacts',
            'fieldName' => 'backgroundInfo',
        ));
        $this->assertEquals(null, $newModel->backgroundInfo);
        $newModel->setMergedField($field2, array(
            $model1,
            $model2,
            $model3,
        ));
        $this->assertEquals($model1->backgroundInfo . "\n--\n" . $model3->backgroundInfo,
            $newModel->backgroundInfo);
    }
    

    public function testSetMergedCreateDate() {
        $initialTime = time();
        $model1 = new Contacts;
        $model1->createDate = $initialTime;
        $model2 = new Contacts;
        $model2->createDate = $initialTime - 1;
        $model3 = new Contacts;
        $model3->createDate = $initialTime + 1;

        $newModel = new Contacts;
        $this->assertEquals(null, $newModel->createDate);
        $newModel->setMergedCreateDate(array($model1, $model2, $model3));
        $this->assertEquals($initialTime - 1, $newModel->createDate);
    }

    /*
     * The following tests are all related to the "mergeRelatedRecords" function
     * which merges the all related record types (Actions, Events, Tags, etc.)
     * on two X2Model instances. The the undo merge is also tested in each
     * unit test. 
     */

    public function testMergeActions() {
        $contact = $this->contact('testAnyone');
        $action = new Actions;
        $action->actionDescription = "TEST";
        $action->visibility = 1;
        $action->associationType = "contacts";
        $action->associationId = $contact->id;
        $action->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(0,
            Yii::app()->db->createCommand()->select('COUNT(*)')
                ->from('x2_actions')
                ->where(
                    'associationType = "contacts" AND associationId = :id',
                    array(':id' => $model->id))
                ->queryScalar());
        $this->assertEquals(1,
            Yii::app()->db->createCommand()->select('COUNT(*)')
                ->from('x2_actions')
                ->where(
                    'associationType = "contacts" AND associationId = :id',
                    array(':id' => $contact->id))
                ->queryScalar());

        $mergeData = $model->mergeActions($contact, true);

        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where(
                                'associationType = "contacts" AND associationId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where(
                                'associationType = "contacts" AND associationId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());

        $model->unmergeActions($contact->id, $mergeData);

        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where(
                                'associationType = "contacts" AND associationId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where(
                                'associationType = "contacts" AND associationId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
    }

    public function testMergeWorkflowActions() {
        $workflow = new Workflow;
        $workflow->name = 'Test Workflow';
        $workflow->save();
        $workflowStage = new WorkflowStage;
        $workflowStage->workflowId = $workflow->id;
        $workflowStage->save();

        $contact = $this->contact('testAnyone');
        $action = new Actions;
        $action->actionDescription = "TEST1";
        $action->type = 'workflow';
        $action->workflowId = $workflow->id;
        $action->stageNumber = $workflowStage->id;
        $action->visibility = 1;
        $action->associationType = "contacts";
        $action->associationId = $contact->id;
        $action->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $now = time();
        $action = new Actions;
        $action->actionDescription = "TEST2";
        $action->type = 'workflow';
        $action->workflowId = $workflow->id;
        $action->stageNumber = $workflowStage->id;
        $action->complete = 'Yes';
        $action->completedBy = 'admin';
        $action->completeDate = $now;
        $action->visibility = 1;
        $action->associationType = "contacts";
        $action->associationId = $model->id;
        $action->save();

        $this->assertEquals(2,
            Yii::app()->db->createCommand()->select('COUNT(*)')
                ->from('x2_actions')
                ->where(
                    'associationType = "contacts" AND workflowId = :id AND stageNumber = :stage',
                    array(
                        ':id' => $workflow->id,
                        ':stage' => $workflowStage->id,
                    ))
                ->queryScalar());

        $mergeData = $contact->mergeWorkflowActions($model, true);

        $this->assertEquals(1,
            Yii::app()->db->createCommand()->select('COUNT(*)')
                ->from('x2_actions')
                ->where(
                    'associationType = "contacts" AND workflowId = :id AND stageNumber = :stage',
                    array(
                        ':id' => $workflow->id,
                        ':stage' => $workflowStage->id,
                    ))
                ->queryScalar());
        $data = Yii::app()->db->createCommand()
            ->select('t.text, a.complete, a.completedBy, a.completeDate')
            ->from('x2_actions a')
            ->join('x2_action_text t', 't.actionId = a.id')
            ->where(
                'associationType = "contacts" AND workflowId = :id AND stageNumber = :stage',
                array(
                    ':id' => $workflow->id,
                    ':stage' => $workflowStage->id,
                ))
            ->queryRow();
        $this->assertEquals('TEST1 - TEST2', $data['text']);
        $this->assertEquals('Yes', $data['complete']);
        $this->assertEquals('admin', $data['completedBy']);
        $this->assertEquals($now, $data['completeDate']);

        $contact->unmergeWorkflowActions($mergeData);
        $this->assertEquals(2,
            Yii::app()->db->createCommand()->select('COUNT(*)')
                ->from('x2_actions')
                ->where(
                    'associationType = "contacts" AND workflowId = :id AND stageNumber = :stage',
                    array(
                        ':id' => $workflow->id,
                        ':stage' => $workflowStage->id,
                    ))
                ->queryScalar());
    }

    public function testMergeEvents() {
        $contact = $this->contact('testAnyone');

        $event = new Events;
        $event->type = 'record_updated';
        $event->associationType = 'Contacts';
        $event->associationId = $contact->id;
        $event->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());

        $mergeData = $model->mergeEvents($contact, true);

        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(2,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());

        $model->unmergeEvents($contact->id, $mergeData);

        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where(
                                'associationType = "Contacts" AND associationId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
    }

    public function testMergeNotifications() {
        $contact = $this->contact('testAnyone');

        $notif = new Notification;
        $notif->modelType = 'Contacts';
        $notif->modelId = $contact->id;
        $notif->type = 'weblead';
        $notif->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());

        $mergeData = $model->mergeNotifications($contact, true);

        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());

        $model->unmergeNotifications($contact->id, $mergeData);

        $this->assertEquals(0,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1,
                Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id',
                                array(':id' => $contact->id))
                        ->queryScalar());
    }

    public function testMergeTags() {
        $contact = $this->contact('testAnyone');

        $tags = $contact->getTags(true);
        if(count($tags) === 0 ){
            $contact->addTags(array('test'));
            $tags = $contact->getTags(true);
        }
        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();
        $this->assertEquals(0, count($model->getTags(true)));
        $this->assertEquals(count($tags), count($contact->getTags(true)));

        $mergeData = $model->mergeTags($contact, true);

        $this->assertEquals(count($tags), count($model->getTags(true)));
        $this->assertEquals(0, count($contact->getTags(true)));

        $model->unmergeTags($contact->id, $mergeData);

        $this->assertEquals(0, count($model->getTags(true)));
        $this->assertEquals(count($tags), count($contact->getTags(true)));
    }

    /**
     * @group failing
     */
    public function testMergeRelationships() {
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        $thirdContact = $this->contact('testUser_unsent');

        $contact->createRelationship($otherContact);
        $contact->createRelationship($thirdContact);

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(1, count($model->getRelatedX2Models(true)));
        $this->assertEquals(2, count($contact->getRelatedX2Models(true)));

        $mergeData = $model->mergeRelationships($contact, true);

        $this->assertEquals(0, count($contact->getRelatedX2Models(true)));
        $this->assertEquals(3, count($model->getRelatedX2Models(true)));

        $model->unmergeRelationships($contact->id, $mergeData);

        $this->assertEquals(2, count($contact->getRelatedX2Models(true)));
        $this->assertEquals(1, count($model->getRelatedX2Models(true)));
    }

    public function testMergeLinkFields() {
        $contact = $this->contact('testAnyone');
        $account = $this->account('testQuote');

        $account->primaryContact = $contact->nameId;
        $account->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals($contact->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);

        $mergeData = $model->mergeLinkFields($contact, true);
        $account = X2Model::model('Accounts')->findByPk($account->id);

        $this->assertEquals($model->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);

        $model->unmergeLinkFields($contact->id, $mergeData);
        $account = X2Model::model('Accounts')->findByPk($account->id);

        $this->assertEquals($contact->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);
    }

}

?>
