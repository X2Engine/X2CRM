<?php
$admin = Yii::app()->settings;
require_once 'protected/extensions/google-api-php-client/src/Google_Client.php';
require_once 'protected/extensions/google-api-php-client/src/contrib/Google_DriveService.php';
;

$authUrl = $auth->getAuthorizationUrl(null);

//Request authorization

if(isset($_GET['code'])){
    $authCode = $_GET['code'];
    // Exchange authorization code for access token
//    $accessToken = $client->authenticate($authCode);
//    $_SESSION['access_token'] = $client->getAccessToken();
//    $client->setAccessToken($accessToken);
}
if(isset($_SESSION['access_token'])){
//  $client->setAccessToken($_SESSION['access_token']);
}
?>
<div class="page-title"><h2>Google Drive Upload</h2></div>
<?php if(!is_null($createdFile)){ ?>
    <div class="form">
        <b>File created successfully!<b>
    </div>
<?php } ?>
<div class="form">
    <?php
    if(!$auth->getErrors() && $auth->getAccessToken()){
        echo CHtml::form('testDrive', 'post', array('enctype' => 'multipart/form-data', 'id' => 'file-form'));
        echo CHtml::fileField('upload');
        echo CHtml::submitButton('Upload');
        echo CHtml::endForm();
    }else{
        echo CHtml::link('Authenticate With Drive', $authUrl, array('class' => 'x2-button'));
    }
    ?>
</div>
<div class="drive-table">
    <?php echo $baseFolder; ?>
</div>

<script>
    $(document).on('click','.toggle-file-system',function(e){
        e.preventDefault();
        var id=$(this).attr('data-id');
        if($('#'+id).is(':hidden')){
            $.ajax({
                'url':'<?php echo Yii::app()->controller->createUrl('/media/media/recursiveDriveFiles') ?>',
                'data':{'folderId':id},
                'success':function(data){
                    $('#'+id).html(data);
                    $('#'+id).show();
                    console.log("TEST");
                    
                }
            });
        }else{
            $('#'+id).html('').hide();
        }
    });

</script>
<style>
    .drive-link{
        text-decoration:none;
        color:#222;
    }
    .drive-item{
        vertical-align:middle;
    }
    .drive-wrapper{
        height:20px;
        border-bottom-style:solid;
        border-width:1px;
        border-color:#ccc;
        padding:5px;
        margin-left:-500px;
        padding-left:520px;
        vertical-align:middle;
    }
    .drive {
        padding-left:20px;
    }
    .drive-table{

    }
</style>
