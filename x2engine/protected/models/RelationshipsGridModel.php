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
 * Handles relationship grid attribute rendering and filtering
 */

class RelationshipsGridModel extends CModel {

    /**
     * @var CActiveRecord $relatedModel
     */
    public $relatedModel; 

    /**
     * @var string $myModelName
     */
    public $myModel; 

    /**
     * @var int $id
     */
    public $id; 

    /**
    * Added these to fix the readonly error 
    */
    public $name;

    public $assignedTo;

    public $label;

    public $createDate;

    public function __construct ($scenario=null) {
        if ($scenario) {
            $this->setScenario ($scenario);
        }
    }

    /**
     * Set attributes with attributes of embedded model or values specified in GET params
     */
    public function init () {
        $scenario = $this->getScenario ();
        if ($scenario === 'search' && isset ($_GET[get_called_class ()])) {
            $this->setAttributes ($_GET[get_called_class ()], false);
        } elseif ($this->relatedModel) {
            $this->setAttributes ($this->relatedModel->getAttributes (), false);
            $this->label = $this->relatedModel->getRelationshipLabel ($this->myModel);
        }
    }

    public function attributeNames () {
        return array (
            'id',
            'name',
            'relatedModelName',
            'assignedTo',
            'label',
            'createDate',
        );
    }

    public function renderAttribute ($name) {
        switch ($name) {
            case 'name':
                echo $this->relatedModel->getLink (/*array (
                    'class' => 'quick-read-link',
                    'data-id' => $this->relatedModel->id,
                    'data-class' => get_class ($this->relatedModel),
                    'data-name' => CHtml::encode ($this->relatedModel->name),
                )*/);
                break;
            case 'relatedModelName':
                echo $this->getRelatedModelName ();
                break;
            case 'assignedTo':
                echo $this->relatedModel->renderAttribute ('assignedTo');
                break;
            case 'label':
                echo $this->label;
                break;
            case 'createDate':
                echo isset ($this->createDate) ? 
                    X2Html::dynamicDate ($this->createDate) : '';
                break;
        }
    }

    public function renderInlineRelabelControls() {
        return CHtml::ajaxLink(X2Html::fa("edit"), "#", array(), array(
            "class" => "editLabelLink",
            "style" => "display: none",
            "data-id" => $this->relatedModel->id,
            "data-label" => $this->label,
            "data-model" => get_class($this->relatedModel)
        ));
    }

    public function filterModels (array $gridModels) {
        $filteredModels = array ();
        $that = $this;
        $filters = array_filter ($this->attributeNames (), function ($a) use ($that) {
            return $that->$a !== '' && $that->$a !== null;
        });
        
        foreach ($gridModels as $model) {
            $filterOut = false;
            foreach ($filters as $filter) {
                $val = $this->$filter;
                switch ($filter) {
                    case 'name':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('name'));
                        break;
                    case 'relatedModelName':
                        $filterOut = $val !== get_class ($model->relatedModel);
                        break;
                    case 'assignedTo':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('assignedTo.fullName'));
                        break;
                    case 'label':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getRelationshipLabel ($this->myModel));
                        break;
                    case 'createDate':
                        $timestampA = Formatter::parseDate ($val); 
                        $timestampB = $model->relatedModel->getAttribute ('createDate'); 
                    
                        // compare days since UNIX epoch
                        $filterOut = floor ($timestampA / (3600 * 24)) !== 
                            floor ($timestampB / (3600 * 24));
                        break;
                }
                if ($filterOut) break;
            }
            if (!$filterOut)
                $filteredModels[] = $model;
        }
        return $filteredModels;
    }

//    public function sortModels (array $gridModels, $sortKey) {
//        if (!isset ($_GET[$sortKey])) return;
//        $sortOrder = explode ('.', $_GET[$sortKey]);
//        if (count ($sortOrder) > 1) $direction = $sortOrder[1];
//        else $direction = 'asc';
//        $sortAttr = $sortOrder[0];
//        @usort ($gridModels, function ($a, $b) use ($sortAttr, $direction) { 
//            if ($a->$sortAttr < $b->$sortAttr) {
//                return ($direction === 'asc' ? -1 : 1);
//            } elseif ($a->$sortAttr > $b->$sortAttr) {
//                return ($direction === 'asc' ? 1 : -1);
//            } else {
//                return 0;
//            }
//        });
//        return $gridModels;
//    }

    public function setRelatedModelName ($name) {
        $this->_relatedModelType = $name;
    }

    private $_relatedModelType; 
    public function getRelatedModelName () {
        if (!isset ($this->_relatedModelType)) {
            if (!isset ($this->relatedModel)) {
                return ($this->_relatedModelType = null);
            }
            $title = Yii::app()->db->createCommand()
                ->select("title")
                ->from("x2_modules")
                ->where("name = :name AND custom = 1")
                ->bindValues(array(":name" => get_class ($this->relatedModel)))
                ->queryScalar();
            if ($title)
                $this->_relatedModelType = $title;
            else
                $this->_relatedModelType = X2Model::getModelTitle (get_class ($this->relatedModel));
        }
        return $this->_relatedModelType;
    }

}

?>


