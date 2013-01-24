<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/
?>

<h2><?php echo $contact->firstName . ' ' . $contact->lastName; ?></h2>

<?php 
if(isset($_GET['fields'])){
    $fields=$_GET['fields'];
    if(count($fields)>0){
        $attrLabels=$contact->attributeLabels();
        foreach($fields as $field){
            if($contact->hasAttribute($field)){
                echo Yii::t('contacts', $attrLabels[$field]).": <strong> ".$contact->$field."</strong><br />";
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
		<?php echo Yii::t('contacts', 'Lead Date').": "; ?> <strong><?php echo $this->formatLongDate($contact->leadDate); ?></strong><br />
	<?php } ?>
	
	
	<?php if(isset($contact->interest) && $contact->interest != "") { ?>
		<?php echo Yii::t('contacts', 'Interest').": "; ?> <strong><?php echo $contact->interest; ?></strong><br />
	<?php } ?>
	
	<?php if(isset($contact->dalevalue) && $contact->dealvalue != "") { ?>
		<?php echo Yii::t('contacts', 'Deal Value').": "; ?> <strong><?php echo $contact->dealvalue; ?></strong><br />
	<?php } ?>
	
	<?php if(isset($contact->closedate) && $contact->closedate != "") { ?>
		<?php echo Yii::t('contacts', 'Close Date').": "; ?> <strong><?php echo $this->formatLongDate($contact->closedate); ?></strong><br />
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