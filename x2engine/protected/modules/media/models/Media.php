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
 * This is the model class for table "x2_media".
 *
 * @package application.modules.media.models
 * @property integer $id
 * @property string $associationType
 * @property integer $associationId
 * @property string $fileName
 * @property string $uploadedBy
 * @property string $createDate
 */
class Media extends X2Model {

    public $supportsWorkflow = false;
    public $_path;
    public $_url;
    protected $fieldFormatterClass = 'MediaFieldFormatter';

    /**
     * Returns the static model of the specified AR class.
     * @return Media the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    private static $_loginLogo;

    public static function getLoginLogo() {
        if (!isset(self::$_loginLogo)) {
            $logo = Media::model()->findByAttributes(array(
                'associationId' => 1,
                'associationType' => 'loginLogo'
            ));
            self::$_loginLogo = $logo;
        }
        return self::$_loginLogo;
    }

    private static $_menuLogo;

    public static function getMenuLogo() {
        if (!isset(self::$_menuLogo)) {
            $logo = Media::model()->findByAttributes(array(
                'associationId' => 1,
                'associationType' => 'logo'
            ));
            self::$_menuLogo = $logo;
        }
        return self::$_menuLogo;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_media';
    }

    public static function createMediaFromFormData() {
        if (Yii::app()->user->isGuest) {
            throw new Exception('You are not logged in.');
        }

        if (!isset($_FILES['uploadMedia'])) {
            throw new Exception('Invalid request.');
        }

        $upload = CUploadedFile::getInstanceByName('uploadMedia');

        if ($upload == null) {
            throw new Exception('Invalid file.');
        }

        $fileName = $upload->getName();
        $fileName = str_replace(' ', '_', $fileName);

        $userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
        $userFolderPath = 'uploads/protected/media/' . $userFolder;
        // if user folder doesn't exit, try to create it
        if (!(file_exists($userFolderPath) && is_dir($userFolderPath))) {
            if (!@mkdir('uploads/protected/media/' . $userFolder, 0777, true)) { // make dir with edit permission
                throw new Exception('Error creating user folder.');
            }
        }

        if (!$upload->saveAs($userFolderPath . DIRECTORY_SEPARATOR . $fileName)) {
            throw new Exception('Error saving file');
        }

        // save media info
        $model = new Media;
        $model->fileName = $fileName;
        $model->createDate = time();
        $model->lastUpdated = time();
        $model->uploadedBy = Yii::app()->user->name;
        $model->associationType = 'none';

        if (!$model->save()) {
            throw new Exception('Error saving Media entry');
        }

        $fileUrl = $model->getPublicUrl();
        
        return array('id' => $model->id, 'link' => $fileUrl);
    }

    /**
     * Called after removing media or changing the filename field. Checks for existing references
     * to filename and deletes file if none exist.
     */
    private function collectGarbage(array $exclude = array()) {
        if (!in_array($this->getPath(), $exclude) && file_exists($this->getPath())) {
            $media = Media::model()->findByAttributes(array(
                'uploadedBy' => $this->uploadedBy,
                'fileName' => $this->fileName
            ));

            // Only delete the file if it is the last media object
            // To use it. 
            if (!$media) {
                unlink($this->getPath());
            }
        }
    }

    public function afterDelete() {
        parent::afterDelete();
        // Reset path if name was changed:
        $this->_path = null;
        $this->collectGarbage();

        // if theme is deleted which is default, unset default theme setting
        if ($this->id === Yii::app()->settings->defaultTheme) {
            Yii::app()->settings->defaultTheme = null;
            Yii::app()->settings->enforceDefaultTheme = false;
            Yii::app()->settings->save();
        }
    }

    public function resolveNameConflicts() {
        $found = (int) Media::model()->countByAttributes(array('fileName' => $this->fileName));

        // rename file if there name conflicts by suffixing "(n)"
        if ($found) {
            $count = 1;
            $newName = $this->fileName;
            $ext = CFileHelper::getExtension($newName);
            $base = preg_replace('/\.' . preg_quote($ext) . '$/', '', $newName);
            while ($found) {
                $newName = "$base($count).$ext";
                $found = (int) Media::model()->countByAttributes(array('fileName' => $newName));
                $count++;
            }
            $this->fileName = $newName;
        }
    }

    public function beforeSave() {
        if (empty($this->associationType)) {
            $this->associationType = 'none';
        }

        if (empty($this->uploadedBy)) {
            $this->uploadedBy = Yii::app()->user->name;
        }

        if (empty($this->name)) {
            $this->name = $this->fileName;
        }
        if ($this->isNewRecord && $this->associationType !== 'theme' && empty($this->accessKey)) {
            $this->accessKey = bin2hex(openssl_random_pseudo_bytes(32));
        }

        $this->getPath();

        // Scan uploaded media for viruses if requested
        if ($this->isNewRecord && Yii::app()->settings->scanUploads === '1' &&
                !$this->clamScan()) {
            FileUtil::rrmdir($this->path);
            return false;
        }

        return parent::beforeSave();
    }

    public function behaviors() {
        $behaviors = array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'media',
                'autoCompleteSource' => null
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'ClamScanBehavior' => array(
                'class' => 'application.components.behaviors.ClamScanBehavior',
                'pathAttribute' => 'path',
            )
        ));
        unset($behaviors['changelog']);
        return $behaviors;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.

        return array_merge(
                $this->getBehaviorRules(), array(
            array('fileName', 'unique', 'on' => 'themeCreate'),
            array('fileName', 'length', 'max' => 100),
                )
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'campainAttachments' => array(self::HAS_MANY, 'CampaignAttachment', 'media'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    /* 	public function attributeLabels() {
      return array(
      'id' => 'ID',
      'associationType' => 'Association Type',
      'associationId' => 'Association',
      'fileName' => 'File Name',
      'uploadedBy' => 'Uploaded By',
      'createDate' => 'Create Date',
      );
      } */

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        $username = Yii::app()->user->name;
        if (!Yii::app()->params->isAdmin)
            $criteria->addCondition("t.uploadedBy='$username' OR t.private=0 OR t.private=null");
        $criteria->addCondition("t.associationType != 'theme'");
        return $this->searchBase($criteria);
    }

    public function isImage() {
        return strpos($this->resolveType(), 'image/') === 0;
    }

    public function isAudio() {
        return strpos($this->resolveType(), 'audio/') === 0;
    }

    public function isVideo() {
        return strpos($this->resolveType(), 'video/') === 0;
    }

    /**
     * Return true if $filename has an image extension. Image extensions include:
     * jpg, gif, png, bmp, jpeg, jpe
     *
     * @param $filename the file name that has the extension
     * @return true if $filename has an image extension, false otherwise
     */
    public static function isImageExt($filename) {
        return (bool) preg_match('/\.(jpg|gif|png|bmp|jpeg|jpe)$/i', $filename);
    }

    // return an img tag of this file
    // return '' if file is not an image
    public function getImage($link = false, array $htmlOptions = array('encode' => false)) {
        if (!$this->fileExists() || !$this->isImage()) {
            return '';
        }

        if ($this->drive) {
            return $this->googlePreview;
        }

        $img = CHtml::image(
                        $this->getPublicUrl(), '', X2Html::mergeHtmlOptions(array(
                            'class' => 'attachment-img',
                                ), $htmlOptions)
        );

        if (!$link) {
            return $img;
        }

        return X2Html::link($img, $this->getPublicUrl());
    }

    // return an audio tag of this file
    // return '' if file is not an image
    public function getAudio($link = false, array $htmlOptions = array('encode' => false)) {
        if (!$this->fileExists() || !$this->isAudio()) {
            return '';
        }

        if ($this->drive) {
            return $this->googlePreview;
        }
        $audio = '';
        if (!Yii::app()->params->isPhoneGap) {
            $audio = Yii::app()->controller->widget('application.extensions.mediaElement.MediaElementPortlet', array(
                'url' => $this->getPublicUrl(),
                // or you can set the model and attributes
                //'model' => $model,
                //'attribute' => 'url'
                // its required and so you have to set correctly
                'mimeType' => $this->resolveType(),
                    ), True);
        } else {
            $audio = '';
        }

        if (!$link) {
            return $audio;
        }

        return X2Html::link($audio, $this->getPublicUrl());
    }

    // return an video tag of this file
    // return '' if file is not an image
    public function getVideo($link = false, array $htmlOptions = array('encode' => false)) {
        if (!$this->fileExists() || !$this->isVideo()) {
            return '';
        }

        if ($this->drive) {
            return $this->googlePreview;
        }
        $video = '';
        if (!Yii::app()->params->isPhoneGap) {
            $video = Yii::app()->controller->widget('application.extensions.mediaElement.MediaElementPortlet', array(
                'url' => $this->getPublicUrl(),
                // or you can set the model and attributes
                //'model' => $model,
                //'attribute' => 'url'
                // its required and so you have to set correctly
                'mimeType' => $this->resolveType(),
                    ), True);
        } else {
            $video = '';
        }

        if (!$link) {
            return $video;
        }

        return X2Html::link($video, $this->getPublicUrl());
    }

    public function getGooglePreview() {
        return X2Html::tag('iframe', array(
                    'src' => $this->url . '/preview',
                    'class' => 'google-drive'
                        ), '');
    }

    public function renderAttribute(
    $fieldName, $makeLinks = true, $textOnly = true, $encode = true) {

        switch ($fieldName) {
            case 'image':
                $imageLink = $this->getImage(true);
                return
                        '<div class="media-image">
                        <div class="full-size-screen">' .
                        X2Html::fa('expand') . Yii::t('media', 'View Full Size') . '
                        </div>
                        ' . $imageLink . '
                    </div>';
                break;
        }

        return call_user_func_array('parent::' . __FUNCTION__, func_get_args());
    }

    /**
     * Magic path getter
     *
     * Obtains the full, absolute path to a file.
     * @return String|NULL Returns a path to the file or NULL if the file does not exist.
     */
    public function getPath() {
        if (!isset($this->_path)) {
            if ($this->associationType === 'logo' || $this->associationType === 'loginLogo') { // an exception for logos, fileName equals path name
                $this->_path = $this->fileName;
            } else {
                $pathFmt = array(
                    implode(DIRECTORY_SEPARATOR, array('{bp}', 'uploads', 'protected', 'media', '{uploadedBy}', '{fileName}')),
                    implode(DIRECTORY_SEPARATOR, array('{bp}', 'uploads', 'protected', '{fileName}')),
                );
                $basePath = realpath(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..');
                $params = array(
                    '{bp}' => $basePath,
                    '{uploadedBy}' => $this->uploadedBy,
                    '{fileName}' => $this->fileName
                );
                foreach ($pathFmt as $pfmt) {
                    $path = realpath(strtr($pfmt, $params));
                    if ((bool) $path) {
                        $this->_path = $path;
                        break;
                    } else {
                        // The file does not exist.
                        $this->_path = null;
                    }
                }
            }
        }

        return $this->_path;
    }

    /**
     * Gets file size
     *
     * Obtains and returns the file size. If it hasn't been saved in the
     * database, this method does so.
     *
     * @return type
     */
    public function resolveSize() {

        if (empty($this->filesize)) {
            if (file_exists($this->path)) {
                $this->filesize = filesize($this->path);
                if (!$this->isNewRecord) {
                    $this->saveAttributes(array('filesize'));
                }
            } else {
                $this->filesize = null;
            }
        }
        return $this->filesize;
    }

    /**
     * Gets dimensions of the file, if it is an image.
     *
     * @return type
     */
    public function resolveDimensions() {
        if (!$this->drive && $this->isImage()) {
            if (empty($this->dimensions) && extension_loaded('gd') && !empty($this->path)) {
                $sizeArr = getimagesize($this->path);
                $this->dimensions = CJSON::encode(array(
                            'width' => $sizeArr[0],
                            'height' => $sizeArr[1],
                ));
                if (!$this->isNewRecord)
                    $this->saveAttributes(array('dimensions'));
            }
        }
        return $this->dimensions;
    }

    /**
     * Magic getter for human-readable file size.
     *
     * @return type
     */
    public function getFmtSize() {
        return FileUtil::formatSize($this->resolveSize());
    }

    public function getFmtDimensions() {
        if ($this->isImage()) {
            $dim = CJSON::decode($this->resolveDimensions());
            if (isset($dim['width'], $dim['height']))
                return "{$dim['width']} x {$dim['height']}";
            else
                return null;
        } else
            return null;
    }

    /**
     * Gets file type info
     *
     * Examines the file and gets MIME info; saves it in the record if it's not
     * there already.
     *
     * @return type
     */
    public function resolveType() {
        if (empty($this->mimetype)) {
            if (file_exists($this->path)) {
                if ($finfo = FileUtil::finfo())
                    $this->mimetype = finfo_file($finfo, $this->path, FILEINFO_MIME);
                else
                    $this->mimetype = null;
                if (!$this->isNewRecord)
                    $this->saveAttributes(array('mimetype'));
            } else {
                $this->mimetype = null;
            }
        }
        return $this->mimetype;
    }

    public static function getFilePath($uploadedBy, $fileName) {
        $possiblePaths = array(
            "uploads/protected/media/{$uploadedBy}/{$fileName}",
            "uploads/protected/{$fileName}",
        );
        foreach ($possiblePaths as $path) {
            if (file_exists(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, "..", $path)))) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Magic uploaded file URL getter method
     * @return type
     */
    public function getUrl() {
        if (!isset($this->_url)) {
            $relPath = self::getFilePath($this->uploadedBy, $this->fileName);

            if ($this->drive) {
                $this->_url = "https://drive.google.com/file/d/" . $this->fileName;
            } else if (file_exists(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, "..", $relPath)))) { // ensure file exists
                $this->_url = Yii::app()->request->baseUrl . "/$relPath";
            } else {
                $this->_url = null;
            }
        }
        return $this->_url;
    }

    public static function getFileUrl($path) {
        if ($path) // ensure file exists
            return Yii::app()->request->baseUrl . "/$path";
        return null;
    }

    // get the full url (including e.g. example.com) to a file
    // return null if file doesn't exist
    public function getFullUrl() {
        if ($path = self::getFilePath($this->uploadedBy, $this->fileName)) // ensure file exists
            return Yii::app()->getBaseUrl(true) . "/$path";

        return null;
    }

    public static function getFullFileUrl($path) {
        if ($path) // ensure file exists
            return Yii::app()->getBaseUrl(true) . "/$path";

        return null;
    }

    // return a link to the Media Module view for this file
    public function getMediaLink() {
        if ($this->drive) {
            $name = $this->name;
            // return CHtml::link($this->name, "https://drive.google.com/file/d/".$this->fileName, array('target' => '_blank'));
        } else {
            $name = $this->fileName;
        }
        if (!ResponseUtil::isCli()) {
            return CHtml::link(
                            $this->fileName, Yii::app()->controller->createUrl('/media/', array('view' => $this->id)));
        } else {
            return CHtml::link($this->fileName, Yii::app()->absoluteBaseUrl . (YII_UNIT_TESTING ? '/index-test.php' : '/index.php') . '/media/media/view/' . $this->id);
        }
    }

    //
    public function fileExists() {
        if (file_exists(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, "..", "uploads", "protected", "media", $this->uploadedBy, $this->fileName)))) // try new format
            return true;
        else if (file_exists(implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, "..", "uploads", "protected", $this->fileName)))) // try old format
            return true;
        else if ($this->drive)
            return true;

        return false;
    }

    // convert a string (eg '10MB') to bytes
    private static function toBytes($size) {
        if (!ctype_alpha(substr($size, -1))) {
            // No suffix, size must already be in bytes
            return $size;
        }
        if (strtolower(substr($size, -1)) === 'b') {
            // Remove a trailing 'b'
            $size = substr($size, 0, -1);
        }
        $type = strtolower(substr($size, -1)); // last char
        $num = substr($size, 0, -1); // number
        switch ($type) {
            case 'p':
                $num *= 1024;
            case 't':
                $num *= 1024;
            case 'g':
                $num *= 1024;
            case 'm':
                $num *= 1024;
            case 'k':
                $num *= 1024;
                break;
        }

        return $num;
    }

    // return the max file size the server will except for upload files
    public static function getServerMaxUploadSize() {
        $max_post = Media::toBytes(ini_get('post_max_size'));
        $max_upload_file = Media::toBytes(ini_get('upload_max_filesize'));
        $max_upload_size = min($max_post, $max_upload_file);
        $max_upload_size /= (1024 * 1024); // convert bytes to megabytes
        $max_upload_size = round($max_upload_size, 2); // round to two decimal places

        return $max_upload_size;
    }

    public static function forbiddenFileTypes() {
        return "exe, bat, dmg, js, jar, swf, php, pl, cgi, htaccess, py, rb";
    }

    private static function getImageText($str, $makeLink, $makeImage, $media) {
        $fileExists = $media->fileExists();

        if ($fileExists == false)
            return $str . ' ' . Yii::t('media', '(deleted)');

        if ($makeLink && !Yii::app()->params->isMobileApp)
            $str .= $media->getMediaLink();
        else
            $str .= "";

        if ($makeImage && $media->isImage()) { // to render an image, first check file extension
            $str .= '<br>' . $media->getImage();
        }

        return $str;
    }

    /**
     * @param string $str
     * @param boolean $makeLink
     * @param boolean $makeImage
     * @return string
     */
    public static function attachmentSocialText($str, $makeLink = false, $makeImage = false) {
        // $a = '<a href="/x2merge/index.php/media/16">footer.png</a>';
        // echo ,preg_match('/^<a href=".+(media\/[0-9]+)" target="_blank">.+<\/a>$/i',$description
        $matches = array();
        // die(CHtml::encode($description));
        if (preg_match('/^<a href=".+media\/view\/([0-9]+)">.+<\/a>$/i', $str, $matches)) {
            if (count($matches) == 2 && is_numeric($matches[1])) {

                $media = X2Model::model('Media')->findByPk($matches[1]);
                if (isset($media)) {
                    $str = $makeLink ? Yii::t('media', 'File:') . ' ' : '';

                    return self::getImageText($str, $makeLink, $makeImage, $media);
                }
            }
        } elseif (preg_match('/^<a target="_blank" href="https:\/\/drive.google.com\/file\/d\/(.+)">.+<\/a>$/i', $str, $matches)) {
            if (count($matches) == 2) {
                $media = X2Model::model('Media')->findByAttributes(array('fileName' => $matches[1]));
                if (isset($media)) {
                    $str = $makeLink ? Yii::t('media', 'Google Drive:') . ' ' : '';

                    return self::getImageText($str, $makeLink, $makeImage, $media);
                }
            }
        }
        return x2base::convertUrls($str);
    }

    public function getDownloadLink($text = null, $htmlOptions = array()) {
        $text = !isset($text) ? '[' . CHtml::encode(Yii::t('media', 'Download')) . ']' : $text;

        if (Yii::app()->params->isPhoneGap) {
            $url = Yii::app()->createAbsoluteUrl(
                    '/media/media/download', array('id' => $this->id));
        } else {

            $url = Yii::app()->createUrl('/media/media/download', array('id' => $this->id));
        }

        return CHtml::link($text, $url, $htmlOptions);
    }

    public function renderFile() {
        $filePath = $this->getPath();
        if ($filePath != null) {
            $file = Yii::app()->file->set($filePath);
        } else {
            throw new CHttpException(404);
        }
        if ($file->exists) {
            header('Content-type: ' . $file->mimeType);
            echo $file->getContents();
        }
    }

    public function getAccessKey() {
        if (empty($this->accessKey)) {
            $this->accessKey = bin2hex(openssl_random_pseudo_bytes(32));
            Yii::app()->db->createCommand()->update(
                    'x2_media', array('accessKey' => $this->accessKey), 'id=:id', array(':id' => $this->id));
        }
        return $this->accessKey;
    }

    public function getPublicUrl($key = true) {
        return Yii::app()->createExternalUrl('/media/media/getFile', array(
                    'id' => $this->id,
                    'key' => $key ? $this->getAccessKey() : '',
        ));
    }

    /**
     * Generates a description message with a link and optional preview image
     * for media items.
     *
     * @param string|Actions $actionDescription
     * @param boolean $makeLink
     * @param boolean $makeImage
     * @return string
     */
    public static function attachmentActionText($action, $makeLink = false, $makeImage = false) {

        $media = null;
        if ($action instanceof Actions) {
            $media = $action->media;
            if ($media)
                $media = array_pop($media);
        }
        if (!$media) { // handle legacy association format
            $actionDescription = Yii::app()->controller->convertUrls($action->actionDescription);
            $data = explode(':', $actionDescription);
            $media = null;
            if (count($data) == 2 && is_numeric($data[1])) // ensure data is formatted properly
                $media = X2Model::model('Media')->findByPK($data[1]); // look for an entry in the media table
        }
        if ($media) { // did we find an entry in the media table?
            if ($media->drive) {
                $str = Yii::t('media', 'Google Drive:') . ' ';
            } else {
                $str = Yii::t('media', 'File:') . ' ';
            }

            $fileExists = $media->fileExists();

            if ($fileExists == false)
                return $str . $data[0] . ' ' . Yii::t('media', '(deleted)');

            if ($makeLink) {
                $str .= $media->getMediaLink();
                if (!$media->drive) {
                    $str .= " | " . CHtml::link('[Download]', array('/media/media/download', 'id' => $media->id));
                }
            } else
                $str .= $data[0];

            if ($makeImage && $media->isImage()) // to render an image, first check file extension
                $str .= $media->getImage();

            return $str;
        } else
            return CHtml::encode(Yii::t('app', 'Attachment not found'));
    }

}
