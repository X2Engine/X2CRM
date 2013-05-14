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
 * Primary/default controller for the web application.
 *
 * @package X2CRM.controllers
 */
class SiteController extends x2base {

    // Declares class-based actions.
    //public $layout = '//layouts/main';

    public $portlets = array();

    public function filters(){
        return array(
            'setPortlets',
            'accessControl',
        );
    }

    protected function beforeAction($action = null){
        return true;
    }

    public function accessRules(){
        return array(
            array('allow',
                'actions' => array('login', 'index', 'logout', 'warning', 'captcha', 'googleLogin', 'error'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('groupChat', 'newMessage', 'getMessages', 'checkNotifications', 'updateNotes', 'addPersonalNote',
                    'getNotes', 'getURLs', 'addSite', 'deleteMessage', 'fullscreen', 'pageOpacity', 'widgetState', 'widgetOrder', 'saveGridviewSettings', 'saveFormSettings',
                    'saveWidgetHeight', 'inlineEmail', 'tmpUpload', 'upload', 'uploadProfilePicture', 'index', 'contact',
                    'viewNotifications', 'inlineEmail', 'toggleShowTags', 'appendTag', 'removeTag', 'addRelationship', 'createRecords',
                    'whatsNew', 'toggleVisibility', 'page', 'showWidget', 'hideWidget', 'reorderWidgets', 'minimizeWidget', 'publishPost', 'getEvents', 'loadComments',
                    'loadPosts', 'addComment', 'flagPost', 'sendErrorReport', 'minimizePosts', 'bugReport', 'deleteRelationship', 'toggleFeedControls', 'toggleFeedFilters', 'getTip', 'share', 'activityFeedOrder', 'likePost', 'loadLikeHistory'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('motd', 'stickyPost'),
                'users' => array('admin'),
            ),
            array('deny',
                'users' => array('*')
            )
        );
    }

    public function actions(){
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
                'testLimit' => 1,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
        );
    }

//	/**
//	 * Obtain the widget list for the current web user.
//	 *
//	 * @param CFilterChain $filterChain
//	 */
//	public function filterSetPortletsq($filterChain){
//		if(!Yii::app()->user->isGuest){
//			$this->portlets=array();
//			$this->portlets = ProfileChild::getWidgets();
//			// $this->portlets=array();
//			// $arr=ProfileChild::getWidgets(Yii::app()->user->getId());
//
//			// foreach($arr as $key=>$value){
//				// $config=ProfileChild::parseWidget($value,$key);
//				// $this->portlets[$key]=$config;
//			// }
//		}
//		$filterChain->run();
//	}

    public function actionSendErrorReport(){
        if(isset($_POST['report'])){
            $errorReport = $_POST['report'];
            if(isset($_POST['email'])){
                $errorReport = unserialize(base64_decode($_POST['report']));
                $errorReport['email'] = $_POST['email'];
                $errorReport = base64_encode(serialize($errorReport));
            }
            if(isset($_POST['bugDescription'])){
                $errorReport = unserialize(base64_decode($_POST['report']));
                $errorReport['bugDescription'] = $_POST['bugDescription'];
                $errorReport = base64_encode(serialize($errorReport));
            }
            $ccUrl = "http://www.x2software.com/receiveErrorReport.php";
            $ccSession = curl_init($ccUrl);
            curl_setopt($ccSession, CURLOPT_POST, 1);
            curl_setopt($ccSession, CURLOPT_HTTPHEADER, array('Accept-Charset: UTF-8;'));
            curl_setopt($ccSession, CURLOPT_POSTFIELDS, array('errorReport' => $errorReport));
            curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
            $ccResult = curl_exec($ccSession);
            curl_close($ccSession);
        }
    }

    /**
     * Default landing page action for the web application.
     *
     * Displays a feed of new records that have been created since the last
     * login of the current web user.
     */
    public function actionWhatsNew(){

        if(!Yii::app()->user->isGuest){
            if(!Yii::app()->request->isAjaxRequest){
                $_SESSION['lastDate'] = 0;
                unset($_SESSION['lastEventId']);
            }

            $dateRange = Yii::app()->params->admin->eventDeletionTime;
            if(!empty($dateRange)){
                $dateRange = $dateRange * 24 * 60 * 60;
                $deletionTypes = json_decode(Yii::app()->params->admin->eventDeletionTypes, true);
                if(!empty($deletionTypes)){
                    $deletionTypes = "('".implode("','", $deletionTypes)."')";
                    $time = time() - $dateRange;
                    X2Model::model('Events')->deleteAll('lastUpdated < '.$time.' AND type IN '.$deletionTypes);
                }
            }
            unset($_SESSION['feed-condition']);
            if(!isset($_GET['filters'])){
                unset($_SESSION['filters']);
            }
            if(isset(Yii::app()->params->profile->defaultFeedFilters)){
                $_SESSION['filters'] = json_decode(Yii::app()->params->profile->defaultFeedFilters, true);
            }
            if((isset($_GET['filters']) && $_GET['filters']) || isset($_SESSION['filters'])){
                if(isset($_GET['filters'])){
                    unset($_SESSION['filters']);
                    $filters = $_GET;
                }else{
                    $filters = $_SESSION['filters'];

                    function implodeFilters($n){
                        return implode(",", $n);
                    }

                    $func = "implodeFilters";
                    $filters = array_map($func, $filters);
                    $filters['default'] = false;
                }
                unset($filters['filters']);
                $visibility = $filters['visibility'];
                $visibility = str_replace('Public', '1', $visibility);
                $visibility = str_replace('Private', '0', $visibility);
                $visibilityFilter = explode(",", $visibility);
                if(!Yii::app()->user->checkAccess('AdminIndex')){
                    $visibilityCondition = " AND (associationId=".Yii::app()->user->getId()." OR user='".Yii::app()->user->getName()."' OR visibility=1)";
                }else{
                    $visibilityCondition = "";
                }
                if($visibility != ""){
                    $visibilityCondition.=" AND visibility NOT IN (".$visibility.")";
                }else{
                    $visibilityFilter = array();
                }

                $users = $filters['users'];
                if($users != ""){
                    $users = explode(",", $users);
                    $userFilter = $users;
                    $users = '"'.implode('","', $users).'"';
                    if($users != "")
                        $userCondition = " AND (user NOT IN (".$users.")";
                    else
                        $userCondition = "(";
                    if(strpos($users, 'Anyone') === false){
                        $userCondition.=" OR user IS NULL)";
                    }else{
                        $userCondition.=")";
                    }
                }else{
                    $userCondition = "";
                    $userFilter = array();
                }

                $types = $filters['types'];
                if($types != ""){
                    $types = explode(",", $types);
                    $typeFilter = $types;
                    $types = '"'.implode('","', $types).'"';
                    $typeCondition = " AND (type NOT IN (".$types.") OR important=1)";
                }else{
                    $typeCondition = "";
                    $typeFilter = array();
                }
                $subtypes = $filters['subtypes'];
                if(strpos($types, "feed") === false && $subtypes != ""){
                    $subtypes = explode(",", $subtypes);
                    $subtypeFilter = $subtypes;
                    $subtypes = '"'.implode('","', $subtypes).'"';
                    if($subtypes != "")
                        $subtypeCondition = " AND (type!='feed' OR subtype NOT IN (".$subtypes.") OR important=1)";
                    else
                        $subtypeCondition = "";
                }else{
                    $subtypeCondition = "";
                    $subtypeFilter = array();
                }
                $default = $filters['default'];
                $_SESSION['filters'] = array(
                    'visibility' => $visibilityFilter,
                    'users' => $userFilter,
                    'types' => $typeFilter,
                    'subtypes' => $subtypeFilter
                );
                if($default == 'true'){
                    Yii::app()->params->profile->defaultFeedFilters = json_encode($_SESSION['filters']);
                    Yii::app()->params->profile->save();
                }
                $condition = "type!='comment' AND (type!='action_reminder' OR user='".Yii::app()->user->getName()."') AND (type!='notif' OR user='".Yii::app()->user->getName()."')".$visibilityCondition.$userCondition.$typeCondition.$subtypeCondition;
                $_SESSION['feed-condition'] = $condition;
            }else{
                $condition = "type!='comment' AND (type!='action_reminder' OR user='".Yii::app()->user->getName()."') AND (type!='notif' OR user='".Yii::app()->user->getName()."') AND (visibility=1 OR user='".Yii::app()->user->getName()."' OR associationId='".Yii::app()->user->getId()."')";
            }
            $condition.= " AND timestamp <= ".time();
            if(!isset($_SESSION['lastEventId'])){
                $lastId = Yii::app()->db->createCommand()
                        ->select('MAX(id)')
                        ->from('x2_events')
                        ->where($condition)
                        ->order('timestamp DESC, id DESC')
                        ->limit(1)
                        ->queryScalar();
                $_SESSION['lastEventId'] = $lastId;
            }else{
                $lastId = $_SESSION['lastEventId'];
            }
            $lastTimestamp = Yii::app()->db->createCommand()
                    ->select('MAX(timestamp)')
                    ->from('x2_events')
                    ->where($condition)
                    ->order('timestamp DESC, id DESC')
                    ->limit(1)
                    ->queryScalar();
            if(empty($lastTimestamp)){
                $lastTimestamp = 0;
            }
            if(isset($_SESSION['lastEventId'])){
                $condition.=" AND id <= ".$_SESSION['lastEventId']." AND sticky = 0";
            }
            $dataProvider = new CActiveDataProvider('Events', array(
                        'criteria' => array(
                            'condition' => $condition,
                            'order' => 'timestamp DESC, id DESC',
                        ),
                        'pagination' => array(
                            'pageSize' => 20
                        ),
                    ));
            $data = $dataProvider->getData();
            if(isset($data[count($data) - 1]))
                $firstId = $data[count($data) - 1]->id;
            else
                $firstId = 0;
            $users = User::getUserIds();
            $_SESSION['firstFlag'] = true;
            $stickyDataProvider = new CActiveDataProvider('Events', array(
                        'criteria' => array(
                            'condition' => 'sticky=1',
                            'order' => 'timestamp DESC, id DESC',
                        ),
                        'pagination' => array(
                            'pageSize' => 20
                        ),
                    ));
            $_SESSION['stickyFlag'] = false;
            $this->render('whatsNew', array(
                'dataProvider' => $dataProvider,
                'users' => $users,
                'lastEventId' => !empty($lastId) ? $lastId : 0,
                'firstEventId' => !empty($firstId) ? $firstId : 0,
                'lastTimestamp' => $lastTimestamp,
                'stickyDataProvider' => $stickyDataProvider,
            ));
        }else{
            $this->redirect('login');
        }
    }

    public function actionGetEvents($lastEventId, $lastTimestamp){

        $result = Events::getEvents($lastEventId, $lastTimestamp);
        $events = $result['events'];
        $eventData = "";
        $newLastEventId = $lastEventId;
        $newLastTimestamp = $lastTimestamp;
        foreach($events as $event){
            if($event instanceof Events){
                if($event->id > $newLastEventId){
                    $newLastEventId = $event->id;
                }
                if($event->timestamp > $newLastTimestamp){
                    $newLastTimestamp = $event->timestamp;
                }
                $eventData.=$this->renderPartial('application.views.site._viewEvent', array('data' => $event, 'noDateBreak' => true), true);
            }
        }
        $commentCriteria = new CDbCriteria();
        $condition = "type='comment' AND timestamp <=".time()." AND id > ".$lastEventId;
        $parameters = array('order' => 'id ASC');
        $parameters['condition'] = $condition;
        $commentCriteria->scopes = array('findAll' => array($parameters));
        $comments = X2Model::model('Events')->findAll($commentCriteria);
        $commentCounts = array();
        $lastCommentId = $lastEventId;
        foreach($comments as $comment){
            $parentPost = X2Model::model('Events')->findByPk($comment->associationId);
            if(isset($parentPost) && !isset($commentCounts[$parentPost->id])){
                $commentCounts[$parentPost->id] = count($parentPost->children);
            }
            $lastCommentId = $comment->id;
        }

        echo CJSON::encode(array(
            $newLastEventId,
            $newLastEventId != $lastEventId ? $eventData : '',
            $commentCounts,
            $lastCommentId,
            $newLastTimestamp,
        ));
    }

    public function actionAddComment(){
        if(isset($_POST['id']) && isset($_POST['text']) && $_POST['text'] != ''){
            $id = $_POST['id'];
            $comment = $_POST['text'];
            $postModel = Events::model()->findByPk($id);

            if($postModel === null)
                throw new CHttpException(404, Yii::t('app', 'The requested post does not exist.'));

            $commentModel = new Events;
            $commentModel->text = $comment;
            $commentModel->user = Yii::app()->user->name;
            $commentModel->type = 'comment';
            $commentModel->associationId = $postModel->id;
            $commentModel->associationType = 'Events';
            $commentModel->timestamp = time();

            if($commentModel->save()){
                $commentCount = X2Model::model('Events')->countByAttributes(array(
                    'type' => 'comment',
                    'associationType' => 'Events',
                    'associationId' => $postModel->id,
                        ));
                $postModel->lastUpdated = time();
                $postModel->save();

                $profileUser = Yii::app()->db->createCommand()
                        ->select('username')
                        ->from('x2_users')
                        ->where('id=:id', array(':id' => $postModel->associationId))
                        ->queryScalar();


                // notify the owner of the feed containing the post you commented on (unless that person is you)
                if($postModel->associationId != Yii::app()->user->getId()){
                    $postNotif = new Notification;
                    $postNotif->type = 'social_comment';
                    $postNotif->createdBy = $commentModel->user;
                    $postNotif->modelType = 'Profile';
                    $postNotif->modelId = $postModel->associationId;

                    // look up the username of the owner of the feed
                    $postNotif->user = $profileUser;

                    $postNotif->createDate = time();
                    $postNotif->save();
                }
                // now notify the person whose post you commented on (unless they're the same person as the first notification)
                if($profileUser != $postModel->user && $postModel->user != Yii::app()->user->name){
                    $commentNotif = new Notification;
                    $commentNotif->type = 'social_comment';
                    $commentNotif->createdBy = $commentModel->user;
                    $commentNotif->modelType = 'Profile';
                    $commentNotif->modelId = $postModel->associationId;

                    $commentNotif->user = $postModel->user;

                    $commentNotif->createDate = time();
                    $commentNotif->save();
                }
            }
            echo $commentCount;
        }else{
            echo "";
        }
    }

    public function actionPublishPost(){
        $post = new Events;
        // $user = $this->loadModel($id);
        if(isset($_POST['text']) && $_POST['text'] != Yii::t('app', 'Enter text here...')){
            $post->text = $_POST['text'];
            $post->visibility = $_POST['visibility'];
            if(isset($_POST['associationId']))
                $post->associationId = $_POST['associationId'];
            //$soc->attributes = $_POST['Social'];
            //die(var_dump($_POST['Social']));
            $post->user = Yii::app()->user->getName();
            $post->type = 'feed';
            $post->subtype = $_POST['subtype'];
            $post->lastUpdated = time();
            $post->timestamp = time();
            if($post->save()){
                if(!empty($post->associationId) && $post->associationId != Yii::app()->user->getId()){

                    $notif = new Notification;

                    $notif->type = 'social_post';
                    $notif->createdBy = $post->user;
                    $notif->modelType = 'Profile';
                    $notif->modelId = $post->associationId;

                    $notif->user = Yii::app()->db->createCommand()
                            ->select('username')
                            ->from('x2_users')
                            ->where('id=:id', array(':id' => $post->associationId))
                            ->queryScalar();

                    // $prof = X2Model::model('ProfileChild')->findByAttributes(array('username'=>$post->user));
                    // $notif->text = "$prof->fullName posted on your profile.";
                    // $notif->record = "profile:$prof->id";
                    // $notif->viewed = 0;
                    $notif->createDate = time();
                    // $subject=X2Model::model('ProfileChild')->findByPk($id);
                    // $notif->user = $subject->username;
                    $notif->save();
                }
            }
        }
    }

    /*
      Used for both like and unlike buttons. If the user has alread liked the
      post, the post will be unliked and visa versa. The function returns a string
      indicating whether the post was liked or unliked.
      Parameter:
      $id - the user's id
     */

    public function actionLikePost($id){
        $userId = Yii::app()->user->id;

        $likedPost = Yii::app()->db->createCommand()
                ->select('count(userId)')
                ->from('x2_like_to_post')
                ->where('userId=:userId and postId=:postId', array(':userId' => Yii::app()->user->id, ':postId' => $id))
                ->queryScalar();

        if(!$likedPost){
            Yii::app()->db->createCommand()
                    ->insert('x2_like_to_post', array('userId' => $userId, 'postId' => $id));
            echo 'liked post';
        }else{
            Yii::app()->db->createCommand()
                    ->delete('x2_like_to_post', 'userId=:userId and postId=:postId', array('userId' => $userId, 'postId' => $id));
            echo 'unliked post';
        }
    }

    /*
      Returns an array of links to the user profiles of users who have liked the
      post.
      Parameter:
      $id - the id of the post
     */

    public function actionLoadLikeHistory($id){
        $likeHistory = Yii::app()->db->createCommand()
                ->select('concat (firstName, " ", lastName), usrs.id')
                ->from('x2_like_to_post as likes, x2_users as usrs')
                ->where('likes.userId=usrs.id and likes.postId=:postId', array('postId' => $id))
                ->queryAll();

        $likeHistoryLinks = array();
        foreach($likeHistory as $like){
            $likeHistoryLinks[] = CHtml::link($like['concat (firstName, " ", lastName)'], array('/profile/'.$like['id']));
        }

        echo CJSON::encode($likeHistoryLinks);
    }

    public function actionLoadComments($id){
        $commentDataProvider = new CActiveDataProvider('Events', array(
                    'criteria' => array(
                        'order' => 'timestamp ASC',
                        'condition' => "type='comment' AND associationType='Events' AND associationId=$id",
                        )));
        $this->widget('zii.widgets.CListView', array(
            'dataProvider' => $commentDataProvider,
            'itemView' => '../social/_view',
            'template' => '&nbsp;{items}',
            'id' => $id.'-comments',
        ));
    }

    public function actionFlagPost(){
        if(isset($_GET['id']) && isset($_GET['attr'])){
            $id = $_GET['id'];
            $important = $_GET['attr'];
            $event = X2Model::model('Events')->findByPk($id);
            if(isset($event)){
                if(isset($_GET['color']) && !empty($_GET['color'])){
                    $event->color = $_GET['color'];
                }else{
                    $event->color = null;
                }
                if(isset($_GET['fontColor']) && !empty($_GET['fontColor'])){
                    $event->fontColor = $_GET['fontColor'];
                }else{
                    $event->fontColor = null;
                }
                if(isset($_GET['linkColor']) && !empty($_GET['linkColor'])){
                    $event->linkColor = $_GET['linkColor'];
                }else{
                    $event->linkColor = null;
                }
                if($important == 'important'){
                    $event->important = 1;
                }else{
                    $event->important = 0;
                }
                if($event->save()){
                    if(isset($_GET['email']) && $_GET['email'] == 'checked' && $event->important){
                        $subject = "Event Broadcast";
                        $phpMail = $this->getPhpMailer();
                        $fromEmail = Yii::app()->params->profile->emailAddress;
                        $fromName = Yii::app()->params->profile->fullName;
                        $phpMail->AddReplyTo($fromEmail, $fromName);
                        $phpMail->SetFrom($fromEmail, $fromName);
                        $phpMail->Subject = $subject;
                        $phpMail->MsgHTML("$fromName has broadcast an event on your X2CRM Activity Feed:<br><br>".$event->getText()."<br><br>Link to activity feed:<br><br>");
                        $users = Profile::model()->findAllByAttributes(array('status' => 1));
                        foreach($users as $user){
                            $phpMail->AddAddress($user->emailAddress, $user->fullName);
                        }
                        if($phpMail->Send()){
                            printR($phpMail);
                        }else{
                            echo $phpMail->ErrorInfo;
                        }
                    }
                }
            }
        }
    }

    public function actionStickyPost($id){
        $event = X2Model::model('Events')->findByPk($id);
        if(isset($event)){
            $event->sticky = !$event->sticky;
            $event->update(array('sticky'));
        }
        echo (date("M j", time()) == date("M j", $event->timestamp) ? Yii::t('app', "Today") : Yii::app()->locale->dateFormatter->formatDateTime($event->timestamp, 'long', null));
    }

    public function actionActivityFeedOrder(){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $profile->activityFeedOrder = !$profile->activityFeedOrder;
            $profile->update(array('activityFeedOrder'));
        }
    }

    public function actionGetTip(){
        //opensource or pro
        $edition = yii::app()->params->admin->edition;
        //True or False
        $admin = Yii::app()->user->checkAccess('AdminIndex');
        //Check user type and editon to deliever an appropriate tip
        if($edition == 'pro'){
            if($admin){
                $where = 'edition = "pro" OR edition = "opensource"';
            }else{
                $where = 'admin = 0';
            }
        }else if($admin){
            $where = 'edition = "opensource"';
        }else{
            $where = 'admin = 0 AND edition = "opensource"';
        }
        $tip = Yii::app()->db->createCommand()
                ->select('*')
                ->from('x2_tips')
                ->where($where)
                ->order('rand()')
                ->queryRow();
        echo json_encode($tip);
    }

    public function actionToggleFeedControls(){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $profile->fullFeedControls = !$profile->fullFeedControls;
            $profile->update(array('fullFeedControls'));
        }
    }

    public function actionToggleFeedFilters($filter){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $filters = json_decode($profile->feedFilters, true);
            if(isset($filters[$filter])){
                $filters[$filter] = $filters[$filter] == 1 ? 0 : 1;
            }else{
                $filters[$filter] = 0;
            }
            $flag = $filters[$filter];
            $profile->feedFilters = json_encode($filters);
            $profile->update(array('feedFilters'));
            echo $flag;
        }
    }

    public function actionMinimizePosts(){
        if(isset($_GET['minimize'])){
            $profile = Yii::app()->params->profile;
            if($_GET['minimize'] == 'minimize'){
                $profile->minimizeFeed = 1;
            }else{
                $profile->minimizeFeed = 0;
            }
            echo $_GET['minimize'] == true;
            $profile->save();
        }
    }

    /**
     * Displays message of the day.
     */
    public function actionMotd(){
        if(isset($_POST['message'])){
            $motd = $_POST['message'];
            $temp = Social::model()->findByAttributes(array('type' => 'motd'));
            $temp->data = $motd;
            if($temp->update())
                echo $motd;
            else
                echo "An error has occured.";
        }else{
            echo "An error has occured.";
        }
    }

    /**
     * Renders the group chat.
     */
    public function actionGroupChat(){
        $this->portlets = array();
        $this->layout = '//layouts/column1';
        //$portlets = $this->portlets;
        // display full screen group chat
        $this->render('groupChat');
    }

    /**
     * Creates a new chat message from the current web user.
     */
    public function actionNewMessage(){
        if(isset($_POST['chat-message']) && $_POST['chat-message'] != ''
                && $_POST['chat-message'] != Yii::t('app', 'Enter text here...')){

            $user = Yii::app()->user->getName();
            $chat = new Social;
            $chat->data = $_POST['chat-message'];
            ;
            $chat->user = $user;
            $chat->visibility = 1;
            $chat->timestamp = time();
            $chat->type = 'chat';

            if($chat->save()){
                echo CJSON::encode(array(
                    array(
                        $chat->id,
                        date('g:i:s A', $chat->timestamp),
                        '<span class="my-username">'.$chat->user.'</span>',
                        $this->convertUrls($chat->data)
                    )
                ));
            }
        }
    }

    /**
     * Add a personal note to the list of notes for the current web user.
     */
    public function actionAddPersonalNote(){
        if(isset($_POST['note-message']) && $_POST['note-message'] != ''){
            $user = Yii::app()->user->getName();
            $note = new Social;
            $note->associationId = Yii::app()->user->getId();
            $note->data = $_POST['note-message'];
            ;
            $note->user = $user;
            $note->visibility = 1;
            $note->timestamp = time();
            $note->type = 'note';

            if($note->save()){
                echo "1";
            }
        }
    }

    /**
     * Adds a new URL
     */
    public function actionAddSite(){
        if((isset($_POST['url-title']) && isset($_POST['url-url'])) && ($_POST['url-title'] != '' && $_POST['url-url'] != '')){
            $site = new URL;
            $site->title = $_POST['url-title'];
            $site->url = $_POST['url-url'];
            $site->userid = Yii::app()->user->getId();
            $site->timestamp = time();
            if($site->save()){
                echo '1';
            }
        }
    }

    /**
     * Obtains notes for displaying within the notes widget.
     * @param string $url The deletion URL for notes
     */
    public function actionGetNotes($url){
        $content = Social::model()->findAllByAttributes(array('type' => 'note', 'associationId' => Yii::app()->user->getId()), array(
            'order' => 'timestamp DESC',
                ));
        $res = "";
        foreach($content as $item){
            $res .= $this->convertUrls($item->data)." ".CHtml::link('[x]', array('site/deleteMessage', 'id' => $item->id, 'url' => $url)).'<br /><br />';
        }
        if($res == ""){
            $res = Yii::t('app', "Feel free to enter some notes!");
        }
        echo $res;
    }

    /**
     * Gets URLs for "top sites"
     * @param string $url
     */
    public function actionGetURLs($url){
        $content = URL::model()->findAllByAttributes(array('userid' => Yii::app()->user->getId()), array(
            'order' => 'timestamp DESC',
                ));
        $res = '<table><tr><th>Title</th><th>Link</th></tr>';
        if($content){
            foreach($content as $entry){
                if(strpos($entry->url, 'http://') === false){
                    $entry->url = "http://".$entry->url;
                }
                $res .= '<tr><td>'.$entry->title."</td><td>".CHtml::link(Yii::t('app', 'Link'), $entry->url)."</td></tr>";
            }
        }else{
            $res .= "<tr><td>Example</td><td><a href='.'>LINK</a></td></tr>";
        }
        echo $res;
    }

    /**
     * Delete a message from the social feed.
     * @param integer $id
     * @param string $url
     */
    public function actionDeleteMessage($id, $url){
        $note = Social::model()->findByPk($id);
        if(isset($note))
            $note->delete();
        $this->redirect($url);
    }

    /**
     * Sets "Fullscreen" mode for the current web user / session
     */
    public function actionFullscreen(){
        Yii::app()->session['fullscreen'] = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile = Yii::app()->params->profile;
        $profile->fullscreen = (isset($_GET['fs']) && $_GET['fs'] == 1);
        $profile->save();
        // echo var_dump(Yii::app()->session['fullscreen']);
        echo 'Success';
    }

    public function actionDeleteRelationship($id){
        $rel = X2Model::model('Relationships')->findByPk($id);
        if(isset($rel)){
            $rel->delete();
        }
        if(isset($_GET['redirect'])){
            $this->redirect($this->createUrl($_GET['redirect']));
        }
    }

    /**
     * Sets the page opacity for the current web user.
     */
    public function actionPageOpacity(){
        if(isset($_GET['opacity']) && is_numeric($_GET['opacity'])){

            $opacity = $_GET['opacity'];
            if($opacity > 1)
                $opacity = 1;
            if($opacity < 0.1)
                $opacity = 0.1;

            $opacity = round(100 * $opacity);

            // $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());

            Yii::app()->params->profile->pageOpacity = $opacity;
            if(Yii::app()->params->profile->save()){
                echo "success";
            }
        }
    }

    /**
     * Checks for the widget's state.
     */
    public function actionWidgetState(){

        if(isset($_GET['widget']) && isset($_GET['state'])){
            $widgetName = $_GET['widget'];
            $widgetState = ($_GET['state'] == 0) ? '0' : '1';

            // $profile = Yii::app()->params->profile;

            $order = explode(":", Yii::app()->params->profile->widgetOrder);
            $visibility = explode(":", Yii::app()->params->profile->widgets);

            // var_dump($order);
            // var_dump($visibility);
            if(array_key_exists($widgetName, Yii::app()->params->registeredWidgets)){

                $pos = array_search($widgetName, $order);
                $visibility[$pos] = $widgetState;
                // die(var_dump($visibility));

                Yii::app()->params->profile->widgets = implode(':', $visibility);

                if(Yii::app()->params->profile->save()){
                    echo 'success';
                }
            }
        }
    }

    /**
     * Responds with the order of widgets for the current user.
     */
    public function actionWidgetOrder(){
        if(isset($_POST['widget'])){

            $widgetList = $_POST['widget'];

            // $profile = Yii::app()->params->profile;
            $order = Yii::app()->params->profile->widgetOrder;
            $visibility = Yii::app()->params->profile->widgets;

            $order = explode(":", $order);
            $visibility = explode(":", $visibility);

            $newOrder = array();

            foreach($widgetList as $item){
                if(array_key_exists($item, Yii::app()->params->registeredWidgets))
                    $newOrder[] = $item;
            }
            $str = "";
            $visStr = "";
            foreach($newOrder as $item){
                $pos = array_search($item, $order);
                $vis = $visibility[$pos];
                $str.=$item.":";
                $visStr.=$vis.":";
            }
            $str = substr($str, 0, -1);
            $visStr = substr($visStr, 0, -1);

            Yii::app()->params->profile->widgetOrder = $str;
            Yii::app()->params->profile->widgets = $visStr;

            if(Yii::app()->params->profile->save()){
                echo 'success';
            }
        }
    }

    /**
     * Save custom gridview settings.
     *
     * Saves the settings (i.e. columns, column position and column width)
     * for the X2GridView model.
     */
    public function actionSaveGridviewSettings(){

        $result = false;
        if(isset($_GET['gvSettings']) && isset($_GET['viewName'])){
            $gvSettings = json_decode($_GET['gvSettings'], true);

            if(isset($gvSettings))
                $result = ProfileChild::setGridviewSettings($gvSettings, $_GET['viewName']);
        }
        if($result)
            echo '200 Success';
        else
            echo '400 Failure';
    }

    /**
     * Save settings for a custom form layout.
     *
     * @throws CHttpException
     */
    public function actionSaveFormSettings(){
        $result = false;
        if(isset($_GET['formSettings']) && isset($_GET['formName'])){
            $formSettings = json_decode($_GET['formSettings'], true);

            if(isset($formSettings))
                $result = ProfileChild::setFormSettings($formSettings, $_GET['formName']);
        }
        if($result)
            echo 'success';
        else
            throw new CHttpException(400, 'Invalid request. Probabaly something wrong with the JSON string.');
    }

    /**
     * Saves the height of a widget.
     */
    public function actionSaveWidgetHeight(){
        if(isset($_POST['Widget']) && isset($_POST['Height'])){
            $heights = $_POST['Height'];
            $widget = $_POST['Widget'];
            $widgetSettings = ProfileChild::getWidgetSettings();

            foreach($heights as $key => $height){
                $widgetSettings->$widget->$key = intval($height);
            }

            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update();
        }
    }

    /**
     * Uploads a file to a temporary folder.
     *
     * Upload a file to a temp folder, which will presumably be deleted shortly thereafter
     * Temp files are stored in a temp folder with a randomly generated name. They are stored
     * in 'uploads/media/temp'
     */
    public function actionTmpUpload(){
        if(isset($_FILES['upload'])){
            $upload = CUploadedFile::getInstanceByName('upload');

            if($upload){

                $name = $upload->getName();
                $name = str_replace(' ', '_', $name);

                $temp = TempFile::createTempFile($name);

                if($temp && $upload->saveAs($temp->fullpath())) // temp file saved
                    echo json_encode(array('status' => 'success', 'id' => $temp->id, 'name' => $name));
                else
                    echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
            } else{
                echo json_encode(array('status' => 'notsent', 'message' => Yii::t('media', 'File was not sent to server.')));
            }
        }else{
            echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
        }
    }

    /**
     * Remove a temp file and the temp folder that is in.
     */
    public function actionRemoveTmpUpload(){
        if(isset($_POST['id'])){
            $id = $_POST['id'];
            if(is_numeric($id)){
                $tempFile = TempFile::model()->findByPk($id);
                $folder = $tempFile->folder;
                $name = $tempFile->name;
                if(file_exists('uploads/media/temp/'.$folder.'/'.$name))
                    unlink('uploads/media/temp/'.$folder.'/'.$name); // delete file
                if(file_exists('uploads/media/temp/'.$folder))
                    rmdir('uploads/media/temp/'.$folder); // delete folder
                $tempFile->delete(); // delete database entry tracking temp file
            }
        }
    }

    /**
     * Upload a file.
     */
    public function actionUpload(){
        if(isset($_FILES['upload'])){
            $model = new Media;
            $temp = CUploadedFile::getInstanceByName('upload');
            if(isset($temp)){
                $name = $temp->getName();
                $name = str_replace(' ', '_', $name);
                $check = Media::model()->findAllByAttributes(array('fileName' => $name));
                if(count($check) != 0){
                    $count = 1;
                    $newName = $name;
                    $arr = explode('.', $name);
                    $name = $arr[0];
                    while(count($check) != 0){
                        $newName = $name.'('.$count.').'.$temp->getExtensionName();
                        $check = Media::model()->findAllByAttributes(array('fileName' => $newName));
                        $count++;
                    }
                    $name = $newName;
                }
                $username = Yii::app()->user->name;
                if(FileUtil::ccopy($temp->getTempName(), "uploads/media/$username/$name")){
                    if(isset($_POST['associationId']))
                        $model->associationId = $_POST['associationId'];
                    if(isset($_POST['associationType']))
                        $model->associationType = $_POST['associationType'];
                    if(isset($_POST['private']))
                        $model->private = $_POST['private'];
                    $model->uploadedBy = Yii::app()->user->getName();
                    $model->createDate = time();
                    $model->lastUpdated = time();
                    $model->fileName = $name;
                    if($model->save()){

                    }
                    if($model->associationType == 'feed'){
                        $event = new Events;
                        $event->user = Yii::app()->user->getName();
                        if(isset($_POST['attachmentText']) && !empty($_POST['attachmentText'])){
                            $event->text = $_POST['attachmentText'];
                        }else{
                            $event->text = Yii::t('app', 'Attached file: ');
                        }
                        $event->type = 'media';
                        $event->timestamp = time();
                        $event->lastUpdated = time();
                        $event->associationId = $model->id;
                        $event->associationType = 'Media';
                        if($event->save()){
                            $this->redirect('whatsNew');
                        }else{
                            unlink('uploads/'.$name);
                        }
                        $this->redirect(array('site/whatsNew'));
                    }else if($model->associationType == 'docs'){
                        $this->redirect(array('docs/index'));
                    }else if($model->associationType == 'loginSound' || $model->associationType == 'notificationSound'){
                        $profile = Yii::app()->params->profile;
                        if($model->associationType == 'loginSound'){
                            $profile->loginSound = $name;
                        }else{
                            $profile->notificationSound = $name;
                        }
                        $profile->update(array($model->associationType));
                        $this->redirect(array('profile/settings', 'id' => Yii::app()->user->getId()));
                    }elseif($model->associationType=='bg' || $model->associationType=='bg-private'){
                        $profile=Yii::app()->params->profile;
                        $profile->backgroundImg = $name;
                        $profile->update(array('backgroundImg'));
                        $this->redirect(array('profile/settings', 'id' => Yii::app()->user->getId()));
                    }else{
                        $note = new Actions;
                        $note->createDate = time();
                        $note->dueDate = time();
                        $note->completeDate = time();
                        $note->complete = 'Yes';
                        $note->visibility = '1';
                        $note->completedBy = Yii::app()->user->getName();
                        if($model->private){
                            $note->assignedTo = Yii::app()->user->getName();
                            $note->visibility = '0';
                        }else{
                            $note->assignedTo = 'Anyone';
                        }
                        $note->type = 'attachment';
                        $note->associationId = $_POST['associationId'];
                        $note->associationType = $_POST['associationType'];

                        $association = $this->getAssociation($note->associationType, $note->associationId);
                        if($association != null)
                            $note->associationName = $association->name;

                        $note->actionDescription = $model->fileName.':'.$model->id;
                        if($note->save()){

                        }else{
                            unlink('uploads/'.$name);
                        }
                        if($model->associationType == 'product')
                            $this->redirect(array('/products/'.$model->associationId));
                        $this->redirect(array($model->associationType.'/'.$model->associationId));
                    }
                }
            }else{
                if(isset($_SERVER['HTTP_REFERER'])){
                    $this->redirect($_SERVER['HTTP_REFERER']);
                }else{
                    throw new CHttpException('400', 'Invalid request');
                }
            }
        }else{
            throw new CHttpException('400', 'Invalid request.');
        }
    }

    /**
     * Upload contact profile picture from Facebook.
     */
    public function actionUploadProfilePicture(){
        if(isset($_POST['photourl'])){
            $photourl = $_POST['photourl'];
            $name = 'profile_picture_'.$_POST['associationId'].'.jpg';
            $model = new Media;
            $check = Media::model()->findAllByAttributes(array('fileName' => $name));
            if(count($check) != 0){
                $count = 1;
                $newName = $name;
                $arr = explode('.', $name);
                $name = $arr[0];
                while(count($check) != 0){
                    $newName = $name.'('.$count.').jpg';
                    $check = Media::model()->findAllByAttributes(array('fileName' => $newName));
                    $count++;
                }
                $name = $newName;
            }
            $model->associationId = $_POST['associationId'];
            $model->associationType = $_POST['type'];
            $model->createDate = time();
            $model->fileName = $name;

            // download and save picture
            $img = FileUtil::ccopy($photourl, "uploads/$name");
            $model->save();

            // put picture into new action
            $note = new Actions;
            $note->createDate = time();
            $note->dueDate = time();
            $note->completeDate = time();
            $note->complete = 'Yes';
            $note->visibility = '1';
            $note->completedBy = "Web Lead";
            $note->assignedTo = 'Anyone';
            $note->type = 'attachment';
            $note->associationId = $_POST['associationId'];
            $note->associationType = $_POST['type'];

            $association = $this->getAssociation($note->associationType, $note->associationId);
            if($association != null){
                $note->associationName = $association->name;
            }
            $note->actionDescription = $model->fileName.':'.$model->id;
            if($note->save()){

            }else{
                unlink('uploads/'.$name);
            }
            $this->redirect(array($model->associationType.'/'.$model->associationId));
        }
    }

    /**
     * Index action.
     *
     * This is the default 'index' action that is invoked when an action
     * is not explicitly requested by users.
     */
    //
    public function actionIndex(){
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        // check if we are on a mobile browser
        if(isset($_GET['mobile']) && $_GET['mobile'] == 'false'){
            $cookie = new CHttpCookie('x2mobilebrowser', 'false'); // create cookie
            $cookie->expire = time() + 31104000; // expires in 1 year
            Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
        }else{
            $mobileBrowser = Yii::app()->request->cookies->contains('x2mobilebrowser') ? Yii::app()->request->cookies['x2mobilebrowser']->value : '';
            if($mobileBrowser == 'true')
                $this->redirect(array('/mobile/site/index'));
        }

        if(Yii::app()->user->isGuest)
            $this->redirect(array('/site/login'));
        else{
            $profile = X2Model::model('profile')->findByPk(Yii::app()->user->getId());
            if(Yii::app()->user->checkAccess('AdminIndex')){
                $admin = &Yii::app()->params->admin;
                if(Yii::app()->session['versionCheck'] == false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time()))
                    Yii::app()->session['alertUpdate'] = true;
                else
                    Yii::app()->session['alertUpdate'] = false;
            }else{
                Yii::app()->session['alertUpdate'] = false;
            }

            if(empty($profile->startPage)){
                $this->redirect(array('site/whatsNew'));
            }else{
                $controller = Yii::app()->file->set('protected/controllers/'.ucfirst($profile->startPage).'Controller.php');
                $module = Yii::app()->file->set('protected/modules/'.$profile->startPage.'/controllers/'.ucfirst($profile->startPage).'Controller.php');
                if($controller->exists || $module->exists){
                    if($controller->exists)
                        $this->redirect(array($profile->startPage.'/index'));
                    if($module->exists)
                        $this->redirect(array($profile->startPage.'/'.$profile->startPage.'/index'));
                } else{
                    $page = CActiveRecord::model('Docs')->findByAttributes(array('name' => ucfirst($profile->startPage)));
                    if(isset($page)){
                        $id = $page->id;
                        $this->redirect('docs/'.$id.'?static=true');
                    }else{
                        $this->redirect(array('site/whatsNew'));
                    }
                }
            }
        }
    }

    function phpinfo_array($return = false){
        ob_start();
        phpinfo(-1);

        $pi = preg_replace(
                array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
            '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
            "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
            .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
            "# +#", '#<tr>#', '#</tr>#'), array('$1', '', '', '', '</$1>'."\n", '<', ' ', ' ', ' ', '', ' ',
            '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
            "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
            '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '<tr><td>Zend Engine</td><td>$2</td></tr>'."\n".
            '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'), ob_get_clean());

        $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
        unset($sections[0]);

        $pi = array();
        foreach($sections as $section){
            $n = substr($section, 0, strpos($section, '</h2>'));
            preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);
            foreach($askapache as $m)
                if(is_array($m) && count($m) == 4){
                    if(empty($p[$n]))
                        $p[$n] = array();
                    $pi[$n][$m[1]] = (!isset($m[3]) || $m[2] == $m[3]) ? $m[2] : array_slice($m, 2);
                }
        }

        return ($return === false) ? print_r($pi) : $pi;
    }

    /**
     * Error printing.
     *
     * This is the action to handle external exceptions.
     */
    public function actionError(){

        function var_dump_to_string($var){
            $output = "<pre>";
            _var_dump_to_string($var, $output);
            $output .= "</pre>";
            return $output;
        }

        function _var_dump_to_string($var, &$output, $prefix = ""){
            foreach($var as $key => $value){
                if(is_array($value)){
                    $output.= $prefix.$key.": \n";
                    _var_dump_to_string($value, $output, "  ".$prefix);
                }else{
                    $output.= $prefix.$key.": ".$value."\n";
                }
            }
        }

        function is_disabled($function) {
            $disabled_functions=explode(',',str_replace(" ","",ini_get('disable_functions')));
            return in_array($function, $disabled_functions);
        }


        if($error = Yii::app()->errorHandler->error){
            if(Yii::app()->request->isAjaxRequest){
                echo $error['message'];
            }else{
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                if($error['code'] == '404'){
                    $request = Yii::app()->request->requestUri;
                    if(preg_match('/opportunity/', $request)){
                        $request = preg_replace('/opportunity/', 'opportunities', $request);
                        $this->redirect($request);
                    }
                    if(preg_match('/id/', $request)){
                        $request = preg_replace('/id\//', '', $request);
                        $this->redirect($request);
                    }
                    if(empty($referer)){
                        $this->render('errorDisplay', $error);
                        Yii::app()->end();
                    }
                }
                if($error['code'] == '403' || $error['code'] == '400'){
                    $this->render('errorDisplay', $error);
                    Yii::app()->end();
                }
                $request = Yii::app()->request->requestUri;
                if(!is_disabled('phpinfo')){
                    $info = $this->phpinfo_array(true);
                }else{
                    $info='';
                }
                if(!empty(Yii::app()->params->admin->emailFromAddr))
                    $email = Yii::app()->params->admin->emailFromAddr;
                else
                    $email = "";
                $get = var_dump_to_string($_GET);
                $post = var_dump_to_string($_POST);
                $phpversion = phpversion();
                $x2version = Yii::app()->params->version;
                unset($error['traces']);
                $error['trace'] = CHtml::encode($error['trace']);

                $phpInfoErrorReport = base64_encode(serialize(array_merge($error, array(
                                    'request' => $request,
                                    'phpinfo' => $info,
                                    'referer' => $referer,
                                    'get' => $get,
                                    'post' => $post,
                                    'phpversion' => $phpversion,
                                    'x2version' => $x2version,
                                    'adminEmail' => $email,
                                    'user' => Yii::app()->user->getName(),
                                    'isAdmin' => Yii::app()->user->checkAccess('AdminIndex'),
                                ))));

                $errorReport = base64_encode(serialize(array_merge($error, array(
                                    'request' => $request,
                                    'referer' => $referer,
                                    'get' => $get,
                                    'post' => $post,
                                    'phpversion' => $phpversion,
                                    'x2version' => $x2version,
                                    'adminEmail' => $email,
                                    'user' => Yii::app()->user->getName(),
                                    'isAdmin' => Yii::app()->user->checkAccess('AdminIndex'),
                                ))));

                $this->render('error', array_merge($error, array(
                            'request' => $request,
                            'info' => $info,
                            'referer' => $referer,
                            'get' => $get,
                            'post' => $post,
                            'phpversion' => $phpversion,
                            'x2version' => $x2version,
                            'errorReport' => $errorReport,
                            'phpInfoErrorReport' => $phpInfoErrorReport,
                        )));
            }
        }
    }

    public function actionBugReport(){

        $info = $this->phpinfo_array(true);
        if(!empty(Yii::app()->params->admin->emailFromAddr))
            $email = Yii::app()->params->admin->emailFromAddr;
        else
            $email = "";
        $phpversion = phpversion();
        $x2version = Yii::app()->params->version;

        $phpInfoErrorReport = base64_encode(serialize(array(
                    'phpinfo' => $info,
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'adminEmail' => $email,
                    'user' => Yii::app()->user->getName(),
                    'isAdmin' => Yii::app()->user->checkAccess('AdminIndex'),
                )));

        $errorReport = base64_encode(serialize(array(
                    'phpversion' => $phpversion,
                    'x2version' => $x2version,
                    'adminEmail' => $email,
                    'user' => Yii::app()->user->getName(),
                    'isAdmin' => Yii::app()->user->checkAccess('AdminIndex'),
                )));

        $this->render('bugReport', array(
            'phpInfoErrorReport' => $phpInfoErrorReport,
            'errorReport' => $errorReport,
            'x2version' => $x2version,
            'phpversion' => $phpversion,
        ));
    }

    /**
     *  Displays the About page
     */
    public function actionContact(){
        $model = new ContactForm;
        if(isset($_POST['ContactForm'])){
            $model->attributes = $_POST['ContactForm'];
            if($model->validate()){
                $headers = "From: {$model->email}\r\nReply-To: {$model->email}";
                mail(Yii::app()->params['adminEmail'], $model->subject, $model->body, $headers);
                Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
        $this->render('contact', array('model' => $model));
    }

    /**
     * Obtains the record association type for an object, i.e. contacts.
     *
     * @param string $type
     * @param integer $id
     * @return mixed
     */
    protected function getAssociation($type, $id){

        $classes = array(
            'actions' => 'Actions',
            'contacts' => 'Contacts',
            'accounts' => 'Accounts',
            'product' => 'Product',
            'products' => 'Product',
            'Campaign' => 'Campaign',
            'quote' => 'Quote',
            'quotes' => 'Quote',
            'opportunities' => 'Opportunity',
        );

        if(array_key_exists($type, $classes) && $id != 0)
            return X2Model::model($classes[$type])->findByPk($id);
        else
            return null;
    }

    /**
     * View all notifications for the current web user.
     */
    public function actionViewNotifications(){
        $dataProvider = new CActiveDataProvider('Notification', array(
                    'criteria' => array(
                        // 'order'=>'viewed ASC',
                        'condition' => 'user="'.Yii::app()->user->name.'"'
                    ),
                    'sort' => array(
                        'defaultOrder' => 'createDate DESC'
                    ),
                ));
        $this->render('viewNotifications', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionWarning(){
        header("Content-type: image/gif");
        $img = 'R0lGODlhZABQAPcAANgAAP///w';
        for($i = 0; $i < 203; $i++)
            $img .= 'AAAAA';
        $img .= 'CwAAAAAZABQAAAI/wABCBxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjwkDiBS5cKRAkiBTKkQJIMBKlwNZqpxJUOZJkzdNjiS5s+VOnDph9qT5EqbPmzGNHk3acilSny6jClVK1CDQqUyzIpUKFaXMn1SrWuXq9CtVnmS9KrUptqBRtE+dLsUKd+7at23dDu2aFCdUpmrB+t2bt7Dhw4gTK57pV+viqmwfG7ZJ+GfOqYOHEn4cmaVnu1vTipYct+bbs5aPovWcWmPYiJ/jUg5bl29kjLcfxi6LNzZctWU3NpYItmvg04GNZ85NfDjptrWfiwUuHfrp6pDvTryKNaRzvWNdo//e3l33a9Mp2TL3HtqsWdPHMd+N/5KvXN0n0T9lfZC6ftCqnXdcafg1pZV/7vl3YHfR6bfXev01xh9vWSkoG4NkgXeZY+ZpCCCFAF63H12vsfYebMhxJ198mF024F/DJfgfdhlJGON5NDan12w54jabejj22FBnJQYpZFERIgShcS5eJ1VqKq5oZHhJVskQXgYaSFd+TWHpZUwQfXffjCGB2WVNSqLJ5ZdTUqnjlWbeeJaaWsa5JHxKijnmWJ8JSGeW8oHZJpnw4QihUGammSigay7qkJ6beahonWsiuuiXjXJ55aD1eZciUHLZ2GKTbeoZJqfY3bnpkZ4K5uqrrbHwWiistAom6624klchizC+aFlQtiKGKaVaWoqlpnNpiupHUfWXn3bEuuXgs5KV2BeazWYpraDFLpueVdiGa+xuZyLqLbPazilttseme6a2iQ1rKaVeXapmvcjmxd+v/Ga43GoBRprrwAQXbPDBCOMqZ6HXtvrkX/f1VNyfPDm60rbEzmukdl/RqTHG5XGqbrTKloRenxZnS/GkJnucL6MXc9tbyurtiHHLiY6r8sbicltps7SxPKixcRb98s1eamY0uM5arOjHMLfbdMlRL73WtE7zaSKeDecJrHIQR3m1oCMnjDPLZsdcZtpst+22QQEBADs=';
        echo base64_decode($img);
    }

    /**
     * Displays the login page
     */
    public function actionLogin(){
        $this->layout = '//layouts/login';
        if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->homeUrl);
            return;
        }

        $model = new LoginForm;
        $model->useCaptcha = false;

        if(isset($_POST['LoginForm'])){
            $model->attributes = $_POST['LoginForm']; // get user input data

            x2base::cleanUpSessions();

            $ip = $this->getRealIp();

            // increment count on every session with this user/IP, to prevent brute force attacks using session_id spoofing or whatever
            Yii::app()->db->createCommand('UPDATE x2_sessions SET status=status-1, lastUpdated=:time WHERE user=:name AND IP=:ip AND status BETWEEN -2 AND 0')
                    ->bindValues(array(':time' => time(), ':name' => $model->username, ':ip' => $ip))
                    ->execute();

            $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                    ->select('username')
                    ->from('x2_users')
                    ->where('username=:name AND status=1', array(':name' => $model->username))
                    ->limit(1)
                    ->queryScalar(); // get the correctly capitalized username

            if($activeUser === false){
                $model->verifyCode = ''; // clear captcha code
                $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
                $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
            }else{
                $model->username = $activeUser;

                if(isset($_SESSION['sessionId']))
                    $sessionId = $_SESSION['sessionId'];
                else
                    $sessionId = null;

                $session = X2Model::model('Session')->findByPk($sessionId);

                // if this client has already tried to log in, increment their attempt count
                if($session === null){
                    $sessionId = $_SESSION['sessionId'] = session_id();
                    $session = new Session;
                    $session->id = $sessionId;
                    $session->user = $model->username;
                    $session->lastUpdated = time();
                    $session->status = 0;
                    $session->IP = $ip;
                }else{
                    $session->lastUpdated = time();
                    if($session->status < -1)
                        $model->useCaptcha = true;
                    if($session->status < -2)
                        $model->setScenario('loginWithCaptcha');
                }

                if($model->validate() && $model->login()){  // user successfully logged in
                    if(Yii::app()->user->checkAccess('AdminIndex'))
                        $this->checkUpdates();   // check for updates if admin
                    else
                        Yii::app()->session['versionCheck'] = true; // ...or don't

                    $session->status = 1;
                    $session->save();
                    SessionLog::logSession($model->username, $sessionId, 'login');
                    $_SESSION['playLoginSound'] = true;
                    if(Yii::app()->user->returnUrl == 'site/index')
                        $this->redirect('index');
                    else
                        $this->redirect(Yii::app()->user->returnUrl); // after login, redirect to wherever
                } else{ // login failed
                    $model->verifyCode = ''; // clear captcha code
                    if($model->hasErrors()){
                        $model->addError('username', Yii::t('app', 'Incorrect username or password.'));
                        $model->addError('password', Yii::t('app', 'Incorrect username or password.'));
                    }
                    $session->save();
                }
            }
        }

        header('REQUIRES_AUTH: 1');    // tell windows making AJAX requests to redirect

        $this->render('login', array('model' => $model)); // display the login form
    }

    /**
     * Log in using a Google account.
     */
    public function actionGoogleLogin(){
        $this->layout = '//layouts/login';
        $model = new LoginForm;
        $model->useCaptcha = false;

        // echo var_dump(Session::getOnlineUsers());
        if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest){
            $this->redirect(Yii::app()->homeUrl);
            return;
        }
        if(isset($_SESSION['access_token'])){
            require_once 'protected/extensions/google-api-php-client/src/Google_Client.php';
            require_once 'protected/extensions/google-api-php-client/src/contrib/Google_Oauth2Service.php';

            $client = new Google_Client();
            $client->setApplicationName("X2Engine CRM");
            // Visit https://code.google.com/apis/console to generate your
            // oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
            $admin = Admin::model()->findByPk(1);
            $client->setClientId($admin->googleClientId);
            $client->setClientSecret($admin->googleClientSecret);
            $client->setRedirectUri('http://www.x2developer.com/x2jake/site/googleLogin');
            //$client->setDeveloperKey('insert_your_developer_key');
            $oauth2 = new Google_Oauth2Service($client);

            $client->setAccessToken($_SESSION['access_token']);

            $user = $oauth2->userinfo->get();
            $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

            $userRecord = User::model()->findByAttributes(array('emailAddress' => $email));
            $profileRecord = Profile::model()->findByAttributes(array(), "emailAddress='$email' OR googleId='$email'");
            if(isset($userRecord) || isset($profileRecord)){
                if(!isset($userRecord)){
                    $userRecord = User::model()->findByPk($profileRecord->id);
                }
                $username = $userRecord->username;
                $password = $userRecord->password;
                $model->username = $username;
                $model->password = $password;
                if($model->login(true)){
                    $ip = $this->getRealIp();

                    x2base::cleanUpSessions();
                    if(isset($_SESSION['sessionId']))
                        $sessionId = $_SESSION['sessionId'];
                    else
                        $sessionId = $_SESSION['sessionId'] = session_id();

                    $session = X2Model::model('Session')->findByPk($sessionId);

                    // if this client has already tried to log in, increment their attempt count
                    if($session === null){
                        $session = new Session;
                        $session->id = $sessionId;
                        $session->user = $model->username;
                        $session->lastUpdated = time();
                        $session->status = 1;
                        $session->IP = $ip;
                    }else{
                        $session->lastUpdated = time();
                    }
                    // x2base::cleanUpSessions();
                    // $session = X2Model::model('Session')->findByAttributes(array('user'=>$userRecord->username,'IP'=>$ip));
                    // if(isset($session)) {
                    // $session->lastUpdated = time();
                    // } else {
                    // $session = new Session;
                    // $session->user = $model->username;
                    // $session->lastUpdated = time();
                    // $session->status = 1;
                    // $session->IP = $ip;
                    // }
                    $session->save();
                    SessionLog::logSession($userRecord->username, $sessionId, 'googleLogin');
                    $userRecord->login = time();
                    $userRecord->save();
                    Yii::app()->session['versionCheck'] = true;

                    Yii::app()->session['loginTime'] = time();
                    $session->status = 1;

                    if(Yii::app()->user->returnUrl == 'site/index')
                        $this->redirect('index');
                    else
                        $this->redirect(Yii::app()->user->returnUrl);
                } else{
                    print_r($model->getErrors());
                }
            }else{
                $this->render('googleLogin', array(
                    'failure' => 'email',
                    'email' => $email,
                ));
            }
        }else{
            $this->render('googleLogin');
        }
    }

    /**
     * Toggle display of tags.
     */
    public function actionToggleShowTags($tags){
        if($tags == 'allUsers'){
            Yii::app()->params->profile->tagsShowAllUsers = true;
            Yii::app()->params->profile->update();
        }else if($tags == 'justMe'){
            Yii::app()->params->profile->tagsShowAllUsers = false;
            Yii::app()->params->profile->update();
        }
    }

    /**
     * Adds a tag to a model.
     *
     * Echoes "true" if tag was created (and was not a duplicate), "false" otherwise.
     */
    public function actionAppendTag(){
        if(isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) && ctype_alpha($_POST['Type'])){
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);
            echo $model->addTags($_POST['Tag']);
            exit;
            if($model !== null && $model->addTags($_POST['Tag'])){
                echo 'true';
                return;
            }
        }
        echo 'false';
    }

    /**
     * Removes a tag from a model.
     *
     * Echoes "true" if tag was removed, "false" otherwise.
     */
    public function actionRemoveTag(){
        if(isset($_POST['Type'], $_POST['Id'], $_POST['Tag']) && ctype_alpha($_POST['Type'])){
            $model = X2Model::model($_POST['Type'])->findByPk($_POST['Id']);

            if($model !== null && $model->removeTags($_POST['Tag'])){
                echo 'true';
                return;
            }
        }
        echo 'false';
    }

    /**
     * Add a record to record relationship
     *
     * A record can be a contact, opportunity, or account. This function is
     * called via ajax from the Relationships Widget.
     *
     */
    public function actionAddRelationship(){
        //check if relationship already exits
        if(isset($_POST['ModelName']) && isset($_POST['ModelId']) && isset($_POST['RelationshipModelName']) && isset($_POST['RelationshipModelId'])){

            $modelName = $_POST['ModelName'];
            $modelId = $_POST['ModelId'];
            $relationshipModelName = $_POST['RelationshipModelName'];
            $relationshipModelId = $_POST['RelationshipModelId'];

            $relationship = Relationships::model()->findByAttributes(array(
                'firstType' => $_POST['ModelName'],
                'firstId' => $_POST['ModelId'],
                'secondType' => $_POST['RelationshipModelName'],
                'secondId' => $_POST['RelationshipModelId'],
                    ));
            if($relationship){
                echo "duplicate";
                Yii::app()->end();
            }
            $relationship = Relationships::model()->findByAttributes(array(
                'firstType' => $_POST['RelationshipModelName'],
                'firstId' => $_POST['RelationshipModelId'],
                'secondType' => $_POST['ModelName'],
                'secondId' => $_POST['ModelId'],
                    ));
            if($relationship){
                echo "duplicate";
                Yii::app()->end();
            }

            $relationship = new Relationships;
            $relationship->firstType = $_POST['ModelName'];
            $relationship->firstId = $_POST['ModelId'];
            $relationship->secondType = $_POST['RelationshipModelName'];
            $relationship->secondId = $_POST['RelationshipModelId'];
            $relationship->save();
            if($relationshipModelName == "Contacts"){
                $results = Yii::app()->db->createCommand("SELECT * from x2_relationships WHERE (firstType='Contacts' AND firstId=$relationshipModelId AND secondType='Accounts') OR (secondType='Contacts' AND secondId=$relationshipModelId AND firstType='Accounts')")->queryAll();
                if(sizeof($results) == 1){
                    $model = Contacts::model()->findByPk($relationshipModelId);
                    if($model){
                        $model->company = $modelId;
                        $model->update();
                    }
                }
            }
            echo "success";
            Yii::app()->end();
        }
    }

    public function actionCreateRecords(){
        $contact = new Contacts;
        $account = new Accounts;
        $opportunity = new Opportunity;
        $users = User::getNames();

        if(isset($_POST['Contacts']) && isset($_POST['Accounts']) && isset($_POST['Opportunity'])){
            //		var_dump($_POST);
            //		exit();
            $contact->setX2Fields($_POST['Contacts']);
            $account->setX2Fields($_POST['Accounts']);
            $opportunity->setX2Fields($_POST['Opportunity']);

            $allValid = true;

            if($contact->validate() == false)
                $allValid = false;

            if($account->validate() == false)
                $allValid = false;

            if($opportunity->validate() == false)
                $allValid = false;

            if($allValid){
                $c = $this->createContact($contact, $contact->attributes, '1');
                $a = $this->createAccount($account, $account->attributes, '1');
                $o = $this->createOpportunity($opportunity, $opportunity->attributes, '1');



                if($c && $a && $o){ // all records created?
                    $contact->company = $account->id;
                    $contact->update();
                    $opportunity->accountName = $account->id;
                    $opportunity->update();

                    Relationships::create('Contacts', $contact->id, 'Accounts', $account->id);
                    Relationships::create('Opportunity', $opportunity->id, 'Contacts', $contact->id);
                    Relationships::create('Opportunity', $opportunity->id, 'Accounts', $account->id);

                    if(isset($_GET['ret'])){
                        if($_GET['ret'] == 'contacts')
                            $this->redirect(array("/contacts/{$contact->id}"));
                        else if($_GET['ret'] == 'accounts')
                            $this->redirect(array("/accounts/{$account->id}"));
                        else if($_GET['ret'] == 'opportunities')
                            $this->redirect(array("/opportunities/{$opportunity->id}"));
                    } else{
                        $this->redirect(array("/contacts/{$contact->id}"));
                    }
                }
            }
        }

        $this->render('createRecords', array(
            'contact' => $contact,
            'account' => $account,
            'opportunity' => $opportunity,
            'users' => $users,
        ));
    }

    /**
     * Creates contact record
     *
     * Call this function from createRecords
     */
    public function createContact($model, $oldAttributes, $api){
        $model->createDate = time();
        $model->lastUpdated = time();
        if(empty($model->visibility) && $model->visibility != 0)
            $model->visibility = 1;
        if($api == 0){
            parent::create($model, $oldAttributes, $api);
        }else{
            $lookupFields = Fields::model()->findAllByAttributes(array('modelName' => 'Contacts', 'type' => 'link'));
            foreach($lookupFields as $field){
                $fieldName = $field->fieldName;
                if(isset($model->$fieldName)){
                    $lookup = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $model->$fieldName));
                    if(isset($lookup))
                        $model->$fieldName = $lookup->id;
                }
            }
            return parent::create($model, $oldAttributes, $api);
        }
    }

    /**
     * Creates account record
     *
     * Call this function from createRecords
     */
    public function createAccount($model, $oldAttributes, $api){

        $model->annualRevenue = $this->parseCurrency($model->annualRevenue, false);
        $model->createDate = time();
        if($api == 0)
            parent::create($model, $oldAttributes, $api);
        else
            return parent::create($model, $oldAttributes, $api);
    }

    /**
     * Creates opportunity record
     *
     * Call this function from createRecords
     */
    public function createOpportunity($model, $oldAttributes, $api = 0){

        // process currency into an INT
//		$model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);

        if(isset($model->associatedContacts))
            $model->associatedContacts = Opportunity::parseContacts($model->associatedContacts);
        $model->createDate = time();
        $model->lastUpdated = time();
        // $model->expectedCloseDate = Formatter::parseDate($model->expectedCloseDate);
        if($api == 1){
            return parent::create($model, $oldAttributes, $api);
        }else{
            parent::create($model, $oldAttributes, '0');
        }
    }

    /**
     * Connects to one of the X2 update servers and sets Yii::app()->session['versionCheck']
     * to true (up to date) or false (not up to date). Also sets Yii::app()->session['newVersion']
     * to the latest version if not up to date.
     */
    protected function checkUpdates(){
        if(ini_get('allow_url_fopen') != 1){
            Yii::app()->session['versionCheck'] = true;
            return;
        }

        $context = stream_context_create(array(
            'http' => array('timeout' => 2)  // set request timeout in seconds
                ));
        $updateSources = array('http://x2planet.com/installs/updates/versionCheck');
        if(in_array(Yii::app()->params->admin['edition'], array('opensource', Null))){
            $updateSources = array(
                'http://x2planet.com/updates/versionCheck.php',
                'http://x2base.com/updates/versionCheck.php'
            );
        }
        $newVersion = '';

        foreach($updateSources as $url){
            $sourceVersion = FileUtil::getContents($url, 0, $context);
            if($sourceVersion !== false){
                $newVersion = $sourceVersion;
                break;
            }
        }
        if(empty($newVersion))
            $newVersion = Yii::app()->params->version;
        /*
          // check X2Planet for updates
          $x2planetVersion = @file_get_contents('http://x2planet.com/updates/versionCheck.php',0,$context);
          if($x2planetVersion !== false)
          $newVersion = $x2planetVersion;
          else {
          // try X2Base if that didn't work
          $x2baseVersion = @file_get_contents('http://x2base.com/updates/versionCheck.php',0,$context);
          if($x2baseVersion !== false)
          $newVersion=$x2baseVersion;
          else
          $newVersion=Yii::app()->params->version;
          } */
        $unique_id = Yii::app()->params->admin['unique_id'];
        if(version_compare($newVersion, Yii::app()->params->version) > 0 && !in_array($unique_id, array('none', Null))){ // if the latest version is newer than our version
            Yii::app()->session['versionCheck'] = false;
            Yii::app()->session['newVersion'] = $newVersion;
        } else
            Yii::app()->session['versionCheck'] = true;
    }

    /**
     * Checks for any tasks that need to be executed at a specific time
     *
     * Needs to be called by a cronjob.
     */
    // public function actionCron() {
    // $emails = X2Model::model('Actions')->findByAttributes(array(
    // 'type'=>'email_staged',
    // 'dueDate'=>'<'.time(),
    // 'complete'=>'No'
    // ));
    // }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout(){
        $user = User::model()->findByPk(Yii::app()->user->getId());
        if(isset($user)){
            $user->lastLogin = time();
            $user->save();

            if(isset($_SESSION['sessionId'])){
                SessionLog::logSession($user->username, $_SESSION['sessionId'], 'logout');
                X2Model::model('Session')->deleteByPk($_SESSION['sessionId']);
            }else{
                X2Model::model('Session')->deleteAllByAttributes(array('IP' => $this->getRealIp()));
            }
        }
        if(isset($_SESSION['access_token']))
            unset($_SESSION['access_token']);

        Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionToggleVisibility(){
        $id = $_SESSION['sessionId'];
        $session = Session::model()->findByAttributes(array('id' => $id));
        if(isset($session)){
            if($session->status < 0)
                $session->status = 0;
            $session->status = !$session->status;
            $session->save();
            SessionLog::logSession(Yii::app()->user->getName(), $id, $session->status ? "visible" : "invisible");
        }
        $this->redirect($_GET['redirect']);
    }

    /**
     *  Remove a widget from the page and put it in the widgets menu
     *
     */
    function actionHideWidget(){
        if(isset($_POST['name'])){
            $name = $_POST['name'];

            $layout = Yii::app()->params->profile->getLayout();

            // the widget could be in any of the blocks in the page, so check all of them
            foreach($layout as $b => &$block){
                if(isset($block[$name])){
                    if($b == 'right'){
                        $layout['hiddenRight'][$name] = $block[$name];
                    }else{
                        $layout['hidden'][$name] = $block[$name];
                    }
                    unset($block[$name]);
                    Yii::app()->params->profile->saveLayout($layout);
                    break;
                }
            }

            // make a list of hidden widgets, using <li>, to send back to the browser
            $list = "";
            foreach($layout['hidden'] as $name => $widget){
                $list .= "<li><span class=\"x2-widget-menu-item\" id=\"$name\">{$widget['title']}</span></li>";
            }
            foreach($layout['hiddenRight'] as $name => $widget){
                $list .= "<li><span class=\"x2-widget-menu-item right\" id=\"$name\">{$widget['title']}</span></li>";
            }

            echo Yii::app()->params->profile->getWidgetMenu();
        }
    }

    function actionShowWidget(){
        if(isset($_POST['name']) && isset($_POST['block'])){ // ensure we have the params we need
            $name = $_POST['name'];
            $block = $_POST['block'];

            if(isset($_POST['modelType']) && isset($_POST['modelId'])){
                $modelType = $_POST['modelType'];
                $modelId = $_POST['modelId'];
            }

            $layout = Yii::app()->params->profile->getLayout();

            if($block == 'right'){ // x2temp: remove when $layout['hiddenRight'] is merged into $layout['hidden']
                foreach($layout['hiddenRight'] as $key => $widget){
                    if($key == $name){
                        $widget['minimize'] = false; // un-minimize widgets when we show them
                        $layout[$block][$key] = $widget;
                        unset($layout['hiddenRight'][$key]);
                        Yii::app()->params->profile->saveLayout($layout);
                        Yii::app()->session['fullscreen'] = false; // we just added a widget to the right sidebar, so turn off fullscreen mode
                        //	Yii::app()->clientScript->scriptMap['*.js'] = false;
                        //	$this->renderPartial('application.components.views.centerWidget', array('widget'=>$widget, 'name'=>$name, 'modelType'=>$modelType, 'modelId'=>$modelId), false, true);

                        break;
                    }
                }
            }else{

                foreach($layout['hidden'] as $key => $widget){
                    if($key == $name){
                        $widget['minimize'] = false; // un-minimize widgets when we show them
                        $layout[$block][$key] = $widget;
                        unset($layout['hidden'][$key]);
                        Yii::app()->params->profile->saveLayout($layout);
                        Yii::app()->clientScript->scriptMap['*.js'] = false;
                        $this->renderPartial('application.components.views.centerWidget', array('widget' => $widget, 'name' => $name, 'modelType' => $modelType, 'modelId' => $modelId), false, true);

                        break;
                    }
                }
            }
        }
    }

    function actionReorderWidgets(){
        if(isset($_POST['x2widget']) && isset($_POST['x2widget'])){
            $widgets = $_POST['x2widget']; // list of widgets
            $block = $_POST['block']; // left, right, or center

            $layout = Yii::app()->params->profile->getLayout();

            $newOrder = array();

            foreach($widgets as $name){
                foreach($layout[$block] as $key => $widget){
                    if($key == $name){
                        $newOrder[$key] = $widget;
                    }
                }
            }

            $layout[$block] = $newOrder;
            Yii::app()->params->profile->saveLayout($layout);
        }
    }

    function actionMinimizeWidget(){
        if(isset($_POST['name']) && isset($_POST['minimize'])){
            $name = $_POST['name'];
            $minimize = json_decode($_POST['minimize']);
            $layout = Yii::app()->params->profile->getLayout();

            // the widget could be in any of the blocks in the page, so check all of them
            foreach($layout as &$block){
                foreach($block as $key => &$widget){
                    if($key == $name){
                        $widget['minimize'] = $minimize;
                        Yii::app()->params->profile->saveLayout($layout);
                        break 2;
                    }
                }
            }
        }
    }

}
