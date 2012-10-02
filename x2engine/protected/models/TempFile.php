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