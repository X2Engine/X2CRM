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
?>
<ul>
	<li><?php echo "<strong>$total</strong> ".Yii::t('app','Total Action|Total Actions',$total); ?></li>
	<li><?php echo "<strong>$unfinished</strong> ".Yii::t('app','Unfinished Action|Unfinished Actions',$unfinished);; ?></li>
	<li><?php echo "<strong>$overdue</strong> ".Yii::t('app','Overdue Action|Overdue Actions',$overdue);; ?></li>
	<li><?php echo "<strong>$complete</strong> ".Yii::t('app','Completed Action|Completed Actions',$complete);; ?></li>
</ul>