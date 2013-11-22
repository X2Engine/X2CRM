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
 * Date formatting utilities (unused)
 * 
 * @package X2CRM.components 
 */
class X2Date {
	public static function dateBox($date) {
		$str = '<div class="date-box" title="';
		$str .= date('Y-m-d H:i',$date);
		
		$str .= '"><span class="month">';
		$str .= date('M',$date);
		$str .= '</span><span class="day">';
		$str .= date('d',$date);
		$str .= '</span></div>';
		return $str;
	}
	
	public static function actionDate($date,$priority,$complete='No') {
        if($complete=="No"){
            if($priority == '3')
                $priority = ' p-3';
            elseif($priority == '2')
                $priority = ' p-2';
            else
                $priority = ' p-1';
        }else{
            $priority='';
        }
		
		$str = '<div class="date-box'.$priority.'" title="';
		$str .= date('Y-m-d H:i',$date);
		
		$str .= '"><span class="month">';
		$str .= Yii::app()->getLocale()->getMonthName(date('n',$date),'abbreviated');
		// $str .= date('M',$date);
		$str .= '</span><span class="day">';
		$str .= date('d',$date);
		$str .= '</span></div>';
		return $str;
	}
}
?>