#!/usr/bin/php
<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






/**
 * An AGI script that makes use of X2Engine's API to produce call notifications.
 *
 * Requires PHPAGI: http://phpagi.sourceforge.net/ (should come standard in FreePBX Distro)
 */
$baseUrl = ''; // Set this to your CRM's URL, with the entry script ('index.php') appended
$user = '';  // Set this to a valid X2Engine user's username
$userKey = ''; // Set this to the user's API key

require_once "phpagi.php";
require_once "APIModel.php";

$agi = new AGI();
$cid = $agi->parse_callerid();

if($cid['username'] == '') // Can't do anything; caller ID is empty.
	exit(0); 

$defaultOpts = array(
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_HTTP200ALIASES => array(400,401,403,404,500),
	CURLOPT_CONNECTTIMEOUT => 3,
);

function newModel() {
	global $baseUrl,$user,$userKey;
	new APIModel($user, $userKey, $baseUrl);
}

// If the caller ID has name enabled, search for a preexisting contact with the same name
$contact = null;
if($cid['name']!='') {
	$agi->verbose('Caller has name enabled: '.$cid['name']);
	$lastFirst = false;
	if(strpos($cid['name'],',') !== false) {
		$fln = explode(',',$cid['name']);
		$lastFirst = true; // lastName, firstName
	} else
		$fln = explode(' ',$cid['name']); // firstName lastName
	$attr = array();
	if(count($fln) > 1) {
		$attr[($lastFirst ? 'lastName'  : 'firstName')] = trim($fln[0]);
		$attr[($lastFirst ? 'firstName' : 'lastName' )] = trim(implode(' ',array_slice($fln,1)));
		$contact = newModel();
		$contact->attributes = $attr;
		$contact->contactLookup();
		if($contact->responseCode == 404) { 
			$agi->verbose('Creating a new contact in X2Engine using the available data.');
			// Time to create a new contact!
			$attr['phone'] = $cid['username'];
			$attr['visibility'] = 1;
			$attr['backgroundInfo'] = 'Contact created via AGI; called in '.strftime('%h %e, %r');
			$attr['leadSource'] = 'Call-In';
			$contact->attributes = $attr;
			$contact->contactCreate();
		} else if($contact->responseCode == 200) {
			$agi->verbose('An existing contact was found in X2Engine matching the name.');
			// Create a call log (experimental/unfinished)
			//$action =  newModel();
			//$action->associationType = 'contacts';
			//$action->associationId = $contact->id;
			//$action->dueDate = time();
			//$action->completeDate = time();
		}
	}	
}

// First check to see if there's already a contact, and if this is
// a repeat caller. 
$ch = curl_init("$baseUrl/api/voip?data={$cid['username']}");
curl_setopt_array($ch,$defaultOpts);
$cr = curl_exec($ch);

if(empty($cr)) {
	$cr = array('error'=>true,'message'=>'Failed connecting to X2Engine.');
	$apiResponseCode = 0;
} else {
	$cr = json_decode($cr,1); 
	$apiResponseCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
}

$agi->verbose("($apiResponseCode) ".$cr['message']);

exit(0);
?>
