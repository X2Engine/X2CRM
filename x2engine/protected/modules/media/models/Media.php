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

/**
 * This is the model class for table "x2_media".
 * 
 * @package X2CRM.modules.media.models
 * @property integer $id
 * @property string $associationType
 * @property integer $associationId
 * @property string $fileName
 * @property string $uploadedBy
 * @property string $createDate
 */
class Media extends X2Model {

	public $_path;
	public $_url;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Media the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_media';
	}

	public function behaviors() {
		$behaviors = array_merge(parent::behaviors(),array(
			'X2LinkableBehavior' => array(
				'class' => 'X2LinkableBehavior',
				'module' => 'media',
				'autoCompleteSource' => null
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults' => array(),
				'defaultStickOnClear' => false
			)
		));
		unset($behaviors['changelog']);
		return $behaviors;
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
		$criteria->addCondition("uploadedBy='$username' OR private=0 OR private=null");
		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria = new CDbCriteria;
		return $this->searchBase($criteria);
	}

	public function isImage() {
		return strpos($this->resolveType(), 'image/') === 0;
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
	public function getImage() {
		if ($this->fileExists() && $this->isImage())
			return CHtml::image($this->url, '', array('class' => 'attachment-img'));
		return '';
	}

	/**
	 * Magic path getter
	 * 
	 * Obtains the full, absolute path to a file.
	 */
	public function getPath() {
		if (!isset($this->_path)) {
			$pathFmt = array(
				'{bp}/uploads/media/{uploadedBy}/{fileName}',
				'{bp}/uploads/{fileName}'
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
					$this->_path = null;
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
		if ($this->isImage()) {
			if (empty($this->dimensions) && extension_loaded('gd')) {
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
			return "{$dim['width']} x {$dim['height']}";
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
				if($finfo = FileUtil::finfo())
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
		$path = "uploads/media/{$uploadedBy}/{$fileName}"; // try new format
		if (file_exists($path))
			return $path;
		else {
			$path = "uploads/{$fileName}"; // try old format
			if (file_exists($path))
				return $path;
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
			if (file_exists($relPath)) // ensure file exists
				$this->_url = Yii::app()->request->baseUrl . "/$relPath";
			else
				$this->_url = null;
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
		if ($path = $this->getPath()) // ensure file exists
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
		return CHtml::link($this->fileName, Yii::app()->controller->createUrl('/media/', array('view' => $this->id)));
	}

	// 
	public function fileExists() {
		if (file_exists("uploads/media/{$this->uploadedBy}/{$this->fileName}")) // try new format
			return true;
		else if (file_exists("uploads/{$this->fileName}")) // try old format
			return true;

		return false;
	}

	// convert a string (eg '10MB') to bytes
	private static function toBytes($size) {
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
		return "exe, bat, dmg, js, jar, swf, php, pl, cgi, htaccess, py";
	}
    
    /**
     * @param string $str
     * @param boolean $makeLink
     * @param boolean $makeImage
     * @return string 
     */ 
	public static function attachmentSocialText($str,$makeLink = false,$makeImage = false) {
		// $a = '<a href="/x2merge/index.php/media/16">footer.png</a>';
		
		// echo ,preg_match('/^<a href=".+(media\/[0-9]+)" target="_blank">.+<\/a>$/i',$description
		$matches = array();
		// die(CHtml::encode($description));
		if(preg_match('/^<a href=".+media\/view\/([0-9]+)">.+<\/a>$/i',$str,$matches)) {
			if(count($matches) == 2 && is_numeric($matches[1])) {
			
				$media = X2Model::model('Media')->findByPk($matches[1]);
				if(isset($media)) {
					$str = Yii::t('media','File:') . ' ';
					
					$fileExists = $media->fileExists();
					
					if($fileExists == false)
					    return $str . ' ' . Yii::t('media','(deleted)');
					
					if($makeLink)
					    $str .= $media->getMediaLink();
					else
					    $str .= "";
					
					if($makeImage && $media->isImage())	// to render an image, first check file extension
					    $str .= $media->getImage();
					
					return $str;
				}
			}
		}
        return x2base::convertUrls($str);
    }
    
    /**
     * Generates a description message with a link and optional preview image
     * for media items.
     * 
     * @param string $actionDescription
     * @param boolean $makeLink
     * @param boolean $makeImage
     * @return string 
     */
	public static function attachmentActionText($actionDescription,$makeLink = false,$makeImage = false) {
	
		$data = explode(':',$actionDescription);
		$media = null;
		if(count($data) == 2 && is_numeric($data[1])) // ensure data is formatted properly
			$media = X2Model::model('Media')->findByPK($data[1]); // look for an entry in the media table
		
		if($media) { // did we find an entry in the media table?
			$str = Yii::t('media','File:') . ' ';
			
			$fileExists = $media->fileExists();
			
			if($fileExists == false)
				return $str . $data[0] . ' ' . Yii::t('media','(deleted)');

			if($makeLink){
			    $str .= $media->getMediaLink() ." | " . CHtml::link('[Download]',array('/media/download/'.$media->id));
            }else
			    $str .= $data[0];

			if($makeImage && $media->isImage())	// to render an image, first check file extension
			    $str .= $media->getImage();
			
			return $str;
			
		} else
			return $actionDescription;
	}

}