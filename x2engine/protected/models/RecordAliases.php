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




class RecordAliases extends CActiveRecord {

    /**
     * @var array $types
     */
    public static $types = array (
        'email', 'phone', 'skype', 'googlePlus', 'linkedIn', 'twitter', 'facebook', 'other');

    public static function model ($className=__CLASS__) {
        return parent::model ($className);
    }

    public static function getActions () {
        return array (
            'createRecordAlias' => 
                'application.components.recordAliases.RecordAliasesCreateAction',
            'deleteRecordAlias' => 
                'application.components.recordAliases.RecordAliasesDeleteAction',
        );
    }

    public static function getAliases (X2Model $model, $aliasType = null) {
        $params =  array (
            ':type' => get_class ($model),
            ':recordId' => $model->id,
        );
        if ($aliasType) {
            $params[':aliasType'] = $aliasType;
        }
        $aliases = RecordAliases::model ()->findAll (array (
            'condition' => 'recordType=:type AND recordId=:recordId'.
                ($aliasType ? ' AND aliasType=:aliasType' : ''),
            'order' => 'aliasType ASC, alias ASC',
            'params' => $params,
        ));
        return $aliases;
    }

    public function tableName () {
        return 'x2_record_aliases';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules () {
        return array(
            array('recordId, aliasType, alias', 'required'),
            array('aliasType', 'validateAliasType'),
            array('recordId', 'validateRecordId'),
            array('alias', 'validateRecordAlias'),
            array('alias', 'validateAliasUniqueness', 'on' => 'insert'),
            array('label', 'safe'),
        );
    }

    public function getModel () {
        $recordType = $this->recordType;
        return $recordType::model ()->findByPk ($this->recordId);
    }

    public function validateAliasType ($attribute) {
        $value = $this->$attribute;
        if (!in_array ($value, self::$types)) {
            throw new CHttpException (400, Yii::t('app', 'Invalid alias type'));
        }
    }

    public function validateRecordId () {
        if (!$this->getModel ()) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record id or type')) ;
        }
    }

    public function renderAlias ($makeLinks=true) {
        if ($makeLinks) {
            switch ($this->aliasType) {
                case 'email':
                    return X2Html::renderEmailLink ($this->alias);
                case 'phone':
                    return X2Html::renderPhoneLink ($this->alias);
                case 'googlePlus':
                    return CHtml::encode ($this->label ? $this->label : $this->alias);
                default:
                    return CHtml::encode ($this->alias);
            }
        } else {
            switch ($this->aliasType) {
                case 'googlePlus':
                    return CHtml::encode ($this->label ? $this->label : $this->alias);
                default:
                    return CHtml::encode ($this->alias);
            }
        }
    }

    public function validateAliasUniqueness () {
        if (self::findByAttributes (array_diff_key ($this->getAttributes (), array (
            'id' => true)))) {

            $this->addError (
                'alias', 
                Yii::t('app', 'This record already has a {aliasType} alias with the '.
                    'name "{alias}"', array (
                    '{aliasType}' => $this->renderAliasType (false),
                    '{alias}' => $this->renderAlias (false),
                )));
        }
    }

    public function validateRecordAlias ($attribute) {
        $value = $this->$attribute;
        switch ($this->aliasType) {
            case 'email':
                $emailValidator = CValidator::createValidator ('email', $this, 'alias');
                $emailValidator->validate ($this, 'email');
                break;
        }
    }

    public function attributeLabels () {
        return array (
            'aliasType' => Yii::t('app', 'Alias Type'),
            'alias' => Yii::t('app', 'Alias'),
        );
    }

    public function getAllIcons () {
        $icons = array ();
        foreach (self::$types as $type) {
            $icons[$type] = $this->getIcon (false, false, $type);
        }
        return $icons;
    }

    public function getIcon ($includeTitle=false, $large=false, $aliasType=null) {
        if ($aliasType === null) {
            $aliasType = $this->aliasType;
        }
        
        $class = '';
        switch ($aliasType) {
            case 'email':
                $class = 'fa-at';
                break;
            case 'phone':
                $class = 'fa-phone';
                break;
            case 'skype':
                $class = 'fa-skype';
                break;
            case 'googlePlus':
                $class = 'fa-google-plus';
                break;
            case 'linkedIn':
                $class = 'fa-linkedin';
                break;
            case 'twitter':
                $class = 'fa-twitter';
                break;
            case 'facebook':
                $class = 'fa-facebook';
                break;
        }
        if ($includeTitle) $aliasOptions = $this->getAliasTypeOptions ();
        if ($large) $class .= ' fa-lg';
        return 
            '<span '.($includeTitle ? 
                'title="'.CHtml::encode ($aliasOptions[$aliasType]).'" ' : '')
            .'class="fa '.$class.'"></span>';
    }

    public function renderAliasType ($encode=true) {
        $options = $this->getAliasTypeOptions ();
        $html = isset ($options[$this->aliasType]) ? $options[$this->aliasType] : '';
        if ($encode) $html = CHtml::encode ($html);
        return $html;
    }

    private $_aliasTypeOptions;
    public function getAliasTypeOptions () {
        if (!isset ($this->_aliasTypeOptions)) {
            $this->_aliasTypeOptions = array ( 
                'email' => Yii::t('app', 'email'),
                'phone' => Yii::t('app', 'phone'),
                'skype' => 'Skype',
                'googlePlus' => 'Google+',
                'linkedIn' => 'LinkedIn',
                'twitter' => 'Twitter',
                'facebook' => 'Facebook',
                'other' => 'Other',
            );
        }
        return $this->_aliasTypeOptions;
    }

}

?>
