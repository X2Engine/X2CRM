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

$this->menu=array(
	array('label'=>'Admin Index', 'url'=>array('index')),
);

Yii::app()->clientScript->registerCss('authGraph','pre em {color:silver;font-style:normal;} pre {color:black;line-height:1em;} pre b {color:blue;font-weight:normal;} pre b.biz {color:red;}');

echo '<h2>AuthGraph</h2>';

function printGraph($task,$level,&$bizrules) {

	
	foreach($task as $child=>$grandChildren) {
		
		if($level > 0) {
			echo '<em>';
			
			for($i=0;$i<$level-1;$i++)
				echo '&#9474; ';
				
			echo '&#9492;&#9472;';
			
			echo '</em>';
		}
		
		// if(in_array($child,$bizrules))
			// $child .= ' (#)';
		
		if(empty($grandChildren)) {
			echo '<b';
			if(in_array($child,$bizrules))
				echo ' class="biz"';
			
			echo ">&#9679; $child</b>\n";
			
			
		} else {
			echo "&#9675; $child \n";
			printGraph($grandChildren,$level+1,$bizrules);
		}
		
	}
}
echo '<div class="form"><pre>';
printGraph($authGraph,0,$bizruleTasks);
echo '</pre></div>';

