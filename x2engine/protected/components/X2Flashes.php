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




/**
 * Used to collect error, notice, and success messages which can then be echoed back to the client.
 * This can be used in conjunction with the UI library X2Flashes.js.
 */

class X2Flashes {

    /**
     * @var array used to hold success, warning, and error messages
     */
    private static $_flashes;

    public static function getFlashes() {
        if (!isset (self::$_flashes)) {
            self::$_flashes = array (
                'notice' => array (),
                'success' => array (),
                'error' => array (),
            );
        }
        return self::$_flashes;
    }

    public static function setFlashes ($flashes) {
        self::$_flashes = $flashes;
    }

    /**
     * @return bool true if flashes have been added, false otherwise 
     */
    public static function hasFlashes ($type=null) {
        $flashes = self::getFlashes ();
        if ($type) {
            $flashes = array ($type => $flashes[$type]);
        }
        return array_reduce (array_values ($flashes), function ($a, $b) { 
            return $a + sizeof ($b); }) !== 0;
    }

    /**
     * Echoes flashes in the flash arrays
     */
    public static function echoFlashes () {
        echo CJSON::encode (self::getFlashes ());
    }

    public static function getFlashesResponse () {
        return CJSON::encode (self::getFlashes ());
    }

    /**
     * Adds a flash of a specified type 
     * @param string $key 'notice'|'success'|'error'
     * @param string $message
     */
    public static function addFlash ($key, $message) {
        $flashes = self::getFlashes ();
        $flashes[$key][] = $message;
        self::setFlashes ($flashes);
    }


    /**
     * Returns html for error, success, and notice flashes. 
     */
    public static function renderFlashes ($type) {
        $flashes = self::getFlashes ();

        if (isset ($flashes[$type])) {
            $flashes = $flashes[$type];
            foreach ($flashes as $flash) {
                echo "<div class='flash-$type'>";
                echo $flash;
                echo "</div>";
            }
        }
    }

    public static function renderTopFlashes ($type) {
        $flashes = Yii::app()->user->getFlashes ();

        if (isset ($flashes[$type])) {
            $flash = $flashes[$type];
            $type = preg_replace ('/^top-/', '', $type);
            echo "
                <div id='top-flashes-container-outer'>
                    <div id='top-flashes-container' class='flash-$type'>
                        <div id='top-flashes-message'>
                        $flash
                        </div>
                    </div>
                
                </div>";
        }
    }

}

?>
