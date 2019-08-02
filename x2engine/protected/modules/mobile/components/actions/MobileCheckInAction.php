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




class MobileCheckInAction extends MobileAction {
    public $pageDepth = 1;

    public function run () {
        $model = new EventPublisherFormModel;
        $profile = Yii::app()->params->profile;
        $settings = Yii::app()->settings;
        $creds = Credentials::model()->findByPk($settings->googleCredentialsId);
        $key = null;
        if($creds && $creds->auth && $creds->auth->apiKey){
            $key = $creds->auth->apiKey;
        }
        if (isset ($_POST['geoCoords']) && isset ($_POST['geoLocationCoords'])) {
            $decodedResponse = $_POST['geoLocationCoords'];
            if ($key && $decodedResponse === 'set'){
                $decodedResponse = json_decode($_POST['geoCoords'],true);
                //https://davidwalsh.name/curl-post
                //extract data from the post
                //set POST variables
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' .
                    $decodedResponse['lat'] . ',' . $decodedResponse['lon'] . 
                    '&key=' . $key;
                //open connection
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_URL, $url);

                //execute post
                $result = curl_exec($ch);
                //close connection
                
                echo $result;
                curl_close($ch);

                Yii::app()->end ();
            }        
        }

        if (isset ($_POST['EventPublisherFormModel'])) {
            $decodedResponse = json_decode(filter_input(INPUT_POST, 'geoCoords', FILTER_DEFAULT),true);
            $location = Yii::app()->params->profile->user->logLocation('mobileCheckIn', 'POST');
            $decodedResult = $location ? $location->generateStaticMap() : null;
            
            $model->setAttributes ($_POST['EventPublisherFormModel']);
            if ($decodedResult && isset ($_FILES['EventPublisherFormModel'])) {
                $model->photo = CUploadedFile::getInstance ($model, 'photo');
            }
            
            //AuxLib::debugLogR ('validating');
            if ($model->validate ()) {
                //AuxLib::debugLogR ('valid');
                $event = new Events;
                $eventTextLocation = ' ' . '$|&|$' . ' ' . '$|&|$' .$model->text. ' | '. 
                              Formatter::formatDateTime(time());

                $event->setAttributes (array (
                    'visibility' => X2PermissionsBehavior::VISIBILITY_PUBLIC,
                    'user' => $profile->username,
                    'type' => 'media',
                    'text' => $eventTextLocation,
                    'photo' => $model->photo
                ), false);
                if ($location)
                    $event->locationId = $location->id;
                if ($key && !empty($decodedResponse) && !empty($decodedResult)) {
                    if ($event->saveRaw ($profile,$decodedResult)) {
                        if (!isset ($_FILES['EventPublisherFormModel'])) {
                            //AuxLib::debugLogR ('saved');
                            $this->controller->redirect (
                                $this->controller->createAbsoluteUrl (
                                    '/profile/mobileActivity'));
                        } else {
                            echo CJSON::encode (array ( 
                                'redirectUrl' => $this->controller->createAbsoluteUrl (
                                    '/profile/mobileActivity'),
                            ));
                            Yii::app()->end ();
                        }
                    } else {
                        //AuxLib::debugLogR ('invalid');
                        throw new CHttpException (500, implode (';', $event->getAllErrorMessages ()));
                    }
                } else {
                    echo CJSON::encode (array (
                        'error' => Yii::t('mobile', 'Failed to retrieve static map. Please ensure that Google Integration is enabled.'),
                        'redirectUrl' => $this->controller->createAbsoluteUrl (
                            '/profile/mobileActivity'),
                    ));
                    Yii::app()->end ();
                }
            } else {
                if (isset ($_FILES['EventPublisherFormModel'])) {
                    throw new CHttpException (500, implode (';', $event->getAllErrorMessages ()));
                }
                //AuxLib::debugLogR ('invalid model');
                //AuxLib::debugLogR ($model->getErrors ());
            }
        }
        $this->controller->render (
            $this->pathAliasBase.'views.mobile.mobileCheckInPublisher', array (
                'profile' => $profile,
                'model' => $model,
            )
        );
    }

}

?>
