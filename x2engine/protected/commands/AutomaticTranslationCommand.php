<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 *
 * @package application.commands
 */
class AutomaticTranslationCommand extends CConsoleCommand {

    private $_verbose = false;
    private $_missing;
    private $_consolidate;
    private $_update;
    private $_add;
    private $_newLanguages;

    public function run($args) {
        list($action, $options, $args) = $this->resolveRequest($args);
        $this->parseOptions($options);
        $this->attachBehaviors(array(
            'X2TranslationBehavior' => array('class' => 'X2TranslationBehavior'),
        ));
        if ($this->_missing) {
            $this->addMissingTranslations();
            $this->_verbose && $this->outputMissingStats();
        }
        if ($this->_consolidate) {
            $this->consolidateMessages();
            $this->_verbose && $this->outputConsolidationStats();
        }
        if ($this->_update) {
            $this->updateTranslations();
            $this->_verbose && $this->outputTranslationStats();
        }
        if ($this->_add) {
            //TODO: Add a new language
        }
        $this->outputErrors();
    }

    private function parseOptions($args) {
        if (isset($args['verbose'])) {
            $this->_verbose = true;
        }

        if (!isset($args['mode'])) {
            $args['mode'] = 'mcu';
        }
        if (strpos($args['mode'], 'm') !== false) {
            $this->_missing = true;
        }
        if (strpos($args['mode'], 'c') !== false) {
            $this->_consolidate = true;
        }
        if (strpos($args['mode'], 'u') !== false) {
            $this->_update = true;
        }

        if (strpos($args['mode'], 'a') !== false) {
            if (!isset($args['add'])) {
                throw new CException('A language code must be provided to add a new language.');
            }
            $this->_add = true;
            if (is_array($args['add'])) {
                $this->_newLanguages = $args['add'];
            } else {
                $this->_newLanguages = explode(',', $args['add']);
            }
        }
    }

    private function outputMissingStats() {
        echo Yii::t('app',
                '{count} new messages were found and added to the translation files.',
                array(
            '{count}' => $this->newMessages,
        )) . "\n\n";
    }

    private function outputConsolidationStats() {
        echo Yii::t('app', '{count} messages were added to the common file.',
                array(
            '{count}' => $this->addedToCommon,
        )) . "\n";
        echo Yii::t('app', '{count} messages were removed from other files.',
                array(
            '{count}' => $this->messagesRemoved,
        )) . "\n\n";
    }

    private function outputTranslationStats() {
        setlocale(LC_MONETARY, 'en_US');
        echo Yii::t('app', '{count} messages needed to be translated.',
                array(
            '{count}' => $this->untranslated,
        )) . "\n";
        echo Yii::t('app',
                '{count} characters were translated, resulting in approximately {cost} in fees to Google.',
                array(
            '{count}' => $this->characterCount,
            '{cost}' => money_format('%n',
                    (($this->characterCount) / 2000000) * 20)
        )) . "\n";
        if($this->limitReached){
            echo Yii::t('app', 'Limit reached. Untranslated messages still remain.') . "\n\n";
        }else{
            echo Yii::t('app', 'Automated translation complete.') . "\n\n";
        }
    }

    private function outputErrors() {
        if (isset($this->errors['missingFiles'])) {
            echo Yii::t('app',
                    'Error - Unable to find the following requested translation files:') . "\n";
            foreach ($this->errors['missingFiles'] as $file) {
                echo $file . "\n";
            }
            echo "\n";
        }
    }

}
