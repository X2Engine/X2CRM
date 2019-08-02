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
 * @package application.controllers
 */
class RelationshipsController extends x2base {

    public $modelClass = 'Relationships';

	public $layout = '//layouts/column1';


    public function filters() {
        return array_merge(parent::filters(), array(
            'accessControl',
        ));
    }

    public function accessRules() {
        return array (
            array ('allow',
                'actions' => array (
                    'addRelationship', 'relabelRelationship',
                      
                    'graph', 'getRecordData', 'addNode', 'connectNodes', 
                    'deleteEdges', 'viewInlineGraph', 'ajaxGetModelAutocomplete',
                     
                ),
                'users' => array ('@'),
            ),
            array ('deny',
                'users' => array ('*')
            )
        );
    }


    /**
     * Add a record to record relationship
     *
     * A record can be a contact, opportunity, or account. This function is
     * called via ajax from the Relationships Widget.
     */
    public function actionAddRelationship() {

        //check if relationship already exits
        if (isset($_POST['ModelName']) && isset($_POST['ModelId']) &&
            isset($_POST['RelationshipModelName']) && isset($_POST['RelationshipModelId'])) {

            $modelName = $_POST['ModelName'];
            $modelId = $_POST['ModelId'];
            $relationshipModelName = $_POST['RelationshipModelName'];
            $relationshipModelId = $_POST['RelationshipModelId'];
            $model = $this->getModelFromTypeAndId ($modelName, $modelId);
            if (!Yii::app()->controller->checkPermissions ($model, 'edit')) {
                $this->denied ();
            }
            $relationshipModel = $this->getModelFromTypeAndId (
                $relationshipModelName, $relationshipModelId);
            if (!Yii::app()->controller->checkPermissions ($relationshipModel, 'view')) {
                $this->denied ();
            }

            if (isset($_POST['mutual']) && $_POST['mutual'] == 'true')
                $_POST['secondLabel'] = $_POST['firstLabel'];

            if ($model->hasRelationship($relationshipModel)) {
                echo 'duplicate';
                Yii::app()->end();
            }

            if ($model->createRelationship($relationshipModel, $_POST['firstLabel'], $_POST['secondLabel']) === true) {
                echo 'success';
                Yii::app()->end();
            } else {
                echo 'failure';
                Yii::app()->end();
            }
        } else {
            throw new CHttpException(400, Yii::t('app', 'Bad Request'));
        }
    }

     
    public function actionAjaxGetModelAutocomplete ($modelType, $name=null) {
        if (!Yii::app()->params->isAdmin) $this->denied ();
        return parent::actionAjaxGetModelAutocomplete ($modelType, $name);
    }

    /**
     * Display the relationships graph with initial focus given to the specified record
     */
    public function actionGraph ($recordId, $recordType) {
        if (!Yii::app()->params->isAdmin) $this->denied ();
        $model = $this->getModelFromTypeAndId ($recordType, $recordId);
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }
        $this->render ('graphFullScreen', array (
            'model' => $model,
        ));
    }

    /**
     * Update the label of a specific relationship
     * @param int $firstId ID
     * @param string $firstType Type of the record being related
     * @param string $label New relationship label
     */
    public function actionRelabelRelationship ($secondId, $secondType) {
        $firstId = filter_input(INPUT_POST, 'firstId', FILTER_SANITIZE_NUMBER_INT);
        $firstType = filter_input(INPUT_POST, 'firstType', FILTER_SANITIZE_STRING);
        $label = filter_input(INPUT_POST, 'label', FILTER_SANITIZE_STRING);
        $firstModel = $this->getModelFromTypeAndId ($firstType, $firstId);
        $secondModel = $this->getModelFromTypeAndId ($secondType, $secondId);
        if (!Yii::app()->controller->checkPermissions ($firstModel, 'edit') ||
            !Yii::app()->controller->checkPermissions ($secondModel, 'edit')) {
                $this->denied ();
        }

        $criteria = new CDbCriteria;
        $params = array(
            ':firstType' => $firstType,
            ':firstId' => $firstId,
            ':secondType' => $secondType,
            ':secondId' => $secondId,
        );
        $criteria->addCondition (
            "firstType=:firstType AND
             firstId=:firstId AND
             secondType=:secondType AND
             secondId=:secondId", 'OR');
        $criteria->addCondition (
            "firstType=:secondType AND
             firstId=:secondId AND
             secondType=:firstType AND
             secondId=:firstId", 'OR');
        $criteria->params = $params;
        $relationship = Relationships::model ()->find($criteria);

        if ($relationship) {
            if ($relationship->firstLabel === $relationship->secondLabel)
                $relationship->firstLabel = $relationship->secondLabel = $label;
            else if ($secondId === $relationship->secondId && $secondType === $relationship->secondType)
                $relationship->firstLabel = $label;
            else
                $relationship->secondLabel = $label;
            if ($relationship->update(array('firstLabel', 'secondLabel'))) {
                echo 'success';
                Yii::app()->end();
            }
        }
    }

    /**
     * Get information about the record and its neighbors 
     */
    public function actionGetRecordData ($recordId, $recordType) {
        $model = $this->getModelFromTypeAndId ($recordType, $recordId);
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }

        $retArr = array ();
        $neighborData = RelationshipsGraph::getNeighborData ($model);
        $retArr['detailView'] = $this->renderPartial (
            'application.components.views._relationshipsGraphRecordDetails', array (
                'model' => $model,
                'neighborData' => $neighborData,
            ), true);
        $retArr['neighborData'] = $neighborData;
        echo CJSON::encode ($retArr);
    }

    /**
     * Creates an edge between source node and all target nodes and echoes info about the new edges
     */
    public function actionAddNode ($recordId, $recordType, array $otherRecordInfo) {
        $model = $this->getModelFromTypeAndId ($recordType, $recordId);
        if (!Yii::app()->controller->checkPermissions ($model, 'edit')) {
            $this->denied ();
        }
        $models = $this->getModelsFromTypeAndId ($otherRecordInfo);
        $modelCount = count ($models);

        // create relationships between new node and each target node
        $edges = array (); // new edges with source and target specified by node uid
        $modelA = $model;
        for ($i = 0; $i < $modelCount; $i++) {
            $modelB = $models[$i];
            $typeA = get_class ($modelA);
            $typeB = get_class ($modelB);
            if ($modelA->createRelationship($modelB) === true) {
                $edges[] = array (
                    'source' => $typeA.$modelA->id,
                    'target' => $typeB.$modelB->id,
                );
            }
        }
        echo CJSON::encode ($edges);
    }

    /**
     * Creates an edge between all pairs of specified nodes and echoes info about the new edges
     * @param array $recordInfo model type, model id pairs (max 4)
     */
    public function actionConnectNodes (array $recordInfo) {
        $models = array ();
        if (count ($recordInfo) > 4) {
            throw new CHttpException (400, Yii::t('app', 'Too many records to connect')); 
        }
        $models = $this->getModelsFromTypeAndId ($recordInfo);
        foreach ($models as $model) {
            if (!Yii::app()->controller->checkPermissions ($model, 'edit')) {
                $this->denied ();
            }
        }
        $modelCount = count ($models);

        // create relationships between each pair of models
        $edges = array (); // new edges with source and target specified by node uid
        for ($i = 0; $i < $modelCount; $i++) {
            $modelA = $models[$i];
            for ($j = $i + 1; $j < $modelCount; $j++) {
                $modelB = $models[$j];
                $typeA = get_class ($modelA);
                $typeB = get_class ($modelB);
                if ($modelA->createRelationship($modelB) === true) {
                    $edges[] = array (
                        'source' => $typeA.$modelA->id,
                        'target' => $typeB.$modelB->id,
                    );
                }
            }
        }
        echo CJSON::encode ($edges);
    }

    public function actionDeleteEdges (array $edgeData) {
        $criteria = new CDbCriteria;
        $qpg = new QueryParamGenerator (':actionDeleteEdges');
        foreach ($edgeData as $edge) {
            $firstType = $edge[0][0];
            $firstId = $edge[0][1];
            $secondType = $edge[1][0];
            $secondId = $edge[1][1];
            // deny access if user doesn't have edit permission for either node
            if (!Yii::app()->params->isAdmin) {
                $modelA = $this->getModelFromTypeAndId ($firstType, $firstId);
                $modelB = $this->getModelFromTypeAndId ($secondType, $secondId);
                if ((!Yii::app()->controller->checkPermissions ($modelA, 'edit')) &&
                    (!Yii::app()->controller->checkPermissions ($modelB, 'edit'))) {

                    $this->denied ();
                }
            }
            $criteria->addCondition (
                "firstType={$qpg->nextParam ($firstType)} AND
                 firstId={$qpg->nextParam ($firstId)} AND
                 secondType={$qpg->nextParam ($secondType)} AND
                 secondId={$qpg->nextParam ($secondId)}", 'OR');
            $criteria->addCondition (
                "secondType={$qpg->nextParam ($secondId)} AND
                 secondId={$qpg->nextParam ($secondType)} AND
                 firstType={$qpg->nextParam ($firstType)} AND
                 firstId={$qpg->nextParam ($firstId)}", 'OR');
        }
        $criteria->params = $qpg->getParams ();
        if (Relationships::model ()->deleteAll ($criteria)) {
            echo 'success';
        }
    }

    public function actionViewInlineGraph ($recordId, $recordType, $height=null) {
        $model = X2Model::getModelOfTypeWithId ($recordType, $recordId);
        if (!$model) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }

        $this->renderPartial ('graphInline', array (
            'model' => $model,
            'height' => $height,
        ), false, true);
    }
     


}
