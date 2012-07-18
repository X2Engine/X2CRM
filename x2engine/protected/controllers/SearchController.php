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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * Description of SearchController
 *
 * @author Jake
 */
class SearchController extends x2base {


	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('search'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionSearch(){
		ini_set('memory_limit',-1);
		$term=$_GET['term'];
                
                if(substr($term,0,1)!="#"){
                    
                        /*$contactsCriteria = new CDbCriteria();

                        $contactsCriteria->compare('firstName', $term, true, 'OR');
                        $contactsCriteria->compare('lastName', $term, true, 'OR');
                        $contactsCriteria->compare('name', $term, true, 'OR');
                        $contactsCriteria->compare('backgroundInfo', $term, true, 'OR');
                        $contactsCriteria->compare('email', $term, true, 'OR');
                        $contactsCriteria->compare('phone', $term, true, 'OR');
                        if(is_numeric($term)){
                            $temp=$term;
                            $first=substr($temp,0,3);
                            $second=substr($temp,3,3);
                            $third=substr($temp,6,4);

                            $contactsCriteria->compare('phone', "($first) $second-$third", true, 'OR');
                            $contactsCriteria->compare('phone', "$first-$second-$third", true, 'OR');
                            $contactsCriteria->compare('phone', "$first $second $third", true, 'OR');

                            $contactsCriteria->compare('phone2', "($first) $second-$third", true, 'OR');
                            $contactsCriteria->compare('phone2', "$first-$second-$third", true, 'OR');
                            $contactsCriteria->compare('phone2', "$first $second $third", true, 'OR');

                        }
						
                        $contacts=CActiveRecord::model('Contacts')->findAll($contactsCriteria);

                        
                        $actionsCriteria=new CDbCriteria();
                        $actionsCriteria->compare('actionDescription',$term,true,'OR');
                        $actions=Actions::model()->findAll($actionsCriteria);
                        
                        $accountsCriteria=new CDbCriteria();
                        $accountsCriteria->compare("name",$term,true,"OR");
                        $accountsCriteria->compare("description",$term,true,"OR");
                        $accountsCriteria->compare('tickerSymbol',$term,true,'OR');
                        $accounts=Accounts::model()->findAll($accountsCriteria);
                        
                        $quotesCriteria=new CDbCriteria();
                        $quotesCriteria->compare("name",$term,TRUE,"OR");
                        $quotes=Quote::model()->findAll($quotesCriteria);
                        
                        $disallow=array(
                            'contacts',
                            'actions',
                            'accounts',
                            'quotes',
                        );*/
                        
                        $modules=Modules::model()->findAllByAttributes(array('searchable'=>1));
                        $comparisons=array();
                        foreach($modules as $module){
                                $module->name=='products'?$type=ucfirst('Product'):$type=ucfirst($module->name);
                                $module->name=='quotes'?$type=ucfirst('Quote'):$type=$type;
                                $criteria=new CDbCriteria();
                                $fields=Fields::model()->findAllByAttributes(array('modelName'=>$type,'searchable'=>1));
                                $temp=array();
                                foreach($fields as $field){
                                    $temp[]=$field->id;
                                    $criteria->compare($field->fieldName,$term,true,"OR");
                                }
                                $arr=CActiveRecord::model($type)->findAll($criteria);
                                $comparisons[$type]=$temp;
                                $other[$type]=$arr;
                            
                            
                        }
			$high=array();
			$medium=array();
                        $low=array();

			$records=array();

			$regEx="/$term/i";

                        foreach($other as $key=>$recordType){
                            $fieldList=$comparisons[$key];
                            foreach($recordType as $otherRecord){
                                foreach($fieldList as $field){
                                    $fieldRecord=Fields::model()->findByPk($field);
                                    $fieldName=$fieldRecord->fieldName;
                                    if(preg_match($regEx,$otherRecord->$fieldName)>0){
                                        switch($fieldRecord->relevance){
                                            case "High":
                                                if(!in_array($otherRecord,$high) && !in_array($otherRecord,$medium) && !in_array($otherRecord,$low))
                                                    $high[]=$otherRecord;
                                                break;
                                            case "Medium":
                                                if(!in_array($otherRecord,$high) && !in_array($otherRecord,$medium) && !in_array($otherRecord,$low))
                                                    $medium[]=$otherRecord;
                                                break;
                                            case "Low":
                                                if(!in_array($otherRecord,$high) && !in_array($otherRecord,$medium) && !in_array($otherRecord,$low))
                                                    $low[]=$otherRecord;
                                                break;
                                            default:
                                                $low[]=$otherRecord;
                                        }
                                        
                                    }
                                }
                            }
                        }

			$records=array_merge($high,$medium);
                        $records=array_merge($records,$low);

			$records=Record::convert($records, false);

			$dataProvider=new CArrayDataProvider($records,array(
					'id'=>'id',
					'pagination'=>array(
							'pageSize'=>ProfileChild::getResultsPerPage(),
					),
			));

			$this->render('search',array(
					'records'=>$records,
					'dataProvider'=>$dataProvider,
					'term'=>$term,
			));
		}else{
			$results=new CActiveDataProvider('Tags',array(
				'criteria'=>array('condition'=>'tag="'.$term.'"')
			));
			$this->render('searchTags',array(
				'tags'=>$results,
				'term'=>$term,
			));
		}
	}
}

?>
