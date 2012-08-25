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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl() . '/css/vcrPager.css');

$listId = Yii::app()->user->getState('vcr-list');
if (empty($listId)) $listId = 'index';
$vcrControls = array();
$searchModel = new Contacts('search');

//listId should be either a number (for a list), 'index', or 'admin'
//convert numbers to list/# for uniform url path
if (is_numeric($listId)) 
	$path = 'list/' . $listId;
else
	$path = $listId;

//try to get the saved sort and filters from the session if applicable
//the strings in this code are tied to values specified in ERememberColumnFilters and SmartDataProvider
$order = Yii::app()->user->getState('contacts/default/'. $path . 'Contacts_sort');
$searchModel->setRememberScenario('contacts/default/'. $path);

//convert session var to sql
$order = preg_replace('/\.desc$/', ' DESC', $order);

//look up all ids of the list we are currently viewing
//find position of model in the list
if (is_numeric($listId)) {
	$list = CActiveRecord::model('X2List')->findByPk($listId);
	$dataProvider = $searchModel->searchList($listId);
	$criteria = $dataProvider->criteria;
	if (empty($order)) $order = $dataProvider->sort->getOrderBy();
	if (!empty($order)) $criteria->order = $order;
	$tableSchema = Contacts::model()->getTableSchema();
	$ids = Yii::app()->db->getCommandBuilder()->createFindCommand($tableSchema, $criteria)->select('id')->queryColumn();
	$thisIndex = current(array_keys($ids, $model->id));
}
 
//if no list, or model is not in specified list
//use default all contacts list
if (!is_numeric($listId) || $thisIndex === false) {
	$dataProvider = $searchModel->searchAll();
	$criteria = $dataProvider->criteria;
	if (empty($order)) $order = $dataProvider->sort->getOrderBy();
	if (!empty($order)) $criteria->order = $order;
	$tableSchema = Contacts::model()->getTableSchema();
	$ids = Yii::app()->db->getCommandBuilder()->createFindCommand($tableSchema, $criteria)->select('id')->queryColumn();
	$thisIndex = current(array_keys($ids, $model->id));
}

//back to where we came from button
$vcrControls['back'] = '<li class="back">'.CHtml::link(CHtml::button('Back', array('class'=>'x2-button')), array($path)).'</li>';

if ($thisIndex !== false) {
	if ($thisIndex > 0) {
		$vcrControls['first'] = '<li class="first">'.CHtml::link(CHtml::button('◄◄', array('class'=>'x2-button')), array("view","id"=>$ids[0])).'</li>';
		$vcrControls['prev'] = '<li class="prev">'.CHtml::link(CHtml::button('◄', array('class'=>'x2-button')), array("view","id"=>$ids[$thisIndex-1])).'</li>';
	} else {
		//same looking buttons but disabled
		$vcrControls['first'] = '<li class="first">'.CHtml::button('◄◄', array('class'=>'x2-button','disabled'=>'disabled')).'</li>';
		$vcrControls['prev'] = '<li class="prev">'.CHtml::button('◄', array('class'=>'x2-button','disabled'=>'disabled')).'</li>';
	}
	if ($thisIndex < count($ids)-1) {
		$vcrControls['next'] = '<li class="next">'.CHtml::link(CHtml::button('►', array('class'=>'x2-button')), array("view","id"=>$ids[$thisIndex+1])).'</li>';
		$vcrControls['last'] = '<li class="last">'.CHtml::link(CHtml::button('►►', array('class'=>'x2-button')), array("view","id"=>$ids[count($ids)-1])).'</li>';
	} else {
		//same looking buttons but disabled
		$vcrControls['next'] = '<li class="next">'.CHtml::button('►', array('class'=>'x2-button','disabled'=>'disabled')).'</li>';
		$vcrControls['last'] = '<li class="last">'.CHtml::button('►►', array('class'=>'x2-button','disabled'=>'disabled')).'</li>';
	}
}
?>

<?php if (count($vcrControls) > 0) { ?>
	<div class="vcrPager">
		<div class="summary"><b><?php echo $thisIndex+1; ?></b> of <b><?php echo count($ids); ?></b></div>
		<?php echo CHtml::tag('ul',array('class'=>'vcrPager'),implode("\n",$vcrControls)); ?>
	</div>
<?php } ?>
