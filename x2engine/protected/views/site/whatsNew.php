<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/
?>

<?php

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
	$(".contact-name").each(function (i) {
		var contactId = $(this).attr("href").match(/\\d+$/);

		if(typeof contactId != null && contactId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.baseUrl+"/index.php/contacts/qtip",
						data: { id: contactId[0] },
						method: "get",
					}
				},
				style: {
				}
			});
		}
	});
}

$(function() {
	refreshQtip();
});
');
?>

<h1><?php echo Yii::t('app','What\'s New'); ?></h1>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'whatsNew-grid',
	'template'=>Yii::t('app','Records that have been modified since your last login.').'{summary}{items}{pager}',
	'dataProvider' => $dataProvider,
	'summaryText' => Yii::t('app','Displaying {start}-{end} of {count} result(s).') . '<br />'
		. '<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;"> '
		. CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
		    	'ajax' => array(
		    		'url' => $this->createUrl('/profile/setResultsPerPage'),
		    		'complete' => "function(response) { $.fn.yiiGridView.update('whatsNew-grid', {data: {'id_page': 1}}) }",
		    		'data' => "js: {results: $(this).val()}",
		    	),
		    	'style' => 'margin: 0;',
		    ))
		. ' </div>'
		. Yii::t('app', 'results per page.'),
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'afterAjaxUpdate'=>'refreshQtip',
	'columns' => array(
		array(
			'name' => Yii::t('app','Name'),
			'type' => 'raw',
			'value' => 'CHtml::link(CHtml::encode($data["name"]), "'.Yii::app()->request->baseUrl.'/index.php".$data["link"], array("class"=>($data["type"]=="Contact"? "contact-name":null)))', 
		),
		array(
			'name' => Yii::t('actions','Type'),
			'type' => 'raw',
			'value' => 'Yii::t("app",CHtml::encode($data["type"]))'
		),
		array(
			'name' => Yii::t('actions','Description'),
			'type' => 'raw',
			'value' => 'Yii::app()->controller->truncateText(CHtml::encode($data["description"]),20)'
		),
		array(
			'name' => Yii::t('actions','Last Updated'),
			'type' => 'raw',
			'value' => 'CHtml::encode(Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat("medium"), $data["lastUpdated"]))'
		),
		array(
			'name' => Yii::t('actions','Updated By'),
			'type' => 'raw',
			'value' => '$data["updatedBy"]'
		),
	),
));
?>