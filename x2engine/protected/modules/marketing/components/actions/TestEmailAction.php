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
 * Simulates campaign email delivery 
 */

class TestEmailAction extends CAction {

	public function getBehaviors(){
		return array(
            'CampaignMailingBehavior' => array(
                'class' => 'application.modules.marketing.components.CampaignMailingBehavior'
            ),
			'responds' => array(
				'class' => 'application.components.ResponseBehavior',
                'errorCode' => 200
			),
		);
	}

	public function run(){
            
        if (Yii::app()->user->isGuest) {
            Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/site/login'));
        }

        $this->attachBehaviors($this->behaviors);
        // Safety net of handlers - they ensure that errors can be caught and seen easily:
        
        $scenario = 'custom';
        $model = new TestEmailActionForm ();
        $model->contactFlag = true;
		$model->setScenario($scenario);
		if(!isset($_POST['InlineEmail'])){
            throw new CHttpException (400, Yii::t('marketing', 'Bad request'));
        }

        $model->attributes = $_POST['InlineEmail'];
        $responseMessage = '';
        $sendStatus = array_fill_keys(array('code','message'),'');
        $failed = false;
        if ($model->validate ()) {
            $model->campaign->content = $model->message;
            $model->campaign->sendAs = $model->credId;
            $this->asa ('CampaignMailingBehavior')->setCampaign ($model->campaign);
            
        /*
         * MISSING ATTRIBUTES CHECK
         */
        
        // Check for null attributes
        $nullVars = Formatter::findNullVariables($model->campaign->content, $model->getTargetModel ());
        if (!empty($nullVars)) {
            $message = '';
            foreach ($nullVars as $val) {
                $message .= $val . 'is null.</br>';
            }
            $this->respond($message, true);
        };
        
            
            list($subject,$message,$uniqueId) = 
                self::prepareEmail ($model->campaign, $model->getTargetModel ());

            $this->deliverEmail ($model->mailingList, $model->subject, $message);
            $sendStatus['code'] = $this->asa ('CampaignMailingBehavior')->status['code'];
            $sendStatus['message'] = $this->asa ('CampaignMailingBehavior')->status['message'];
            if ($this->asa ('CampaignMailingBehavior')->status['code'] == 200) {
                $responseMessage = Yii::t(
                    'marketing','Test email sent successfully to {address}.',
                    array('{address}' => $model->to));
            } else {
                $responseMessage = Yii::t(
                    'marketing','Test email sent could not be sent to {address}.',
                    array('{address}' => $model->to));
                $failed = true;
            }
        }

        // Populate response data:
        $modelHasErrors = $model->hasErrors();
        $failed = $failed || $modelHasErrors;
        $model->attachments = array (); // prevent response json encoding failures
        $response = array(
            'scenario' => $scenario,
            'sendStatus' => $sendStatus,
            'attributes' => $model->attributes,
            'modelErrors' => $model->errors,
            'failed' => $failed,
            'modelHasErrors' => $modelHasErrors,
            'modelErrorHtml' => CHtml::errorSummary(
                $model,Yii::t('app', "Please fix the following errors:"),
                null,
                array('style'=>'margin-bottom: 5px;', 'class' => '')),
        );
        $this->mergeResponse($response);
        $this->respond($responseMessage,$failed);
	}
}

/**
 * Adds test campaign-specific fields to InlineEmail
 */

class TestEmailActionForm extends InlineEmail {
    public $campaignId;
    public $campaign;
    public $modelName = 'Contacts';
    public $recordName;

    public function rules () {
        return array_merge (parent::rules (), array (
            array (
                'campaignId', 'required'
            ),
            array (
                'modelName', 'required'
            ),
            array (
                'recordName', 'safe'
            ),
            array (
                'modelName', 'validateModelName'
            ),
            array (
                'campaignId', 'validateCampaignId'
            ),
        ));
    }

    /**
     * Allow for record name lookup, in addition to default id lookup
     */
    public function validateModelName ($attr) {
        $value = $this->$attr;
        $contact = null;
        if (isset ($this->modelId) && is_numeric ($this->modelId)) {
            $contact = Contacts::model ()->findByPk ($this->modelId);
        } else if (isset ($this->recordName)) {
            $contact = Contacts::model ()->findByAttributes (array (
                'name' => $this->recordName
            ));
        }
        if (!$contact) $this->addError ($attr, Yii::t('app', 'Contact not found'));
        else $this->setTargetModel ($contact);
    }

    public function validateCampaignId ($attr) {
        $value = $this->$attr;
        $campaign = Campaign::model ()->findByPk ($value);
        if (!$campaign) {
            throw new CHttpException (400, Yii::t('marketing', 'Bad request'));
        }
        $this->campaign = $campaign;
    }
}

?>
