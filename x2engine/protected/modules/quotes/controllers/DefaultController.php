<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

class DefaultController extends x2base {

	public $modelClass = 'Quote';
		
	public function accessRules() {
		return array(
                        array('allow',
                            'actions'=>array('getItems'),
                            'users'=>array('*'), 
                        ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index', 'view', 'create', 'quickCreate', 'update', 'quickUpdate', 'search', 'addUser', 'addContact', 'removeUser', 'removeContact', 'saveChanges', 'print', 'delete', 'quickDelete', 'addProduct', 'deleteProduct', 'shareQuote'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','testScalability'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
        
        public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_quotes WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
		
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$type = 'quotes';
		$model = $this->loadModel($id);
		$model->associatedContacts = Contacts::getContactLinks($model->associatedContacts);
				
		// find associated products and their quantities
		$quoteProducts = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$model->id));
		$orders = array(); // array of product-quantity pairs
		$total = 0; // total price for the quote
		foreach($quoteProducts as $qp) {
		    $price = $qp->price * $qp->quantity;
		    if($qp->adjustmentType == 'percent') {
		        $price += $price * ($qp->adjustment / 100);
		        $qp->adjustment = "{$qp->adjustment}%";
		    } else {
		    	$price += $qp->adjustment;
		    }
		    $orders[] = array(
		    	'name' => $qp->name,
		    	'id' => $qp->productId,
		    	'unit' => $qp->price,
		    	'quantity' => $qp->quantity,
				'adjustment' => $qp->adjustment,
		    	'price' => $price,
		    );
		    $order = end($orders);
		    $total += $order['price'];
		}
		
		$dataProvider = new CArrayDataProvider($orders, array(
		    'keyField'=>'name',
		    'sort'=>array(
		    	'attributes'=>array('name', 'unit', 'quantity', 'adjustment', 'price'),
		    ),
		    'pagination'=>array('pageSize'=>false),
		    
		));

		parent::view($model, $type, array('dataProvider'=>$dataProvider, 'total'=>$total));
	}
	
	public function actionShareQuote($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('quotes','Quote Record Details')." \n
".Yii::t('quotes','Name').": $model->name
".Yii::t('quotes','Description').": $model->description
".Yii::t('quotes','Quotes Stage').": $model->salesStage
".Yii::t('quotes','Lead Source').": $model->leadSource
".Yii::t('quotes','Probability').": $model->probability
".Yii::t('app','Link').": ".'http://'.Yii::app()->request->getServerName().$this->createUrl('/quotes/'.$model->id);
		
		$body = trim($body);

		$errors = array();
		$status = array();
		$email = '';
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('quotes','Quote Record Details');
			$email = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
			$body = $_POST['body'];
			// if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
			if($email === false)
				$errors[] = 'email';
			if(empty($body))
				$errors[] = 'body';
			
			if(empty($errors))
				$status = $this->sendUserEmail($email,$subject,$body);

			if(array_search('200',$status)) {
				$this->redirect(array('view','id'=>$model->id));
				return;
			}
			if($email === false)
				$email = $_POST['email'];
			else
				$email = $this->mailingListToString($email);
		}
		$this->render('shareQuote',array(
			'model'=>$model,
			'body'=>$body,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,'quotes'),
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}
	
	public function createQuote($model, $oldAttributes, $products){
		
		$model->createDate=time();
		$model->lastUpdated = time();
		$model->createdBy = Yii::app()->user->getName();
		$model->updatedBy = Yii::app()->user->getName();
		if($model->expectedCloseDate!=""){
				$model->expectedCloseDate=strtotime($model->expectedCloseDate);
		}
		
		$name=$this->modelClass;
		if($model->save()){
		
		    $changes=$this->calculateChanges($oldAttributes, $model->attributes, $model);
		    $this->updateChangelog($model,$changes);
		    if($model->assignedTo!=Yii::app()->user->getName()){
		        $notif=new Notifications;
		        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$model->assignedTo));
		        if(isset($profile))
		        	$notif->text="$profile->fullName has created a(n) ".$name." for you";
		        $notif->user=$model->assignedTo;
		        $notif->createDate=time();
		        $notif->viewed=0;
		        $notif->record="$name:$model->id";
		        $notif->save();
		
		    }
		   	
		   	// tie contacts to quote
		   	/*
		   	foreach($contacts as $contact) {
		   		$relate = new Relationships;
		   		$relate->firstId = $model->id;
		   		$relate->firstType = "quotes";
		   		$relate->secondId = $contact;
		   		$relate->secondType = "contacts";
		   		$relate->save();
		   	} */
		   	
		   	// tie products to quote
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
		}else{
		    return false;
		}
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new Quote;
		$users = UserChild::getNames();
		
		$currency = Yii::app()->params->currency;
		$productNames = Product::productNames();
		$productCurrency = Product::productCurrency();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote'])) {
                    foreach($_POST as $key=>$arr){
                            $pieces=explode("_",$key);
                            if(isset($pieces[0]) && $pieces[0]=='autoselect'){
                                $newKey=$pieces[1];
                                if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
                                    $val=$_POST[$newKey."_id"];
                                }else{
                                    $field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
                                    if(isset($field)){
                                        $type=ucfirst($field->linkType);
                                        if($type!="Contacts"){
                                            eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
                                        }else{
                                            $names=explode(" ",$arr);
                                            $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel))
                                            $val=$lookupModel->id;
                                        else
                                            $val=$arr;
                                    }
                                }
                                $model->$newKey=$val;
                            }
                        }
//			$this->render('test', array('model'=>$_POST));
            $temp=$model->attributes;
        	foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Quote'][$field])){
                                $model->$field=$_POST['Quote'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Quotes','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
        	        	
			$model->expirationDate = $this->parseDate($model->expirationDate);
        	
        	/*
        	if(isset($model->associatedContacts)) {
        		$contacts = $model->associatedContacts;
        		$model->associatedContacts = Quote::parseContacts($model->associatedContacts);
        	} else {
        		$contacts = array();
        	}
        	*/

			// get products
                $products = array();
                if(isset($_POST['ExistingProducts'])){
			$ids = $_POST['ExistingProducts']['id'];
			$prices = $_POST['ExistingProducts']['price'];
			$quantities = $_POST['ExistingProducts']['quantity'];
			$adjustments = $_POST['ExistingProducts']['adjustment'];
			
			foreach($ids as $key=>$id) {
				if($id != 0) { // remove blanks
					$products[$key]['id'] = $id;
					$products[$key]['name'] = $productNames[$id];
					$products[$key]['price'] = $prices[$key];
					$products[$key]['quantity'] = $quantities[$key];
					if(strchr($adjustments[$key], '%')) { // percent adjustment
						$products[$key]['adjustment'] = intval(str_replace("%", "", $adjustments[$key]));
						$products[$key]['adjustmentType'] = 'percent';
					} else {
						$products[$key]['adjustment'] = $adjustments[$key];
						$products[$key]['adjustmentType'] = 'linear';
					}
				}
			}
			if(!empty($products))
				$currency = $productCurrency[$products[0]['id']];
                }
        	$model->currency = $currency;

			
        	$this->createQuote($model, $temp, $products);
		}

		$products = Product::activeProducts();
		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'products'=>$products,
			'productNames'=>$productNames,
		));
	}
	
	// create a quote from a mini Create Quote Form
	public function actionQuickCreate() {
		
		if(isset($_POST['Quote'])) {
                    foreach($_POST as $key=>$arr){
                            $pieces=explode("_",$key);
                            if(isset($pieces[0]) && $pieces[0]=='autoselect'){
                                $newKey=$pieces[1];
                                if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
                                    $val=$_POST[$newKey."_id"];
                                }else{
                                    $field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
                                    if(isset($field)){
                                        $type=ucfirst($field->linkType);
                                        if($type!="Contacts"){
                                            eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
                                        }else{
                                            $names=explode(" ",$arr);
                                            $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel))
                                            $val=$lookupModel->id;
                                        else
                                            $val=$arr;
                                    }
                                }
                                $model->$newKey=$val;
                            }
                        }
//			$this->render('test', array('model'=>$_POST));
			$model = new Quote;
            $oldAttributes=$model->attributes;
        	foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Quote'][$field])){
                                $model->$field=$_POST['Quote'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Quotes','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
        	
			$model->expirationDate = $this->parseDate($model->expirationDate);
				    	
			$contacts = $_POST['associatedContacts']; // get contacts
			$contact = Contacts::model()->findByPk($contacts[0]);
			$model->associatedContacts = $contact->id;
			
			$redirect = $_POST['redirect'];
			
			// get product names
			$allProducts = Product::model()->findAll(array('select'=>'id, name, currency'));
			$productNames = array(0 => '');
			foreach($allProducts as $product) {
				$productNames[$product->id] = $product->name;
				$productCurrency[$product->id] = $product->currency;
			}
			$currency = Yii::app()->params->currency;

			
			// get products
			if(isset($_POST['ExistingProducts'])) {
				$ids = $_POST['ExistingProducts']['id'];
				$prices = $_POST['ExistingProducts']['price'];
				$quantities = $_POST['ExistingProducts']['quantity'];
				$adjustments = $_POST['ExistingProducts']['adjustment'];
				$products = array();
				foreach($ids as $key=>$id) {
					if($id != 0) { // remove blanks
						$products[$key]['id'] = $id;
						$products[$key]['name'] = $productNames[$id];
						$products[$key]['price'] = $prices[$key];
						$products[$key]['quantity'] = $quantities[$key];
						if(strchr($adjustments[$key], '%')) { // percent adjustment
							$products[$key]['adjustment'] = floatval(str_replace("%", "", $adjustments[$key]));
							$products[$key]['adjustmentType'] = 'percent';
						} else {
							$products[$key]['adjustment'] = $adjustments[$key];
							$products[$key]['adjustmentType'] = 'linear';
						}
					}
				}
			} else {
				$products = array();
			}
			
			if(!empty($products))
				$currency = $productCurrency[$products[0]['id']];
        	$model->currency = $currency;
			        	
			$model->createDate = time();
			$model->lastUpdated = time();
			$model->createdBy = Yii::app()->user->getName();
			$model->updatedBy = Yii::app()->user->getName();
			
			if($model->save()){
							
			    $changes=$this->calculateChanges($oldAttributes, $model->attributes, $model);
			    $this->updateChangelog($model,$changes);
			   	
			   	// tie contacts to quote
			   	/*
			   	foreach($contacts as $contactid) {
			   		$relate = new Relationships;
			   		$relate->firstId = $model->id;
			   		$relate->firstType = "quotes";
			   		$relate->secondId = $contactid;
			   		$relate->secondType = "contacts";
			   		$relate->save();
			   	} */
			   	
		   		// tie products to quote
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
		   		
				// generate history
				$action = new Actions;
				$action->associationType = 'contacts';
				$action->type = 'quotes';
				$action->associationId = $contact->id;
				$action->associationName = $contact->name;
				$action->assignedTo = Yii::app()->user->getName();
				$action->completedBy=Yii::app()->user->getName();
				$action->createDate = time();
				$action->dueDate = time();
				$action->completeDate = time();
				$action->visibility = 1;
				$action->complete='Yes';
				$created = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->createDate);
				$updated = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->lastUpdated);
				$expires = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->expirationDate);
			
				$description = "New Quote: <b>{$model->id}</b> {$model->name} ({$model->status})
				Created: <b>$created</b>
				Updated: <b>$updated</b> by <b>{$model->updatedBy}</b>
				Expires: <b>$expires</b>\n\n";

				$table = $model->productTable();
				$table = str_replace("\n", "", $table);
				$table = str_replace("\t", "", $table);
				$description .= $table;
				$action->actionDescription = $description;
				$action->save();
			}
			
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			$contact = Contacts::model()->findByPk($contacts[0]);
			$this->renderPartial('quoteFormWrapper', array('model'=>$contact), false, true);
		    
        }
	}
        
	public function updateQuote($model, $oldAttributes, $products) {
	    
	    $model->lastUpdated = time();
	    $model->updatedBy = Yii::app()->user->name;
	    
	    if($model->expectedCloseDate!=""){
	            $model->expectedCloseDate=strtotime($model->expectedCloseDate);
	    }
	    
	    $changes = $this->calculateChanges($oldAttributes, $model->attributes, $model);
	    $model = $this->updateChangelog($model,$changes);
	
	    if($model->save()) {
	    	
	    	// update contacts
	    	/*
	    	$relationships = Relationships::model()->findAllByAttributes(
	    		array(
	    			'firstType'=>'quotes', 
	    			'firstId'=>$model->id, 
	    			'secondType'=>'contacts'
	    		)
	    	);
	    	foreach($relationships as $relate)
	    		if($key = array_search($relate->secondId, $contacts))
	    			unset($contacts[$key]);
	    		else
	    			$relate->delete();
	    	
	   		// tie new contacts to quote
	   		/*
	   		foreach($contacts as $contact) {
	   			$relate = new Relationships;
	   			$relate->firstId = $model->id;
	   			$relate->firstType = "quotes";
	   			$relate->secondId = $contact;
	   			$relate->secondType = "contacts";
	   			$relate->save();
	   		}
	   		*/
	   		
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
	
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		
		$users=UserChild::getNames();
		
		// get associated contacts
		/*
		$relationships = Relationships::model()->findAllByAttributes(array('firstType'=>'quotes', 'firstId'=>$model->id, 'secondType'=>'contacts'));
		$selectedContacts = array();
		foreach($relationships as $relate) {
			$selectedContacts[] = $relate->secondId;
		}
		$model->associatedContacts = $selectedContacts; */
		$productNames = $model->productNames();
                $fields=Fields::model()->findAllByAttributes(array('modelName'=>"Quotes"));
                foreach($fields as $field){
                    if($field->type=='link'){
                        $fieldName=$field->fieldName;
                        $type=ucfirst($field->linkType);
                        if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
                            eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
                            if(isset($lookupModel))
                                $model->$fieldName=$lookupModel->name;
                        }
                    }elseif($field->type=='date'){
                        $fieldName=$field->fieldName;
                        $model->$fieldName=date("Y-m-d",$model->$fieldName);
                    }
                }

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote'])) {
                    foreach($_POST as $key=>$arr){
                            $pieces=explode("_",$key);
                            if(isset($pieces[0]) && $pieces[0]=='autoselect'){
                                $newKey=$pieces[1];
                                if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
                                    $val=$_POST[$newKey."_id"];
                                }else{
                                    $field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
                                    if(isset($field)){
                                        $type=ucfirst($field->linkType);
                                        if($type!="Contacts"){
                                            eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
                                        }else{
                                            $names=explode(" ",$arr);
                                            $lookupModel=Contacts::model()->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
                                        }
                                        if(isset($lookupModel))
                                            $val=$lookupModel->id;
                                        else
                                            $val=$arr;
                                    }
                                }
                                $model->$newKey=$val;
                            }
                        }
            $temp=$model->attributes;
        	foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Quote'][$field])){
                                $model->$field=$_POST['Quote'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Quotes','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
        	
        	$model->expirationDate = $this->parseDate($model->expirationDate);
        	
        	/*
        	if(isset($model->associatedContacts)) {
        		$contacts = $model->associatedContacts;
        		$model->associatedContacts = Quote::parseContacts($model->associatedContacts);
        	} else {
        		$contacts = array();
        	} */
			
			// get products
			if(isset($_POST['ExistingProducts'])) {
				$ids = $_POST['ExistingProducts']['id'];
				$prices = $_POST['ExistingProducts']['price'];
				$quantities = $_POST['ExistingProducts']['quantity'];
				$adjustments = $_POST['ExistingProducts']['adjustment'];
				$products = array();
				foreach($ids as $key=>$id) {
					if($id != 0) { // remove blanks
						$products[$key]['id'] = $id;
						$products[$key]['name'] = $productNames[$id];
						$products[$key]['price'] = $prices[$key];
						$products[$key]['quantity'] = $quantities[$key];
						if(strchr($adjustments[$key], '%')) { // percent adjustment
							$products[$key]['adjustment'] = floatval(str_replace("%", "", $adjustments[$key]));
							$products[$key]['adjustmentType'] = 'percent';
						} else {
							$products[$key]['adjustment'] = $adjustments[$key];
							$products[$key]['adjustmentType'] = 'linear';
						}
					}
				}
			} else {
				$products = array();
			}
			
        	$this->updateQuote($model, $temp, $products);

		}
//		if(!empty($model->expirationDate)) // format expiration date
//			$model->expirationDate = Yii::app()->dateFormatter->format('MMM dd, yyyy', $model->expirationDate);
		$products = $model->activeProducts();
		$orders = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$model->id));
		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'products'=>$products,
			'productNames'=>$productNames,
			'orders'=>$orders,
		));
	}
	
	public function actionQuickUpdate($id) {
		$model = $this->loadModel($id);

        foreach(array_keys($model->attributes) as $field){
                            if(isset($_POST['Quote'][$field])){
                                $model->$field=$_POST['Quote'][$field];
                                $fieldData=Fields::model()->findByAttributes(array('modelName'=>'Quotes','fieldName'=>$field));
                                if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
                                    $model->$field=Accounts::parseUsers($model->$field);
                                }elseif($fieldData->type=='date'){
                                    $model->$field=strtotime($model->$field);
                                }
                                
                            }
                        }
        
		$model->expirationDate = $this->parseDate($model->expirationDate);
    	
        $model->save();
        
		$allProducts = Product::model()->findAll(array('select'=>'id, name, price'));
		$productNames = array(0 => '');
		foreach($allProducts as $product) {
		    $productNames[$product->id] = $product->name;
		}
		
	    $model->lastUpdated = time();
	    $model->updatedBy = Yii::app()->user->name;
			
		// get products
		if(isset($_POST['ExistingProducts'])) {
		    $ids = $_POST['ExistingProducts']['id'];
		    $prices = $_POST['ExistingProducts']['price'];
		    $quantities = $_POST['ExistingProducts']['quantity'];
		    $adjustments = $_POST['ExistingProducts']['adjustment'];
		    $products = array();
		    foreach($ids as $key=>$id) {
		        if($id != 0) { // remove blanks
		        	$products[$key]['id'] = $id;
		        	$products[$key]['name'] = $productNames[$id];
		        	$products[$key]['price'] = $prices[$key];
		        	$products[$key]['quantity'] = $quantities[$key];
		        	if(strchr($adjustments[$key], '%')) { // percent adjustment
		        		$products[$key]['adjustment'] = floatval(str_replace("%", "", $adjustments[$key]));
		        		$products[$key]['adjustmentType'] = 'percent';
		        	} else {
		        		$products[$key]['adjustment'] = $adjustments[$key];
		        		$products[$key]['adjustmentType'] = 'linear';
		        	}
		        }
		    }
		
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
	   	}
	   	
	   	$contact = Contacts::model()->findByPk($_POST['contactId']);
	   	
		// generate history
		$action = new Actions;
		$action->associationType = 'contacts';
		$action->type = 'quotes';
		$action->associationId = $contact->id;
		$action->associationName = $contact->name;
		$action->assignedTo = Yii::app()->user->getName();
		$action->completedBy=Yii::app()->user->getName();
		$action->dueDate = time();
		$action->completeDate = time();
		$action->visibility = 1;
		$action->complete='Yes';
		$created = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->createDate);
		$updated = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->lastUpdated);
		$expires = Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $model->expirationDate);
		
		$description = "Updated Quote
		<span style=\"font-weight: bold; font-size: 1.25em;\">{$model->id}</span> {$model->name} ({$model->status})
		Created: <b>$created</b>
		Updated: <b>$updated</b> by <b>{$model->updatedBy}</b>
		Expires: <b>$expires</b>\n\n";
		
		$table = $model->productTable();
		$table = str_replace("\n", "", $table);
		$table = str_replace("\t", "", $table);
		$description .= $table;
		$action->actionDescription = $description;
		$action->save();
		
		if(isset($_POST['contactId'])) {
		    Yii::app()->clientScript->scriptMap['*.js'] = false;
		    $contact = Contacts::model()->findByPk($_POST['contactId']);
		    $this->renderPartial('quoteFormWrapper', array('model'=>$contact), false, true);
		}
	}
	
	
	public function actionPrint($id) {
		$model = $this->loadModel($id);
		
		if(isset($_POST['Quote'])) {
		
			if(isset($_POST['includeNotes']))
				$includeNotes = $_POST['includeNotes'];
			else
				$includeNotes = false;

			if(isset($_POST['includeLogo']))
				$includeLogo = $_POST['includeLogo'];
			else
				$includeLogo = false;

			if(isset($_POST['Quote']['description']))
				$notes = $_POST['Quote']['description'];
			else
				$notes = '';
			
			$this->renderPartial('print',
				array(
					'model'=>$model,
					'includeNotes'=>$includeNotes,
					'notes'=>$notes,
					'includeLogo'=>$includeLogo,
				)
			);
		} else {
			$this->renderPartial('printOptions', array('model'=>$model));
		}
	}

	public function actionAddUser($id) {
		$users=UserChild::getNames();
		$contacts=Contacts::getAllNames();
		$model=$this->loadModel($id);
		$users=Quote::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote'])) {
			$temp=$model->assignedTo; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Quote'];  
			$arr=$model->assignedTo;
			

			$model->assignedTo=Quote::parseUsers($arr);
			if($temp!="")
				$temp.=", ".$model->assignedTo;
			else
				$temp=$model->assignedTo;
			$model->assignedTo=$temp;
                        $changes=$this->calculateChanges($tempArr,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
			'action'=>'Add'
		));
	}

	public function actionAddContact($id) {
		$users=UserChild::getNames();
		$contacts=Contacts::getAllNames();
		$model=$this->loadModel($id);

		$contacts=Quote::editContactArray($contacts, $model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote'])) {
			$temp=$model->associatedContacts; 
            $tempArr=$model->attributes;
			$model->attributes=$_POST['Quote'];  
			$arr=$model->associatedContacts;
            foreach($arr as $contactId) {
                $rel=new Relationships;
                $rel->firstType='quotes';
                $rel->firstId=$model->id;
                $rel->secondType='contacts';
                $rel->secondId=$contactId;
                $rel->save();
            }
			

			$model->associatedContacts=Quote::parseContacts($arr);
			$temp.=" ".$model->associatedContacts;
			$model->associatedContacts=$temp;
                        $changes=$this->calculateChanges($tempArr,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array( 
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
			'action'=>'Add'
		));
	}

	public function actionRemoveUser($id) {

		$model=$this->loadModel($id);

		$pieces=explode(', ',$model->assignedTo);
		$pieces=Quote::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Quote'];  
			$arr=$model->assignedTo;
			
			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=Quote::parseUsersTwo($pieces);

			$model->assignedTo=$temp;
                        $changes=$this->calculateChanges($temp,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$pieces,
			'action'=>'Remove'
		));
	}

	public function actionRemoveContact($id) {

		$model=$this->loadModel($id);
		$pieces=explode(" ",$model->associatedContacts);
		$pieces=Quote::editContactsInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Quote']))
		{
            $temp=$model->attributes;
			$model->attributes=$_POST['Quote'];  
			$arr=$model->associatedContacts;
			
			
			foreach($arr as $id=>$contact){
            	$rel=CActiveRecord::model('Relationships')->findByAttributes(
            		array(
            			'firstType'=>'Contacts',
            			'firstId'=>$contact,
            			'secondType'=>'Quotes',
            			'secondId'=>$model->id
            		)
            	);
                if(isset($rel))
                	$rel->delete();
				unset($pieces[$contact]);
			}
			
			$temp2=Quote::parseContactsTwo($pieces);

			$model->associatedContacts=$temp2;
                        $changes=$this->calculateChanges($temp,$model->attributes);
                        $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array(
			'model'=>$model,
			'contacts'=>$pieces,
			'action'=>'Remove'
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Quote('search');
		$name='Quote';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new Quote('search');
		$name='Quote';
//		$this->render('test', array('model'=>$name));
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Quote::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
        
        public function delete($id){
            $model=$this->loadModel($id);
            $dataProvider=new CActiveDataProvider('Actions', array(
                    'criteria'=>array(
                    'condition'=>'associationId='.$id.' AND associationType=\'quote\'',
            )));
            $actions=$dataProvider->getData();
            foreach($actions as $action){
                    $action->delete();
            }
            $this->cleanUpTags($model);
            $model->delete();
        }

	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			
			// delete associated actions
			Actions::model()->deleteAllByAttributes(array('associationId'=>$id, 'associationType'=>'quotes'));
			// delete product relationships
			QuoteProduct::model()->deleteAllByAttributes(array('quoteId'=>$id));
			// delete contact relationships
			Relationships::model()->deleteAllByAttributes(array('firstType'=>'quotes', 'firstId'=>$id, 'secondType'=>'contacts'));
			
            $this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	public function actionQuickDelete($id) {
		$model=$this->loadModel($id);
		
		if($model) {

			// delete associated actions
			Actions::model()->deleteAllByAttributes(array('associationId'=>$id, 'associationType'=>'quotes'));
			// delete product relationships
			QuoteProduct::model()->deleteAllByAttributes(array('quoteId'=>$id));
			// delete contact relationships
			Relationships::model()->deleteAllByAttributes(array('firstType'=>'quotes', 'firstId'=>$id, 'secondType'=>'contacts'));

			$name = $model->name;

			// generate history
			
			$contact = Contacts::model()->findByPk($_GET['contactId']);

			$action = new Actions;
			$action->associationType = 'contacts';
			$action->type = 'quotes';
			$action->associationId = $contact->id;
			$action->associationName = $contact->name;
			$action->assignedTo = Yii::app()->user->getName();
			$action->completedBy=Yii::app()->user->getName();
			$action->createDate = time();
			$action->dueDate = time();
			$action->completeDate = time();
			$action->visibility = 1;
			$action->complete='Yes';
			$action->actionDescription = "Deleted Quote: <span style=\"font-weight:bold;\">{$model->id}</span> {$model->name}";
			$action->save();
	
            $this->cleanUpTags($model);
			$model->delete();
			
		}  else 
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
		
		if($_GET['contactId']) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			$contact = Contacts::model()->findByPk($_GET['contactId']);
			$this->renderPartial('quoteFormWrapper', array('model'=>$contact), false, true);
		}
	}
	
	// delete a product from a quote
	public function actionAddProduct($id) {
		$model=$this->loadModel($id);
				
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
			
			if(isset($_POST['contactId'])) {
				Yii::app()->clientScript->scriptMap['*.js'] = false;
				$contact = Contacts::model()->findByPk($_POST['contactId']);
				$this->renderPartial('quoteFormWrapper', array('model'=>$contact), false, true);
			}
		}
	}
		
	// delete a product from a quote
	public function actionDeleteProduct($id) {
		$model=$this->loadModel($id);
		
		if(isset($_GET['productId']))
			QuoteProduct::model()->deleteAllByAttributes(array('quoteId'=>$id, 'productId'=>$_GET['productId']));
		
		if($_GET['contactId']) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			$contact = Contacts::model()->findByPk($_GET['contactId']);
			$this->renderPartial('quoteFormWrapper', array('model'=>$contact), false, true);
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
}