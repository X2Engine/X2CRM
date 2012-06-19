<?php
/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */
?>
<?php
$this->menu = array(
	array('label' => Yii::t('dashboard', 'Lead Volume'), 'url' => array('leadVolume')),
	array('label' => Yii::t('dashboard', 'Lead Activity'), 'url' => array('leadActivity')),
	array('label' => Yii::t('dashboard', 'Lead Performance'), 'url' => array('leadPerformance')),
	array('label' => Yii::t('dashboard', 'Lead Sources')),
	array('label' => Yii::t('dashboard', 'Marketing'), 'url' => array('marketing')),
	array('label' => Yii::t('dashboard', 'Pipeline'), 'url' => array('pipeline')),
	array('label' => Yii::t('dashboard', 'Sales'), 'url' => array('sales')),
);
Yii::app()->clientScript->registerScript('leadPerformance',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});

	$('#lead-volume-chart').bind('jqplotDataHighlight', function (e, seriesIndex, pointIndex, data) {
			$('#pie-tooltip').html('<div style=\"font-size:14px;font-weight:bold;padding:5px;color:#000;background:white;background:rgba(255,255,255);\">' + data[0] + '</span>');
			$('#pie-tooltip').show();
			
			var chart_left = $('#lead-volume-chart').position().left;
				var chart_top = $('#lead-volume-chart').position().top;
		
			$(document).bind('mousemove.pieChart',function(e) {
				x = e.pageX; //plot.axes.xaxis.u2p(data[0]),  // convert x axis unita to pixels
				y = e.pageY; //plot.axes.yaxis.u2p(data[1]);  // convert y axis units to pixels
				$('#pie-tooltip').css({left:x-chart_left+5, top:y-50});
			});
		});

		// Bind a function to the unhighlight event to clean up after highlighting.
		$('#lead-volume-chart').bind('jqplotDataUnhighlight', 
		function (e, seriesIndex, pointIndex, data) {
			$(document).unbind('mousemove.pieChart');
			$('#pie-tooltip').empty();
			$('#pie-tooltip').hide();
		});

",CClientScript::POS_READY);
?>
<div class="form">
	<h2><?php echo Yii::t('dashboard', 'Lead Sources'); ?></h2>

	<?php
	// $_GET = array();
	
	$form = $this->beginWidget('CActiveForm', array(
		'action'=>$this->createUrl('leadSources'),
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get'
	));

	?>
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Workflow'),'workflow'); ?>
			<?php
			$workflowOptions = array();
			$query = Yii::app()->db->createCommand()
			->select('id,name')
			->from('x2_workflows')->query();
			while(($row = $query->read()) !== false)
				$workflowOptions[$row['id']] = $row['name'];

			echo CHtml::dropDownList('workflow',$workflow,$workflowOptions);
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>$this->formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>$this->formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>$this->formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('dashboard', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				'custom'=>Yii::t('dashboard','Custom'),
				'thisWeek'=>Yii::t('dashboard','This Week'),
				'thisMonth'=>Yii::t('dashboard','This Month'),
				'lastWeek'=>Yii::t('dashboard','Last Week'),
				'lastMonth'=>Yii::t('dashboard','Last Month'),
				// 'lastQuarter'=>Yii::t('dashboard','Last Quarter'),
				'thisYear'=>Yii::t('dashboard','This Year'),
				'lastYear'=>Yii::t('dashboard','Last Year'),
				
			),array('id'=>'dateRange'));
			?>
		</div>
	</div>
	<div class="row">
		<div class="cell">
			<?php
				echo $form->label($model,'assignedTo');
				echo $form->dropDownList($model,'assignedTo',$users);
			?>
		</div>
		<div class="cell">
			<?php
				echo $form->label($model,'company');

				$linkId = '';
				// if the field is an ID, look up the actual name
				if(isset($model->company) && ctype_digit($model->company)) {
					$linkModel = CActiveRecord::model('Accounts')->findByPk($model->company);
					if(isset($linkModel)) {
						$model->company = $linkModel->name;
						$linkId = $linkModel->id;
					} else {
						$model->company = '';
					}
				}
				// $linkSource = $this->createUrl(CActiveRecord::model('Accounts')->getAutoCompleteSource());
				$linkSource = $this->createUrl('/accounts/getItems');

				echo CHtml::hiddenField('Contacts[company_id]',$linkId,array('id'=>'Contacts_company_id'));
				$form->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'model'=>$model,
					'attribute'=>'company',
					// 'name'=>'autoselect_'.$fieldName,
					'source' => $linkSource,
					'value'=>$model->company,
					'options'=>array(
						'minLength'=>'1',
						'select'=>'js:function( event, ui ) {
							$("#Contacts_company_id").val(ui.item.id);
							$(this).val(ui.item.value);
							return false;
						}',
					),
				));
				// echo $form->textField($model,'company',array());
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::submitButton(Yii::t('dashboard','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;')); ?>
		</div>
	</div>
		<?php
		if(isset($dataProvider)) {
		$this->widget('zii.widgets.grid.CGridView', array(
			'id'=>'lead-activity-grid',
			'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
			'template'=> '{items}{pager}',
			// 'template'=> '<h2>'.Yii::t('dashboard','Lead Activity').'</h2><div class="title-bar">'
				// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
				// .CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
				// .CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
				// .'{summary}</div>{items}{pager}',
			'dataProvider'=>$dataProvider,
			// 'enableSorting'=>false,
			'enablePagination'=>false,
			// 'model'=>$model,
			//'filter'=>$model,
			// 'columns'=>$columns,
			//'modelName'=>'Contacts',
			// 'viewName'=>'leadsrccontacts',
			'columns'=>array(
				'user'=>array(
					'name'=>'leadSource',
					'header'=>Yii::t('contacts','Lead Source'),
					'value'=>'$data["leadSource"]',
					'type'=>'raw',
					'headerHtmlOptions'=>array('style'=>'width:20%')
				),
				'leads'=>array(
					'name'=>'leads',
					'header'=>Yii::t('contacts','Leads'),
					'value'=>'$data["leads"]',
					'type'=>'raw',
				),
				'interviewed'=>array(
					'name'=>'interviewed',
					'header'=>Yii::t('contacts','Interviewed'),
					'value'=>'$data["interviewed"]',
					'type'=>'raw',
				),
				'enrolled'=>array(
					'name'=>'enrolled',
					'header'=>Yii::t('contacts','Enrolled'),
					'value'=>'$data["enrolled"]',
					'type'=>'raw',
				),
				'started'=>array(
					'name'=>'started',
					'header'=>Yii::t('contacts','Started'),
					'value'=>'$data["started"]',
					'type'=>'raw',
				),
				'L_I_ratio'=>array(
					'name'=>'L_I_ratio',
					'header'=>Yii::t('contacts','L-I'),
					'value'=>'Yii::app()->controller->formatLeadRatio($data["interviewed"],$data["leads"])',
					'type'=>'raw',
				),
				'I_E_ratio'=>array(
					'name'=>'I_E_ratio',
					'header'=>Yii::t('contacts','I-E'),
					'value'=>'Yii::app()->controller->formatLeadRatio($data["enrolled"],$data["interviewed"])',
					'type'=>'raw',
				),
				'L_E_ratio'=>array(
					'name'=>'L_E_ratio',
					'header'=>Yii::t('contacts','L-E'),
					'value'=>'Yii::app()->controller->formatLeadRatio($data["enrolled"],$data["leads"])',
					'type'=>'raw',
				),
				'L_S_ratio'=>array(
					'name'=>'L_S_ratio',
					'header'=>Yii::t('contacts','L-S'),
					'value'=>'Yii::app()->controller->formatLeadRatio($data["started"],$data["leads"])',
					'type'=>'raw',
				),
			),
		));
	}
	?><br>
	<?php
	$form = $this->endWidget();

	?>
	
</div>








