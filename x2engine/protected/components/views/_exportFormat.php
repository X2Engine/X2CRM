<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::app()->clientScript->registerCss ('exportFormatCss', '
    .compressOutput label { display: inline !important; }

    
'); ?>

<div class="compressOutput exportOption">
<?php
    echo CHtml::label(Yii::t('admin', 'Compress Output?'), 'compressOutput');
    echo CHtml::checkbox('compressOutput', false);
?>
</div>

<br />

<?php
    
Yii::app()->clientScript->registerScript ('exportFormatControls', '
    if (typeof x2 === "undefined") x2 = {};
    if (typeof x2.exportFormats === "undefined") x2.exportFormats = {};

    

    // Build a parameter string of the format controls for the selected type
    x2.exportFormats.readExportFormatOptions = function() {
        var type = $("#targetType").children (":checked").val();
        var compressOutput = $("#compressOutput").is(":checked");
        var params = $("#" + type + ".targetForm").serialize();
        var destination = "exportDestination=" + type;
        var compress = "compressOutput=" + compressOutput;
        return [params, destination, compress].join("&");
    };
', CClientScript::POS_READY);
