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






var x2Identity = {};

/**
* JS Implementation of MurmurHash3 (r136) (as of May 20, 2011)
*
* @author <a href="mailto:gary.court@gmail.com">Gary Court</a>
* @see http://github.com/garycourt/murmurhash-js
* @author <a href="mailto:aappleby@gmail.com">Austin Appleby</a>
* @see http://sites.google.com/site/murmurhash/
*
* @param {string} key ASCII only
* @param {number} seed Positive integer only
* @return {number} 32-bit positive integer hash
*/
x2Identity.murmurhash3 = function(key, seed) {
    var remainder, bytes, h1, h1b, c1, c1b, c2, c2b, k1, i;

    remainder = key.length & 3; // key.length % 4
    bytes = key.length - remainder;
    h1 = seed;
    c1 = 0xcc9e2d51;
    c2 = 0x1b873593;
    i = 0;

    while (i < bytes) {
        k1 =
        ((key.charCodeAt(i) & 0xff)) |
        ((key.charCodeAt(++i) & 0xff) << 8) |
        ((key.charCodeAt(++i) & 0xff) << 16) |
        ((key.charCodeAt(++i) & 0xff) << 24);
        ++i;

        k1 = ((((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16))) & 0xffffffff;
        k1 = (k1 << 15) | (k1 >>> 17);
        k1 = ((((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16))) & 0xffffffff;

        h1 ^= k1;
        h1 = (h1 << 13) | (h1 >>> 19);
        h1b = ((((h1 & 0xffff) * 5) + ((((h1 >>> 16) * 5) & 0xffff) << 16))) & 0xffffffff;
        h1 = (((h1b & 0xffff) + 0x6b64) + ((((h1b >>> 16) + 0xe654) & 0xffff) << 16));
    }

    k1 = 0;

    switch (remainder) {
        case 3: k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
        case 2: k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
        case 1: k1 ^= (key.charCodeAt(i) & 0xff);

        k1 = (((k1 & 0xffff) * c1) + ((((k1 >>> 16) * c1) & 0xffff) << 16)) & 0xffffffff;
        k1 = (k1 << 15) | (k1 >>> 17);
        k1 = (((k1 & 0xffff) * c2) + ((((k1 >>> 16) * c2) & 0xffff) << 16)) & 0xffffffff;
        h1 ^= k1;
    }

    h1 ^= key.length;

    h1 ^= h1 >>> 16;
    h1 = (((h1 & 0xffff) * 0x85ebca6b) + ((((h1 >>> 16) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
    h1 ^= h1 >>> 13;
    h1 = ((((h1 & 0xffff) * 0xc2b2ae35) + ((((h1 >>> 16) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
    h1 ^= h1 >>> 16;

    return h1 >>> 0;
};

/**
 * Gather information about the clients browser and hash it to form the fingerprint.
 */
x2Identity.fingerprint = function (options) {
    var fingerprint = [],
        attributes = {},
        plugins = [],
        fonts = [],
        ua, res, req, headers, cookieEnabled;

    if (navigator.appName === "Microsoft Internet Explorer" ||
            (navigator.appName === 'Netscape' && /Trident/.test(navigator.userAgent))) {
        // Internet Explorer
        var testPlugins = ['ShockwaveFlash.ShockwaveFlash', 'AcroPDF.PDF', 'PDF.PdfCtrl', 'QuickTime.QuickTime', 'rmocx.RealPlayer G2 Control',
           'rmocx.RealPlayer G2 Control.1', 'RealPlayer.RealPlayer(tm) ActiveX Control (32-bit)', 'RealVideo.RealVideo(tm) ActiveX Control (32-bit)',
           'RealPlayer', 'SWCtl.SWCtl', 'WMPlayer.OCX', 'AgControl.AgControl', 'Skype.Detection'];
        for (var plugin in testPlugins) {
            var found = false;
            if (navigator.plugins[plugin])
                found = true;
            try {
                new ActiveXObject(plugin);
                found = true;
            } catch(e) {
                // Plugin does not exist
            }
            if (found)
                plugins.push(plugin);
        }
    } else {
        // Non-IE Browsers
        for (var i = 0; i < navigator.plugins.length; i++) {
            var plugin = navigator.plugins[i];
            var pluginString = plugin.name;
            if (plugin.description.length > 0)
                pluginString += " (" + plugin.description + ")";
            plugins.push( pluginString );
        };
    }
    if (plugins.length > 0) {
        var pluginString = plugins.join(',');
        fingerprint.push( pluginString );
        attributes['plugins'] = x2Identity.murmurhash3 (pluginString, 31);
    }

    ua = navigator.userAgent;
    if (typeof ua !== 'undefined') {
        fingerprint.push( ua );
        attributes['userAgent'] = ua;
    }

    fingerprint.push( navigator.language );
    attributes['language'] = navigator.language;

    if (typeof screen.height !== 'undefined' && screen.width !== 'undefined') {
        res = screen.height + 'x' + screen.width + 'x' + screen.colorDepth;
    }else if (typeof screen.availHeight !== 'undefined' && screen.availWidth !== 'undefined') {
        res = screen.availHeight + 'x' + screen.availWidth + 'x' + screen.colorDepth;
    }
    if (res !== 'undefined') {
        fingerprint.push( res );
        attributes['screenRes'] = res;
    }

    var tz = new Date().getTimezoneOffset()
    fingerprint.push( tz );
    attributes['timezone'] = tz;
    
    cookieEnabled = (navigator.cookieEnabled) ? 1 : 0;
    if (typeof navigator.cookieEnabled === 'undefined' && !cookieEnabled) {
        document.cookie = "testcookie";
        cookieEnabled = (document.cookie.indexOf('testcookie') != -1)? 1 : 0;
    }
    fingerprint.push( cookieEnabled );
    attributes['cookiesEnabled'] = cookieEnabled;

    try { // attempt to access indexedDB throws an error in ff selenium tests
        if (typeof window.indexedDB !== 'undefined') {
            fingerprint.push( true );
            attributes['indexedDB'] = 1;
        } else {
            attributes['indexedDB'] = 0;
        }
    } catch (e) {
        attributes['indexedDB'] = 1;
    }

    if (typeof document.body !== 'undefined' && typeof document.body.addBehavior !== 'undefined') {
        fingerprint.push( true );
        attributes['addBehavior'] = 1;
    } else
        attributes['addBehavior'] = 0;

    if (typeof navigator.javaEnabled() !== 'undefined') {
        fingerprint.push( true );
        attributes['javaEnabled'] = 1;
    } else
        attributes['javaEnabled'] = 0;

    // Build an HTML5 Canvas fingerprint
    var canvas = document.createElement('canvas');
    if (typeof canvas.getContext !== 'undefined') {
        // https://www.browserleaks.com/canvas#how-does-it-work
        var ctx = canvas.getContext('2d');
        var txt = "BrowserLeaks,com <canvas> 1.0";
        ctx.textBaseline = "top";
        ctx.font = "14px 'Arial'";
        ctx.textBaseline = "alphabetic";
        ctx.fillStyle = "#f60";
        ctx.fillRect(125,1,62,20);
        ctx.fillStyle = "#069";
        ctx.fillText(txt, 2, 15);
        ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
        ctx.fillText(txt, 4, 17);
        fingerprint.push( canvas.toDataURL() );
        attributes['canvasFingerprint'] = x2Identity.murmurhash3(canvas.toDataURL(), 31);
    }

    try { // Check for localStorage functionality
        if (typeof window.localStorage !== 'undefined') {
            fingerprint.push(true);
            attributes['localStorage'] = 1;
        } else
            attributes['localStorage'] = 0;
    } catch(e) {
        fingerprint.push(true);
        attributes['localStorage'] = 1;
    }

    try { // Check for sessionStorage functionality
        if (typeof window.sessionStorage !== 'undefined') {
            fingerprint.push(true);
            attributes['sessionStorage'] = 1;
        } else
            attributes['sessionStorage'] = 0;
    } catch(e) {
        fingerprint.push(true);
        attributes['sessionStorage'] = 1;
    }

    // Detect installed fonts
    var testFonts = x2Identity.fontlist();
    var d = new Detector();
    for (var i = 0; i < testFonts.length; i++) {
        if (d.detect(testFonts[i]))
            fonts.push(testFonts[i]);
    }
    if (fonts.length > 0) {
        fonts = fonts.join(',');
        fingerprint.push(fonts);
        attributes['fonts'] = x2Identity.murmurhash3(fonts, 31);
    }

    fingerprint = fingerprint.join('#');
    return {
        fingerprint: x2Identity.murmurhash3(fingerprint, 31), 
        attributes: /*JSON.stringify(*/attributes/*)*/
    };
};

x2Identity.fontlist = function() {
    // Many fonts from http://flippingtypical.com/about.html
    return new Array(
        "Academy Engraved LET",
        "ADOBE CASLON PRO",
        "Adobe Garamond",
        "ADOBE GARAMOND PRO",
        "AGENCY FB",
        "ALGERIAN",
        "American Typewriter",
        "American Typewriter Condensed",
        "Andale Mono",
        "Apple Chancery",
        "Apple Color Emoji",
        "Apple SD Gothic Neo",
        "ARCHER",
        "Arial",
        "ARIAL",
        "Arial Black",
        "Arial Hebrew",
        "Arial Narrow",
        "Arial Rounded MT Bold",
        "ARNO PRO",
        "AVENIR",
        "Ayuthaya",
        "Bandy",
        "Bangla Sangam MN",
        "Bank Gothic",
        "Baskerville",
        "BATANG",
        "Bauer Bodoni",
        "BAUHAUS 93",
        "BELL MT",
        "Bembo",
        "Big Caslon",
        "Bitstream Charter",
        "Bitstream Charter Bold",
        "Bitstream Charter Bold Italic",
        "Bitstream Charter Italic",
        "BLACKADDER ITC",
        "BlairMdITC TT",
        "Bodoni 72",
        "Bodoni 72 Oldstyle",
        "Bodoni 72 Smallcaps",
        "Book antiqua",
        "BOOKMAN OLD STYLE",
        "Bradley Hand",
        "BROADWAY",
        "CALIBRI",
        "Cambria",
        "CANDARA",
        "CASTELLAR",
        "Centaur",
        "Century",
        "Century gothic",
        "Century Schoolbook L Bold",
        "Century Schoolbook L Bold Italic",
        "Century Schoolbook L Italic",
        "Century Schoolbook L Roman",
        "Chalkboard",
        "Chalkboard SE",
        "Chalkduster",
        "CHILLER",
        "Clarendon",
        "cmex10",
        "cmmi10",
        "cmr10",
        "cmsy10",
        "Cochin",
        "Comic Sans",
        "COMIC SANS MS",
        "CONSOLAS",
        "CONSTANTIA",
        "Copperplate",
        "Copperplate Gothic",
        "Corbel",
        "COURIER",
        "Courier 10 Pitch",
        "Courier 10 Pitch Bold",
        "Courier 10 Pitch Bold Italic",
        "Courier 10 Pitch Italic",
        "Courier New",
        "CURLZ MT",
        "DB LCD Temp",
        "DejaVu Sans",
        "DejaVu Sans Bold",
        "DejaVu Sans Mono",
        "DejaVu Sans Mono Bold",
        "DejaVu Serif",
        "DejaVu Serif Bold",
        "DELICIOUS",
        "Devanagari Sangam MN",
        "Dialog.bold",
        "Dialog.bolditalic",
        "DialogInput.bold",
        "DialogInput.bolditalic",
        "DialogInput.italic",
        "DialogInput.plain",
        "Dialog.italic",
        "Dialog.plain",
        "Didot",
        "DIN",
        "Dingbats ",
        "Droid Sans",
        "Droid Sans Arabic",
        "Droid Sans Bold",
        "Droid Sans Fallback",
        "Droid Sans Hebrew",
        "Droid Sans Japanese",
        "Droid Sans Mono",
        "Droid Sans Thai",
        "Droid Serif",
        "Droid Serif Bold",
        "Droid Serif Bold Italic",
        "Droid Serif Italic",
        "EDWARDIAN SCRIPT ITC",
        "ELEPHANT",
        "esint10",
        "eufm10",
        "Euphemia UCAS",
        "EUROSTILE",
        "FONTIN",
        "FORTE",
        "Franklin Gothic",
        "Franklin Gothic Medium",
        "Free Monospaced",
        "Free Monospaced Bold",
        "Free Monospaced Bold Oblique",
        "Free Monospaced Oblique",
        "Free Sans",
        "Free Sans Bold",
        "Free Sans Bold Oblique",
        "Free Sans Oblique",
        "Free Serif",
        "Free Serif Bold",
        "Free Serif Bold Italic",
        "Free Serif Italic",
        "Fruitger",
        "FRUTIGER",
        "Futura",
        "GARAMOND",
        "gargi",
        "Garuda",
        "Garuda Bold",
        "Garuda Bold Oblique",
        "Garuda Oblique",
        "GAUTAMI",
        "Geeza Pro",
        "Geneva",
        "Georgia",
        "Gill Sans",
        "GILL SANS MT",
        "GOTHAM",
        "GOTHAM BOLD",
        "GOUDY OLD STYLE",
        "Gujarati Sangam MN",
        "Gurmukhi MN",
        "HARRINGTON",
        "Heiti SC",
        "Heiti TC",
        "HELV",
        "Helvetica",
        "Helvetica Neue",
        "Hiragino Kaku Gothic ProN",
        "Hiragino Mincho ProN",
        "Hoefler Text",
        "Impact",
        "INCONSOLATA",
        "INTERSTATE",
        "Jazz LET",
        "Jenson",
        "JOKERMAN",
        "KacstArt",
        "KacstBook",
        "KacstDecorative",
        "KacstDigital",
        "KacstFarsi",
        "KacstLetter",
        "KacstNaskh",
        "KacstOffice",
        "KacstOne",
        "KacstOne Bold",
        "KacstPen",
        "KacstPoster",
        "KacstQurn",
        "KacstScreen",
        "KacstTitle",
        "KacstTitleL",
        "Kailasa",
        "Kannada Sangam MN",
        "KARTIKA",
        "Kedage Bold",
        "Kedage Normal",
        "Khmer OS",
        "Khmer OS System",
        "Kinnari",
        "Kinnari Bold",
        "Kinnari Bold Italic",
        "Kinnari Bold Oblique",
        "Kinnari Italic",
        "Kinnari Oblique",
        "Krungthep",
        "LATHA",
        "Liberation Mono",
        "Liberation Mono Bold",
        "Liberation Mono Bold Italic",
        "Liberation Mono Italic",
        "Liberation Sans",
        "Liberation Sans Bold",
        "Liberation Sans Bold Italic",
        "Liberation Sans Italic",
        "Liberation Sans Narrow",
        "Liberation Sans Narrow Bold",
        "Liberation Sans Narrow Bold Italic",
        "Liberation Sans Narrow Italic",
        "Liberation Serif",
        "Liberation Serif Bold",
        "Liberation Serif Bold Italic",
        "Liberation Serif Italic",
        "Lohit Bengali",
        "Lohit Gujarati",
        "Lohit Hindi",
        "Lohit Punjabi",
        "Lohit Tamil",
        "Loma",
        "Loma Bold",
        "Loma Bold Oblique",
        "Loma Oblique",
        "Lucida Bright Demibold",
        "Lucida Bright Demibold Italic",
        "Lucida Bright Italic",
        "Lucida Bright Regular",
        "LUCIDA CONSOLE",
        "LUCIDA GRANDE",
        "LUCIDA SANS",
        "Lucida Sans Demibold",
        "Lucida Sans Regular",
        "Lucida Sans Typewriter Bold",
        "Lucida Sans Typewriter Regular",
        "MAGNETO",
        "Malayalam Sangam MN",
        "Mallige Bold",
        "Mallige Normal",
        "MANGAL",
        "Marion",
        "Marker Felt",
        "Meera",
        "Minion",
        "Minion Pro",
        "Monaco",
        "Mona Lisa Solid ITC TT",
        "MONO",
        "Monospaced.bold",
        "Monospaced.bolditalic",
        "Monospaced.italic",
        "Monospaced.plain",
        "MONOTYPE CORSIVA",
        "Mrs Eaves",
        "mry_KacstQurn",
        "msam10",
        "msbm10",
        "Mukti Narrow",
        "Mukti Narrow Bold",
        "MUSEO",
        "MYRIAD",
        "MYRIAD PRO",
        "Nadeem",
        "NanumGothic",
        "NanumGothic Bold",
        "NanumMyeongjo",
        "NanumMyeongjoBold",
        "NEVIS",
        "News Gothic",
        "Nimbus Mono L Bold",
        "Nimbus Mono L Bold Oblique",
        "Nimbus Mono L Regular",
        "Nimbus Mono L Regular Oblique",
        "Nimbus Roman No9 L Medium",
        "Nimbus Roman No9 L Medium Italic",
        "Nimbus Roman No9 L Regular",
        "Nimbus Roman No9 L Regular Italic",
        "Nimbus Sans L Bold",
        "Nimbus Sans L Bold Condensed",
        "Nimbus Sans L Bold Condensed Italic",
        "Nimbus Sans L Bold Italic",
        "Nimbus Sans L Regular",
        "Nimbus Sans L Regular Condensed",
        "Nimbus Sans L Regular Condensed Italic",
        "Nimbus Sans L Regular Italic",
        "Norasi",
        "Norasi Bold",
        "Norasi Bold Italic",
        "Norasi Bold Oblique",
        "Norasi Italic",
        "Norasi Oblique",
        "Noteworthy",
        "OpenSymbol",
        "OPTIMA",
        "Oriya Sangam MN",
        "OSAKA",
        "Palatino",
        "PALATINO LINOTYPE",
        "Papyrus",
        "Party LET",
        "PERPETUA",
        "PetitaBold",
        "Phetsarath OT",
        "PLAYBILL",
        "Pothana2000",
        "PRINCETOWN LET",
        "Purisa",
        "Purisa Bold",
        "Purisa Bold Oblique",
        "Purisa Oblique",
        "Rachana",
        "Rekha",
        "Rockwell",
        "rsfs10",
        "Saab",
        "SansSerif.bold",
        "SansSerif.bolditalic",
        "SansSerif.italic",
        "SansSerif.plain",
        "Santa Fe LET",
        "Savoye LET",
        "Sawasdee",
        "Sawasdee Bold",
        "Sawasdee Bold Oblique",
        "Sawasdee Oblique",
        "SCRIPT",
        "SCRIPTINA",
        "SEGOE UI",
        "Serifa",
        "Serif.bold",
        "Serif.bolditalic",
        "Serif.italic",
        "Serif.plain",
        "SILKSCREEN",
        "Sinhala Sangam MN",
        "Sketch Rockwell",
        "Skia",
        "Snell Roundhand",
        "Standard Symbols L",
        "STENCIL",
        "Styllo",
        "SYMBOL",
        "Synchro LET",
        "SYSTEM",
        "Tahoma",
        "TakaoPGothic",
        "Tamil Sangam MN",
        "Telugu Sangam MN",
        "Thonburi",
        "Times",
        "Times New Roman",
        "Tlwg Mono",
        "Tlwg Mono Bold",
        "Tlwg Mono Bold Oblique",
        "Tlwg Mono Oblique",
        "Tlwg Typewriter",
        "Tlwg Typewriter Bold",
        "Tlwg Typewriter Bold Oblique",
        "Tlwg Typewriter Mono Oblique",
        "Tlwg Typist",
        "Tlwg Typist Bold",
        "Tlwg Typist Bold Oblique",
        "Tlwg Typist Oblique",
        "Tlwg Typo",
        "Tlwg Typo Bold",
        "Tlwg Typo Bold Oblique",
        "Tlwg Typo Oblique",
        "Trajan",
        "TRAJAN PRO",
        "Trebuchet MS",
        "TUNGA",
        "Ubuntu",
        "Ubuntu Bold",
        "Ubuntu Bold Italic",
        "Ubuntu Condensed",
        "Ubuntu Italic",
        "Ubuntu Light",
        "Ubuntu Light Italic",
        "Ubuntu Mono",
        "Ubuntu Mono Bold",
        "Ubuntu Mono Bold Italic",
        "Ubuntu Mono Italic",
        "Umpush",
        "Umpush Bold",
        "Umpush Bold Oblique",
        "Umpush Light",
        "Umpush Light Oblique",
        "Umpush Oblique",
        "Univers",
        "Univers CE 55 Medium",
        "Untitled1",
        "URW Bookman L Demi Bold",
        "URW Bookman L Demi Bold Italic",
        "URW Bookman L Light",
        "URW Bookman L Light Italic",
        "URW Chancery L Medium Italic",
        "URW Gothic L Book",
        "URW Gothic L Book Oblique",
        "URW Gothic L Demi",
        "URW Gothic L Demi Oblique",
        "URW Palladio L Bold",
        "URW Palladio L Bold Italic",
        "URW Palladio L Italic",
        "URW Palladio L Roman",
        "utkal medium",
        "Vemana2000",
        "Verdana",
        "VRINDA",
        "Waree",
        "Waree Bold",
        "Waree Bold Oblique",
        "Waree Oblique",
        "wasy10",
        "WEBDINGS",
        "WenQuanYi Micro Hei",
        "WenQuanYi Micro Hei Mono",
        "WHITNEY",
        "WINGDINGS",
        "ZAPF DINGBATS",
        "Zapfino"
    );
};
