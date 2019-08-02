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




/**
 * Made for the purposes of getting pagingation to work properly.
 *
 * @package application.components
 */
class SmartDataProviderBehavior extends CBehavior {

    public $settingsBehavior;

	private $_pagination;

    public function attach ($owner) {
        parent::attach ($owner);
        $this->attachBehaviors (array (
            'settingsBehavior' => array (
                'class' => $this->settingsBehavior,
                'uid' => $this->owner->uid,
                'modelClass' => $this->owner->modelClass,
            )
        ));
    }

    /**
     * Unsets sort order if sort is on an attribute not in list of specified attributes
     * @param array $attrs names of model attributes
     * @return bool true if sort order was unset, false otherwise
     */
//    public function unsetSortOrderIfNotIn (array $attrs) {
//        $sortOrder = $this->getSetting ('sort'); 
//        $sortOrderUnset = false;
//        if (!empty ($sortOrder) && !in_array (preg_replace ('/\.desc$/', '', $sortOrder), $attrs)) {
//            $sortOrder = '';
//            unset ($_GET[$this->owner->modelClass][$this->getSortKey ()]);
//            $sortOrderUnset = true;
//        }
//        $this->saveSetting ('sort', $sortOrder); 
//        return $sortOrderUnset;
//
//    }

    public function getSortKey () {
        return $this->owner->getId()!='' ? $this->owner->getId().'_sort' : 'sort';
    }
    
    public function getPageKey () {
        return $this->owner->getId()!='' ? $this->owner->getId().'_page' : 'page';
    }

    public function getSessionPageKey () {
        return $this->getStatePrefix ().$this->getPageKey ();
    }

    public function storeSettings () {

		//Sort and page saving code modified from:
		//http://www.stupidannoyingproblems.com/2012/04/yii-grid-view-remembering-filters-pagination-and-sort-settings/

        // sort order gets saved in db or session depending on settingsBehavior
		$key = $this->getSortKey ();

		if(!empty($_GET[$key])){
            if (!$this->owner->disablePersistentGridSettings)
			    $val = $this->saveSetting ('sort', $_GET[$key]);
		} else {
            if (!$this->owner->disablePersistentGridSettings)
			    $val = $this->getSetting ('sort');
			if(!empty($val))
				$_GET[$key] = $val;
		}

        // active page always gets stored in session
		$key = $this->getPageKey ();
        $statePrefix = $this->getStatePrefix ();
		if(!empty($_GET[$key])){
			Yii::app()->user->setState($this->getSessionPageKey (), $_GET[$key]);
		} elseif(!empty($_GET["ajax"])){
			// page 1 passes no page number, just an ajax flag
			Yii::app()->user->setState($this->getSessionPageKey (), 1);
		} else {
			$val = Yii::app()->user->getState($this->getSessionPageKey ());
			if(!empty($val))
				$_GET[$key] = $val;
		}

	}

	/**
	 * Returns the pagination object.
	 * @return CPagination the pagination object. If this is false, it means the pagination is 
     *  disabled.
	 */
	public function getSmartPagination() {
		if($this->_pagination===null) {
			$this->_pagination=new RememberPagination;
			if(($id=$this->owner->getId())!='')
				$this->_pagination->pageVar=$id.'_page';
		}
		return $this->_pagination;
	}

}
