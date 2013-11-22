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

<h2><?php echo $contact->name; ?></h2>

<?php
if(isset($_GET['fields'])){
    $fields=$_GET['fields'];
    if(count($fields)>0){
        $attrLabels=$contact->attributeLabels();
        foreach($fields as $field){
            if($contact->hasAttribute($field) && !empty($contact->$field)){
                echo Yii::t('contacts', $attrLabels[$field]).": <strong> ".$contact->getAttribute($field,true)."</strong><br />";
            }else{
                if($field=='link'){
                    echo CHtml::link(Yii::t('contacts','Link to Record'),$this->createUrl('view',array('id'=>$contact->id)),array('style'=>'text-decoration:none;'))."<br />";
                }elseif($field=='directions'){
                    if(!empty(Yii::app()->params->admin->corporateAddress))
                        echo CHtml::link(Yii::t('contacts','Directions from Corporate'),'#',array('style'=>'text-decoration:none;','id'=>'corporate-directions','class'=>'directions'))."<br />";
                    if(!empty(Yii::app()->params->profile->address))
                        echo CHtml::link(Yii::t('contacts','Directions from Personal Address'),'#',array('style'=>'text-decoration:none;','id'=>'personal-directions','class'=>'directions'));
                }
            }
        }
    }
}else{
?>


<?php /* Contact Info */ ?>
<?php if(isset($contact->email) || isset($contact->website)) { ?>
	<?php if(isset($contact->email) && $contact->email != "") { ?>
		<?php echo Yii::t('contacts', 'Email').": "; ?> <strong><?php echo $contact->email; ?></strong><br />
	<?php } ?>

	<?php if(isset($contact->website) && $contact->website != "") { ?>
		<?php echo Yii::t('contacts', 'Website: '); ?> <strong><?php echo $contact->website; ?></strong><br />
	<?php } ?>
	<br />
<?php } ?>


<?php /* Sales and Marketing */ ?>
<?php if(isset($contact->leadtype) ||
		isset($contact->leadstatus) ||
		isset($contact->leadDate) ||
		isset($contact->interest) ||
		isset($contact->dalevalue) ||
		isset($contact->closedate) ||
		isset($contact->closestatus)) { ?>
	<?php if(isset($contact->leadtype) && $contact->leadtype != "") { ?>
	    <?php echo Yii::t('contacts', 'Lead Type').": "; ?> <strong><?php echo $contact->leadtype; ?></strong><br />
	<?php } ?>

	<?php if(isset($contact->leadstatus) && $contact->leadstatus != "") { ?>
		<?php echo Yii::t('contacts', 'Lead Status').": "; ?> <strong><?php echo $contact->leadstatus; ?></strong><br />
	<?php } ?>


	<?php if(isset($contact->leadDate) && $contact->leadDate != "") { ?>
		<?php echo Yii::t('contacts', 'Lead Date').": "; ?> <strong><?php echo Formatter::formatLongDate($contact->leadDate); ?></strong><br />
	<?php } ?>


	<?php if(isset($contact->interest) && $contact->interest != "") { ?>
		<?php echo Yii::t('contacts', 'Interest').": "; ?> <strong><?php echo $contact->interest; ?></strong><br />
	<?php } ?>

	<?php if(isset($contact->dalevalue) && $contact->dealvalue != "") { ?>
		<?php echo Yii::t('contacts', 'Deal Value').": "; ?> <strong><?php echo $contact->dealvalue; ?></strong><br />
	<?php } ?>

	<?php if(isset($contact->closedate) && $contact->closedate != "") { ?>
		<?php echo Yii::t('contacts', 'Close Date').": "; ?> <strong><?php echo Formatter::formatLongDate($contact->closedate); ?></strong><br />
	<?php } ?>

	<?php if(isset($contact->dealstatus) && $contact->dealstatus != "") { ?>
		<?php echo Yii::t('contacts', 'Deal Status').": "; ?> <strong><?php echo $contact->dealstatus; ?></strong><br />
	<?php } ?>
	<br />
<?php } ?>

<?php if(isset($contact->backgroundInfo) && $contact->backgroundInfo != "") { ?>
		<?php echo Yii::t('contacts', 'Background Info').": "; ?> <strong><?php echo $contact->backgroundInfo; ?></strong><br />
<?php }

} ?>