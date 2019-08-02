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
 * Helper web script for testing API hooks (for data pull requests)
 * 
 * The only thing this script really needs to do is save the incoming data in 
 * the testing output folder for examination and testing.
 */
$testsDir = implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    'protected',
    'tests',
));

$users = require(implode(DIRECTORY_SEPARATOR,array(
    $testsDir,
    'fixtures',
    'x2_users.php'
)));

$requestData = array();
$requestData['body'] = json_decode(file_get_contents('php://input'),1);

$hookName = isset($_GET['name'])?$_GET['name']:null;

// Saving to files:
$outPath = implode(DIRECTORY_SEPARATOR,array(
    $testsDir,
    'data',
    'output',
));
file_put_contents($outPath.DIRECTORY_SEPARATOR."hook_$hookName.json", json_encode($requestData));

if(isset($requestData['body']['resource_url'])) {
    // Make a GET request to retrieve the data that X2Engine requested us to retrieve:
    $ch = curl_init($requestData['body']['resource_url']);
    $options = array(
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "{$users['admin']['username']}:{$users['admin']['userKey']}",
        CURLOPT_RETURNTRANSFER => true,
    );
    curl_setopt_array($ch,$options);
    file_put_contents($outPath.DIRECTORY_SEPARATOR."hook_pulled_$hookName.json",curl_exec($ch));
}