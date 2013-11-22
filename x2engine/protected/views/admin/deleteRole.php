<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Delete A Role'); ?></h2></div>
<div class="form">
    <br /> <span style="color:red;"><?php echo Yii::t('admin','<b>WARNING:</b> this operation is not reversible, all users will have this role removed from them.');?></span>
    <form name="deleteRoles" action="deleteRole" method="POST">
        <br />
        <select name="role">
            <?php foreach($roles as $key => $value)
                echo "<option value='$key'>$value</option>"; ?>
        </select>
        <br /><br />
        <input class="x2-button" type="submit" value="<?php echo Yii::t('admin', 'Delete'); ?>" />
    </form>
</div>