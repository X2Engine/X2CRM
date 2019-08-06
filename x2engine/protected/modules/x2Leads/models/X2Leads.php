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




Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_leads".
 *
 * @package application.modules.x2Leads.models
 */
class X2Leads extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return X2Leads the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_x2leads'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'x2Leads'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
			'ModelConversionBehavior' => array(
				'class' => 'application.components.behaviors.ModelConversionBehavior',
                'deleteConvertedRecord' => false,
                'convertedField' => 'converted',
                'conversionDateField' => 'conversionDate',
                'convertedToTypeField' => 'convertedToType',
                'convertedToIdField' => 'convertedToId',
			),
            'ContactsNameBehavior' => array(
                'class' => 'application.components.behaviors.ContactsNameBehavior',
            ),
            'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
		));
	}

	public static function getNames() {
		$arr = X2Leads::model()->findAll();
		$names = array(0=>'None');
		foreach($arr as $x2Leads)
			$names[$x2Leads->id] = $x2Leads->name;

		return $names;
	}

	public static function getX2LeadsLinks($accountId) {
		$allX2Leads = 
            X2Model::model('X2Leads')->findAllByAttributes(array('accountName'=>$accountId));

		$links = array();
		foreach($allX2Leads as $model) {
			$links[] = CHtml::link($model->name,array('/x2Leads/x2Leads/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

    /**
     * Gets a DataProvider for all the contacts in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('X2Leads', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

        
    public function beforeSave () {
        // backwards compatibility check for when leads didn't have first and last name fields
        if (!$this->isNewRecord && 
            !$this->firstName && 
            !$this->lastName && 
            ($this->attributeChanged ('firstName') ||
             $this->attributeChanged ('lastName'))) {

            $this->name = '';
        }
        return parent::beforeSave ();
    }

     public function search($resultsPerPage=null, $uniqueId=null) {
         $criteria=new CDbCriteria;
        if ($resultsPerPage === null) {
            if (!Yii::app()->user->isGuest) {
                $resultsPerPage = Profile::getResultsPerPage();
            } else {
                $resultsPerPage = 20;
            }
        }
 
         // allows converted leads to be filtered out of grid by default
         $filters = $this->asa ('ERememberFiltersBehavior')->getSetting ('filters');
         if (!isset ($filters['converted'])) {
             $this->converted = 'false';
         } elseif ($filters['converted'] === 'all') {
             unset ($this->converted);
         }   
         return $this->searchBase($criteria, $resultsPerPage);
     }   

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}

    public function getConvertedTo () {
        if ($this->converted) {
            $type = $this->convertedToType;
            $id = $this->convertedToId;
            return X2Model::model ($type)->findByPk ($id);
        }
    }

    public function renderConvertedNotice () {
        $convertedTo = $this->getConvertedTo ();
        if ($convertedTo) {
            Yii::app()->user->setFlash (
                'notice', 
                Yii::t('x2Leads', 'This record has been converted. '.
                    'To view the new record, click {here}.', array (
                        '{here}' => CHtml::link (
                            Yii::t('x2Leads', 'here'), $convertedTo->getUrl ()
                    ))));
            X2Flashes::renderTopFlashes ('notice');
        }
    }

}
