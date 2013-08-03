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
 * The action for running the upgrader.
 * 
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2CRMUpgradeAction extends WebUpdaterAction  {

	public function run(){
		if(isset($_GET['n_'])) {
			echo Yii::app()->db->createCommand('SELECT COUNT(*) FROM x2_users')->queryScalar();
			Yii::app()->end();
		}
		// Remove database backup; if it exists, the user most likely came here
		// immediately after updating to the latest version, in which case the
		// backup is outdated (applies to the old version)
		$this->removeDatabaseBackup();
		$thisVersion = Yii::app()->params->version;
		$currentVersion = FileUtil::getContents($this->updateServer.'/installs/updates/versionCheck');
		if(version_compare($thisVersion, $currentVersion) < 0){
			$this->controller->render('updater', array(
				'scenario' => 'error',
				'message' => 'Update required',
				'longMessage' => "Before upgrading, you must update to the latest version ($currentVersion). ".CHtml::link(Yii::t('app', 'Update'), 'updater', array('class' => 'x2-button'))
			));
		}else{
			$configVars = $this->configVars;
			extract($configVars);

			$context = stream_context_create(array(
				'http' => array(
					'timeout' => 15  // Timeout in seconds
					)));

			// Check to see if the updater has changed:
			$updaterCheck = FileUtil::getContents($this->updateServer.'/installs/updates/updateCheck', 0, $context);

			if($updaterCheck != $updaterVersion){
				$this->runUpdateUpdater($updaterCheck, 'upgrade');
			}
			$this->controller->render('updater', array(
				'scenario' => 'upgrade',
				'version' => $thisVersion,
				'unique_id' => '',
				'url' => 'x2planet',
				'newVersion' => $currentVersion,
				'updaterCheck' => $updaterCheck,
				'updaterVersion' => $updaterVersion,
				'edition' => Yii::app()->params->admin->edition
			));
		}
	}

}

?>
