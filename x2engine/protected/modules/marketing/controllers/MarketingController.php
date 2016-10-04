<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

/**
 * Controller to handle creating and mailing campaigns.
 *
 * @package application.modules.marketing.controllers
 */
class MarketingController extends x2base {
    
    public $modelClass = 'Campaign';

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'CampaignMailingBehavior' => array('class' => 'application.modules.marketing.components.CampaignMailingBehavior'),
            'ResponseBehavior' => array('class' => 'application.components.ResponseBehavior', 'isConsole' => false,'errorCode'=>200),
        ));
    }

    public function accessRules(){
        return array(
            array('allow', // allow all users
                'actions' => array('click', 'doNotEmailLinkClick'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform the following actions
                'actions' => array(
					'index', 'view', 'create', 'createFromTag', 'update', 'search', 'delete', 
                    'launch', 
					'toggle', 'complete', 'getItems', 'inlineEmail', 'mail', 'deleteWebForm',
					'webleadForm', 'getCampaignChartData'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' action
                'actions' => array('admin'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actions(){
        return array_merge(parent::actions(), array(
            'webleadForm' => array(
                'class' => 'CreateWebFormAction',
            ),
            'inlineEmail' => array(
                'class' => 'application.modules.marketing.components.actions.TestEmailAction',
            ),
        ));
    }

    /**
     * Deletes a web form record with the specified id 
     * @param int $id
     */
    public function actionDeleteWebForm ($id) {
        $model = WebForm::model ()->findByPk ($id); 
        $name = $model->name;
        $success = false;

        if ($model) {
            $success = $model->delete ();
        }
        AuxLib::ajaxReturn (
            $success,  
            Yii::t('app', "Deleted '$name'"),
            Yii::t('app', 'Unable to delete web form')
        );
    }

    
    /**
     * View anonymous contacts that have been spotted by
     * the tracker
     */
    public function actionAnonContactIndex() {
        $model = new AnonContact('search');
        $this->render('anonContactIndex', array('model'=>$model));
    }

    /**
     * View a single AnonContact record
     * @param integer $id of the AnonContact to view
     */
    public function actionAnonContactView($id) {
        $model = X2Model::model('AnonContact')->findByPk($id);
        if (!isset($model))
            throw new CHttpException(404, "The requested page does not exist.");
        $this->render('anonContactView', array(
            'model'=>$model,
            'modelName'=>'AnonContact',
        ));
    }

    public function actionAnonContactDelete ($id) {
        $model = X2Model::model('AnonContact')->findByPk($id);
        if(!isset($model)){
            Yii::app()->user->setFlash(
                'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('anonContactIndex'));
        }
        $model->delete ();
        $this->redirect(array('anonContactIndex'));
    }

    /**
     * View all fingerprints
     */
    public function actionFingerprintIndex() {
        $model = new Fingerprint('search');
        $this->render('fingerprintIndex', array('model'=>$model));
    }
    

    /**
     * Returns a JSON array of the names of all campaigns filtered by a search term.
     *
     * @return string A JSON array of strings
     */
    public function actionGetItems($modelType){
        $term = $_GET['term'].'%';
         
        if ($modelType === 'AnonContact') {
		    if(Yii::app()->user->checkAccess('MarketingAdminAccess')) {
                LinkableBehavior::getItems ($term, 'id', 'id', 'AnonContact');
            } else {
                throw new CHttpException (403, Yii::t('marketing', 'You do no have permission to'.
                    ' perform this action'));
            }
        } else {
         
            LinkableBehavior::getItems ($term);
         
        }
         
    }
    


    /**
     * Displays a particular model.
     *
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $model = $this->loadModel($id);
        if (!$this->checkPermissions($model, 'view')) $this->denied ();

        if (isset($_GET['ajax']) && $_GET['ajax'] == 'campaign-grid') {
            $this->renderPartial('campaignGrid', array('model' => $model));
            return;
        }

        if(!isset($model)){
            Yii::app()->user->setFlash(
                'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        if(isset($model->list)){
            //set this as the list we are viewing, for use by vcr controls
            Yii::app()->user->setState('contacts-list', $model->list->id);
        }

        // add campaign to user's recent item list
        User::addRecentItem('p', $id, Yii::app()->user->getId()); 

        $this->view($model, 'marketing');
    }

    /**
     * Displays the content field (email template) for a particular model.
     *
     * @param integer $id the ID of the model to be displayed
     */
    public function actionViewContent($id){
        $model = $this->loadModel($id);

        if(!isset($model)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        echo $model->content;
    }

    /**
     * Override of {@link CommonControllerBehavior::loadModel()}; expected
     * behavior is in this case deference to the campaign model's
     * {@link Campagin::load()} function.
     *
     * @param type $id
     * @return type
     */
    public function loadModel($id){
        return Campaign::load($id);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate(){
        $model = new Campaign;
        $model->type = 'Email'; //default choice for now

        if(isset($_POST['Campaign'])){
            $model->setX2Fields($_POST['Campaign']);

            $model->content = Fields::getPurifier()->purify($model->content);
            $model->content = Formatter::restoreInsertableAttributes($model->content);
            $model->createdBy = Yii::app()->user->getName();
            if($model->save()){
                if(isset($_POST['AttachmentFiles'])){
                    if(isset($_POST['AttachmentFiles']['id'])){
                        foreach($_POST['AttachmentFiles']['id'] as $mediaId){
                            $attachment = new CampaignAttachment;
                            $attachment->campaign = $model->id;
                            $attachment->media = $mediaId;
                            $attachment->save();
                        }
                    }
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }elseif(isset($_GET['Campaign'])){
            //preload the create form with query params
            $model->setAttributes($_GET['Campaign']);
            $model->setX2Fields($_GET['Campaign']);
        }

        $this->render('create', array('model' => $model));
    }

    /**
     * Create a campaign for all contacts with a certain tag.
     *
     * This action will create and save the campaign and redirect the user to
     * edit screen to fill in the email message, etc.  It is intended to provide
     * a fast workflow from tags to campaigns.
     *
     * @param string $tag
     */
    public function actionCreateFromTag($tag){
        //enusre tag sanity
        if(empty($tag) || strlen(trim($tag)) == 0){
            Yii::app()->user->setFlash('error', Yii::t('marketing', 'Invalid tag value'));
            $this->redirect(Yii::app()->request->getUrlReferrer());
        }

        //ensure sacred hash
        if(substr($tag, 0, 1) != '#'){
            $tag = '#'.$tag;
        }

        //only works for contacts
        $modelType = 'Contacts';
        $now = time();

        //get all contact ids from tags
        $ids = Yii::app()->db->createCommand()
                ->select('itemId')
                ->from('x2_tags')
                ->where('type=:type AND tag=:tag')
                ->group('itemId')
                ->order('itemId ASC')
                ->bindValues(array(':type' => $modelType, ':tag' => $tag))
                ->queryColumn();

        //create static list
        $list = new X2List;
        $list->name = Yii::t('marketing', 'Contacts for tag').' '.$tag;
        $list->modelName = $modelType;
        $list->type = 'campaign';
        $list->count = count($ids);
        $list->visibility = 1;
        $list->assignedTo = Yii::app()->user->getName();
        $list->createDate = $now;
        $list->lastUpdated = $now;

        //create campaign
        $campaign = new Campaign;
        $campaign->name = Yii::t('marketing', 'Mailing for tag').' '.$tag;
        $campaign->type = 'Email';
        $campaign->visibility = 1;
        $campaign->assignedTo = Yii::app()->user->getName();
        $campaign->createdBy = Yii::app()->user->getName();
        $campaign->updatedBy = Yii::app()->user->getName();
        $campaign->createDate = $now;
        $campaign->lastUpdated = $now;

        $transaction = Yii::app()->db->beginTransaction();
        try{
            if(!$list->save())
                throw new Exception(array_shift(array_shift($list->getErrors())));
            $campaign->listId = $list->nameId;
            if(!$campaign->save())
                throw new Exception(array_shift(array_shift($campaign->getErrors())));

            foreach($ids as $id){
                $listItem = new X2ListItem;
                $listItem->listId = $list->id;
                $listItem->contactId = $id;
                if(!$listItem->save())
                    throw new Exception(array_shift(array_shift($listItem->getErrors())));
            }

            $transaction->commit();
            $this->redirect($this->createUrl('update', array('id' => $campaign->id)));
        }catch(Exception $e){
            $transaction->rollBack();
            Yii::app()->user->setFlash('error', Yii::t('marketing', 'Could not create mailing').': '.$e->getMessage());
            $this->redirect(Yii::app()->request->getUrlReferrer());
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);

        if(!isset($model)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        if(isset($_POST['Campaign'])){
            $oldAttributes = $model->attributes;
            $model->setX2Fields($_POST['Campaign']);
            $model->content = Fields::getPurifier()->purify($model->content);
            $model->content = Formatter::restoreInsertableAttributes($model->content);

            if($model->save()){
                CampaignAttachment::model()->deleteAllByAttributes(array('campaign' => $model->id));
                if(isset($_POST['AttachmentFiles'])){
                    if(isset($_POST['AttachmentFiles']['id'])){
                        foreach($_POST['AttachmentFiles']['id'] as $mediaId){
                            $attachment = new CampaignAttachment;
                            $attachment->campaign = $model->id;
                            $attachment->media = $mediaId;
                            $attachment->save();
                        }
                    }
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('update', array('model' => $model));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id){
        if(Yii::app()->request->isPostRequest){
            $model = $this->loadModel($id);

            if(!isset($model)){
                Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
                $this->redirect(array('index'));
            }
            // now in ChangeLogBehavior
            // $event=new Events;
            // $event->type='record_deleted';
            // $event->associationType=$this->modelClass;
            // $event->associationId=$model->id;
            // $event->text=$model->name;
            // $event->user=Yii::app()->user->getName();
            // $event->save();
            $list = $model->list;
            if(isset($list) && $list->type == "campaign")
                $list->delete();
            // $this->cleanUpTags($model);	// now in TagBehavior
            $model->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else{
            Yii::app()->user->setFlash('error', Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
            $this->redirect(array('index'));
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){
        $model = new Campaign('search');
        $this->render('index', array('model' => $model));
    }

    public function actionAdmin(){
        $this->redirect('index');
    }

    /**
     * Launches the specified campaign, activating it for mailing
     *
     * When a campaign is created, it is specified with an existing contact list.
     * When the campaign is lauched, this list is replaced with a duplicate to prevent
     * the original from being modified, and to allow campaign specific information to
     * be saved in the list.  This includes the email send time, and the times when a
     * contact has opened the mail or unsubscribed from the list.
     *
     * @param integer $id ID of the campaign to launch
     */
    public function actionLaunch($id){
        $campaign = $this->loadModel($id);

        if(!isset($campaign)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        if(!isset($campaign->list)){
            Yii::app()->user->setFlash('error', Yii::t('marketing', 'Contact List cannot be blank.'));
            $this->redirect(array('view', 'id' => $id));
        }

        if(empty($campaign->subject) && $campaign->type === 'Email'){
            Yii::app()->user->setFlash('error', Yii::t('marketing', 'Subject cannot be blank.'));
            $this->redirect(array('view', 'id' => $id));
        }

        if($campaign->launchDate != 0 && $campaign->launchDate < time()){
            Yii::app()->user->setFlash('error', Yii::t('marketing', 'The campaign has already been launched.'));
            $this->redirect(array('view', 'id' => $id));
        }

        if(($campaign->list->type == 'dynamic' && 
            X2Model::model($campaign->list->modelName)
                ->count($campaign->list->queryCriteria()) < 1) || 
            ($campaign->list->type != 'dynamic' && count($campaign->list->listItems) < 1)){

            Yii::app()->user->setFlash('error', Yii::t('marketing', 'The contact list is empty.'));
            $this->redirect(array('view', 'id' => $id));
        }

        //Duplicate the list for campaign tracking, leave original untouched
        //only if the list is not already a campaign list
        if($campaign->list->type != "campaign"){
            $newList = $campaign->list->staticDuplicate();
            if(!isset($newList)){
                Yii::app()->user->setFlash('error', Yii::t('marketing', 'The contact list is empty.'));
                $this->redirect(array('view', 'id' => $id));
            }
            $newList->type = 'campaign';
            if($newList->save()) {
                $campaign->list = $newList;
                $campaign->listId = $newList->nameId;
            } else {
                Yii::app()->user->setFlash('error', Yii::t('marketing', 'Failed to save temporary list.'));
            }
        }

        $campaign->launchDate = time();
        $campaign->save();

        Yii::app()->user->setFlash('success', Yii::t('marketing', 'Campaign launched'));
        $this->redirect(array('view', 'id' => $id, 'launch' => true));
    }

    /**
     * Deactivate a campaign to halt mailings, or resume paused campaign
     *
     * @param integer $id The ID of the campaign to toggle
     */
    public function actionToggle($id){
        $campaign = $this->loadModel($id);

        if(!isset($campaign)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        $campaign->active = $campaign->active ? 0 : 1;
        $campaign->save();
        $message = $campaign->active ? Yii::t('marketing', 'Campaign resumed') : Yii::t('marketing', 'Campaign paused');
        Yii::app()->user->setFlash('notice', Yii::t('app', $message));
        $this->redirect(array('view', 'id' => $id, 'launch' => $campaign->active));
    }

    /**
     * Forcibly complete a campaign despite any unsent mail
     *
     * @param integer $id The ID of the campaign to complete
     */
    public function actionComplete($id){
        $campaign = $this->loadModel($id);

        if(!isset($campaign)){
            Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('index'));
        }

        $campaign->active = 0;
        $campaign->complete = 1;
        $campaign->save();
        $message = Yii::t('marketing', 'Campaign complete.');
        Yii::app()->user->setFlash('notice', Yii::t('app', $message));
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Sends an individual email to an item in a campaign/newsletter list.
     *
     * @param type $campaignId
     * @param type $itemId
     */
    public function actionMailIndividual($campaignId,$itemId) {
        $this->itemId = $itemId;
        $this->campaign = Campaign::model()->findByPk($campaignId);
        $email = $this->recipient->email;
        if($this->campaign instanceof Campaign && $this->listItem instanceof X2ListItem) {
            $this->sendIndividualMail();
            $this->response['fullStop'] = $this->fullStop;
            $status = $this->status;
            // Actual SMTP (or elsewise) delivery error that should stop the batch:
            $error = ($status['code']!=200 && $this->undeliverable) || $this->fullStop;
            $this->response['status'] = $this->status;
            $this->respond($status['message'],$error);
        } else {
            $this->respond(Yii::t('marketing','Specified campaign does not exist.'),1);
        }
    }

    public function actionDoNotEmailLinkClick ($x2_key) {
        $contact = Contacts::model ()->findByAttributes (array (
            'trackingKey' => $x2_key,
        ));
        if ($contact !== null) {
            $contact->doNotEmail = true;
            if ($contact->update ()) {
                if (Yii::app()->settings->doNotEmailPage) {
                    echo Yii::app()->settings->doNotEmailPage;
                } else {
                    echo Admin::getDoNotEmailDefaultPage ();
                }
            }
        }

    }

    /**
     * Track when an email is viewed, a link is clicked, or the recipient unsubscribes
     *
     * Campaign emails include an img tag to a blank image to track when the message was opened,
     * an unsubscribe link, and converted links to track when a recipient clicks a link.
     * All those links are handled by this action.
     *
     * @param integer $uid The unique id of the recipient
     * @param string $type 'open', 'click', or 'unsub'
     * @param string $url For click types, this is the urlencoded URL to redirect to
     * @param string $email For unsub types, this is the urlencoded email address
     *  of the person unsubscribing
     */
    public function actionClick($uid, $type, $url = null, $email = null){
        // If the request is coming from within the web application, ignore it.
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $baseUrl = Yii::app()->request->getBaseUrl(true);
        $fromApp = strpos($referrer, $baseUrl) === 0;
        if ($fromApp && $type === 'open') {
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
            return;
        }

        $now = time();
        $item = CActiveRecord::model('X2ListItem')
            ->with('contact', 'list')->findByAttributes(array('uniqueId' => $uid));

        // It should never happen that we have a list item without a campaign,
        // but it WILL happen on any old db where x2_list_items does not cascade on delete
        // we can't track anything if the listitem was deleted, but at least prevent breaking links
        if($item === null || $item->list->campaign === null){
            if($type == 'click'){
                // campaign redirect link click
                $this->redirect(htmlspecialchars_decode($url, ENT_NOQUOTES));
            }elseif($type == 'open'){
                //return a one pixel transparent gif
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
            }elseif($type == 'unsub' && !empty($email)){
                Contacts::model()
                    ->updateAll(
                        array('doNotEmail' => true), 'email=:email', array(':email' => $email));
                X2ListItem::model()
                    ->updateAll(
                        array('unsubscribed' => time()), 
                        'emailAddress=:email AND unsubscribed=0', array('email' => $email));
                $message = Yii::t('marketing', 'You have been unsubscribed');
                echo '<html><head><title>'.$message.'</title></head><body>'.$message.
                    '</body></html>';
            }
            return;
        }

        $contact = $item->contact;
        $list = $item->list;
        if (!is_null($contact)) {
            $location = $contact->logLocation($type, false);
        }

        $event = new Events;
        $notif = new Notification;

        $action = new Actions;
        $action->completeDate = $now;
        $action->complete = 'Yes';
        $action->updatedBy = 'API';
        $skipActionEvent = true;

        if($contact !== null){
            $skipActionEvent = false;
            if($email === null)
                $email = $contact->email;

            $action->associationType = 'contacts';
            $action->associationId = $contact->id;
            $action->associationName = $contact->name;
            $action->visibility = $contact->visibility;
            $action->assignedTo = $contact->assignedTo;

            $event->associationId = $action->associationId;
            $event->associationType = 'Contacts';

            if($action->assignedTo !== '' && $action->assignedTo !== 'Anyone'){
                $notif->user = $contact->assignedTo;
                $notif->modelType = 'Contacts';
                $notif->modelId = $contact->id;
                $notif->createDate = $now;
                $notif->value = $item->list->campaign->getLink();
            }
        }elseif($list !== null){
            $action = new Actions;
            $action->type = 'note';
            $action->createDate = $now;
            $action->lastUpdated = $now;
            $action->completeDate = $now;
            $action->complete = 'Yes';
            $action->updatedBy = 'admin';

            $action->associationType = 'X2List';
            $action->associationId = $list->id;
            $action->associationName = $list->name;
            $action->visibility = $list->visibility;
            $action->assignedTo = $list->assignedTo;
        }

        if($type == 'unsub'){
            $item->unsubscribe();

            // find any weblists associated with the email address and create unsubscribe actions 
            // for each of them
            $sql = 
                'SELECT t.* 
                FROM x2_lists as t 
                JOIN x2_list_items as li ON t.id=li.listId 
                WHERE li.emailAddress=:email AND t.type="weblist";';
            $weblists = Yii::app()->db->createCommand($sql)
                ->queryAll(true, array('email' => $email));
            foreach($weblists as $weblist){
                $weblistAction = new Actions();
                $weblistAction->disableBehavior('changelog');
                //$weblistAction->id = 0; // this causes primary key contraint violation errors
                $weblistAction->isNewRecord = true;
                $weblistAction->type = 'email_unsubscribed';
                $weblistAction->associationType = 'X2List';
                $weblistAction->associationId = $weblist['id'];
                $weblistAction->associationName = $weblist['name'];
                $weblistAction->visibility = $weblist['visibility'];
                $weblistAction->assignedTo = $weblist['assignedTo'];
                $weblistAction->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".$email." ".
                    Yii::t('marketing', 'has unsubscribed').".";
                $weblistAction->save();
            }

            $action->type = 'email_unsubscribed';
            $notif->type = 'email_unsubscribed';

            if($contact === null) {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                $item->list->campaign->name."\n\n".$item->emailAddress.' '.
                Yii::t('marketing', 'has unsubscribed').".";
            } else {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".
                    Yii::t('marketing', 'Contact has unsubscribed').".\n".
                    Yii::t('marketing', '\'Do Not Email\' has been set').".";
            }

            $message = Yii::t('marketing', 'You have been unsubscribed');
            echo '<html><head><title>'.$message.'</title></head><body>'.$message.'</body></html>';
        } elseif($type == 'open'){
            //return a one pixel transparent gif
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
            // Check if it has been marked as opened already, or if the contact
            // no longer exists. If so, exit; nothing more need be done.
            if($item->opened != 0)
                Yii::app()->end();
            // This needs to happen before the skip option to accomodate the case of newsletters
            $item->markOpened(); 
            if($skipActionEvent)
                Yii::app()->end();
            $action->disableBehavior('changelog');
            $action->type = 'campaignEmailOpened';
            $event->type = 'email_opened';
            $notif->type = 'email_opened';
            $event->save();
            if($contact === null) {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".$item->emailAddress.' '.
                    Yii::t('marketing', 'has opened the email').".";
            } else {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".
                    Yii::t('marketing', 'Contact has opened the email').".";
            }
        } elseif($type == 'click'){
            // redirect link click
            $item->markClicked($url);

            $action->type = 'email_clicked';
            $notif->type = 'email_clicked';

            if($contact === null) {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".
                    Yii::t('marketing', 'Contact has clicked a link').":\n".urldecode($url);
            } else {
                $action->actionDescription = Yii::t('marketing', 'Campaign').': '.
                    $item->list->campaign->name."\n\n".$item->emailAddress.' '.
                    Yii::t('marketing', 'has clicked a link').":\n".urldecode($url);
            }
            $this->redirect(htmlspecialchars_decode($url, ENT_NOQUOTES));
        }

        if (isset($location))
            $action->locationId = $location->id;
        $action->save();
        // if any of these hasn't been fully configured
        $notif->save();  // it will simply not validate and not be saved
    }

    public function actionRemoveWebLeadFormCustomHtml () {
        if(!empty($_POST) && !empty ($_POST['id'])) {
            $model = WebForm::model()->findByPk ($_POST['id']);
            if ($model) {
                $model->header = '';
                if ($model->save ()) {
                    echo CJSON::encode (
                        array ('success', $model->attributes));
                    return;
                }
            }
        }
        echo CJSON::encode (
            array ('error', Yii::t('marketing', 'Custom HTML could not be removed.')));
    }

    public function actionSaveWebLeadFormCustomHtml () {
        if(!empty($_POST) && !empty ($_POST['id']) && !empty ($_POST['html'])){
            $model = WebForm::model()->findByPk ($_POST['id']);
            if ($model) {
                $model->header = $_POST['html'];
                if ($model->save ()) {
                    echo CJSON::encode (array ('success', $model->attributes));
                    return;
                }
            }
        }
        echo CJSON::encode (
            array ('error', Yii::t('marketing', 'Custom HTML could not be saved.')));
    }

    /**
     * Get the web tracker code to insert into your website
     */
    public function actionWebTracker(){
        $admin = Yii::app()->settings;
        if(isset($_POST['Admin']['enableWebTracker'], $_POST['Admin']['webTrackerCooldown'])){
            $admin->enableWebTracker = $_POST['Admin']['enableWebTracker'];
            $admin->webTrackerCooldown = $_POST['Admin']['webTrackerCooldown'];
            
            if (Yii::app()->contEd('pla')) {
                $settings = array(
                    'enableFingerprinting',
                    'identityThreshold',
                    'maxAnonContacts',
                    'maxAnonActions',
                    'performHostnameLookups',
                );
                foreach ($settings as $setting) {
                    if (isset($_POST['Admin'][$setting]))
                        $admin->$setting = $_POST['Admin'][$setting];
                }
            }
            
            $admin->save();
        }
        $this->render('webTracker', array('admin' => $admin));
    }

    /**
     * Export the web tracker code to upload to your website
     */
    public function actionExportWebTracker() {
        $srcFile = implode (DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'webTracker.php'));
        $dstFile = $this->attachBehavior('ImportExportBehavior', new ImportExportBehavior)
            ->safePath('webTracker.js');

        // Evaluate and capture webTracker.php
        ob_start ();
        require_once ($srcFile);
        $trackerCode = ob_get_contents ();
        ob_end_clean ();

        // Adjust webListener URL as it would be generated in an incorrect context
        $trackerCode = preg_replace ('/(index.php\/)?marketing\/exportWebTracker\//', '', $trackerCode);
        $trackerCode = preg_replace ('/return \'https?:\/\//', 'return \'//', $trackerCode);

        // Retrieve current license header
        $headerLength = 35; // in lines
        $fh = fopen ($srcFile, 'r');
        fgets ($fh); // skip first line (open PHP tag)
        $header = '';
        for ($i = 0; $i < $headerLength; $i++)
            $header .= fgets ($fh);
        fclose ($fh);

        // Write to temporary file and send to browser
        $fh = fopen ($dstFile, 'w');
        fwrite ($fh, $header);
        fwrite ($fh, $trackerCode);
        fclose ($fh);
        $this->sendFile ('webTracker.js', @file_get_contents($dstFile), 'text/javascript');
    }

	
	public function actionGetCampaignChartData(
		$id, $modelName, $startTimestamp, $endTimestamp) {
        echo CJSON::encode(CampaignChartWidget::getChartData (
			$id, $modelName, $startTimestamp, $endTimestamp));
    }
	

    /**
     * Create a menu for Marketing
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Contact = Modules::displayName(false, "Contacts");
        $modelId = isset($model) ? $model->id : 0;
        $marketingAdmin = Yii::app()->user->checkAccess ('MarketingAdminAccess');

        /**
         * To show all options:
         * $menuOptions = array(
         *     'all', 'create', 'view', 'edit', 'delete', 'lists', 'import', 'export',
         *     'newsletters', 'weblead', 'webtracker', 'x2flow', 'email',
         * );
         */
        
        /**
         * Additionally, the following platinum options can be used:
         * $plaOptions = array(
         *     'viewAnon', 'deleteAnon', 'anoncontacts', 'fingerprints'
         * );
         * $menuOptions = array_merge($menuOptions, $plaOptions);
         */
        

        $menuItems = array(
            array(
                'name'=>'all',
                'label'=>Yii::t('marketing','All Campaigns'),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('marketing','Create Campaign'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'viewAnon',
                'label' => Yii::t('module', 'View'),
                'url'=>array('anonContactView', 'id' => $modelId),
            ),
            array(
                'name'=>'edit',
                'label' => Yii::t('module', 'Update'),
                'url' => array('update', 'id' => $modelId)
            ),
            array(
                'name'=>'delete',
                'label' => Yii::t('module', 'Delete'),
                'url' => '#',
                'linkOptions' => array(
                    'submit' => array('delete', 'id' => $modelId),
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))
            ),
            array(
                'name' => 'deleteAnon',
                'label' => Yii::t('contacts', 'Delete'), 
                'url' => '#', 
                'linkOptions' => array(
                    'submit' => array('/marketing/anonContactDelete', 'id' => $modelId),
                    'confirm' => 'Are you sure you want to delete this anonymous contact?')
            ),
            array(
                'name'=>'lists',
                'label'=>Yii::t('contacts','{module} Lists', array('{module}'=>$Contact)),
                'url'=>array('/contacts/contacts/lists')),
            array(
                'name'=>'import',
                'label'=>Yii::t('marketing', 'Import Campaigns'),
                'url'=>array('admin/importModels', 'model'=>'Campaign'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('marketing', 'Export Campaigns'),
                'url'=>array('admin/exportModels', 'model'=>'Campaign'),
            ),
            array(
                'name'=>'newsletters',
                'label' => Yii::t('marketing', 'Newsletters'),
                'url' => array('/marketing/weblist/index'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
            array(
                'name'=>'weblead',
                'label' => Yii::t('marketing', 'Web Lead Form'),
                'url' => array('webleadForm')
            ),
            array(
                'name'=>'webtracker',
                'label' => Yii::t('marketing', 'Web Tracker'),
                'url' => array('webTracker'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
        
            array(
                'name'=>'anoncontacts',
                'label' => Yii::t('marketing', 'Anonymous Contacts'),
                'url'=>array('anonContactIndex'),
                'visible' => $marketingAdmin && Yii::app()->contEd('pla')
            ),
            array(
                'name'=>'fingerprints',
                'label' => Yii::t('marketing', 'Fingerprints'),
                'url' => array('fingerprintIndex'),
                'visible' => $marketingAdmin && Yii::app()->contEd('pla')
            ),
        
            array(
                'name'=>'x2flow',
                'label' => Yii::t('app', 'X2Workflow'),
                'url' => array('/studio/flowIndex'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
            array(
                'name'=>'email',
                'label' => Yii::t('app', 'Send Email'), 'url' => '#',
                'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
