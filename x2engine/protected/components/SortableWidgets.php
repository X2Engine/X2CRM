<?php
/*********************************************************************************
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
 ********************************************************************************/


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
class SortableWidgets extends CJuiWidget
{
	/**
	 * @var array list of sortable items (id=>item content).
	 * Note that the item contents will not be HTML-encoded.
	 */
	public $portlets = array();
	public $jQueryOptions = array();
	/**
	 * @var string the name of the container element that contains all items. Defaults to 'ul'.
	 */
	public $tagName='div';

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	*/
	public function run() {
		$themeURL = Yii::app()->theme->getBaseUrl();
		Yii::app()->clientScript->registerScript('logos',"
		$(window).load(function(){
			if((!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
				$('a').removeAttr('href');
				alert('Please put the logo back');
				window.location='http://www.x2engine.com';
			}
			var touchlogosrc = $('#x2touch-logo').attr('src');
			var logosrc=$('#x2crm-logo').attr('src');
			if(logosrc!='$themeURL/images/x2footer.png'|| touchlogosrc!='$themeURL/images/x2touch.png'){
				$('a').removeAttr('href');
				alert('Please put the logo back');
				window.location='http://www.x2engine.com';
			}
		});    
		");
		Yii::app()->clientScript->registerScript('toggleWidgetState',"
			function toggleWidgetState(widget,state) {
				$.ajax({
					url: '" . CHtml::normalizeUrl(array('/site/widgetState')) . "',
					type: 'GET',
					data: 'widget='+widget+'&state='+state,
					success: function(response) {
						if(response=='success') {
							var link = $('#widget_'+widget+' .portlet-minimize a');
							var newLink = (link.html()=='[+]')? '[&ndash;]' : '[+]';			// toggle link between [+] and [-]
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
		",CClientScript::POS_HEAD);

		$id=$this->getId();	//get generated id
		if (isset($this->htmlOptions['id']))
			$id = $this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		$options = empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
		Yii::app()->getClientScript()->registerScript('SortableWidgets'.'#'.$id,"jQuery('#{$id}').sortable({$options});");

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

		$widgetHideList = array();
		
		foreach($this->portlets as $class=>$properties) {
			$visible = ($properties['visibility'] == '1');
			
			if(!$visible)
				$widgetHideList[] = '#widget_'.$class;
			
			$minimizeLink = CHtml::link($visible? '[&ndash;]' : '[+]','#',array('onclick'=>"toggleWidgetState('$class',".($visible? 0 : 1)."); return false;"));
			// $t0 = microtime(true);
			// for($i=0;$i<100;$i++)
				$widget = $this->widget($class,$properties['params'],true);
			
			// $t1 = microtime(true);
			if(!empty($widget)) {
				$this->beginWidget('zii.widgets.CPortlet',array(
					'title'=>Yii::t('app',Yii::app()->params->registeredWidgets[$class]) . '<div class="portlet-minimize">'.$minimizeLink.'</div>',
					'id'=>$properties['id']
				));
				echo $widget;
				$this->endWidget();
				// echo ($t1-$t0);
			} else {
				echo '<div ',CHtml::renderAttributes(array('style'=>'display;none;','id'=>$properties['id'])),'></div>';
			}
		}
		
		
		Yii::app()->clientScript->registerScript('setWidgetState', '
			$(document).ready(function() {
				$("' . implode(',',$widgetHideList) . '").find(".portlet-content").hide();
			});',CClientScript::POS_HEAD);
		
		echo CHtml::closeTag($this->tagName);
	}
}


