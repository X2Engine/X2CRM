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

<html>
	<head>
		<meta charset="UTF-8">
		<link rel='stylesheet' type='text/css' 
		 href='<?php echo Yii::app()->getTheme()->getBaseUrl().'/css/x2forms.css'; ?>'/>
		<link rel='stylesheet' type='text/css' 
		 href='<?php echo Yii::app()->getTheme()->getBaseUrl().'/css/printableRecord.css'; ?>'/>
		<!--<link rel='stylesheet' type='text/css' 
		 href='<?php //echo Yii::app()->getClientScript()->getCoreScriptUrl().'/rating/jquery.rating.css'; ?>'/>-->
		<link rel='stylesheet' type='text/css' 
		 href='<?php echo Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css'; ?>'/>
		<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
		 '/jquery.js'; ?>'></script>
		<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
		 '/jquery.metadata.js'; ?>'></script>
		<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
		 '/jquery.rating.js'; ?>'></script>
	</head>
	<body>

	<h1 id='page-title'><?php echo addslashes ($pageTitle); ?></h1>

	<?php 
	$this->renderPartial('application.components.views._detailView', 
		array('model' => $model, 'modelName' => $modelClass)); 
	?>

	<script>
		// replace stars with textual representation
		$('span[id^="<?php echo $modelClass; ?>-<?php echo $id; ?>-rating"]').each (function () {
			var stars = $(this).find ('[checked="checked"]').val ();
			//console.log ('stars = ' + stars);
			$(this).children ().remove ();
			$(this).html (stars + '/5 <?php echo addslashes (Yii::t('app', 'Stars')); ?>');
		});
	</script>

	</body>
</html>



