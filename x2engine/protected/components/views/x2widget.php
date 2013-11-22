<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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

	/* x2prostart */
	if ($modelType === 'Marketing' && $model->type === 'Email') {
		$params['widgetParams']['hideByDefault'] = true;
		$this->widget($name, $params);
		$params['widgetParams']['chartType'] = 'campaignChart';
	}
	/* x2proend */
} 

$this->widget($name, $params);


?>
