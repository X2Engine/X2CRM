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
 * Base module class for all modules following the convention of self-contained
 * assets, components, etc.
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2WebModule extends CWebModule {

    private $_assetsUrl;

    /**
     * Automatically publishes assets and returns the assets base URL.
     *
     * If constant YII_DEBUG is enabled, assets will be forcefully copied. This
     * is recommended when working on assets; otherwise it would be necessary to
     * clear assets after every change to see changes.
     * @return type
     */
	public function getAssetsUrl() {
		if (!isset($this->_assetsUrl)) {
			$this->_assetsUrl = Yii::app()->assetManager->publish(
                Yii::getPathOfAlias('application.modules.'.$this->id.'.assets'),
                false,-1,YII_DEBUG?true:null);
        }
		return $this->_assetsUrl;
	}

    public function setAssetsUrl ($assetsUrl) {
        $this->_assetsUrl = $assetsUrl;
    }

    public static function getAssetsUrlOfModule ($moduleName) {
        return Yii::app()->assetManager->publish(
            Yii::getPathOfAlias('application.modules.'.lcfirst ($moduleName).'.assets'),
            false,-1,YII_DEBUG?true:null);
    }

}

?>
