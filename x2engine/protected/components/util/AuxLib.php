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
 * Standalone class with miscellaneous utility functions
 */
class AuxLib {

    /**
     * @param int $errCode php file upload error code 
     */
    public static function getFileUploadErrorMessage ($errCode) {
        switch ($errCode) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $errMsg = Yii::t('app', 'File exceeds the maximum upload size.');
                break;
            case UPLOAD_ERR_PARTIAL:
                $errMsg = Yii::t('app', 'File upload was not completed.');
                break;
            case UPLOAD_ERR_NO_FILE:
                $errMsg = Yii::t('app', 'Zero-length file uploaded.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                break;
            case UPLOAD_ERR_CANT_WRITE:
                break;
            case UPLOAD_ERR_EXTENSION:
                break;
            default: 
                $errMsg = Yii::t('app', 'Failed to upload file.');
        }
        return $errMsg;
    }

    /**
     * @return bool True if the file upload failed with errors, false otherwise
     */
    public static function checkFileUploadError ($name) {
        if (!isset ($_FILES[$name])) return false;
        if(empty($_FILES[$name]['tmp_name'])) 
            return true;
        return false;
    }

    /**
     * Registers a script which instantiates a dictionary of translations.
     * @param string $scriptName The name of the script which will be registered
     *   and which will be a property of the global JS object x2.
     * @param array $messages An associateive array (<message label> => <untranslated message>)
     * @param string $translationFile The first parameter to Yii::t
     * @param string $namespace The name of the JS object which will contain the translations
     *  dictiory
     */
    public static function registerTranslationsScript (
        $namespace, $messages, $translationFile='app', $scriptName='passMsgsToClientScript') {

        $passVarsToClientScript = "
            if (!x2.".$namespace.") x2.".$namespace." = {};
            x2.".$namespace.".translations = {};
        ";
        foreach ($messages as $key=>$val) {
            $passVarsToClientScript .= "x2.".$namespace.".translations['".
                $key. "'] = '" . addslashes (Yii::t($translationFile, $val)) . "';\n";
        }
        Yii::app()->clientScript->registerScript(
            $scriptName, $passVarsToClientScript,
            CClientScript::POS_HEAD);
    }

    /**
     * @param string $namespace The name of the JS object which will contain the translations
     *  dictionary. For nested namespaces, each namespace should be separated by a '.' character.
     * @param array $vars An associative array (<var name> => <var value>)
     * @param string $scriptName The name of the script which will be registered
     *   and which will be a property of the global JS object x2.
     */
    public static function registerPassVarsToClientScriptScript (
        $namespace, $vars, $scriptName='passVarsToClientScript') {

        $namespaces = explode ('.', $namespace);
        $rootNamespace = array_shift ($namespaces);

        // declare nested namespaces one at a time if they don't already exist, starting at the root
        $passVarsToClientScript = "
            ;(function () {
                if (typeof ".$rootNamespace." === 'undefined') ".$rootNamespace." = {};
                var namespaces = ".CJSON::encode ($namespaces).";
                var prevNameSpace = ".$rootNamespace.";

                for (var i in namespaces) {
                    if (typeof prevNameSpace[namespaces[i]] === 'undefined') {
                        prevNameSpace[namespaces[i]] = {};
                    }
                    prevNameSpace = prevNameSpace[namespaces[i]];
                }
            }) ();
        ";
        foreach ($vars as $key=>$val) {
            $passVarsToClientScript .= $namespace.".".$key." = ".$val.";";
        }
        Yii::app()->clientScript->registerScript(
            $scriptName, $passVarsToClientScript,
            CClientScript::POS_HEAD);
    }

    /**
     * Used by actions to return JSON encoded array containing error status and error message.
     * Used for testing purposes only.
     */
    public static function printTestError ($message) {
        if (YII_DEBUG) echo CJSON::encode (array ('error' => array (Yii::t('app', $message))));
    }

    /**
     * Used by actions to return JSON encoded array containing error status and error message.
     */
    public static function printError ($message) {
        echo CJSON::encode (array (false, $message));
    }

    /**
     * Used by actions to return JSON encoded array containing success status and success message.
     */
    public static function printSuccess ($message) {
        echo CJSON::encode (array (true, $message));
    }

    /**
     * Calls printError or printSuccess depending on the value of $success.
     *
     * @param bool $success 
     * @param string $successMessage
     * @param string $errorMessage
     * @return array (<bool>, <string>)
     */
    public static function ajaxReturn ($success, $successMessage, $errorMessage) {
        if ($success) {
            self::printSuccess ($successMessage);
        } else { // !$success
            self::printError ($errorMessage);
        }
    }

    /**
     * Used to log debug messages
     */
    public static function debugLog ($message) {
        if (!YII_DEBUG) return;
        Yii::log ($message, 'error', 'application.debug');
    }

    public static function debugLogR ($arr) {
        if (!YII_DEBUG) return;
        $logMessage = print_r ($arr, true);
        Yii::log ($logMessage, 'error', 'application.debug');
    }

    /**
     * Render a hex dump to the debug log
     * Adapted from: https://stackoverflow.com/a/4225813
     */
    public static function debugLogHd ($data) {
        if (!YII_DEBUG) return;
        static $from = '';
        static $to = '';
        static $width = 16; # number of bytes per line
        static $pad = '.'; # padding for non-visible characters

        if ($from==='') {
            for ($i=0; $i<=0xFF; $i++) {
                $from .= chr($i);
                $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
            }
        }

        $hex = str_split(bin2hex($data), $width*2);
        $chars = str_split(strtr($data, $from, $to), $width);

        $output = "\n"; // begin on next line
        $offset = 0;
        foreach ($hex as $i => $line) {
            $hexBytes = str_split($line, 16);
            $hexContent = implode(' ', str_split($hexBytes[0],2));
            if (isset($hexBytes[1]))
                $hexContent .= '  '.implode(' ', str_split($hexBytes[1],2));
            $output .= sprintf('%06X  %-48s',$offset, $hexContent). ' |' . $chars[$i] . "|\n";
            $offset += $width;
        }
        self::debugLog ($output);
    }

    /**
     * Generic version of debugLogR 
     */
    public static function logR ($arr, $route) {
        $logMessage = print_r ($arr, true);
        Yii::log ($logMessage, 'error', 'application.'.$route);
    }

    public static function debugLogExport ($arr) {
        if (!YII_DEBUG) return;
        $logMessage = var_export ($arr, true);
        Yii::log ($logMessage, 'error', 'application.debug');
    }

    public static function isIE8 () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie 8/i', $userAgentStr);
    }

    public static function isIE () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie/i', $userAgentStr);
    }

    public static function isAndroid () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/android/', $userAgentStr);
    }

    public static function isIPad () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/ipad/', $userAgentStr);
    }

    public static function getLayoutType () {
        $pathInfo = strtolower(Yii::app()->request->getPathInfo ());
        if (AuxLib::isIE8 () || strpos ($pathInfo, 'admin') === 0 ||
            preg_match ('/flowDesigner(\/\d+)?$/', $pathInfo)) {

            return 'static';
        } else {
            return 'responsive';
        }
    }

    /**
     * @return mixed The IE version if available, otherwise infinity 
     */
    public static function getIEVer () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        preg_match('/msie ([0-9]+)/', $userAgentStr, $matches);
        if (sizeof ($matches) === 2) {
            $ver = (int) $matches[1];
        } else {
            $ver = INF;
        }
        return $ver;
    }

    public static function setCookie ($key, $val, $time) {
        if (YII_DEBUG) { // workaround which allows chrome to set cookies for localhost
            $serverName = Yii::app()->request->getServerName() === 'localhost' ? '' :
                Yii::app()->request->getServerName();
        } else {
            $serverName = Yii::app()->request->getServerName();
        }
        setcookie($key,$val,time()+$time,dirname(Yii::app()->request->getScriptUrl()), $serverName);
    }

    public static function clearCookie ($key){
        if(YII_DEBUG){ // workaround which allows chrome to set cookies for localhost
            $serverName = Yii::app()->request->getServerName() === 'localhost' ? '' :
                    Yii::app()->request->getServerName();
        }else{
            $serverName = Yii::app()->request->getServerName();
        }
        unset($_COOKIE[$key]);
        setcookie(
            $key, '', time() - 3600, dirname(Yii::app()->request->getScriptUrl()), $serverName);
    }

    /**
     * Generates parameter binding placeholders for each element in array
     * @param array $arr parameter values to be bound in a SQL query
     * @param string $prefix prefix to use for paramater names
     * @return array parameter values indexed by parameter name
     */
    public static function bindArray ($arr, $prefix='X2') {
        $placeholders = array ();
        $arrLen = sizeof ($arr);
        for ($i = 0; $i < $arrLen; ++$i) {
            $placeholders[] = ':' . $prefix . $i;
        }
        if ($arrLen === 0) {
            return array ();
        } 
        return array_combine ($placeholders, $arr);
    }

    public static function arrToStrList ($arr) {
        return '('.implode (',', $arr).')';
    }

    /**
     * @deprecated 
     */
    public static function coerceToArray (&$arr) {
        $arr = ArrayUtil::coerceToArray ($arr);
    }

    /**
     * Prints stack trace 
     * @param int $limit If set, only the top $limit items on the call stack will get printed. 
     *  debug_backtrace does have an optional limit argument, but it wasn't introduced until php
     *  5.4.0.
     */
    public static function trace ($limit=null) {
        if ($limit !== null) {
            /**/AuxLib::debugLogR (
                array_slice (debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS), 0, $limit));
        } else {
            /**/AuxLib::debugLogR (debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS));
        }
    }

    /**
     * Checks if a string is json
     * @param string a string to be checked 
     * @return bool this function returns true if the string passed in is json
     */    
    public static function isJson($string) {
     json_decode($string);
     return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Checks if a command exists in your system
     * @param string the command in question
     * @return bool this function returns true if the command exists
     */        
    public static function command_exist($cmd) {
        $returnVal = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
        return !empty($returnVal);
    }

    /**
     * Reformats and translates dropdown arrays to preserve sorting in {@link CJSON::encode()}
     * @param array an associative array of dropdown options ($value => $label)
     * @return array a 2-D array of values and labels
     */
    public static function dropdownForJson($options) {
        $dropdownData = array();
        foreach($options as $value => &$label)
            $dropdownData[] = array($value,$label);
        return $dropdownData;
    }

    public static function println ($message) {
        /**/print ($message."\n");
    }

    public static function issetIsArray ($param) {
        return (isset ($param) && is_array ($param));
    }

    public static function captureOutput ($fn) {
        ob_start();
        ob_implicit_flush(false);
        $fn ();
        return ob_get_clean();
    }

    // Determines if the user is using a Mac
    public static function isMac () {
        $user_agent = getenv ("HTTP_USER_AGENT");
        return (strpos ($user_agent, "Mac") !== false);
    }

    // Returns if the current request was made with ajax
    public static function isAjax () {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    }

    public static function getRequestUrl () {
        $protocol = !empty ($_SERVER['HTTPS']) ? 'https' : 'http';
		$baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
		$uri = $_SERVER['REQUEST_URI'];
        return $baseUrl.$uri;
    }
    
    
}
