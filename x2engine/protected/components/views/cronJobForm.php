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
$enableName = $name.'['.$tag.'][enabled]';
?>
<div id="<?php echo $tag ?>-cron-job" class="cron-job">
    <input value="1" <?php echo $enabled ? 'checked="checked"' : ''; ?> type="checkbox" name="<?php echo $enableName ?>" class="cron-enabled" id="cron-job-<?php echo $tag; ?>" />&nbsp;
    <label for="<?php echo $enableName; ?>" class="<?php echo $labelClass; ?>"><?php echo $title; ?></label>
    <p><?php echo $longdesc; ?></p>
    <div id="cron-job-<?php echo $tag; ?>-form" class="cron-job-form">
        <p><?php echo $instructions; ?></p>
        <?php echo CrontabUtil::schedForm($initialCron, $name, $userCmd ? $cmd : "echo>/dev/null ", $tag, $initialCron['desc']); ?>
    </div>

</div>