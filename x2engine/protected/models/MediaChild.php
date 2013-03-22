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
 * @package X2CRM.models
 * @property integer $id
 * @property string $associationType
 * @property integer $associationId
 * @property string $fileName
 * @property string $uploadedBy
 * @property string $createDate
 */
class MediaChild extends Media {

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
			$media = Media::model()->findByPK($data[1]); // look for an entry in the media table
		
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
			
				$media = X2Model::model('MediaChild')->findByPk($matches[1]);
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
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
	return array(
	    'id' => Yii::t('media', 'ID'),
	    'associationType' => Yii::t('media', 'Association Type'),
	    'associationId' => Yii::t('media', 'Association'),
	    'fileName' => Yii::t('media', 'File Name'),
	    'uploadedBy' => Yii::t('media', 'Uploaded By'),
	    'createDate' => Yii::t('media', 'Create Date'),
	);
    }

}
