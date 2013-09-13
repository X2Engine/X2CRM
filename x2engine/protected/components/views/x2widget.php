<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/X2Chart.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/X2ActionHistoryChart.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/X2EventsChart.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/X2UsersChart.js', CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCssFile(
	Yii::app()->getTheme()->getBaseUrl().'/css/x2chart.css');

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/jquery.jqplot.js');
Yii::app()->clientScript->registerCssFile(
	Yii::app()->request->baseUrl . '/js/jqplot/jquery.jqplot.css');

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.pieRenderer.js');

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.categoryAxisRenderer.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.pointLabels.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.dateAxisRenderer.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.highlighter.js');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/jqplot/plugins/jqplot.enhancedLegendRenderer.js');
Yii::app()->clientScript->registerCoreScript('cookie');

Yii::app()->clientScript->registerScriptFile(
	Yii::app()->request->baseUrl . '/js/checklistDropdown/jquery.multiselect.js');
Yii::app()->clientScript->registerCssFile(
	Yii::app()->request->baseUrl . '/js/checklistDropdown/jquery.multiselect.css');

if(!is_null($model) && property_exists($name,'model')){
    $params = array('model'=>$model, 'modelName'=>($modelType));
}else{
    $params=array();
}

if ($name === 'InlineRelationships' && isset ($moduleName)) {
    $params['moduleName'] = $moduleName;
}

if($name == 'WorkflowStageDetails') { // workflow has an extra param
	if(isset($this->controller)) {
    	$params['currentWorkflow'] = $this->controller->getCurrentWorkflow($model->id, $modelType);
    } else {
    	$params['currentWorkflow'] = $this->getCurrentWorkflow($model->id, $modelType);
    }
}

if ($name === 'RecordViewChart') {

	$params['widgetParams'] = array ();
	$params['widgetParams']['chartType'] = 'actionHistoryChart';
	$params['widgetParams']['hideByDefault'] = false;

	
} 

$this->widget($name, $params);


?>
