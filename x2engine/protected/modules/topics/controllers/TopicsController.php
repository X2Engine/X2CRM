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




class TopicsController extends x2base {

    public $modelClass = 'Topics';

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.'.
                        'MobileTopicsControllerBehavior'
            ),
        ));
    }

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id, $replyId = null, $latest = null) {
        $page = null;
        $model=$this->loadModel($id);
        if(!is_null($latest) && !Yii::app()->request->isAjaxRequest) {
            $replyId = $model->lastPost->id;
        }
        if(!is_null($replyId) && !Yii::app()->request->isAjaxRequest){
            $post = TopicReplies::model()->findByPk($replyId);
            if(!is_null($post)){
                $page = $post->getTopicPage();
            } else {
                $replyId = null;
            }
        }
        $this->noBackdrop = true;
        $topicReply = new TopicReplies;
        if(!isset($_GET['ajax'])){
            $log=new ViewLog;
            $log->user=Yii::app()->user->getName();
            $log->recordType=get_class($model);
            $log->recordId=$model->id;
            $log->timestamp=time();
            $log->save();
            X2Flow::trigger('RecordViewTrigger',array('model'=>$model));
        }        
        $dataProvider = new CArrayDataProvider($model->replies, array(
            'id' => 'topic-replies',
            'pagination' => array(
                'pageSize'=>Topics::PAGE_SIZE,
            ),
        ));
        $dataProvider->getPagination()->setItemCount($dataProvider->getTotalItemCount());
        if(!Yii::app()->request->isAjaxRequest && !is_null($page)){
            $dataProvider->getPagination()->setCurrentPage($page);
        }
        $this->render('view', array(
            'model' => $model,
            'replyId' => $replyId,
            'dataProvider' => $dataProvider,
            'topicReply' => $topicReply,
            'page' => is_null($page) ? $dataProvider->getPagination()->getCurrentPage() : $page,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Topics;
        $fileCreation = false;
        if(isset($_FILES['upload']) || isset($_POST['Topics'])) {
            $data = array();
            $topicText = null;
            if (isset($_FILES['upload'])) {
                $fileCreation = true;
                $data = array('name' => $_POST['topicName']);
                $topicText = $_POST['topicText'];
            } else if (isset($_POST['Topics'])) {
                $data = $_POST['Topics'];
                $topicText = $_POST['TopicReplies']['text'];
            }
            $data['text'] = $topicText;
            $model->setX2Fields($data, false, true);
            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
            } else{
                if ($fileCreation) {
                    // file uploaded through form
                    $temp = CUploadedFile::getInstanceByName('upload'); 
                    $model->upload = $temp;
                }
                if ($model->save()) {
                    if ($fileCreation && count ($model->originalPost->attachments)) {
                        echo $model->id;
                        Yii::app()->end();
                    } else {
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                } elseif ($model->hasErrors ('text')) {
                    Yii::app()->user->setFlash('error','Original post text cannot be blank.');
                }
            }
        }

        if(isset($_POST['x2ajax']) || isset ($_FILES['upload'])){
            $this->renderInlineForm ($model);
        } else {
            $this->render('create',array(
                'model'=>$model,
            ));
        }
    }

    public function renderInlineForm ($model) {
        echo CJSON::encode (
            array (
                'status' => $model->hasErrors () ? 'userError' : 'success',
                'page' => $this->renderPartial ('_topicForm', array (
                    'model' => $model,
                ), true, true)
            ));
    }

    public function actionPinUnpinTopic($id) {
        $model = $this->loadModel($id);
        if(empty($model->sticky)){
            $model->sticky = 1;
        }else{
            $model->sticky = 0;
        }
        if($model->save()){
            echo $model->sticky ? Yii::t('topics', 'Unpin Topic') : Yii::t('topics', 'Pin Topic');
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();

        if(isset($_POST['Topics'])) {
            $data = $_POST['Topics'];
            $data['text'] = $_POST['TopicReplies']['text'];
            $model->setX2Fields($data, false, true);
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            } elseif ($model->hasErrors ('text')) {
                Yii::app()->user->setFlash('error','Original post text cannot be blank.');
            }
        }

        $this->render('update',array(
            'model'=>$model,
            'users'=>$users,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if(Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $model=$this->loadModel($id);
            $this->cleanUpTags($model);
            $model->delete();

            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
               the browser */
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else {
            throw new CHttpException(
                400,'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex($order = null) {
        $model = new Topics('search');
        $orderStr = $model->getOrder($order);
        $dataProvider = new CActiveDataProvider('Topics', array(
            'criteria' => array(
                'select' => 't.*, (SELECT COUNT(id) FROM x2_topic_replies WHERE topicId = t.id) AS replyCount, min(lastPost.createDate) as minCreateDate',
                'with' => array('lastPost'),
                'order' => $orderStr,
                'group' => 'lastPost.topicId',
            ),
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage(),
            )
        ));
        $this->render('index', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'order' => $order,
        ));
    }

    public function actionNewReply(){
        if(isset($_POST['TopicReplies'])){
        $model = $this->loadModel($_POST['TopicReplies']['topicId']);
            if (!$this->checkPermissions($model, 'view')) {
                $this->denied();
            }
            $reply = new TopicReplies;
            $reply->text = $_POST['TopicReplies']['text'];
            $reply->topicId = $_POST['TopicReplies']['topicId'];
            if ($reply->save()) {
                echo $reply->id;
            }
        }
    }
    
    public function actionUpdateReply($id){
        $reply = TopicReplies::model()->findByPk($id);
        if (is_null($reply)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        if(!$this->checkPermissions($reply, 'edit')){
            $this->denied();
        }
        $topicId = $reply->topicId;
        $topic = $this->loadModel($topicId);
        if (isset($_POST['TopicReplies'])) {
            $reply->text = $_POST['TopicReplies']['text'];
            if ($reply->save()) {
                $this->redirect(array('/topics/topics/view', 'id' => $reply->topicId, 'replyId' => $reply->id));
            }
        }
        $this->render('updateReply', array(
            'topic' => $topic,
            'model' => $reply,
        ));
    }
    
    public function actionDeleteReply($id){
        if(Yii::app()->request->isPostRequest){
            $reply = TopicReplies::model()->findByPk($id);
            if (is_null($reply)) {
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
            }
            if(!$this->checkPermissions($reply, 'delete') || !$reply->isDeletable()){
                $this->denied();
            } 
            $reply->delete();
        }else{
            throw new CHttpException(
                400,'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='topics-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
    
    /**
     * Create a menu for Topics
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Topics = Modules::displayName();
        $Topic = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('topics','{topics} List', array(
                    '{topics}'=>$Topics,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('topics','Create {topic}', array(
                    '{topic}'=>$Topic,
                )),
                'url'=>array('create')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('topics','View {topic}', array(
                    '{topic}'=>$Topic,
                )),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('topics','Edit {topic}', array(
                    '{topic}'=>$Topic,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('topics','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
        );
        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
