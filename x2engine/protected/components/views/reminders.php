<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
$tidBIT = date("l, F j, Y");
echo "<center><h4 id='day'>Today:</h4><h5>".$tidBIT."</h5>";
echo "<ul id='actionLS'>";
if ($today==NULL){echo "<li>Nothing is Due Today.</li>";}
else {
    foreach ($today as $tD){
        $time = date("g a",$tD['dueDate']);
        $name = $tD['associationName'];
        $id = $tD['id'];
        if ($name!=NULL){
            echo "<span class='lITEM' id='".$id."'><li>".$name."</li></span>";
       }else {
           $string = $tD['actionDescription'];
           $subSTR = substr($string,0,25);
           echo "<span class='lITEM' id='".$id."'><li>".$subSTR."</li></span>";
       }
       echo "<div class='shown' id='X".$id."'>
           <p class='descrip'>Description:</p><p>".$tD['actionDescription']."</p>";
       if (strcmp($time, "12 am") != 0){
           $tiempo = date("g:i a",$time);
           echo "<p class='descrip'>Time:</p><p>".$tiempo."</p>";
       }
       echo "</div>";
   }
}
echo "</ul></center><hr></hr>";
$tidBIT = date("l, F j, Y",mktime(0,0,0,date("m"),date("d")+1,date("y")));
echo "<center><h4 id='day'>Tomorrow:</h4><h5>".$tidBIT."</h5>";
echo "<ul id='actionLS'>";
if ($tomorrow == NULL){echo "<li>Nothing is Due Tomorow.</li>";}
else {
    foreach ($tomorrow as $tM){
        $name = $tM['associationName'];
        $id = $tM['id'];
        if ($name!=NULL){
          echo "<span class='lITEM' id='".$id."'><li>".$name."</li></span>";
        }else {
            $string = $tM['actionDescription'];
            $subSTR = substr($string,0,25);
            echo "<span class='lITEM' id='".$id."'><li>".$subSTR."</li></span>";
        }
        echo "<div class='shown' id='X".$id."'>
            <p class='descrip'>Description:</p><p>".$tM['actionDescription']."</p>
            <p class='descrip'>Time:</p><p>
           </div>";
   }
}
echo "</ul></center><hr></hr>";
$tidBIT = date("l, F j, Y",mktime(0,0,0,date("m"),date("d")+2,date("y")));
echo "<center><h4 id='day'>Next Day</h4><h5>".$tidBIT."</h5>";
echo "<ul id='actionLS'>";
if ($nextDay == NULL){echo "<li>Nothing is Due the Next Day.</li>";}
else {
    foreach($nextDay as $nD){
        $name = $nD['associationName'];
        $id = $nD['id'];
        if($name!=NULL){
            echo "<span class='lITEM' id='".$id."'><li>".$name."</li></span>";
        }else {
             $string = $nD['actionDescription'];
             $subSTR = substr($string,0,25);
             echo "<span class='lITEM' id '".$id."'><li>".$subSTR."</li></span>";
        }
        echo "<div class='shown' id='X".$id."'>
            <p><span class='descrip'>Description:</span><br/>".$nD['actionDescription']."</p>
            </div>";
   }
}
echo "</ul></center>";
Yii::app()->clientScript->registerScript('showDIVS', "
    $(document).ready(function(){
        $('div[id*=X]').hide();
        $('.lITEM').click(function(){
            var id = $(this).attr('id');
            $('#X'+id).toggle();
        })
    });
",CClientScript::POS_HEAD);
?>

