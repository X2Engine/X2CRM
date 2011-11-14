<?php

include('protected/config/emailConfig.php');

$context = stream_context_create(array(
    'http' => array(
        'timeout' => 15		// Timeout in seconds
    )
));

if($versionTest = @file_get_contents('http://x2planet.com/updates/versionCheck.php',0,$context)){
    $url='x2planet';
}
else if($versionTest = @file_get_contents('http://x2base.com/updates/versionCheck.php',0,$context)){
    $url='x2base';
}
else {
    echo "Unable to connect.";
    echo "<br /><a href='index.php'>Click here to return to the application!</a>";
    exit;
}

$contents=file_get_contents("http://www.$url.com/updates/update.php?version=$version");
$pieces=explode(";",$contents);
$newVersion=$pieces[2];
$sqlList=$pieces[1];
$changelog=$pieces[3];
$pieces=explode(":",$pieces[0]);
saveBackup($pieces);
$fileCount=0;
foreach($pieces as $file){
    if($file!=""){
        if(!copy("http://www.$url.com/updates/x2engine/".$file , $file)){
            echo "Failed to copy file $file<br />";
            restoreBackup($pieces);
            cleanUp();
            echo "Update failed";
            exit;
        }else{
            $fileCount++;     
        }
    }else{
        echo "No new files!<br />";
    }
}
$message="";
$con=mysql_connect($host,$user,$pass);
mysql_select_db($dbname);
$sqlList=explode(":",$sqlList);
$sqlCount=0;
foreach($sqlList as $sql){
    if($sql!=""){
        mysql_query($sql) or $message='SQL Failure';
        $sqlCount++;
    }
    if($message=='SQL Failure'){
        echo "Failed SQL query: $sql<br />";
        restoreBackup($pieces);
        cleanUp();
        echo "Update failed";
        exit;
    }
}
$config="<?php
\$host='$host';
\$user='$user';
\$pass='$pass';
\$dbname='$dbname';
\$version='$newVersion';
?>";
file_put_contents('protected/config/emailConfig.php', $config);
echo "$fileCount file(s) have been changed.";
echo "<br />There have been $sqlCount changes to the database schema.";
echo "<br />X2Contacts is now up to date with version: $newVersion!";
$pieces=explode(":",$changelog);
foreach($pieces as $piece){
    echo "<br />".$piece;
}
echo "<br /><a href='index.php'>Click here to return to the application!</a>";
cleanUp();

function saveBackup($fileList){
    if(!is_dir('backup'))
        mkdir('backup');
    foreach($fileList as $file){
        if($file!="" && file_exists($file)){
            makeDirectories($file);
            copy($file,'backup/'.$file);
        }
    }
}

function makeDirectories($file){
    if($file!=""){
            $pieces=explode('/',$file);
            unset($pieces[count($pieces)]);
            for($i=0;$i<count($pieces);$i++){
                $str="";
                for($j=0;$j<$i;$j++){
                    $str.='/'.$pieces[$j];
                }
                if(!is_dir('backup'.$str)){
                    mkdir('backup'.$str);
                }
            }
        }
}

function restoreBackup($fileList){
    foreach($fileList as $file){
        if($file!=""){
            copy('backup/'.$file,$file);
        }
    }
    
    
}

function cleanUp(){
    if(is_dir('backup'))
        rrmdir('backup');
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
}


?>
