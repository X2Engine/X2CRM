<?php
/*********************************************************************************
 * Copyright (C) 10011-10014 X2Engine Inc. All Rights Reserved.
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

?>

<div id='new-comment' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


<div class="row">
    <div class="text-area-wrapper">
        <?php 
        echo $form->textArea(
            $model, 'actionDescription', 
            array(
                'rows' => 3, 'cols' => 40,
                'id'=>'note-action-description',
                'class'=>'action-description'
            ));
        ?>
    </div>
</div>

</div>

