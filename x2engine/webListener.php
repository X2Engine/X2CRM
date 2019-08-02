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






/*
Legacy web tracker. New version: webTracker.php
*/

$url = '';
if(!empty($_SERVER['HTTP_REFERER'])) {
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    
    // get referring URL's GET params
    $referer_GET = array();
    if(isset($referer['query'])) {
        parse_str($referer['query'],$referer_GET);
    }

    // get referring URL
    $url = isset($referer['host'])? $referer['host'] : '';
    $url .= (isset($referer['path'])? $referer['path'] : '');
}

$entryScript = (defined ('FUNCTIONAL_TEST') && constant ('FUNCTIONAL_TEST') ? 
            'index-test.php' : 'index.php'); 
$protocol = !empty ($_SERVER['HTTPS']) ? 'https' : 'http';

// use the link key first, then look at cookies (so marketing campaigns override generic tracking)
if(isset($referer_GET['get_key']) && ctype_alnum($referer_GET['get_key'])) {
	Header('Location: '.$protocol.'://'.$_SERVER['HTTP_HOST'].'/'.$entryScript.
        '/api/webListener?x2_key='.$referer_GET['get_key'].'&url='.urlencode($url));
} elseif(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) {
	Header('Location: '.$protocol.'://'.$_SERVER['HTTP_HOST'].'/'.$entryScript.'/api/webListener?'.
        '&url='.urlencode($url));
} else {
	/* In this last effort to work properly, attempt to find the root parent window
	 * (i.e. in the case that the script is being called within two or more levels
	 * of nested iframes). It's still not guaranteed to work, if any of the iframes
	 * are on different domains.
	 */
?><html>
<head></head>
<body>
<script type="text/javascript" >
var thiswindow = window, i = 0;
while(thiswindow != top && i < 10) {
	thiswindow = thiswindow.parent;
	i++;
}
try {
    var getparam = /(https?:\/\/[\.\w\/\?]+)[\?&]x2_key=(\w+)/.exec(thiswindow.location.href);
} catch (e) {
    var getparam = null;
}
if(getparam != null) {
	var xmlhttp;
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open(
        "GET",'index.php/api/webListener?url='+
        encodeURIComponent(getparam[1])+'&x2_key='+encodeURIComponent(getparam[2]),true);
	xmlhttp.send();
}
</script>
</body>
</html>
<?php 
}
?>
