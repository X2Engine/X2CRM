<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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


Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiSortable class.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package X2CRM.components
 */
class SortableWidgets extends CJuiWidget {

	/**
	 * @var array list of sortable items (id=>item content).
	 * Note that the item contents will not be HTML-encoded.
	 */
	public $portlets = array();
	public $jQueryOptions = array();

	/**
	 * @var string the name of the container element that contains all items. Defaults to 'ul'.
	 */
	public $tagName = 'div';

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run(){
		$themeURL = Yii::app()->theme->getBaseUrl();
		Yii::app()->clientScript->registerScript('logos', base64_decode(
						'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
						.'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
						.'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));

		Yii::app()->clientScript->registerScript('toggleWidgetState', "
			function toggleWidgetState(widget,state) {
				if($('#widget_' + widget).hasClass('ui-sortable-helper') == false) {
					$.ajax({
						url: '".CHtml::normalizeUrl(array('/site/widgetState'))."',
						type: 'GET',
						data: 'widget='+widget+'&state='+state,
						success: function(response) {
							if(response=='success') {
								var link = $('#widget_'+widget+' .portlet-minimize a.portlet-minimize-button');
								var newLink = ($(link).find('img').attr('class')=='expand-widget')? '<img src=\"".$themeURL."/images/icons/Collapse_Widget.png\" class=\'collapse-widget\' />' : '<img src=\"".$themeURL."/images/icons/Expand_Widget.png\" class=\'expand-widget\'/>';			// toggle link between [+] and [-]
								link.html(newLink);

								// slide widget open or closed
								$('#widget_'+widget+' .portlet-content').toggle('blind',{},200,function() {
									if(widget == 'GoogleMaps' && $(this).is(':visible'))	// for google maps, trigger a resize event
										google.maps.event.trigger(window.map,'resize');
								});
							}
						}
					});
				}

			}
		", CClientScript::POS_HEAD);

		$id = $this->getId(); //get generated id
		if(isset($this->htmlOptions['id']))
			$id = $this->htmlOptions['id'];
		else
			$this->htmlOptions['id'] = $id;

		$options = empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
		Yii::app()->getClientScript()->registerScript('SortableWidgets'.'#'.$id, "jQuery('#{$id}').sortable({$options});");

		echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

		$widgetHideList = array();
		if(!Yii::app()->user->isGuest){
			$layout = Yii::app()->params->profile->getLayout();
		}else{
			$layout = array();
		}

		foreach($this->portlets as $class => $properties){
			if(!in_array($class, array_keys($layout['hiddenRight']))){ // show widget if it isn't hidden
				$visible = ($properties['visibility'] == '1');

				if(!$visible)
					$widgetHideList[] = '#widget_'.$class;
				
				// $minimizeLink = '<div class="collapse-widget '.($visible? 'collapse-widget' : 'expand-widget').'"></div><div class="close-widget"></div>';
				
				
				
				$minimizeLink = CHtml::link(
					$visible ? CHtml::image($themeURL.'/images/icons/Collapse_Widget.png', '', array('class' => 'collapse-widget')) : CHtml::image($themeURL.'/images/icons/Expand_Widget.png', '', array('class' => 'expand-widget'))
					, '#', array('class' => 'portlet-minimize-button')
				)
						.' '.CHtml::link(CHtml::image($themeURL.'/images/icons/Close_Widget.png'), '#', array('onclick' => "$('#widget_$class').hideWidgetRight(); return false;"));

				// $t0 = microtime(true);
				// for($i=0;$i<100;$i++)
				$widget = $this->widget($class, $properties['params'], true);

				// $t1 = microtime(true);
				$profile = yii::app()->params->profile;
				if($profile->activityFeedOrder){
					$activityFeedOrderSelect = 'top';
				}else{
					$activityFeedOrderSelect = 'bottom';
				}
				if(!empty($widget)){
					$this->beginWidget('zii.widgets.CPortlet', array(
						'title' => '<div '.(($class == 'ChatBox')?'style="text-align:left"':'').'>'.(
						$class == 'ChatBox' ?
								CHtml::dropDownList("activityFeedDropDown", $activityFeedOrderSelect, array('top' => 'Top Down', 'bottom' => 'Bottom Up'), array('style' => 'float:left;margin-top:-1px;'))
								.CHtml::link(Yii::t('app', 'Activity Feed'), array('/site/whatsNew'), array('style' => 'text-decoration:none;margin-left:10px;')) :
								Yii::t('app', Yii::app()->params->registeredWidgets[$class])
						).'<div class="portlet-minimize" onclick="toggleWidgetState(\''.$class.'\','.($visible ? 0 : 1).'); return false;">'.$minimizeLink.'</div></div>',
						'id' => $properties['id']
					));
					echo $widget;
					$this->endWidget();
					// echo ($t1-$t0);
				}else{
					echo '<div ', CHtml::renderAttributes(array('style' => 'display;none;', 'id' => $properties['id'])), '></div>';
				}
			}
		}


		Yii::app()->clientScript->registerScript('setWidgetState', '
			$(document).ready(function() {
				$("'.implode(',', $widgetHideList).'").find(".portlet-content").hide();
			});', CClientScript::POS_HEAD);

		echo CHtml::closeTag($this->tagName);
	}

}

