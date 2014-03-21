<?php
// Authorized Partner/Reseller footer content (sample/placeholder file)
/* @start:footer */
?>
<img src="data:image/gif;base64,<?php echo base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'partnerLogo_example.gif')); ?>" />
<br />
<span>Content for this area in file: <strong><?php echo str_replace(Yii::app()->basePath.DIRECTORY_SEPARATOR,'',__FILE__); ?></strong></span><br /><br />
<span>For instructions on editing this content, see <?php echo CHtml::link('Partner Branding How-To',array('/site/page','view'=>'brandingHowto'));?> or view the file <em>protected/partner/README.md</em></span>
<?php /* @end:footer */ ?>

<p>Drawing outside the lines</p>