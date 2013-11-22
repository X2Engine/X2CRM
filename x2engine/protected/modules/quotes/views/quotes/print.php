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
//
?>
<html>

  <head>
	<meta charset="UTF-8">
  </head>
  <body>
<table style="width:100%;">
	<tbody>
		<tr>
			<td><b><?php echo $model->name; ?></b></td>
			<td style="text-align:right;font-weight:bold;">
				<span><?php echo ( $model->type == 'invoice'? Yii::t('quotes', 'Invoice:') : Yii::t('quotes','Quote:')); ?> # <?php echo $model->id; ?></span><br />
				<span><?php echo Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), time()); ?></span>
			</td>
		</tr>
	</tbody>
</table><br />

<?php
echo $model->productTable($email);
?>
  <?php echo empty($model->description)?'':'<div>'.$model->renderAttribute('description').'</div>'; ?>

  </body>
</html>