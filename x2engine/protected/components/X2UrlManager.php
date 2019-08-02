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
 * 
 */
class X2UrlManager extends CUrlManager {
    
    private $_urlFormat=self::GET_FORMAT;
    private $_rules=array();
    private $_baseUrl;

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/  
     */
    public function createUrlWithoutBase($route, $params = array(), $ampersand = '&') {
        unset($params[$this->routeVar]);
        foreach ($params as $i => $param)
            if ($param === null) 
                $params[$i] = '';

        if (isset($params['#'])) {
            $anchor = '#' . $params['#'];
            unset($params['#']);
        } else 
            $anchor = '';
        $route = trim($route, '/');
        foreach ($this->_rules as $i => $rule) {
            if (is_array($rule))
                    $this->_rules[$i] = $rule = Yii::createComponent($rule);
            if (($url = $rule->createUrl($this, $route, $params, $ampersand)) !== false) {
                /* x2modstart */ 
                if ($rule->hasHostInfo)
                    return $url === '' ? '/' . $anchor : $url . $anchor;
                else 
                    return '/' . $url . $anchor;
                /* x2modend */ 
            }
        }
        return $this->createUrlWithoutBaseDefault($route, $params, $ampersand) . $anchor;
    }
    
    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    protected function createUrlWithoutBaseDefault($route, $params, $ampersand) {
        if ($this->getUrlFormat() === self::PATH_FORMAT) {
            /* x2modstart */ 
            $url = '/'.rtrim(basename (Yii::app()->getRequest()->getScriptUrl()) . '/' . $route, '/');
            /* x2modend */ 
            if ($this->appendParams) {
                $url = rtrim($url . '/' . $this->createPathInfo($params, '/',
                                '/'), '/');
                return $route === '' ? $url : $url . $this->urlSuffix;
            } else {
                if ($route !== '') $url.=$this->urlSuffix;
                $query = $this->createPathInfo($params, '=', $ampersand);
                return $query === '' ? $url : $url . '?' . $query;
            }
        }
        else {
            /* x2modstart */ 
            $url = '/'.basename (Yii::app()->getRequest()->getScriptUrl());
            /* x2modend */ 
            if (!$this->showScriptName) $url = '/';
            if ($route !== '') {
                $url.='?' . $this->routeVar . '=' . $route;
                if (($query = $this->createPathInfo($params, '=', $ampersand)) !== '')
                        $url.=$ampersand . $query;
            }
            elseif (($query = $this->createPathInfo($params, '=', $ampersand)) !== '')
                    $url.='?' . $query;
            return $url;
        }
    }

}
