<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
