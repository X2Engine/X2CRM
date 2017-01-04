<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

// run silent installer with default values?
$silent = isset($_GET['silent']) || (isset($argv) && in_array('silent', $argv));

if($silent){
    header('Location: initialize.php?silent');
    exit;
}

include(realpath('protected/config/X2Config.php'));



// scan for installed language folders
$languageDirs = scandir('./protected/messages');
$languages = array();

foreach($languageDirs as $code){  // look for langauges name
    $name = getLanguageName($code);  // in each item in $languageDirs
    if($name !== false)
        $languages[$code] = $name; // add to $languages if name is found
}
$lang = isset($_GET['language']) ? strtolower($_GET['language']) : ''; // get language setting, default to none (english)

if(array_key_exists($lang, $languages)){    // is this language installed?
    $installMessageFile = realpath("protected/messages/$lang/install.php");
    $commonMessageFile = realpath("protected/messages/$lang/common.php");
}

$installMessages = array();
$commonMessages = array();
if(isset($installMessageFile) && file_exists($installMessageFile)){ // attempt to load installer messages
    $installMessages = include($installMessageFile);     // from the chosen language
    if(!is_array($installMessages))
        $installMessages = array();      // ...or return an empty array
}
if(isset($commonMessageFile) && file_exists($commonMessageFile)){ // attempt to load installer messages
    $commonMessages = include($commonMessageFile);     // from the chosen language
    if(!is_array($commonMessages))
        $commonMessages = array();      // ...or return an empty array
}

function getLanguageName($code){ // lookup language name for the language code provided
    global $languageDirs;

    if(in_array($code, $languageDirs)){ // is the language pack here?
        $appMessageFile = realpath("protected/messages/$code/app.php");
        if(file_exists($appMessageFile)){ // attempt to load 'app' messages in
            $appMessages = include($appMessageFile);     // the chosen language
            if(is_array($appMessages) and isset($appMessages['languageName']) && $appMessages['languageName'] != 'Template')
                return $appMessages['languageName'];       // return language name
        }
    }
    return false; // false if languge pack wasn't there
}

// translates by looking up string in install.php language file
function installer_t($str){
    global $installMessages, $commonMessages;
    if(isset($installMessages[$str]) && $installMessages[$str] != ''){  // if the chosen language is available
        return $installMessages[$str];  // and the message is in there, use it
    }elseif(isset($commonMessages[$str]) && $commonMessages[$str] != ''){
        return $commonMessages[$str];
    }
    return $str;
}

$themeURL = 'themes/x2engine';

// check for submitted data (errors from initialize.php)
$dbStatus = '';

if(isset($_GET['errors'])){

    $errorMessagesIni = $_GET['errors'];
    $errorMessages = array();
    $errorCss = array();

    foreach($errorMessagesIni as $message){
        if($message == 'DB_COULD_NOT_SELECT'){
            $dbErr = installer_t('Could not select database.');
            $dbStatus = '<img src="'.$themeURL.'/images/NOT_OK.png">'.addslashes($dbErr);
            $errorMessages[] = $dbErr;
            $errorCss = array_unique(array_merge($errorCss, array('dbName', 'dbUser', 'dbPass')));
        }else if($message == 'DB_CONNECTION_FAILED'){
            $dbErr = installer_t('Could not connect to host.');
            $dbStatus = '<img src="'.$themeURL.'/images/NOT_OK.png">'.addslashes($dbErr);
            $errorMessages[] = $dbErr;
            $errorCss = array_unique(array_merge($errorCss, array('dbHost', 'dbName', 'dbUser', 'dbPass')));
        }else{
            $error = explode('--', $message);
            if(count($error) > 1){
                $errorMessages[] = $error[1];
                $errorCss[] = $error[0];
            }else{
                $errorMessages[] = $message;
            }
        }
    }
}

function getField($name, $default, $return = False){
    $ret = Null;
    if(isset($_GET[$name])){
        if($name == 'dummy_data' && $_GET[$name] == 1)
            $ret = ' checked="checked"';
        else
            $ret = $_GET[$name];
    } else{
        $ret = $default;
    }
    if($return)
        return $ret;
    else
        echo $ret;
}

function checkCurrency($code){
    if(isset($_GET['currency'])){
        if($_GET['currency'] == $code)
            echo ' selected="selected"';
    } else if($code == 'USD')
        echo ' selected="selected"';
}

function checkTimezone($timezone){
    if((isset($_GET['timezone']) && $_GET['timezone'] == $timezone) || (!isset($_GET['timezone']) && $timezone == 'US/Pacific')) //date_default_timezone_get()))
        return ' selected="selected"';
}

date_default_timezone_set(isset($_GET['timezone']) ? $_GET['timezone'] : 'UTC');

$timezones = array(
    'Pacific/Midway' => "(GMT-11:00) Midway Island",
    'US/Samoa' => "(GMT-11:00) Samoa",
    'US/Hawaii' => "(GMT-10:00) Hawaii",
    'US/Alaska' => "(GMT-09:00) Alaska",
    'US/Pacific' => "(GMT-08:00) Pacific Time (US & Canada)",
    'America/Tijuana' => "(GMT-08:00) Tijuana",
    'US/Arizona' => "(GMT-07:00) Arizona",
    'US/Mountain' => "(GMT-07:00) Mountain Time (US & Canada)",
    'America/Chihuahua' => "(GMT-07:00) Chihuahua",
    'America/Mazatlan' => "(GMT-07:00) Mazatlan",
    'America/Mexico_City' => "(GMT-06:00) Mexico City",
    'America/Monterrey' => "(GMT-06:00) Monterrey",
    'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
    'US/Central' => "(GMT-06:00) Central Time (US & Canada)",
    'US/Eastern' => "(GMT-05:00) Eastern Time (US & Canada)",
    'US/East-Indiana' => "(GMT-05:00) Indiana (East)",
    'America/Bogota' => "(GMT-05:00) Bogota",
    'America/Lima' => "(GMT-05:00) Lima",
    'America/Caracas' => "(GMT-04:30) Caracas",
    'Canada/Atlantic' => "(GMT-04:00) Atlantic Time (Canada)",
    'America/La_Paz' => "(GMT-04:00) La Paz",
    'America/Santiago' => "(GMT-04:00) Santiago",
    'Canada/Newfoundland' => "(GMT-03:30) Newfoundland",
    'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
    'Greenland' => "(GMT-03:00) Greenland",
    'Atlantic/Stanley' => "(GMT-02:00) Stanley",
    'Atlantic/Azores' => "(GMT-01:00) Azores",
    'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
    'Africa/Casablanca' => "(GMT) Casablanca",
    'Europe/Dublin' => "(GMT) Dublin",
    'Europe/Lisbon' => "(GMT) Lisbon",
    'Europe/London' => "(GMT) London",
    'Africa/Monrovia' => "(GMT) Monrovia",
    'UTC' => "(UTC)",
    'Europe/Amsterdam' => "(GMT+01:00) Amsterdam",
    'Europe/Belgrade' => "(GMT+01:00) Belgrade",
    'Europe/Berlin' => "(GMT+01:00) Berlin",
    'Europe/Bratislava' => "(GMT+01:00) Bratislava",
    'Europe/Brussels' => "(GMT+01:00) Brussels",
    'Europe/Budapest' => "(GMT+01:00) Budapest",
    'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
    'Europe/Ljubljana' => "(GMT+01:00) Ljubljana",
    'Europe/Madrid' => "(GMT+01:00) Madrid",
    'Europe/Paris' => "(GMT+01:00) Paris",
    'Europe/Prague' => "(GMT+01:00) Prague",
    'Europe/Rome' => "(GMT+01:00) Rome",
    'Europe/Sarajevo' => "(GMT+01:00) Sarajevo",
    'Europe/Skopje' => "(GMT+01:00) Skopje",
    'Europe/Stockholm' => "(GMT+01:00) Stockholm",
    'Europe/Vienna' => "(GMT+01:00) Vienna",
    'Europe/Warsaw' => "(GMT+01:00) Warsaw",
    'Europe/Zagreb' => "(GMT+01:00) Zagreb",
    'Europe/Athens' => "(GMT+02:00) Athens",
    'Europe/Bucharest' => "(GMT+02:00) Bucharest",
    'Africa/Cairo' => "(GMT+02:00) Cairo",
    'Africa/Harare' => "(GMT+02:00) Harare",
    'Europe/Helsinki' => "(GMT+02:00) Helsinki",
    'Europe/Istanbul' => "(GMT+02:00) Istanbul",
    'Asia/Jerusalem' => "(GMT+02:00) Jerusalem",
    'Europe/Kiev' => "(GMT+02:00) Kyiv",
    'Europe/Minsk' => "(GMT+02:00) Minsk",
    'Europe/Riga' => "(GMT+02:00) Riga",
    'Europe/Sofia' => "(GMT+02:00) Sofia",
    'Europe/Tallinn' => "(GMT+02:00) Tallinn",
    'Europe/Vilnius' => "(GMT+02:00) Vilnius",
    'Asia/Baghdad' => "(GMT+03:00) Baghdad",
    'Asia/Kuwait' => "(GMT+03:00) Kuwait",
    'Europe/Moscow' => "(GMT+03:00) Moscow",
    'Africa/Nairobi' => "(GMT+03:00) Nairobi",
    'Asia/Riyadh' => "(GMT+03:00) Riyadh",
    'Europe/Volgograd' => "(GMT+03:00) Volgograd",
    'Asia/Tehran' => "(GMT+03:30) Tehran",
    'Asia/Baku' => "(GMT+04:00) Baku",
    'Asia/Muscat' => "(GMT+04:00) Muscat",
    'Asia/Tbilisi' => "(GMT+04:00) Tbilisi",
    'Asia/Yerevan' => "(GMT+04:00) Yerevan",
    'Asia/Kabul' => "(GMT+04:30) Kabul",
    'Asia/Yekaterinburg' => "(GMT+05:00) Ekaterinburg",
    'Asia/Karachi' => "(GMT+05:00) Karachi",
    'Asia/Tashkent' => "(GMT+05:00) Tashkent",
    'Asia/Kolkata' => "(GMT+05:30) Kolkata",
    'Asia/Kathmandu' => "(GMT+05:45) Kathmandu",
    'Asia/Almaty' => "(GMT+06:00) Almaty",
    'Asia/Dhaka' => "(GMT+06:00) Dhaka",
    'Asia/Novosibirsk' => "(GMT+06:00) Novosibirsk",
    'Asia/Bangkok' => "(GMT+07:00) Bangkok",
    'Asia/Jakarta' => "(GMT+07:00) Jakarta",
    'Asia/Krasnoyarsk' => "(GMT+07:00) Krasnoyarsk",
    'Asia/Chongqing' => "(GMT+08:00) Chongqing",
    'Asia/Hong_Kong' => "(GMT+08:00) Hong Kong",
    'Asia/Irkutsk' => "(GMT+08:00) Irkutsk",
    'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
    'Australia/Perth' => "(GMT+08:00) Perth",
    'Asia/Singapore' => "(GMT+08:00) Singapore",
    'Asia/Taipei' => "(GMT+08:00) Taipei",
    'Asia/Ulaanbaatar' => "(GMT+08:00) Ulaan Bataar",
    'Asia/Urumqi' => "(GMT+08:00) Urumqi",
    'Asia/Seoul' => "(GMT+09:00) Seoul",
    'Asia/Tokyo' => "(GMT+09:00) Tokyo",
    'Asia/Yakutsk' => "(GMT+09:00) Yakutsk",
    'Australia/Adelaide' => "(GMT+09:30) Adelaide",
    'Australia/Darwin' => "(GMT+09:30) Darwin",
    'Australia/Brisbane' => "(GMT+10:00) Brisbane",
    'Australia/Canberra' => "(GMT+10:00) Canberra",
    'Pacific/Guam' => "(GMT+10:00) Guam",
    'Australia/Hobart' => "(GMT+10:00) Hobart",
    'Australia/Melbourne' => "(GMT+10:00) Melbourne",
    'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
    'Australia/Sydney' => "(GMT+10:00) Sydney",
    'Asia/Vladivostok' => "(GMT+10:00) Vladivostok",
    'Asia/Magadan' => "(GMT+11:00) Magadan",
    'Pacific/Auckland' => "(GMT+12:00) Auckland",
    'Pacific/Fiji' => "(GMT+12:00) Fiji",
    'Asia/Kamchatka' => "(GMT+12:00) Kamchatka",
);
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
	<meta charset="UTF-8" />
	<meta name="language" content="en" />
	<title><?php echo installer_t('X2CRM Installation'); ?></title>
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/form.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/install.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ui-elements.css" />

	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/backgroundImage.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>

	<script type="text/javascript" src="js/webtoolkit.sha256.js"></script>

	<script type="text/javascript">

	function installStageRequest(stage,formData,done,fail,always) {
		done = typeof done == 'undefined' ? function(){} : done;
		fail = typeof fail == 'undefined' ? function(){} : fail;
		always = typeof always == 'undefined' ? function(){} : always;
		formData = typeof formData == undefined ? $('form#install').serialize() : formData;
		$.ajax({
			url:'initialize.php?stage='+stage,
			type:'POST',
			data:formData,
			dataType:'json'
		}).done(done).fail(fail).always(always);
	}


	function installStage(stages,formData,form,nDone,responseData) {
		var thisStage = stages[0],stagesRemaining = stages.slice(1);
		var box = $('#error-box');
		if (typeof thisStage != 'undefined') {
			if (thisStage=='validate') {
				installStageRequest('validate',formData,(function(data) {
					if(data.error || data.errors || data.globalError) {
						box.html($("<h3>").text(data.message));
						if(data.globalError)
							box.append($("<span>").text(data.globalError).addClass('error'));
						var errorList = $('<ul>');
						for (var i in data.errors) {
							errorList.append($('<li>').text(data.errors[i]).addClass('error'));
							form.find("#"+i).addClass('error');
						}
						box.append(errorList);
					} else {
						installStage(stagesRemaining,formData,form,nDone+1,data);
					}
				}),(function(jqXHR,textStatus,errorMessage) {
					alert('An unexpected server error occurred during validation: '+textStatus+' '+jqXHR.status+' '+errorMessage);
				}));
			} else {
				var messageHeader = box.find('h3');
				var percentDone = messageHeader.find('#percentDone');
				var progressList = box.find('ul');
				if(percentDone.length == 0) {
					progressList.remove(); // Get rid of any error messages
					box.append($('<img src="<?php echo $themeURL; ?>/images/loading.gif">').css({'display':'block','margin-left':'auto','margin-right':'auto'}));
					messageHeader.text("<?php echo installer_t("Installing X2CRM"); ?>");
					percentDone = $('<span id="percentDone">');
					messageHeader.append(percentDone);
					progressList = $('<ul>');
					progressList.insertAfter(messageHeader);
				}
				installStageRequest(thisStage,formData,(function(data) {
					progressList.append($('<li>').text(data.message).css({color: (data.error ? 'red':'green')}));
					if(!data.error)
						installStage(stagesRemaining,formData,form,nDone+1,data);
					else
						box.find('img').remove();
				}),(function(jqXHR,textStatus,errorMessage) {
					alert('An unexpected server error occurred during installation: '+textStatus+' '+jqXHR.status+' '+errorMessage);
				}));
			}
		} else {
			// Submit the form, mark as complete.
			form.find("#complete").val(1);
			document.forms[form.attr('id')].submit();
		}
	}

	submitExternalForm = function() {
		(function($){
			var form = $('form#install');
			form.find('.error').removeClass('error');
			var stages = <?php $stageLabels = require_once(realpath('protected/data/installStageLabels.php')); echo '["'.implode('","',array_keys($stageLabels)).'"]'; ?>;
			installStage(stages,form.serialize(),form,0);
		})(jQuery);
	}


	function changeLang(lang) {
		window.location=('install.php?language='+lang);
	}
	$(function() {
		$('#db-test-button').click(testDB);

		$('#currency').change(function() {
			if($('#currency').val() == 'other')
				$('#currency2').fadeIn(300);
			else
				$('#currency2').fadeOut(300);
		});

		$('#test_db').change(function() {
			if($('#test_db').is(':checked'))
				$('#test_db_notice').fadeIn(300);
			else
				$('#test_db_notice').fadeOut(300);
		});

<?php if (!empty($errorMessages)): // Add error class to fields that failed validation ?>
		$("#install").find("#<?php echo implode(',#', $errorCss); ?>").addClass('error');
<?php endif; ?>
        });


        function testDB() {
            var data = $('#install').serialize()+'&testDb=1';
            $.ajax({
                type: "POST",
                url: "initialize.php",
                data: data,
                dataType:'json',
                beforeSend: function() {
                    $('#response-box').html('<img src="<?php echo $themeURL; ?>/images/loading.gif">');
                }
            }).done(function(r) {
                var message = '';
                var okImage = '<img src="<?php echo $themeURL; ?>/images/OK.png">';
                var notOkImage = '<img src="<?php echo $themeURL; ?>/images/NOT_OK.png">';
                if (r.errors || r.globalError || r.error) {
                    message =  notOkImage + '<span class="error">'+r.message+'</span>';
                } else {
                    message = okImage + r.message;
                }
                $('#response-box').html(message);
            });
        }

        </script>
    </head>
    <body class='not-mobile-body'>
    <!--<img id="bg" src="uploads/defaultBg.jpg" alt="">-->
        <div id="installer-box">
            <noscript><h3><span id="noscript-error"><?php echo installer_t('This web application requires Javascript to function properly. Please enable Javascript in your web browser before continuing.'); ?></span></h3></noscript>
            <?php
//            $edSuf = array('_pla','_pro','');//'images/x2engine_crm_pla.png';
//            foreach($edSuf as $suffix) {
//                $logoFile = "images/x2engine_crm$suffix.png";
//                if(file_exists(__DIR__.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$logoFile))) {
//                    echo "<img src=\"$logoFile\" alt=\"X2CRM\" id=\"installer-logo\">";
//                    break;
//                }
//            }
            $logoFile = "images/mobile_logo.png";
            echo "<img src=\"$logoFile\" alt=\"X2CRM\" id=\"installer-logo\">";
            ?>
            <h2 id="title"><?php echo installer_t('Install X2CRM Version').'&nbsp'.$version; ?></h2>



<?php echo installer_t('Welcome to the X2CRM application installer! We need to collect a little information before we can get your application up and running. Please fill out the fields listed below.'); ?>


            <div class="wide form" id="install-form">
                <?php
                $thisFile = __FILE__;
                $reqCheck = 'protected/components/views/requirements.php';
                if(file_exists($reqCheck))
                    require_once($reqCheck);
                else
                    echo "<span class=\"error\">Note: cannot find requirements check script.</span>";
                ?>
                <form name="install" id="install" action="initialize.php" method="POST">
                    <h2><?php echo installer_t('Application Info'); ?></h2><hr>
                    <div class="row"><label for="app"><?php echo installer_t('Application Name'); ?></label><input type="text" name="app" id="app" value="<?php getField('app', 'X2CRM'); ?>" style="width:190px" /></div>
                    <div class="row"><label for="language"><?php echo installer_t('Default Language'); ?></label>
                        <select name="language" id="language" onChange="changeLang(this.options[this.selectedIndex].value);" style="width:200px"><option value="">English</option>
                            <?php
                            foreach($languageDirs as $code){ // generate language dropdown
                                $languageName = getLanguageName($code); // lookup language name
                                if($languageName !== false){
                                    $selected = ($code == $lang) ? ' selected' : ''; // mark option selected if user has chosen this language
                                    echo "		<option value=\"$code\"$selected>$languageName</option>\n"; // list all available languages
                                }
                            }
                            echo '</select>';
                            ?></div>

                    <div class="row"><label for="currency"><?php echo installer_t('Currency'); ?></label>
                        <select name="currency" id="currency">
                            <option value="USD"<?php checkCurrency('USD'); ?>>USD</option>
                            <option value="EUR"<?php checkCurrency('EUR'); ?>>EUR</option>
                            <option value="GBP"<?php checkCurrency('GBP'); ?>>GBP</option>
                            <option value="CAD"<?php checkCurrency('CAD'); ?>>CAD</option>
                            <option value="JPY"<?php checkCurrency('JPY'); ?>>JPY</option>
                            <option value="CNY"<?php checkCurrency('CNY'); ?>>CNY</option>
                            <option value="CHF"<?php checkCurrency('CHF'); ?>>CHF</option>
                            <option value="INR"<?php checkCurrency('INR'); ?>>INR</option>
                            <option value="BRL"<?php checkCurrency('BRL'); ?>>BRL</option>
                            <option value="other"<?php checkCurrency('other'); ?>><?php echo installer_t('Other'); ?></option>
                        </select>
                        <input type="text" name="currency2" id="currency2" style="width:120px;<?php if(!isset($_GET['currency']) || $_GET['currency'] != 'other') echo 'display:none;'; ?>" value="<?php getField('currency2', ''); ?>" />
                    </div>
                    <div class="row"><label for="timezone"><?php echo installer_t('Default Timezone'); ?></label>
                        <select name="timezone" id="timezone">
                            <?php
                            foreach($timezones as $key => $value)
                                echo '<option value="'.$key.'"'.checkTimezone($key).'>'.$value.'</option>';
                            ?>
                        </select>
                    </div>
                    <div class="row"><label for="dummy_data"><?php echo installer_t('Create sample data'); ?></label><input type='checkbox' name='dummy_data' id="dummy_data" value='1' <?php echo getField('dummy_data', 0, true) ? ' checked=1' : ''; ?> /><br /><br /></div>
                    <div class="row"><label for="adminUsername"><?php echo installer_t('Admin Username'); ?></label><input type="text" name="adminUsername" id="adminUsername" value="<?php getField('adminUsername', 'admin'); ?>" /></div>
                    <div class="row"><label for="adminPass"><?php echo installer_t('Admin Password'); ?></label><input type="password" name="adminPass" id="adminPass" /></div>
                    <div class="row"><label for="adminPass2"><?php echo installer_t('Confirm Password'); ?></label><input type="password" name="adminPass2" id="adminPass2" /></div>
                    <div class="row"><label for="adminEmail"><?php echo installer_t('Administrator Email'); ?></label><input type="text" name="adminEmail" id="adminEmail" value="<?php getField('adminEmail', ''); ?>" /></div>
                    <h2><?php echo installer_t('Visible Modules'); ?></h2><hr>
                    <div id="menu_items" class="row">
                        <?php echo installer_t('Choose which modules will be visible in the main menu. Any of these can be re-enabled after installation if necessary.'); ?><br /><br />
                        <?php
                        $modules = require_once(dirname(__FILE__).implode(DIRECTORY_SEPARATOR, array('', 'protected', 'data', '')).'enabledModules.php');
                        $disabledByDefault = array('products', 'quotes', 'bugReports', 'x2Leads');
                        foreach($modules as $moduleName):
                            $item = "menu_$moduleName";
                            if (function_exists('ucfirst')) { 
                                $moduleLabel = ucfirst($moduleName);
                            } else {
                                $moduleLabel = $moduleName;
                            }
                            if ($moduleLabel === 'Workflow') {
                                $moduleLabel = 'Process';
                            } else if ($moduleLabel === 'X2Leads') {
                                $moduleLabel = 'Leads';
                            } else if ($moduleLabel === 'X2Activity') {
                                $moduleLabel = 'Activity';
                            } else if ($moduleLabel === 'BugReports') {
                                $moduleLabel = 'Bug Reports';
                            } else if ($moduleLabel === 'EmailInboxes') {
                                $moduleLabel = 'Email Inboxes';
                            }
                            ?>
                            <div class="checkbox-grid-cell">
                                <label for="<?php echo $item ?>"><?php echo $moduleLabel; ?></label>
                                <input type="checkbox" name="<?php echo $item ?>" id="<?php echo $item; ?>" value="1"<?php 
                                 echo getField($item, 1, !in_array($moduleName, $disabledByDefault)) ? ' checked=1' : ''; ?> />
                            </div>
<?php endforeach; ?>
                    </div>

                    <h2><?php echo installer_t('Database Connection Info'); ?></h2><hr>
<?php echo installer_t('This release only supports MySQL. Please create a database before installing.'); ?><br /><br />

                    <div id="db-form-box">
                        <div class="row"><label for="dbHost"><?php echo installer_t('Host Name'); ?></label><input type="text" name="dbHost" id="dbHost" value="<?php getField('dbHost', 'localhost'); ?>" /></div>
                        <div class="row"><label for="dbName"><?php echo installer_t('Database Name'); ?></label><input type="text" name="dbName" id="dbName" value="<?php getField('dbName', 'x2engine'); ?>" /></div>
                        <div class="row"><label for="dbUser"><?php echo installer_t('Username'); ?></label><input type="text" name="dbUser" id="dbUser" value="<?php getField('dbUser', 'root'); ?>" /></div>
                        <div class="row"><label for="dbPass"><?php echo installer_t('Password'); ?></label><input type="password" name="dbPass" id="dbPass" /></div>
                        <div class="row"><label for="test_db"><?php echo installer_t('Testing database'); ?></label><input type='checkbox' name='test_db' id="test_db" value='1' <?php echo getField('test_db', 0, true) ? ' checked=1' : ''; ?> /> </div>
                        <div class="row" id="test_db_notice" style="display:none;padding-bottom:20px;">
                            Enable this only if reinstalling on a separate database for automated testing. <em>Do not</em> use the same database for testing and production.
                            <br /><br />
                            To set up the configuration for web testing, ensure the following URL correctly resolves to index-test.php on this server:
                            <br />
                            <label for="test_url"><?php echo installer_t('Test base URL'); ?></label><input type="text" name='test_url' id="test_url" value="<?php getField('test_url', 'http://'.rtrim($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], 'install.php').'index-test.php'); ?>" />
                        </div>
                    </div>
                    <div id="db-test-box"><input type="button" id="db-test-button" class="x2-button" value="<?php echo installer_t('Test Connection'); ?>" />
                        <div id="response-box"><?php echo $dbStatus; ?></div>
                    </div>

                    <div></div>

                    <br /><br /><br />

            <div><?php if(file_exists('install_pro.php')) include('install_pro.php'); ?></div>


			<?php
			include(realpath('protected/components/UpdatesForm.php'));
			// Configuration for the updates / optional info form:
			$edition = 'opensource';
			$form = new UpdatesForm(
							array(
								'x2_version' => $version,
								'unique_id' => getField('unique_id', 'none', True),
								'formId' => 'install',
								'submitButtonId' => 'install-button',
								'statusId' => 'error-box',
								'themeUrl' => $themeURL,
								'receiveUpdates' => getField('receiveUpdates', 1, True),
								'edition' => $edition,
							),
							'installer_t'
			);
			require_once(realpath('protected/views/admin/stayUpdated.php'));
			?>


                        <?php $haveErrors = !empty($errorMessages); ?>
                    <hr />
                    <div class="form" id="error-box"<?php echo $haveErrors ? Null : ' style="display:none;"'; ?>>
                            <?php if($haveErrors): ?>
                            <h3><?php echo installer_t("Please correct the following errors:") ?></h3>
                            <ul>
                                <?php foreach($errorMessages as $message): ?>
                                    <li><?php echo $message; ?></li>
                            <?php endforeach; ?>
                            </ul>
<?php endif; ?>
                    </div>
                    <input type="hidden" id="complete" name="complete" value="0" />
                    <input type="submit" id="install-button" class="x2-button" value="<?php echo installer_t('Install'); ?>" />
                    <br />
                </form>
                <a style="text-align: center; display:block;" href="http://www.x2crm.com"><?php echo installer_t('For help or more information - X2CRM.com'); ?></a>

            </div>
            <div id="footer">


                Copyright &copy; <?php echo date('Y'); ?><a href="http://www.x2crm.com">X2Engine Inc.</a><br />
<?php echo installer_t('All Rights Reserved.'); ?>
            </div>
        </div>
    </body>
</html>
