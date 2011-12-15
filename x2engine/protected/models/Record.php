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

class Record {

	public static function convert($records) {
		$arr=array();
		$id=1;
		
		foreach ($records as $record) {
			$user=UserChild::model()->findByAttributes(array('username'=>$record->updatedBy));
                        if(isset($user)){
                            $name=$user->firstName." ".$user->lastName;
                            $userId=$user->id;
                        }
                        else{
                            $name='web admin';
                            $userId=1;
                        }
			if ($record instanceof Contacts) {
				$temp=array();
				$temp['id']=$id;
					$id++;
				$temp['name']=$record->firstName.' '.$record->lastName;
				$temp['description']=$record->backgroundInfo;
				$temp['link']='/contacts/'.$record->id;
				$temp['type']='Contact';
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=CHtml::link($name,array('profile/'.$userId));
				$arr[]=$temp;
			} elseif ($record instanceof Actions) {
				$temp=array();
				$temp['id']=$id;
					$id++;
				$temp['name']=empty($record->type)? Yii::t('actions','Action') : Yii::t('actions','Action: ').ucfirst($record->type);
				$temp['description']=$record->actionDescription;
				$temp['link']='/actions/'.$record->id;
				$temp['type']='Action';
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=$name;
				$arr[]=$temp;
			} else {
				$temp=array();
				$temp['id']=$id;
					$id++;
				$temp['name']=$record->name;
				$temp['description']=$record->description;
				
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=$name;
				
				if($record instanceof Sales){
					$temp['link']='/sales/'.$record->id;
					$temp['type']='Sale';
				}
				elseif ($record instanceof Accounts){
					$temp['link']='/accounts/'.$record->id;
					$temp['type']='Account';
				}

				$arr[]=$temp;
			}
		}
		return $arr;
	}
}

?>
