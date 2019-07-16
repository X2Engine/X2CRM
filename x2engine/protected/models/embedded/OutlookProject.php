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




class OutlookProject extends JSONEmbeddedModel implements AdminOwnedCredentials {

    public static function getAdminProperty () {
        return 'outlookCredentialsId'; 
    }

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $outlookId = '';

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $outlookSecret = '';

     
    /**
     *  @var string Used for Google+ and Google APIs integration
     */
    public $apiKey = '';
    
    /**
     *  @var string the path for the Service Account key file used for Google+ and Google APIs integration
     */
    public $serviceAccountKeyFileContents = '';
    
    /**
     *  @var string project id used for Google+ and Google APIs integration
     */
    public $projectId = '';

    // pseudo-attributes which belong to Admin. These don't get enrypted with the other attributes,
    // but do get rendered in the same form
    private $_gaTracking_internal;
    private $_gaTracking_public;

    public function getProtectedFields () {
        return array ('outlookId', 'outlookSecret', 'apiKey', 'serviceAccountKeyFileContents', 'projectId');
    }

    public function renderForm () {
        Yii::app()->controller->renderPartial (
            'application.views.profile._outlookProjectForm', array (
                'model' => $this
            ));
    }

    public function getMetaData () {
        return array (
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'Outlook project',
        );
    }

    public function rules(){
        return array(      
            array('apiKey', 'safe'),          
            array('projectId', 'safe'),         
            array ('serviceAccountKeyFileContents', 'safe'),          
            array('outlookId,outlookSecret', 'safe'),
        );
    }

    /**
     * Ensure that if one of the OAuth 2.0 fields are set, both are
     */
//    public function validateOAuthCreds ($attr) {
//        if ($this->$attr === null) return;
//        if ($attr === 'clientSecret' && $this->clientId === null) {
//            $this->addError ('clientId', Yii::t('app', 'Field required'));
//        } elseif ($attr === 'clientId' && $this->clientSecret === null) {
//            $this->addError ('clientSecret', Yii::t('app', 'Field required'));
//        }
//    }

    public function setGaTracking_internal ($gaTracking_internal) {
        $this->_gaTracking_internal = $gaTracking_internal;
    }

    public function setGaTracking_public ($gaTracking_public) {
        $this->_gaTracking_public = $gaTracking_public;
    }

    public function getGaTracking_public () {
        return $this->_gaTracking_public;
    }

    public function getGaTracking_internal () {
        return $this->_gaTracking_internal;
    }

    public function getPageTitle () {
        return $this->modelLabel ();
    }

    public function modelLabel() {
        return Yii::t('app','Outlook Integration');
    }

    public function attributeLabels(){
        return array(
            'outlookId' => Yii::t('app','Outlook ID'),
            'outlookSecret' => Yii::t('app','Outlook Secret'),
             
            'apiKey' => Yii::t('app','API Key'),
            'serviceAccountKeyFileContents' => Yii::t('app','Service Account File Key'),
            'projectId' => Yii::t('app','Google API Project ID'),
             
            'gaTracking_public' => Yii::t('app','Outlook Analytics Property ID (public)'),
            'gaTracking_internal' => Yii::t('app','Outlook Analytics Property ID (internal)'),
        );
    }

    public function htmlOptions ($name, $options=array ()) {
        return X2Html::mergeHtmlOptions (
            parent::htmlOptions ($name, $options), array ('class' => 'outlook-credential-input'));
    }

}

?>
