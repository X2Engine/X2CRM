<?php 
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/
?>


<?php if(in_array($scenario, array('update','upgrade'))): ?>
<script>
String.prototype.format = function() {
	var formatted = this;
	for (var i = 0; i < arguments.length; i++) {
		var regexp = new RegExp('\\{'+i+'\\}', 'gi');
		formatted = formatted.replace(regexp, arguments[i]);
	}
	return formatted;
};
var scenario="<?php echo $scenario; ?>";
var unique_id = '<?php echo $scenario == 'update' ? $unique_id : ''; ?>';
var edition = '<?php echo isset($edition) ? $edition : Yii::app()->params->admin->edition; ?>';
var n_users = 0; // Used in the upgrade process

var fileList0=<?php echo $scenario == 'update' ? CJSON::encode($fileList) : '[]' ; ?>; 
<?php 
$altSrcFiles = '[]';
$filesToDownload = array();
if ($scenario == 'update' && isset($nonFreeFileList)) {
	$altSrcFiles = CJSON::encode($nonFreeFileList);
	$filesToDownload = array_merge($fileList,$nonFreeFileList);
} else if($scenario == 'update') {
	$filesToDownload = $fileList;
}
$n_files = count($filesToDownload);

?>
var fileList1=<?php echo $scenario=='update'?$altSrcFiles:"[]"; ?>;
var n_files = fileList0.length + fileList1.length;
var fileCount=0;

var deletionList=<?php echo $scenario=='update'?CJSON::encode($deletionList):"[]"; ?>;
var n_deletions = deletionList.length;
var deletionCount=0;

var sqlList=<?php echo $scenario == 'update' ? CJSON::encode($sqlList) : "[]"; ?>;
var n_sql = sqlList.length;
var sqlCount=0;
var version = '<?php echo $scenario == 'update'?$newVersion:Yii::app()->params->version; ?>';
var buildDate = <?php echo $scenario == isset($buildDate)?$buildDate:Yii::app()->params->buildDate; ?>;

//var $('#progress-errors') = $('#progress-errors'); 

if (jQuery == undefined) {
	alert('The jQuery JavaScript library is required for the updater to work, and it is missing.');
}

function makeBackup() {
	var proceed = true;
	var inProgress = $('#something-inprogress').show().css({'display':'inline-block'});
	$.ajax({
		url:'backup',
		type:'GET',
		dataType:'json'
	}).done(function(data){
		alert(data.message);
		$('#backup-state-error').hide();
		$('#backup-download-link').show();
	}).fail(function(jqXHR,textStatus,errorMessage) {
		if(jqXHR.status != 0)
			alert('Backup failed: '+textStatus+' '+jqXHR.status+' '+errorMessage);
	}).always(function() {
		inProgress.hide();
	});
}

function downloadFile(i,altSource) {
	if (fileCount == n_files) { // No files left to download
		var proceed = true;
		if(n_files > 0) {
			$('#update-text').text('Download complete.');
			proceed = confirm('All files downloaded. Proceed with {0}?'.format(scenario));
		} else { // Case where there are no new files, only deletions and/or SQL changes
			proceed = confirm('Proceed with {1}?'.format(scenario));
		}
		if(proceed) {
			enactChanges();
		}
	} else { // Download next file in queue at index i
		if (!altSource)
			if (fileList0[i] == undefined)
				altSource = true; // No non-free files to download, so skip this list
		var currentFile = altSource ? fileList1[i] : fileList0[i];
		$('#update-text').text('Downloading file {0}/{1}: {2}'.format((fileCount+1).toString(),n_files.toString(),currentFile));
		$.ajax({
			url: "download",
			type: "GET",
			dataType: 'json',
			data: {
				// The server from which to download
				url:'<?php echo $url; ?>',
				// The file to download
				file:currentFile,
				// The route to use for accessing the file on the server
				route:(altSource ? 'installs/update/{0}/{1}'.format(edition,unique_id) : 'updates/x2engine')
			},
			context: document.body
		}).done(function(data) {
			if(!data.error) {
				fileCount++; // One more file successfully downloaded
				var width=fileCount/n_files*100;
				width=Math.round(width);
				$('#progress').css({'width':width+'%'});
				$('#progress-text').text(width+"%");
				if (fileCount==fileList0.length && fileList1.length > 0) {
					// Begin alternate source downloads
					downloadFile(0,true);
				} else {
					// Continue downloading as before
					downloadFile(i+1,altSource);
				}
			} else {
				$('#progress-errors').html(data.message).show();
			}
		}).fail(function(jqXHR,textStatus,errorMessage) {
			if(jqXHR.status != 0)
				alert('Error: server failed to respond to request to download file '+currentFile+'; '+textStatus+' '+jqXHR.errorCode+' '+errorMessage);
		});
	}
}

function enactChanges() {
	$('#update-text').text('Applying database and file changes...');
	$('#progress-bar').hide();
	var inProgress = $('#update-status').prepend($('#something-inprogress').clone().removeAttr('id')).find('img').show();
	var scenarioTitle = scenario.charAt(0).toUpperCase() + scenario.slice(1);
	
	$.ajax({
		url: "enactChanges?scenario={0}{1}".format(scenario,($('#auto-restore').is(':checked')?'&autoRestore=1':'')),
		type: "POST",
		data: {
			'scenario':scenario,
			'sqlList':sqlList,
			'deletionList':(scenario=='update'?deletionList:[]),
			'version':version,
			'buildDate':(scenario=='update'?buildDate:0),
			'edition':edition,
			'unique_id':unique_id
		},
		dataType: 'json',
		context: document.body
	}).done(function(data) {
		if(!data.error) {
			$('#update-text').text(scenarioTitle+' complete.');
			exitUpdater(data);
		} else {
			inProgress.hide();
			$('#progress-errors').html(data.message).show();
		}
	}).fail(function(jqXHR,textStatus,errorMessage) {
		if (jqXHR.status != 0) {
			inProgress.hide();
			$('#progress-errors').text('{0} could not be completed because the request to the server failed or timed out.'.format(scenarioTitle)).show();
			alert('{0} failed due to unsuccessful web request.'.format(scenarioTitle));
		}
	});
}

// This function used by the upgrade/pro registration form
function submitExternalForm() {
	var errorBox = $('#error-box');
	var statusMsg = errorBox.find('h3').text('Retrieving upgrade data...');
	$.ajax({
		url:'getNUsers',
		type:'GET'
	}).done(function(response){
		var n_users = Number(response);
		// Update global variables:
		unique_id = $('#unique_id').val();
		edition = $('#edition').val();
		$.ajax({
			url:'http://x2planet.com/installs/upgrades/{0}/{1}_{2}'.format(unique_id,"<?php echo $edition; ?>",n_users),
			type:'GET',
			dataType:'json'
		}).done(function(r){
			// Display data & "upgrade" form:
			if (r.errors != undefined) {
				statusMsg.html("Could not retrieve upgrade data.");
				errorBox.append(r.errors);
			} else {
				sqlList = r.sqlUpgrade;
				fileList1 = r.fileUpgrade;
				n_files = fileList1.length;
				n_sql = sqlList.length;
				$('#registration-form').fadeOut();
				$('#upgrade-step').text("Ready to begin the upgrade!");
				$('#upgrade-data').html("Number of files to download: <b>{0}</b><br />Number of database changes: <b>{1}</b><br />".format(n_files,n_sql)).show();
				$('#updates-control').show();
			}
		}).fail(function(r){
			statusMsg.html("<?php echo Yii::t('install','Could not connect to the updates server at this time.');?>");
		});
	});
}

function exitUpdater(response) {
	if(response != undefined)
		alert(response.message);
	if(scenario == 'upgrade') // Go to about page
		window.location.href = '<?php echo CHtml::normalizeUrl(array('site/page','view'=>'about')); ?>';
	else // Reload to show we're at the latest verion
		window.location.reload();
}

$(function() {
		$('#auto-restore').change(function() {
			if($(this).is(':checked'))
				$('#autorestore-disclaimer').fadeIn(300);
			else
				$('#autorestore-disclaimer').fadeOut(300);
		})
});
//()(functio);

</script>
<?php endif; ?>
<style>
	#progress{
		background:-webkit-gradient(linear, left top, left bottom, from(#729C00), to(#579100));
		background:-moz-linear-gradient(top,  #729C00,  #579100);
		width:0px;
		height:30px;
	}
</style>

<?php
Yii::app()->clientScript->registerScript("updater","$('#update-button').click(function(){
	$('#progress-bar').fadeIn(300);
	$('#update-status').show();
    downloadFile(0,scenario=='upgrade');
});",CClientScript::POS_READY);
?>
<div class="span-20">
<div class="form">
<h2><?php echo in_array($scenario,array('message','error')) ? $message : "X2CRM ".ucfirst($scenario); ?></h2>
<?php if($scenario != 'error'): ?>
<hr />
<?php if (in_array($scenario,array('update','upgrade'))): ?>
<h3><?php echo Yii::t('admin','Before Proceeding'); ?></h3>
<?php echo Yii::t('admin','The following precautions are highly recommended:') ?><br />
<ul style="margin-top:10px;">
	<li><?php echo Yii::t('admin',"Make a backup copy of X2CRM's database:")?>
		<ul>
			<li><?php echo Yii::t('admin','using third-party web hosting tools, or:'); ?></li> 
			<li><?php echo Yii::t('admin','by clicking the button below.'); ?></li>
		</ul>
	</li>
	<li><?php echo Yii::t('admin',"Disable pop-up blocking on this page.");?></li>
	<?php if($scenario == 'update') echo '<li>'.Yii::t('admin','Notify all users that an update will be occurring; everyone (including you) will be logged out when the update has completed.').'</li>'; ?>
</ul>

<a href="#" onclick="makeBackup()" class="x2-button" id="backup-button"><?php echo Yii::t('admin','Backup Database'); ?></a>
<img id="something-inprogress" style="height:25px;width:25px;vertical-align:middle;display:none" src="<?php echo Yii::app()->theme->BaseUrl.'/images/loading.gif'; ?>" /><br />
<label for="auto-restore" style="display:inline-block;margin-right:10px"><?php echo Yii::t('admin','Automatically restore from backup if update fails'); ?></label>
<input type="checkbox" name="auto-restore" id="auto-restore" style="display:inline-block;padding:0;margin:0;vertical-align: middle" />
<?php
$msg = '';
try {
	$this->checkDatabaseBackup();
} catch (Exception $e) {
	if($e->getCode() == 1) {
		$msg = Yii::t('admin','Note: no database backup was found.');
	} else if ($e->getCode() == 2) {
		$msg = Yii::t('admin','Note: a database backup was found, but it is over 24 hours old.');
	} else {
		throw $e;
	}
}

?>
<span id="backup-state">
	<span id="backup-state-error" style="color:red;"><?php echo $msg; ?></span>
	<span id="backup-download-link" style="<?php echo empty($msg)?'':'display:none;' ?>"><?php echo CHtml::link('[ '.Yii::t('admin','Download database backup').' ]',array('admin/downloadDatabaseBackup')); ?></span>
</span>
<div style="display:none;margin-top:10px;" class="form" id="autorestore-disclaimer">
	<h4><?php echo Yii::t('admin','Disclaimer'); ?></h4>
	<?php
	$disclaimer = array();
	$disclaimer[] = 'Restoring a database may take longer than the maximum PHP execution time permitted in some server environments, or even longer than the request timeout value in the configuration of your web browser.';
	$disclaimer[] = 'This is especially likely to occur if you have a large X2CRM installation with hundreds of thousands of records.';
	$disclaimer[] = 'If a database restore operation is cut short, the consequences could be severe.';
	$disclaimer[] = 'Please check your web server configuration and test making a backup of the database first.';
	$disclaimer[] = 'If database backups do not succeed, consider disabling this option.';
	?>
	<?php echo Yii::t('admin',implode(' ',$disclaimer)); ?>
</div>
<br /><br /><hr />
<?php endif; ?>
<?php 
	if ($scenario == 'update') {
		echo '<h3>'.Yii::t('admin',"Update Details").'</h3>';
		echo "Number of files to download: <b>$n_files</b><br />";
		echo empty($deletionList)?'':Yii::t('admin','Number of obsolete files to be deleted:').' <b>'.count($deletionList)."</b><br />";
		echo "Number of database changes: <b>" . (!empty($sqlList)?($sqlList[0] != "" ? count($sqlList) : "0"):'0') . "</b><br />";
		echo Yii::t('admin',"Updater utility version check:").'<strong>&nbsp;'
				.( $updaterCheck == $updaterVersion 
				? Yii::t('admin','pass')
				: '<span style="color: red">'.Yii::t('admin','Something went wrong; the updater utility is at version {uver}, but to enact the changes requested requires it to be at {uchk}',array('{uver}'=>$updaterVersion,'{uchk}'=>$updaterCheck))).'</strong><br />';
		echo "Current X2CRM version: <b>$newVersion</b><br />";
	}
	echo "Your X2CRM version: <b>$version</b><br /><br />";
	if (isset($changelog)) echo $changelog;
	if ($scenario == 'upgrade') echo '<p id="upgrade-step">To upgrade, begin by filling out the following form with your registration details. To obtain a license key: see <a href="http://www.x2engine.com/pricing-plans/" target="_blank">pricing plans</a>.</p>';
	if ($scenario=='upgrade') {
		// Upgrade registration form
		Yii::app()->clientScript->registerScriptfile(Yii::app()->baseUrl.'/js/webtoolkit.sha256.js');
		$form = $this->beginWidget('CActiveForm', array(
			'id' => 'registration-form',
			'enableAjaxValidation' => false,
				));
		$updatesForm = new UpdatesForm(
						array(
							'x2_version' => Yii::app()->params['version'],
							'unique_id' => '',
							'formId' => 'registration-form',
							'submitButtonId' => 'submit-button',
							'statusId' => 'error-box',
							'themeUrl' => Yii::app()->theme->baseUrl,
							'serverInfo' => True,
							'edition' => $edition,
							'titleWrap' => array('<span style="display: block;font-size: 11px;font-weight: bold;">', '</span>'),
							'receiveUpdates' => 1,
							'isUpgrade' => True
						),
						'Yii::t',
						array('install')
		);
		$this->renderPartial('stayUpdated', array('form' => $updatesForm));
		echo CHtml::submitButton(Yii::t('app', 'Register'), array('class' => 'x2-button', 'id' => 'submit-button')) . "\n";
		echo '<div id="error-box" class="form" style="display:none"></div>';
		$this->endWidget();
		echo '<div id="upgrade-data" style="display:none;"></div>';
	}
endif;
?>

<?php if (!in_array($scenario, array('message','error'))): ?>
<div id="updates-control"<?php echo $scenario == 'upgrade'?' style="display:none"':'';?>>
<a href="#" class="x2-button" id="update-button"><?php echo ucfirst($scenario); ?></a><br />
<div id="update-status">
<div id="progress-bar" style="display:none;width:300px;height:30px;border-style:solid;border-width:2px;">
    <div id="progress"><div id="progress-text" style="height:30px;width:300px;text-align:center;font-weight:bold;font-size:15px;">0%</div></div>
</div><br />
<div id="update-text">Click "<?php echo ucfirst($scenario); ?>" to begin the <?php echo $scenario; ?>.</div>
</div>
<div id="progress-errors" class="form" style="display:none; color:red"></div>
</div>
</div>
<?php else: ?>
<?php 
if (isset($longMessage)) echo "<p>$longMessage</p>";
echo CHtml::link(Yii::t('admin','Go back'),array('admin/index')); ?>
<?php endif;?>
</div>
</div>