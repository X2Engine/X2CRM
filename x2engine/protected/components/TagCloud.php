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
 * Widget that renders a tag cloud
 * 
 * @package application.components 
 */
class TagCloud extends X2Widget {
	
	public $visibility;
	public function init() {
		parent::init();
	}

	public function run() {
        $hiddenTags=json_decode(Yii::app()->params->profile->hiddenTags,true);
        $params = array ();
        if((is_array($hiddenTags) || $hiddenTags instanceof Countable) && count($hiddenTags)>0){
            $tagParams = AuxLib::bindArray ($hiddenTags);
            $params = array_merge ($params, $tagParams);
            $str1=" AND tag NOT IN (".implode (',', array_keys ($tagParams)).")";
        }else{
            $str1="";
        }
		$myTags = Yii::app()->db->createCommand()
			->select('COUNT(*) AS count, tag')
			->from('x2_tags')
			->where('taggedBy=:user AND tag IS NOT NULL'.$str1,array_merge ($params, array(':user'=>Yii::app()->user->getName())))
			->group('tag')
			->order('count DESC')
			->limit(20)
			->queryAll();
		
		$allTags = Yii::app()->db->createCommand()
			->select('COUNT(*) AS count, tag')
			->from('x2_tags')
			->group('tag')
            ->where('tag IS NOT NULL'.$str1, $params)
			->order('count DESC')
			->limit(20)
			->queryAll();
	
		// $myTags=Tags::model()->findAllBySql("SELECT *, COUNT(*) as num FROM x2_tags WHERE taggedBy='".Yii::app()->user->getName()."' GROUP BY tag ORDER BY num DESC LIMIT 20");
		// $allTags=Tags::model()->findAllBySql("SELECT *, COUNT(*) as num FROM x2_tags GROUP BY tag ORDER BY num DESC LIMIT 20");
		$this->render('tagCloud',array(
			'myTags'=>$myTags,
			'allTags'=>$allTags,
			'showAllUsers'=>Yii::app()->params->profile->tagsShowAllUsers,
		));
	}
}
