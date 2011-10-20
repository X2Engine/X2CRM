<?php

include('protected/config/emailConfig.php');

$contents=file_get_contents("http://x2base.com/updates/update.php?version=$version");
$pieces=explode(";",$contents);
$newVersion=$pieces[2];
$sqlList=$pieces[1];
$pieces=explode(":",$pieces[0]);
saveBackup($pieces);
foreach($pieces as $file){
    if($file!=""){
        if(!copy('http://x2base.com/updates/x2engine/'.$file , $file)){
            echo "Failed to copy file $file";
            restoreBackup($pieces);
            cleanUp();
            echo "Update failed";
            exit;
        }else{
            echo "Successfully copied $file<br />";     
        }
    }else{
        echo "No new files!<br />";
    }
}
$message="";
$con=mysql_connect($host,$user,$pass);
mysql_select_db($dbname);
$sqlList=explode(":",$sqlList);
foreach($sqlList as $sql){
    if($sql!=""){
        mysql_query($sql) or $message='SQL Failure';
    }else{
        echo "No MySQL changes!<br />";
    }
    if($message=='SQL Failure'){
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
echo "<br />X2Contacts is now up to date with version: $newVersion!";
echo "<br /><a href='index.php'>Click here to return to the application!</a>";
cleanUp();

function saveBackup($fileList){
    if(!is_dir('backup'))
        mkdir('backup');
    foreach($fileList as $file){
        if($file!=""){
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
