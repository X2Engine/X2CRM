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
 * Description
 * @author Alex Rowe <alex@x2engine.com>
 */
class ScatterPlotFormModel extends ChartFormModel {
    

    public $groupField;

    public $yAxisField;

    public $xAxisField;

    // Used for uniqueValidator
    private $uniqueFields = array();

    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array (
                    'groupField, yAxisField, xAxisField',
                    'required',
                ),
                array (
                    'yAxisField, xAxisField',
                    'uniqueValidator'
                ),
            )
        );
    }

    public function uniqueValidator ($attribute) {
        if (in_array($this->$attribute, $this->uniqueFields)){
            $this->addError ($attribute, Yii::t('charts', 'X and Y axis columns must be unique'));
            return false;
        } 
        $this->uniqueFields[$attribute] = $this->$attribute;

        return true;
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            // 'labelField' => Yii::t('charts', 'Labels'),
            'groupField' => Yii::t('charts', 'Groups'),
            'yAxisField' => Yii::t('charts', 'Y Axis'),
            'xAxisField' => Yii::t('charts', 'X Axis'),
        ));
    }

    public function getHelpItems() {
        return array(
            'groupField' => Yii::t('charts',  "Choose a column to group eat point by. This should be a field that contains only several different values."),
            'xAxisField' => Yii::t('charts',  "Choose a numerical column on the report grid that will display as the values on the X Axis"),
            'yAxisField' => Yii::t('charts',  "Choose a numerical column on the report grid that will display as the values on the Y Axis")
        );
    }
}

?>
