<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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



/**
 * @edition: ent
 */

class NexmoProject extends JSONEmbeddedModel implements AdminOwnedCredentials {

    public static function getAdminProperty () {
        return 'nexmoCredentialsId'; 
    }

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $clientId = '';

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $clientSecret = '';

     
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

    public function getProtectedFields () {
        return array ('clientId', 'clientSecret', 'apiKey', 'serviceAccountKeyFileContents', 'projectId');
    }

    public function renderForm () {
        Yii::app()->controller->renderPartial (
            'application.views.profile._nexmoProjectForm', array (
                'model' => $this
            ));
    }

    public function getMetaData () {
        return array (
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'Nexmo project',
        );
    }

    public function rules(){
        return array(      
            array('apiKey', 'safe'),          
            array('projectId', 'safe'),         
            array ('serviceAccountKeyFileContents', 'safe'),          
            array('clientId,clientSecret', 'safe'),
        );
    }

    public function getPageTitle () {
        return $this->modelLabel ();
    }

    public function modelLabel() {
        return Yii::t('app','Nexmo Integration');
    }

    public function attributeLabels(){
        return array(
            'clientId' => Yii::t('app','Client ID'),
            'clientSecret' => Yii::t('app','Client Secret'),
             
            'apiKey' => Yii::t('app','API Key'),
            'serviceAccountKeyFileContents' => Yii::t('app','Service Account File Key'),
            'projectId' => Yii::t('app','Nexmo API Project ID')
        );
    }

    public function htmlOptions ($name, $options=array ()) {
        return X2Html::mergeHtmlOptions (
            parent::htmlOptions ($name, $options), array ('class' => 'nexmo-credential-input'));
    }

}

?>
