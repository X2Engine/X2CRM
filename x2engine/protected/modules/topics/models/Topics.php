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




Yii::import ('application.modules.topics.components.formatters.TopicsFieldFormatter');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class Topics extends X2Model {
    
    const PAGE_SIZE = 20;

    public $upload;

    public $supportsFieldLevelPermissions = false;
    
    public $supportsWorkflow = false;

    protected $fieldFormatterClass = 'TopicsFieldFormatter';

    private $_originalPost;
    private $_postTextChanged = false;

    /**
     * Returns the static model of the specified AR class.
     * @return Template the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public static function getListViewHeader() {
        $titleHeader = X2Html::tag(
            'div', array('class' => 'topic-title'), Yii::t('topics', 'Topic Title'));
        $replyCountHeader = X2Html::tag(
            'div', array('class' => 'topic-reply-count'), Yii::t('topics', 'Replies'));
        $lastUpdatedHeader = X2Html::tag(
            'div', array('class' => 'topic-last-updated'), Yii::t('topics', 'Latest Post'));
        $attributesHeader = X2Html::tag(
            'div', array('class' => 'topic-attributes'), $replyCountHeader . $lastUpdatedHeader);
        $header = X2Html::tag(
            'div', array('class' => 'clear-fix'), $titleHeader . $attributesHeader);

        return X2Html::tag('div', array('class' => 'topics-header'), $header);
    }
    
    public static function getSortLinks($order = null) {
        $ret = X2Html::link(
            Yii::t('topics','Sorting'), '#', 
            array(
                'id' => 'topics-sort-toggle',
                'class' => 'x2-button',
                'style' => 'vertical-align:top;'));
        $links = array(
            X2Html::link(
                Yii::t('topics','Most Recent'), 
                Yii::app()->controller->createUrl(
                    '/topics/topics/index', 
                    array('order' => 'mostRecent')
                ), 
                array(
                    'class' => 'x2-button' . (($order == 'mostRecent' || is_null($order)) ? 
                        ' disabled disabled-link' : ''), 'style' => 'vertical-align:top;')
            ),
            X2Html::link(
                Yii::t('topics','Alphabetical'), 
                Yii::app()->controller->createUrl(
                    '/topics/topics/index', 
                    array('order' => 'alphabetical')
                ), 
                array(
                    'class' => 'x2-button' . ($order == 'alphabetical' ? ' disabled disabled-link' : ''), 
                    'style' => 'vertical-align:top;')),
            X2Html::link(
                Yii::t('topics','Create Date'), 
                Yii::app()->controller->createUrl(
                    '/topics/topics/index', 
                    array('order' => 'firstCreated')
                ), 
                array('class' => 'x2-button' . ($order == 'firstCreated' ? ' disabled disabled-link' : ''), 
                'style' => 'vertical-align:top;')),
            X2Html::link(
                Yii::t('topics','Most Popular'), 
                Yii::app()->controller->createUrl(
                    '/topics/topics/index', 
                    array('order' => 'mostPopular')), 
                array(
                    'class' => 'x2-button' . ($order == 'mostPopular' ? ' disabled disabled-link' : ''), 
                    'style' => 'vertical-align:top;')),
        );
        $ret .= X2Html::tag(
            'div', array('id' => 'topics-sort-buttons', 'style' => 'display:none;'), 
            implode(' ', $links));
        return $ret;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_topics';
    }

    public function behaviors() {
        $that = $this;
        return array_merge(parent::behaviors(), array(
            'AssociatedMediaBehavior' => array(
                'class' => 'application.components.behaviors.AssociatedMediaBehavior',
                'fileAttribute' => 'upload',
                'associationType' => 'topicReply',
                'getAssociationId' => function () use ($that) {
                    return $that->originalPost->id;
                },
            ),
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'topics'
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'InlineEmailModelBehavior' => array(
                'class' => 'application.components.behaviors.InlineEmailModelBehavior',
            )
        ));
    }
    
    public function relations() {
        return array(
            'originalPostRelation' => array(self::HAS_ONE, 'TopicReplies',
                'topicId', 'order' => 'originalPostRelation.createDate ASC'),
            'replies' => array(self::HAS_MANY, 'TopicReplies', 'topicId',
                'order'=>'replies.createDate ASC'),
            'lastPost' => array(self::HAS_ONE, 'TopicReplies', 'topicId',
                'order' => 'lastPost.createDate DESC'),
        );
    }

    public function renderAttribute (
        $fieldName, $makeLinks = true, $textOnly = true, $encode = true) { 

        switch ($fieldName) {
            case 'replyCount':
                $val = Yii::t('topics', '1#{n} reply|n!=1#{n} replies', array (
                    $this->replyCount,
                ));
                return $encode ? CHtml::encode($val) : $val;
            default:
                return call_user_func_array ('parent::'.__FUNCTION__, func_get_args ());
        }
    }

    private $_text;
    public function getText () {
        if (!isset ($this->_text) && $this->originalPost) {
            $this->_text = $this->originalPost->text;
        }
        return $this->_text;
    }

    public function setText ($text) {
        $this->_text = $text;
        $this->setOriginalPostText ($text);
    }
    
    public function afterSave(){
        if ($this->_originalPost->isNewRecord || $this->_postTextChanged) {
            if ($this->_originalPost->isNewRecord) {
                $this->_originalPost->topicId = $this->id;
                $this->_originalPost->assignedTo = Yii::app()->user->getName();
            }
            $this->_originalPost->updatedBy = Yii::app()->user->getName();
            $this->_originalPost->save();
        }
        return parent::afterSave();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria);
    }
    
    public function getOrder($order){
        $ret = 'sticky DESC, ';
        switch($order){
            case 'alphabetical':
                $ret .= 't.name ASC';
                break;
            case 'firstCreated':
                $ret .= 't.createDate ASC';
                break;
            case 'mostPopular':
                $ret .= 'replyCount DESC';
                break;
            case 'mostRecent':
            default:
                $ret.='minCreateDate DESC';
        }
        return $ret;
    }
    
    public function getOriginalPost(){
        if(is_null($this->_originalPost)){
            $this->_originalPost = $this->originalPostRelation;
        }
        return $this->_originalPost;
    }
    
    public function setOriginalPostText($text){
        if(is_null($this->originalPost)){
            $this->_originalPost = new TopicReplies;
        }
        if($this->_originalPost->text !== $text){
            $this->_originalPost->text = $text;
            $this->_postTextChanged = true;
        }
    }
    
    public function getReplyCount(){
        return Yii::app()->db->createCommand()
            ->select('COUNT(id)')
            ->from('x2_topic_replies')
            ->where('topicId = :id',array(':id'=>$this->id))
            ->queryScalar() - 1;
    }

    private $_attachments;
    public function getAttachments () {
        if (!isset ($this->_attachments)) {
            $this->_attachments = $this->originalPost ? $this->originalPost->attachments : array ();
        }
        return $this->_attachments;
    }

}
