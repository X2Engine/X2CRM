<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
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

			$names=array();
			$descriptions=array();
			$notes=array();

			$records=array();

			$regEx="/$term/i";

			foreach($contacts as $contact){
					if(preg_match($regEx,$contact->firstName." ".$contact->lastName)>0){
							$names[]=$contact;
					}elseif(preg_match($regEx,$contact->firstName)>0 || preg_match($regEx,$contact->lastName)>0){
							$notes[]=$contact;
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
							$names[]=$action;
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


			$records=array_merge($names,$descriptions);

			$records=array_merge($records,$notes);

			asort($records);

			$records=Record::convert($records);

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
