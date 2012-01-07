<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
<br />
<div class="view">

	<b><?php echo CHtml::encode("Name"); ?>:</b>
	<?php echo CHtml::encode($data->firstName." ".$data->lastName); ?>
	<br />
	
	<b><?php echo CHtml::encode("Phone"); ?>:</b>
	<?php echo "<a href='tel:".CHtml::encode($data->phone)."'>".$data->phone."</a>"; ?>
	<br />
	<b><?php echo CHtml::encode("E-Mail");?>:</b>
	<?php echo "<a href='mailto:".CHtml::encode($data->email)."'>".$data->email."</a>"; ?><br />
	
	<b><?php echo CHtml::encode("Address");?>:</b>
	<?php 
		$address="";
		if(isset($data->address)){
			$address.=$data->address;
			if(isset($data->city)){
				$address.=", ".$data->city;
			}
			if(isset($data->state)){
				$address.=", ".$data->state;
			}
			if(isset($data->zipcode)){
				$address.=", ".$data->zipcode;
			}
			if(isset($data->country)){
				$address.=", ".$data->country;
			}
		}
	?>
	<?php echo $address ?><br />
	<?php if(isset($data->address))
			echo "<a href='http://maps.google.com/maps?q=$data->address,+$data->city,+$data->state,+$data->zipcode,+$data->country'>Map it!</a>"; ?>

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('IM')); ?>:</b>
	<?php echo CHtml::encode($data->IM); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('website')); ?>:</b>
	<?php echo CHtml::encode($data->website); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('visibility')); ?>:</b>
	<?php echo CHtml::encode($data->visibility); ?>
	<br />

	

	*/ ?> 

</div>