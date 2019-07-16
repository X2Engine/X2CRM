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




class MobileActiveForm extends X2ActiveForm {

    public $JSClass = 'MobileActiveForm'; 
    public $photoAttrName = null; 
    public $audioAttrName = null; 
    public $videoAttrName = null; 
    public $locationPhotoAttrName = null;
    public $redirectUrl = null; 

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'MobileActiveFormJS' => array(
                'baseUrl' => Yii::app()->controller->assetsUrl,
                'js' => array(
                    'js/MobileActiveForm.js',
                ),
                'depends' => array ('X2FormJS'),
            ),
        ));
    }

    public function getJSClassParams () {
        return array_merge (parent::getJSClassParams (), array (
            'photoAttrName' => $this->photoAttrName,
            'locationPhotoAttrName' => $this->locationPhotoAttrName,
            'audioAttrName' => $this->audioAttrName,
            'videoAttrName' => $this->videoAttrName,
            'translations' => ".CJSON::encode (array (
                'Upload failed' => Yii::t('app','Upload failed'),
            )).",
            'redirectUrl' => $this->redirectUrl ? $this->redirectUrl : AuxLib::getRequestUrl (),
        ));
    }

    public function init () {
        if (!$this->action && Yii::app()->params->isPhoneGap) {
            $this->action = AuxLib::getRequestUrl ();
        }
        return parent::init ();
    }

    public function photoAttachment (CModel $model, $attr, Media $media) {
        static $count = 0;
        $count++;
        $html = '';
        $html .= CHtml::openTag ('div', array (
            'class' => 'photo-attachment-container',
        ));
        $html .= $media->getImage (false, array (
            'class' => 'photo-attachment dummy-attachment',
        ));
        $html .= CHtml::tag ('div', array (
            'class' => 'remove-attachment-button',
            'id' => 'remove-attachment-button-'.$count,
        ), 
            X2Html::fa ('circle').
            X2Html::fa ('times-circle-o')
        );
        $html .= $this->hiddenField ($model, $attr.'[]', array (
            'value' => $media->id
        ));
        
        $html .= CHtml::closeTag ('div');

        Yii::app()->clientScript->registerScript('MobileActiveForm::photoAttachment'.$count,"
            $('#remove-attachment-button-$count').click (function () {
                $(this).parent ().remove ();
            });
        ");

        return $html;
    }

    public function photoAttachmentsContainer (
        CModel $model, $attr, $uploadAttr, array $htmlOptions=array ()) {

        $htmlOptions = X2Html::mergeHtmlOptions (array (
            'class' => 'photo-attachments-container',
        ), $htmlOptions);

        $attachmentTags = array ();
        foreach ($model->$attr as $attachment) {
            $attachmentTags[] = $this->photoAttachment ($model, $uploadAttr, $attachment);
        }

        return CHtml::tag ('div', $htmlOptions, implode ("\n", $attachmentTags));
    }

    public function photoAttachmentButton () {
        $html = ''; 
        $html .= "<div class='photo-attach-button icon-button'>".
            X2Html::fa ('camera').
        '</div>';
        return $html;
    }

    public function mobileCoordinates() {
        return '<input type="hidden" name="geoCoords" id="geoCoords">';
    }
   
    public function mobileLocationCoordinates() {
        return '<input type="hidden" name="geoLocationCoords" id="geoLocationCoords">';
    }
}

?>
