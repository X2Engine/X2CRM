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





class TopicReplies extends X2ActiveRecord {
    
    public $module = 'topics';
    public $upload;
    
    private $_attachments;
    
    /**
     * Returns the static model of the specified AR class.
     * @return Template the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_topic_replies';
    }
    
    public function rules() {
        return array(
            array(
                'text','filter','filter'=>array($obj=new CHtmlPurifier(),'purify')
            ),
            array(
                'topicId', 'required'
            ),
            array(
                'text', 'application.components.validators.RequiredIfNotSetValidator',
                'otherAttr' => 'upload'
            ),
        );
    }

    public function behaviors(){
        $that = $this;
        return array_merge(parent::behaviors(),array(
            'AssociatedMediaBehavior' => array(
                'class' => 'application.components.behaviors.AssociatedMediaBehavior',
                'fileAttribute' => 'upload',
                'associationType' => 'topicReply',
                'getAssociationId' => function () use ($that) {
                    return $that->id;
                },
            ),
            'StaticFieldsBehavior' => array(
                'class' => 'application.components.behaviors.StaticFieldsBehavior',
                'translationCategory' => 'topics',
                'fields' => array (
                    array (
                        'fieldName' => 'text',
                        'attributeLabel' => 'Text',
                        'type' => 'text',
                    ),
                ),
            ),
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
            'permissions' => array('class' => Yii::app()->params->modelPermissions),
        ));
    }
    
    public function relations(){
        return array(
            'topic' => array(self::BELONGS_TO, 'Topics', 'topicId'),
            'profile' => array(self::HAS_ONE, 'Profile', 
                array ('username' => 'assignedTo')),
        );
    }
    
    public function beforeSave(){
        if(empty($this->assignedTo)){
            $this->assignedTo = Yii::app()->user->getName();
        }
        $this->updatedBy = Yii::app()->user->getName();
        return parent::beforeSave();
    }

    public function afterSave () {
        parent::afterSave ();
        $event = new Events;
        $event->visibility = 1;
        $event->associationType = 'TopicRepies';
        $event->associationId = $this->id;
        $event->user = Yii::app()->user->getName();
        $event->type = 'topic_reply';
        $event->save();
    }
    
    public function getTopicPage(){
        $tableSchema = $this->getTableSchema();
        $criteria = new CDbCriteria();
        $criteria->select = 't.id';
        $criteria->compare('t.topicId',$this->topicId);
        $criteria->order = 't.createDate ASC';
        $searchConditions = Yii::app()->db->getCommandBuilder()
            ->createFindCommand($tableSchema, $criteria)->getText();
        $varPrefix = '@'; //Current prefix is MySQL specific
        $varName = $varPrefix.'rownum';
        $varText = 'SET '.$varName.' = 0'; // Current declaration is MySQL specific
        Yii::app()->db->createCommand()
                ->setText($varText)
                ->execute();
        $subQuery = Yii::app()->db->createCommand()
                ->select('*, ('.$varName.':='.$varName.'+1) r')
                ->from('('.$searchConditions.') t1')
                ->getText();
        $rowNumberQuery = Yii::app()->db->createCommand()
                ->select('(r-1)')
                ->from('('.$subQuery.') t2')
                ->where('t2.id=:t2_id');
        $rowNumberQuery->params = array_merge(array(':t2_id'=>$this->id),$criteria->params);
        $rowNumber = $rowNumberQuery->queryScalar();
        return (int)($rowNumber / Topics::PAGE_SIZE);
    }
    
    public function getAuthorId(){
        return Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_profile')
                ->where('username=:user', array(':user' => $this->assignedTo))
                ->queryScalar();
    }
    
    public function isOriginalPost(){
        return $this->id === $this->topic->originalPost->id;
    }
    
    public function isEditable(){
        return Yii::app()->controller->checkPermissions($this, 'edit');
    }
    
    public function isDeletable(){
        return !$this->isOriginalPost() && Yii::app()->controller->checkPermissions($this, 'delete');
    }
    
    public function isEdited(){
        return $this->createDate !== $this->lastUpdated;
    }
    
    public function getAttachments() {
        if (is_null($this->_attachments)) {
            $this->_attachments = Media::model()->findAllByAttributes(array('associationType' => 'topicReply',
                'associationId' => $this->id));
        }
        return $this->_attachments;
    }

}
