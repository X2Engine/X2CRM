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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_quotes".
 * @package X2CRM.modules.quotes.models
 */
class Quote extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Quotes the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_quotes';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array_merge(parent::relations(),array(
			'products'=>array(self::HAS_MANY, 'QuoteProduct', 'quoteId'),
		));
	}

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'quotes'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

	public static function statusList() {
		$field = Fields::model()->findByAttributes(array('modelName'=>'Quote', 'fieldName'=>'status'));
		$dropdown = Dropdowns::model()->findByPk($field->linkType);
		return json_decode($dropdown->options,true);
		
		/*
		return array(
		    'Draft'=>Yii::t('quotes','Draft'),
		    'Presented'=>Yii::t('quotes','Presented'),
		    "Issued"=>Yii::t('quotes','Issued'),
		    "Won"=>Yii::t('quotes','Won')
		); */
	}

	public function productTable($emailTable = false) {
		$tableStyle = 'border-collapse: collapse; width: 100%;';
		$thStyle = 'border: 1px solid black; background:#eee;';
		$thProductStyle = $thStyle;
		if(!$emailTable)
			$tableStyle .= 'display: inline;';
		else
			$thProductStyle .=  "width:60%;";

		$tdStyle = 'border-left: 1px solid black; border-right: 1px solid black; padding: 5px;';
		$tdFooterStyle = "border-top: 1px solid black; border-spacing: 0; border-left: 0; border-right: 0; padding: 7px 0 0 0;";

		$table = "
<table style=\"$tableStyle\">
    <thead>
    	<tr>
    		<th style=\"$thProductStyle\">".Yii::t('products','Line Item')."</th>
    		<th style=\"$thStyle\">".Yii::t('products','Unit Price')."</th>
    		<th style=\"$thStyle\">".Yii::t('products','Quantity')."</th>
    		<th style=\"$thStyle\">".Yii::t('products', 'Adjustment')."</th>
    		<th style=\"$thStyle\">".Yii::t('products', "Price")."</th>
    	</tr>
    </thead>
    <tbody>";
		$quotesProducts = QuoteProduct::model()->findAllByAttributes(array('quoteId'=>$this->id));
		$orders = array(); // array of product-quantity pairs
		$total = 0; // total price for the quote
		foreach($quotesProducts as $qp) {
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
		    	'quantity'=> $qp->quantity,
		    	'adjustment' => $qp->adjustment,
		    	'price' => $price,
		    );
		    $order = end($orders);
		    $total += $order['price'];
		}
		
		foreach($orders as $order) {
		    $table .= "
		<tr>
		    <td style=\"$tdStyle\">{$order['name']}</td>
		    <td style=\"$tdStyle\">".Yii::app()->locale->numberFormatter->formatCurrency($order["unit"],$this->currency)."</td>
		    <td style=\"$tdStyle\">{$order['quantity']}</td>
		    <td style=\"$tdStyle\">{$order['adjustment']}</td>
		    <td style=\"$tdStyle\">".Yii::app()->locale->numberFormatter->formatCurrency($order["price"],$this->currency)."</td>
		</tr>";
		}
			
		$table .= "
    	<tr>
    		<td style=\"$tdFooterStyle\"></td>
    		<td style=\"$tdFooterStyle\"></td>
    		<td style=\"$tdFooterStyle\"></td>
    		<td style=\"$tdFooterStyle\"><hr style=\"width: 100%;height:2px;background:black;\" /><b>Total</b></td>
    		<td style=\"$tdFooterStyle\"><hr style=\"width: 100%;height:2px;background:black;\" /><b>".Yii::app()->locale->numberFormatter->formatCurrency($total,$this->currency)."</b></td>
    	</tr>
    </tbody>
</table>";
		
//		$table = str_replace("\n", "", $table);
//		$table = str_replace("\t", "", $table);
		
		return $table;
	}

	public static function getNames() {
	
		$names = array(0=>"None");
		
		foreach(Yii::app()->db->createCommand()->select('id,name')->from('x2_quotes')->queryAll(false) as $row)
			$names[$row[0]] = $row[1];

		return $names;
	}

	public static function parseUsers($userArray){
		return implode(', ',$userArray);
	}

	public static function parseUsersTwo($arr){
		$str="";
		foreach($arr as $user=>$name){
			$str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
						
		return $str;
	}

	public static function parseContacts($contactArray){
		return implode(' ',$contactArray);
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function getQuotesLinks($accountId) {

		$quotesList = X2Model::model('Quote')->findAllByAttributes(array('accountName'=>$accountId));
		// $quotesList = $this->model()->findAllByAttributes(array('accountId'),'=',array($accountId));
		
		$links = array();
		foreach($quotesList as $model) {
			$links[] = CHtml::link($model->name,array('quotes/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

	public static function editContactArray($arr, $model) {

		$pieces=explode(" ",$model->associatedContacts);
		unset($arr[0]);

		foreach($pieces as $contact){
			if(array_key_exists($contact,$arr)){
				unset($arr[$contact]);
			}
		}
		
		return $arr;
	}

	public static function editUserArray($arr, $model) {

		$pieces=explode(', ',$model->assignedTo);
		unset($arr['Anyone']);
		unset($arr['admin']);
		foreach($pieces as $user){
			if(array_key_exists($user,$arr)){
				unset($arr[$user]);
			}
		}
		return $arr;
	}

	public static function editUsersInverse($arr) {
		
		$data=array();
		
		foreach($arr as $username){
			if($username!='')
				$data[]=User::model()->findByAttributes(array('username'=>$username));
		}
		
		$temp=array();
		if(isset($data)){
			foreach($data as $item){
				if(isset($item))
					$temp[$item->username]=$item->firstName.' '.$item->lastName;
			}
		}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data=array();
		
		foreach($arr as $id){
			if($id!='')
				$data[]=X2Model::model('Contacts')->findByPk($id);
		}
		$temp=array();
		
		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
	}

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
		$criteria->addCondition("type!='invoice' OR type IS NULL");

		return $this->searchBase($criteria);
	}
	
	public function searchInvoice() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
		$criteria->addCondition("type='invoice'");

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
	
	public function searchBase($criteria) {

		$dateRange = Yii::app()->controller->partialDateRange($this->expectedCloseDate);
		if($dateRange !== false)
			$criteria->addCondition('expectedCloseDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
        
        if($criteria === null)
			$criteria = $this->getAccessCriteria();
		else
			$criteria->mergeWith($this->getAccessCriteria());

		return parent::searchBase($criteria);
	}
	
	
	/**
	 * Get all active products indexed by their id,
	 * and any inactive products still in this quote
	 */
	public function productNames() {
		$products = Product::model()->findAll(
			array(
				'select'=>'id, name',
				'condition'=>'status=:active',
				'params'=>array(':active'=>'Active'),
			)
		);
		$productNames = array(0 => '');
		foreach($products as $product)
			$productNames[$product->id] = $product->name;
		
		// get any inactive products in this quote
		$quoteProducts = QuoteProduct::model()->findAll(
			array(
				'select'=>'productId, name',
				'condition'=>'quoteId=:quoteId',
				'params'=>array(':quoteId'=>$this->id),
			)
		);
		foreach($quoteProducts as $qp)
			if(!isset($productNames[$qp->productId]))
				$productNames[$qp->productId] = $qp->name;
		
		return $productNames;
	}
	
	public function productPrices() {
		$products = Product::model()->findAll(
			array(
				'select'=>'id, price',
				'condition'=>'status=:active',
				'params'=>array(':active'=>'Active'),
			)
		);
		$productPrices = array(0 => '');
		foreach($products as $product)
			$productPrices[$product->id] = $product->price;
		
		// get any inactive products in this quote
		$quoteProducts = QuoteProduct::model()->findAll(
			array(
				'select'=>'productId, price',
				'condition'=>'quoteId=:quoteId',
				'params'=>array(':quoteId'=>$this->id),
			)
		);
		foreach($quoteProducts as $qp)
			if(!isset($productPrices[$qp->productId]))
				$productPrices[$qp->productId] = $qp->price;
		
		return $productPrices;
	}
	
	public function activeProducts() {
		$products = Product::model()->findAllByAttributes(array('status'=>'Active'));
		$inactive = Product::model()->findAllByAttributes(array('status'=>'Inactive'));
		$quoteProducts = QuoteProduct::model()->findAll(
			array(
				'select'=>'productId',
				'condition'=>'quoteId=:quoteId',
				'params'=>array(':quoteId'=>$this->id),
			)
		);
		foreach($quoteProducts as $qp)
			foreach($inactive as $i)
				if($qp->productId == $i->id)
					$products[] = $i;
		return $products;
	}
}