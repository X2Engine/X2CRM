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
	'template'=> '<div class="page-title"><h2>'.Yii::t('admin','Active Sessions').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'user',
        'IP',
        array(
            'name'=>'lastUpdated',
            'header'=>Yii::t('admin','Last Activity'),
            'type'=>'raw',
            'value'=>'Formatter::formatCompleteDate($data->lastUpdated)',
        ),
        array(
            'name'=>'status',
            'header'=>Yii::t('admin','Status'),
            'type'=>'raw',
            'value'=>'$data->status==1?"Active":"Invisible"',
        ),
        array(
            'header'=>Yii::t('admin','Toggle Invisible'),
            'type'=>'raw',
            'value'=>"CHtml::link(Yii::t('admin','Toggle'),'#',array('class'=>'x2-button toggle-session', 'id'=>\$data->id))"
        ),
        array(
            'header'=>Yii::t('admin','End Session'),
            'type'=>'raw',
            'value'=>"CHtml::link(Yii::t('admin','End'),'#',array('class'=>'x2-button end-session', 'title'=>\$data->id))"
        ),
	),
));
Yii::app()->clientScript->registerScript('session-controls','
$(document).on("click",".toggle-session",function(e){
    e.preventDefault();
    var link=this;
    if(confirm('.Yii::t('admin',"Are you sure you want to toggle this session?").')){
        $.ajax({
            url:"toggleSession?id="+$(this).attr("id"),
            success:function(data){
                if(data==1){
                    $(link).parent().prev().html("Active");
                }else if(data==0){
                    $(link).parent().prev().html("Inactive");
                }
            }
        });
    }
});

$(document).on("click",".end-session",function(e){
    e.preventDefault();
    var link=this;
    if(confirm('.Yii::t('admin',"Are you sure you want to end this session?").')){
        $.ajax({
            url:"endSession?id="+$(this).attr("title"),
            success:function(){
                $.fn.yiiGridView.update("sessions-grid");
            }
        });
    }
});
');
?>
