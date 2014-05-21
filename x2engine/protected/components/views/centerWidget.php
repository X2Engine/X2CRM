<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

if ($this instanceof X2WidgetList) {
    Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerCoreScript('jquery.ui');
    Yii::app()->clientScript->packages = X2WidgetList::packages ();
    Yii::app()->clientScript->registerCssFiles ('widgetCombinedCss', array (
        "galleryWidgetCssOverrides.css",
        "../../../js/gallerymanager/bootstrap/css/bootstrap.css",
        "../../../js/jqplot/jquery.jqplot.css",
        "x2chart.css",
        "workflowFunnel.css",
    ));
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
	Yii::app()->clientScript->registerScript('recordViewChartJS', "
		$(document).on ('chartWidgetMaximized', function () {
			var chartName = 'actionHistoryChart';
			".
			
			"
			x2[chartName].chart.show ();
			x2[chartName].chart.replot ();
		});

        this.popupDropdownMenu = new PopupDropdownMenu ({
            containerElemSelector: '.chart-controls-container',
            openButtonSelector: '#chart-settings-button',
            onClickOutsideSelector: 
                '#chart-settings-button, ' +
                '.chart-controls-container, ' +
                '.ui-datepicker-header, .ui-multiselect-header, .ui-multiselect-checkboxes',
            autoClose: false,
            defaultOrientation: 'left',
            css: {
                'max-width': '100%',
                padding: '6px'
            }
        });

        /***********************************************************************
        * chart subtype selection  
        ***********************************************************************/

		$('#chart-subtype-selector').on ('click', function (evt) {
			return false;
		});
		$('#chart-subtype-selector').on ('change', function (evt) {
			var selectedSubType = $(this).val ();
			var selectedChart = 'actionHistoryChart';
			".
			
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



$themeUrl = Yii::app()->theme->getBaseUrl();

// only used in InlineRelationships title; shows the number of relationships
$relationshipCount = ""; 
if($name == "InlineRelationships"){
    $modelName = ucwords($modelType);
    $relationshipsDataProvider = new CArrayDataProvider($model->relatedX2Models, array(
                'id' => 'relationships-gridview',
                'sort' => array(
                    'attributes' => array('name', 'myModelName', 'createDate', 'assignedTo')),
                'pagination' => array('pageSize' => 10)
            ));
    $relationshipCount = " (".$relationshipsDataProvider->totalItemCount.")";
}
?>


<div class="x2-widget form x2-layout-island" id="x2widget_<?php echo $name; ?>">
    <div class="x2widget-header">

		<?php
		if ($name === 'RecordViewChart') {
		?>
			<!--  -->
			<span class="x2widget-title">
				<b><?php echo Yii::t('app', Yii::t('app', 'Action History')); ?></b>
			</span>
			<!--  -->
			<select id='chart-subtype-selector' class='x2-select'>
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
            <b><?php echo Yii::t('app', $widget['title'])?></b>
            <?php if ($relationshipCount !== '') { ?> 
            <b id='relationship-count'>
            <?php echo $relationshipCount; ?> 
            </b>
            <?php } ?>
        </span>
		<?php
		}
		?>

        <?php if(!Yii::app()->user->isGuest){ ?>
            <div class="portlet-minimize">
                <?php
		        if ($name === 'RecordViewChart') {
                    echo X2Html::settingsButton (Yii::t('app', 'Action History Chart Settings'), 
                        array (
                            'id' => 'chart-settings-button',
                        ));
                }
                ?> 
                <a onclick="$('#x2widget_<?php echo $name; ?>').minimizeWidget(); return false" 
                 href="#" class="x2widget-minimize">
					<?php
					if ($widget['minimize']) {
						echo CHtml::image(
                            $themeUrl.'/images/icons/Expand_Widget.png', 
                            Yii::t('app', 'Maximize Widget'),
							array (
                                'title' => Yii::t('app', 'Maximize Widget'),
                                'class' => 'center-widget-maximize-button',
                            ));
					} else {
						echo CHtml::image(
                            $themeUrl.'/images/icons/Collapse_Widget.png', 
                            Yii::t('app', 'Minimize Widget'),
							array (
                                'title' => Yii::t('app', 'Minimize Widget'),
                                'class' => 'center-widget-minimize-button',
                            ));
					}
					?>
				</a>
				<?php
					/*echo CHtml::image(
						$themeUrl.'/css/gridview/arrow_both.png',
						Yii::t('app', 'Sort Widget'),
						array (
							'title' => Yii::t('app', 'Sort Widget'),
							'class' => 'widget-sort-handle'
						)
					);*/
				?>
                <a onclick="$('#x2widget_<?php echo $name; ?>').hideWidget(); return false" 
                 href="#">
					<?php 
                    echo CHtml::image(
                        $themeUrl.'/images/icons/Close_Widget.png', Yii::t('app', 'Close Widget'),
						array (
                            'title' => Yii::t('app', 'Close Widget'),
                            'class' => 'center-widget-close-button',
                        )); 
                    ?>
				</a>
            </div>
        <?php } ?>
    </div>
    <div class="x2widget-container" 
     style="<?php echo $widget['minimize'] ? 'display: none;' : ''; ?>">
        <?php 
        $params = array (
            'widget' => $widget,
            'name' => $name, 
            'model' => $model, 
            'modelType' => $modelType,
            'widgetParams' => (isset ($widgetParams) ? $widgetParams : array ())
        ); 
        if (isset ($moduleName)) {
            $params['moduleName'] = $moduleName;
        }
        if(isset($this->controller)){ // not ajax  
            $this->render('x2widget', $params);
        } else { // we are in an ajax call 
            $this->renderPartial('application.components.views.x2widget', $params);
        } 
        ?>
    </div>
</div>
