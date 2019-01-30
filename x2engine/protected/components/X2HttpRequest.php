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





class X2HttpRequest extends CHttpRequest {

    private $csrfValidationWhitelist = array (
        '/^api2?\//', // allow all api requests
        '/^contacts\/weblead$/', // allow web form requests
        '/^services\/webForm$/', // allow web form requests
    );

    /**
     * Override parent method to prevent csrf token validation during whitelisted requests
     */
    public function validateCsrfToken ($event) {
        foreach ($this->csrfValidationWhitelist as $regex) {
            if (preg_match ($regex, $this->pathInfo)) {
                return;
            }
        }

        return parent::validateCsrfToken ($event);
    }

	public function redirect($url,$terminate=true,$statusCode=302) {
        if (Yii::app()->isMobileApp ()) {
            $params = array ();
            if (isset ($_GET['x2ajax'])) $params['x2ajax'] = $_GET['x2ajax'];
            if (isset ($_GET['isMobileApp'])) $params['isMobileApp'] = $_GET['isMobileApp'];
             
            if (isset ($_GET['isPhoneGap'])) $params['isPhoneGap'] = $_GET['isPhoneGap'];
            if (isset ($_GET['includeX2TouchJsAssets'])) 
                $params['includeX2TouchJsAssets'] = $_GET['includeX2TouchJsAssets'];
            if (isset ($_GET['includeX2TouchCssAssets'])) 
                $params['includeX2TouchCssAssets'] = $_GET['includeX2TouchCssAssets'];
              
            $url = UrlUtil::mergeParams ($url, $params);
        }
        return parent::redirect ($url, $terminate, $statusCode);
    }


}

?>
