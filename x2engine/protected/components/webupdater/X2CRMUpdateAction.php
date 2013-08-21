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

Yii::import('application.components.webupdater.*');

/**
 * Description of X2CRMUpdateAction
 *
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2CRMUpdateAction extends WebUpdaterAction {

	/**
	 * Runs the updater. It is in this action where the entire file is copied
	 * from the remote update server.
	 */
	public function run(){
		// Get configuration variables:
		$configVars = $this->configVars;
		extract($configVars);

		// Check to see if there's an update available. By this point in time
		// (if this AdminController is running inside of a much older installation)
		// FileUtil should have been downloaded and available.
		$versionTest = $this->checkUpdates(true);
		$updaterCheck = $this->getLatestUpdaterVersion();
		
		if($updaterCheck && $versionTest){
			if($updaterCheck != $updaterVersion){
				$this->runUpdateUpdater($updaterCheck, 'updater');
			}

			if(version_compare($version, $versionTest) < 0){
				$updateData = $this->getUpdateData();

				if($updateData){
					// Render the updater with the data
					$updateData['newVersion'] = $versionTest;
					$updateData['updaterCheck'] = $updaterCheck;
					
					if(!isset($updateData['errors'])){
						$updateData['scenario'] = 'update';
						$unique_id = $this->uniqueId;
						foreach(array('updaterCheck', 'updaterVersion', 'version', 'unique_id') as $var)
							$updateData[$var] = ${$var};
						$updateData['edition'] = 'opensource';
						$updateData['edition'] =  $this->edition;
						$updateData['url'] = 'x2planet';
						// Ready to run the updater.
						$this->controller->render('updater', $updateData);
					}else{ // Scenario $updateData['errors'] is set; server denied/dropped requests
						// Redirect, with the appropriate error message
						$this->controller->render('updater', array(
							'scenario' => 'error',
							'message' => Yii::t('admin', "Could not retrieve update data."),
							'longMessage' => Yii::t('admin', $updateData['errors'])
						));
					}
				}else{ // Scenario $updateData === False; request failed
					// Redirect, with the appropriate error message
					$this->controller->render('updater', array(
						'scenario' => 'error',
						'message' => Yii::t('admin', "Could not retrieve update data."),
						'longMessage' => Yii::t('admin', 'Error connecting to the updates server.')
					));
				}
			}else{ // scenario $version == $versionTest; already up-to-date.
				Yii::app()->session['versionCheck'] = true;
				$this->controller->render('updater', array('scenario' => 'message', 'version' => $version, 'message' => Yii::t('admin', 'X2CRM is at the latest version!')));
			}
		}else{ // $updaterCheck === False || $versionTest === False; couldn't connect to server
			// Is it the fault of the user's server?
			$this->controller->checkRemoteMethods();
			// Redirect to updater with the appropriate error message
			$this->controller->render('updater', array('scenario' => 'error', 'message' => Yii::t('admin', 'Could not connect to the updates server, or an error occurred on the updates server.')));
		}
	}

}

?>
