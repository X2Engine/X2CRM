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
 * Widget that renders a tag cloud
 * 
 * @package X2CRM.components 
 */
class TagCloud extends X2Widget {
	
	public $visibility;
	public function init() {
		parent::init();
	}

	public function run() {
        $hiddenTags=json_decode(Yii::app()->params->profile->hiddenTags,true);
        if(count($hiddenTags)>0){
            $str1=" AND tag NOT IN ('".implode("','",$hiddenTags)."')";
        }else{
            $str1="";
        }
		$myTags = Yii::app()->db->createCommand()
			->select('COUNT(*) AS count, tag')
			->from('x2_tags')
			->where('taggedBy=:user AND tag IS NOT NULL'.$str1,array(':user'=>Yii::app()->user->getName()))
			->group('tag')
			->order('count DESC')
			->limit(20)
			->queryAll();
		
		$allTags = Yii::app()->db->createCommand()
			->select('COUNT(*) AS count, tag')
			->from('x2_tags')
			->group('tag')
            ->where('tag IS NOT NULL'.$str1)
			->order('count DESC')
			->limit(20)
			->queryAll();
	
		// $myTags=Tags::model()->findAllBySql("SELECT *, COUNT(*) as num FROM x2_tags WHERE taggedBy='".Yii::app()->user->getName()."' GROUP BY tag ORDER BY num DESC LIMIT 20");
		// $allTags=Tags::model()->findAllBySql("SELECT *, COUNT(*) as num FROM x2_tags GROUP BY tag ORDER BY num DESC LIMIT 20");
		$this->render('tagCloud',array(
			'myTags'=>$myTags,
			'allTags'=>$allTags,
			'showAllUsers'=>Yii::app()->params->profile->tagsShowAllUsers,
		));
	}
}