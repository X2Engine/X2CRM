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



class MobileActionHistoryAttachmentsPublishAction extends MobileAction {

    /**
     * @param int $id id of record to which to attach published action
     */
    public function run ($id, $type) {
        if (!Yii::app()->params->isAdmin && !Yii::app()->user->checkAccess ('ActionsCreate')) {
            $this->controller->denied ();
        }

        $model = $this->getModel ($id);

        if (!$this->controller->checkPermissions ($model, 'view')) {
            $this->controller->denied ();
        }
        
        $profile = Yii::app()->params->profile;
        $settings = Yii::app()->settings;
        $creds = Credentials::model()->findByPk($settings->googleCredentialsId);
        $key = null;
        $decodedResult = null;
        
        $action = new Actions;
        $action->setAttributes (array (
            'associationType' => X2Model::getAssociationType (get_class ($model)), 
            'associationId' => $model->id,
            'associationName' => $model->name,
            'dueDate' => time (),
            'completeDate' => time (),
            'complete' => 'Yes',
            'completedBy' => Yii::app()->user->getName (),
            'private' => 0,
        ), false);
        $valid = false;
        $geoLocationCoords = isset ($_POST['geoLocationCoords']) ? $_POST['geoLocationCoords'] : "";
        $attachmentType = '';
        if ($geoLocationCoords == 'set' && isset ($_POST['geoCoords'])) {
            if ($creds && $creds->auth && !empty($creds->auth->apiKey)) {
                $key = $creds->auth->apiKey;
            } else {
                throw new CHttpException (403, Yii::t('app', 'Google API key missing'));
            }
            $decodedResponse = json_decode(filter_input(INPUT_POST, 'geoCoords', FILTER_DEFAULT),true);
            $location = Yii::app()->params->profile->user->logLocation('mobileActionPost', 'POST');
            $action->location = $location;
            if(!empty($decodedResponse)) {
                /* 
                 * get static map here
                 */
                $url = 'https://maps.googleapis.com/maps/api/staticmap?center=' . 
                        $decodedResponse['lat'] . ',' . $decodedResponse['lon'] .
                        '&zoom=13&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:%7C' .
                        $decodedResponse['lat'] . ',' . $decodedResponse['lon'] .
                        '&key=' . $key;
                    
                //open connection
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_URL, $url);

                //execute post
                $result = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if($http_code === 200){
                    //close connection
                    $decodedResult = $result;
                    
                } else {
                    throw new CHttpException (500, Yii::t('app', 'Failed to fetch location photo'));
                }
                curl_close($ch);
            } else {
                throw new CHttpException (500, Yii::t('app', 'Failed to decode JSON'));
            }      
            $attachmentType = 'location';
            $valid = true;
        } else if ($type ==='attachments' && isset ($_FILES['Actions'])) {
            $attachmentType = 'file';
            $valid = true;
            $action->upload = CUploadedFile::getInstance ($action, 'upload'); 
            
        } 
        $action->type = 'attachment';
        if(!strcmp($attachmentType,'location')) {
            if ($valid && $action->saveRaw ($profile,$decodedResult)) {
                $this->controller->renderPartial (
                    'application.modules.mobile.views.mobile._actionHistoryAttachments', array (
                    'model' => $model,
                    'refresh' => true,
                    'type'=>$type,
                ), false, true);

                Yii::app()->end ();
            } else {
                throw new CHttpException (500, Yii::t('app', 'Publish failed'));
            }
        } else if (!strcmp($attachmentType,'file')){
            if ($valid && $action->save ()) {

                $media = $action->media;
                if ($media) $media = array_pop ($media);
                $key = Yii::app()->settings->getGoogleApiKey('speech');

                // Check if the attachment is an audio file and if avconv is installed
                if ($media->isAudio() &&  !AuxLib::isAndroid() && AuxLib::command_exist("avconv")) {
                    //check if google service account key file is present
                    if(isset($key) 
                        && !empty($key)) {

                        $tempFilename = hash('sha256', uniqid(rand(), true));
                        $userFolderPath = implode(DIRECTORY_SEPARATOR, array(
                            Yii::app()->basePath,
                            '..',
                            'uploads',
                            'protected',
                            'media',
                            Yii::app()->params->profile->username
                        ));
                        //get audio file and convert it to flac if avconv is present
                        $pathToAudioFile = $media->getPath();
                        $pathToTempFlac = $userFolderPath.DIRECTORY_SEPARATOR.$tempFilename.'.flac';
                    
                        //execute command to convert audio file to flac                       
                        $returnVal = shell_exec(sprintf("avconv -i %s -ar 16000 -c:a flac %s",escapeshellarg($pathToAudioFile),escapeshellarg($pathToTempFlac)));

                        //incremental sleep to wait for the creation of the flac file 
                        for ($i=0; $i <= 10; $i++) {
                            if(file_exists($pathToTempFlac)) {
                                break;
                            }
                            sleep(3); // this should halt for 3 seconds for every loop
                        }
                        $rawBase64data = file_get_contents($pathToTempFlac);

                        $googlespeechURL = "https://speech.googleapis.com/v1beta1/speech:syncrecognize?key=". $key;

                        $data = array(
                            "config" => array(
                                'encoding' => 'FLAC',
                                'sampleRate' => 16000,
                                'languageCode' => 'en-US'                                
                            ),
                           "audio" => array(
                                "content" => base64_encode($rawBase64data)
                            )
                        );

                        $data_string = json_encode($data);                                                              

                        $ch = curl_init($googlespeechURL);                                                                      
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                           'Content-Type: application/json',                                                                                
                           'Content-Length: ' . strlen($data_string))                                                                       
                        );                                                                                                                   

                        $result = curl_exec($ch);
                        $result_array = json_decode($result, true);
                        
                        //parse result_array to get text
                        $audioText = $result_array['results'][0]['alternatives'][0]['transcript'];
                        unlink($pathToTempFlac); //delete created flac file (was only used for audio to text translation anyway) 
                        $action->delete(); //delete audio file attachment action because the text is aquired by this point
                        $action = new Actions;
                        $action->setAttributes (array (
                            'associationType' => X2Model::getAssociationType (get_class ($model)), 
                            'associationId' => $model->id,
                            'associationName' => $model->name,
                            'dueDate' => time (),
                            'completeDate' => time (),
                            'complete' => 'Yes',
                            'completedBy' => Yii::app()->user->getName (),
                            'private' => 0,
                        ), false);
                        $action->actionDescription = $audioText;
                        $action->type = 'note';

                        if(!$action->save ()) {
                            throw new CHttpException (500, Yii::t('app', 'Publish failed'));
                        }
                        $action->setActionDescription($audioText);
                        //$action->includeTextToAction($audioText);

                    } else {
                       throw new CHttpException (403, Yii::t('app', 'Google key file missing'));
                    }
                } 
                
                    $this->controller->renderPartial (
                        'application.modules.mobile.views.mobile._actionHistoryAttachments', array (
                        'model' => $model,
                        'refresh' => true,
                        'type' => $type,
                    ), false, true);
                
                Yii::app()->end ();
            } else {
                throw new CHttpException (500, Yii::t('app', 'Publish failed'));
            }
            
        }
    }

}

?>
