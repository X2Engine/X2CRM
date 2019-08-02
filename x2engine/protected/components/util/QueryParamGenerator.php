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
 * Utility class for simplifying generation SQL parameters
 */
class QueryParamGenerator extends CComponent {

    /**
     * Number of parameters bound 
     */
    private $count = 0;

    /**
     * Parameter namespace 
     */
    private $prefix = ':QueryParamGenerator';

    /**
     * Bound parameters 
     */
    private $params = array ();

    /**
     * @param string $prefix parameter prefix which should be used to prevent parameter name 
     *  collisions
     */
    public function __construct ($prefix=':QueryParamGenerator') {
        $this->prefix = $prefix;
    }

    /**
     * Binds and array of parameters, optionally generating a string representation of the array 
     * which can be embedded into a SQL in statement
     */
    public function bindArray (array $values, $createInStmt=false) {
        if ($createInStmt) {
            $inStmt = '(';
        }
        foreach ($values as $val) {
            $currParam = $this->prefix.++$this->count;
            $this->params[$currParam] = $val;
            if ($createInStmt) {
                if ($inStmt !== '(') {
                    $inStmt .= ',';
                }
                $inStmt.=$currParam;
            }
        }
        if ($createInStmt) {
            $inStmt .= ')';
            return $inStmt;
        }
    }

    /**
     * Bind a value to a parameter name 
     * @return string the generated parameter name
     */
    public function nextParam ($val) {
        $currParam = $this->prefix.++$this->count;
        $this->params[$currParam] = $val;
        return $currParam;
    }

    /**
     * @return string the name of the most recently generated parameter
     */
    public function currParam () {
        return $this->prefix.$this->count;
    }

    public function setParam ($val) {
        $this->params[$this->currParam ()] = $val;
    }

    /**
     * @return array all generated parameters (bound values indexed by parameter names)
     */
    public function getParams () {
        return $this->params;
    }

    /**
     * Merge the internal parameters array with an arbitrary number of other parameters arrays
     * @param {...array}
     * @throws CException if parameter name collision occurs 
     */
    public function mergeParams () {
        $arguments = func_get_args ();
        foreach ($arguments as $params) {
            if (count (array_intersect (array_keys ($params), $this->params))) {
                throw new CException ('parameter name collision');
            }
            $this->params = array_merge ($this->params, $params);
        }
        return $this;
    }
}
