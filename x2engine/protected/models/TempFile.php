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
 * This is the model class for table "x2_temp_files".
 *
 * @package application.models
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
		    if(file_exists('uploads/protected/media/temp/'. $oldFolder .'/'. $oldName))
		    	unlink('uploads/protected/media/temp/'. $oldFolder .'/'. $oldName); // delete file
		    if(file_exists('uploads/protected/media/temp/'. $oldFolder))
		    	rmdir('uploads/protected/media/temp/'. $oldFolder); // delete folder
		    $oldTempFile->delete(); // delete database entry tracking temp file
		}
		
		// generate temp folder name
		$folder = substr(md5(rand()), 0, 10);

		// try to create temp folder
		if(!@mkdir('uploads/protected/media/temp/'. $folder, 0777, true))
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
		return 'uploads/protected/media/temp/'. $this->folder .'/'. $this->name;
	}

    public function convertToMedia (array $attributes=array ()) {
        $username = Yii::app()->user->getName();
        $name = $this->name;
        $tempFilename = 'uploads/protected/media/temp/'.$this->folder.'/'.$this->name;
        if (FileUtil::ccopy($tempFilename, "uploads/protected/media/$username/$name")) {
            $model = new Media;
            $model->name = $model->fileName = $name;
            $model->uploadedBy = $username;
            $model->createDate = time ();
            $model->lastUpdated = time ();
            $model->resolveType ();
            $model->resolveSize ();
            $model->setAttributes ($attributes, false);
            if ($model->save ()) return $model;
        }
        return false;
    }
}
