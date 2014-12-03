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
 * Manages conversion between subclasses of X2Model 
 */

class X2ModelConversionBehavior extends CActiveRecordBehavior {

    /**
     * @var array $_fieldMappings
     */
    private $_fieldMappings = array (
        'X2Leads' => array (
            'Contacts' => array (
                'accountName' => 'company',
                'quoteAmount' => 'dealvalue',
                'salesStage' => 'dealstatus',
                'probability' => null,
                'description' => 'backgroundInfo',
            ),
            'Opportunity' => array (
                'firstName' => null, // ignore first and last name since full name will be mapped
                'lastName' => null,
            ),
        )
    );

    /**
     * @var bool $conversionFailed
     */
    public $conversionFailed = false; 

    public static function getActions () {
        return array (
            'convert' => 
                'application.components.recordConversion.X2ModelConversionAction',
        );
    }
        
   /**
    * @return array field map for this class and the target class, or an empty array if no map
    *   exists.
    */
    public function getFieldMap ($targetClass, $flip=false) {
        $modelClass = get_class ($this->owner);
        if (!isset ($this->_fieldMappings[$modelClass]) || 
            !isset ($this->_fieldMappings[$modelClass][$targetClass])) {

            return array ();
        }
        $fieldMappings = $this->_fieldMappings[$modelClass][$targetClass];
        if ($flip) {
            foreach ($fieldMappings as $key => $val) {
                if ($val === null) { 
                    unset ($fieldMappings[$key]);
                }
            }
            $fieldMappings = array_flip ($fieldMappings);
        }
        return $fieldMappings;
    }

    /**
     * Replace attribute names with the names they map to in the field map
     * @param bool $associative
     * @return array
     */
    public function mapFields (array $attributes, $targetClass, $associative=false) {
        $modelClass = get_class ($this->owner);
        $fieldMappings = $this->getFieldMap ($targetClass);
        if (!count ($fieldMappings)) {
            return $attributes;
        }
        $attributeNames = array_flip ($associative ? array_keys ($attributes) : $attributes);
        foreach ($fieldMappings as $source => $target) {
            if (isset ($attributeNames[$source])) {
                if ($target !== null) {
                    if ($associative) {
                        $attributes[$target] = $attributes[$source];
                    } else {
                        $attributes[] = $target;
                    }
                }
                $key = $associative ? $source : array_search ($source, $attributes);
                unset ($attributes[$key]);
            }
        }
        return $attributes;
    }

    public function checkConversionCompatibility ($targetClass) {
        $targetModel = new $targetClass ();
        $fieldMap = $this->getFieldMap ($targetClass, true);

        // don't convert if this model has fields not in target model
        $fieldDiff = array_diff (
            $this->mapFields ($this->owner->attributeNames (), $targetClass), 
            $targetClass::model ()->attributeNames ());
        if (count ($fieldDiff) > 0) {
            $potentialDataLoss = false;
            foreach ($fieldDiff as $name) {
                $name = isset ($fieldMap[$name]) ? $fieldMap[$name] : $name;
                if (isset ($this->owner->$name)) {
                    $potentialDataLoss = true;
                }
            }
            if ($potentialDataLoss) return false;
        }

        // don't convert if any of this model's fields and the targetModel's fields 
        // have the same name but a different type
        $sharedAttrs = array_intersect (
            $this->mapFields ($this->owner->attributeNames (), $targetClass), 
            $targetModel->attributeNames ());
        foreach ($sharedAttrs as $name) {
            // get this model's field name from converted field name, if field name is in the map
            $name = isset ($fieldMap[$name]) ? $fieldMap[$name] : $name;
            $leadField = $this->owner->getField ($name);
            $targetModelField = $targetModel->getField ($name);

            if (!$leadField instanceof Fields || !$targetModelField instanceof Fields) {
                continue;
            }

            if ($leadField->type !== $targetModelField->type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uses the attributes of this model to generate a model of another type. This model is deleted.
     * @param bool $force If true, model will be converted even if there is potential
     *  for data loss
     * @return CModel|false 
     */
    public function convert ($targetClass, $force=false) {
        $attributes = $this->mapFields ($this->owner->getAttributes (), $targetClass, true);
        unset ($attributes['id']);
        unset ($attributes['nameId']);
        unset ($attributes['createDate']);
        $targetModel = new $targetClass ();

        if (!$force && !$this->checkConversionCompatibility ($targetClass)) { 
            return false;
        }

        $targetModel->setAttributes ($attributes, false);

        // don't create antargetModel creation notification or event
        $targetModel->disableBehavior('changelog'); 
        if ($targetModel->save ()) {
            $targetModel->mergeRelatedRecords ($this->owner, true, false, true);
            $targetModel->mergeRelationships ($this->owner);
            $changeLogBehavior = $this->owner->asa ('changelog');
            $changeLogBehavior->createEvent = false; // don't create a lead deletion event
            $this->owner->delete ();
            return $targetModel;
        }
        return $targetModel;
    }

    /**
     * @return <array of strings> Incompatibility warnings to be presented to the user before
     *  they convert this model to the target model.
     */
    public function getConversionIncompatibilityWarnings ($targetClass) {
        $warnings = array ();
        $targetModel = $targetClass::model ();
        $attributeNames = $this->mapFields ($this->owner->attributeNames (), $targetClass);
        $leadsAttrs = array_diff (
            $attributeNames, $targetClass::model()->attributeNames ());
        $fieldMap = $this->getFieldMap ($targetClass, true);

        foreach ($leadsAttrs as $name) {
            $name = isset ($fieldMap[$name]) ? $fieldMap[$name] : $name;
            // if field isn't set, there's no risk of data loss
            if (!isset ($this->owner->$name)) continue;
            $warnings[] = 
                Yii::t('app', 
                    'A field {fieldName} is in Leads but not in {targetModel}.',
                    array (
                        '{fieldName}' => $name,
                        '{targetModel}' => X2Model::getModelTitle ($targetClass),
                    )
                );
        }

        $sharedAttrs = array_intersect (
            $attributeNames, $targetModel->attributeNames ());
        foreach ($sharedAttrs as $name) {
            $originalName = $name;
            $name = isset ($fieldMap[$name]) ? $fieldMap[$name] : $name;
            $leadField = $this->owner->getField ($name);
            $targetModelField = $targetModel->getField ($name);

            if (!$leadField instanceof Fields || !$targetModelField instanceof Fields) {
                continue;
            }

            if ($leadField->type !== $targetModelField->type) {
                if (isset ($fieldMap[$originalName])) {
                    $warnings[] = 
                        Yii::t('app', 
                            'The {model} field {fieldName} maps to the {targetModel} field '.
                            '{targetField} but the fields have different types.', 
                            array (
                                '{fieldName}' => $originalName,
                                '{fieldName}' => $name,
                                '{model}' => X2Model::getModelTitle (get_class ($this->owner)),
                                '{targetModel}' => X2Model::getModelTitle ($targetClass),
                            )
                        );
                } else {
                    $warnings[] = 
                        Yii::t('app', 
                            'A field {fieldName} is in both {model} and {targetModel} but the fields
                             have different types.', 
                            array (
                                '{fieldName}' => $name,
                                '{model}' => X2Model::getModelTitle (get_class ($this->owner)),
                                '{targetModel}' => X2Model::getModelTitle ($targetClass),
                            )
                        );
                }
            }
        }

        return $warnings;
    }
}

?>
