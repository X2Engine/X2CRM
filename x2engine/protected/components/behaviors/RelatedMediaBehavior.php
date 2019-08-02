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
 * Like AssociatedMediaBehavior except that it relates the records via a join table.
 */

class RelatedMediaBehavior extends ActiveRecordBehavior {

    const IMAGE = 1;

    public $fileAttribute;
    public $fileType;

    public function rules () {
        if ($this->fileType === self::IMAGE) {
            return array (
                array (
                    $this->fileAttribute, 'file', 'allowEmpty' => true,
                    'maxSize' => 2000000,
                    'types' => array (
                        'gif', 'jpg', 'jpeg', 'tif', 'tiff', 'bmp', 'png'
                    )
                )
            );
        } else {
            return array ();
        }
    }

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterSave'=>'afterSave',
		));
	}

    /**
     * Save uploaded file and add associated media object
     */
    public function saveAssociatedMedia ($file) {
        if (!($file instanceof CUploadedFile)) return;

        $fileAttribute = $this->fileAttribute;
        $media = new Media;
        $username = Yii::app()->user->getName();
        // file uploaded through form
        $tempName = $file->getTempName ();
        $media->setAttributes (array (
            'uploadedBy' => $username,
            'createDate' => time(),
            'lastUpdated' => time(),
            'fileName' => preg_replace ('/ /', '_', $file->getName ()),
            'mimetype' =>$file->type,
        ), false);
        $media->resolveNameConflicts ();

        if (!$media->save ()) {
            throw new CException (implode (';', $media->getAllErrorMessages ()));
        }

        if (!FileUtil::ccopy(
            $tempName, 
            "uploads/protected/media/$username/{$media->fileName}")) {

            throw new CException ();
        }

        // relate file to event
        $join = new RelationshipsJoin (
            'insert', 'x2_'.lcfirst (get_class ($this->owner)).'_to_media');
        $ownerIdAttrName = lcfirst (get_class ($this->owner)).'Id';
        $join->$ownerIdAttrName = $this->owner->id;
        $join->mediaId = $media->id;
        if (!$join->save ()) {
            throw new CException (implode (';', $join->getAllErrorMessages ()));
        }
    }

    public function afterSave ($evt) {
        // file attribute value is expected to be either an id of old associated media or
        // an instance of CUploadedFile. Can also be an array containing either.
        $fileAttribute = $this->fileAttribute;
        $transaction = Yii::app()->db->getCurrentTransaction ();
        $commit = false;
        if (is_null($transaction)) {
            $transaction = Yii::app()->db->beginTransaction ();
            $commit = true;
        }
        try {
            $files = ArrayUtil::coerceToArray ($this->owner->$fileAttribute); 

            // save uploaded files as associated media
            foreach ($files as $file) {
                $this->saveAssociatedMedia ($file);
            }

            if ($commit)
                $transaction->commit ();
        } catch (CException $e) {
            $transaction->rollback ();
        }

    }

}

?>
