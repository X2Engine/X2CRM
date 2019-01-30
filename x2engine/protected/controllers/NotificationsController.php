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
 * User notifications & social feed controller
 *
 * @package application.controllers
 */
Yii::import('application.models.Relationships');
Yii::import('application.components.behaviors.RelationshipsBehavior');
Yii::import('application.models.Tags');

class NotificationsController extends CController {

    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('get','delete','deleteAll','newMessage','getMessages','checkNotifications','saveGridviewSettings','saveFormSettings', 'fullScreen', 'widgetState','widgetOrder'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*')
            )
        );
    }

    /**
     * Obtain all current notifications/events for the current web user.
     */
    public function actionGet() {

        if(Yii::app()->user->isGuest) {
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'sessionError'=>Yii::t('app','Your X2Engine session has expired. You may select "cancel" to ignore this message and recover unsaved data from the current page. Otherwise, you will be redirected to the login page.')
            ));
            Yii::app()->end();
        }

        if(!isset($_GET['lastNotifId']))    // if the client doesn't specify the last
            $_GET['lastNotifId'] = 0;        // message ID received, send everything

        $notifications = $this->getNotifications($_GET['lastNotifId']);
        $notifCount = 0;
        if(count($notifications))
            $notifCount = X2Model::model('Notification')
                ->countByAttributes(array('user'=>Yii::app()->user->name),'createDate < '.time());

        $chatMessages = array();
        $lastEventId = 0;
        $lastTimestamp=0;
        // if the client specifies the last message ID received,
        if(isset($_GET['lastEventId']) && is_numeric($_GET['lastEventId'])){   
            // only send newer messages
            $lastEventId = $_GET['lastEventId'];                                
        }
        if(isset($_GET['lastTimestamp']) && is_numeric($_GET['lastTimestamp'])){
            $lastTimestamp=$_GET['lastTimestamp'];
        }
        if($lastEventId==0){
            // get page of newest events
            $retVal = Events::getFilteredEventsDataProvider (
                null, true, null, isset ($_SESSION['filters']));
            $dataProvider = $retVal['dataProvider'];
            $events = $dataProvider->getData ();
        }else{
            // get new events
            $limit=null;
            $result=Events::getEvents($lastEventId,$lastTimestamp,$limit);
            $events=$result['events'];
        }

        $i=count($events)-1;
        for($i; $i>-1; --$i) {
            if(isset($events[$i])){
                $userLink = '<span class="widget-event">'.$events[$i]->user.'</span>';
                $chatMessages[] = array(
                    (int)$events[$i]->id,
                    (int)$events[$i]->timestamp,
                    $userLink,
                    $events[$i]->getText(array ('truncated' =>true)),
                    Formatter::formatFeedTimestamp($events[$i]->timestamp)
                );
            }
        }

        if(!empty($notifications) || !empty($chatMessages)) {
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'notifCount'=>$notifCount,
                'notifData'=>$notifications,
                'chatData'=>$chatMessages,
            ));
        }
    }

    /**
     * Looks up notifications using the specified offset and limit
     */
    public function getNotifications($lastId=0,$getNext=false) {

        $notifications = array();

        if($getNext) {
            $criteria = new CDbCriteria(array(
                'condition'=>'id<=:lastId AND user=:user AND createDate <= :time',                                // don't get anything more recent than lastId,
                'params'=>array(':user'=>Yii::app()->user->name,':lastId'=>$lastId,':time'=>time()),        // because these are going to get appended to the end,
                'order'=>'id DESC',                                                                         // not the beginning of the list
                'limit'=>1,        // only get the 10th row
                'offset'=>9
            ));
        } else {
            $criteria = new CDbCriteria(array(
                'condition'=>'id>:lastId AND user=:user AND createDate <= :time',                                // normal request; get everything since lastId
                'params'=>array(':user'=>Yii::app()->user->name,':lastId'=>$lastId,':time'=>time()),
                'order'=>'id DESC',
                'limit'=>10
            ));
        }


        $notifModels = X2Model::model('Notification')->findAll($criteria);
        $skipAnonContactNotifs = Yii::app()->settings->disableAnonContactNotifs;

        foreach($notifModels as &$model) {
            $msg = $model->getMessage();
            $isAnonContactNotif = false;
            if ($skipAnonContactNotifs && $model->modelType === 'Actions') {
                $associatedAction = Actions::model()->findByPk($model->modelId);
                if ($associatedAction && $associatedAction->associationType === 'anoncontact' &&
                    $associatedAction->type === 'webactivity') {
                        $isAnonContactNotif = true;
                }
            }

            if($msg !== null && !$isAnonContactNotif) {
                $notifications[] = array(
                    'id'=>$model->id,
                    'viewed'=>$model->viewed,
                    'date'=>Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'),$model->createDate),
                    'text'=>$msg,
                    'timestamp'=>$model->createDate,
                    'modelId' => $model->modelId,
                    'type'=>$model->type,
                );
                if($model->type == 'voip_call') {
                    $model->viewed = 1;
                    $model->update('viewed');
                }
            }
        }
        return $notifications;
    }

    /**
     * Mark an action as viewed.
     */
    public function actionMarkViewed() {
        if(isset($_GET['id'])) {
            if(!is_array($_GET['id']))
                $_GET['id'] = array($_GET['id']);

            foreach($_GET['id'] as &$id) {
                $notif = X2Model::model('Notification')->findByPk($id);
                if(isset($notif) && $notif->user == Yii::app()->user->name) {
                    $notif->viewed = 1;
                    $notif->save();
                }
            }
        }
    }

    /**
     * Delete an action by its ID. Encode and return the next notification if requested
     * @param type $id
     */
    public function actionDelete($id) {

        if(!isset($_GET['lastNotifId']))
            $_GET['lastNotifId'] = 0;

        $model = X2Model::model('Notification')->findByPk($id);
        if(isset($model) && $model->user = Yii::app()->user->name)
            $model->delete();

        if(isset($_GET['getNext']))
            echo CJSON::encode(array('notifData'=>$this->getNotifications($_GET['lastNotifId'],true)));
    }

    /**
     * Clear all notifications.
     */
    public function actionDeleteAll() {
        X2Model::model('Notification')->deleteAllByAttributes(array('user'=>Yii::app()->user->name));
        $this->redirect(array('/site/viewNotifications'));
    }

    /**
     * Normalize linebreaks in output.
     *
     * @todo refactor this out of controllers
     * @param string $text
     * @param boolean $allowDouble
     * @param boolean $allowUnlimited
     * @return string
     */
    public static function convertLineBreaks($text,$allowDouble = true,$allowUnlimited = false) {
        $text = mb_ereg_replace("\r\n","\n",$text);        //convert microsoft's stupid CRLF to just LF

        if(!$allowUnlimited)
            $text = mb_ereg_replace("[\r\n]{3,}","\n\n",$text);    // replaces 2 or more CR/LF chars with just 2
        if($allowDouble)
            $text = mb_ereg_replace("[\r\n]",'<br />',$text);    // replaces all remaining CR/LF chars with <br />
        else
            $text = mb_ereg_replace("[\r\n]+",'<br />',$text);

        return $text;
    }
}
