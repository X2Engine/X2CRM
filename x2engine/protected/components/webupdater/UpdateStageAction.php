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




Yii::import('application.components.webupdater.*');

/**
 * Generic action class for performing a stage of an update/upgrade via AJAX.
 *
 * @package application.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdateStageAction extends WebUpdaterAction {

    public function behaviors() {
        return array(
			'UpdaterBehavior' => array(
				'class' => 'application.components.UpdaterBehavior',
                'errorCode' => 200, // Simple UI-based error reporting
                'handleErrors' => true,
                'handleExceptions' => true
			)
		);
    }

	public function run($stage,$scenario,$version,$uniqueId,$autoRestore=0){
        $this->scenario = $scenario;
		if(Yii::app()->request->isAjaxRequest){
            switch($stage) {
                case 'download':
                    // Download the update/upgrade package
                    $this->downloadPackage($version,$uniqueId,$this->edition);
                    $this->respond(Yii::t('admin','Successfully downloaded package.'));
                    break;
                case 'enact':
                    // The final and most critical part of the update, where
                    // files are copied and database commands are run.
                    $autoRestore = ($autoRestore === 'true') ? true : false;
                    $this->uniqueId = $uniqueId;
                    $this->enactChanges($autoRestore);
                    $this->finalizeUpdate($scenario, $uniqueId, $this->version, $this->edition);
                    $this->respond(Yii::t('admin', 'All done.'));
                    break;
                case 'check':
                    // Retrive the manifest of changes pre-emptively, or load
                    // the existing manifest, for review. Check the installation
                    // and server environment for compatibility.
                    if(!$this->checkIf('manifestAvail',false)) {
                        // No package has yet been downloaded; if the package
                        // were present, but the manifest not ready for use,
                        // UpdaterBehavior would have thrown an exception by now
                        // (the 'verify' stage, which performs the necessary
                        // checks, should have been run first).
                        $data = $this->getUpdateData($version,$uniqueId,$this->edition);
                        if(array_key_exists('errors',$data)) {
                            // The update server doesn't like us.
                            $this->respond($data['errors'],1);
                            break;
                        }
                        $this->manifest = $data;
                    }

                    $cStatus = $this->getCompatibilityStatus();
                    $this->response['allClear'] = $cStatus['allClear'];
                    $this->response['manifest'] = $this->manifest;
                    $this->response['compatibilityStatus'] = $cStatus;
                    $this->respond($this->renderCompatibilityMessages('strong',array('style'=>'text-decoration:underline;')));
                    break;
                case 'unpack':
                    // Unpack the zip file
                    $this->unpack();
                    $this->respond(Yii::t('admin','Successfully extracted package.'));
                    break;
                case 'verify':
                    try{
                        // Check package contents.
                        $this->checkIf('packageApplies');
                        // The JSON returned by this action should include all the
                        // necessary data to render warning messages concerning files.
                        $this->response['statusCodes'] = array(
                            'present' => UpdaterBehavior::FILE_PRESENT,
                            'corrupt' => UpdaterBehavior::FILE_CORRUPT,
                            'missing' => UpdaterBehavior::FILE_MISSING
                        );
                        $this->response['filesStatus'] = $this->filesStatus;
                        $this->response['filesByStatus'] = $this->filesByStatus;
                        $this->respond(Yii::t('admin', 'Files checked.'), !((bool) $this->files) || $this->filesStatus[UpdaterBehavior::FILE_CORRUPT] > 0 || $this->filesStatus[UpdaterBehavior::FILE_MISSING] > 0);
                    }catch(Exception $e){
                        $this->respond($e->getMessage(), true);
                    }
                    break;
            }
		}else{
			$this->respond('Update requests must be made via AJAX.',true);
		}
	}
}

?>
