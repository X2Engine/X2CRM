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
<div class="page-title"><h2><?php echo Yii::t('admin','Delete A Custom Dropdown'); ?></h2></div>
<div class="form">
<br> <span style="color:red;"><b><?php echo Yii::t('admin','WARNING');?>:</b> <?php echo Yii::t('admin','this operation is not reversible, and will create issues with any forms using the deleted dropdown.');?></span>
<form name="deleteDropdowns" action="deleteDropdown" method="POST">
	<br>
	<select name="dropdown">
		<?php foreach($dropdowns as $dropdown) echo "<option value='$dropdown->id'>$dropdown->name</option>"; ?>
	</select>
	<br><br>
	<input class="x2-button" type="submit" value="<?php echo Yii::t('admin','Delete');?>" />
</form>
</div>