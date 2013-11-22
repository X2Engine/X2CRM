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



function removeCheckerImage (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', '');
}

function addCheckerImage (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', 'url("' + yii.baseUrl + '/themes/x2engine/images/checkers.gif")');
}

function setupSpectrum (element, replaceHash /* optional */) {
    replaceHash = typeof replaceHash === 'undefined' ? false : true;

    $(element).spectrum ({
        move: function (color) {
            $(element).data ('ignoreChange', true);
        },
        hide: function (color) {
            removeCheckerImage ($(element));
            $(element).data ('ignoreChange', false);

            if (replaceHash) {
                var text = color.toHexString ().replace (/#/, '');
            } else {
                var text = color.toHexString ();
            }

            $(element).val (text);
            $(element).change ();
        }
    });
    
    $(element).show ();
    if ($(element).val () === '') {
        addCheckerImage ($(element));
    }

    $(element).blur (function () {
        var color = $(this).val ();

        // make color picker color match input field without triggering change events
        if (color !== '') { 
            removeCheckerImage ($(this));

            if (replaceHash) {
                var text = '#' + color;
            } else {
                var text = color;
            }

            $(this).next ('div.sp-replacer').find ('.sp-preview-inner').css (
                'background', '#' + text);
        }
    });

    $(element).change (function () {
        var text = $(this).val ();
        if (text === '') {
            addCheckerImage ($(this));
        }
    });

}


