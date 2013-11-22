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
 * Class for displaying tags on a record.
 * 
 * @package X2CRM.components 
 */
class InlineTags extends X2Widget {
	public $model;
	public $modelName;
    public $filter = false;
    public $tags = array();

	public function init() {
		parent::init();
	}

	public function run() {
		if($this->filter) {
			$this->render('inlineTags',array('filter'=>true,'tags'=>$this->tags));
		} else {
			$tags = Yii::app()->db->createCommand()
				->select('COUNT(*) AS count, tag')
				->from('x2_tags')
				->where('type=:type AND itemId=:itemId',array(':type'=>get_class($this->model),':itemId'=>$this->model->id))
				->group('tag')
				->order('count DESC')
				->limit(20)
				->queryAll();

			$this->render('inlineTags',array('model'=>$this->model,'tags'=>$tags,'filter'=>false));
		}
	}
}