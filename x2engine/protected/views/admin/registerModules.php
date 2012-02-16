<?php
 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h2>Available Modules</h2>
<?php 
echo "<table>";
foreach($modules as $key=>$module){
    $flag=array_search($key,$menuOrder)!==false;
    echo "<tr>";
    echo $flag?"<td style='width:20%'><a href='toggleModule?module=$key' class='x2-button'>Deactivate Module</a></td>":"<td style='width:20%'><a href='toggleModule?module=$key' class='x2-button'>Activate Module</a></td>";
    echo "<td><b>".ucfirst($module)."</b></td>";
    echo $flag?"<td style='color:green'>Active</td>":"<td style='color:red'>Inactive</td>";
    echo "</tr>";
}
echo "</table>";
?>