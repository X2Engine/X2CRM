<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * Data provider class
 *
 * A child of CActiveDataProvider made for the purposes of getting pagingation to
 * work properly.
 *
 * @package application.components
 */
class SmartActiveDataProvider extends CActiveDataProvider {

	private $_pagination;

    private $_countCriteria;

    /**
     * Overrides parent __construct ().
     * @param string $uniqueId (optional) If set, will be used to uniquely identify this data
     *  provider. This overrides the default behavior of using the model name as the uid.
	 *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function __construct($modelClass,$config=array(),
        /* x2modstart */$uniqueId=null, $persistentGridSettings=false/* x2modend */) {
        /* x2modstart */  
        $this->attachBehaviors (array (
                'SmartDataProviderBehavior' => array (
                    'class' => 'SmartDataProviderBehavior',
                    'settingsBehavior' => ($persistentGridSettings ? 
                        'GridViewDbSettingsBehavior' : 'GridViewSessionSettingsBehavior'),
                )
        ));
        /* x2modend */ 
    
        if(is_string($modelClass))
        {
            $this->modelClass=$modelClass;
            $this->model=$this->getModel($this->modelClass);
        }
        elseif($modelClass instanceof CActiveRecord)
        {
            $this->modelClass=get_class($modelClass);
            $this->model=$modelClass;
        }

        /* x2modstart */     
        if ($uniqueId !== null) {
            $this->setId($uniqueId);
        } else {
        /* x2modend */ 
            $this->setId(CHtml::modelName($this->model));
        /* x2modstart */ 
        }
        /* x2modend */ 
        foreach($config as $key=>$value)
            $this->$key=$value;

        /* x2modstart */ 
        $this->storeSettings ();
        /* x2modend */ 
    }

	/**
	 * Returns the pagination object.
	 * @return CPagination the pagination object. If this is false, it means the pagination is 
     *  disabled.
	 */
	public function getPagination() {
		if($this->_pagination===null) {
            $this->_pagination = $this->getSmartPagination ();
        } 
        return $this->_pagination;
	}

	/**
     * Overrides parent fetchData ().
	 * Fetches the data from the persistent data storage.
	 *
	 * Modified to always sort by id DESC as well as the chosen sort
	 * @return array list of data items
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	protected function fetchData() {
		$criteria=clone $this->getCriteria();
        /* x2modstart */ 
        $criteria->with = array();
        /* x2modend */ 

		if(($pagination=$this->getPagination())!==false) {
			$pagination->setItemCount($this->getTotalItemCount());
			$pagination->applyLimit($criteria);
		}

		$baseCriteria=$this->model->getDbCriteria(false);

		if(($sort=$this->getSort())!==false) {
			// set model criteria so that CSort can use its table alias setting
			if($baseCriteria!==null) {
				$c=clone $baseCriteria;
				$c->mergeWith($criteria);
				$this->model->setDbCriteria($c);
			} else
				$this->model->setDbCriteria($criteria);
			$sort->applyOrder($criteria);
		}

        /* x2modstart */ 
		$orderBy = $criteria->order;
		if(!preg_match('/\bid\b/',$orderBy)) {
			if(!empty($orderBy))
				$orderBy .= ',';
			$orderBy .= 't.id DESC';
			$criteria->order = $orderBy;
		}
        /* x2modend */ 

		$this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
		$data=$this->model->findAll($criteria);
		$this->model->setDbCriteria($baseCriteria);  // restore original criteria
		return $data;
	}

    /**
     * Generates the item count without eager loading, to improve performance.
     * @return type
     */
    public function calculateTotalItemCount(){
        if($this->model instanceof X2Model) {
            if(!isset($this->_countCriteria)) {
                $this->_countCriteria = clone $this->getCriteria();
                $this->_countCriteria->with = array();
            }
            return X2Model::model($this->modelClass)->count($this->_countCriteria);
        }else{
            return parent::calculateTotalItemCount();
        }
    }

}
