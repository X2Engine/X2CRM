<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductFeature
 *
 * @author demetrius
 */
class ProductFeature extends X2Model {

    public $supportsWorkflow = false;

    /**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_product_features'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'baseRoute'=>'/products/products/productFeature'
			)
		));
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>get_class($this)));
                $arr=array(
                    'varchar'=>array(),
                    'text'=>array(),
                    'date'=>array(),
                    'dropdown'=>array(),
                    'int'=>array(),
                    'email'=>array(),
                    'currency'=>array(),
                    'url'=>array(),
                    'float'=>array(),
                    'boolean'=>array(),
                    'required'=>array(),
                    
                );
                $rules=array();
                foreach($fields as $field){
			$arr[$field->type][]=$field->fieldName;
			if($field->required)
				$arr['required'][]=$field->fieldName;
                        if($field->type!='date')
                            $arr['search'][]=$field->fieldName;
		}
                $arr['search'][]='name';
		foreach($arr as $key=>$array){
			switch($key){
				case 'email':
					$rules[]=array(implode(',',$array),$key);
					break;
				case 'required':
					$rules[]=array(implode(',',$array),$key);
					break;
                                case 'search':
                                        $rules[]=array(implode(",",$array),'safe','on'=>'search');
                                        break;
				case 'int':
					$rules[]=array(implode(',',$array),'numerical','integerOnly'=>true);
					break;
				case 'float':
					$rules[]=array(implode(',',$array),'type','type'=>'float');
					break;
				case 'boolean':
					$rules[]=array(implode(',',$array),$key);
					break;
				default:
					break;
				
			}
			
		}  
		return $rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'ProductFeature'));
		$arr=array();
		foreach($fields as $field){
			$arr[$field->fieldName]=Yii::t('app',$field->attributeLabel);
		}
		
		return $arr;

	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'ProductFeature'));
		foreach($fields as $field){
			$fieldName=$field->fieldName;
			switch($field->type){
				case 'boolean':
					$criteria->compare($field->fieldName,$this->compareBoolean($this->$fieldName), true);
					break;
				case 'assignment':
					$criteria->compare($field->fieldName,$this->compareAssignment($this->$fieldName), true);
					break;
				default:
					$criteria->compare($field->fieldName,$this->$fieldName,true);
			}
			
		}

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Profile::getResultsPerPage(),
			),
		));
	}
        

    
}

?>
