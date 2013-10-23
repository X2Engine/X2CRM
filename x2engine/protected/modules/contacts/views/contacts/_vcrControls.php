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


$listId = Yii::app()->user->getState('vcr-list');
if(empty($listId))
	$listId = 'index';

/*Yii::app()->clientScript->registerScript('vcrListCookie', "
// $('#content').on('mouseup','#contacts-grid a',function(e) {
	// document.cookie = 'vcr-list=".$listId."; expires=0; path=/';
// });
",CClientScript::POS_READY);*/

$vcrControls = array();
$searchModel = new Contacts('search');
$tagFlag=false;
//listId should be either a number (for a list), 'index', or 'admin'
//convert numbers to list/# for uniform url path
if(is_numeric($listId)){
	$path = 'list/' . $listId;
}elseif(strpos($listId,'#')===0){
    $tagFlag=true;
	$path = $listId;
}else{
    $path = $listId;
}

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
        $listLink = CHtml::link($list->name,array('/contacts/list','id'=>$listId));
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
} elseif($tagFlag){
    $listLink = CHtml::link(Yii::t('contacts','Tag Search'),array('/search/search?term='.$listId));
    $_GET['tagField']=$listId;
    $vcrDataProvider = $searchModel->searchAll();
} else {
	$listLink = CHtml::link(Yii::t('contacts','All Contacts'),array('/contacts/'.$path));	// default to All Contacts
	$vcrDataProvider = $searchModel->searchAll();
}
if(empty($order) && !$tagFlag)
	$order = $vcrDataProvider->sort->getOrderBy();
elseif(empty($order) && $tagFlag)
	$order = $vcrDataProvider->criteria->order;
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
