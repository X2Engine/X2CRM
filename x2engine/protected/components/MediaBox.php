<?php

/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */

/**
 * Class for the media library box widget.
 *
 * @package X2CRM.components
 */
class MediaBox extends X2Widget {

    public $visibility;
    public $drive = 0;

    public function init(){
        $this->drive = Yii::app()->params->profile->mediaWidgetDrive && Yii::app()->params->admin->googleIntegration;
        if(Yii::app()->params->admin->googleIntegration){
            $auth = new GoogleAuthenticator();
            if(!isset($_SESSION['driveFiles']) && $auth->getAccessToken()){
                Yii::import('application.modules.media.controllers.MediaController');
                $mediaController = new MediaController('MediaController');
                $_SESSION['driveFiles'] = $mediaController->printFolder('root', $auth);
            }
        }
        parent::init();
    }

    public function run(){
        $this->render('mediaBox', array()); //array(
    }

}
