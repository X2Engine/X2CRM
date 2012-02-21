<?php

/**
 * This is the model class for table "x2_quotes".
 *
 * The followings are the available columns in table 'x2_quotes':
 * @property integer $id
 * @property string $name
 * @property string $accountName
 * @property integer $accountId
 * @property string $salesStage
 * @property string $expectedCloseDate
 * @property integer $probability
 * @property string $leadSource
 * @property string $description
 * @property string $assignedTo
 * @property integer $createDate
 * @property string $associatedContacts
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Quote extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Quotes the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_quotes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('probability', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>40),
			array('accountName', 'length', 'max'=>100),
			array('salesStage, expectedCloseDate, updatedBy', 'length', 'max'=>20),
			array('leadSource', 'length', 'max'=>10),
			array('description, assignedTo, associatedContacts', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, accountName, salesStage, expectedCloseDate, probability, leadSource, description, assignedTo, createDate, associatedContacts, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'products'=>array(self::HAS_MANY, 'QuoteProduct', 'quoteId'),
		);
	}
	
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Quotes'));
                $arr=array();
                foreach($fields as $field){
                    $arr[$field->fieldName]=Yii::t('quotes',$field->attributeLabel);
                }
                
                return $arr;
	}
	
	public static function statusList() {
		$field = Fields::model()->findByAttributes(array('modelName'=>'Quotes', 'fieldName'=>'status'));
		$dropdown = Dropdowns::model()->findByPk($field->linkType);
		return json_decode($dropdown->options);
		
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
		$arr=Quote::model()->findAll();
		$names=array(0=>"None");
		foreach($arr as $quote){
			$names[$quote->id]=$quote->name;
		}
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

		$quotesList = CActiveRecord::model('Quote')->findAllByAttributes(array('accountName'=>$accountId));
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
				$data[]=UserChild::model()->findByAttributes(array('username'=>$username));
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
				$data[]=Contacts::model()->findByPk($id);
		}
		$temp=array();
		
		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
	}

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),           /* optional line */
				'defaultStickOnClear'=>false   /* optional line */
			),
		);
	}

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
	
	private function searchBase($criteria) {
		// $criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('accountName',$this->accountName,true);
		$criteria->compare('salesStage',$this->salesStage,true);
		// $criteria->compare('expectedCloseDate',$this->expectedCloseDate,true);
		$criteria->compare('probability',$this->probability);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		// $criteria->compare('createDate',$this->createDate);
		$criteria->compare('associatedContacts',$this->associatedContacts,true);
		// $criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('updatedBy',$this->updatedBy,true);

		$dateRange = Yii::app()->controller->partialDateRange($this->expectedCloseDate);
		if($dateRange !== false)
			$criteria->addCondition('expectedCloseDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
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