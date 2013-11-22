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



mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = ProfileChild::model()->findByPk(1);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>"
 lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>

<style type="text/css">
body {
	font-size:12px;
	font-family: Arial, Helvetica, sans-serif;
	width:189px;
}
</style>
</head>
<body>
<?php
if (!empty($error)) { ?>
	<h1><?php echo Yii::t('contacts','We\'re Sorry!'); ?></h1>
	<p><?php echo $error; ?></p>
<?php
} else { ?>
	<h1><?php echo Yii::t('contacts','Thank You!'); ?></h1>
<?php
if ($type === 'weblead'/* x2prostart */ || $type === 'weblist'/* x2proend */) { ?>
	<p><?php echo Yii::t('contacts','Thank you for your interest!'); ?></p>
<?php
} elseif ($type === 'service') { ?>
	<p><?php echo Yii::t('contacts','Your case number is: ') . $caseNumber; ?></p>
<?php
} ?>
	<p><?php echo Yii::t('contacts','Someone will be in touch shortly.'); ?></p>
<?php } ?>
</body>
</html>
