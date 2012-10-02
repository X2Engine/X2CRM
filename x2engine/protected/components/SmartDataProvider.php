<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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
		} else if (!empty($_GET["ajax"])){
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
		if($this->_pagination===null)
		{
			//$this->_pagination=new CPagination;
			$this->_pagination=new RememberPagination;
			if(($id=$this->getId())!='')
				$this->_pagination->pageVar=$id.'_page';
		}
		return $this->_pagination;
	}
}