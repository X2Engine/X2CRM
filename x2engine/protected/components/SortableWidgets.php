<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

/**
 * CJuiSortable class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

class SortableWidgets extends CJuiWidget
{
	/**
	 * @var array list of sortable items (id=>item content).
	 * Note that the item contents will not be HTML-encoded.
	 */
	public $portlets=array();
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
	
		Yii::app()->clientScript->registerScript('toggleWidgetState',"
			function toggleWidgetState(widget,state) {
				$.ajax({
					url: '" . CHtml::normalizeUrl(array('site/widgetState')) . "',
					type: 'GET',
					data: 'widget='+widget+'&state='+state,
					success: function(response) {
						if(response=='success') {
							var link = $('#widget_'+widget+' .portlet-minimize a');
							var newLink = (link.html()=='[+]')? '[&ndash;]' : '[+]';			// toggle link between [+] and [-]
							link.html(newLink);
							$('#widget_'+widget+' .portlet-content').toggle('blind',{},200);	// slide widget open or closed
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

		$options=empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
		Yii::app()->getClientScript()->registerScript('SortableWidgets'.'#'.$id,"jQuery('#{$id}').sortable({$options});");

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

		$hideWidgetJs = '';
		
		foreach($this->portlets as $class=>$properties) {
			$visible = ($properties['visibility'] == '1');
			
			if(!$visible)
				$hideWidgetJs .= "$('#widget_" . $class . " .portlet-content').hide();\n";
			
			$minimizeLink = CHtml::link($visible? '[&ndash;]' : '[+]','#',array('onclick'=>"toggleWidgetState('$class',".($visible? 0 : 1)."); return false;"));

			$this->beginWidget('zii.widgets.CPortlet',array(
				'title'=>Yii::t('app',Yii::app()->params->registeredWidgets[$class]) . '<div class="portlet-minimize">'.$minimizeLink.'</div>',
				'id'=>$properties['id']
			));
			$this->widget($class);
			$this->endWidget();
		}
		Yii::app()->clientScript->registerScript('setWidgetState', "
			$(document).ready(function() {
				" . $hideWidgetJs . "
			});",CClientScript::POS_HEAD);
		
		echo CHtml::closeTag($this->tagName);
	}
}


