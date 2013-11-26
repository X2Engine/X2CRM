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
