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
 * Filterform to use filters in combination with CArrayDataProvider and CGridView
 * @see http://www.yiiframework.com/wiki/232/using-filters-with-cgridview-and-carraydataprovider/
 */
class FiltersForm extends CFormModel
{
    /**
     * @var array filters, key => filter string
     */
    public $filters = array();
 
    /**
     * Override magic getter for filters
     * @param string $name
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->filters)) {
            $this->filters[$name] = '';
        }
        return $this->filters[$name];
    }
 
    /**
     * Override magic setter for filters
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->filters[$name] = $value;
    }
 
    /**
     * Filter input array by key value pairs
     * @param array $data rawData
     * @return array filtered data array
     */
    public function filter(array $data)
    {
        foreach ($data AS $rowIndex => $row) {
            foreach ($this->filters AS $key => $searchValue) {
                if (!is_null($searchValue) AND $searchValue !== '') {
                    $compareValue = null;
 
                    if ($row instanceof CModel) {
                        if (isset($row->$key) == false) {
                            throw new CException("Property " . get_class($row) . "::{$key} does not exist!");
                        }
                        $compareValue = $row->$key;
                    } elseif (is_array($row)) {
                        if (!array_key_exists($key, $row)) {
                            throw new CException("Key {$key} does not exist in array!");
                        }
                        $compareValue = $row[$key];
                    } else {
                        throw new CException("Data in CArrayDataProvider must be an array of arrays or an array of CModels!");
                    }
 
                    if (stripos($compareValue, $searchValue) === false) {
                        unset($data[$rowIndex]);
                    }
                }
            }
        }
        return $data;
    }
 
}