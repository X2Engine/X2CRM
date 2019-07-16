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




class EditMobileFormsFormModel extends X2FormModel {

    public $modelName;
    public $defaultForm;
    public $defaultView;

    public function rules () {
        return array (
            array (
                'modelName', 'validateModelName',
            ),
            array (
                'defaultForm,defaultView',
                'application.components.validators.ArrayValidator',
                'throwExceptions' => true,
                'allowEmpty' => true,
            ),
            array (
                'defaultForm,defaultView', 'validateLayout'
            ),
        );
    }

    public function validateLayout ($attr) {
        $value = $this->$attr;
        $model = new $this->modelName;
        foreach ($value as $fieldName) {
            if (!$model->getField ($fieldName)) {
                $this->addError ($attr, Yii::t('app', 'Invalid layout. Field could not be found.'));
                return;
            }
        }
    }

    public function validateModelName ($attr) {
        $value = $this->$attr;

        $modules = MobileModule::supportedModules (new CDbCriteria (array (
            'condition' => 'editable'
        )));
        $validNames = array_map (function ($module) {
            return X2Model::getModelName ($module->name);
        }, $modules);

        if (!in_array ($value, $validNames)) {
            $this->addError ($attr, Yii::t('app', 'Invalid model name'));
        }
    }

}

?>
