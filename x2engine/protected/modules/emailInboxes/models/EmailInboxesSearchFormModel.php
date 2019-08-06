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




class EmailInboxesSearchFormModel extends CFormModel {

    //public $all;
    public $answered;
    //public $bcc;
    public $before;
    //public $body;
    //public $cc;
    //public $deleted;
    public $flagged;
    public $from;
    //public $keyword;
    //public $new;
    //public $old;
    public $on;
    //public $recent;
    public $seen;
    public $since;
    public $subject;
    public $to;
    public $unanswered;
    //public $undeleted;
    public $unflagged;
    //public $unkeyword;
    public $unseen;
    public $text;

    //public $fullText;

    /**
     * Filters attributes to only those which need to be checked when emails are filtered
     * @return array non-null values indexed by attribute
     */
//    public function getSearchCriteria () {
//        $searchOperators = EmailInboxes::$searchOperators;
//        $searchCriteria = array ();
//        foreach ($this->getAttributes () as $operator => $val) {
//            $operandType = $searchOperators[$operator];
//            // ignore unchecked null type operators, operators not set, and string type operators
//            // set to the empty string
//            if (($operandType !== null || intval ($val) === 1) && $val !== null && 
//                $val !== '') {
//
//                $searchCriteria[$operator] = $val;
//            }
//        }
//        return $searchCriteria;
//    }

    /**
     * Generates search string from attributes
     * @return string to pass to imap_search 
     */
    public function composeSearchString () {
        $searchOperators = EmailInboxes::$searchOperators;
        $searchString = '';
        $first = true;
        foreach ($this->getAttributes () as $operator => $val) {
            $operandType = $searchOperators[$operator];
            if (($operandType !== null || intval ($val) === 1) && $val !== null && 
                $val !== '') {

                if (!$first) {
                    $searchString .= ' ';
                    $first = false;
                }
                if ($operandType === null) {
                    $searchString .= strtoupper ($operator);
                } elseif ($operandType === 'date' || $operandType === 'string') {
                    $val = preg_replace ('/"/', '\"', $val);
                    $searchString .= strtoupper ($operator).' "'.$val.'"';
                } else {
                    throw new CException ('Invalid search operand type');
                }
            }

        }
        return $searchString;
    }

    /**
     * Attribute labels in order of appearance in form 
     */
    public function attributeLabels () {
        return array (
            'from' => Yii::t('emailInboxes', 'From:'),
            'to' => Yii::t('emailInboxes', 'To:'), 
            //'cc' => Yii::t('emailInboxes', 'Cc:'),
            //'bcc' => Yii::t('emailInboxes', 'Bcc:'),
            'subject' => Yii::t('emailInboxes', 'Subject:'), 
            'on' => Yii::t('emailInboxes', 'On:'),
            'before' => Yii::t('emailInboxes', 'Before:'),
            'since' => Yii::t('emailInboxes', 'Since:'),
            'seen' => Yii::t('emailInboxes', 'read'),
            'unseen' => Yii::t('emailInboxes', 'unread'), 
            'answered' => Yii::t('emailInboxes', 'answered'),
            'unanswered' => Yii::t('emailInboxes', 'unanswered'), 
            'flagged' => Yii::t('emailInboxes', 'starred'), 
            'unflagged' => Yii::t('emailInboxes', 'not starred'),
            //'body' => Yii::t('emailInboxes', 'Body:'),
            //'unkeyword' => Yii::t('emailInboxes', 'Keyword:'),
            //'keyword' => Yii::t('emailInboxes', 'Keyword:'),
            //'text' => Yii::t('emailInboxes', 'Text:'), 
            //'all' => Yii::t('emailInboxes', ''),
            //'deleted' => Yii::t('emailInboxes', ''),
            //'new' => Yii::t('emailInboxes', 'new'),
            //'old' => Yii::t('emailInboxes', 'old'),
            //'recent' => Yii::t('emailInboxes', 'recent'),
            //'undeleted' => Yii::t('emailInboxes', ''),
        );
    }

    public function rules () {
        return array ();
    }

}
?>
