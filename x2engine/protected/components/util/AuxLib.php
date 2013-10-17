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
        $namespace, $messages, $translationFile='app', $scriptName='passVarsToClientScript') {

        $passVarsToClientScript = "
            if (!x2.".$namespace.") x2.".$namespace." = {};
            x2.".$namespace.".translations = {};
        ";
        foreach ($messages as $key=>$val) {
            $passVarsToClientScript .= "x2.".$namespace.".translations['".
                $key. "'] = '" . addslashes (Yii::t($translationFile, $val)) . "';\n";
        }
        Yii::app()->clientScript->registerScript(
            'passVarsToClientScript', $passVarsToClientScript,
            CClientScript::POS_HEAD);
    }

    /**
     * Used by actions to return JSON encoded array containing error status and error message.
     * Used for testing purposes only.
     */
    public static function printTestError ($message) {
        if (YII_DEBUG) echo CJSON::encode (array ('failure', Yii::t('app', $message)));
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
    
    public static function isIE8 () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie 8/', $userAgentStr);
    }

}
