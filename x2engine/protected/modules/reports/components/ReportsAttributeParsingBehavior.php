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
 * Handles parsing of attributes in reports attribute notation
 */

class ReportsAttributeParsingBehavior extends CBehavior {

    public function attach ($owner) {
        if (property_exists ($this, 'primaryModelType'))
            throw new CException ('owner must have property primaryModelType');
        parent::attach ($owner);
    }

    /**
     * @return CModel static model for specified primary model type 
     */
    private $_primaryModel;
    public function getPrimaryModel () {
        if (!isset ($this->_primaryModel)) {
            $primaryModelType = $this->owner->primaryModelType;
            $this->_primaryModel = $primaryModelType::model ();
        }
        return $this->_primaryModel;
    }

    /**
     * Separate date and aggregate functions from the attribute 
     */
    public function parseFns ($attr, array $fns=array ()) {
        $matches = array ();
        if (preg_match ('/^([^(]+)\(.*\)$/', $attr, $matches)) {
            $fns[] = $matches[1];
            $attr = preg_replace ('/^[^(]+\(/', '', $attr);
            $attr = preg_replace ('/\)$/', '', $attr);
            return $this->parseFns ($attr, $fns);
        } else {
            return array ($attr, $fns);
        }
    }

    public function getDateFn ($fns) {
        $dateFn = null;
        foreach ($fns as $fn) {
            if (in_array ($fn, array ('second', 'minute', 'hour', 'day', 'year', 'month'))) {
                $dateFn = $fn;
                break;
            }
        }
        return $dateFn;
    }

    /**
     * Parses attribute specified with dot notation and returns model and attribute
     * @return array    
     */
    public function getModelAndAttr ($attr) {
        list ($attr, $fns) = $this->parseFns ($attr); 

        if ($attr === '*') return array ($this->getPrimaryModel (), $attr, $fns, null);

        $pieces = explode ('.', $attr);
        $linkField = null;
        if (count ($pieces) > 1) {
            $linkField = $pieces[0];
            $relatedModel = $this->_getRelatedModel ($linkField);
            $columnAttrModel = $relatedModel;
            $columnAttr = $pieces[1];
        } else {
            $columnAttrModel = $this->getPrimaryModel ();
            $columnAttr = $pieces[0];
        }
        return array ($columnAttrModel, $columnAttr, $fns, $linkField);
    }

    /**
     * @param string $linkField Name of the field whose corresponding model should be returned
     * @return CModel
     */
    private $_relatedModelsByLinkField = array ();
    public function _getRelatedModel ($linkField) {
        if (isset ($this->_relatedModelsByLinkField[$linkField])) {
            return $this->_relatedModelsByLinkField[$linkField];
        }
        if ($this->owner->primaryModelType === 'Actions') {
            $this->_relatedModelsByLinkField[$linkField] = $linkField::model ();
        } else {
            $model = $this->getPrimaryModel ();
            $field = $model->getField ($linkField);
            $linkType = $field->linkType;
            $this->_relatedModelsByLinkField[$linkField] = $linkType::model ();
        }
        return $this->_relatedModelsByLinkField[$linkField];
    }

}

?>
