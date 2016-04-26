<?php
// Authorized Partner/Reseller login page content
/* @start:login */
?>
<div class="cell partner-logo-cell">
    <img id='partner-login-logo' src="data:image/gif;base64,<?php echo base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'partnerLoginLogo_example.gif')); ?>" />
</div>
<div id='x2-partner-info'>
    <div>[Your product name here] is a registered trademark of [Your company name here], an authorized partner of X2Engine, Inc.</div>
</div>

<div id='partner-login-info-how-to'>For instructions on how to edit this content, login and use the link in the footer or view the file <em>protected/partner/README.md</em></div>

<?php /* @end:login */ ?>
