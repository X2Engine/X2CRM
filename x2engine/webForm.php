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

/* x2prostart */
/* 
JS generates an iframe and inserts the visitor's tracking key cookie into the 
get parameters of the iframe source url. Generates a tracking key cookie for the visitor if
they don't have one already (duplicating some of the functionality of the web tracker).
*/
/* x2proend */

?>

(function () {

   <?php
   /* x2prostart */ 
   // lookup cookie or generate it if there isn't one
   ?>
    var cookieRegex = /(?:^|.*;)\s*x2_key\s*=\s*([^;]*)(?:.*$|$)/;
    if (!document.cookie.match (cookieRegex)) {
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var x2KeyCookie = '';
        for(var i = 0; i < 32; ++i) { 
            x2KeyCookie += chars.charAt (
                    parseInt (Math.floor (Math.random () * chars.length)));
        }
        <?php
        // set cookie, expire after 1 year
        ?>
        document.cookie = 'x2_key=' + x2KeyCookie + 
            ';expires=' + (new Date (+new Date () + 31556940000)).toGMTString ();
    } else { 
        var x2KeyCookie = document.cookie.replace (cookieRegex, '$1');
    }
    <?php
    // create iframe and insert cookie into get params
    /* x2proend */  
    ?>
    var x2WebFormIframe = document.createElement ('iframe');
    x2WebFormIframe.src = "<?php 
        echo $_GET['iframeUrl'] . '?'; 
        foreach ($_GET as $key=>$val) {
            if ($key !== 'iframeUrl') {
                echo '&' . $key . '=' . urlencode ($val);
            }
        }; ?>"
       <?php /* x2prostart */ ?>
            + '&x2_key=' + encodeURIComponent (x2KeyCookie)
       <?php /* x2proend */ ?>;
    x2WebFormIframe.setAttribute ('frameborder', '0');
    x2WebFormIframe.setAttribute ('scrolling', '0');
    x2WebFormIframe.setAttribute ('width', '<?php echo $_GET['iframeWidth'] ?>');
    x2WebFormIframe.setAttribute ('height', '<?php echo $_GET['iframeHeight'] ?>');
    document.write (x2WebFormIframe.outerHTML);
}) ();
