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
 * "Top sites" widget class
 * 
 * @package X2CRM.components 
 */
class TopSites extends X2Widget {

	public $visibility;
	public function init() {
		parent::init();
	}

	public function run() {
		$content=URL::model()->findAllByAttributes(
            array('userid'=>Yii::app()->user->getId()),array('order'=>'timestamp DESC'));
        $data = array();
        if(count($content)>0){
            foreach($content as $entry){
                $dt['title'] = $entry->title;
                if(strpos($entry->url,'http://') === false){
                    $entry->url="http://".$entry->url;
                }
                $dt['url'] = $entry->url;
                $dt['id'] = $entry->id;
                $data[] = $dt;
            }
        }else{
            $dt['title'] = Yii::t('app',"Example");
            $dt['url'] = "http://www.x2engine.com";
            $data[] = $dt;
        }
		$this->render('topSites', array(
			'data'=>$data,
		));
	}
}
?>
