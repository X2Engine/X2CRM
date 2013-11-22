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
 
$this->widget('X2Chart', array (
	'getChartDataActionName' => Yii::app()->request->getScriptUrl () . '/site/GetActionsBetweenAction',
	'suppressChartSettings' => true,
	'actionParams' => array (
		'associationId' => $associationId,
		'associationType' => $associationType,
		'showRelationships' => 'false'
	),
	'metricTypes' => array (
		'any'=>Yii::t('app', 'All Actions'),
		''=>Yii::t('app', 'Tasks'),
		'attachment'=>Yii::t('app', 'Attachments'),
		'call'=>Yii::t('app', 'Calls'),
		'email'=>Yii::t('app', 'Emails'),
		'emailOpened'=>Yii::t('app', 'Emails Opened'),
		'event'=>Yii::t('app', 'Events'),
		'note'=>Yii::t('app', 'Notes'),
		'quotes'=>Yii::t('app', 'Quotes'),
		'webactivity'=>Yii::t('app', 'Web Activity'),
		'workflow'=>Yii::t('app', 'Workflow Actions')
	),
	'chartType' => 'actionHistoryChart',
	'getDataOnPageLoad' => true,
	'hideByDefault' => $hideByDefault
));

?>

