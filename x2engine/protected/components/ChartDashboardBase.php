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






abstract class ChartDashboardBase extends X2Widget {

	public $report = false;

	/*
	* Filter chart ids wheter to display or not
	*/
	public function filterReport($chartId) {

		// Check if chart Id is set
		if( !$chartId ) {
			return false;
		}

		$chart = X2Model::model('Charts')->findByPk($chartId);
		
		// Dont display chart if it is broken
		if( !$chart ) {
			$this->deleteChart($chartId);
			return false;
		}

		// Display all charts if report is not set
    	if ( !$this->report ) {
    		return true;
    	}

		// Display chart if report Id matches
		if ( $chart->report->id == $this->report->id ) {
			return true;
		}

		return false;
	}

	/**
	 * Deletes the chart
	 */
	public function deleteChart($chartId) {
		$profile = Yii::app()->params->profile;
		$layout = $profile->dataWidgetLayout;

		foreach ($layout as $widget => $settings) {
			if ($settings['chartId'] == intval($chartId)) {
				unset($layout[$widget]);
			}
		}

		$profile->dataWidgetLayout = $layout;
		$profile->save();

	}

}

?>
