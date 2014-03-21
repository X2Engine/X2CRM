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

Yii::import('zii.widgets.grid.CGridView');
Yii::import('X2GridViewBase');

/**
 * Custom grid view display function.
 *
 * @package X2CRM.components
 */
class X2GridViewGeneric extends X2GridViewBase {

    /**
     * Used to populate allFieldNames property with attribute labels indexed by
     * attribute names.
     */
    protected function addFieldNames () {
        foreach ($this->columns as $column) {
            $header = (isset ($column['header'])) ? $column['header'] : '';
            $name = (isset ($column['name'])) ? $column['name'] : '';
            $this->allFieldNames[$name] = $header;
        }
    }

    protected function generateColumns () {
        $unsortedColumns = array ();
        foreach ($this->columns as &$column) {
            $name = (isset ($column['name'])) ? $column['name'] : '';
            if (!isset ($this->gvSettings[$name])) {
                $unsortedColumns[] = $column;
                continue;
            }
            $width = $this->gvSettings[$name];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $column['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');
            $column['id'] = $this->namespacePrefix.'C_'.$name;
        }
        unset ($column); // unset lingering reference

        if (isset ($this->gvSettings['gvControls']) && $this->enableControls) {
            $width = $this->gvSettings['gvControls'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvControlsColumn ($width);
        }
        if (isset ($this->gvSettings['gvCheckBox'])) {
            $width = $this->gvSettings['gvCheckBox'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvCheckboxColumn ($width);
        }

        $sortedColumns = array ();
        foreach ($this->gvSettings as $columnName => $width) {
            foreach ($this->columns as $column) {
                $name = (isset ($column['name'])) ? $column['name'] : '';
                if ($name === $columnName) {
                    $sortedColumns[] = $column;
                    break;
                } 
            }
        }
        $this->columns = array_merge ($sortedColumns, $unsortedColumns);
    }

    protected function setSummaryText () {}

}
?>
