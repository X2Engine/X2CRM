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
 * Custom extension of CAssetManager used by the app.
 *
 * @package application.components
 */
class X2AssetManager extends CAssetManager {

    public static $enableAssetDomains;
    private $_baseUrl;
    private $_baseUri;
    protected $_staticAssetUrls;
    private static $_assetUrlsRRIndex;

    /**
     * Blacklist of assets to skip when considering using a static asset domain
     */
    public static $skipAssets = array(
        'form.css',
        'x2forms.css',
        'main.css',
        'layout.css',
        'publisher.css',
        'fontAwesome/css/font-awesome.css',
    );

    /**
     * Set the baseUri for generating URLs to static assets
     * @param string $uri Base URI for web requests
     */
    public function setBaseUri($uri) {
        $this->_baseUri = $uri;
    }

    /**
     * Enable use of static asset domains
     */
    public function enableAssetDomains() {
        $assetDomains = Yii::app()->settings->assetBaseUrls;
        $baseUri = Yii::app()->settings->externalBaseUri;
        $this->baseUri = $baseUri;
        $this->_staticAssetUrls = $assetDomains;
        self::$enableAssetDomains = true;
    }

    /**
     * Return the next static asset baseUrl in a round-robin fashion
     */
    public function getNextRRAssetDomain() {
        self::$_assetUrlsRRIndex = (self::$_assetUrlsRRIndex + 1) % count($this->_staticAssetUrls);
        return $this->_staticAssetUrls[self::$_assetUrlsRRIndex].$this->_baseUri;
    }

    /**
     * Replace the baseUrl with an asset domain
     * @param string $url Asset URL to substitute
     * @returns string Replaced asset URL
     */
    public function substituteAssetDomain($url) {
        $baseUrl = Yii::app()->request->baseUrl;
        $assetDomain = $this->getNextRRAssetDomain();

        // Return if this asset is marked as skip, otherwise replace the base URL
        foreach (self::$skipAssets as $asset)
            if (strpos ($url, $asset) !== false)
                return $url;
        $url = str_replace ($baseUrl, $assetDomain, $url);
        return $url;
    }
}
