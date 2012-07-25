<header>
<script>
var count=1;
var fileList=<?php echo json_encode($fileList);?>;
var fileCount=0;
var sqlList=<?php echo json_encode($sqlList);?>;
var j=0;
var k=0;
var sqlCount=1;
var deleteCount=1;
var deleteList=<?php echo json_encode($deletionList);?>;
function downloadFile(fileList, i){
    $.ajax({
          url: "download",
          type: "GET",
          data: {'url':'<?php echo $url;?>','file':fileList[i]},
          context: document.body,
          success: function(){
              if(count==fileList.length){
                  $('#update-text').html('Download complete.');
                  alert('Download complete.');
                  deleteFile(deleteList, k);
              }else{
                  count++;
                  var width=count/fileList.length*100;
                  width=Math.round(width);
                  $('#progress').css({'width':width+'%'});
                  $('#progress-text').html(width+"%");
                  $('#update-text').html('Downloading file: '+fileList[count-1]);
                  downloadFile(fileList, i+1);
              }
              
          },
          error: function(){
              cleanUp('error');
          }
        });
}

function deleteFile(deleteList, k){
    $.ajax({
          url: "delete",
          type: "POST",
          data: {'delete':deleteList[k]},
          context: document.body,
          success: function(){
              if(deleteCount==deleteList.length){
                  $('#update-text').html('Deletions complete.');
                  alert('Deletions complete.');
                  copyInstall(sqlList);
              }else{
                  deleteCount++;
                  var width=deleteCount/deleteList.length*100;
                  width=Math.round(width);
                  $('#progress').css({'width':width+'%'});
                  $('#progress-text').html(width+"%");
                  $('#update-text').html('Deleting file: '+deleteList[deleteCount-1]);
                  deleteFile(deleteList, k+1);
              }
              
          },
          error: function(){
              cleanUp('error');
          }
        });
}

function copyInstall(sqlList){
    $.ajax({
          url: "installUpdate",
          type: "POST",
          data: {'sqlList':sqlList},
          context: document.body,
          success: function(){
              $('#update-text').html('Update complete.');
              alert("Install Complete.");
              cleanUp('success');
          },
          error: function(){
              cleanUp('error');
              
          }
        });
}

function cleanUp(status){
    $.ajax({
          url: "cleanUp",
          context: document.body,
          type: "POST",
          data: {'status':status, 'version':'<?php echo $newVersion; ?>', 'fileList':fileList, 'url':'<?php echo $url;?>'},
          success: function(response){
              alert(response);
              window.location.reload();
          }
        });
}
</script>
<style>
    #progress{
        background:-webkit-gradient(linear, left top, left bottom, from(#729C00), to(#579100));
	background:-moz-linear-gradient(top,  #729C00,  #579100);
        width:0px;
        height:30px;
    }
</style>
</header>
<?php
Yii::app()->clientScript->registerScript("updater","$('#update-button').click(function(){
    downloadFile(fileList, fileCount);
    $('#update-status').show();
});",CClientScript::POS_READY);
?>

<h2>X2CRM Automatic Update</h2>
<?php
echo "Number of files to download: <b>".($fileList[0]!=""?count($fileList):"0")."</b><br />";
echo "Number of changes to database schema: <b>".($sqlList[0]!=""?count($sqlList):"0")."</b><br /><br />";
echo "Your updater version: <b>".$updaterVersion."</b><br />";
echo "Current updater version: <b>".$updaterCheck."</b><br /><br />";
echo "Your X2CRM version: <b>".$version."</b><br />";
echo "Current X2CRM version: <b>".$versionTest."</b><br /><br />";
echo $changelog;
?>
<a href="#" class="x2-button" id="update-button">Update</a><br /><br />
<div id="update-status" style="">
<div id="progress-bar" style="width:300px;height:30px;border-style:solid;border-width:2px;">
    <div id="progress"><div id="progress-text" style="height:30px;width:300px;text-align:center;font-weight:bold;font-size:15px;">0%</div></div>
</div><br />
<div id="update-text" style="">Click "Update" to begin the update.</div>
</div>

