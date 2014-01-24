<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

/**
 * Standalone class with miscellaneous utility functions
 */
class AuxLib {


    /**
     * @param int $errCode php file upload error code 
     */
    public static function getFileUploadErrorMessage ($errCode) {
        $errMsg = 'Failed to upload file.';
        switch ($errCode) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $errMsg = Yii::t('app', 'File exceeds the maximum upload size.');
                break;
            case UPLOAD_ERR_PARTIAL:
                break;
            case UPLOAD_ERR_NO_FILE:
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                break;
            case UPLOAD_ERR_CANT_WRITE:
                break;
            case UPLOAD_ERR_EXTENSION:
                break;
        }
        return $errMsg;
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
            (function () {
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
        Yii::log ($message, '', 'application.debug');
    }

    public static function debugLogR ($arr) {
        if (!YII_DEBUG) return;
        $logMessage = print_r ($arr, true);
        Yii::log ($logMessage, '', 'application.debug');
    }

    public static function isIE8 () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie 8/', $userAgentStr);
    }

    /**
     * @return bool returns true if user is using mobile app, false otherwise 
     */
    public static function isMobile () {
        return (Yii::app()->request->cookies->contains('x2mobilebrowser') && 
                Yii::app()->request->cookies['x2mobilebrowser']->value);
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


}
