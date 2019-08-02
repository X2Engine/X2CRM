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






/**
 * @param array $accessGroups access groups indexed by module name
 * @param array $titles module titles indexed by module name
 * @param object $auth auth manager object
 * @param string $name name of role being edited
 * @param bool $adminFlag true if role is flagged as admin, false otherwise
 */


Yii::app()->clientScript->registerScript('editRoleAccessPermissionsTableJS',"
$(function () {
    // checking an admin checkbox disables/checks all other checkboxes in the row 
    $(document).off ('change', '.admin-checkbox').on('change','.admin-checkbox',function(){ 
        var row = $(this).closest ('tr');
        var relatedSelects = $(row).find ('select');
        var relatedCheckboxes = $(row).find ('input:checkbox').not ($(this));
        if(this.checked) { 
            $(relatedCheckboxes).prop ('checked', true);
            $(relatedCheckboxes).prop ('disabled', true);
            $(relatedSelects).val ('all');
            $(relatedSelects).prop ('disabled', true);
        } else {
            $(relatedCheckboxes).prop ('disabled', false);
            $(relatedSelects).prop ('disabled', false);
        } 
    });
    $('.admin-checkbox').trigger ('change'); 

    // behavior of column header checkbox
    $(document).off ('change', '.master-checkbox').on('change','.master-checkbox',function(){ 
        var checked = this.checked;
        var isAdminCheckbox = $(this).attr ('id') === 'admin-master';
        x2.forms.forEachCellInColumn ($(this).closest ('th'), function () {
            var checkbox$ = $(this).find ('input:checkbox');
            if (!checkbox$.is (':disabled'))
                checkbox$.prop('checked',checked); 
            if (isAdminCheckbox) $('.admin-checkbox').trigger ('change');  
        });
    });

    // check header checkbox if all checkboxes in that column are checked
    (function () {
        var headerCheckboxes = $.makeArray (
            $('#permissions-table thead th'));
        var permissionsCells = $.makeArray (
            $('#permissions-table tbody td'));
        var columns = $('#permissions-table tbody tr').first ().find ('td').
            length;
        var rows = $('#permissions-table tbody tr').length;
        for (var i = 1; i < headerCheckboxes.length; i++) {
            var checked = true;
            for (var j = 0; j < rows; j++) {
                var cell = $(permissionsCells[i + columns * j]);
                if ($(cell).find ('input').length) 
                    checked &= $(cell).find ('input').is (':checked');
            }
            $(headerCheckboxes[i]).find ('input')[0].checked = checked;
        }
    }) ();

    $('#admin-flag').off ('click').on ('click', function () { 
        var value = $('#admin-flag').attr('checked');
        if (value === 'checked') {
            $('#permissions-table').hide();
        } else {
            $('#permissions-table').show();
        }
    });

});
", CClientScript::POS_END);

?>
<div class="row" id="admin-flag-box" style="display:none;">
    <br>
    <?php 
    echo CHtml::label(Yii::t('admin', 'Role is Admin?'), 'adminFlag'); 
    echo CHtml::checkBox('adminFlag', $adminFlag, array(
        'id' => 'admin-flag',
    ));
    ?>
</div>
<br>

<table id='permissions-table' <?php echo $adminFlag ? "style='display: none;'" : ''; ?>>
<thead><tr>
    <th><?php echo Yii::t('admin', 'Module'); ?></th>
    <th style='width:150px;'><?php echo 
    Yii::t('admin', 'View') . " " . 
    CHtml::checkBox(
        '', 
        false, 
        array(
            'class' => 'master-checkbox',
            'id' => 'view-master'
        ));
    echo X2Html::hint2 (
      Yii::t('app', 'Permission required to view records in the given module.'), array ( 
        'class' => 'table-hint',
      ));
    ?>
</th>
<th><?php echo 
    Yii::t('admin', 'Create') . " " . 
    CHtml::checkBox(
        '',
        false,
        array(
            'class' => 'master-checkbox',
            'id' => 'create-master'
        ));
    echo X2Html::hint2 (
      Yii::t('app', 'Permission required to create records in the given module.'), 
      array ( 
        'class' => 'table-hint',
      ));
    ?>
</th>
<th style='width:150px;'><?php echo 
    Yii::t('admin', 'Update') . " " . 
    CHtml::checkBox(
        '',
        false,
        array(
            'class' => 'master-checkbox',
            'id' => 'update-master'
        ));
    echo X2Html::hint2 (
      Yii::t('app', 'Permission required to update records in the given module.'), 
      array ( 
        'class' => 'table-hint',
      ));
    ?>
</th>
<th style='width:150px;'><?php echo 
    Yii::t('admin', 'Delete') . " " . 
    CHtml::checkBox(
        '',
        false,
        array(
            'class' => 'master-checkbox',
            'id' => 'delete-master')
        );
    echo X2Html::hint2 (
      Yii::t('app', 'Permission required to delete records in the given module.'), 
      array ( 
        'class' => 'table-hint',
      ));
    ?>
</th>
<th><?php echo 
    Yii::t('admin', 'Admin') . " " . 
    CHtml::checkBox(
        '',
        false,
        array(
            'class' => 'master-checkbox',
            'id' => 'admin-master'
        ));
    echo X2Html::hint2 (
      Yii::t('app', 'Permission required for admin-level activity in the given module. Implies ' .
        'all other permissions.'), 
      array ( 
        'class' => 'table-hint',
      ));
    ?>
</th>
</tr></thead>
<?php
// create rows of permissions table. permissions checkboxes and privacy select elements based on
// presence of corresponding auth items.
$i = 1;
$ret = '';
foreach ($accessGroups as $module => $accessGroup) {
    $ret.="<tr class='" . ($i % 2 === 0 ? "even-row" : "odd-row") . "'>";
    $ret.="<td>" . Yii::t('app', $titles[$module]) . "</td>";
    $permissionNames = array_flip (RoleAccessActionBase::$permissionNames);

    foreach (RoleAccessActionBase::$permissionNames as $permissionName => $keyword) {
        if (in_array ($keyword . 'Access', $accessGroup)) {
            $authItem = $auth->getAuthItem (ucfirst ($module).$keyword.'Access');
            $index = array_search($keyword .'Access', $accessGroup);
            $privateIndex = false;
            if (in_array("Private{$keyword}Access", $accessGroup)) {
                $privateIndex = array_search("Private{$keyword}Access", $accessGroup);
            }
            $ret.="<td>" . CHtml::checkBox(
                $module . "-" . $permissionName, 
                ($auth->hasItemChild($name, $index) || 
                    $auth->hasItemChild($name, $privateIndex)), 
                array(
                    'style' => 'position:relative;top:2px;',
                    'class' => $module . "-checkbox $permissionName-checkbox"
                ));

            if (in_array ($keyword, array ('ReadOnly', 'Update', 'Full')) && $privateIndex) {
                $ret.="&nbsp;" . CHtml::dropDownList(
                    $module . "-$permissionName-privacy", 
                    $auth->hasItemChild($name, $privateIndex) ? 
                        'private' : 'all', 
                    array(
                        'all' => Yii::t('admin',"All Records"),
                        'private' => Yii::t('admin',"Only Assigned"),
                    ),
                    array(
                        'style' => 'margin-left:10px;',
                        'class' => $module . '-dropdown'
                    ));
            }
            if (!empty ($authItem->description)) {
                $ret .= X2Html::hint2 ($authItem->description, array (
                    'class' => 'table-hint'
                ));
            }
            $ret.="</td>";
        } else {
            $ret.="<td>&nbsp;</td>";
        }
    }
    $ret.="</tr>";
    $i++;
}
$ret.="</table>";
echo $ret;
?>
