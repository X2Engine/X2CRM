<h1><?php echo Yii::t('admin','Contact Us');?></h1>
<?php echo Yii::t('admin','X2Engine Inc. is the company behind X2Contacts - a high-performance contact management web application.
X2Engine can offer to your organization professional support and training on X2Contacts.  Please fill out the form below to contact us.');?>
<form name="contact-us" method="POST"><br />
	<b><?php echo Yii::t('app','E-Mail');?>:</b><br /><input type="text" name="email" /><br />
	<b><?php echo Yii::t('admin','Subject');?>:</b><br /><input type="text" name="subject" size="60" /><br />
	<b><?php echo Yii::t('app','Message Body');?>:</b><br /><textarea style="height:200px;width:590px;" name="body"></textarea><br />
	<input class="x2-button" type="submit" value="<?php echo Yii::t('app','Send Email');?>" />
</form>