<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = Profile::model()->findByPk(1);
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
	<h1 id='web-form-submit-message'><?php echo Yii::t('contacts','Thank You!'); ?></h1>
    <?php
    if (empty($thankYouText)) {
    if ($type === 'weblead' || $type === 'weblist') { ?>
        <p><?php echo Yii::t('contacts','Thank you for your interest!'); ?></p>
    <?php
    } elseif ($type === 'service') { ?>
        <p><?php echo Yii::t('contacts','Your case number is: ') . $caseNumber; ?></p>
    <?php
    } ?>
        <p><?php echo Yii::t('contacts','Someone will be in touch shortly.'); ?></p>
    <?php 
    } else { ?>
        <p><?php echo $thankYouText; ?></p>
        <?php if ($type === 'service') { ?>
            <p><?php echo Yii::t('contacts','Your case number is: ') . $caseNumber; ?></p>
        <?php
        }
    }
} 
if (isset ($redirectUrl) && $redirectUrl) {
?>
<script>
    window.top.location.href = '<?php echo addslashes ($redirectUrl); ?>';
</script>
<?php
}
?>
</body>
</html>
