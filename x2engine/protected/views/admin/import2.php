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
<div class="page-title"><h2><?php echo Yii::t('admin', 'Import via {i2}',array('{i2}'=>'Import2')); ?></h2></div>

<div class="form">
	<form action="">
		<script src="https://www.import2.com/assets/v1/import2.js"
				data-api-token="<not given yet>"
				data-destination-instance-url="<?php echo Yii::app()->baseUrl; ?>"
				data-destination-username="<?php echo Yii::app()->user->name; ?>"
				data-destination-token="<?php echo Yii::app()->user->userKey ?>"
		type="text/javascript"></script>
	</form>
</div>