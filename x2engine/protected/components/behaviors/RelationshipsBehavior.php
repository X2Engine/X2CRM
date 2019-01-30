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




class RelationshipsBehavior extends CActiveRecordBehavior {
    
    protected $_relationships;
    protected $_visibleRelatedX2Models;
    protected $_relatedX2Models;
    
    public function getRelationships($refreshCache = false) {
        if ($refreshCache || !isset($this->_relationships)) {
            $this->_relationships = 
                    Relationships::model()->findAll($this->getAllRelationshipsCriteria());
        }
        return $this->_relationships;
    }

    public function createRelationship($target, $firstLabel = null, $secondLabel = null) {
        if ($this->isValidTarget($target)) {
            $relationship = new Relationships();
            $relationship->firstType = get_class($this->owner);
            $relationship->firstId = $this->owner->id;
            $relationship->firstLabel = $firstLabel;
            $relationship->secondType = get_class($target);
            $relationship->secondId = $target->id;
            $relationship->secondLabel = $secondLabel;
            if ($relationship->save()) {
                return true;
            }else{
                return $relationship->getAllErrorMessages();
            }
        }
        return false;
    }

    public function deleteRelationship(CActiveRecord $target) {
        $affected = 0;
        if ($this->hasRelationship($target)) {
            $affected+=Relationships::model()->deleteAllByAttributes(array(
                'firstType' => get_class($this->owner),
                'firstId' => $this->owner->id,
                'secondType' => get_class($target),
                'secondId' => $target->id,
            ));
            $affected+=Relationships::model()->deleteAllByAttributes(array(
                'firstType' => get_class($target),
                'firstId' => $target->id,
                'secondType' => get_class($this->owner),
                'secondId' => $this->owner->id,
            ));
        }
        return $affected;
    }

    public function getRelationship(CActiveRecord $target) {
        $rel1 = Relationships::model()->findByAttributes(array(
            'firstType' => get_class($this->owner),
            'firstId' => $this->owner->id,
            'secondType' => get_class($target),
            'secondId' => $target->id,
        ));
        if(isset($rel1)){
            return $rel1;
        }
        
        $rel2 = Relationships::model()->findByAttributes(array(
            'firstType' => get_class($target),
            'firstId' => $target->id,
            'secondType' => get_class($this->owner),
            'secondId' => $this->owner->id,
        ));
        if(isset($rel2)){
            return $rel2;
        }
        return null;
    }

    public function getRelationshipLabel(CActiveRecord $target) {
        $relationship = $this->getRelationship($target);
        if ($relationship->firstType == get_class($this->owner) && 
                $relationship->firstId == $this->owner->id) {
            return $relationship->firstLabel;
        }
        return $relationship->secondLabel;
    }
    
    public function getRelatedX2Models($refreshCache = false) {
        if (!isset($this->_relatedX2Models) || $refreshCache) {
            $myModelName = get_class($this->owner);
            $this->_relatedX2Models = array();
            $relationships = $this->getRelationships($refreshCache);
            $modelRelationships = array();
            foreach ($relationships as $relationship) {
                list($idAttr, $typeAttr) = ($relationship->firstId == $this->owner->id &&
                        $relationship->firstType == $myModelName) ?
                        array('secondId', 'secondType') :
                        array('firstId', 'firstType');
                if (!array_key_exists($relationship->$typeAttr,
                                $modelRelationships))
                        $modelRelationships[$relationship->$typeAttr] = array();
                if (!empty($relationship->$idAttr))
                        $modelRelationships[$relationship->$typeAttr][] = $relationship->$idAttr;
            }
            foreach ($modelRelationships as $modelName => $ids) {
                $this->_relatedX2Models = array_merge(
                        $this->_relatedX2Models,
                        X2Model::model($modelName)->findAllByPk($ids));
            }
        }
        return $this->_relatedX2Models;
    }

    public function getVisibleRelatedX2Models($refreshCache = false) {
        if (!isset($this->_visibleRelatedX2Models) || $refreshCache) {
            if (Yii::app()->params->isAdmin) {
                $this->_visibleRelatedX2Models = $this->getRelatedX2Models($refreshCache);
            } else {
                $this->_visibleRelatedX2Models = array_filter($this->getRelatedX2Models($refreshCache),
                    function ($model) {
                        return Yii::app()->controller->checkPermissions($model, 'view');
                    }
                );
            }
        }
        return $this->_visibleRelatedX2Models;
    }
    
    public function afterSave($event){
        $oldAttributes = $this->owner->getOldAttributes();
        $linkFields = Fields::model()->findAllByAttributes(array(
            'modelName'=>get_class($this->owner),
            'type'=>'link',
        ));
        foreach($linkFields as $field){
            $nameAndId = Fields::nameAndId($this->owner->getAttribute($field->fieldName));
            $oldNameAndId = Fields::nameAndId(
                    isset($oldAttributes[$field->fieldName]) 
                    ? $oldAttributes[$field->fieldName] : '');
            // Remove previous relationship unless we are in the context of a webform upload
            if(!empty($oldNameAndId[1]) && $nameAndId[1] !== $oldNameAndId[1] &&
                !in_array($this->owner->scenario, array('webForm', 'webFormWithCaptcha'))){
                    $oldTarget = X2Model::model($field->linkType)->findByPk($oldNameAndId[1]);
                    if ($oldTarget)
                        $this->owner->deleteRelationship($oldTarget);
            }
            if (!empty($nameAndId[1])) {
                $newTarget = X2Model::model($field->linkType)->findByPk($nameAndId[1]);
                $this->owner->createRelationship($newTarget);
            }
        }
        parent::afterSave($event);
    }
    
    public function afterDelete($event) {
        $condition = '(`firstType`=:ft AND `firstId`=:fid) OR (`secondType`=:st AND `secondId`=:sid)';
        Relationships::model()->deleteAll($condition,
            array(
                ':ft' => get_class($this->owner),
                ':fid' => $this->owner->id,
                ':st' => get_class($this->owner),
                ':sid' => $this->owner->id
            )
        );
        parent::afterDelete($event);
    }

    public function isValidTarget($target) {
        if (!$target instanceof CActiveRecord) {
            return false;
        }else if (!$target->asa('relationships')) {
            return false;
        } else if (empty($target->id)) {
            return false;
        } else if (get_class($this->owner) === get_class($target) 
                && $this->owner->id === $target->id){
            return false;
        }else if ($this->hasRelationship($target)) {
            return false;
        }
        return true;
    }

    public function hasRelationship($target) {
        $rel1 = Relationships::model()->countByAttributes(array(
            'firstType' => get_class($this->owner),
            'firstId' => $this->owner->id,
            'secondType' => get_class($target),
            'secondId' => $target->id,
        ));

        $rel2 = Relationships::model()->countByAttributes(array(
            'firstType' => get_class($target),
            'firstId' => $target->id,
            'secondType' => get_class($this->owner),
            'secondId' => $this->owner->id,
        ));

        return ($rel1 || $rel2);
    }
    
    private function getAllRelationshipsCriteria() {
        return new CDbCriteria(array(
            'condition' =>
            '(firstType=:myType AND firstId=:myId OR
                      secondType=:myType AND secondId=:myId) AND
                     (firstId IS NOT NULL AND firstId != "" AND
                      secondId IS NOT NULL AND secondId != "")',
            'params' => array(
                ':myType' => get_class($this->owner),
                ':myId' => $this->owner->id,
            )
        ));
    }

    protected function getVisibleRelationshipsCriteria() {
        $criteria = new CDbCriteria;
        $qpg = new QueryParamGenerator(':getRelationshipsCriteria');
        $models = $this->getVisibleRelatedX2Models();
        if (!count($models)) {
            $criteria->addCondition('FALSE');
        } else {
            foreach ($models as $model) {
                $criteria->addCondition(
                        "(firstType=:myType AND firstId=:myId AND
                          secondType={$qpg->nextParam(get_class($model))} AND 
                          secondId={$qpg->nextParam($model->id)})", 'OR');
                $criteria->addCondition(
                        "(secondType=:myType AND secondId=:myId AND
                          firstType={$qpg->nextParam(get_class($model))} AND 
                          firstId={$qpg->nextParam($model->id)})", 'OR');
            }
            $criteria->params = array_merge(
                    array(
                ':myType' => get_class($this->owner),
                ':myId' => $this->owner->id,
                    ), $qpg->getParams());
        }
        return $criteria;
    }

}
