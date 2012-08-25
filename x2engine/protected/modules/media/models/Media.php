<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

/**
 * This is the model class for table "x2_media".
 *
 * The followings are the available columns in table 'x2_media':
 * @property integer $id
 * @property string $associationType
 * @property integer $associationId
 * @property string $fileName
 * @property string $uploadedBy
 * @property string $createDate
 */
class Media extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Media the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_media';
	}

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/media',
				'autoCompleteSource'=>null
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
/*	public function attributeLabels() {
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

		$criteria=new CDbCriteria;
		$username = Yii::app()->user->name;
		$criteria->addCondition("uploadedBy='$username' OR private=0");
		return $this->searchBase($criteria);
	}
	
	public function searchAdmin() {
		$criteria=new CDbCriteria;
		return $this->searchBase($criteria);
	}
	
	public function isImage() {
		// $imageExtensions = array('jpg','gif','png','bmp','jpeg','jpe');
		
		// $extension = array_pop(explode('.', $this->fileName));
		// return in_array($extension, $imageExtensions);
		return (bool)preg_match('/\.(jpg|gif|png|bmp|jpeg|jpe)$/i',$this->fileName);
	}
	
	// return an img tag of this file
	// return '' if file is not an image
	public function getImage() {
		if($this->fileExists() && $this->isImage())
			return CHtml::image($this->getUrl(), '', array('class'=>'attachment-img'));

		return '';
	}
	
	// get a directory path to the file (including the file name)
	// return null if file doesn't exist
	public function getPath() {
		$path = "uploads/media/{$this->uploadedBy}/{$this->fileName}"; // try new format
		if(file_exists($path))
			return $path;
		else {
			$path = "uploads/{$this->fileName}"; // try old format
			if(file_exists($path))
				return $path;
		}
		
		return null;
	}
	
	// get a url to a file
	// return null if file doesn't exist
	public function getUrl() {
		if($path = $this->getPath()) // ensure file exists
			return Yii::app()->request->baseUrl . "/$path";
		
		return null;
	}
	
	// return a link to the Media Module view for this file
	public function getMediaLink() {
		return CHtml::link($this->fileName, Yii::app()->controller->createUrl('/media/', array('view'=>$this->id)));
	}
	
	// 
	public function fileExists() {
		if(file_exists("uploads/media/{$this->uploadedBy}/{$this->fileName}")) // try new format
			return true;
		else if(file_exists("uploads/{$this->fileName}")) // try old format
			return true;
		
		return false;
	}
	
	// convert a string (eg '10MB') to bytes
	private static function toBytes($size) {
		$type = strtolower(substr($size, -1)); // last char
		$num = substr($size, 0, -1); // number
		switch($type) {
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
		$max_upload_size /= (1024*1024); // convert bytes to megabytes
		$max_upload_size = round($max_upload_size, 2); // round to two decimal places
		
		return $max_upload_size;
	}
	
	public static function forbiddenFileTypes() {
		return "exe, bat, dmg, js, jar, swf, php, pl, cgi, htaccess, py";
	}
}