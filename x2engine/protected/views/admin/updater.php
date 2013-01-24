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

function downloadFile(i,altSource) {
	if (fileCount == n_files) { // No files left to download
		if(n_files > 0) {
			$('#update-text').text('Download complete.');
			alert('Download complete.');
		}
		if (scenario == 'update') {
			// Update in progress. Clear files deleted between versions.
			deleteFile(0);
		} else {
			// Upgrade in progress. Skip ahead to applying database changes.
			copyInstall();
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
				cleanUp('error');
			}
		}).fail(function(){
			cleanUp('error');
		});
	}
}

function deleteFile(k){
	if (deletionCount == n_deletions) { // No files left to delete
		$('#update-text').text('Deletions complete.');
		alert('Deletions complete.');
		copyInstall();
	} else { // Delete next file in queue at index k
		var currentFile = deletionList[k];
		$('#update-text').text("Deleting file {0}/{1}: {2}".format((deletionCount+1).toString(),n_deletions.toString(),currentFile));
		$.ajax({
			url: "delete",
			type: "POST",
			data: {'delete':currentFile},
			context: document.body
		}).done(function(){
			deletionCount++;
			var width=deletionCount/n_deletions*100;
			width=Math.round(width);
			$('#progress').css({'width':width+'%'});
			$('#progress-text').text(width+"%");
			deleteFile(k+1);
		}).fail(function(){
			cleanUp('error');
		});
	}
}
	
function copyInstall() {
	$('#update-text').text('Applying database changes...');
	$.ajax({
		url: "installUpdate",
		type: "POST",
		data: {'sqlList':sqlList},
		dataType: 'json',
		context: document.body
	}).done(function(data) {
		if(!data.error) {
			var scenarioTitle = scenario.charAt(0).toUpperCase() + scenario.slice(1)
			$('#update-text').text(scenarioTitle+' complete.');
			
			alert(scenarioTitle+" Complete.");
			if(scenario == 'update') {
				cleanUp('success');			
			} else {
				finishUpgrade('success');
			}
		} else {
			$('#progress-errors').html(data.message).show();
			cleanUp('error');
		}
	}).fail(function() {
		cleanUp('error');
	});
}
	
function cleanUp(status){
	$.ajax({
		url: scenario=='update'?"cleanUp":"saveEdition",
		context: document.body,
		type: "POST",
		data: scenario=='update'?{
			'status':status, 
			'version':'<?php echo $newVersion; ?>', 
			'updater':'<?php echo $updaterCheck; ?>',
			'dataType':'json',
			'fileList':"<?php echo addslashes(CJSON::encode($filesToDownload)); ?>",
			'url':'<?php echo $url; ?>'
		}:{
			'status':status, 
			'edition':edition, 
			'unique_id':unique_id,
			'fileList':"<?php echo addslashes(CJSON::encode($filesToDownload)); ?>"
		}
	}).done(function(response){
		if(status != 'error') {
			alert(response.message);
			window.location.reload();
		}
	});
}

function submitExternalForm() {
	var statusMsg = $('#error-box').find('h3').text('Retrieving upgrade data...');
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
				$('#error-box').append(r.errors);
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

function finishUpgrade(status) {
	$.ajax({
		url: "saveEdition",
		context: document.body,
		type: "POST",
		data: {
			'status':status, 
			'edition':edition,
			'unique_id':unique_id,
			'dataType':'json',
			'fileList':"<?php echo addslashes(CJSON::encode($filesToDownload)); ?>"
		}
	}).done(function(response){
		if (status != 'error') {
			alert(response.message);
			window.location.href = '<?php echo CHtml::normalizeUrl(array('site/page','view'=>'about')); ?>';
		}
	});
}

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
    downloadFile(0,scenario=='upgrade');
    $('#update-status').show();
});",CClientScript::POS_READY);
?>
<div class="span-20">
<div class="form">
<h2><?php echo in_array($scenario,array('message','error')) ? $message : "X2CRM ".ucfirst($scenario); ?></h2>
<?php if ($scenario != 'error') {
	if ($scenario == 'update') {
		echo "Number of files to download: <b>$n_files</b><br />";
		echo "Number of database changes: <b>" . ($sqlList[0] != "" ? count($sqlList) : "0") . "</b><br /><br />";
		echo "Current updater version: <b>$updaterCheck</b><br />";
		echo "Your updater version: <b>$updaterVersion</b><br /><br />";
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
}
?>


<?php if (!in_array($scenario, array('message','error'))): ?>
<div id="updates-control"<?php echo $scenario == 'upgrade'?' style="display:none"':'';?>>
<a href="#" class="x2-button" id="update-button"><?php echo ucfirst($scenario); ?></a><br />(note: you will need to disable pop-up blocking on this page before continuing)<br /><br />
<div id="update-status" style="">
<div id="progress-bar" style="width:300px;height:30px;border-style:solid;border-width:2px;">
    <div id="progress"><div id="progress-text" style="height:30px;width:300px;text-align:center;font-weight:bold;font-size:15px;">0%</div></div>
</div><br />
<div id="update-text" style="">Click "<?php echo ucfirst($scenario); ?>" to begin the <?php echo $scenario; ?>.</div>
</div>
</div>
<div id="progress-errors" class="form" style="display:none; color:red"></div>
<?php else: ?>
<?php 
if (isset($longMessage)) echo "<p>$longMessage</p>";
echo CHtml::link(Yii::t('admin','Go back'),array('admin/index')); ?>
<?php endif;?>
</div>
</div>