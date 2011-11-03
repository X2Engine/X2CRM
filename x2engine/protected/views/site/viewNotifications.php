<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'actions-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(

			'name'=>'text',
			'header'=>Yii::t('actions','Notification'),
			'value'=>'$data->viewed==0?"<b>".$data->text."</b>" : $data->text',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
		array(
			'name'=>'record',
			'header'=>Yii::t('actions','Link To Record'),
			'value'=>'NotificationChild::parseLink($data->record)',
			'type'=>'raw',
		),
                array(
			'name'=>'createDate',
			'header'=>Yii::t('actions','Timestamp'),
			'value'=>'date("Y-m-d",$data->createDate)." at ".date("g:i:s A",$data->createDate)',
			'type'=>'raw',
		)
			//'type'
	),
));

$arr=$dataProvider->getData();
foreach($arr as $notif){
    if($notif->viewed==0){
        $notif->viewed=1;
        $notif->save();
    }
}
?>
