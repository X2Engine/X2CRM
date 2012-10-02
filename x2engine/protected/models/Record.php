<?php

/* * *******************************************************************************
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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */

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
            $assignment=User::model()->findByAttributes(array('username'=>$record->assignedTo));
            $temp['assignedTo']=isset($assignment)?CHtml::link($assignment->firstName." ".$assignment->lastName,array('profile/'.$assignment->id)):"";
			if($record instanceof Contacts) {
				$temp['id']=$record->id;
				$temp['name']=$record->firstName.' '.$record->lastName;
				$temp['description']=$record->backgroundInfo;
				$temp['link']='/contacts/'.$record->id;
				$temp['type']='Contact';
				$temp['lastUpdated']=$record->lastUpdated;
				$temp['updatedBy']=CHtml::link($name,array('profile/'.$userId));
				
				while(isset($arr[$temp['lastUpdated']]))
					$temp['lastUpdated']++;
				$arr[$temp['lastUpdated']]=$temp;
				
			} elseif($record instanceof Actions) {
				$temp['id']=$record->id;
				$temp['name']=empty($record->type)? Yii::t('actions','Action') : Yii::t('actions','Action: ').ucfirst($record->type);
				$temp['description']=$record->actionDescription;
				$temp['link']='/actions/'.$record->id;
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
					$temp['link']='/opportunities/'.$record->id;
					$temp['type']='Opportunity';
				}
				elseif($record instanceof Accounts) {
					$temp['link']='/accounts/'.$record->id;
					$temp['type']='Account';
				} else {
					$temp['link']='/'.strtolower(get_class($record)).'/'.$record->id;
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