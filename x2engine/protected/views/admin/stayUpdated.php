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


/**
 * @file stayUpdated.php
 * 
 * Self-contained updates registration form.
 * 
 * Generates all the javascript that it needs to run properly, and does not 
 * require Yii to function, so it can be used in the installer. Requires $form 
 * be an UpdatesForm instance; that is how the form is configured.
 * 
 * @package X2CRM.views.admin 
 */

?>
<?php echo "\n<!-- \begin{UpdatesForm} -->"; ?>
<?php echo $form->wrapTitle($form->os ? $form->message['updatesTitle'] : $form->message['registrationTitle']); ?>
<hr />
<?php if (in_array($form->config['unique_id'],array('none',Null))): ?>
<?php if ($form->os): ?>
		<div class="row">
			<label for="receiveUpdates"><?php echo $form->label['receiveUpdates']; ?></label><input type="checkbox" value='1' <?php echo $form->config['receiveUpdates'] ? 'checked="checked"' : Null; ?> name="receiveUpdates" id="receiveUpdates" />
		</div><!-- .row -->
<?php
	else:
		include($form->nosForm);
	endif;
?>
	<div id="receiveUpdates-form">
		<?php echo $form->wrapTitle($form->message['optionalTitle']); ?><hr />
		<div class="row">
			<label for="subscribe"><?php echo $form->label['subscribe']; ?></label><input type="checkbox" value='1' name="subscribe" id="subscribe" />
		</div><!-- .row -->
		<div class="row">
			<label for="requestContact"><?php echo $form->label['requestContact']; ?></label><input type='checkbox' name='data' value='1' name="requestContact" id="requestContact" />
		</div><!-- .row -->
		<br /><?php echo $form->message['intro']; ?><br /><br />
		<?php $form->textFields($form->os ? array('firstName', 'lastName', 'email', 'phone', 'company', 'position') : array('phone', 'company', 'position')); ?>
		<div class="row">
			<label for="source"><?php echo $form->label['source']; ?></label>
			<select name="source" id="source">
<?php foreach ($form->leadSources as $option => $optionDisplay): ?>
				<option value="<?php echo $option; ?>"><?php echo $optionDisplay; ?></option>
<?php endforeach; ?>
			</select>
			<input type="text" name="source2" id="source2" style="width:120px;display:none;" />
		</div><!-- .row -->

		<div class="row">
			<label for="info"><?php echo $form->label['info']; ?></label>
			<textarea style="width:360px;height:100px;" rows="5" height="50" width="100" columns="10" name="info" id="info"></textarea>
		</div><!-- .row -->
	</div><!-- #receiveUpdates-form -->

	<script>	
		jQuery(document).ready(function($) {
			var isos = <?php echo $form->os ? 'true' : 'false' ; ?>;
			$("#source").change(function() {
				if($(this).find("option:selected").first().attr("value") == "Other") {
					$("#source2").fadeIn(300);
				} else {
					$("#source2").fadeOut(300);
				}
			}).change();
<?php foreach (array('formId', 'submitButtonId', 'statusId') as $attr): ?>
			var <?php echo $attr; ?> = '<?php echo $form->config[$attr]; ?>';
<?php endforeach; ?>
<?php if ($form->os): ?>
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
<?php endif; ?>
			$('#'+submitButtonId).click(function(e) {
				e.preventDefault();
				
				var PII = ['firstName','lastName','email'];
				var checkBoxInputs = ['subscribe','requestContact','dummy_data'];
				var textInputs = ['language','currency','timezone','unique_id','edition'];
				var optionalTextInputs = ['phone','company','position','source','info'];
				if(isos)
					optionalTextInputs = optionalTextInputs.concat(PII);
				else
					textInputs = textInputs.concat(PII);


				var form = $("#"+formId);
				var status = $("#"+statusId);
				var empty = function(s) {
					return (s==null || s=="" || s==undefined);
				}
				var elt,elts={},sendOptional = false,postData={},val,idEmail;
<?php if ($form->config['serverInfo']): ?>
<?php foreach (array('x2_version', 'php_version', 'db_type', 'db_version', 'GD_support') as $attr): ?>
				postData.<?php echo $attr; ?> = '<?php echo str_replace("'", "\\'", $form->config[$attr]); ?>';
<?php endforeach; ?>
				postData.serverInfo = 1;
<?php else: ?>
				postData.serverInfo = 0;
<?php endif; ?>

				elts.receiveUpdates = form.find('#receiveUpdates');
				
				// Get data from the form. Can't simply use form.serialize() 
				// because then it would be scraping info like the database 
				// password, which would be bad.
				
				// Get checkbox states 
				for(var i in checkBoxInputs) {
					elt = checkBoxInputs[i];
					elts[elt] = form.find('#'+elt);
					postData[checkBoxInputs[i]] = elts[elt].is(":checked") ? 1:0;
				}
				// Get required text inputs (will be sent even if empty)
				for(var i in textInputs) {
					var elt = textInputs[i];
					elts[elt] = form.find('#'+elt);
					postData[elt] = elts[elt].val();
				}
				// Get optional text inputs
				for(var i in optionalTextInputs) {
					elt = optionalTextInputs[i];
					elts[elt] = form.find('#'+elt);
					val = elts[elt].val();
					if(!empty(val)) {
						sendOptional = true;
						postData[elt] = val;
					}
				}
				// "Other" field in lead source:
				if(postData.source=='Other') {
					elts.source = form.find("#source2");
					postData.source = elts.source.val();
				}
				// Admin email as a backup
				elts.adminEmail = form.find('#adminEmail');
				idEmail = elts.email.val();
				
				if(isos && empty(idEmail)) {
					// Contact email assumed same as admin email if empty.
					idEmail = elts.adminEmail.val();
					if(sendOptional) // Send it with other info
						postData.email = idEmail;
				}
				
				// Send a salted hash of the email address to identify the 
				// user while respecting their privacy; if no optional PII 
				// is submitted, the only thing that identifies them is the 
				// hash of the email.
				postData.emailHash = SHA256(idEmail+SHA256(idEmail));
				elts.emailHash = elts.email;
				console.log('Criteria for posting form:');
				console.log(!isos || ((postData.unique_id == 'none' || empty(postData.unique_id)) && elts.receiveUpdates.is(":checked")));
				console.log(isos);
				console.log(postData.unique_id);
				console.log(elts.receiveUpdates.is(':checked'));

				if(!isos || ((postData.unique_id == 'none' || empty(postData.unique_id)) && elts.receiveUpdates.is(":checked"))) {
					// Form hasn't been submitted, or there were validation 
					// errors on the last submit. Try submitting again.
					// Submission: 
					// isos implies unique_id not set properly & receiveUpdates checked
					// (otherwise, submit one way or another)
					var emlEl = elts['adminEmail'];
					var proceed = true;
					emlEl.removeClass('error');
					if(!(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i.test(emlEl.val()))) {
						// Preliminary email validation. Can't send email
						// address to the server in plain text unless optional
						// PII is being willingly given, so no validation can be
						// performed on a hash, hence admin email is needed.
						emlEl.addClass('error');
						proceed = false;
					}

					if(!proceed) {
						status.fadeIn(300).html('<span class="error"><?php echo str_replace("'","\\'",$form->message['emailValidation']); ?></span>');
					
					} else {
						form.find('.error').removeClass('error');
						var loadingImg = $('<img src="<?php echo $form->config['themeUrl']; ?>/images/loading.gif">').css({'display':'block','margin-left':'auto','margin-right':'auto'});
						status.fadeIn(300).html(loadingImg);

						$.ajax({
							type:'POST',
							url:'http://x2planet.com/installs/registry/<?php echo $form->os ? 'new' : 'register'; ?>',
							data:postData,
							dataType:'json'
						}).success(function(data,statusObj,jqXHR) {
							var messages = "<h3>"+data.message+"</h3>";
							if(data.errors != undefined || data.log != undefined) {
								messages += '<ul>';
								if(data.errors != undefined) {
									for(var attr in data.errors) {
										var attrId = attr;
										elts[attr].addClass('error');
										for (var error in data.errors[attr]) {
											messages += '<li><span class="error">'+data.errors[attr][error]+'</span></li>';
										}
									}
									
								}
								if(data.log != undefined) {
									for(var i in data.log) {
										messages += '<li style="color:green">'+data.log[i]+'</li>';
									}
								}
								
								messages += '</ul>';
								status.html(messages);
								
							} else {
								if (data.message != undefined) 
									status.html(messages);
								elts.unique_id.val(data.unique_id);
								if(!isos)
									elts.edition.val(data.edition);
								document.forms[formId].submit();
								status.append(loadingImg);
							}
						}).error(function(data,statusObj,jqXHR) {
							status.html('<?php echo str_replace("'", "\\'", '<h3>' . $form->message['connectionErrHeader'] . '</h3>' . ($form->os ? $form->message['connectionErrMessage'] : $form->message['connectionNOsMessage'])); ?>');
						});
					}
				} else {
					// Submit form as usual
					document.forms[formId].submit();
				}
			});
		});
	</script>
	
<?php else: ?>
	<span><?php echo $form->os ? $form->message['already'] : $form->message['registrationSuccess']; ?></span><br /><br />
	
<?php endif;
if ($form->os || !in_array($form->config['unique_id'],array('none',Null))): ?>
	<input type="hidden" name="unique_id" id="unique_id" value="<?php echo $form->config['unique_id']; ?>">
<?php endif; ?>

<?php // Here we need the check for the edition type ?>
<input type="hidden" name="edition" id="edition" value="<?php echo $form->config['edition']; ?>" >

<!-- \end{UpdatesForm} -->
