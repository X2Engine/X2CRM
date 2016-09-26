<?php
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
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
 ********************************************************************************/

class GoogleCalendarSync extends CalDavSync {
    
    public $calendarUrl = 'https://apidata.googleusercontent.com/caldav/v2/{calendarId}/events';

    protected function authenticate() {
        $credentials = json_decode($this->owner->credentials, true);
        $auth = new GoogleAuthenticator();
        if (!isset($credentials['refreshToken'])) {
            $user = User::model()->findByAttributes(array('username'=>$this->owner->createdBy));
            if($user && !is_null($auth->getStoredCredentials($user->id))){
                $credentials['refreshToken'] = $auth->getStoredCredentials($user->id);
                $this->owner->credentials = json_encode($credentials);
                $this->owner->update(array('credentials'));
            }else{
                $auth->flushCredentials();
                throw new CException('Invalid Google Calendar sync configuration.', 400);
            }
        }
        $auth->flushCredentials(false);
        $token = $auth->exchangeRefreshToken($credentials['refreshToken']);
        $accessToken = json_decode($token, true);
        return array(
            'oAuthToken' => $accessToken['access_token'],
            'username' => null,
            'password' => null,
        );
    }

}
