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




// Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_docs".
 *
 * @package application.modules.docs.models
 */
class Docs extends X2Model {

    public $supportsWorkflow = false;

    /**
     * Returns the static model of the specified AR class.
     * @return Docs the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_docs';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'docs',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'FileSystemObjectBehavior' => array(
                'class' => 'application.modules.docs.components.FileSystemObjectBehavior',
                'folderRefName' => 'folderId',
            ),
        ));
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'parent' => array(self::BELONGS_TO, 'DocFolders', 'folderId'),
        );
    }

    public function rules() {

        return array_merge(
            array(
                array('name','menuCheck','on'=>'menu'),
                array('subject', 'length', 'max' => 255),
            ),
            parent::rules()
        );
    }

    public function menuCheck($attr,$params=array()) {
        $this->$attr;
        $this->scenario = 'menu';

        if(sizeof(Modules::model()->findAllByAttributes(array('name'=>$this->name))) > 0)
        {
            $this->addError('name', 'That name is not available.');
        }
      }

    public function parseType() {
        if (!isset($this->type))
            $this->type = '';
        switch ($this->type) {
            case 'email':
            case 'quote':
                return Yii::t('docs', 'Template');
            default:
                return Yii::t('docs', 'Document');
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($pageSize=null) {
        $criteria = new CDbCriteria;
        return $this->searchBase ($criteria, $pageSize, false);
    }

    /**
     * Replace tokens with model attribute values.
     *
     * @param type $str Input text
     * @param X2Model $model Model to use for replacement
     * @param array $vars List of extra variables to replace
     * @param bool $encode Encode replacement values if true; use renderAttribute otherwise.
     * @return string
     */
    public static function replaceVariables(
        $str,$model,$vars = array(),$encode = false,$renderFlag=true) {

        if($encode) {
            foreach(array_keys($vars) as $key)
                $vars[$key] = CHtml::encode($vars[$key]);
        }
        $str = strtr($str,$vars);    // replace any manually set variables
        if ($model instanceof X2Model) {
            if (get_class($model) === 'Quote') {
                $quoteTitle = Modules::displayName(false, "Quotes");
                $quoteParams = array(
                    '{lineItems}' => $model->productTable(true),
                    '{dateNow}' => date("F d, Y", time()),
                    '{quoteOrInvoice}' => Yii::t('quotes',
                            $model->type == 'invoice' ? 'Invoice' : $quoteTitle),
                );
                $str = strtr($str, $quoteParams);
            }
            $str = Formatter::replaceVariables($str, $model, '', $renderFlag,
                            false);
        }
        return $str;
    }

    /**
     * Returns a list of email available email templates.
     *
     * Email and quote are the only two types of supported templates;
     * no design has yet been done to completely generalize templating to
     * accomodate generic models. Part of the challenge will lie in how,
     * for multiple associated contacts (i.e. an account) any reference
     * to a contact is ambiguous unless it is distinguished (i.e.
     * primary contact, secondary contact, etc.)
     *
     * Current solution to this problem: Templates only contain insertable attributes for one type
     *
     * @param type $type
     * @param string associationType Type associated with template (used for attribute replacement).
     *  If the empty string is passed, templates of all association types will be retrieved. 
     * @return type
     */
    public static function getEmailTemplates($type = 'email', $associationType=''){
        $templateLinks = array();
        if(in_array($type, array('email', 'quote'))){
            // $criteria = new CDbCriteria(array('order'=>'lastUpdated DESC'));
            $condition = 'TRUE';
            $params = array ();
            if(!Yii::app()->params->isAdmin){
                $params[':username'] = Yii::app()->user->getName();
                $condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy=:username ';

                /* x2temp */
                $uid = Yii::app()->getSuID();
                if(empty($uid)){
                    if(Yii::app()->params->noSession)
                        $uid = 1;
                    else
                        $uid = Yii::app()->user->id;
                }
                $groupLinks = Yii::app()->db->createCommand()
                    ->select('groupId')
                    ->from('x2_group_to_user')
                    ->where('userId='.$uid)
                    ->queryColumn();

                if(!empty($groupLinks))
                    $condition .= ' OR createdBy IN ('.implode(',', $groupLinks).')';

                $condition .= 
                    'OR (visibility=2 AND createdBy IN
                        (SELECT username FROM x2_group_to_user WHERE groupId IN
                            (SELECT groupId FROM x2_group_to_user WHERE userId='.$uid.')))';

                // $criteria->addCondition($condition);
            }

            // for email templates, retrieve only templates with given association type.
            // if associationType is empty, get templates of all association types
            if ($type === 'email' && $associationType !== '') {
                $condition .= ' AND (associationtype=:associationType)';
                $params[':associationType'] = $associationType;
            }
            // $templates = 
                //X2Model::model('Docs')->findAllByAttributes(array('type'=>'email'),$criteria);
            $params[':type'] = $type;

            $templateData = Yii::app()->db->createCommand()
                    ->select('id,name')
                    ->from('x2_docs')
                    ->where('type=:type AND ('.$condition.')', $params)
                    ->order('name ASC')
                    // ->andWhere($condition)
                    ->queryAll(false);
            foreach($templateData as &$row)
                $templateLinks[$row[0]] = $row[1];
        }
        return $templateLinks;
    }

    public static function getEmailTemplates2() {
        $doc = new Docs;
        $criteria = $doc->getAccessCriteria();
        return self::model()->findAllByAttributes(array(
            'type' => 'email',
        ), $criteria);
    }

    /**
     * @return array names of models which support email templates 
     */
    public static function modelsWhichSupportEmailTemplates () {
        // get all x2model types not in blacklist
        return array_diff_key (X2Model::getModelNames (), array_flip (array (
            'Actions', 'Quote', 'Product', 'Opportunities','Campaign',
        )));
    }

}
