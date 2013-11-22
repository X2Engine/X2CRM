<?php
/*********************************************************************************
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
 ********************************************************************************/
 
Yii::app()->clientScript->registerScript('topContacts',"
function addTopContact(contactId) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/users/users/addTopContact')) . "',
		type: 'GET',
		data: 'contactId='+contactId,
		//data: 'contactId='+contactId+'&viewId='+viewId,
		success: function(response) {
			if(response!='')
				$('#top-contacts-list').html(response);
				$('#sidebar-left-box').height($('#sidebar-left').height());
			}
	});
}
function removeTopContact(contactId) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/users/users/removeTopContact')) . "',
		type: 'GET',
		data: 'contactId='+contactId,
		// data: 'contactId='+contactId+'&viewId='+viewId,
		success: function(response) {
			if(response!='')
				$('#top-contacts-list').html(response);
				$('#sidebar-left-box').height($('#sidebar-left').height());
			}
	});
	//$('#contact'+id).remove();
}",CClientScript::POS_HEAD);

$actionParams = Yii::app()->controller->getActionParams();
//if(!isset($viewId) || $viewId == null)
	$viewId = isset($actionParams['id'])? $actionParams['id'] : null;

?>
<ul id="top-contacts-list">
<?php
$contactIdList = array();
foreach($topContacts as $contact) {
	$contactIdList[] = $contact->id;
	echo '<li id="contact' . $contact->id . '">';
	$link = '<strong>'.$contact->firstName.' '.$contact->lastName.'</strong><br />'.$contact->phone;
	echo CHtml::link($link,array('/contacts/contacts/view','id'=>$contact->id));
	
	echo CHtml::link('[x]','#',array(
		'class'=>'delete-link',
		'onclick'=>"removeTopContact('".$contact->id."'); return false;" //."','".$viewId."'); return false;"
	));
	echo "</li>\n";
}

if((Yii::app()->controller->id=='contacts' || (!is_null(Yii::app()->controller->module) && Yii::app()->controller->module->id=='contacts'))			// must be a contact
	&& Yii::app()->controller->action->id=='view'	// must be viewing it
	&& $viewId != null							// must have an actual ID value
	&& !in_array($viewId,$contactIdList)) {		// must not already be in Top Contacts

	$currentRecord = X2Model::model('Contacts')->findByPk($viewId);

	echo '<li>';
	echo CHtml::link(
		Yii::t('app','Add {name}',array('{name}'=>$currentRecord->firstName.' '.$currentRecord->lastName)),
		'#',
		array('onclick'=>"addTopContact('".$viewId."'); return false;") //"','".$viewId."'); return false;")
	);
	echo "</li>\n";;
}
?>
</ul>
