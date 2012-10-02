<?php

/* * *******************************************************************************
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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */

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

			if($makeLink)
			    $str .= $media->getMediaLink();
			else
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
			
				$media = CActiveRecord::model('MediaChild')->findByPk($matches[1]);
				if(isset($media)) {
					$str = Yii::t('media','File:') . ' ';
					
					$fileExists = $media->fileExists();
					
					if($fileExists == false)
					    return $str . $data[0] . ' ' . Yii::t('media','(deleted)');
					
					if($makeLink)
					    $str .= $media->getMediaLink();
					else
					    $str .= $data[0];
					
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
