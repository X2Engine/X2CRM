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




class MobileFieldInputRenderer extends FieldInputRenderer {

    public function renderLink ($field, array $htmlOptions = array ()) {
        $fieldName = $field->fieldName;
        $linkId = '';
        $name = '';
        $linkSource = null;

        // TODO: move this code and duplicate code in X2Model::renderModelInput into a helper 
        // method. Might be able to use X2Model::getLinkedModel.
        if (class_exists($field->linkType)) {
            if (!empty($this->owner->$fieldName)) {
                list($name, $linkId) = Fields::nameAndId($this->owner->$fieldName);
                $linkModel = X2Model::getLinkedModelMock($field->linkType, $name, $linkId, true);
            } else {
                $linkModel = X2Model::model($field->linkType);
            }
            if ($linkModel instanceof X2Model && 
                $linkModel->asa('LinkableBehavior') instanceof LinkableBehavior) {

                $linkSource = Yii::app()->controller->createAbsoluteUrl (
                    $linkModel->autoCompleteSource);
                $linkId = $linkModel->id;
                $oldLinkFieldVal = $this->owner->$fieldName; 
                $this->owner->$fieldName = $name;
            }
        }

        $input = CHtml::hiddenField(
            $field->modelName . '[' . $fieldName . '_id]', $linkId, 
            array());

        $input .= CHtml::activeTextField($this->owner, $field->fieldName, array_merge(array(
            'title' => $field->attributeLabel,
            'data-x2-link-source' => $linkSource,
            'class' => 'x2-mobile-autocomplete',
            'autocomplete' => 'off',
        ), $htmlOptions));
        return $input;
    }

    public function renderDate ($field, array $htmlOptions = array ()) {
        $model = $this->owner;
        $fieldName = $field->fieldName;

        $oldDateVal = $model->$fieldName;
        $model->$fieldName = Formatter::formatDate($model->$fieldName, 'medium');
        Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
        $pickerOptions = array(// jquery options
            'dateFormat' => Formatter::formatDatePicker(),
            'changeMonth' => false,
            'changeYear' => false,
        );
        if (Yii::app()->getLanguage() === 'fr')
            $pickerOptions['monthNamesShort'] = Formatter::getPlainAbbrMonthNames();
        $input = Yii::app()->controller->widget('CJuiDateTimePicker', array(
            'model' => $model, //Model object
            'attribute' => $fieldName, //attribute name
            'mode' => 'date', //use "time","date" or "datetime" (default)
            'options' => $pickerOptions,
            'htmlOptions' => array_merge(array(
                'title' => $field->attributeLabel,
                'class' => 'x2-mobile-datepicker',
            ), $htmlOptions),
            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                ), true);
        $model->$fieldName = $oldDateVal;
        return $input;
    }

    /**
     * TODO: add full support for datetime fields 
     */
    public function renderDateTime ($field, array $htmlOptions = array ()) {
        return $this->renderDate ($field, $htmlOptions);

//        $model = $this->owner;
//        $fieldName = $field->fieldName;
//
//        $oldDateTimeVal = $model->$fieldName;
//        $pickerOptions = array(// jquery options
//            'dateFormat' => Formatter::formatDatePicker('medium'),
//            'timeFormat' => Formatter::formatTimePicker(),
//            'ampm' => Formatter::formatAMPM(),
//            'changeMonth' => false,
//            'changeYear' => false,
//        );
//        if (Yii::app()->getLanguage() === 'fr')
//            $pickerOptions['monthNamesShort'] = Formatter::getPlainAbbrMonthNames();
//        $model->$fieldName = Formatter::formatDateTime($model->$fieldName);
//        Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
//        $input = Yii::app()->controller->widget('CJuiDateTimePicker', array(
//            'model' => $model, //Model object
//            'attribute' => $fieldName, //attribute name
//            'mode' => 'datetime', //use "time","date" or "datetime" (default)
//            'options' => $pickerOptions,
//            'htmlOptions' => array_merge(array(
//                'title' => $field->attributeLabel,
//                    ), $htmlOptions),
//            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
//                ), true);
//        $model->$fieldName = $oldDateTimeVal;
//        return $input;
    }

    public function renderDropdown ($field, array $htmlOptions = array ()) {
        $om = $field->getDropdownOptions ();
        $multi = (bool) $om['multi'];
        // enables custom multi-select menu for non-phonegap x2touch. Not needed for phonegap since
        // it uses a native multiselct
        if ($multi && !Yii::app()->params->isPhoneGap)
            $htmlOptions['data-native-menu'] = 'false';
        return X2Model::renderModelInput ($this->owner, $field, $htmlOptions);
    }

    public function renderAssignment ($field, array $htmlOptions = array ()) {
        // enables custom multi-select menu for non-phonegap x2touch. Not needed for phonegap since
        // it uses a native multiselct
        if ($field->linkType === 'multiple' && !Yii::app()->params->isPhoneGap)
            $htmlOptions['data-native-menu'] = 'false';
        return X2Model::renderModelInput ($this->owner, $field, $htmlOptions);
    }

    public function renderBoolean ($field, array $htmlOptions = array ()) {
        $fieldName = $field->fieldName;
        $inputName=CHtml::resolveName($this->owner, $fieldName);
        $for=CHtml::getIdByName($inputName);
        return 
            CHtml::label ('', $for).
            CHtml::activeCheckBox($this->owner, $field->fieldName, array_merge(array(
                'unchecked' => 0,
                'title' => $field->attributeLabel,
            ), $htmlOptions));
    }
}

?>
