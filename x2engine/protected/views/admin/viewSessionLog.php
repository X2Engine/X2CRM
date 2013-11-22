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
?>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sessions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('admin','Session Log').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
            'name'=>'user',
            'header'=>'User',
            'type'=>'raw',
            'value'=>'User::getUserLinks($data->user)',
        ),
        array(
            'name'=>'sessionId',
            'header'=>'Session ID',
            'type'=>'raw',
        ),
        array(
            'header'=>'Session History',
            'type'=>'raw',
            'value'=>'CHtml::link("Session History","#",array("id"=>$data->sessionId,"class"=>"session-link"))',
        ),
        array(
            'name'=>'timestamp',
            'header'=>'Timestamp',
            'type'=>'raw',
            'value'=>'Formatter::formatCompleteDate($data->timestamp)',
        ),
        array(
            'name'=>'status',
            'header'=>'Session Event',
            'type'=>'raw',
            'value'=>'SessionLog::parseStatus($data->status)',
        ),
        array(
            'header'=>'Active',
            'type'=>'raw',
            'value'=>'Session::model()->countByAttributes(array("id"=>$data->sessionId))?"<b>Active</b>":"Inactive"',
        ),
	),
));
Yii::app()->clientScript->registerScript("session-history",'
    $(document).on("click",".session-link",function(e){
        e.preventDefault();
        var link=$(this);
        $.ajax({
            url:"viewSessionHistory",
            type:"GET",
            data:{"id":$(link).attr("id")},
            success:function(data){
                $("#session-history").hide().html(data).fadeIn();
                $("html, body").animate({
                    scrollTop: $("#session-history").offset().top
                }, 500);
            }
        });
    });
');
?>
<br>
<div class="grid-view" id="session-history" style="display:none;">
    
</div>