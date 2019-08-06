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




$migrateTemplates = function() {
    $contactsTitle = getModuleTitle('contacts');
    $contactsPattern = '/{' . $contactsTitle . '\.(.*?)}/';
    $contactsReplacement = '{associatedContacts.$1}';
    $accountsTitle = getModuleTitle('accounts');
    $accountsPattern = '/{' . $accountsTitle . '\.(.*?)}/';
    $accountsReplacement = '{accountName.$1}';
    $quotesTitle = getModuleTitle('quotes');
    $quotesPattern = '/{' . $quotesTitle . '\.(.*?)}/';
    $quotesReplacement = '{$1}';

    $quotes = Yii::app()->db->createCommand()
            ->select('id, text')
            ->from('x2_docs')
            ->where('type = "quote"')
            ->queryAll();

    foreach ($quotes as $quote) {
        $text = $quote['text'];
        $text = preg_replace($contactsPattern, $contactsReplacement, $text);
        $text = preg_replace($accountsPattern, $accountsReplacement, $text);
        $text = preg_replace($quotesPattern, $quotesReplacement, $text);
        Yii::app()->db->createCommand()
                ->update('x2_docs', array('text' => $text), 'id = :id',
                        array(':id' => $quote['id']));
    }
};

function getModuleTitle($module, $plural = false) {
    $moduleTitle = Yii::app()->db->createCommand()
            ->select('title')
            ->from('x2_modules')
            ->where("name = :name")
            ->bindValue(':name', $module)
            ->limit(1)
            ->queryScalar();

    if (!$moduleTitle) return false;

    if (Yii::app()->locale->id === 'en') {
        // Handle silly English pluralization
        if ($plural === false) {
            if (preg_match('/ies$/', $moduleTitle)) {
                $moduleTitle = preg_replace('/ies$/', 'y', $moduleTitle);
            } else if (preg_match('/ses$/', $moduleTitle)) {
                $moduleTitle = preg_replace('/es$/', '', $moduleTitle);
            } else if ($moduleTitle !== 'Process') {
                // Otherwise chop the trailing s
                $moduleTitle = trim($moduleTitle, 's');
            }
        } elseif ($plural === 'optional') {
            if (preg_match('/y$/', $moduleTitle)) {
                $moduleTitle = preg_replace('/y$/', '(ies)', $moduleTitle);
            } else if (preg_match('/ss$/', $moduleTitle)) {
                $moduleTitle .= '(es)';
            } else if (in_array($moduleTitle, array('Service'))) {
                $moduleTitle .= '(s)';
            } elseif (preg_match('/s$/', $moduleTitle)) {
                $moduleTitle = preg_replace('/s$/', '(s)', $moduleTitle);
            }
        } else {
            if (preg_match('/y$/', $moduleTitle)) {
                $moduleTitle = preg_replace('/y$/', 'ies', $moduleTitle);
            } else if (preg_match('/ss$/', $moduleTitle)) {
                $moduleTitle .= 'es';
            } else if (in_array($moduleTitle, array('Service'))) {
                $moduleTitle .= 's';
            }
        }
    }
    return $moduleTitle;
}

$migrateTemplates();