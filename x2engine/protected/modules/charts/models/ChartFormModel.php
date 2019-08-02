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
class ChartFormModel extends CFormModel {
    

    /**
     * @var int Id of the current report
     */    
    public $reportId;
    
    /**
     * @see rules()
     */
    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array(
                    'reportId',
                    'required'
                )
            )
        );
    }


    /**
     * @see attributeLabels()
     */
    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'reportId' => Yii::t('reports', 'Report'),
        ));
    }

    /**
     * Returns the chart Type from this class name
     * @return string Name of the chart type
     */
    public function getChartType() {
        $match = array();
        
        if( !preg_match('/(.*)FormModel/', get_class($this), $match) ) {
            return false;
        }

        return $match[1];
    }

    public function columnType($attribute, $params) {
        if (!$this->$attribute) {
            return true;
        }

        if ($this->hasErrors())
            return false;

        AuxLib::debugLogR($params);

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $field = X2Model::model('Fields')->findByAttributes (array (
            'fieldName' => $this->$attribute,
            'modelName' => $report->setting('primaryModelType')
            )
        );

        if ($field && in_array($field->type, $params['types'])) {
            return true;
        }

        $this->addError ($attribute,
            Yii::t('charts','Field type must be one of the following: {type}', array (
                '{type}'=> implode(', ', $params['types'])
            ))
        );

        return false;

    }


    public function isValidColumn($attribute) {
        if (!$this->$attribute) {
            return true;
        }

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $columns = $report->setting('columns');

        if (!in_array($this->$attribute, $columns)) {
            $this->addError ($attribute,
                Yii::t('charts','The column "{column}" was not found in the report', array('{column}' => $this->$attribute))
            );
            return false;
        }

        return true;
    }

}

?>
