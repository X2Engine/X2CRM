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




$that = $this; 
$echoTabRow = function ($tabs, $rowNum=1) use ($that) {
    ?><ul id='<?php echo 'publisher-tabs-row-'.$rowNum; ?>' 
       style='display: none;'>
            <?php 
            // Publisher tabs
            foreach ($tabs as $tab) {
                ?> <li> <?php
                $tab->renderTitle ();
                ?> </li> <?php
            }
            ?>
        </ul><?php    
};

?>

<div id="<?php echo 'publisher'; ?>" 
 <?php echo (sizeof ($tabs) > 4 ? 'class="multi-row-tabs-publisher"' : ''); ?>>
    <?php
    $tabsTmp = $tabs;
    if (sizeof ($tabs) > 4) {
        $rowNum = 0;
        while (sizeof ($tabsTmp)) {
            $tabRow = array_slice ($tabsTmp, 0, 3);
            $echoTabRow ($tabRow, ++$rowNum);
            $tabsTmp = array_slice ($tabsTmp, 3);
        }
    } else {
        $echoTabRow ($tabsTmp);
    }
    ?>
    <div class='clearfix sortable-widget-handle'></div>
    <div class="form2 x2-layout-island">
    <?php
    // Publisher tab content 
    foreach ($tabs as $tab) {
        $tab->renderTab (array (
            'model' => $model,
            'associationType' => $associationType,
            'email' => $email,
        ));
    }

    $checkInPlaceholder = Yii::t('app', 'Check-in comment.');
    if (!isset($_SERVER['HTTPS']))
        $checkInPlaceholder .= Yii::t('app', ' Note: for higher accuracy and an embedded static map, visit the site under HTTPS.');
    ?><div style="margin-top:-45px; clear: both;">
        <button id="toggle-location-button" class="x2-button" title="<?php echo Yii::t('app', 'Location Check-In'); ?>" style="display:inline-block;"><?php
            echo X2Html::fa('crosshairs fa-lg');
        ?></button>
        <button id="toggle-location-comment-button" class="x2-button" title="<?php echo Yii::t('app', 'Add a comment on your location '); ?>" style="display:inline-block"><?php
            echo X2Html::fa('stack', array(),
                X2Html::fa('comment-o fa-stack-2x').
                X2Html::fa('crosshairs fa-stack-1x')
            );
        ?></button>
        <textarea id="checkInComment" style="display: none; height: 32px" placeholder="<?php echo $checkInPlaceholder; ?>"></textarea>
    </div>
    </div>
</div>

