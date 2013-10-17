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
 * Generic action class for performing a stage of an update/upgrade via AJAX.
 *
 * @package X2CRM.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdateStageAction extends WebUpdaterAction {

	public function run($stage,$scenario,$version,$uniqueId,$autoRestore=0){
        set_exception_handler('ResponseBehavior::respondWithException');
        set_error_handler('UpdaterBehavior::respondWithError');
        $this->scenario = $scenario;
		if(Yii::app()->request->isAjaxRequest){
            switch($stage) {
                case 'download':
                    // Download the update/upgrade package
                    $this->downloadPackage($version,$uniqueId,$this->edition);
                    self::respond(Yii::t('admin','Successfully downloaded package.'));
                    break;
                case 'enact':
                    // The final and most critical part of the update, where
                    // files are copied and database commands are run.
                    $autoRestore = (bool) $autoRestore;
                    $this->uniqueId = $uniqueId;
                    $this->enactChanges($autoRestore);
                    self::respond(Yii::t('admin', 'All done.'));
                    break;
                case 'check':
                    // Retrive the manifest of changes pre-emptively, or load
                    // the existing manifest, for review. Check the installation
                    // and server environment for compatibility.
                    if(!file_exists($this->updateDir.DIRECTORY_SEPARATOR.'manifest.json')) {
                        $this->manifest = $this->getUpdateData($version,$uniqueId,$this->edition);
                    }
                    $cStatus = $this->getCompatibilityStatus();
                    $this->addResponseProperty('allClear',$cStatus['allClear']);
                    $this->addResponseProperty('manifest',$this->manifest);
                    $this->addResponseProperty('compatibilityStatus',$cStatus);
                    self::respond($this->renderCompatibilityMessages('strong',array('style'=>'text-decoration:underline;')));
                    break;
                case 'unpack':
                    // Unpack the zip file
                    $this->unpack();
                    self::respond(Yii::t('admin','Successfully extracted package.'));
                    break;
                case 'verify':
                    try{
                        // Check package contents.
                        $this->checkIf('packageApplies'); // Will throw an exception and thus print the appropriate message if it doesn't apply
                        // The JSON returned by this action should include all the
                        // necessary data to render warning messages concerning files.
                        $this->addResponseProperty('statusCodes', array(
                            'present' => UpdaterBehavior::FILE_PRESENT,
                            'corrupt' => UpdaterBehavior::FILE_CORRUPT,
                            'missing' => UpdaterBehavior::FILE_MISSING
                        ));
                        $this->addResponseProperty('filesStatus', $this->filesStatus);
                        $this->addResponseProperty('filesByStatus', $this->filesByStatus);
                        self::respond(Yii::t('admin', 'Files checked.'), !((bool) $this->files) || $this->filesStatus[UpdaterBehavior::FILE_CORRUPT] > 0 || $this->filesStatus[UpdaterBehavior::FILE_MISSING] > 0);
                    }catch(Exception $e){
                        self::respond($e->getMessage(), true);
                    }
                    break;
            }
		}else{
			self::respond('Update requests must be made via AJAX.',true);
		}
	}
}

?>
