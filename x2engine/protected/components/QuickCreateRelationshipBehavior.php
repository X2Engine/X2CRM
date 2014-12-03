<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

/**
 * Provides utility methods for handling quick creation of records and relationships. 
 * This class involves the use of two models:
 *  The model associated with the owner of this behavior (referred to as 'the first model') and 
 *  the model associated with the view from which the quick create ajax request was made 
 *  (referred to as 'the second model').
 *
 * @package application.components
 */
class QuickCreateRelationshipBehavior extends CBehavior {

    /**
     * Used to specify which attributes (for a given model type) should be updated to match
     * the first model's attribute values. 
     * @var array (<model type> => <array of attributes in second model indexed by attributes in 
     *  the first model>)
     */
    public $attributesOfNewRecordToUpdate = array ();

    private static $_modelsWhichSupportQuickCreate;

    /**
     * Returns an array of all model classes (associated with some module) which have this
     * behavior
     *
     * @return <array of strings>
     */
    public static function getModelsWhichSupportQuickCreate ($includeActions=false) {
        if (!isset (self::$_modelsWhichSupportQuickCreate)) {
            self::$_modelsWhichSupportQuickCreate = array_diff (
                array_keys (X2Model::getModelNames()), 
                    array ('Docs', 'Groups', 'Campaign', 'Media', 'Quote',
                        'BugReports'));
            self::$_modelsWhichSupportQuickCreate[] = 'Actions';
        }
        $modelNames = self::$_modelsWhichSupportQuickCreate;
        if (!$includeActions) {
            array_pop ($modelNames);
        }
        return $modelNames;
    }

    /**
     * @param array $models
     * @return array of urls for create actions of each model in $models 
     */
    public static function getCreateUrlsForModels ($models) {
        $createUrls = array_flip ($models);
        array_walk (
            $createUrls,
            function (&$val, $key) {
                $moduleName = strtolower (X2Model::getModuleName ($key));
                $val = Yii::app()->controller->createUrl ("/$moduleName/$moduleName/create");
            });
        return $createUrls;
    }

    /**
     * Returns array of dialog titles to be used for quick create dialogs for each model 
     * @param array $models
     * @return array
     */
    public static function getDialogTitlesForModels ($models) {
        // get create relationship dialog titles for each linkable model
        $dialogTitles = array_flip ($models);
        array_walk (
            $dialogTitles,
            function (&$val, $key) {
                $val = Yii::t('app', 
                    'Create {relatedModelClass}', 
                    array ('{relatedModelClass}' => ucfirst (X2Model::getRecordName ($key))));
            });
        return $dialogTitles;
    }

    /**
     * Returns array of tooltips to be applied to quick create buttons for each model 
     * @param array $models
     * @param string $modelName
     * @return array
     */
    public static function getDialogTooltipsForModels ($models, $modelName) {
        $tooltips = array_flip ($models);
        array_walk (
            $tooltips,
            function (&$val, $key) use ($modelName) {
                $val = Yii::t('app', 
                    'Create a new {relatedModelClass} associated with this {modelClass}', 
                    array (
                        '{relatedModelClass}' => X2Model::getRecordName ($key), 
                        '{modelClass}' => 
                            X2Model::getRecordName (X2Model::getModelName ($modelName))
                    )
                );
            });
        return $tooltips;
    }

    /**
     * For controllers implementing this behavior, this method should be called if the GET parameter
     * 'x2ajax' is set to '1' after the model is created and fields are set. 
     * 
     * If called from the record create page:
     *  No record exists yet for the second model. An array is echoed containing values of the 
     *  first model which should be used to populate fields in the create form of the second model.
     *
     * If called from the record view page:
     *  Attempts to create a new relationship between first and second models.
     *  If creation of new record is successful and if the second model has been updated, 
     *  an updated detailView of the second model is returned.
     *
     *  If the first record could not be created, the create form is rendered again with errors.
     * 
     * @return bool true if errors were encountered, false otherwise
     */
    public function quickCreate ($model) {
        Yii::app()->clientScript->scriptMap['*.css'] = false;

        $errors = false;

        if (isset ($_POST['validateOnly'])) return;

        if ($model->save ()) {
            if (isset ($_POST['ModelName'])) {
                $secondModelName = $_POST['ModelName']; 
            }
            if (!empty ($_POST['ModelId'])) {
                $secondModelId = $_POST['ModelId']; 
            }

            if (isset ($secondModelName) && !empty ($secondModelId)) {
                $secondModel = $this->quickCreateRelationship (
                    $model, get_class ($model), $model->id, $secondModelName, $secondModelId);
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'data' => ($secondModel ? $this->owner->getDetailView ($secondModel) : ''),
                        'name' => $model->name,
                        'id' => $model->id,
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else if (isset ($secondModelName)) {
                $data = $this->getValuesOfNewRecordToUpdate ($model, $secondModelName);
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'data' => $data,
                        'name' => $model->name,
                        'id' => $model->id,
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else if (isset ($_POST['quickCreateOnly']) && $_POST['quickCreateOnly']) {
                $model->refresh ();
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'message' => Yii::t('app', '{recordType} created: {link}', array (
                            '{recordType}' => get_class ($model),
                            '{link}' => $model->link 
                        )),
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else {
                throw new CHttpException (400, Yii::t ('app', 'Bad Request'));
            }

            Yii::app()->end();
        } else {
            $errors = true;
        }

        return $errors;
    }

    /**
     * Renders an inline record create form
     * @param object $model 
     * @param bool $hasErrors
     */
    public function renderInlineCreateForm ($model, $hasErrors) {
        Yii::app()->clientScript->scriptMap['*.css'] = false;

        if ($hasErrors) {
            $page = $this->owner->renderPartial(
                'application.components.views._form', 
                array(
                    'model' => $model,
                    'modelName' => strtolower (get_class ($model)),
                    'suppressQuickCreate' => true,
                ), true, true);
            echo json_encode(
                array(
                    'status' => 'userError',
                    'page' => $page,
                ));
        } else {
            $this->owner->renderPartial(
                'application.components.views._form', 
                array(
                    'model' => $model, 
                    'modelName' => strtolower (get_class ($model)),
                    'suppressQuickCreate' => true,
                ), false, true);
        }

    }

    /**
     * Returns an associative array of values of the first model indexed by attribute
     * names in the second model.
     * @return array (<name of attribute to modify => <value of attribute in new record>)
     */
    private function getValuesOfNewRecordToUpdate ($firstModel, $secondModelName) {
        $attributesToUpdate = (isset ($this->attributesOfNewRecordToUpdate[$secondModelName]) ? 
            $this->attributesOfNewRecordToUpdate[$secondModelName] : array ());

        $data = array ();
        foreach ($attributesToUpdate as $firstModelAttr => $secondModelAttr) {
            if (isset ($firstModel->$firstModelAttr)) {
                $data[$secondModelAttr] = $firstModel->$firstModelAttr;
            }
        }

        return $data;
    }

    /**
     * Creates a new relationship and then, based on the value of attributesOfNewRecordToUpdate,
     * sets values of the second model using values of the first model.
     * Returns an array of the values that were changed indexed by the attribute name.
     * @param object $firstModel 
     * @param string $firstModelNamethe class name of the first model
     * @param string $firstModelId the id of the first model
     * @param string $secondModelName the class name of the second model
     * @param string $secondModelId the id of the second model 
     * @return mixed false if the second model isn't updated, the second model otherwise
     */
    private function quickCreateRelationship (
        $firstModel, $firstModelName, $firstModelId, $secondModelName, $secondModelId) {

        $success = Relationships::create (
            $firstModelName, $firstModelId, $secondModelName, $secondModelId);

        $attributesToUpdate = (isset ($this->attributesOfNewRecordToUpdate[$secondModelName]) ? 
            $this->attributesOfNewRecordToUpdate[$secondModelName] : array ());

        $secondModel = $secondModelName::model ()->findByPk ($secondModelId);

        if ($secondModel) {
            $changed = false;

            /* 
            Set values of existing record to values of newly created record based on mapping
            configured in $attributesOfNewRecordToUpdate
            */
            foreach ($attributesToUpdate as $firstModelAttr => $secondModelAttr) {
                
                if (isset ($firstModel->$firstModelAttr) &&
                    (!isset ($secondModel->$secondModelAttr) || 
                     $secondModel->$secondModelAttr === '')) {

                    $secondModel->$secondModelAttr = $firstModel->$firstModelAttr;

                    $changed = true;
                }
            }

            if ($changed) {
                $secondModel->update ();
            }
        }

        if ($secondModel && $changed) return $secondModel;
        else return false;
    }

}
?>
