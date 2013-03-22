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
 * Provides an inline form for sending email from a view page.
 * 
 * @package X2CRM.components 
 */
class InlineEmailForm extends X2Widget {

	public $model;
	public $attributes;
	
	public $insertableAttributes = null;

	public $errors = array();
	public $startHidden = false;

	public function init() {
		// $this->startHidden = false;
	
		$this->model = new InlineEmail;
		$this->model->attributes = $this->attributes;
		$signature = Yii::app()->params->profile->getSignature(true);
		
		//if message comes prepopulated, don't overwrite with signature
		if (empty($this->model->message)) {
			$this->model->message = empty($signature)? '' : '<br><br><!--BeginSig--><font face="Arial" size="2">'.$signature.'</font><!--EndSig-->';
		}
		
		// die(var_dump($this->model->attributes));
		
		if(isset($_POST['InlineEmail'])) {
			$this->model->attributes = $_POST['InlineEmail'];
			$this->startHidden = false;
		}

		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/inlineEmailForm.js');
		// var_dump($this->insertableAttributes);
		if($this->insertableAttributes !== null) { // && !empty($this->model->attributes['modelName'])) {
			// $this->insertableAttributes = array(
				// Yii::t('contacts','Contact Attributes')=>X2Model::model($this->model->attributes['modelName'])->attributeLabels()
			// );
			Yii::app()->clientScript->registerScript('setInsertableAttributes',
				'x2.insertableAttributes = '.CJSON::encode($this->insertableAttributes).';',
			CClientScript::POS_HEAD);
		}
		
		
		
 		Yii::app()->clientScript->registerScript('toggleEmailForm',
		($this->startHidden? "window.hideInlineEmail = true;\n" : "window.hideInlineEmail = false;\n"),CClientScript::POS_HEAD);

		parent::init();
	}

	public function run() {
		$action = new InlineEmailAction($this->controller,'inlineEmail');
		$action->model = &$this->model;
		$action->run(); 
	}
}
