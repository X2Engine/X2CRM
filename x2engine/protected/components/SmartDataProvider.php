<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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