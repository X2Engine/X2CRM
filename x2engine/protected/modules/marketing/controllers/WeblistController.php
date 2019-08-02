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
 * @package application.modules.marketing.controllers
 */
class WeblistController extends x2base {
	public $modelClass = 'X2List';

	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('index','view','create','update','delete'),
				'users'=>array('@'),
			),
		);
	}

	public function actionCreate() {
		if (isset($_POST['X2List'])) {
			$list = new X2List;
			$list->setAttributes($_POST['X2List']);
			$list->modelName = 'Contacts';
			$list->type = 'weblist';
			$list->createDate = time();
			$list->lastUpdated = time();

			//special case: "Default Newsletter" is system created only
			if ($list->name == "Default Newsletter") {
				if (Yii::app()->request->isAjaxRequest) {
					echo json_encode(array('errors'=>array('name'=>Yii::t('marketing','Name cannot be') .' "Default Newsletter"')));
				} else {
					Yii::app()->user->setFlash('error', Yii::t('marketing','Name cannot be') .' "Default Newsletter"');
					$this->redirect(array('index'));
				}
			}

			if ($list->save()) {
				if (!Yii::app()->request->isAjaxRequest)
					$this->redirect(array('index'));
			} else {
				if (Yii::app()->request->isAjaxRequest) {
					echo json_encode(array('errors'=>$list->getErrors()));
				} else {
					Yii::app()->user->setFlash('error', Yii::t('marketing','Name cannot be blank.'));
					$this->redirect(array('index'));
				}
			}
		} else {
			$this->redirect(array('index'));
		}
	}

	public function actionView($id) {
		$list = X2List::load($id);

		if (!isset($list)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('lists'));
		}

		if ($list->type != 'weblist') {
			$this->redirect(array('/contacts/contacts/list','id'=>$list->id));
		}

		$this->render('view', array('model'=>$list));
	}

	public function actionUpdate($id) {
		$model = X2List::load($id);

		if (!isset($model)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}

		if (isset($_POST['X2List'])) {
			$model->attributes = $_POST['X2List'];
			$model->lastUpdated = time();
			$model->save();
			$this->redirect(array('view', 'id'=>$id));
		}

		$this->render('update', array('model'=>$model));
	}

	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			$model = X2List::load($id);

			if (!isset($model)) {
				Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
				$this->redirect(array('index'));
			}

			$this->cleanUpTags($model);
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			}
		} else {
			Yii::app()->user->setFlash('error', Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
			$this->redirect(array('index'));
		}
	}

	public function actionIndex() {
		if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
			/* x2temp */
			$groupLinks = Yii::app()->db->createCommand()
                ->select('groupId')
                ->from('x2_group_to_user')
                ->where('userId='.Yii::app()->user->getId())->queryColumn();
			if(!empty($groupLinks))
				$condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';

			$condition .= ' OR (visibility=2 AND assignedTo IN
				(SELECT username FROM x2_group_to_user WHERE groupId IN
				(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().'))))';
		} else {
			$condition='';
		}
		$dataProvider = new SmartActiveDataProvider('X2List', array(
			'criteria'=>array('condition'=>'type="weblist"'.$condition),
			'sort'=>array('defaultOrder'=>'createDate DESC'),
		));
		$this->render('index', array('dataProvider'=>$dataProvider));
	}

	public function actionWeblist() {
		/* This action is largely the same as the /contacts/weblead, with some differences:
		 * - It creates a list item with an email address rather than a contact, though it may
                 *   reference an existing contact
		 * - Each entry is always associated with a list, using a default when no other specified
		 * - The web list form only takes email address as input
		 */
        if (!empty($_GET['webFormId']) && ctype_digit((string)$_GET['webFormId'])) {
            $webFormId = $_GET['webFormId'];
            $webForm = WebForm::model()->findByPk($webFormId);
        }
        $requireCaptcha = isset($webForm) ? $webForm->requireCaptcha : false;

		if (Yii::app()->request->isPostRequest) {
            Yii::app()->params->noSession = true;
			$now = time();
            $list = null;

            //look up list by id
            if (!empty($_GET['lid']) && ctype_digit((string)$_GET['lid'])) {
                $listId = $_GET['lid'];
                $list = X2List::model()->findByPk($listId, 'type="weblist"');
            }

			//look for "Default Newsletter"
			if (!isset($list)) {
				$list = X2List::model()->findByAttributes(
                    array('name'=>'Default Newsletter'), 'type="weblist"');
			}

			//create it otherwise
			if (!isset($list)) {
				$list = new X2List;
				$list->name = 'Default Newsletter';
				$list->description = Yii::t('contacts','Default list for email list signups');
				$list->type = 'weblist';
				$list->modelName = 'Contacts';
				$list->visibility = 1;
				$list->assignedTo = 'admin';
				$list->createDate = $now;
				$list->lastUpdated = $now;
				if (!$list->save()) $list = null;
			}

			//we just can't get a list, very unlikely
			if (!isset($list)) {
				$this->renderPartial('application.components.views.webFormSubmit',
					array('error'=>Yii::t('contacts','This request cannot be made at this time.')));
				return;
			}

			//require email field, check format
			$email = $_POST['Contacts']['email'];
			if (preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/", $email) == 0) {
				Yii::app()->user->setFlash(
                    'error', Yii::t('contacts','Could not sign up') .': '. Yii::t('contacts','Invalid Email Address'));
				$this->refresh();
			}

			//see if that email has already signed up
			$listItem = X2ListItem::model()->findByAttributes(
                array('emailAddress'=>$email, 'listId'=>$list->id));

			if (!isset($listItem)) {
				$listItem = new X2ListItem('WebForm');
				$listItem->listId = $list->id;
				$listItem->emailAddress = $email;
				$list->count++;
			}

            if ($requireCaptcha && CCaptcha::checkRequirements() &&
                array_key_exists('verifyCode', $_POST['Contacts']))
                    $listItem->verifyCode = $_POST['Contacts']['verifyCode'];

			//use the submitted info to create an action
			$action = new Actions;
			$action->actionDescription = Yii::t('contacts','Newsletter Signup') .": ". $email;
			$action->associationType = 'X2List';
			$action->associationId = $list->id;
			$action->associationName = $list->name;
			$action->type = 'note';
			$action->assignedTo = $list->assignedTo;
			$action->visibility = $list->visibility;
			$action->createDate = $now;
			$action->lastUpdated = $now;
			$action->completeDate = $now;
			$action->complete = 'Yes';
			$action->updatedBy = 'admin';

			//check for existing contact with email address
			$contact = Contacts::model()->findByAttributes(array('email'=>$email));

			if (isset($contact)) {
				//add contact to the list
				$listItem->contactId = $contact->id;

                $contact->recordAddress ();

				//create second action for contact record
				$contactAction = new Actions;
				$contactAction->setAttributes($action->getAttributes());
				$contactAction->actionDescription = Yii::t('contacts','Newsletter Signup') .": ".
                    $list->name;
				$contactAction->associationType = 'contacts';
				$contactAction->associationId = $contact->id;
				$contactAction->associationName = $contact->firstName ." ". $contact->lastName;
				$contactAction->assignedTo = $contact->assignedTo;
				$contactAction->visibility = $contact->visibility;
            } else {
                // Otherwise, search for or create an anonymous contact
                $fingerprint = (isset($_POST['fingerprint']))? $_POST['fingerprint'] : null;
                $attributes = (isset($_POST['fingerprintAttributes']))? json_decode($_POST['fingerprintAttributes'], true) : array();

                $contact = X2Model::model('AnonContact')->findByAttributes(array('email'=>$email));
                if ($contact === null) {
                    $fingerprintRecord = X2Model::model('Fingerprint')->findByAttributes(array('fingerprint'=>$fingerprint));
                    if ($fingerprintRecord !== null) {
                        // Locate the Contact of AnonContact associated with this fingerprint
                        $type = ($fingerprintRecord->anonymous) ? 'AnonContact' : 'Contacts';
                        $contact = X2Model::model($type)->findByAttributes(array('fingerprintId'=>$fingerprintRecord->id));
                    } else
                        list($contact, $bits) = Fingerprint::partialMatch($attributes);
                    if (!isset($contact)) {
                        $contact = new AnonContact();
                        $contact->createDate = $now;
                        $contact->trackingKey = Contacts::getNewTrackingKey();
                    }
                }

                $contact->email = $email;
                $contact->lastUpdated = $now;
                $contact->setFingerprint($fingerprint, $attributes);
                $contact->save();
                $contact->recordAddress ();
            }

			$transaction = Yii::app()->db->beginTransaction();
			try {
				if (!$listItem->save())
                    throw new Exception(array_shift(array_shift($listItem->getErrors())).'listiem');
				if (!$list->save())
                    throw new Exception(array_shift(array_shift($list->getErrors())));
				$action->save();
				if (isset($contactAction)) $contactAction->save();

				$transaction->commit();
                $sanitizedGetParams = WebFormAction::sanitizeGetParams ();
                $thankYouText = isset($webForm->thankYouText) ? $webForm->thankYouText : null;
				$this->renderPartial(
                    'application.components.views.webFormSubmit',
                    array_merge (array (
                        'type'=>'weblist',
                        'thankYouText' => $thankYouText,
                    ), $sanitizedGetParams));
			} catch (Exception $e) {
				$transaction->rollBack();
				Yii::app()->user->setFlash(
                    'error', Yii::t('contacts','Could not sign up') .': '. $e->getMessage());
				$this->refresh();
				//$this->redirect($this->createUrl('', array('lid'=>$list->id)));
			}
		} else {
            $sanitizedGetParams = WebFormAction::sanitizeGetParams ();
			$this->renderPartial(
                'application.components.views.webForm', 
                array_merge (
                    array(
                        'type'=>'weblist',
                        'requireCaptcha' => $requireCaptcha,
                    ),
                    $sanitizedGetParams
                )
            );
		}
	}

    /**
     * Create a menu for Weblist
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Contact = Modules::displayName(false, "Contacts");
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'all', 'create', 'view', 'edit', 'delete', 'lists',
         *     'newsletters', 'weblead', 'webtracker', 'x2flow',
         * );
         */
        
        /**
         * Additionally, the following platinum options can be used:
         * $plaOptions = array(
         *     'anoncontacts', 'fingerprints'
         * );
         * $menuOptions = array_merge($menuOptions, $plaOptions);
         */
        

        $menuItems = array(
            array(
                'name'=>'all',
                'label'=>Yii::t('marketing','All Campaigns'),
                'url'=>array('/marketing/marketing/index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('marketing','Create Campaign'),
                'url'=>array('/marketing/marketing/create')
            ),
            
            array(
                'name'=>'lists',
                'label'=>Yii::t('contacts','{module} Lists', array('{module}'=>$Contact)),
                'url'=>array('/contacts/contacts/lists')),
            array(
                'name'=>'newsletters',
                'label' => Yii::t('marketing', 'Newsletters'),
                'url' => array('index'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('module','View'),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('module','Update'),
                'url'=>array('update','id'=>$modelId),
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('module','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))
            ),
            array(
                'name'=>'weblead',
                'label' => Yii::t('marketing', 'Web Lead Form'),
                'url'=>array('/marketing/marketing/webleadForm'),
            ),
            
            array(
                'name'=>'webtracker',
                'label' => Yii::t('marketing', 'Web Tracker'),
                'url'=>array('/marketing/marketing/webTracker'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
        
            array(
                'name'=>'anoncontacts',
                'label' => Yii::t('marketing', 'Anonymous Contacts'),
                'url'=>array('/marketing/marketing/anonContactIndex'),
                'visible' => Yii::app()->params->isAdmin && Yii::app()->contEd('pla')
            ),
            array(
                'name'=>'fingerprints',
                'label' => Yii::t('marketing', 'Fingerprints'),
                'url' => array('/marketing/marketing/fingerprintIndex'),
                'visible' => Yii::app()->params->isAdmin && Yii::app()->contEd('pla')
            ),
        
            array(
                'name'=>'x2flow',
                'label' => Yii::t('app', 'X2Flow'),
                'url' => array('/studio/flowIndex'),
                'visible' => (Yii::app()->contEd('pro'))
            ),
            
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
 
    /**
     * Remove an individual email address from a newsletter list
     */
    public function actionRemoveFromList($email) {
		//look up list by id
		if (!empty($_GET['lid']) && ctype_digit((string)$_GET['lid'])) {
			$listId = $_GET['lid'];
			$list = X2List::model()->findByPk($listId, 'type="weblist"');

            Yii::app()->db->createCommand()
                ->delete ('x2_list_items', 'emailAddress = :email AND listId = :lid', array(
                    ':email' => $email,
                    ':lid' => $listId
                ));
            $this->redirect (array('view', 'id' => $listId));
		}
    }
}
