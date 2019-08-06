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




// $translation = 'Field\'s wi\th <span class="required">*</span> are required.';
// $test =  htmlspecialchars(stripslashes($translation));
// echo addcslashes(htmlspecialchars_decode($test),'\'');
// die();
// $test = 'You may option"al\'ly enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.';
// header('Content-Type: text/html; charset=utf-8');
// if(!empty($_POST))
	// die('eeeee');
// $test = isset($_POST['test'])? $_POST['test'] : '';
// $test = 'o\'really?<b>"&lt;"</b>';
// $test = htmlspecialchars(stripslashes($test),ENT_COMPAT,'UTF-8',true);
// $test = '&lt;b&gt;&amp;lt;';
// echo addcslashes(htmlspecialchars_decode($test),'\'');
// echo $test;
function decodeQuotes($str) {
	return preg_replace('/&quot;/','"',$str);
}
function encodeQuotes($str) {
	return htmlspecialchars($str);
	// return preg_replace('/"/','&quot;',$str);
}

// echo '<form method="POST" action="translationManager.php"><input size="180" name="test" type="text" value="'.encodeQuotes($test).'"></form>';

// die();

if(!isset($messagePath))
	$messagePath = 'protected/messages';

// die($messagePath);
$targetFile = '';
if(isset($_GET['file'])){
    if(strpos($_GET['file'],'/')!==false){
        throw new CHttpException(400,'This file is not within allowed translations paths. Do not repeat this request.');
    }
}
if(isset($_GET['file']) && file_exists($messagePath.'/template/'.$_GET['file']))
	$targetFile = $_GET['file'];

if(!file_exists($messagePath))
	die('Error: '.$messagePath.' not found.');
$messageDir = scandir($messagePath);

$messages = array();

foreach($messageDir as $langPack) {
	if($langPack == '.' || $langPack == '..' || filetype($messagePath.'/'.$langPack) != "dir")
		continue;

	$messageFiles = scandir($messagePath.'/'.$langPack);
	if(file_exists($messagePath.'/'.$langPack.'/app.php')) {
		$appFile = include($messagePath.'/'.$langPack.'/app.php');
		if(isset($appFile['languageName']) && !empty($appFile['languageName']))
			$messages[$langPack][] = $appFile['languageName'];
		else
			continue;
	}

	foreach($messageFiles as $messageFile) {
		if($messageFile == '.' || $messageFile == '..' || filetype($messagePath.'/'.$langPack.'/'.$messageFile) == "dir")
			continue;
		$messages[$langPack][] = $messageFile;
	}
}
// echo var_dump($messages);
// die();
if(!array_key_exists('template',$messages))
	die('Error: Template files not found.');




if(isset($_POST['data']) && isset($_POST['file'])) {
    ini_set('max_input_vars',9999);
	foreach($messages as $langPack=>$messageFiles) {
		if(!isset($_POST['data'][$langPack])) //|| !in_array($_POST['file'],$messageFiles))
			die('Error: language pack <b>'.strtoupper($langPack).'</b> missing.');
			// die('Error: <b>'.$langPack.'/'.$_POST['file'].'</b> missing.');
	}

	$fileHeader = '<?php
return array (
';

	foreach(array_keys($messages) as $langPack) {
        // open all files to be rewritten
		$file = fopen($messagePath.'/'.$langPack.'/'.$_POST['file'],'w');	

		fwrite($file,$fileHeader);

		$index = 0;
		for($i=0; $i<count($_POST['data']['template']); $i++) {

			$line = $_POST['data']['template'][$i];
			// foreach($fileHandles as $langPack=>$file) {

			if(preg_match('/^s*\/\/\s*$/u',$line)) {
				fwrite($file,"\n");
			} else if(preg_match('/^s*\/\/.*$/u',$line)) {
				fwrite($file,$line."\n");
			} else {
				if($langPack == 'template') {
					if($line == 'languageName')
						fwrite($file,"'languageName'=>'Template',\n");
					else
						fwrite($file,"'".addcslashes(decodeQuotes($line),'\'')."'=>'',\n");
				} else{
                    if(isset($_POST['data'][$langPack][$index])){
                        fwrite(
                            $file,
                            "'".addcslashes(decodeQuotes($line),'\'')."'=>'". 
                                addcslashes(decodeQuotes($_POST['data'][$langPack][$index]),'\'') .
                                "',\n");
                    }else{
                        fwrite($file,"'".addcslashes(decodeQuotes($line),'\'')."'=>'". '' ."',\n");
                    }
                }
                $index++;
			}
		}
		fwrite($file,');');
		fclose($file);
	}


	// die(var_dump($_POST['data']));
	// header('Location: '.$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta name="language" content="en" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>X2Engine Translation Manager</title>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/translationManager.css" />
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript">
var lang = 'none';

$(function() {
	$('input[type="text"]').bind('change keydown',function() {
		if(this.value==this.defaultValue)
			$(this).addClass('modified');
		else
			$(this).removeClass('modified');
	});

	$('#translationForm').submit(function() { $('input.comment').val(function(index,value) { return '// '+value; }); });

	$('.content table').delegate('a.add-comment','click',function() { addComment(this); return false; })
		.delegate('a.add-entry','click',function() { addLine(this); return false; })
		.delegate('a.remove','click',function() { removeLine(this); return false; })
		.delegate('.language','click',function() { googleTranslate(this); });

	showLang('none',null);
});
function showAll(object) {
	if(lang != 'all')
		$(object).closest('tr').find('.translation, .language').not('.'+lang).toggle();
}

function showLang(newLang,object) {
	lang = newLang;
	$('#languageMenu tr').removeClass();
	$(object).closest('tr').addClass('active');

	// var lang = $('#langDropdown').val();
	if(lang == 'all')
		$('.translation, .language').show();
	else {
		$('.translation, .language').hide();
		if(lang != 'none')
			$('.translation.'+lang+', .language.'+lang).show();
	}
}

function googleTranslate(object) {
	var message = $(object).closest('tr').find('.source input').val();
	var lang = $(object).html();
	if(lang == 'zh_cn')
		lang = 'zh-CN';
    if(lang == 'he')
        lang = 'iw';
	var url = 'http://translate.google.com/#en|'+lang+'|'+encodeURI(message);
	// $('#googleTranslate').attr('src',url);

	var windowName = "popUp";//$(this).attr("name");

	window.open(url, windowName, "width=800,height=400");

	// event.preventDefault();

	// $.ajax({
		// url: 'http://translate.google.com/#en|'+lang+'|'+encodeURI(message),
		// context: document.body,
		// success: function(response){
			// $('#googleTranslate').html(response);
		// }
	// });

	// $('#contentPane').animate({height:400});
	// $('#iframeBox').animate({height:250});

}

function toggleGoogle() {
	if($('#iframeBox').height() == 0) {
		$('#contentPane').animate({height:400});
		$('#iframeBox').animate({height:250});
	} else {
		$('#contentPane').animate({height:650});
		$('#iframeBox').animate({height:0});
	}
}

var entryHeader = '	<tr class="entry">\n';
var entryFooter = '\
		<td class="controls" width="100">\
			<a href="#" class="add-entry" title="Add Entry">[+]</a>\
			<a href="#" class="add-comment" title="Add Comment">[/*]</a>\
			<a href="#" class="remove" title="Delete Line">[&ndash;]</a>\
		</td>\
	</tr>';

function addComment(object) {
	var commentField = '		<td colspan="3"><input name="data[template][]" type="text" class="comment"></td>'
	$(object).closest('.entry').after(entryHeader + commentField + entryFooter);
}

function addLine(object) {

	var langPacks = [<?php
	foreach(array_keys($messages) as $langPack)
		if($langPack != 'template') echo '"'.$langPack.'",';
	?>];

	var newEntry = '\
		<td width="30" class="controls">\
			<a href="#" onclick="showAll(this); return false;" title="Show all languages">All</a>\
		</td>\
		<td width="45%">\
			<div class="source">\
				<input name="data[template][]" type="text">\
			</div>\
		</td>\
		<td>';

	for(var i=0; i<langPacks.length; i++) {
		newEntry += '\
			<div class="language '+langPacks[i]+'">'+langPacks[i]+'</div>\
			<div class="translation '+langPacks[i]+'">\
				<input name="data['+langPacks[i]+'][]" type="text">\
			</div>\
		';
	}
	$(object).closest('.entry').after(entryHeader + newEntry + entryFooter);
}

function removeLine(object) {
	if(confirm('Are you sure you want to delete this entry from all language packs?')) {
		$(object).closest('.entry').remove();
		return true;
	} else
		return false
}
</script>
</head>
<body>

<div class="side-bar">
	<!-- File Menu - select which language files to edit -->
	<table class="rounded" style="width:100%;">
		<tr>
			<th width="75%">File</th>
			<th>Lines</th>
		</tr>
		<?php
		for($i=1; $i<count($messages['template']); $i++) {
			$fileName = $messages['template'][$i];
			$lines = count(file($messagePath.'/template/'.$fileName));

			$active = ($fileName == $targetFile);
			?>
			<tr<?php if($active) echo ' class="active"'; ?>>
				<td><?php echo $active ? CHtml::encode ($fileName) : '<a href="translationManager?file='.CHtml::encode ($fileName).'">'.CHtml::encode ($fileName).'</a>'; ?></td>
				<td><?php echo $lines; ?></td>
			</tr>
			<?php
		}


	?>
	</table>
	<?php
	if(!empty($targetFile)) {
	?>
	<!-- Language Menu - select which language to show, and find out which languages are incomplete -->
	<table class="rounded" id="languageMenu" style="width:100%;">
		<tr>
			<th width="50%">Language</th>
			<th>Status</th>
		</tr>
		<tr class="active">
			<td colspan="2">
				<a href="#" onclick="showLang('all',this); return false;">Show All</a> |
				<a href="#" onclick="showLang('none',this); return false;">Hide All</a>
			</td>
		</tr>
		<?php
		$langPackFiles = array();
		$langPackFiles['template'] = include($messagePath.'/template/'.$targetFile);

		foreach($messages as $langPack => $messageFiles) {
			if($langPack == 'template')
				continue;

			$fileName = $messagePath.'/'.$langPack.'/'.$targetFile;


			$missing = !file_exists($fileName);
			$incomplete = false;

			$langPackFiles[$langPack] = $missing? array() : include($fileName);
			foreach($langPackFiles['template'] as $key => $value) {
				if(!array_key_exists($key,$langPackFiles[$langPack]) || $langPackFiles[$langPack][$key] == '')
					$incomplete = true;
			}

			?>
			<tr>
				<td><a href="#" onclick="showLang('<?php echo $langPack; ?>',this); return false;"><?php echo $messageFiles[0]; ?></a></td>
				<td><?php
					if($missing)
						echo '<span class="not-ok">File Missing</span>';
					else if($incomplete)
						echo '<span class="not-ok">Incomplete</span>';
					else
						echo '<span class="ok">OK</span>'; ?>
				</td>
			</tr>
			<?php
		}
	?>
	</table>
</div>
<!-- Main window - view, edit and translations -->
<div class="content-container">
<div class="content">
<form method="POST" action="<?php echo Yii::app()->controller->createAbsoluteUrl('/admin/translationManager',array('file'=>$targetFile)); ?>" id="translationForm">
<?php
echo X2Html::csrfToken ();
?>
<input type="hidden" name="file" value="<?php echo CHtml::encode ($_GET['file']); ?>">
<table class="rounded" style="table-layout:fixed;">
	<tr>
		<th width="50%" height="15" style="height:16px;">Message</th>
		<th width="50%" height="15" style="height:16px;">Translation</th>
	</tr>
	<tr>
		<td colspan="3" id="contentPane" style="padding:0;height:650px;">
			<div class="scroll-box">
			<table class="translation-list">
			<?php
			$dataStarted = false;
			$currentTemplate = file($messagePath.'/template/'.$targetFile);
			foreach($currentTemplate as $line) {

				if(!$dataStarted) {
					if(preg_match('/^\s*return array/',$line))
						$dataStarted = true;
					else
						continue;
				}

				$matches = array();

				if(preg_match("/'(.+)'=>'(.*)',/u",$line,$matches)) { ?>
				<tr class="entry">
					<td width="30" class="controls"><a href="#" onclick="showAll(this); return false;" title="Show all languages">All</a></td>
					<td width="42%">
						<div class="source"><input name="data[template][]" type="text" value="<?php echo encodeQuotes(stripslashes($matches[1])); ?>"></div>
					</td>
					<td><?php
					foreach($messages as $langPack => $messageFiles) {
						if($langPack == 'template')
							continue;

						$translation = isset($langPackFiles[$langPack][stripslashes($matches[1])])? $langPackFiles[$langPack][stripslashes($matches[1])] : '';
						?>
						<div class="language <?php echo $langPack; ?>"><?php echo $langPack; ?></div>
						<div class="translation <?php echo $langPack; if(empty($translation)) echo ' empty'; ?>">
							<input name="data[<?php echo $langPack; ?>][]" type="text" value="<?php echo encodeQuotes(stripslashes($translation)); ?>">
						</div>
					<?php } ?>
					</td>
					<td class="controls" width="100">
						<a href="#" class="add-entry" title="Add Entry">[+]</a>
						<a href="#" class="add-comment" title="Add Comment">[/*]</a>
						<a href="#" class="remove" title="Delete Line">[&ndash;]</a>
					</td>
				</tr>
				<?php } else if(preg_match('/^\s*\/\/\s*(.*)$/u',$line,$matches) || preg_match('/^(\s*)$/',$line,$matches)) { ?>
				<tr class="entry">
					<td colspan="3"><input name="data[template][]" type="text" class="comment" value="<?php echo encodeQuotes($matches[1]); ?>"></td>
					<td class="controls" width="100">
						<a href="#" class="add-entry" title="Add Entry">[+]</a>
						<a href="#" class="add-comment" title="Add Comment">[/*]</a>
						<a href="#" class="remove" title="Delete Line">[&ndash;]</a>
					</td>
				</tr>
				<?php
				}
			}
			?>
			</table>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="3" height="30" style="padding:0 0 0 5px;">
			<input type="submit" class="x2-button" value="Save">
			<input type="button" class="x2-button" value="Reset" onclick="window.location.reload();">
			<!--<input type="button" class="x2-button" style="float:right;" value="Translator" onclick="toggleGoogle();">-->
		</td>
	</tr>
	<tr>
		<td colspan="3" id="translationPane"><div id="iframeBox"><!--<iframe id="googleTranslate" scrolling="no" name="googleTranslate" src="http://translate.google.com/"></iframe>--></div></td>
	</tr>
</table>
<?php 
echo X2Html::csrfToken (); 
$this->renderPartial('//layouts/footer');

}

//$langCodeUrl = 'http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes';
?>

</div>
</div>

</body>
</html>
