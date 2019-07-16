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




Yii::import('application.modules.reports.components.reports.*');

/**
 * This is the model class for table "x2_reports".
 *
 * The followings are the available columns in table 'x2_reports':
 */
class Reports extends X2Model {

    /**
     * @var string $settings JSON-encoded report form model attributes
     */
    public $settings; 

    /**
     * @var string $type 'rowsAndColumns'|'grid'|'summation'|'external'
     */
    public $type; 

    /**
     * @var string $version the version of X2Engine in which the report was saved
     */
    public $version; 
    
    public $supportsWorkflow = false;

    public static function model ($className=__CLASS__) {
        return parent::model ($className);
    }

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'x2_reports_2';
	}

    public function rules () {
        $rules = parent::rules ();
        return array_merge (array (
            array (
                'settings', 'validateSettings'
            ),
        ), $rules);
    }

    public function behaviors() {
        $behaviors = array_merge(parent::behaviors(), array (
            'WidgetLayoutJSONFieldsBehavior' => array(
                'class' => 'application.components.behaviors.WidgetLayoutJSONFieldsBehavior', 
                'transformAttributes' => array (
                    'dataWidgetLayout' => SortableWidget::DATA_WIDGET_PATH_ALIAS
                ) 
            ) ,
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
        )); 
        unset ($behaviors['TagBehavior']);
        unset($behaviors['relationships']);
        return $behaviors;
    }

    public function relations() {
        $rules = parent::relations ();
        return array_merge (array (
            'charts' => array(self::HAS_MANY, 'Charts', 'reportId'),
        ), $rules);

    }

    public function validateSettings ($attribute) {
        $value = $this->$attribute;

        // make attempt to parse JSON if value isn't already an array. This allows reports to be
        // validated on import
        if (!is_array ($value)) {
            $value = CJSON::decode ($value);
        }
        if (!is_array ($value)) {
            throw new CHttpException (400, Yii::t('app', '{attribute} must be an array', array (
                '{attribute}' => $attribute,
            )));
        }

        if (count ($value) > 1) {
            return false;
        }
        $keys = array_keys ($value);
        $formModelName = array_pop ($keys);
        if (!in_array ($formModelName, 
            array ('SummationReportFormModel', 'RowsAndColumnsReportFormModel', 
                'GridReportFormModel', 'ExternalReportFormModel'))) {

            return false;
        }
        $formModel = new $formModelName;
        $formModel->setAttributes ($value[$formModelName]);
        if (!$formModel->validate ()) {
            //AuxLib::debugLogR ($formModel->getErrors ());
            $this->addError ($attribute, Yii::t('reports', 'Invalid report settings')) ;
            return false;
        }
        $this->type = $formModel->getReportType ();
        $this->$attribute = CJSON::encode ($formModel->getSettings ());
    }

    public function getFormModelName () {
        return ucfirst ($this->type).'ReportFormModel';
    }

    public function changeSetting($key, $value) {
        $settings = CJSON::decode ($this->settings);
        $settings[$key] = $value;
        $this->settings = CJSON::encode ($settings);
    }

    public function setting($key) {
        $settings = CJSON::decode ($this->settings);
        return $settings[$key];
    }

    public function addFilters($filters) {
        $oldfilters = $this->setting('allFilters');
        if (!is_array ($oldfilters)) $oldfilters = array ();
        $filters = array_merge ($oldfilters, $filters);
        $this->changeSetting ('allFilters', $filters);
    }

    /**
     * Returns whether a widget supports a this 
     * By checking if the widget contains the get{reportType}Data() function
     * @param string $chartType the chart type to test support for 
     */
    public function chartSupports($chartType) {
        return method_exists($chartType.'Widget', 'get'.ucfirst($this->type).'Data');
    }


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
	 */
	public function search ($pageSize=null) {
		$criteria = new CDbCriteria;
        return $this->searchBase ($criteria, $pageSize);
	}

    /**
     * @return string 
     */
    public function getPrettyType () {
        return self::prettyType ($this->type);
    }

    /**
     * Retrieves as setting such as column field or rowField,
     * And returns the field label
     * @param $setting string the key name in settings
     */
    public function getAttrLabel($fieldName) {

        list($model, $attr) = $this->instance->getModelAndAttr($fieldName);
        $field = $model->getField ($attr);
        if ($field) {
            return $field->attributeLabel;
        }

        return ucfirst($fieldName);

    }

    public static function prettyType ($type) {
        return Yii::t('reports', preg_replace ('/And/', 'and', ucfirst (Formatter::deCamelCase ($type))));
    }

    public function getClassName() {
        if ($this->type == 'grid')
            return 'X2GridReport';
        else if ($this->type == 'summation') {
            return 'X2SummationReport';
        } else if ($this->type == 'rowsAndColumns') {
            return 'X2RowsAndColumnsReport';
        } 

    }

    public static function getExternalReportUrl($path) {
        $credsId = Yii::app()->settings->jasperCredentialsId;
        $creds = Credentials::model()->findByPk($credsId);
        if ($creds) {
            $server = $creds->auth->server;
            $user = $creds->auth->username;
            $pass = $creds->auth->password;
            $reportName = str_replace('/', '%2F', $path);
            $uri = "{$server}/flow.html?_flowId=viewReportFlow&decorate=no&j_username={$user}&j_password={$pass}&reportUnit={$reportName}";
            return $uri;
        } else {
            return null;
        }
    }

    public function getInstance () {
        $reportName = $this->getClassName();

        $report = new $reportName;
        $settings = CJSON::decode($this->settings);
        if (!$settings) $settings = array ();

        foreach($settings as $key => $value) {
            if (property_exists($report, $key) ||
            method_exists($report, 'set'.$key)) {
                $report->$key = $value;
            }
        }

        return $report;
    }

    public function afterDelete () {
        foreach($this->charts as $chart) {
            $chart->delete();
        }
        return parent::afterDelete ();
    }

}
