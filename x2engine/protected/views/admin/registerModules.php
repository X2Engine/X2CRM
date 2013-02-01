<?php
 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="page-title"><h2><?php echo Yii::t('admin','Registered Modules'); ?></h2></div>
<?php 
echo "<table>";
foreach($registeredModules as $module){
    echo "<tr>";
    echo $module->visible?"<td style='width:20%'><a href='toggleModule?module=$module->name' class='x2-button'>Deactivate Module</a></td>":"<td style='width:20%'><a href='toggleModule?module=$module->name' class='x2-button'>Activate Module</a></td>";
    echo "<td><b>".($module->title)."</b></td>";
    echo $module->visible?"<td style='color:green'>Active</td>":"<td style='color:red'>Inactive</td>";
    echo "</tr>";
}
echo "</table>";
?>
<br />
<div class="page-title"><h2><?php echo Yii::t('admin','Available Modules'); ?></h2></div>
<?php 
echo "<table>";
foreach($modules as $module){
    echo "<tr>";
    echo "<td style='width:20%'><a href='toggleModule?module=$module' class='x2-button'>Activate Module</a></td>";
    echo "<td><b>".ucfirst($module)."</b></td>";
    echo "<td style='color:red'>Inactive</td>";
    echo "</tr>";
}
echo "</table>";
?>