<?php
/***********************************************************************************
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
 **********************************************************************************/

/**
 * Standalone class with miscellaneous utility functions
 */
class AuxLib {

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
     * @param array $messages An associateive array (<var name> => <var value>)
     * @param string $namespace The name of the JS object which will contain the translations 
     *  dictionary 
     * @param string $scriptName The name of the script which will be registered
     *   and which will be a property of the global JS object x2.
     */
    public static function registerPassVarsToClientScriptScript (
        $namespace, $vars, $scriptName='passVarsToClientScript') {

        $passVarsToClientScript = "
            if (!".$namespace.") ".$namespace." = {};
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
        echo CJSON::encode (array ('failure', Yii::t('app', $message)));
    }

    /**
     * Used by actions to return JSON encoded array containing success status and success message.
     */
    public static function printSuccess ($message) {
        echo CJSON::encode (array ('success', Yii::t('app', $message)));
    }

    /**
     * Used to log debug messages
     */
    public static function debugLog ($message) {
        if (YII_DEBUG) Yii::log ($message, '', 'application.debug');
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

}
