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




class MobileTopicsViewAction extends MobileViewAction {

    public $pageClass = 'mobile-view mobile-topics-view';

    public function run ($id) {
        parent::beforeRun ();

        $model = $this->getModel ($id);
        $reply = new TopicReplies;
        if(isset($_POST['TopicReplies'])){
            if (!$this->controller->checkPermissions($model, 'view')) {
                $this->controller->denied();
            }
            $reply->text = $_POST['TopicReplies']['text'];
            $reply->topicId = $model->id;
            $this->controller->setFileFields ($reply, true);

            if ($reply->save()) {
                if (isset ($_FILES['TopicReplies'])) {
                    Yii::app()->end ();
                }
                $reply = new TopicReplies;
            } else {
                if (isset ($_FILES['TopicReplies'])) {
                    throw new CHttpException (400, Yii::t('app', 'Upload failed'));
                }
            }
        }

        $this->controller->pageClass = $this->pageClass;
        $this->controller->dataUrl = $model->getUrl ();
//       AuxLib::debugLogR ('$_GET = ');
//        AuxLib::debugLogR ($_GET);
//
//        if (isset ($_GET['topic-replies_page'])) {
//            AuxLib::debugLogR ('merging');
//            $this->controller->dataUrl .= '/topic-replies_page/' . $_GET['topic-replies_page'];
//           AuxLib::debugLogR ('$this->controller->dataUrl = ');
//            AuxLib::debugLogR ($this->controller->dataUrl);
//
//             
//        }
        $this->controller->pageId .= '-'.$model->id;
        $this->controller->headerTitle = $model->name;

        User::addRecentItem (get_class ($model), $id); 

        $dataProvider = new CArrayDataProvider ($model->replies, array(
            'id' => 'topic-replies',
            'pagination' => array(
                'pageSize'=> Topics::PAGE_SIZE,
            ),
        ));
        $dataProvider->getPagination()->setItemCount($dataProvider->getTotalItemCount());

        $this->controller->render (
            $this->pathAliasBase.'views.mobile.topicsView',
            array (
                'model' => $model,
                'reply' => $reply,
                'dataProvider' => $dataProvider,
            )
        );
    }

}

?>
