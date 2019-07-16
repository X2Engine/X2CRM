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




class MobileUpdateAction extends MobileAction {

    public $pageClass = 'record-update';
    public $viewFile = 'recordUpdate';
    public $pageDepth = 1;

    public function loadModel ($id) {
        return $this->controller->loadModel ($id);
    }

    public function getRedirectUrl ($model) {
        return $model->getUrl ();
    }

    public function run ($id) {
        parent::beforeRun ();

        $model = $this->loadModel ($id);
        $modelClass = get_class ($model);
        $this->controller->dataUrl = $this->controller->createAbsoluteUrl (
            $this->getId (), array ('id' => $model->id));
        $this->controller->pageId .= '-'.$model->id;

        if ($this->controller->checkPermissions($model, 'edit')) {
            if (isset ($_POST[$modelClass])) {
                if ($model instanceof X2Model) {
                    $model->setX2Fields ($_POST[$modelClass]);
                } else {
                    $model->setAttributes ($_POST[$modelClass]);
                }

                // special case. Shouldn't need to add a fields db record just to get setX2Fields
                // to set an attribute
                if (($model instanceof Topics ||
                     $model instanceof TopicReplies) &&
                    isset ($_POST[get_class ($model)]['upload'])) {

                    $model->upload = $_POST[get_class ($model)]['upload'];
                }

                $this->controller->setFileFields ($model, true);
                if ($model->save ()) {
                    if (isset ($_FILES[get_class ($model)])) {
                        // this is an ajax file upload request
                        echo CJSON::encode (array ( 
                            'redirectUrl' => $this->getRedirectUrl ($model),
                        ));
                        Yii::app()->end ();
                    } else {
                        $this->controller->redirect ($this->getRedirectUrl ($model));
                    }
                } elseif (isset ($_FILES[$modelClass])) {
                    throw new CException (400, Yii::t('app', 'Upload failed'));
                }

            }

            $this->controller->pageClass = $this->pageClass;
            $model->setInputRenderer (
                'application.modules.mobile.components.formatters.MobileFieldInputRenderer');
            $this->controller->render (
                $this->pathAliasBase.'views.mobile.'.$this->viewFile,
                array (
                    'model' => $model,
                )
            );
        } else {
            $this->controller->denied ();
        }
    }

}

?>
