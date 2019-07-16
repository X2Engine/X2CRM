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




class SummationReportFormModel extends X2ReportFormModel {
    public $drillDownColumns = array ();
    public $orderBy = array ();
    public $groupsOrderBy = array ();
    public $groupsAnyFilters = array ();
    public $groupsAllFilters = array ();
    public $groups = array ();
    public $groupAttrValues = array ();
    public $aggregates = array ();
    public $subgridIndex;
    public $generateSubgrid = false;

    public static $validAggregateFns = array ('sum', 'avg', 'min', 'max');

    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array ('generateSubgrid', 'boolean'),
                array (
                    'drillDownColumns, orderBy, aggregates, groups, groupAttrValues',
                    'application.components.validators.ArrayValidator',
                    'throwExceptions' => true,
                    'allowEmpty' => false,
                ),
                array ('orderBy, groups', 'validateOrderBy', 'unique' => true),
                array ('groupsOrderBy', 'validateGroupsOrderBy'),
                array ('subgridIndex', 'validateSubgridIndex'),
                array ('drillDownColumns', 'validateAttrs', 'unique' => true),
                array ('groupAttrValues', 'validateGroupAttrValues'),
                array ('aggregates', 'validateAggregates'),
                array (
                    'groupsAllFilters,groupsAnyFilters',
                    'application.components.validators.ArrayValidator',
                    'throwExceptions' => true,
                    'allowEmpty' => true,
                ),
            )
        );
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'drillDownColumns' => Yii::t('reports', 'Drill Down Columns:'),
            'orderBy' => Yii::t('reports', 'Drill Down Order:'),
            'groupsOrderBy' => Yii::t('reports', 'Group Order:'),
            'groups' => Yii::t('reports', 'Groups:'),
            'aggregates' => Yii::t('reports', 'Aggregate Columns:'),
            'groupsAllFilters' => 
                Yii::t('reports', 'Groups must pass all of these conditions:'), 
            'groupsAnyFilters' => 
                Yii::t('reports', 'Groups must pass any of these conditions:'), 
            'allFilters' => 
                Yii::t('reports', 'Drill down records must pass all of these conditions:'), 
            'anyFilters' => 
                Yii::t('reports', 'Drill down records must pass any of these conditions:'), 
        ));
    }

    public function validateSubgridIndex ($attribute) {
        if ($this->generateSubgrid && (!isset ($this->$attribute) ||
            !is_numeric ($this->$attribute))) {

            throw new CHttpException (
                400, Yii::t('reports', 'Invalid subgrid index'));
        }
    }

    /**
     * Validate group by attribute values array, ensuring that attributes are valid
     */
    public function validateGroupAttrValues ($attribute) {
        $value = $this->$attribute;
        $valid = $this->_validateAttrs (array_keys ($value));
        if (!$valid) {
            throw new CHttpException (
                400, Yii::t('reports', 'Invalid group attribute'));
        }
    }

    protected function _validateAggregates (array $value, $attribute) {
        if (array_unique ($value) !== $value) {
            $this->addError ($attribute, Yii::t('reports', '{attribute} must be unique', array (
                '{attribute}' => ucfirst ($this->getAttributeLabel ($attribute)),
            )));
        }
        $attrs = array ();
        $fnNames = array ();
        $valid = true;

        foreach ($value as $aggregate) {
            if (preg_match ('/^count\(\*\)$/', $aggregate)) {
                continue;
            } elseif (preg_match (
                '/^('.implode ('|', self::$validAggregateFns).')\(.*\)$/', $aggregate, $matches)) {

                $fnNames[] = $matches[1];
                $aggregate = preg_replace ('/^[^(]+\(/', '', $aggregate);
                $aggregate = preg_replace ('/\)$/', '', $aggregate);
                $attrs[] = $aggregate;
            } else {
                $valid = false;
                break;
            }
        }

        $this->_validateAttrs ($attrs);
        foreach ($fnNames as $fn) {
            if (!in_array ($fn, self::$validAggregateFns)) {
                $valid = false;
            }
        }
        if (!$valid) {
            throw new CHttpException (
                400, Yii::t('reports', 'Invalid aggregate function name'));
        }
    }

    /**
     * Validates attributes and mysql aggregate functions for specified aggregates 
     */
    public function validateAggregates ($attribute) {
        $value = $this->$attribute;
        $valid = $this->_validateAggregates ($value, $attribute);
    }

    /**
     * Validates attributes and sort directions
     */
    public function validateGroupsOrderBy ($attribute) {
        $value = $this->$attribute;

        $valid = $this->_validateAggregates (array_map (function ($entry) {
                return $entry[0];
            }, $value), $attribute);
        $sortDirections = array_map (function ($entry) {
                return $entry[1];
            }, $value);
        $valid = $this->_validateSortDirections ($sortDirections);
        if (!$valid) {
            throw new CHttpException (400, Yii::t('reports', 'Invalid group order attribute name'));
        }
    }

    public function getAggregateFieldOptions ($condList=false) {
        $primaryModelType = $this->primaryModelType;
        return $primaryModelType::model ()->getFieldsForDropdown (
            true, $condList, function ($field) { 
                return in_array (
                    $field->type, 
                    array (
                        'currency', 'int', 'date', 'dateTime', 'rating', 'boolean', 'percentage'));
            });
    }


    public function addAggregatesToFieldOptions (
        array $options, $condList=false, $excludeNonAggregates=false) {

        $aggregateFns = self::$validAggregateFns;
        $newOptions = array ();
        if ($condList) {
            foreach ($options as $header => $group) {
                $newOptions[$header] = array (X2ConditionList::listOption (
                    array (
                        'attributeLabel' => Yii::t('reports', 'Count')
                    ), 'count(*)'));
                foreach ($group as $option) {
                    if (!$excludeNonAggregates) $newOptions[$header][] = $option;
                    foreach ($aggregateFns as $fn) {
                        $newOption = $option;
                        $newOption['name'] = $fn.'('.$option['name'].')';
                        $newOption['label'] = ucfirst ($fn).' '.$option['label'];
                        $newOptions[$header][] = $newOption;
                    }
                }
            }
           // AuxLib::debugLogR ('$newOptions = ');
            // AuxLib::debugLogR ($newOptions);

        } else {
            foreach ($options as $header => $group) {
                $newOptions[$header] = array ('count(*)' => Yii::t('reports', 'Count'));
                foreach ($group as $val => $label) {
                    if (!$excludeNonAggregates) $newOptions[$header][$val] = $label;
                    foreach ($aggregateFns as $fn) {
                        $newOptions[$header][$fn.'('.$val.')'] = ucfirst ($fn).' '.$label;
                    }
                }
            }
        }
        return $newOptions;
    }


}

?>
