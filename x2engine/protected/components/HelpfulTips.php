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
 * Widget class for rendering a user's actions widget.
 * 
 * Renders the actions widget with action statistics, i.e. how many actions total,
 * how many actions complete, how many incomplete, titled "My Actions"
 * @package X2CRM.components 
 */
class HelpfulTips extends X2Widget {
	public $visibility;
	public function init() {
		parent::init();
	}
	/**
	 * Creates the widget. 
	 */
	public function run() {
            //opensource or pro
            $edition = yii::app()->params->admin->edition;
            //True or False
            $admin = Yii::app()->params->isAdmin;
            //Check user type and editon to deliever an appropriate tip
            if($edition == 'pro'){
                if($admin){
                    $where = 'TRUE';
                } else {
                    $where = 'admin = 0';                
                }   
            } else if($admin){
                $where = 'edition = "opensource"';
            } else {
                $where = 'admin = 0 AND edition = "opensource"';
            }
            $tip=Yii::app()->db->createCommand()
                    ->select('*')
                    ->from('x2_tips')
                    ->where($where)
                    ->order('rand()')
                    ->queryRow(); 
            $this->render('tip',$tip);
	}
}
?>
