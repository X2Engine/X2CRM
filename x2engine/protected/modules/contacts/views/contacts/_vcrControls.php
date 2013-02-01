<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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


$listId = Yii::app()->user->getState('vcr-list');
if(empty($listId))
	$listId = 'index';

Yii::app()->clientScript->registerScript('vcrListCookie', "
// $('#content').on('mouseup','#contacts-grid a',function(e) {
	// document.cookie = 'vcr-list=".$listId."; expires=0; path=/';
// });
",CClientScript::POS_READY);

$vcrControls = array();
$searchModel = new Contacts('search');

//listId should be either a number (for a list), 'index', or 'admin'
//convert numbers to list/# for uniform url path
if(is_numeric($listId)){
	$path = 'list/' . $listId;
}else
	$path = $listId;

//try to get the saved sort and filters from the session if applicable
//the strings in this code are tied to values specified in ERememberColumnFilters and SmartDataProvider
$order = Yii::app()->user->getState('contacts/contacts/'. $path . 'Contacts_sort');
$searchModel->setRememberScenario('contacts/contacts/'. $path);

//convert session var to sql
$order = preg_replace('/\.desc$/', ' DESC', $order);

//look up all ids of the list we are currently viewing
//find position of model in the list


// decide which data provider to use
if(is_numeric($listId)) {
	$list = X2Model::model('X2List')->findByPk($listId);
    if(isset($list)){
        $listLink = CHtml::link($list->name,array('/contacts/'.$path));
        $vcrDataProvider = $searchModel->searchList($listId);
    }else{
        $listLink = CHtml::link(Yii::t('contacts','All Contacts'),array('/contacts/'.$path));	// default to All Contacts
        $vcrDataProvider = $searchModel->searchAll();
    }
} elseif($listId=='myContacts') {
	$listLink = CHtml::link(Yii::t('contacts','My Contacts'),array('/contacts/'.$path));
	$vcrDataProvider = $searchModel->searchMyContacts();
} elseif($listId=='newContacts') {
	$listLink = CHtml::link(Yii::t('contacts','New Contacts'),array('/contacts/'.$path));
	$vcrDataProvider = $searchModel->searchNewContacts();
} else {
	$listLink = CHtml::link(Yii::t('contacts','All Contacts'),array('/contacts/'.$path));	// default to All Contacts
	$vcrDataProvider = $searchModel->searchAll();
}
if(empty($order))
	$order = $vcrDataProvider->sort->getOrderBy();
if(!empty($order))
	$vcrDataProvider->criteria->order = $order;

// run SQL to get VCR links
$vcrData = X2List::getVcrLinks($vcrDataProvider,$model->id);

// if this contact isn't on the list, default to All Contacts (unless we already tried that)
if($vcrData === false && $listId !== 'index') {
	$listLink = CHtml::link(Yii::t('contacts','All Contacts'),array('/contacts/'.$path));
	$vcrDataProvider = $searchModel->searchAll();
	
	if(empty($order))
		$order = $vcrDataProvider->sort->getOrderBy();
	if(!empty($order))
		$vcrDataProvider->criteria->order = $order;
    
	
	$vcrData = X2List::getVcrLinks($vcrDataProvider,$model->id);
}

if(is_array($vcrData) && count($vcrData)) {

	
?>
<div class="vcrPager">
	<div class="summary">
		<?php if(isset($listLink)) echo $listLink; ?>
		<?php echo Yii::t('contacts','<b>{m}</b> of <b>{n}</b>',array('{m}'=>$vcrData['index'],'{n}'=>$vcrData['count'])); ?>
	</div>
	<?php
	//echo CHtml::tag('ul',array('class'=>'vcrPager'),$vcrData['prev']."\n".$vcrData['next']);
	if(isset($vcrData['prev']))
		echo $vcrData['prev'];
	if(isset($vcrData['next']))
		echo $vcrData['next'];
	?>
</div>
<?php

}
