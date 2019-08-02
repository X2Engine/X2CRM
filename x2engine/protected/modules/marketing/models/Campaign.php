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




/**
 * A Campaign represents a one time mailing to a list of contacts.
 *
 * When a campaign is created, a contact list must be specified. When a campaing is 'launched'
 * a duplicate list is created leaving the original unchanged. The duplicate 'campaign' list
 * will keep track of which contacts were sent email, who opened the mail, and who unsubscribed.
 * A campaign is 'active' after it has been launched and ready to send mail. A campaign is 'complete'
 * when all applicable email has been sent. This is the model class for table "x2_campaigns".
 *
 * @package application.modules.marketing.models
 */
class Campaign extends X2Model {

    const CAMPAIGN_TYPE_DROPDOWN = 107;

    public $supportsWorkflow = false;

    public static function model($className=__CLASS__) {
            return parent::model($className);
    }

    public function tableName()	{ return 'x2_campaigns'; }

    public function behaviors() {
        return array_merge(parent::behaviors(),array(
            'LinkableBehavior'=>array(
                    'class'=>'LinkableBehavior',
                    'module'=>'marketing'
            ),
            'ERememberFiltersBehavior' => array(
                    'class'=>'application.components.behaviors.ERememberFiltersBehavior',
                    'defaults'=>array(),
                    'defaultStickOnClear'=>false
            ),
            'TagBehavior' => array(
                'class' => 'TagBehavior',
                'disableTagScanning' => true,
            ),
        ));
    }

    public function relations() {
        return array_merge(parent::relations(), array(
            'list' => array(self::BELONGS_TO, 'X2List', array('listId'=>'nameId')),
            'suppressionList' => array(self::BELONGS_TO, 'X2List', array('suppressionListId'=>'nameId')),
            'attachments' => array(self::HAS_MANY, 'CampaignAttachment', 'campaign'),
        ));
    }

    //Similar to X2Model but we had a special case with 'marketing'
    public function attributeLabels() {
        $this->queryFields();

        $labels = array();

        foreach(self::$_fields[$this->tableName()] as &$_field) {
            $labels[ $_field->fieldName ] = Yii::t('marketing', $_field->attributeLabel);
        }
        return $labels;
    }

    //Similar to X2Model but we had a special case with 'marketing'
    public function getAttributeLabel($attribute) {
        $this->queryFields();

        // don't call attributeLabels(), just look in self::$_fields
        foreach(self::$_fields[$this->tableName()] as &$_field) {
            if($_field->fieldName == $attribute) {
                return Yii::t('marketing', $_field->attributeLabel);
            }
        }
        // original Yii code
        if(strpos($attribute,'.') !== false) {
            $segs = explode('.',$attribute);
            $name = array_pop($segs);
            $model = $this;
            foreach($segs as $seg) {
                    $relations = $model->getMetaData()->relations;
                    if(isset($relations[$seg])) {
                        $model = X2Model::model($relations[$seg]->className);
                    } else {
                        break;
                    }
            }
            return $model->getAttributeLabel($name);
        } else {
            return $this->generateAttributeLabel($attribute);
        }
    }

    /**
     * Convenience method to retrieve a Campaign model by id. Filters by the current user's permissions.
     *
     * @param integer $id Model id
     * @return Campaign
     */
    public static function load($id) {
        $model = X2Model::model('Campaign');
        $campaign = $model->with('list')->findByPk((int)$id, $model->getAccessCriteria());
        $campaign->recalculateRates();
        return $campaign;
    }

    /**
     * Search all Campaigns using this model's attributes as the criteria
     *
     * @return Array Set of matching Campaigns
     */
    public function search() {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria);
    }

    /**
     * Override of {@link X2Model::setX2Fields}
     *
     * Skips HTML purification for the content so that tracking links will work.
     */
    public function setX2Fields(&$data, $filter = false, $bypassPermissions = false) {
        $originalContent = isset($data['content'])?$data['content']:$this->content;
        parent::setX2Fields($data, $filter, $bypassPermissions);
        $this->content = $originalContent;
    }

    public static function getValidContactLists () {
        $list = new X2List;
        $criteria = $list->getAccessCriteria();
        $criteria->addCondition("type!='campaign' and type!='UnSubscribe'");
    	$lists = X2Model::model('ContactList')->findAllByAttributes (array(), 
            $criteria
    	);
    	return $lists;
    }
    


    public function getDisplayName ($plural=true, $ofModule=true) {
        if (!$ofModule) {
            return Yii::t('app', 'Campaign'.($plural ? 's' : ''));
        } else {
            return parent::getDisplayName ($plural, $ofModule);
        }
    }

    protected function recalculateRates() {
        $list = $this->list;
        if (!$list) { 
            return;
        }

        $total = $list->statusCount('sent');
        if ($total == 0) { 
            return;
        }
        
        $opened = $list->statusCount('opened');
        $clicked = $list->statusCount('clicked');
        $unsubscribed = $list->statusCount('unsubscribed');
        $updateAttrs = array();
        $this->openRate = sprintf('%.2f', $opened / $total * 100);
        if (!isset($this->_oldAttributes['openRate']) || $this->openRate != $this->_oldAttributes['openRate']) {
            $updateAttrs[] = 'openRate';
        }
        $this->clickRate = sprintf('%.2f', $clicked / $total * 100);
        if (!isset($this->_oldAttributes['clickRate']) || $this->clickRate != $this->_oldAttributes['clickRate']) {
            $updateAttrs[] = 'clickRate';
        }
        $this->unsubscribeRate = sprintf('%.2f', $unsubscribed / $total * 100);
        if (!isset($this->_oldAttributes['unsubscribeRate']) || $this->unsubscribeRate != $this->_oldAttributes['unsubscribeRate']) {
            $updateAttrs[] = 'unsubscribeRate';
        }

        if (!empty($updateAttrs)) {
            $this->update($updateAttrs);
        }
    }
}
