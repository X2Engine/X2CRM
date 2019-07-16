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




require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'protected','components','util','ResponseUtil.php')));

function throwException(){
    throw new Exception("I'm dyin'. Here's how.");
}

if(!isset($_GET['case'])) {
    die('Test case ("case") is a required parameter.');
}

$c = $_GET['case'];
$case = substr($c,0,strpos($c,'.'));
if($case == '')
    $case = $c;
$subcase = substr($c,strpos($c,'.')+1);
switch ($case) {
    case 'isCli':
        header('Content-type: text/plain');
        echo (integer) !ResponseUtil::isCli();
        break;
    case 'respond':
        ResponseUtil::$errorCode = 400;
        switch($subcase) {
            case 'errTrue':
                ResponseUtil::respond($subcase,true);
                break;
            case 'errFalse':
                ResponseUtil::respond($subcase,false);
                break;
            case 'property':
                $r = new ResponseUtil;
                $r['property'] = 'value';
                ResponseUtil::respond($subcase,false);
                break;
        }
        break;
    case 'respondWithError':
        set_error_handler('ResponseUtil::respondWithError');
        switch($subcase) {
            case 'nonFatalFalse':
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                ResponseUtil::respond('All clear!',false);
                break;
            case 'nonFatalTrue':
                ResponseUtil::$exitNonFatal = true;
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                break;
            case 'longErrorTrace':
                ResponseUtil::$exitNonFatal = true;
                ResponseUtil::$longErrorTrace = true;
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                break;
        }
        break;
    case 'respondFatalErrorMessage':
        register_shutdown_function('ResponseUtil::respondFatalErrorMessage');
        switch($subcase){
            case 'parse':
                $e = 'a';
                // Trigger a parse error:
                eval('return $e"bc";');

                break;
            case 'class':
                $odysseus = new NoMan;
                break;
        }
        break;
    case 'respondWithException':
        set_exception_handler('ResponseUtil::respondWithException');
        switch($subcase) {
            case 'normal';
                throw new Exception("I'm dyin' here.");
                break;
            case 'long':
                ResponseUtil::$longErrorTrace = true;
                throwException();
                break;
        }
        break;
    case 'catchDouble':
        set_exception_handler('ResponseUtil::respondWithException');
        $response = new ResponseUtil();
        $response = new ResponseUtil();
        break;
    case 'sendHttp':
        $r = new ResponseUtil;
        if(ctype_digit($subcase) || is_int($subcase)){
            $r->sendHttp($subcase);
        }
        $r['message'] = 'The response';
        $r['error'] = false;
        switch($subcase) {
            case 'badCode':
                $r->sendHttp(666);
                break;
            case 'extraHeader':
                $r->httpHeader['Content-MD5'] = base64_encode(md5('not the content'));
                $r->sendHttp();
                break;
            case 'raw':
                $r->body = 'The message in plain text.';
                $r->httpHeader['Content-Type'] = 'text/plain';
                $r->sendHttp();
                break;
        }
        break;
    case 'setProperties':
        $r = new ResponseUtil();
        $r->setProperties(array('foo'=>'bar','message'=>'ni'));
        $r->sendHttp();
        break;
    default:
        die('Unknown test case.');
}

?>
