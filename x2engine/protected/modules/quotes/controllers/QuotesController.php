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
 * The Quote module lets users send people a quote with a list of products. Quote can be converted to invoices.
 *
 * Quote can be created, updated, deleted, and converted into invoices from the contacts view. The code
 * for that is in the file components/InlineQuote.php and is heavily based on ajax calls to this controller.
 *
 * The function actionConvertToInvoice handles both ajax and non-ajax calls. If called via ajax,
 * it will return the list of quotes for the contact id passed in the ajax call.
 *
 * @property Quote $model Model class being dealt with.
 * @package application.modules.quotes.controllers
 * @author David Visbal, Demitri Morgan <demitri@x2engine.com>
 */
class QuotesController extends x2base {

	public $modelClass = 'Quote';
    public function behaviors () {
         return array_merge (parent::behaviors (), array (

            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.'.
                    'MobileQuotesControllerBehavior'
            ),
            'MobileActionHistoryBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileActionHistoryBehavior'
            ),
         ));
    }
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id){
		$type = 'quotes';
		$model = $this->getModel($id);
        if (!$this->checkPermissions($model, 'view')) $this->denied ();
		$quoteProducts = $model->lineItems;
        // add quote to user's recent item list
        User::addRecentItem('q', $id, Yii::app()->user->getId()); 
        $contactNameId = Fields::nameAndId ($model->associatedContacts);
        $contactId = $contactNameId[1];
		parent::view($model, $type, array('orders' => $quoteProducts,
			'contactId' => $contactId
		));
	}
    /**
     * Return a set of copies of the specified quotes line items
     * @param Quote The quote whose line items are to be duplicated
     * @return array of line items
     */
    private function duplicateLineItems(Quote $quote) {
        $lineItems = array();
        foreach ($quote->lineItems as $item) {
            $copy = new QuoteProduct;

            foreach($item->attributes as $name => $value)

                if ($name !== 'id' && $name !== 'listId') {
                    $copy->$name = $value;
                }
            
            $lineItems[] = $copy;
        }
        return $lineItems;
    }
	/**
	 * Creates a new model.
	 *
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param bool $quick If true, this indicates the action is being requested via AJAX
	 */
	public function actionCreate($quick=false,$duplicate = false){
		$model = new Quote;
		if($duplicate && !isset ($_POST['Quote'])) {
			$copiedModel = Quote::model()->findByPk($duplicate);
			if(!empty($copiedModel)) {
				foreach($copiedModel->attributes as $name => $value)
					if($name != 'id')
						$model->$name = $value;
				$model->setLineItems ($this->duplicateLineItems($copiedModel), false, true);
			}
		}
		$users = User::getNames();
		if($quick && !Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);
		$currency = Yii::app()->params->currency;
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		if(isset($_POST['Quote'])){
			$model->setX2Fields($_POST['Quote']);
			$model->currency = $currency;
			$model->createDate = time();
			$model->lastUpdated = $model->createDate;
			$model->createdBy = Yii::app()->user->name;
			$model->updatedBy = $model->createdBy;
			if(empty($model->name))
				$model->name = '';
			if(isset($_POST['lineitem']))
				$model->lineItems = $_POST['lineitem'];
			if(!$model->hasLineItemErrors){
				if($model->save()){
					$model->createEventRecord();
					$model->createActionRecord();
					$model->saveLineItems();
					if(!$quick) {
						$this->redirect(array('view', 'id' => $model->id));
					} else {
                        if (isset ($_GET['recordId']) && isset ($_GET['recordType'])) {
                            $recordId = $_GET['recordId'];
                            $recordType = $_GET['recordType'];
                            $relatedModel = X2Model::model ($_GET['recordType'])->findByPk ($recordId);
                            $model->createRelationship($relatedModel);
                        }
						return;
                    }
				}
			}
		}
		// get products
		$products = Product::activeProducts();
		$viewData = array(
			'model' => $model,
			'users' => $users,
			'products' => $products,
			'quick' => $quick,
		);
		if(!$quick)
			$this->render('create', $viewData);
		else {
			if($model->hasErrors() || $model->hasLineItemErrors) {
				// Sneak into the response that validation failed via setting
				// the response code manually:
				header('HTTP/1.1 400 Validation Error');
			}
			$this->renderPartial('create', $viewData,false,true);
		}
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id,$quick=0){
		$model = $this->getModel($id);
		if(isset($_POST['Quote'])){
			$model->setX2Fields($_POST['Quote']);
			if(isset($_POST['lineitem']))
				$model->lineItems = $_POST['lineitem'];
			if(!$model->hasLineItemErrors) {
				if($model->save()) {
					$model->saveLineItems();
					$this->redirect(array('view','id' => $model->id));
				}
			}
		}
		$users=User::getNames();
		$products = $model->activeProducts();
		$quoteProducts = $model->lineItems;
		$viewData = array(
			'model' => $model,
			'users' => $users,
			'products' => $products,
			'orders' => $quoteProducts,
			'quick'=>$quick,
		);
		if(!$quick)
			$this->render('update', $viewData);
		else {
			if($model->hasErrors() || $model->hasLineItemErrors) {
				// Sneak into the response that validation failed via setting
				// the response code manually:
				header('HTTP/1.1 400 Validation Error');
			}
			$this->renderPartial('update', $viewData,false,true);
		}
	}
	/**
	 * Print a quote using a template or the legacy print view.
	 */
	public function actionPrint($id,$inline=false) {
        header('Content-type: text/html; charset=utf-8');
        if (!$inline) { 
            $this->layout = '//layouts/print';
        } else {
            $this->layout = false;
        }
		$this->render('printQuote', array(
			'id' => $id
		));
		return;
	}
	/**
	 * Generate presentation markup for the quote.
	 *
	 * @param integer $id ID of the quote for which to create a presentation
	 * @param bool $email Parameter passed to the item table markup generator
	 * @param string $header Optional header to pass to the print view
	 * @return type
	 */
	public function getPrintQuote($id = null,$email = false) {
        $this->throwOnNullModel = false;
		$model = $this->getModel($id);
		if($model == null)
			return Yii::t('quotes','Quote {id} does not exist. It may have been deleted.',array('{id}'=>$id));
		if (! ($model->templateModel instanceof Docs)) { // Legacy view (very, very plain!)
			return $this->renderPartial('print', array(
				'model' => $model,
				'email' => $email
			),true);
		} else { // User-defined template
			$template = $model->templateModel;
			if(!($template instanceof Docs)) {
				// Template not found (it was probably deleted).
				// Use the default quotes view.
				$model->template = null;
				$model->update(array('template'));
				return $this->getPrintQuote($model->id);
			}
			return Docs::replaceVariables($template->text,$model);
		}
	}
	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Quote('search');
		$this->render('index', array('model'=>$model));
	}
	/**
	 * Lists all models.
	 *
	 *  This is a separate list for invoices. An invoice is a quote
	 *  with field type='invoice'. The only difference is that when listing,
	 *  printing, or emailing an invoice, we call it an invoice instead of a
	 *  quote.
	 */
	public function actionIndexInvoice() {
		$model=new Quote('search');
		$this->render('indexInvoice', array('model'=>$model));
	}
	public function delete($id){
		$model = $this->getModel($id);
		$dataProvider = new CActiveDataProvider('Actions', array(
					'criteria' => array(
						'condition' => 'associationId='.$id.' AND associationType=\'quote\'',
						)));
		$actions = $dataProvider->getData();
		foreach($actions as $action){
			$action->delete();
		}
		$this->cleanUpTags($model);
		$model->delete();
	}
	public function actionDelete($id) {
		$model=$this->getModel($id);
		if(Yii::app()->request->isPostRequest) {
            $this->cleanUpTags($model);

            $model->delete();
        } else
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    /**
     *  Convert the Quote into an Invoice
     *  An invoice is a quote with field type='invoice'. The only difference is that
     *  when listing, printing, or emailing an invoice, we call it an invoice instead
     *  of a quote.
     *
     *  @param $id id of the quote to convert to invoice
     *
     */
    public function actionConvertToInvoice($id) {
        $model = $this->getModel($id); // get model
        // convert to invoice
        $model->type = 'invoice';
        $model->invoiceCreateDate = time();

        // set invoice status to the top choice in the invoice status drop down
        $field = $model->getField('invoiceStatus');
        if ($field) {
            $dropDownId = $field->linkType;
            if ($dropDownId) {
                $dropdowns = Dropdowns::getItems($field->linkType);
                if ($dropdowns) {
                    reset($dropdowns);
                    $status = key($dropdowns);
                    if ($status) {
                        $model->invoiceStatus = $status;
                    }
                }
            }
        }

        $model->save();

        // ajax request from a contact view, don't reload page, instead return a list of quotes 
        // for this contact
        if (isset($_GET['modelName']) && isset($_GET['recordId'])) {
            if ($model) {

                Yii::app()->clientScript->scriptMap['*.js'] = false;
                $this->renderPartial(
                    'quoteFormWrapper', 
                    array(
                        'modelId'=>$_GET['recordId'],
                        'modelName'=>$_GET['modelName']
                    ), false, true);
                return;
            }
		}
		$this->redirect(array('view','id'=>$model->id)); // view quote
	}
	/**
	 * Obtain the markup for the inline quotes widget.
	 *
	 * @param type $contactId Contact ID to use for displaying quotes
	 */
	public function actionViewInline($recordId, $recordType){
		Yii::app()->clientScript->scriptMap['*.js'] = false;
        $model = X2Model::model ($recordType)->findByPk ($recordId);
		$this->renderPartial(
            'quoteFormWrapper', 
            array(
                'modelId' => $model->id,
                'modelName' => $recordType
            ), false, true
        );
	}
	public function updateQuote($model, $oldAttributes, $products) {
	    $model->lastUpdated = time();
	    $model->updatedBy = Yii::app()->user->name;
	    if($model->save()) {
	   		// update products
	   		$orders = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$model->id));
	   		foreach($orders as $order) {
	   			$found = false;
	   			foreach($products as $key=>$product) {
	   				if($order->productId == $product['id']) {
	   					$order->price = $product['price'];
	   					$order->quantity = $product['quantity'];
	   					$order->adjustment = $product['adjustment'];
	   					$order->adjustmentType = $product['adjustmentType'];
	   					$order->save();
	   					unset($products[$key]);
	   					$found = true;
	   					break;
	   				}
	   			}
	   			if(!$found)
	   				$order->delete();
	   		}
	   		// tie new products to quote
	   		foreach($products as $product) {
		   		$qp = new QuoteProduct;
		   		$qp->quoteId = $model->id;
		   		$qp->productId = $product['id'];
		   		$qp->name = $product['name'];
		   		$qp->price = $product['price'];
		   		$qp->quantity = $product['quantity'];
		   		$qp->adjustment = $product['adjustment'];
		   		$qp->adjustmentType = $product['adjustmentType'];
		   		$qp->save();
	   		}
			$this->redirect(array('view','id'=>$model->id));
	    } else {
		    return false;
		}
	}
	public function actionQuickDelete($id) {
		$model=$this->getModel($id);
		if($model) {
            $this->cleanUpTags($model);
			$model->delete();
		}  else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
	}
	// delete a product from a quote
	public function actionAddProduct($id) {
		$model=$this->getModel($id);
		if(isset($_POST['ExistingProducts'])) {
			// get products
			$ids = $_POST['ExistingProducts']['id'];
			$quantities = $_POST['ExistingProducts']['quantity'];
			$products = array();
			foreach($ids as $key=>$id) {
				if($id != 0) { // remove blanks
					$products[$key]['id'] = $id;
					$products[$key]['quantity'] = $quantities[$key];
				}
			}
			// tie products to quote
			foreach($products as $product) {
			    $qp = new QuoteProduct;
			    $qp->quoteId = $model->id;
			    $qp->productId = $product['id'];
			    $qp->quantity = $product['quantity'];
			    $qp->save();
			}
			if(isset($_POST['recordId'])) {
				Yii::app()->clientScript->scriptMap['*.js'] = false;
				$contact = X2Model::model('Contacts')->findByPk($_POST['recordId']);
				$this->renderPartial(
                    'quoteFormWrapper', 
                    array(
                        'recordId'=>$contact->id,'accountName'=>$contact->company
                    ), false, true
                );
			}
		}
	}
	// delete a product from a quote
	public function actionDeleteProduct($id) {
		$model=$this->getModel($id);
		if(isset($_GET['productId']))
			QuoteProduct::model()->deleteAllByAttributes(array('quoteId'=>$id, 'productId'=>$_GET['productId']));
		if($_GET['contactId']) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			$contact = X2Model::model('Contacts')->findByPk($_GET['contactId']);
			$this->renderPartial('quoteFormWrapper', array('contactId'=>$contact->id,'accountName'=>$contact->company), false, true);
		}
	}
	public function actionGetTerms(){
		$sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
    public function actionGetItems ($term) {
        LinkableBehavior::getItems ($term);
    }
    /**
     * Create a menu for Quotes
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Quotes = Modules::displayName();
        $Quote = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;
        $isInvoice = (isset($model) && $model->type === 'invoice');
        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'invoices', 'create', 'view', 'email', 'edit', 'editLock', 'editStrictLock',
         *     'delete', 'attach', 'print', 'import', 'export', 'convert', 'duplicate'
         * );
         */
        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('quotes','{module} List', array(
                    '{module}'=>$Quotes
                )),
                'url' => array('index'),
            ),
            array(
                'name'=>'invoices',
                'label'=>Yii::t('quotes','Invoice List'),
                'url'=>array('indexInvoice')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('quotes','Create'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'email',
                'label'=>Yii::t('app','Email {type}', array(
                    '{type}' => ((isset($model) && $model->type=='invoice') ? 'Invoice' : $Quote)
                )),
                'url'=>'#','linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;'),
            ),
            array(
                'name'=>'editStrictLock',
                'label'=>Yii::t('quotes','Update'),
                'url'=>'#',
                'linkOptions'=>array('onClick'=>'dialogStrictLock();')
            ),
            array(
                'name'=>'editLock',
                'label'=>Yii::t('quotes','Update'),
                'url'=>'#',
                'linkOptions'=>array('onClick'=>'dialogLock();')
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('quotes','Update'),
                'url'=>array('update', 'id'=>$modelId),
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('quotes','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?'
                ),
            ),
			ModelFileUploader::menuLink(),
            array(
                'name'=>'print',
                'label'=>((isset($model) && $model->type == 'invoice') ? 
                    Yii::t('quotes', 'Print Invoice') : Yii::t('quotes','Print {quote}', array(
                        '{quote}' => $Quote,
                    ))),
                'url'=>'#',
                'linkOptions'=>array(
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/quotes/quotes/print',
                        array('id'=>$modelId)) ."')"
                )
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('quotes', 'Import {module}', array(
                    '{module}' =>$Quotes,
                )),
                'url'=>array('admin/importModels', 'model'=>'Quote'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('quotes', 'Export {module}', array(
                    '{module}' => $Quotes,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Quote'),
            ),
            array(
                'name' => 'convert',
                'label' => Yii::t('quotes', 'Convert To Invoice'),
                'url' => array ('convertToInvoice', 'id' => $modelId),
                'visible' => !$isInvoice,
            ),
            array(
                'name' => 'duplicate',
                'label' => Yii::t('quotes', 'Duplicate'),
                'url' => array ('create', 'duplicate' => $modelId),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );
        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}