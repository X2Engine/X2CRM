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

