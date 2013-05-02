<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * wok properly.
 * 
 * @package X2CRM.components 
 */
class SmartDataProvider extends CActiveDataProvider {
	public function __construct($modelClass,$config=array()) {
		parent::__construct($modelClass, $config);

		//Sort and page saving code modified from:
		//http://www.stupidannoyingproblems.com/2012/04/yii-grid-view-remembering-filters-pagination-and-sort-settings/

		//a string unique to each controller/action (and optionally id) combination
		$statePrefix = Yii::app()->controller->uniqueid .'/'. Yii::app()->controller->action->id . (isset($_GET['id']) ? '/'.$_GET['id'] : '');

		// store also sorting order
		$key = $this->getId()!='' ? $this->getId().'_sort' : 'sort';
		if(!empty($_GET[$key])){
			Yii::app()->user->setState($statePrefix . $key, $_GET[$key]);
		} else {
			$val = Yii::app()->user->getState($statePrefix . $key);
			if(!empty($val))
				$_GET[$key] = $val;
		}

		// store active page in page
		$key = $this->getId()!='' ? $this->getId().'_page' : 'page';
		if(!empty($_GET[$key])){
			Yii::app()->user->setState($statePrefix . $key, $_GET[$key]);
		} elseif(!empty($_GET["ajax"])){
			// page 1 passes no page number, just an ajax flag
			Yii::app()->user->setState($statePrefix . $key, 1);
		} else {
			$val = Yii::app()->user->getState($statePrefix . $key);
			if(!empty($val))
				$_GET[$key] = $val;
		}
	}

	private $_pagination;
	
	/**
	 * Returns the pagination object.
	 * @return CPagination the pagination object. If this is false, it means the pagination is disabled.
	 */
	public function getPagination() {
		if($this->_pagination===null) {
			//$this->_pagination=new CPagination;
			$this->_pagination=new RememberPagination;
			if(($id=$this->getId())!='')
				$this->_pagination->pageVar=$id.'_page';
		}
		return $this->_pagination;
	}
	
	/**
	 * Fetches the data from the persistent data storage.
	 * 
	 * Modified to always sort by id DESC as well as the chosen sort
	 * @return array list of data items
	 */
	protected function fetchData() {
		$criteria=clone $this->getCriteria();

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
		
		$orderBy = $criteria->order;
		if(!preg_match('/\bid\b/',$orderBy)) {
			if(!empty($orderBy))
				$orderBy .= ',';
			$orderBy .= 't.id DESC';
			$criteria->order = $orderBy;
		}

		$this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
		$data=$this->model->findAll($criteria);
		$this->model->setDbCriteria($baseCriteria);  // restore original criteria
		return $data;
	}
}