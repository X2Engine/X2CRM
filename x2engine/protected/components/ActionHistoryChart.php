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

/*
Used primarily as a ui container for the X2Chart widget. Also retrieves association id
and association type of model and the date of the first associated action.
 */
class ActionHistoryChart extends X2Widget {
	public $model;
	public $modelName;

	public function init() {
		parent::init();
	}

	public function run() {
		if (isset ($this->model)) {
			$associationId = $this->model->id;
			$associationType = Yii::app()->controller->module->id;
		} else {
			return;
		}

		// get date of first action
		$command = Yii::app()->db->createCommand()
				->select('min(createDate)')
				->from('x2_actions');
		$command->where(
				'associationId=:associationId AND associationType=:associationType AND (visibility="1" OR assignedTo="' . 
				Yii::app()->user->getName() . '")', 
				array(
					'associationId' => $associationId, 
					'associationType' => $associationType
				));
		$actionsStartDate = $command->queryScalar();

		$this->render ('_actionHistoryChart', array (
			'associationId' => $associationId,
			'associationType' => $associationType,
			'actionsStartDate' => $actionsStartDate
		));
	}
}

