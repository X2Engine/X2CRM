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
 * Validation for grid report form 
 */
class TimeSeriesFormModel extends ChartFormModel {
    
    public $timeField;

    public $labelField;

    public $aggregateField;

    public $filterType = 'trailing';

    public $filter = 'week';

    public $filterFrom = null;

    public $filterTo = null;


    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array (
                    'timeField',
                    'required',
                ),
                array(
                    'labelField, timeField',
                    'isValidColumn'
                ),
                array(
                    'timeField',
                    'isTimeField'
                ),
                // array (
                //     'reportRow, reportColumn',
                //     'validateRowColumn'
                // ),
            )
        );
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'timeField' => Yii::t('charts', 'Dates'),
            'labelField' => Yii::t('charts', 'Groups'),
            'aggregateField' => Yii::t('charts', 'Aggregate Values'),
        ));
    }

    public function optional($attribute) {
    }

    public function isTimeField($attribute) {
        if ($this->hasErrors())
            return false;

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $columns = $report->setting('columns');
        $field = X2Model::model('Fields')->findByAttributes (array (
            'fieldName' => $this->$attribute,
            'modelName' => $report->setting('primaryModelType')
            )
        );

        if ($field->type == 'date' || $field->type == 'dateTime') {
            return true;
        }

        $this->addError ($attribute,
            Yii::t('charts','Date field must only contain dates')
        );

        return false;

    }


    public function getHelpItems() {
        return array(
            'timeField' => Yii::t('charts', "Choose a column on the report grid that contains dates such as 'Create Date' or 'Updated On'"),
            'labelField' => Yii::t('charts',  "Choose a column on the report grid to group the data that contains discrete values such as 'Status', 'Type' or 'Assigned To.'"),
            'aggregateField' => Yii::t('charts', 'Choose a column that contains numerical data to be displayed on the y-axis on the cart. ')
        );
    }
}

?>
