<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'sessions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<h2>'.Yii::t('admin','Active Sessions').'</h2><div class="title-bar">'
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'user',
        'IP',
        array(
            'name'=>'lastUpdated',
            'header'=>'Last Activity',
            'type'=>'raw',
            'value'=>'Actions::formatCompleteDate($data->lastUpdated)',
        ),
        array(
            'name'=>'status',
            'header'=>'Status',
            'type'=>'raw',
            'value'=>'$data->status==1?"Active":"Invisible"',
        ),
        array(
            'header'=>'Toggle Invisible',
            'type'=>'raw',
            'value'=>"CHtml::link('Toggle','#',array('class'=>'x2-button', 'submit'=>'toggleSession?id='.\$data->id,'confirm'=>'Are you sure you want to change this session\'s status?'))"
        ),
        array(
            'header'=>'End Session',
            'type'=>'raw',
            'value'=>"CHtml::link('End','#',array('class'=>'x2-button', 'submit'=>'endSession?id='.\$data->id,'confirm'=>'Are you sure you want to end this session?'))"
        ),
	),
));
?>
