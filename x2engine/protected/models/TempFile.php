<?php
/*********************************************************************************
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
 ********************************************************************************/

/**
 * This is the model class for table "x2_temp_files".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $folder
 * @property string $name
 * @property integer createDate
 */
class TempFile extends CActiveRecord {
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
		return 'x2_temp_files';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
		);
	}

	public function behaviors() {
		return array(
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
	public function attributeLabels() {
		return array(
		);
	}
	
	/*
	 *  Create a temp folder to save a temp file in.
	 *  Create an entry in x2_temp_files to track the file
	 *  Delete any old temp files
	 *
	 *	return TempFile
	 *         or false if failed to create temp folder
	 */
	public static function createTempFile($name) {
	
		// delete old temp files if they exist
		$old = time() - (86400); // 1 day old
		$oldTempFiles = TempFile::model()->findAll("createDate < $old");
		foreach($oldTempFiles as $oldTempFile) {
		    $oldFolder = $oldTempFile->folder;
		    $oldName = $oldTempFile->name;
		    if(file_exists('uploads/media/temp/'. $oldFolder .'/'. $oldName))
		    	unlink('uploads/media/temp/'. $oldFolder .'/'. $oldName); // delete file
		    if(file_exists('uploads/media/temp/'. $oldFolder))
		    	rmdir('uploads/media/temp/'. $oldFolder); // delete folder
		    $oldTempFile->delete(); // delete database entry tracking temp file
		}
		
		// generate temp folder name
		$folder = substr(md5(rand()), 0, 10);

		// try to create temp folder
		if(!@mkdir('uploads/media/temp/'. $folder, 0777, true))
			return false; // couldn't create temp folder
		
		$tempFile = new TempFile; // track temp file in database
		$tempFile->folder = $folder;
		$tempFile->name = $name;
		$tempFile->createDate = time();
		if($tempFile->save())		
			return $tempFile; // TempFile
		else
			return false;
	}
	
	
	/*
	 *	Get the full path including the file name
	 */
	public function fullpath() {
		return 'uploads/media/temp/'. $this->folder .'/'. $this->name;
	}
}