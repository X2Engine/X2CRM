<?php
/* * *******************************************************************************
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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */
?>

<?php echo $form->wrapTitle($form->message['updatesTitle']); ?>
<hr />
<?php if ($form->config['unique_id'] == 'none'): ?>
    <div class="row"><label for="receiveUpdates"><?php echo $form->label['receiveUpdates']; ?></label><input type="checkbox" value='1' <?php echo $form->config['receiveUpdates'] ? 'checked="checked"' : Null; ?> name="receiveUpdates" id="receiveUpdates" /></div>
    <div id="receiveUpdates-form">
	<?php echo $form->wrapTitle($form->message['optionalTitle']); ?><hr />
        <div class="row"><label for="subscribe"><?php echo $form->label['subscribe']; ?></label><input type="checkbox" value='1' name="subscribe" id="subscribe" /></div>
        <div class="row"><label for="requestContact"><?php echo $form->label['requestContact']; ?></label><input type='checkbox' name='data' value='1' name="requestContact" id="requestContact" /></div>
        <br /><?php echo $form->message['intro']; ?><br /><br />
	<?php foreach (array('firstName', 'lastName', 'email','phone', 'company', 'position') as $field): ?>
	    <div class="row">
		<label for="<?php echo $field; ?>"><?php echo $form->label[$field]; ?></label>
		<input type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" />
	    </div>
	<?php endforeach; ?>

        <div class="row">
    	<label for="source"><?php echo $form->label['source']; ?></label>
    	<select name="source" id="source">
		<?php foreach ($form->leadSources as $option => $optionDisplay): ?>
		    <option value="<?php echo $option; ?>"><?php echo $optionDisplay; ?></option>
		<?php endforeach; ?>
    	</select>
    	<input type="text" name="source2" id="source2" style="width:120px;display:none;" />
        </div>

        <div class="row">
    	<label for="info"><?php echo $form->label['info']; ?></label>
    	<textarea style="width:360px;height:100px;" rows="5" height="50" width="100" columns="10" name="info" id="info"></textarea>
        </div>
        <script>	
    	jQuery(document).ready(function($) {
    	    $("#source").change(function() {
    		if($(this).find("option:selected").first().attr("value") == "Other") {
    		    $("#source2").fadeIn(300);
    		} else {
    		    $("#source2").fadeOut(300);
    		}
    	    }).change();
                	
    <?php
    foreach (array('formId', 'submitButtonId', 'statusId') as $attr) {
	echo "var $attr = '{$form->config[$attr]}';\n";
    }
    ?>

    	$("#receiveUpdates").each(function() {
	    if(!$(this).is(":checked"))
		$("#receiveUpdates-form").hide();
	}).change(function(e) {
    	    if($(this).is(":checked")) {
    		$("#receiveUpdates-form").slideDown();
    	    } else {
    		$("#receiveUpdates-form").slideUp();
    	    }	
    	});
    	$('#'+submitButtonId).click(function(e) {
    	    e.preventDefault();
                                            	    
    	    var checkBoxInputs = ['subscribe','requestContact','dummy_data'];
    	    var textInputs = ['language','currency','timezone','unique_id'];
    	    var optionalTextInputs = ['firstName','lastName','email','phone','company','position','source','info'];


    	    var form = $("#"+formId);
    	    var status = $("#"+statusId);
    	    var empty = function(s) {
    		return (s==null || s=="" || s==undefined);
    	    }
    	    var elt,elts={},sendOptional = false,postData={},val,idEmail;
    <?php if ($form->config['serverInfo']): ?>
	<?php foreach (array('x2_version', 'php_version', 'db_type', 'db_version', 'GD_support') as $attr): ?>
	    <?php echo "postData.$attr"; ?> = '<?php echo str_replace("'", "\\'", $form->config[$attr]); ?>';
	<?php endforeach; ?>
			postData.serverInfo = 1;
    <?php else: ?>
		    postData.serverInfo = 0;
    <?php endif; ?>

    	    elts.receiveUpdates = form.find('#receiveUpdates');
    	    for(var i in checkBoxInputs) {
    		elt = checkBoxInputs[i];
    		elts[elt] = form.find('#'+elt);
    		postData[checkBoxInputs[i]] = elts[elt].is(":checked") ? 1:0;
    	    }
    	    for(var i in textInputs) {
    		var elt = textInputs[i];
    		elts[elt] = form.find('#'+elt);
    		postData[elt] = elts[elt].val();
    	    }
    	    for(var i in optionalTextInputs) {
    		elt = optionalTextInputs[i];
    		elts[elt] = form.find('#'+elt);
    		val = elts[elt].val();
    		if(!empty(val)) {
    		    sendOptional = true;
    		    postData[elt] = val;
    		}
    	    }
    	    // "Other" field:
    	    if(postData.source==undefined) {
    		elts.source = form.find("#source2");
    		postData.source = elts.source.val();
    	    }
            	    
    	    //
    	    if(postData.email == undefined) {
    		elts.email = form.find('#adminEmail');
    		if(sendOptional) // Send it with other info
    		    postData.email = elts.email.val();
    	    }
            	    
    	    idEmail = elts.email.val();
    	    if(!empty(idEmail)) {
    		postData.emailHash = SHA256(idEmail+SHA256(idEmail));
    		elts.emailHash = elts.email;
    	    }
                        	    
                                                                                                

    	    if(postData.unique_id == 'none' && elts.receiveUpdates.is(":checked")) {
    		// Form hasn't been submitted, or there were validation errors on the last submit.
    		if(!(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(idEmail))) {
    		    // Preliminary email validation. Can't send email address to the server
    		    // unless optional information is being willingly given, so no 
    		    // validation can be performed on a hash.
    		    elts.email.addClass('error');
    		    status.fadeIn(300).append("<span class=\"error\"><?php echo $form->message['emailValidation']; ?></span>");
    		} else {
    		    elts.email.removeClass('error');
    		    status.fadeIn(300).html('<img src="<?php echo $form->config['themeUrl']; ?>/images/loading.gif" style="display:block;margin-left:auto; margin-right:auto;">');
        			
    		    $.ajax({
    			'type':'POST',
    			'url':'http://x2planet.com/installs/registry/new',
    			'data':postData,
    			'complete': function(data,statusObj,jqXHR) {
    			    var response = $.parseJSON(data.responseText);
    			    var messages = "<h3>"+response.message+"</h3>";
    			    if(response.errors != undefined) {
    				messages += '<ul>';
    				for(var attr in response.errors) {
    				    var attrId = attr;
    				    elts[attr].addClass('error');
    				    for (var error in response.errors[attr]) {
    					messages += '<li><span class="error">'+response.errors[attr][error]+'</span></li>';
    				    }
    				}
    				messages += '</ul>';
    				status.html(messages);
    			    } else {
    				if (response.message != undefined) 
    				    status.html(messages);
    				elts.unique_id.val(response.unique_id);
    				document.forms[formId].submit();
    			    }
    			},
    			'error': function(data,statusObj,jqXHR) {
    			    status.html('<?php echo str_replace("'", "\\'", '<h3>' . $form->message['connectionErrHeader'] . '</h3>' . $form->message['connectionErrMessage']); ?>');
    			}
    		    });
    		}
    	    } else {
	    // Submit form as usual
    		document.forms[formId].submit();
    	    }
    	});
        });
                                                                                        	
                                                    	
        </script>
    </div>

<?php else: ?>
    <span><?php echo $form->message['already']; ?></span><br /><br />
<?php endif; ?>
<input type="hidden" name="unique_id" id="unique_id" value="<?php echo $form->config['unique_id']; ?>">
