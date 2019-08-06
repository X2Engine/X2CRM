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
 * Class for formatting pretty console messages.
 *
 * Formatting methods are intended to be chained, i.e.
 *
 * $formatter->bgColor($backgroundColor)->bold()->color($textColor)->format();
 *
 * Only "format" returns the message with the escape sequences. Note also,
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package application.commands
 */
class ConsoleFormatterUtil {

    public $msg;

    /**
     * ANSI escape sequence codes
     * @var type
     */
    private $_seq = array(
        'clr' => '0',
        'bold' => '1',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'purple' => '35',
        'cyan' => '36',
        'white' => '37',
        'bg_black' => '40',
        'bg_red' => '41',
        'bg_magenta' => '45',
        'bg_yellow' => '43',
        'bg_green' => '42',
        'bg_blue' => '44',
        'bg_cyan' => '46',
        'bg_light_gray' => '47',
    );

    public function __construct($msg){
        $this->msg = $msg;
    }

    public function bgColor($color) {
        $color = isset($this->_seq["bg_$color"]) ? $this->_seq["bg_$color"] : $color;
        $this->msg = "\033[".$color.'m'.$this->msg;
        return $this;
    }

    /**
     * Returns a message in boldface.
     * @param type $msg
     */
    public function bold(){
        $this->msg = "\033[".$this->_seq['bold'].'m'.$this->msg;
        return $this;
    }

    /**
     * Returns a message with color sequences applied.
     *
     * @param type $msg
     * @param type $color
     * @return type
     */
    public function color($color){
        $color = isset($this->_seq[$color]) ? $this->_seq[$color] : $color;
        $this->msg = "\033[".$color.'m'.$this->msg;
        return $this;
    }

    public function format(){
        return $this->msg."\033[".$this->_seq['clr'].'m';
    }
}

?>
