<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * @package X2CRM.models 
 */
class Record {

    /**
     * Compiles new actions and contacts into a list for the "What's New" page
     * @param array $records
     * @param boolean $whatsNew
     * @return array 
     */
	public static function convert($records, $whatsNew=true) {
		$arr=array();
		
		foreach ($records as $record) {
			$user=UserChild::model()->findByAttributes(array('username'=>$record->updatedBy));
			if(isset($user)) {
				$name=$user->firstName." ".$user->lastName;
				$userId=$user->id;
			} else {
				$name='web admin';
				$userId=1;
			}
            $temp=array();
            if($record->hasAttribute('assignedTo')){
                $assignment=User::model()->findByAttributes(array('username'=>$record->assignedTo));
                $temp['assignedTo']=isset($assignment)?CHtml::link($assignment->firstName." ".$assignment->lastName,array('/profile/view','id'=>$assignment->id)):"";
            }else{
                $temp['assignedTo']='';
            }
            if($record instanceof Contacts) {
				$temp['id']=$record->id;
				$temp['name']=$record->firstName.' '.$record->lastName;
				$temp['description']=$record->backgroundInfo;
				$temp['link']=array('/contacts/contacts/view','id'=>$record->id);
				$temp['type']='Contact';
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=CHtml::link($name,array('/profile/view','id'=>$userId));
				
				while(isset($arr[$temp['lastUpdated']]))
					$temp['lastUpdated']++;
				$arr[$temp['lastUpdated']]=$temp;
				
			} elseif($record instanceof Actions) {
				$temp['id']=$record->id;
				$temp['name']=empty($record->type)? Yii::t('actions','Action') : Yii::t('actions','Action: ').ucfirst($record->type);
				$temp['description']=$record->actionDescription;
				$temp['link']=array('/actions/actions/view','id'=>$record->id);
				$temp['type']='Action';
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=$name;
				while(isset($arr[$temp['lastUpdated']]))
					$temp['lastUpdated']++;
				$arr[$temp['lastUpdated']]=$temp;
			} else {
				$temp['id']=$record->id;
				$temp['name']=$record->name;
				if(!is_null($record->description))
					$temp['description']=$record->description;
				else
					$temp['description']="";
				
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=$name;
				
				if($record instanceof Opportunity) {
					$temp['link']=array('/opportunities/opportunities/view','id'=>$record->id);
					$temp['type']='Opportunity';
				}
				elseif($record instanceof Accounts) {
					$temp['link']=array('/accounts/accounts/view','id'=>$record->id);
					$temp['type']='Account';
				} elseif($record instanceof Quote || $record instanceof Product){
                    $temp['type']=get_class($record);
					$temp['link']=array(str_repeat('/'.strtolower(get_class($record)).'s',2).'/view','id'=>$record->id);
                }else {
                    $temp['type']=get_class($record);
					$temp['link']=array(str_repeat('/'.strtolower(get_class($record)),2).'/view','id'=>$record->id);
				}

				while(isset($arr[$temp['lastUpdated']]))
					$temp['lastUpdated']++;
				if($whatsNew)
					$arr[$temp['lastUpdated']]=$temp;
				else
					$arr[]=$temp;
			}
		}
		if($whatsNew){
			ksort($arr);
			return array_values(array_reverse($arr));
		}else{
			return array_values($arr);
		}
	}
}
