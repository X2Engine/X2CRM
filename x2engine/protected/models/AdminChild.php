<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

class AdminChild extends Admin {
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('accounts, sales, timeout, chatPollTime', 'numerical', 'integerOnly'=>true),
			array('chatPollTime', 'numerical', 'max'=>10000, 'min'=>100),
			array('webLeadEmail', 'length', 'max'=>200),
			array('currency', 'length', 'max'=>3),
			array('menuOrder, menuNicknames', 'length', 'max'=>255),
			array('menuVisibility', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
		);
	}

	public static function getMenuItems($returnSelected = false) {
		// get admin model
		$admin=Admin::model()->findByPk(1);

		$nicknames = explode(":",$admin->menuNicknames);
		$menuOrder = explode(":",$admin->menuOrder);
		$menuVis = explode(":",$admin->menuVisibility);
		
		$menuItems = array();		// assoc. array with correct order, containing realName => nickName
		$selectedItems = array();
		
		for($i=0;$i<count($menuOrder);$i++) {				// load items from menuOrder into $menuItems keys
			$menuItems[$menuOrder[$i]] = Yii::t('app',$nicknames[$i]);	// set values to their (translated) nicknames
			
			if($menuVis[$i] == 1 && $returnSelected)
				$selectedItems[] = $menuOrder[$i];			// but only include them if they are visible (or we need the full list)
		}
		return $returnSelected? array($menuItems,$selectedItems) : $menuItems;
	}
	
}
