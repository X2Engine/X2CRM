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

if (!auxlib) var auxlib = {};
auxlib.DEBUG = true;

auxlib.error = function (message) {
    if (auxlib.DEBUG && x2.DEBUG) console.log ('Error: ' + message);
};

// display error message in red after prevElem
auxlib.createErrorFeedbackBox = function (argsDict) {
    var prevElem = argsDict['prevElem']; // required
    var message = argsDict['message']; // required
    var classes = argsDict['classes'];
    classes = typeof classes === 'undefined' ? [] : classes;

    var feedbackBox = $('<div>', {'class': 'auxlib-error-msg-container'}).append (
        $("<span>", { 
            'class': "auxlib-error-box-msg",
            'text': message
        })
    );
    for (var i in classes) {
        $(feedbackBox).addClass (classes[i]);
    }
    $(prevElem).after (feedbackBox);
}

// delete error message created with createErrorFeedbackBox
auxlib.destroyErrorFeedbackBox = function (prevElem) {
    $(prevElem).next ('.auxlib-error-msg-container').remove ();
}

/*
Creates a feedback box div containing the specified message. The feedback box is
placed in the dom after the specified previous element. The box is faded out and,
after a specified delay, is removed.
Parameters:
    argsDict - a dictionary containing arguments
        prevElement (required) - the element after which the box will get placed
        message (required) - a string
        delay - the time after which the box will get removed
        disableButton - a button which will be disabled until the feedback box fades out
        classes - an array. css classes which will be added to the feedback box
*/
auxlib.createReqFeedbackBox = function (argsDict) {
    var prevElem = argsDict['prevElem']; // required
    var message = argsDict['message']; // required
    var delay = argsDict['delay'];
    var classes = argsDict['classes'];
    var disableButton = argsDict['disableButton'];
    classes = typeof classes === 'undefined' ? [] : classes;
    delay = typeof delay === 'undefined' ? 2000 : delay;
    disableButton = typeof disableButton === 'undefined' ? prevElem : disableButton;

    if ((disableButton).attr ('disabled')) return;
    $(disableButton).attr ('disabled', 'disabled');

    var feedbackBox = $('<div>', {'class': 'feedback-container'}).append (
        $("<span>", { 
            'class': "feedback-msg",
            'text': message
        })
    );
    for (var i in classes) {
        $(feedbackBox).addClass (classes[i]);
    }
    $(prevElem).after (feedbackBox);
    auxlib._startFeedbackBoxFadeOut (feedbackBox, delay, prevElem, disableButton);
    return feedbackBox;
}


/*
Private function.
Removes a feedback box created by createReqFeedbackBox () after a specified delay.
Specified button will be disabled until delay elapses.
Parameters:
    feedbackBox - a jQuery element created by createReqFeedbackBox ()
    delay - in milliseconds
*/
auxlib._startFeedbackBoxFadeOut = function (feedbackBox, delay, button, disableButton) {
    $(feedbackBox).children ().fadeOut (delay, function () {
        $(feedbackBox).remove ();
        $(disableButton).removeAttr ('disabled');
    });
}


/*
Returns true if parent element has an error box, false otherwise.
*/
auxlib.errorBoxExists = function (parentElem) {
    return ($(parentElem).find ('.error-summary-container').length > 0);
};



/*
Removes an error div created by createErrorBox ().  
Parameters:
	parentElem - a jQuery element which contains the error div
*/
auxlib.destroyErrorBox = function (parentElem) {
	var $errorBox = $(parentElem).find ('.error-summary-container');
	if ($errorBox.length !== 0) {
		$errorBox.remove ();
	}
}

/*
Returns a jQuery element corresponding to an error box. The error box will
contain the specified errorHeader and a bulleted list of the specified error
messages.
Parameters:
	errorHeader - a string
	errorMessages - an array of strings
*/
auxlib.createErrorBox = function (errorHeader, errorMessages) {
	var errorBox = $('<div>', {'class': 'error-summary-container'}).append (
		$("<div>", { 'class': "error-summary"}).append (
			$("<p>", { text: errorHeader }),
			$("<ul>")
	));
	for (var i in errorMessages) {
		var msg = errorMessages[i];
		$(errorBox).find ('.error-summary').
			find ('ul').append ($("<li> " + msg + " </li>"));
	}
	return errorBox;
}




/*
Select an option from a select element
Parameters:
	selector - a jquery selector for the select element
	setting - the value of the option to be selected
*/
auxlib.selectOptionFromSelector = function (selector, setting) {
	$(selector).children (':selected').removeAttr ('selected');
	$(selector).children ('[value="' + setting + '"]').attr ('selected', 'selected');
}


/*
Set object properties. Default property values are used where an expected property value 
is not defined.
*/
auxlib.applyArgs = function (obj, defaultArgs, args) {
	for (var i in defaultArgs) {
		if (args[i] === undefined) {
			obj[i] = defaultArgs[i];
		} else {
			obj[i] = args[i];
		}
	}
}

auxlib.makeDialogClosableWithOutsideClick = function (dialogElem) {
    $("body").on ('click', function (evt) {
        if ($(dialogElem).closest (".ui-dialog").length &&
            $(dialogElem).is (':visible') &&
            $(dialogElem).dialog ("isOpen") &&
            !$(evt.target).is ("a") &&
            !$(evt.target).closest ('.ui-dialog').length) {
            $(dialogElem).dialog ("close");
        }
    });
};


// convert css value in pixels to an int
auxlib.pxToInt = function (str) {
    return parseInt (auxlib.rStripPx (str), 10);
}

// remove trailing 'px'
auxlib.rStripPx = function (str) {
    return str.replace (/px$/, '');
}

/*
Used to replace Object.keys which is not available in ie8
*/
auxlib.keys = function (obj) {
    if (typeof obj !== 'object') return false;
    var keys = []; 
    for (var key in obj) {
        keys.push (key);
    }
    return keys;
};

/*
Used to replace Object.create which is not available in ie8
*/
auxlib.create = function (prototype) {
    function dummyFn () {};
    dummyFn.prototype = prototype;
    return new dummyFn ();
};


/*
Remove cursor from input by focusing on a temporary dummy input element.
*/
auxlib.removeCursorFromInput = function (elem) {
    $(elem).append ($("<input>", {"id": "auxlib-dummy-input"}));
    var x = window.scrollX;
    var y = window.scrollY;
    $("#auxlib-dummy-input").focus ();
    window.scrollTo (x, y); // prevent scroll from focus event
    $("#auxlib-dummy-input").remove ();
};

/*
Returns a JSON string with form field names as keys and corresponding form field values as values
*/
auxlib.formToJSON = function (formElem) {
    var arr = $(formElem).serializeArray ();
    var JSONarr = {};
    var name, value;
    for (var i in arr) {
        name = arr[i]['name'];
        value = arr[i]['value'];
        if (name) JSONarr[name] = value;
    }
    return JSONarr;
};

auxlib.htmlEncode = function (text) {
    return $('<div>', { 'text': text }).html ();
};

auxlib.htmlDecode = function (html) {
    return $('<div>', { 'html': html }).text ();
};

/*
Saves settings as a property of the miscLayoutSettings JSON field of the profile model. This 
should be used to make miscellaneous layout settings persistent.
Parameters:
    settingName - string - must be an existing property name of the JSON field
    settingVal - mixed - the value to which the JSON field property will get set
*/
auxlib.saveMiscLayoutSetting = function (settingName, settingVal) {
    $.ajax ({
        url: auxlib.saveMiscLayoutSettingUrl,
        type: 'POST',
        data: {
            settingName: settingName,
            settingVal: settingVal,
        },
        success: function () {
        }
    });
};
