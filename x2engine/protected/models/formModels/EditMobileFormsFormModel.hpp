<?php
/***********************************************************************************
 * Copyright (C) 2011-2015 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * Company website: http://www.x2engine.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes only
 * for the number of users purchased by you. Your use of this Software for
 * additional users is not covered by this license and requires a separate
 * license purchase for such users. You shall not distribute, license, or
 * sublicense the Software. Title, ownership, and all intellectual property
 * rights in the Software belong exclusively to X2Engine. You agree not to file
 * any patent applications covering, relating to, or depicting this Software
 * or modifications thereto, and you agree to assign any patentable inventions
 * resulting from your use of this Software to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 **********************************************************************************/

class CreatePageFormModel extends X2FormModel {

    public $topLinkUrl;
    public $topLinkText;
    public $recordType;
    public $recordId;
    public $recordName;
    public $openInNewTab = false;
    public $dummyAttribute;
    public $record;

    private $_validTypes;
    public function getValidTypes () {
        if (!isset ($this->_validTypes)) {
            $this->_validTypes = X2Model::getModelTypes (true, function ($elem) {
                return X2Model::model ($elem)->asa ('X2LinkableBehavior');
            });
        }
        return $this->_validTypes;
    }

    public function rules () {
        return array (
            array (
                'topLinkUrl', 'application.components.validators.X2UrlValidator', 
                    'allowEmpty' => true, 'defaultScheme' => 'http',
                    'message' => Yii::t('app', 'Invalid URL') 
            ),
            array (
                'recordId', 'numerical', 'allowEmpty' => true, 'integerOnly' => true,
            ),
            array (
                'recordType', 'validateRecordType',
            ),
            array (
                'openInNewTab', 'boolean',
            ),
            array (
                'topLinkUrl', 'requireOneExclusive', 
                'and' => 'topLinkText', 'xor' => 'recordId,recordType'
            ),
            array (
                'topLinkText,topLinkUrl', 'length', 'max' => 250,
            ),
            array (
                'recordName', 'safe',
            ),
        );
    }

    public function attributeLabels () {
        return array (
            'topLinkUrl' => Yii::t('app', 'Link URL'),
            'topLinkText' => Yii::t('app', 'Link name'),
            'recordName' => Yii::t('app', 'Select a record:'),
            'recordType' => Yii::t('app', 'Record type'),
            'recordName' => Yii::t('app', 'Record name'),
            'openInNewTab' => Yii::t('app', 'Open link in new tab when clicked?'),
        );
    }

    public function getSelection () {
        if (!isset ($this->recordType) && !isset ($this->recordId)) {
            return 'topLinkUrl';
        } else {
            return 'recordName';
        }
    }

    /**
     * Ensure that only (link href and link text) xor (record type and id) is specified
     */
    public function requireOneExclusive ($attr, $params) {
        $xor = explode (',', $params['xor']); 
        $and = explode (',', $params['and']); 
        $that = $this;
        if (!(!empty ($this->$attr) &&
            (array_reduce ($and, function ($carry, $item) use ($that) { 
                return $carry && !empty ($that->$item);
            }, true))) ^ 
            (array_reduce ($xor, function ($carry, $item) use ($that) { 
            return $carry && !empty ($that->$item);
        }, true))) {

            $this->addError ('dummyAttribute', '');
            Yii::app()->user->setFlash (
                'error', Yii::t('app', 'Please specify a URL or select a record.'));
        }
    }

    /**
     * Ensure that specified record exists 
     */
    public function validateRecordType ($attr) {
        if (!isset ($this->$attr) && !isset ($this->recordId)) return;
        $val = $this->$attr;
        if (!in_array ($val, array_keys ($this->getValidTypes ()))) {
            Yii::app()->controller->badRequest ();
        }
        if (!isset ($this->recordId) || 
            !($record = X2Model::model ($val)->findByPk ($this->recordId))) {

            $this->addError ('recordName', Yii::t('app', 'Record could not be found') );
        } else {
            $this->record = $record;
        }
    }

}

?>
