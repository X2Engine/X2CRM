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
		
		$term=$_GET['term'];
			
		if(substr($term,0,1)!="#"){

			$sql = 'SELECT * FROM x2_contacts WHERE (visibility=1 OR assignedTo="'.Yii::app()->user->getName()
				.'") AND (CONCAT(firstName," ",lastName) LIKE "%'.$term
				.'%" OR backgroundInfo LIKE "%'.$term
				.'%" OR email LIKE "%'.$term
				.'%" OR firstName LIKE "%'.$term
				.'%" OR lastName LIKE "%'.$term
				.'%" OR phone LIKE "%'.$term
				.'%" OR address LIKE "%'
				.$term.'%")';

			$contacts=Contacts::model()->findAllBySql($sql);

			$actions=Actions::model()->findAllBySql('SELECT * FROM x2_actions WHERE actionDescription LIKE "%'.$term.'%" LIMIT 10000');
			$sales=Sales::model()->findAllBySql('SELECT * FROM x2_sales WHERE name LIKE "%'.$term.'%" OR description LIKE "%'.$term.'%"');
			$accounts=Accounts::model()->findAllBySql('SELECT * FROM x2_accounts WHERE name LIKE "%'.$term.'%" OR description LIKE "%'.$term.'%" 
					OR tickerSymbol LIKE "%'.$term.'%"');
                        $quotes=Quote::model()->findAllBySql('SELECT * FROM x2_quotes WHERE name LIKE "%'.$term.'%"');
                        
                        $other=array();
                        $disallow=array(
                            'contacts',
                            'actions',
                            'sales',
                            'accounts',
                            'dashboard',
                            'users',
                            'docs',
                            'quotes',
                            'workflow',
                            'groups',
                        );
                        $order=explode(":",Yii::app()->params->admin->menuOrder);
                        foreach($order as $item){
                            if(is_null(Docs::model()->findByAttributes(array('title'=>$item)))){
                                if(array_search($item,$disallow)===false){
                                    $type=ucfirst($item);
                                    if($type=='Products')
                                        $type='Product';
                                    eval("\$arr=$type::model()->findAllBySql('SELECT * FROM x2_$item WHERE name LIKE \'%$term%\' OR description LIKE \'%$term%\'');");
                                    $other[]=$arr;
                                }
                            }
                        }
                        $other[]=$quotes;
			$names=array();
			$descriptions=array();

			$records=array();

			$regEx="/$term/i";

			foreach($contacts as $contact){
					if(preg_match($regEx,$contact->firstName." ".$contact->lastName)>0){
							$names[]=$contact;
					}elseif(preg_match($regEx,$contact->firstName)>0 || preg_match($regEx,$contact->lastName)>0){
							$descriptions[]=$contact;
					}elseif(preg_match($regEx,$contact->backgroundInfo)>0){
							$descriptions[]=$contact;
					}elseif(preg_match($regEx,$contact->email)>0){
							$names[]=$contact;
					}elseif(preg_match($regEx,$contact->phone)>0){
							$names[]=$contact;
					}elseif(preg_match($regEx,$contact->address)>0){
							$names[]=$contact;
					}
			}

			foreach($actions as $action){
					if(preg_match($regEx,$action->actionDescription)>0){
							$descriptions[]=$action;
					}
			}

			foreach($sales as $sale){
					if(preg_match($regEx,$sale->name)>0){
							$names[]=$sale;
					}elseif(preg_match($regEx,$sale->description)>0){
							$descriptions[]=$sale;
					}
			}

			foreach($accounts as $account){
					if(preg_match($regEx,$account->name)>0){
							$names[]=$account;
					}elseif(preg_match($regEx,$account->tickerSymbol)>0){
							$names[]=$account;
					}elseif(preg_match($regEx,$account->description)>0){
							$descriptions[]=$account;
					}elseif(preg_match($regEx,$account->website)>0){
							$names[]=$account;
					}
			}
                        foreach($other as $recordType){
                            foreach($recordType as $otherRecord){
                                if(preg_match($regEx,$otherRecord->name)>0){
                                    $names[]=$otherRecord;
                                }elseif(preg_match($regEx,$otherRecord->description)>0){
                                    $descriptions[]=$otherRecord;
                                }
                            }
                        }

			$records=array_merge($names,$descriptions);

			$records=Record::convert($records, false);

			$dataProvider=new CArrayDataProvider($records,array(
					'id'=>'id',
					'pagination'=>array(
							'pageSize'=>10,
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
			));
		}
	}
}

?>
