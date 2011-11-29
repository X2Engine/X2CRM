<?php
$this->breadcrumbs=array(
	'Workflows'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>Yii::t('workflow','List Workflow'), 'url'=>array('index')),
	array('label'=>Yii::t('workflow','Create Workflow'), 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('workflow-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h2><?php echo Yii::t('workflow','Manage Workflows'); ?></h2>
<?php echo Yii::t('app','You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.'); ?>
<br />

<?php //echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'workflow-grid',
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'template'=> '<div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1))
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'name',
		'lastUpdated',
		array(
			'class'=>'CButtonColumn',
			'headerHtmlOptions'=>array('style'=>'width:70px;'),
		),
	),
)); ?>
