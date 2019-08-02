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
 *
 * @package application.commands
 */
class AutomaticTranslationCommand extends CConsoleCommand {

    private $_find;
    private $_consolidate;
    private $_update;
    private $_merge;
    private $_assimilate;

    public function run($args) {
        $this->attachBehaviors(array(
            'TranslationBehavior' => array('class' => 'TranslationBehavior'),
        ));
        
        list($action, $options, $args) = $this->resolveRequest($args);
        $this->parseOptions($options);
        if($this->_assimilate){
            $this->assimilateLanguageFiles();
        }
        if ($this->_find) {
            $this->addMissingTranslations();
            $this->outputMissingStats();
        }
        if ($this->_consolidate) {
            $this->consolidateMessages();
            $this->outputConsolidationStats();
        }
        if ($this->_update) {
            $this->updateTranslations();
            $this->outputTranslationStats();
        }
        if($this->_merge){
            $this->mergeCustomTranslations();
            $this->outputMergeStats();
        }
        $this->outputErrors();
    }

    private function parseOptions($args) {
        if (isset($args['verbose'])) {
            $this->verbose = true;
        }

        if (!isset($args['mode'])) {
            $args['mode'] = 'fcu';
        }
        if (strpos($args['mode'], 'f') !== false) {
            $this->_find = true;
        }
        if (strpos($args['mode'], 'c') !== false) {
            $this->_consolidate = true;
        }
        if (strpos($args['mode'], 'u') !== false) {
            $this->_update = true;
        }
        if (strpos($args['mode'], 'm') !== false) {
            $this->_merge = true;
        }
        if(strpos($args['mode'], 'a') !== false){
            $this->_assimilate = true;
        }
    }

    private function outputMissingStats() {
        echo $this->newMessages." new messages were found and added to the translation files.\n\n";
    }

    private function outputConsolidationStats() {
        echo $this->addedToCommon." messages were added to the common file.\n";
        echo $this->messagesRemoved." messages were removed from other files.\n\n";
    }

    private function outputTranslationStats() {
        setlocale(LC_MONETARY, 'en_US');
        echo $this->characterCount . ' characters were translated, resulting in approximately ' . money_format('%n',
                (($this->characterCount) / 2000000) * 20) . " in fees to Google.\n";
        if($this->limitReached){
            echo 'Limit reached. Untranslated messages still remain.' . "\n\n";
        }else{
            echo 'Automated translation complete.' . "\n\n";
        }
    }
    
    private function outputMergeStats(){
        echo $this->customMessageCount." custom translations incorporated into base code.\n\n";
    }

    private function outputErrors() {
        if (isset($this->errors['missingFiles'])) {
            echo 'Error - Unable to find the following requested translation files:' . "\n";
            foreach ($this->errors['missingFiles'] as $file) {
                echo $file . "\n";
            }
            echo "\n";
        }
        if(isset($this->errors['missingAttributes'])){
            echo 'Error - Unable to find associated files for the following models:'."\n";
            foreach($this->errors['missingAttributes'] as $modelName){
                echo $modelName . "\n";
            }
            echo "\n";
        }
    }

}
