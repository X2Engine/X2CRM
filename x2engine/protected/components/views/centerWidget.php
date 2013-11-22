<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

if ($this instanceof X2WidgetList) {
    Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerCoreScript('jquery.ui');
    Yii::app()->clientScript->packages = X2WidgetList::packages ();
    Yii::app()->clientScript->registerPackage('widgetListCombinedCss');
    Yii::app()->clientScript->registerPackage('widgetListCombinedCss2');
    if ($name === 'GalleryWidget') {
        Yii::import('application.extensions.gallerymanager.GalleryManager');
        $galleryWidget = new GalleryManager ();
        $galleryWidget->init ();
        $galleryWidgetAssets = $galleryWidget->assets;
        Yii::app()->clientScript->registerScriptFile(
            $galleryWidgetAssets.'/jquery.iframe-transport.js');
        Yii::app()->clientScript->registerScriptFile(
            $galleryWidgetAssets.'/jquery.galleryManager.js');
        Yii::app()->clientScript->registerPackage('GalleryWidgetJS');
        //Yii::app()->clientScript->registerPackage('GalleryWidgetCss');
    } else if ($name === 'RecordViewChart') {
        Yii::app()->clientScript->registerPackage('ChartWidgetExtJS');
        Yii::app()->clientScript->registerPackage('ChartWidgetJS');
        //Yii::app()->clientScript->registerPackage('ChartWidgetCss');
    } else if ($name === 'InlineRelationships') {
        Yii::app()->clientScript->registerPackage('InlineRelationshipsJS');
    } else if ($name === 'InlineTags') {
        Yii::app()->clientScript->registerPackage('InlineTagsJS');
    }
}

if (isset ($packagesOnly) && $packagesOnly) return;

// check if we need to load a model
if(!isset($model) && isset($modelType) && isset($modelId)){
    // didn't get passed a model, but we have the modelType and modelId, so load the model
    $model = X2Model::model($modelType)->findByPk($modelId);
}

if ($name === 'RecordViewChart') {
	Yii::app()->clientScript->registerScript('chartShowHide', "
		$(document).on ('chartWidgetMaximized', function () {
			var chartName = 'actionHistoryChart';
			".
			/* x2prostart */	
			($modelType === 'Marketing' && $model->type === 'Email' ? 'chartName = $("#chart-type-selector").val ();' : '').
			/* x2proend */
			"
			x2[chartName].chart.show ();
			x2[chartName].chart.replot ();
		});
	");

	Yii::app()->clientScript->registerScript('chartSubTypeSelection', "
		$('#chart-subtype-selector').on ('click', function (evt) {
			return false;
		});
		$('#chart-subtype-selector').on ('change', function (evt) {
			var selectedSubType = $(this).val ();
			var selectedChart = 'actionHistoryChart';
			".
			/* x2prostart */	
			($modelType === 'Marketing' && $model->type === 'Email' ? 
			"
			selectedChart = $('#chart-type-selector').val ();
			x2['actionHistoryChart'].chart.setChartSubtype (
                selectedSubType, true, false, true);	
			x2['campaignChart'].chart.setChartSubtype (selectedSubType);	
			$.cookie ('recordViewChartSelectedSubtype', selectedSubType);
			return;
			" : "")
			./* x2proend */
			"
			x2['actionHistoryChart'].chart.setChartSubtype (
                selectedSubType, true, false, true);	
			$.cookie ('recordViewChartSelectedSubtype', selectedSubType);
		});
		if ($.cookie ('recordViewChartSelectedSubtype')) {
			// set chart type using cookie
			$('#chart-subtype-selector').find ('option').each (function () {
				$(this).removeAttr ('selected');
			});
			$('#chart-subtype-selector').children ().each (function () {
				if ($(this).val () === $.cookie ('recordViewChartSelectedSubtype'))
					$(this).attr ('selected', 'selected');
			});
		} 
	");
}

/* x2prostart */
if ($name === 'RecordViewChart' && $modelType === 'Marketing' && $model->type === 'Email') {
	Yii::app()->clientScript->registerScript('chartSelection', "
		$('#chart-type-selector').on ('click', function (evt) {
			return false;
		});
		$('#chart-type-selector').on ('change', function (evt) {
			var selectedChart = $(this).val ();
			if (selectedChart === 'actionHistoryChart') {
				x2['campaignChart'].chart.hide ();
				x2['actionHistoryChart'].chart.show ();
				x2['actionHistoryChart'].chart.replot ();
			} else if (selectedChart === 'campaignChart') {
				x2['actionHistoryChart'].chart.hide ();
				x2['campaignChart'].chart.show ();
				x2['campaignChart'].chart.replot ();
			}
			$.cookie ('marketingSelectedChart', selectedChart);
		});

		var chartsReady = 0;
		// event triggered by _x2chart.js
		$(document).on ('actionHistoryChartReady campaignChartReady', function () { 
			if (++chartsReady === 2)
				checkChartShow ();
		});
	
		function checkChartShow () {
			var currChartSubtype = $('#chart-subtype-selector').val ();
			x2['campaignChart'].chart.setChartSubtype (currChartSubtype, false);	
			x2['actionHistoryChart'].chart.setChartSubtype (currChartSubtype, false);	

			if ($.cookie (x2['actionHistoryChart'].chart.cookiePrefix + 'chartIsShown') === 
				'true' ||
				$.cookie (x2['campaignChart'].chart.cookiePrefix + 'chartIsShown') === 
				'true') {
				var currChart = $('#chart-type-selector').val ();
				x2[currChart].chart.show ();
				x2[currChart].chart.replot ();
			} else {
				x2['campaignChart'].chart.show ();
				x2['campaignChart'].chart.replot ();
			}
		}

		if ($.cookie ('marketingSelectedChart')) {
			// set chart type using cookie
			$('#chart-type-selector').find ('option').each (function () {
				$(this).removeAttr ('selected');
			});
			$('#chart-type-selector').children ().each (function () {
				if ($(this).val () === $.cookie ('marketingSelectedChart'))
					$(this).attr ('selected', 'selected');
			});
		} 
	");
}
/* x2proend */

if ($name === 'RecordViewChart') {
}



$themeUrl = Yii::app()->theme->getBaseUrl();
$relationshipCount = ""; // only used in InlineRelationships title; shows the number of relationships
if($name == "InlineRelationships"){
    $modelName = ucwords($modelType);
    $relationshipsDataProvider = new CArrayDataProvider($model->relatedX2Models, array(
                'id' => 'relationships-gridview',
                'sort' => array('attributes' => array('name', 'myModelName', 'createDate', 'assignedTo')),
                'pagination' => array('pageSize' => 10)
            ));
    $relationshipCount = " (".$relationshipsDataProvider->totalItemCount.")";
}
?>


<div class="x2-widget form" id="x2widget_<?php echo $name; ?>">
    <div class="x2widget-header">

		<?php
		if ($name === 'RecordViewChart') {
		?>
			<!-- /* x2prostart */ -->
			<?php 
			if ($modelType === 'Marketing' && $model->type === 'Email') {
			?>
			<select id='chart-type-selector'>
				<option value='campaignChart'>
					<?php echo Yii::t('app', 'Campaign'); ?>
				</option>
				<option value='actionHistoryChart'>
					<?php echo Yii::t('app', 'Action History'); ?>
				</option>
			</select>
			<?php
			} else {
			?>
			<!-- /* x2proend */ -->
			<span class="x2widget-title">
				<b><?php echo Yii::t('app', Yii::t('app', 'Action History')); ?></b>
			</span>
			<!-- /* x2prostart */ -->
			<?php
			} 
			?> 
			<!-- /* x2proend */ -->
			<select id='chart-subtype-selector'>
				<option value='line'>
					<?php echo Yii::t('app', 'Line Chart'); ?>
				</option>
				<option value='pie'>
					<?php echo Yii::t('app', 'Pie Chart'); ?>
				</option>
			</select>
		<?php
		} else {
		?> 
        <span class="x2widget-title">
            <b><?php echo Yii::t('app', $widget['title']).$relationshipCount; ?></b>
        </span>
		<?php
		}
		?>

        <?php if(!Yii::app()->user->isGuest){ ?>
            <div class="portlet-minimize">
                <a onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false" href="#" class="x2widget-minimize">
					<?php
					if ($widget['minimize']) {
						echo CHtml::image($themeUrl.'/images/icons/Expand_Widget.png', Yii::t('app', 'Maximize Widget'),
							array ('title' => Yii::t('app', 'Maximize Widget')));
					} else {
						echo CHtml::image($themeUrl.'/images/icons/Collapse_Widget.png', Yii::t('app', 'Minimize Widget'),
							array ('title' => Yii::t('app', 'Minimize Widget')));
					}
					?>
				</a>
				<?php
					echo CHtml::image(
						$themeUrl.'/css/gridview/arrow_both.png',
						Yii::t('app', 'Sort Widget'),
						array (
							'title' => Yii::t('app', 'Sort Widget'),
							'class' => 'widget-sort-handle'
						)
					);
				?>
                <a onclick="$('#x2widget_<?php echo $name; ?>').hideWidget(); return false" href="#">
					<?php echo CHtml::image($themeUrl.'/images/icons/Close_Widget.png', Yii::t('app', 'Close Widget'),
						array ('title' => Yii::t('app', 'Close Widget'))); ?>
				</a>
            </div>
        <?php } ?>
    </div>
    <div class="x2widget-container" style="<?php echo $widget['minimize'] ? 'display: none;' : ''; ?>">
        <?php 
        $widgetParams = array (
            'widget' => $widget,
            'name' => $name, 
            'model' => $model, 
            'modelType' => $modelType
        ); 
        if (isset ($moduleName)) {
            $widgetParams['moduleName'] = $moduleName;
        }
        if(isset($this->controller)){ // not ajax  
            $this->render('x2widget', $widgetParams);
        } else{ // we are in an ajax call 
            $this->renderPartial('application.components.views.x2widget', $widgetParams);
        } 
        ?>
    </div>
</div>
