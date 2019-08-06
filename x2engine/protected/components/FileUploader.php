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




/**
 * Widget to upload files via AJAX. 
 * @package  application.components
 * @author Alex Rowe <alex@x2engine.com>
 *
 * Examples Usage:
 *
 * widget('FileUploader', array(
 *        
 *     'id' => 'myFileUploader',
 *     
 *     'mediaParams' => array (
 *         'associationType' => 'Contacts',
 *         'associationId'   => 23
 *     ),
 *     
 *     'viewParams' => array (
 *         'showButton' => false
 *     ),
 *
 *     'events' => array (
 *         'success' => 'console.log("success")'
 *     )
 *
 *     'acceptedFiles' => 'image/*'
 * 
 * ));
 *
 * How to access in Javascript:
 * x2.FileUploader.list['myFileUploader']
 *
 * See js/FileUploader.js
 * for Javascript Examples
 * 
 */
class FileUploader extends X2Widget {

    /**
     * Static counter for number of instances
     * @var integer
     */
    public static $instanceCount = 0;

    /**
     * Config array of extra options to be sent to viewFile
     * @var array
     */
    public static $defaultViewParams = array (
        'class' => '',
        'noPadding' => false,
        'showButton' => true,
        'open' => false,
        'closeButton' => true,
        'buttonText' => null,
    );

    /**
     * @see X2Widget::$JSClass
     */
    public $JSClass = 'FileUploader';

    /**
     * @see X2Widget::$viewFile
     */
    public $viewFile = 'fileUploader';

    /**
     * Id / Namespace of this instance. Used to create a unique
     * ID, and to reference 
     * @var string
     */
    public $id;

    /**
     * Url to upload media to 
     */
    public $url = '/site/upload';

    /**
     * Wether to allow Google Drive
     * @var array 
     */
    public $googleDrive = true;

    /**
     * Array of model attributes to set to uploaded files
     * @var array 
     */
    public $mediaParams = array();

    /**
     * Array of model attributes to set to uploaded files
     * @var array 
     */
    public $viewParams = array();

    /**
     * Array of Javascript snippets
     * @var array 
     */
    public $events = array(
        // 'success' => 'console.log(this)'
    );

    public $acceptedFiles = '';

    public function init() {
        // Increment instance count
        self::$instanceCount++;
        
        // Create a unique ID if one is not set
        if (empty($this->id)) {
            $this->id = 'attachments-'.self::$instanceCount;
        }

        // Create a name space to register mutiple scripts
        $this->namespace = 'attachments'.self::$instanceCount;

        // Set up default view Params
        $this->viewParams = array_merge (self::$defaultViewParams, $this->viewParams);
        if(is_null($this->viewParams['buttonText'])){
            $this->viewParams['buttonText'] = Yii::t('media','Upload File');
        }

        $this->googleDrive &= Yii::app()->params->profile->mediaWidgetDrive && 
            Yii::app()->settings->googleIntegration;


        $this->registerJSEvents ($this->events);
    }

    public function run () {
        $this->registerPackages ();
        $this->instantiateJSClass ();
        $this->render ($this->viewFile, $this->viewParams);
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'FileUploaderJS' => array(
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/FileUploader.js'
                ),
                'depends' => array('Dropzone', 'auxlib')
            ),
            'FileUploaderCSS' => array(
                'baseUrl' => Yii::app()->theme->baseUrl,
                'css' => array(
                    'css/components/FileUploader.css'
                ),
            ),
        ));
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge( 
                parent::getJSClassParams(), array(
                    'url' => $this->url,
                    'id'  => $this->id,
                    'mediaParams' => $this->mediaParams,
                    'viewParams' => $this->viewParams,
                    'acceptedFiles' => $this->acceptedFiles,
                    'maxFileSize' =>  
                        AppFileUtil::sizeToMb (ini_get('upload_max_filesize'), false),
                )
            );
        }
        return $this->_JSClassParams;
    }


    public function registerJSEvents ($events) {
        $js = '';
        foreach ($events as $event => $snippet) {
            $js .= "x2.FileUploader.on('$this->id', '$event', function(){".$snippet.";});";
        }
        Yii::app()->clientScript->registerScript ("FileUploaderEvents-$this->id", $js);
    }
}
    
?>
