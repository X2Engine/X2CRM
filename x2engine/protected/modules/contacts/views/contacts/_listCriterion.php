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
?><div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'attribute'); ?>
		<?php echo $form->dropDownList($model,'attribute',$attributeList); ?>
		<?php echo $form->error($model,'attribute'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'comparison'); ?>
		<?php echo $form->dropDownList($model,'comparison',$comparisonList,array('encode'=>false)); ?>
		<?php echo $form->error($model,'comparison'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'value'); ?>
		<?php echo $form->textField($model,'value',array('size'=>'30')); ?>
		<?php echo $form->error($model,'value'); ?>
	</div>
</div>