<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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

