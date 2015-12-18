<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
                    'addRelationship', 
                     
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

            if ($model->createRelationship($relationshipModel) === true) {
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

     


}
