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




abstract class X2ReportFormModel extends CFormModel {
    public $primaryModelType = 'Contacts';
    public $allFilters = array ();
    public $anyFilters = array ();
    public $refreshForm = false;
    public $export = false;
    public $print = false;
    public $email = false;

    /**
     * @var bool $includeTotalsRow
     */
    public $includeTotalsRow; 

    /**
     * @var string $_reportType
     */
    private $_reportType; 

    public function behaviors () {
        return array (
            'ReportsAttributeParsingBehavior' => array (
                'class' => 'application.modules.reports.components.ReportsAttributeParsingBehavior'
            ),
        );
    }

    public function rules () {
        return array (
            array (
                'refreshForm, export, print, email, includeTotalsRow', 'boolean'
            ), 
            array (
                'primaryModelType', 'required',
            ),
            array (
                'primaryModelType', 'application.components.validators.ModuleModelNameValidator', 
                'throwExceptions' => true,
                'includeActions' => true,
            ),
            array (
                'allFilters,anyFilters',
                'application.components.validators.ArrayValidator',
                'throwExceptions' => true,
                'allowEmpty' => true,
            ),
            array (
                'allFilters,anyFilters',
                'validateFilters',
            ),
        );
    }

    public function getReportType () {
        if (!isset ($this->_reportType)) {
            $this->_reportType = lcfirst (
                preg_replace ('/ReportFormModel$/', '', get_class ($this)));
        }
        return $this->_reportType;
    }

    /**
     * Allows form refresh validation to be handled specially
     */
    public function validate ($attributes=null, $clearErrors=true) {
        if ($this->refreshForm) {
            return parent::validate (array ('refreshForm', 'primaryModelType'), $clearErrors);
        } else {
            return parent::validate ($attributes, $clearErrors);
        }
    }

    /**
     * Allows form refresh validation to be handled specially
     */
    public function setAttributes ($values, $safeOnly=true) {
        if (isset ($values['refreshForm'])) {
            $newValues = array ();
            foreach ($values as $name => $val) {
                if (in_array ($name, array ('refreshForm', 'primaryModelType'))) {
                    $newValues[$name] = $val;
                }
            }
            $values = $newValues;
        }
        return parent::setAttributes ($values, $safeOnly);
    }

    public function getSettings () {
        $settings = $this->getAttributes ();
        unset ($settings['refreshForm']);
        return $settings;
    }

    /**
     * @return array attributes to pass to {@link X2Report}
     */
    public function getReportAttributes () {
        $attributes = $this->getAttributes ();
        unset ($attributes['refreshForm']);
        return $attributes;
    }

    public function attributeLabels () {
        return array (
            'primaryModelType' => Yii::t('reports', 'Primary Record Type'),
            'allFilters' => Yii::t('reports', 'Records must pass all of these conditions:'), 
            'anyFilters' => Yii::t('reports', 'Records must pass any of these conditions:'), 
            'includeTotalsRow' => Yii::t('reports', 'Include totals row?'), 
        );
    }

    /**
     * Validates 'any' and 'all' filters 
     */
    public function validateFilters ($attribute) {
        $value = &$this->$attribute;
        $valid = true;
        foreach ($value as &$arr) {
            if (array_keys ($arr) !== array ('name', 'operator', 'value') ||
                !in_array ($arr['operator'], array ('=', '>', '<', '>=', '<=', '<>', 'notEmpty',
                    'empty', 'list', 'notList', 'noContains', 'contains'), true)) {

                $valid = false;
                break;
            }
            $this->_validateAttrs (array ($arr['name']));
            list ($model, $attr, $fns, $linkField) = $this->getModelAndAttr ($arr['name']);
            $field = $model->getField ($attr);
            $arr['value'] = $field->parseValue ($arr['value']); 
        }

        if (!$valid) {
            throw new CHttpException (
                400, Yii::t('reports', 'Invalid report filter'));
        }
        return true;
    }

    /**
     * Like {@link validateAttrs} but for a single attribute 
     */
    public function validateAttr ($attribute, $params=array ()) {
        $value = $this->$attribute;
        if (isset ($params['empty']) && $params['empty'] && empty ($value)) return;
        $this->_validateAttrs (array ($value));
    }

    /**
     * Ensure that attributes are either names of attributes of primary model type or are of the
     * form <link field name>.<link type attribute name>
     * @throws CHttpException
     */
    public function validateAttrs ($attribute, $params=array ()) {
        $value = $this->$attribute;
        if (!is_array ($value)) return true;
        return $this->_validateAttrs ($value, $attribute, isset ($params['unique']) ? 
            $params['unique'] : false);
    }

    /**
     * Validates attributes and sort directions
     */
    public function validateOrderBy ($attribute, $params=array ()) {
        $value = $this->$attribute;
        $attributes = array_map (function ($entry) {
                return $entry[0];
            }, $value);

        $this->_validateAttrs ($attributes, $attribute, isset ($params['unique']) ? 
            $params['unique'] : false);
        $sortDirections = array_map (function ($entry) {
                return $entry[1];
            }, $value);
        $valid = $this->_validateSortDirections ($sortDirections);
        if (!$valid) {
            throw new CHttpException (400, Yii::t('reports', 'Invalid order by attribute name'));
        }
    }

    protected function _validateSortDirections ($sortDirections) {
        $valid = true;
        foreach ($sortDirections as $fn) {
            if (!in_array ($fn, array ('asc', 'desc'))) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    /**
     * Ensure that attributes are either names of attributes of primary model type or are of the
     * form <link field name>.<link type attribute name>
     * @throws CHttpException
     */
    protected function _validateAttrs (array $value, $attribute=null, $uniqueConstraint=false) {
        $valid = true;
        $primaryModelType = $this->primaryModelType;
        if ($uniqueConstraint && array_unique ($value) !== $value) {
            $this->addError ($attribute, Yii::t('reports', '{attribute} must be unique', array (
                '{attribute}' => ucfirst ($this->getAttributeLabel ($attribute)),
            )));
        }

        foreach ($value as $name) {
            $matches = array ();

            // check for date function
            $fnName = null;
            if (preg_match ('/^(year|month|day|hour|minute|second)\(.*\)$/', $name, $matches)) {
                $fnName = $matches[1];
                $name = preg_replace ('/[^(]+\(/', '', $name);
                $name = preg_replace ('/\)$/', '', $name);
            }

            // parse dot notation
            $pieces = explode ('.', $name);
            if (count ($pieces) > 2) {
                $valid = false;
                break;
            }
            if (count ($pieces) > 1) { // link field

                $relatedField = $pieces[1];
                $linkFieldName = $pieces[0];
                if ($primaryModelType === 'Actions' &&
                    (in_array ($linkFieldName, array_keys (X2Model::getModelNames ())) ||
                     $linkFieldName === 'ActionText')) {

                    // actions link fields can also be of the form 
                    // <model class A>.<attribute of model class A>
                    $linkFieldType = $pieces[0];
                } else {
                    if (!($linkField = $primaryModelType::model ()->getField ($linkFieldName))) {
                        $valid = false;
                        break;
                    }
                    $linkFieldType = $linkField->linkType;
                }

                if ($primaryModelType === 'Actions' && $linkFieldName === 'ActionText') {
                    if ($relatedField !== 'text') {
                        $valid = false;
                        break;
                    }
                } else {
                    $field = $linkFieldType::model ()->getField ($relatedField);
                    if (!$field) {
                        $valid = false;
                        break;
                    }
                }
            } else { // field of primary model
                $field = $primaryModelType::model ()->getField ($name);
                if (!$field) {
                    $valid = false;
                    break;
                }
            }

            if ($fnName) { 
                // validate date function
                if ($field->type === 'date' && 
                    in_array ($fnName, array ('hour', 'minute', 'second'))) {

                    $valid = false;
                    break;
                }
            }
        }
        if (!$valid) {
            $this->addError ($attribute, Yii::t('reports', 'Invalid columns', array (
            )));
        }
        return true;
    }

}
?>
