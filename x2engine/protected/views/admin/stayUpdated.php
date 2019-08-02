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





/**
 * @file stayUpdated.php
 * 
 * Self-contained updates registration form.
 * 
 * Generates all the javascript that it needs to run properly, and does not 
 * require Yii to function, so it can be used in the installer. Requires $form 
 * be an UpdatesForm instance; that is how the form is configured.
 * 
 * @package application.views.admin 
 */

$protocol = (!empty ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

?>
<?php echo "\n<!-- \begin{UpdatesForm} -->"; ?>
<?php echo $form->wrapTitle($form->os ? $form->message['updatesTitle'] : $form->message['registrationTitle']); ?>
<hr />
<?php if (in_array($form->config['unique_id'],array('none',Null))): ?>
<?php if ($form->os && !$form->config['isUpgrade']): ?>
		<div class="row">
			<label for="receiveUpdates"><?php echo $form->label['receiveUpdates']; ?></label><input type="checkbox" value='1' <?php echo $form->config['receiveUpdates'] ? 'checked="checked"' : Null; ?> name="receiveUpdates" id="receiveUpdates" />
		</div><!-- .row -->
<?php
	else:
		$mandatoryFields = array('unique_id', 'firstName', 'lastName', 'email');
		if (in_array($form->config['unique_id'],array(Null,'none'))) {
			echo '<span class="registration-sub-text">'.$form->message['registrationSubtext'] . '</span>';
			$form->textFields($mandatoryFields);
			echo '<br>'.$form->message['registrationPostText'].': <a href="http://www.x2crm.com/contact-us/">x2crm.com</a><br><br>';
		} else {
			echo $form->message['registrationSuccess'] . '<br><br>';
			$form->hiddenFields($mandatoryFields);
		}
		echo '<input type="checkbox" style="display:none" value="1"'.($form->config['receiveUpdates'] ? ' checked="checked"' : Null).' name="receiveUpdates" id="receiveUpdates" />';

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
		<br><?php echo $form->message['intro']; ?><br><br>
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
			<textarea class='x2-extra-wide-input' style="height:100px;" rows="5" height="50" width="100" columns="10" name="info" id="info"></textarea>
		</div><!-- .row -->
	</div><!-- #receiveUpdates-form -->

	<script>
<?php foreach (array('formId', 'submitButtonId', 'statusId') as $attr): ?>
		<?php echo $attr; ?> = '<?php echo $form->config[$attr]; ?>';
<?php endforeach; ?>
		
		$(function() {
			$.support.cors = true;
			if (typeof submitExternalForm === 'undefined') {
				submitExternalForm = function() {document.forms[formId].submit();};
			}
			var isos = <?php echo $form->os ? 'true' : 'false' ; ?>;
			$("#source").change(function() {
				if($(this).find("option:selected").first().attr("value") == "Other") {
					$("#source2").fadeIn(300);
				} else {
					$("#source2").fadeOut(300);
				}
			}).change();
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
				
//				// Admin email as a backup
				idEmail = elts.email.val();
				
				// Send a salted hash of the email address to identify the 
				// user while respecting their privacy; if no optional PII 
				// is submitted, the only thing that identifies them is the 
				// hash of the email.
				if (!empty(idEmail)) {
					postData.emailHash = SHA256(idEmail+SHA256(idEmail));
				}
				var loadingImg = $('<img src="<?php echo $form->config['themeUrl']; ?>/images/loading.gif">').css({'display':'block','margin-left':'auto','margin-right':'auto'});
                
                /**
                 * Response data handler
                 */
				var handleResponse = function(data,statusObj,jqXHR) {
						var messages = "<h3>"+data.message+"</h3>";
						if(data.errors != undefined || data.log != undefined) {
							messages += '<ul id="registryerrors">';
							if(data.errors != undefined) {
								for(var attr in data.errors) {
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
							if (data.message)
								status.html(messages);
							elts.unique_id.val(data.unique_id);
							if(!isos)
								elts.edition.val(data.edition);
							setTimeout(function(){submitExternalForm();},500);
						}
					};
                var handleErr = function() {
                            status.html('<?php echo str_replace("'", "\\'", '<h3>' . $form->message['connectionErrHeader'] . '</h3>' . ($form->os ? $form->message['connectionErrMessage'] : $form->message['connectionNOsMessage'])); ?>');
                        };
                    
                // Now it is time to connect to the updates server
				if(!isos || ((postData.unique_id == 'none' || empty(postData.unique_id)) && elts.receiveUpdates.is(":checked"))) {
                    var submitToUrl = '<?php echo $protocol; ?>://x2planet.com/installs/registry/<?php echo $form->os ? 'new' : 'register'; ?>';
					form.find('.error').removeClass('error');
					status.fadeIn(300).html(loadingImg);
                    if($.browser.msie || typeof window.XDomainRequest != 'undefined') {
                        // Internet Explorer needs its own way of connecting to
                        // the updates server 'cause it's special
                        var xdr = new XDomainRequest();
                        var formData = $.param(postData);
                        xdr.onerr = handleErr;
                        xdr.onload = function() {
                            handleResponse(JSON.parse(xdr.responseText));
                        }
                        xdr.ontimeout = handleErr;
                        xdr.timeout = 10000;
                        xdr.open("POST", submitToUrl);
                        xdr.send(formData);
                        return 0;
                    }
                    // For every other browser it's much simpler:
					$.ajax({
						type:'POST',
						url:submitToUrl,
						data:postData,
						dataType:'json'
					}).done(handleResponse).fail(function(data,statusObj,jqXHR) {
						handleErr();
					});
				} else {
					// Submit form as usual
					status.fadeIn(300).html('<h3></h3><ul></ul>');
					submitExternalForm();
				}
			});
		});
	</script>
	
<?php else: ?>
	<span><?php echo $form->os ? $form->message['already'] : $form->message['registrationSuccess']; ?></span><br><br>
	
<?php endif;
if ($form->os || !in_array($form->config['unique_id'],array('none',Null))): ?>
	<input type="hidden" name="unique_id" id="unique_id" value="<?php echo $form->config['unique_id']; ?>">
<?php endif; ?>

<?php // Here we need the check for the edition type ?>
<input type="hidden" name="edition" id="edition" value="<?php echo $form->config['edition']; ?>" >

<!-- \end{UpdatesForm} -->
