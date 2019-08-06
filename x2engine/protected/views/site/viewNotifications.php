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




Yii::app()->clientScript->registerCss('viewNotificationsCss',"

#clear-all-button {
    margin-top: 4px;
}

");
?>
<div class="flush-grid-view">
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'notifs-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'template'=>'<div class="page-title"><h2>'.Yii::t('app','Notifications').'</h2>'
		.CHtml::link(Yii::t('app','Clear All'),'#',array(
			'class'=>'x2-button right',
            'id' => 'clear-all-button',
			'submit'=>array('/notifications/deleteAll'),
			'confirm'=>Yii::t('app','Permanently delete all notifications?'),
			'params'=>array (
                'YII_CSRF_TOKEN' => Yii::app()->request->csrfToken,
            )
		))
		.'<div class="title-bar right">{summary}</div></div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
    .'<div class="form no-border" style="display:inline;"> '
    .CHtml::dropDownList(
        'resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(),
        array(
            'ajax' => array(
                'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                'data' => 'js:{results:$(this).val()}',
                'complete' => 'function(response) { 
                    $.fn.yiiGridView.update("notifs-grid"); 
                }',
            ),
            'style' => 'margin: 0;',
        )
    ).'</div>',
	'columns'=>array(
		array(
			// 'name'=>'text',
			'header'=>Yii::t('actions','Notification'),
			'value'=>'$data->getMessage()',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:70%'),
		),
		array(
			'name'=>'createDate',
			'header'=>Yii::t('actions','Time'),
			'value'=>'date("Y-m-d",$data->createDate)." ".date("g:i A",$data->createDate)',
			'type'=>'raw',
		),
		array(
			'class'=>'X2ButtonColumn',
			'template'=>'{delete}',
			'deleteButtonUrl'=>'Yii::app()->controller->createUrl("/notifications/delete",array("id"=>$data->id))',
			'afterDelete'=>'function(link,success,data){
                var match = $(link).attr ("href").match (/[0-9]+$/);
                if (match !== null) x2.Notifs.triggerNotifRemoval (match[0]);
            }',
			'deleteConfirmation'=>false,
			'headerHtmlOptions'=>array('style'=>'width:40px'),
		 ),
	),
	'rowCssClassExpression'=>'$data->viewed? "" : "unviewed"',
    'pager' => array (
        'class' => 'CLinkPager', 
        'header' => '',
        'firstPageCssClass' => '',
        'lastPageCssClass' => '',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'firstPageLabel' => '<<',
        'lastPageLabel' => '>>',
    ),
));

?>
</div>
<?php

foreach($dataProvider->getData() as $notif) {
	if(!$notif->viewed) {
		$notif->viewed = true;
		$notif->save();
	}
}
unset($notif);

?>
