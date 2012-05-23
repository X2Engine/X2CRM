<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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

