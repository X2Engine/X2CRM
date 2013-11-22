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

if ($this->showActions !== null) {
    $this->beginWidget('zii.widgets.CPortlet', array(
        'title'=>Yii::t('actions', 'Show Actions'),
        'id'=>'actions-filter',
    ));
    echo '<div class="form no-border" style="text-align:center; height:25px;">';
    echo CHtml::dropDownList('show-actions', $this->showActions,
        array(
            'uncomplete'=>Yii::t('actions', 'Incomplete'),
            'complete'=>Yii::t('actions', 'Complete'),
            'all'=>Yii::t('actions', 'All'),
        ),
        array(
            'id'=>'dropdown-show-actions',
            'onChange'=>'toggleShowActions();',
        )
    );

    echo '</div>';
    $this->endWidget();
}
